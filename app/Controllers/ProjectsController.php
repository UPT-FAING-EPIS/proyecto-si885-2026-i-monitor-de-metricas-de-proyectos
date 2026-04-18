<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Flash;

final class ProjectsController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $accessToken = $this->authAccessToken();

        $projectsRes = $this->supabase()->postgrestSelect('projects', [
            'select' => 'id,name,description,created_at',
            'order' => 'created_at.desc',
        ], $accessToken);

        $projects = ($projectsRes['ok'] && is_array($projectsRes['json'])) ? $projectsRes['json'] : [];

        $this->render('projects.index', [
            'projects' => $projects,
            'loadError' => $projectsRes['ok'] ? null : 'No se pudo cargar proyectos (esquema/RLS).',
        ]);
    }

    public function showCreate(): void
    {
        $this->requireAuth();
        $this->render('projects.new');
    }

    public function create(): void
    {
        $this->requireAuth();
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            echo 'CSRF';
            return;
        }

        $profileErr = $this->syncProfileIfPossible();
        if (is_string($profileErr) && $profileErr !== '') {
            Flash::set('error', 'No se pudo preparar tu perfil en Supabase: ' . $profileErr);
            $this->redirect('/projects/new');
        }

        $name = trim((string)($_POST['name'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));

        if ($name === '') {
            Flash::set('error', 'El nombre del proyecto es obligatorio.');
            $this->redirect('/projects/new');
        }

        $accessToken = $this->authAccessToken();
        $res = $this->supabase()->postgrestInsert('projects', [
            [
                'name' => $name,
                'description' => $description !== '' ? $description : null,
            ],
        ], $accessToken);

        if (!$res['ok'] || !is_array($res['json']) || empty($res['json'][0]['id'])) {
            $status = (int)($res['status'] ?? 0);
            $msg = $this->extractError($res);
            Flash::set('error', 'No se pudo crear el proyecto. (' . $status . ') ' . $msg);
            $this->redirect('/projects/new');
        }

        $id = (string)$res['json'][0]['id'];
        Flash::set('success', 'Proyecto creado.');
        $this->redirect('/projects/' . $id);
    }

    public function show(string $id): void
    {
        $this->requireAuth();
        $accessToken = $this->authAccessToken();

        $projectRes = $this->supabase()->postgrestSelect('projects', [
            'select' => 'id,name,description,created_at',
            'id' => 'eq.' . $id,
            'limit' => '1',
        ], $accessToken);

        $project = null;
        if ($projectRes['ok'] && is_array($projectRes['json']) && isset($projectRes['json'][0])) {
            $project = $projectRes['json'][0];
        }

        if (!$project) {
            http_response_code(404);
            echo 'Proyecto no encontrado';
            return;
        }

        $tasksRes = $this->supabase()->postgrestSelect('tasks', [
            'select' => 'id,title,description,status,estimated_minutes,due_date,assignee_id,created_at,updated_at',
            'project_id' => 'eq.' . $id,
            'order' => 'created_at.desc',
        ], $accessToken);

        $tasks = ($tasksRes['ok'] && is_array($tasksRes['json'])) ? $tasksRes['json'] : [];

        $taskIds = [];
        $estimatedTotal = 0;
        $completed = 0;
        foreach ($tasks as $t) {
            if (!empty($t['id'])) {
                $taskIds[] = (string)$t['id'];
            }
            $estimatedTotal += (int)($t['estimated_minutes'] ?? 0);
            if (($t['status'] ?? '') === 'completed') {
                $completed++;
            }
        }

        $minutesReal = 0;
        if (!empty($taskIds)) {
            $inList = '(' . implode(',', array_map(static fn(string $x): string => $x, $taskIds)) . ')';
            $timeRes = $this->supabase()->postgrestSelect('time_entries', [
                'select' => 'minutes,task_id',
                'task_id' => 'in.' . $inList,
            ], $accessToken);
            if ($timeRes['ok'] && is_array($timeRes['json'])) {
                foreach ($timeRes['json'] as $e) {
                    $minutesReal += (int)($e['minutes'] ?? 0);
                }
            }
        }

        $total = count($tasks);
        $progress = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

        $this->render('projects.show', [
            'project' => $project,
            'tasks' => $tasks,
            'metrics' => [
                'total' => $total,
                'completed' => $completed,
                'pending' => max(0, $total - $completed),
                'progress' => $progress,
                'estimated' => $estimatedTotal,
                'real' => $minutesReal,
            ],
        ]);
    }

    public function createTask(string $id): void
    {
        $this->requireAuth();
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            echo 'CSRF';
            return;
        }

        $title = trim((string)($_POST['title'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));
        $estimated = (int)($_POST['estimated_minutes'] ?? 0);
        $dueDate = trim((string)($_POST['due_date'] ?? ''));
        $assigneeEmail = trim((string)($_POST['assignee_email'] ?? ''));

        if ($title === '' || $assigneeEmail === '') {
            Flash::set('error', 'Título y email del asignado son obligatorios.');
            $this->redirect('/projects/' . $id);
        }

        $accessToken = $this->authAccessToken();
        $rpc = $this->supabase()->rpc('get_user_id_by_email', [
            'email_in' => $assigneeEmail,
        ], $accessToken);

        $assigneeId = null;
        if ($rpc['ok']) {
            $json = $rpc['json'] ?? null;
            if (is_array($json) && isset($json[0]['id'])) {
                $assigneeId = (string)$json[0]['id'];
            } elseif (is_string($json) && $json !== '') {
                $assigneeId = $json;
            }
        }

        if (!is_string($assigneeId) || $assigneeId === '') {
            Flash::set('error', 'No se encontró un usuario con ese email.');
            $this->redirect('/projects/' . $id);
        }

        $res = $this->supabase()->postgrestInsert('tasks', [
            [
                'project_id' => $id,
                'title' => $title,
                'description' => $description !== '' ? $description : null,
                'status' => 'pending',
                'estimated_minutes' => $estimated > 0 ? $estimated : null,
                'due_date' => $dueDate !== '' ? $dueDate : null,
                'assignee_id' => $assigneeId,
            ],
        ], $accessToken);

        if (!$res['ok']) {
            Flash::set('error', 'No se pudo crear la tarea. Verifica RLS/asignación.');
            $this->redirect('/projects/' . $id);
        }

        Flash::set('success', 'Tarea creada y asignada.');
        $this->redirect('/projects/' . $id);
    }
}
