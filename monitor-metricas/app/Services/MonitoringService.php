<?php
declare(strict_types=1);

namespace App\Services;

use App\Interfaces\IMonitoringService;
use App\Repositories\ProjectMetricsRepository;

final class MonitoringService implements IMonitoringService
{
    public function __construct(private readonly ProjectMetricsRepository $metrics)
    {
    }

    public function getDashboardData(string $userId): array
    {
        $summary = $this->metrics->getSummary($userId);
        $boards = $this->metrics->getBoardBreakdown($userId, 12);
        $workspaces = $this->metrics->getWorkspaceBreakdown($userId, 6);
        $statusDistribution = $this->normalizeStatusDistribution($this->metrics->getStatusDistribution($userId), $summary);
        $logs = $this->metrics->getRecentLogsForUser($userId, 6);
        $alerts = $this->buildAlertsFromBoards($boards, $logs);
        $recentActivity = $this->buildRecentActivity($logs, $alerts);
        $riskCount = count(array_filter($alerts, static fn (array $alert): bool => ($alert['severity'] ?? '') !== 'Riesgo Bajo'));
        $series = $this->progressSeriesFromBoards($boards, (float)($summary['progress_percentage'] ?? 0.0));

        return [
            'kpis' => [
                $this->kpi('Proyectos Activos', (int)($summary['boards'] ?? 0), (float)($summary['progress_percentage'] ?? 0.0), 'up', 'layers'),
                $this->kpi('Tareas Totales', (int)($summary['total_tasks'] ?? 0), (float)($summary['pending_tasks'] ?? 0), 'up', 'list'),
                $this->kpi('Tareas Completadas', (int)($summary['completed_tasks'] ?? 0), (float)($summary['progress_percentage'] ?? 0.0), 'up', 'check'),
                $this->kpi('Tareas Vencidas', (int)($summary['overdue_tasks'] ?? 0), $summary['total_tasks'] > 0 ? round(((int)$summary['overdue_tasks'] / (int)$summary['total_tasks']) * 100, 2) : 0.0, 'down', 'alert'),
                $this->kpi('Riesgos Detectados', $riskCount, (float)$riskCount, $riskCount > 0 ? 'up' : 'down', 'shield'),
            ],
            'projectProgressSeries' => $series,
            'teams' => array_map(fn (array $row): array => [
                'name' => (string)$row['name'],
                'value' => (float)$row['progress_percentage'],
            ], $workspaces),
            'statusDistribution' => $statusDistribution,
            'recentActivity' => $recentActivity,
            'topPerformance' => array_slice(array_map(fn (array $board): array => [
                'name' => (string)$board['name'],
                'owner' => (string)($board['workspace_name'] ?? 'Trello'),
                'progress' => (int)round((float)($board['progress_percentage'] ?? 0.0)),
                'delta' => round((float)($board['progress_percentage'] ?? 0.0) - (float)($summary['progress_percentage'] ?? 0.0), 2),
            ], $this->sortBoards($boards, 'progress_percentage', true)), 0, 4),
            'topRisk' => array_slice(array_map(fn (array $board): array => [
                'name' => (string)$board['name'],
                'owner' => (string)($board['workspace_name'] ?? 'Trello'),
                'risk' => $this->riskLabel((int)($board['overdue_tasks'] ?? 0), (float)($board['progress_percentage'] ?? 0.0)),
                'overdue' => (int)($board['overdue_tasks'] ?? 0),
            ], $this->sortBoards($boards, 'overdue_tasks', true)), 0, 4),
            'payload' => [
                'projectProgressSeries' => $series,
                'teams' => array_map(fn (array $row): array => [
                    'name' => (string)$row['name'],
                    'value' => (float)$row['progress_percentage'],
                ], $workspaces),
                'statusDistribution' => array_map(static fn (array $row): array => [
                    'label' => (string)$row['label'],
                    'value' => (int)$row['value'],
                ], $statusDistribution),
            ],
        ];
    }

