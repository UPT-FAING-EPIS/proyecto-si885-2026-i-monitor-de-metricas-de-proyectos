<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

final class DashboardController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $accessToken = $this->authAccessToken();
        $user = $this->authUser();
        $userId = is_array($user) ? (string)($user['id'] ?? '') : '';

        $projects = [];
        $assignedTasks = [];
        $timeEntries = [];
        $errors = [];

        if ($userId !== '') {
            $projectsRes = $this->supabase()->postgrestSelect('projects', [
                'select' => 'id,name,description,created_at',
                'order' => 'created_at.desc',
            ], $accessToken);
            if ($projectsRes['ok'] && is_array($projectsRes['json'])) {
                $projects = $projectsRes['json'];
            } else {
                $errors[] = 'No se pudo cargar proyectos. Verifica esquema/RLS.';
            }

            $tasksRes = $this->supabase()->postgrestSelect('tasks', [
                'select' => 'id,project_id,title,status,estimated_minutes,due_date,updated_at',
                'assignee_id' => 'eq.' . $userId,
                'order' => 'due_date.asc.nullslast,created_at.desc',
            ], $accessToken);
            if ($tasksRes['ok'] && is_array($tasksRes['json'])) {
                $assignedTasks = $tasksRes['json'];
            } else {
                $errors[] = 'No se pudo cargar tareas asignadas.';
            }

            $timeRes = $this->supabase()->postgrestSelect('time_entries', [
                'select' => 'id,task_id,minutes,created_at',
                'user_id' => 'eq.' . $userId,
                'order' => 'created_at.desc',
            ], $accessToken);
            if ($timeRes['ok'] && is_array($timeRes['json'])) {
                $timeEntries = $timeRes['json'];
            }
        }

        $assignedCount = count($assignedTasks);
        $completedCount = 0;
        foreach ($assignedTasks as $t) {
            if (($t['status'] ?? '') === 'completed') {
                $completedCount++;
            }
        }

        $minutesTotal = 0;
        foreach ($timeEntries as $e) {
            $minutesTotal += (int)($e['minutes'] ?? 0);
        }

        $today = (new \DateTimeImmutable('today'))->format('Y-m-d');
        $inTwoDays = (new \DateTimeImmutable('today +2 days'))->format('Y-m-d');
        $dueSoon = [];
        $overdue = [];
        foreach ($assignedTasks as $t) {
            $due = (string)($t['due_date'] ?? '');
            if ($due === '') {
                continue;
            }
            if ($due < $today && ($t['status'] ?? '') !== 'completed') {
                $overdue[] = $t;
                continue;
            }
            if ($due >= $today && $due <= $inTwoDays && ($t['status'] ?? '') !== 'completed') {
                $dueSoon[] = $t;
            }
        }

        $this->render('dashboard.index', [
            'projects' => $projects,
            'assignedTasks' => $assignedTasks,
            'stats' => [
                'assigned' => $assignedCount,
                'completed' => $completedCount,
                'minutes' => $minutesTotal,
            ],
            'notifications' => [
                'dueSoon' => $dueSoon,
                'overdue' => $overdue,
            ],
            'errors' => $errors,
        ]);
    }
}
