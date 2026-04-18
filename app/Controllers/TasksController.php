<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Flash;

final class TasksController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $accessToken = $this->authAccessToken();
        $user = $this->authUser();
        $userId = is_array($user) ? (string)($user['id'] ?? '') : '';

        $query = [
            'select' => 'id,project_id,title,status,estimated_minutes,due_date,updated_at',
            'assignee_id' => 'eq.' . $userId,
            'order' => 'due_date.asc.nullslast,created_at.desc',
        ];

        $status = trim((string)($_GET['status'] ?? ''));
        if ($status !== '') {
            $query['status'] = 'eq.' . $status;
        }

        $from = trim((string)($_GET['from'] ?? ''));
        if ($from !== '') {
            $query['due_date'] = 'gte.' . $from;
        }

        $to = trim((string)($_GET['to'] ?? ''));
        if ($to !== '') {
            $existing = (string)($query['due_date'] ?? '');
            if ($existing !== '') {
                $query['and'] = '(due_date.gte.' . $from . ',due_date.lte.' . $to . ')';
                unset($query['due_date']);
            } else {
                $query['due_date'] = 'lte.' . $to;
            }
        }

        $res = $this->supabase()->postgrestSelect('tasks', $query, $accessToken);
        $tasks = ($res['ok'] && is_array($res['json'])) ? $res['json'] : [];

        $this->render('tasks.index', [
            'tasks' => $tasks,
            'filters' => [
                'status' => $status,
                'from' => $from,
                'to' => $to,
            ],
            'loadError' => $res['ok'] ? null : 'No se pudo cargar tareas.',
        ]);
    }

    public function show(string $id): void
    {
        $this->requireAuth();
        $accessToken = $this->authAccessToken();
        $user = $this->authUser();
        $userId = is_array($user) ? (string)($user['id'] ?? '') : '';

        $taskRes = $this->supabase()->postgrestSelect('tasks', [
            'select' => 'id,project_id,title,description,status,estimated_minutes,due_date,assignee_id,created_at,updated_at',
            'id' => 'eq.' . $id,
            'limit' => '1',
        ], $accessToken);

        $task = null;
        if ($taskRes['ok'] && is_array($taskRes['json']) && isset($taskRes['json'][0])) {
            $task = $taskRes['json'][0];
        }
        if (!$task) {
            http_response_code(404);
            echo 'Tarea no encontrada';
            return;
        }

        $isAssignee = $userId !== '' && $userId === (string)($task['assignee_id'] ?? '');

        $timeRes = $this->supabase()->postgrestSelect('time_entries', [
            'select' => 'id,minutes,notes,created_at',
            'task_id' => 'eq.' . $id,
            'order' => 'created_at.desc',
        ], $accessToken);
        $timeEntries = ($timeRes['ok'] && is_array($timeRes['json'])) ? $timeRes['json'] : [];

        $minutesReal = 0;
        foreach ($timeEntries as $e) {
            $minutesReal += (int)($e['minutes'] ?? 0);
        }

        $evRes = $this->supabase()->postgrestSelect('task_evidences', [
            'select' => 'id,storage_path,filename,created_at',
            'task_id' => 'eq.' . $id,
            'order' => 'created_at.desc',
        ], $accessToken);
        $evidences = ($evRes['ok'] && is_array($evRes['json'])) ? $evRes['json'] : [];

        $signedUrls = [];
        if ($isAssignee) {
            foreach ($evidences as $ev) {
                $path = (string)($ev['storage_path'] ?? '');
                if ($path === '') {
                    continue;
                }
                $signed = $this->supabase()->storageCreateSignedUrl('task-evidences', $path, 3600, $accessToken);
                $json = $signed['json'] ?? null;
                if ($signed['ok'] && is_array($json) && isset($json['signedURL'])) {
                    $signedUrls[$path] = $this->supabaseUrl() . (string)$json['signedURL'];
                }
            }
        }

        $this->render('tasks.show', [
            'task' => $task,
            'isAssignee' => $isAssignee,
            'timeEntries' => $timeEntries,
            'minutesReal' => $minutesReal,
            'evidences' => $evidences,
            'signedUrls' => $signedUrls,
        ]);
    }

    public function updateStatus(string $id): void
    {
        $this->requireAuth();
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            echo 'CSRF';
            return;
        }

        $status = (string)($_POST['status'] ?? '');
        $allowed = ['pending', 'in_progress', 'completed'];
        if (!in_array($status, $allowed, true)) {
            Flash::set('error', 'Estado inválido.');
            $this->redirect('/tasks/' . $id);
        }

        $accessToken = $this->authAccessToken();
        $res = $this->supabase()->postgrestPatch('tasks', [
            'id' => 'eq.' . $id,
        ], [
            'status' => $status,
        ], $accessToken);

        if (!$res['ok']) {
            Flash::set('error', 'No se pudo actualizar el estado (RLS).');
            $this->redirect('/tasks/' . $id);
        }

        Flash::set('success', 'Estado actualizado.');
        $this->redirect('/tasks/' . $id);
    }

    public function addTime(string $id): void
    {
        $this->requireAuth();
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            echo 'CSRF';
            return;
        }

        $minutes = (int)($_POST['minutes'] ?? 0);
        $notes = trim((string)($_POST['notes'] ?? ''));

        if ($minutes <= 0) {
            Flash::set('error', 'Los minutos deben ser mayores a 0.');
            $this->redirect('/tasks/' . $id);
        }

        $accessToken = $this->authAccessToken();
        $res = $this->supabase()->postgrestInsert('time_entries', [
            [
                'task_id' => $id,
                'minutes' => $minutes,
                'notes' => $notes !== '' ? $notes : null,
            ],
        ], $accessToken, returnRepresentation: false);

        if (!$res['ok']) {
            Flash::set('error', 'No se pudo registrar tiempo (RLS).');
            $this->redirect('/tasks/' . $id);
        }

        Flash::set('success', 'Tiempo registrado.');
        $this->redirect('/tasks/' . $id);
    }

    public function uploadEvidence(string $id): void
    {
        $this->requireAuth();
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            echo 'CSRF';
            return;
        }

        if (!isset($_FILES['file']) || !is_array($_FILES['file'])) {
            Flash::set('error', 'Archivo inválido.');
            $this->redirect('/tasks/' . $id);
        }

        $file = $_FILES['file'];
        $tmp = (string)($file['tmp_name'] ?? '');
        $name = (string)($file['name'] ?? '');
        $type = (string)($file['type'] ?? 'application/octet-stream');
        $error = (int)($file['error'] ?? 0);

        if ($error !== UPLOAD_ERR_OK || $tmp === '' || !is_file($tmp)) {
            Flash::set('error', 'No se pudo leer el archivo.');
            $this->redirect('/tasks/' . $id);
        }

        $user = $this->authUser();
        $userId = is_array($user) ? (string)($user['id'] ?? '') : '';
        if ($userId === '') {
            Flash::set('error', 'Sesión inválida.');
            $this->redirect('/tasks/' . $id);
        }

        $safeName = preg_replace('/[^a-zA-Z0-9._-]+/', '_', $name) ?: 'evidence.bin';
        $path = $userId . '/' . $id . '/' . bin2hex(random_bytes(8)) . '_' . $safeName;
        $bytes = file_get_contents($tmp);
        if ($bytes === false) {
            Flash::set('error', 'No se pudo cargar el archivo.');
            $this->redirect('/tasks/' . $id);
        }

        $accessToken = $this->authAccessToken();
        $up = $this->supabase()->storageUpload('task-evidences', $path, $bytes, $type, $accessToken);
        if (!$up['ok']) {
            Flash::set('error', 'No se pudo subir el archivo (Storage/RLS).');
            $this->redirect('/tasks/' . $id);
        }

        $ins = $this->supabase()->postgrestInsert('task_evidences', [
            [
                'task_id' => $id,
                'storage_path' => $path,
                'filename' => $safeName,
            ],
        ], $accessToken, returnRepresentation: false);

        if (!$ins['ok']) {
            Flash::set('error', 'Se subió el archivo, pero no se pudo registrar la evidencia.');
            $this->redirect('/tasks/' . $id);
        }

        Flash::set('success', 'Evidencia subida.');
        $this->redirect('/tasks/' . $id);
    }

    private function supabaseUrl(): string
    {
        $cfg = $this->config();
        return (string)($cfg['supabase']['url'] ?? '');
    }
}