    public function getProjectsData(string $userId): array
    {
        $summary = $this->metrics->getSummary($userId);
        $workspaces = $this->metrics->getWorkspaceBreakdown($userId, 100);
        $latestSync = $this->metrics->getLatestSyncForUser($userId);
        $projects = array_map(fn (array $board): array => $this->mapBoardToProjectCard($board, $latestSync), $this->metrics->getBoardBreakdown($userId, 100));
        $statusSummary = [
            'enCurso' => 0,
            'riesgo' => 0,
            'completado' => 0,
            'espera' => 0,
        ];
        foreach ($projects as $project) {
            $status = (string)($project['status'] ?? '');
            if ($status === 'Riesgo') {
                $statusSummary['riesgo']++;
                continue;
            }
            if ($status === 'Completado') {
                $statusSummary['completado']++;
                continue;
            }
            if ($status === 'En espera') {
                $statusSummary['espera']++;
                continue;
            }
            $statusSummary['enCurso']++;
        }

        return [
            'projects' => $projects,
            'summary' => [
                'projects' => count($projects),
                'workspaces' => count($workspaces),
                'tasksTotal' => (int)($summary['total_tasks'] ?? 0),
                'tasksDone' => (int)($summary['completed_tasks'] ?? 0),
                'tasksPending' => (int)($summary['pending_tasks'] ?? 0),
                'tasksOverdue' => (int)($summary['overdue_tasks'] ?? 0),
                'progress' => (int)round((float)($summary['progress_percentage'] ?? 0.0)),
                'lastSync' => (string)($latestSync['finished_at'] ?? $latestSync['started_at'] ?? ''),
            ],
            'statusSummary' => $statusSummary,
        ];
    }

    public function getProjectDetailData(string $userId, string $projectId): array
    {
        $boardId = (int)$projectId;
        $board = $boardId > 0 ? $this->metrics->getBoardById($userId, $boardId) : null;
        $latestSync = $this->metrics->getLatestSyncForUser($userId);
        if ($board === null) {
            $emptyProject = [
                'id' => '',
                'name' => 'Sin datos sincronizados',
                'status' => 'En espera',
                'owner' => 'Trello',
                'lastSync' => gmdate(DATE_ATOM),
                'progress' => 0,
                'tasksTotal' => 0,
                'tasksDone' => 0,
                'tasksOverdue' => 0,
                'members' => [],
            ];
            return [
                'projects' => [$emptyProject],
                'projectId' => '',
                'activity' => [],
            ];
        }

        $lists = $this->metrics->getListBreakdownForBoard($userId, $boardId);
        $projects = [$this->mapBoardToProjectDetail($board, $latestSync, $lists)];

        return [
            'projects' => $projects,
            'projectId' => (string)$boardId,
            'activity' => $this->buildProjectActivity($board, $latestSync, $lists),
        ];
    }

    public function getAnalyticsData(string $userId): array
    {
        $summary = $this->metrics->getSummary($userId);
        $boards = $this->metrics->getBoardBreakdown($userId, 100);
        $workspaces = $this->metrics->getWorkspaceBreakdown($userId, 20);
        $topProject = $boards[0] ?? null;
        if ($topProject !== null) {
            foreach ($boards as $board) {
                if ((float)($board['progress_percentage'] ?? 0.0) > (float)($topProject['progress_percentage'] ?? 0.0)) {
                    $topProject = $board;
                }
            }
        }
        $topTeam = $workspaces[0] ?? null;
        if ($topTeam !== null) {
            foreach ($workspaces as $workspace) {
                if ((float)($workspace['progress_percentage'] ?? 0.0) > (float)($topTeam['progress_percentage'] ?? 0.0)) {
                    $topTeam = $workspace;
                }
            }
        }

        return [
            'projects' => array_map(fn (array $board): array => [
                'id' => (string)$board['board_id'],
                'name' => (string)$board['name'],
                'series' => $this->buildSeriesFromMetrics(
                    (int)$board['total_tasks'],
                    (int)$board['completed_tasks'],
                    (int)$board['pending_tasks'],
                    (int)$board['overdue_tasks']
                ),
            ], $boards),
            'teams' => array_map(fn (array $workspace): array => [
                'id' => (string)$workspace['workspace_id'],
                'name' => (string)$workspace['name'],
                'series' => $this->buildSeriesFromMetrics(
                    (int)$workspace['total_tasks'],
                    (int)$workspace['completed_tasks'],
                    (int)$workspace['pending_tasks'],
                    (int)$workspace['overdue_tasks']
                ),
            ], $workspaces),
            'summary' => [
                'projectCount' => count($boards),
                'teamCount' => count($workspaces),
                'totalTasks' => (int)($summary['total_tasks'] ?? 0),
                'completedTasks' => (int)($summary['completed_tasks'] ?? 0),
                'pendingTasks' => (int)($summary['pending_tasks'] ?? 0),
                'overdueTasks' => (int)($summary['overdue_tasks'] ?? 0),
                'progress' => (int)round((float)($summary['progress_percentage'] ?? 0.0)),
                'topProject' => $topProject === null ? '' : (string)($topProject['name'] ?? ''),
                'topProjectProgress' => $topProject === null ? 0 : (int)round((float)($topProject['progress_percentage'] ?? 0.0)),
                'topTeam' => $topTeam === null ? '' : (string)($topTeam['name'] ?? ''),
                'topTeamProgress' => $topTeam === null ? 0 : (int)round((float)($topTeam['progress_percentage'] ?? 0.0)),
            ],
        ];
    }

    public function getAlertsData(string $userId): array
    {
        $boards = $this->metrics->getBoardBreakdown($userId, 100);
        $logs = $this->metrics->getRecentLogsForUser($userId, 10);
        $alerts = $this->buildAlertsFromBoards($boards, $logs);

        return [
            'alerts' => $alerts,
            'projects' => array_values(array_unique(array_map(static fn (array $alert): string => (string)$alert['project'], $alerts))),
            'types' => [
                ['id' => 'overdue', 'label' => 'Muchas tareas vencidas'],
                ['id' => 'productivity', 'label' => 'Baja productividad'],
                ['id' => 'overload', 'label' => 'Carga operativa alta'],
                ['id' => 'inactivity', 'label' => 'Sincronización desactualizada'],
            ],
        ];
    }

    /** @param array<string,mixed> $summary */
    private function normalizeStatusDistribution(array $distribution, array $summary): array
    {
        if ($distribution !== []) {
            return $distribution;
        }

        return [
            ['label' => 'To Do', 'value' => max(0, (int)($summary['pending_tasks'] ?? 0) - (int)($summary['overdue_tasks'] ?? 0)), 'color' => 'bg-slate-400', 'tone' => 'slate'],
            ['label' => 'In Progress', 'value' => (int)($summary['pending_tasks'] ?? 0), 'color' => 'bg-pm-500', 'tone' => 'pm'],
            ['label' => 'Blocked', 'value' => (int)($summary['overdue_tasks'] ?? 0), 'color' => 'bg-rose-500', 'tone' => 'rose'],
            ['label' => 'Done', 'value' => (int)($summary['completed_tasks'] ?? 0), 'color' => 'bg-emerald-500', 'tone' => 'emerald'],
        ];
    }

    /** @param list<array<string,mixed>> $boards */
    private function progressSeriesFromBoards(array $boards, float $fallback): array
    {
        $series = array_values(array_map(static fn (array $board): int => (int)round((float)($board['progress_percentage'] ?? 0.0)), $boards));
        if ($series === []) {
            $series = [(int)round($fallback)];
        }
        while (count($series) < 12) {
            $series[] = (int)end($series);
        }
        return array_slice($series, 0, 12);
    }

    /** @param list<array<string,mixed>> $boards */
    private function buildAlertsFromBoards(array $boards, array $logs): array
    {
        $alerts = [];
        foreach ($boards as $board) {
            $boardId = (string)($board['board_id'] ?? '');
            $project = (string)($board['name'] ?? 'Proyecto');
            $progress = (float)($board['progress_percentage'] ?? 0.0);
            $overdue = (int)($board['overdue_tasks'] ?? 0);
            $pending = (int)($board['pending_tasks'] ?? 0);
            $total = (int)($board['total_tasks'] ?? 0);
            $completed = (int)($board['completed_tasks'] ?? 0);
            $workspace = (string)($board['workspace_name'] ?? 'Trello');

            if ($overdue > 0) {
                $alerts[] = [
                    'id' => 'overdue-' . $boardId,
                    'projectId' => $boardId,
                    'severity' => $overdue >= 10 ? 'Riesgo Alto' : ($overdue >= 4 ? 'Riesgo Medio' : 'Riesgo Bajo'),
                    'date' => gmdate(DATE_ATOM),
                    'project' => $project,
                    'signal' => 'Muchas tareas vencidas',
                    'detail' => sprintf('%d tareas vencidas detectadas en %s.', $overdue, $project),
                    'recommended' => 'Revisar responsables, fechas objetivo y capacidad del equipo.',
                    'type' => 'overdue',
                ];
            }

            if ($total > 0 && $progress < 50.0 && $pending >= max(5, $completed)) {
                $alerts[] = [
                    'id' => 'productivity-' . $boardId,
                    'projectId' => $boardId,
                    'severity' => $progress < 35.0 ? 'Riesgo Alto' : 'Riesgo Medio',
                    'date' => gmdate(DATE_ATOM),
                    'project' => $project,
                    'signal' => 'Baja productividad',
                    'detail' => sprintf('El avance actual es %.1f%% con %d tareas pendientes.', $progress, $pending),
                    'recommended' => 'Ajustar alcance, desbloquear tareas y revisar el flujo de trabajo del board.',
                    'type' => 'productivity',
                ];
            }

            if ($pending >= 20) {
                $alerts[] = [
                    'id' => 'overload-' . $boardId,
                    'projectId' => $boardId,
                    'severity' => $pending >= 40 ? 'Riesgo Alto' : 'Riesgo Medio',
                    'date' => gmdate(DATE_ATOM),
                    'project' => $project,
                    'signal' => 'Carga operativa alta',
                    'detail' => sprintf('%s mantiene %d tareas pendientes activas.', $workspace, $pending),
                    'recommended' => 'Priorizar entregables, limitar WIP y redistribuir capacidad.',
                    'type' => 'overload',
                ];
            }
        }

        if ($logs !== []) {
            $lastLog = $logs[0];
            $finishedAt = (string)($lastLog['finished_at'] ?? $lastLog['started_at'] ?? '');
            if ($finishedAt !== '' && strtotime($finishedAt) !== false && strtotime($finishedAt) < strtotime('-24 hours')) {
                $alerts[] = [
                    'id' => 'inactivity-sync',
                    'projectId' => '',
                    'severity' => 'Riesgo Medio',
                    'date' => $finishedAt,
                    'project' => 'Integración Trello',
                    'signal' => 'Sincronización desactualizada',
                    'detail' => 'La última sincronización está fuera de la ventana operativa esperada.',
                    'recommended' => 'Ejecutar sincronización manual y revisar el estado de la conexión Trello.',
                    'type' => 'inactivity',
                ];
            }
        }

        usort($alerts, static fn (array $a, array $b): int => strcmp((string)$b['date'], (string)$a['date']));
        return $alerts;
    }

    /** @param list<array<string,mixed>> $logs */
    private function buildRecentActivity(array $logs, array $alerts): array
    {
        $items = [];
        foreach (array_slice($logs, 0, 3) as $log) {
            $items[] = [
                'title' => sprintf(
                    'Sincronización %s completada: %d boards, %d cards',
                    (string)($log['sync_type'] ?? 'all'),
                    (int)($log['boards_processed'] ?? 0),
                    (int)($log['cards_processed'] ?? 0)
                ),
                'meta' => $this->humanizeDate((string)($log['finished_at'] ?? $log['started_at'] ?? '')) . ' · Trello',
                'type' => ((int)($log['errors_count'] ?? 0) > 0) ? 'risk' : 'sync',
            ];
        }
        foreach (array_slice($alerts, 0, 3) as $alert) {
            $items[] = [
                'title' => (string)$alert['signal'] . ' en "' . (string)$alert['project'] . '"',
                'meta' => $this->humanizeDate((string)$alert['date']) . ' · Alertas',
                'type' => (string)$alert['type'] === 'overdue' ? 'alert' : 'risk',
            ];
        }
        return array_slice($items, 0, 6);
    }

    /** @param array<string,mixed>|null $latestSync */
    private function mapBoardToProjectCard(array $board, ?array $latestSync): array
    {
        $overdue = (int)($board['overdue_tasks'] ?? 0);
        $progress = (float)($board['progress_percentage'] ?? 0.0);
        return [
            'id' => (string)($board['board_id'] ?? ''),
            'name' => (string)($board['name'] ?? 'Proyecto'),
            'status' => $this->projectStatus($overdue, $progress, (int)($board['pending_tasks'] ?? 0), (int)($board['total_tasks'] ?? 0)),
            'owner' => (string)($board['workspace_name'] ?? 'Trello'),
            'lastSync' => (string)($latestSync['finished_at'] ?? $latestSync['started_at'] ?? gmdate(DATE_ATOM)),
            'progress' => (int)round($progress),
            'tasksTotal' => (int)($board['total_tasks'] ?? 0),
            'tasksDone' => (int)($board['completed_tasks'] ?? 0),
            'tasksOverdue' => $overdue,
            'members' => [],
        ];
    }

    /** @param list<array<string,mixed>> $lists */
    private function mapBoardToProjectDetail(array $board, ?array $latestSync, array $lists): array
    {
        $project = $this->mapBoardToProjectCard($board, $latestSync);
        $project['members'] = array_map(fn (array $list): array => [
            'name' => (string)$list['name'],
            'initials' => $this->initials((string)$list['name']),
            'assigned' => (int)($list['total_tasks'] ?? 0),
        ], array_slice($lists, 0, 5));
        return $project;
    }

    /** @param list<array<string,mixed>> $lists */
    private function buildProjectActivity(array $board, ?array $latestSync, array $lists): array
    {
        $items = [];
        foreach (array_slice($lists, 0, 3) as $list) {
            $items[] = [
                'type' => 'change',
                'title' => 'Lista "' . (string)$list['name'] . '" actualizada',
                'meta' => $this->humanizeDate((string)($latestSync['finished_at'] ?? $latestSync['started_at'] ?? gmdate(DATE_ATOM))) . ' · Trello',
                'detail' => sprintf(
                    '%d tareas totales, %d completadas y %d vencidas.',
                    (int)($list['total_tasks'] ?? 0),
                    (int)($list['completed_tasks'] ?? 0),
                    (int)($list['overdue_tasks'] ?? 0)
                ),
            ];
        }
        $items[] = [
            'type' => 'comment',
            'title' => 'Resumen del proyecto "' . (string)$board['name'] . '"',
            'meta' => (string)($board['workspace_name'] ?? 'Trello'),
            'detail' => sprintf(
                'Avance %.1f%%, %d tareas pendientes y %d vencidas.',
                (float)($board['progress_percentage'] ?? 0.0),
                (int)($board['pending_tasks'] ?? 0),
                (int)($board['overdue_tasks'] ?? 0)
            ),
        ];
        return $items;
    }

    /** @return array<string,list<float>> */
    private function buildSeriesFromMetrics(int $total, int $completed, int $pending, int $overdue): array
    {
        $total = max(0, $total);
        $completed = max(0, min($total, $completed));
        $pending = max(0, min($total, $pending));
        $overdue = max(0, min($pending, $overdue));
        $stages = 6;

        $burndown = [];
        $burnup = [];
        $productivity = [];
        $velocity = [];
        $lead = [];
        $cycle = [];

        for ($i = 1; $i <= $stages; $i++) {
            $ratio = $i / $stages;
            $burndown[] = round(max($pending, $total - (($total - $pending) * $ratio)), 2);
            $burnup[] = round($completed * $ratio, 2);
            $productivity[] = round(($completed > 0 ? ($completed / max(1, $stages)) : 0) * (0.6 + ($ratio * 0.8)), 2);
            $velocity[] = round(($completed + $pending) / max(1, $stages) * (0.8 + $ratio * 0.4), 2);
            $lead[] = round(max(1, ($pending + $overdue) / max(1, $i)), 2);
            $cycle[] = round(max(0.5, ($completed + max(1, $i)) / max(1, $i * 2)), 2);
        }

        return [
            'burndown' => $burndown,
            'burnup' => $burnup,
            'productivity' => $productivity,
            'velocity' => $velocity,
            'lead' => $lead,
            'cycle' => $cycle,
        ];
    }

    /** @param list<array<string,mixed>> $boards */
    private function sortBoards(array $boards, string $key, bool $desc): array
    {
        usort($boards, static function (array $a, array $b) use ($key, $desc): int {
            $left = (float)($a[$key] ?? 0);
            $right = (float)($b[$key] ?? 0);
            return $desc ? ($right <=> $left) : ($left <=> $right);
        });
        return $boards;
    }

    private function projectStatus(int $overdue, float $progress, int $pending, int $total): string
    {
        if ($total > 0 && $progress >= 100.0) {
            return 'Completado';
        }
        if ($overdue >= 5 || $progress < 50.0) {
            return 'Riesgo';
        }
        if ($pending === 0 && $total === 0) {
            return 'En espera';
        }
        return 'En curso';
    }

    private function riskLabel(int $overdue, float $progress): string
    {
        if ($overdue >= 10 || $progress < 35.0) {
            return 'Alto';
        }
        if ($overdue >= 4 || $progress < 60.0) {
            return 'Medio';
        }
        return 'Bajo';
    }

    private function initials(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [];
        $initials = '';
        foreach (array_slice($parts, 0, 2) as $part) {
            $initials .= strtoupper(substr($part, 0, 1));
        }
        return $initials !== '' ? $initials : 'TR';
    }

    private function humanizeDate(string $iso): string
    {
        $time = strtotime($iso);
        if ($time === false) {
            return 'sin fecha';
        }
        $delta = time() - $time;
        if ($delta < 3600) {
            return 'hace ' . max(1, (int)floor($delta / 60)) . ' min';
        }
        if ($delta < 86400) {
            return 'hace ' . max(1, (int)floor($delta / 3600)) . ' h';
        }
        return 'hace ' . max(1, (int)floor($delta / 86400)) . ' d';
    }

    private function kpi(string $label, int $value, float $delta, string $trend, string $icon): array
    {
        return [
            'label' => $label,
            'value' => $value,
            'delta' => round($delta, 2),
            'trend' => $trend,
            'icon' => $icon,
        ];
    }
}
