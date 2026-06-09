<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class ProjectMetricsRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function getSummary(string $userId): array
    {
        $sql = <<<SQL
select
  (select count(*) from trello_workspaces where user_id = :user_id) as workspaces,
  (select count(*) from trello_boards where user_id = :user_id and closed = false) as boards,
  (select count(*) from trello_lists where user_id = :user_id and closed = false) as lists,
  count(*) as total_tasks,
  sum(case when c.closed then 1 else 0 end) as completed_tasks,
  sum(case when c.closed = false then 1 else 0 end) as pending_tasks,
  sum(case when c.closed = false and c.due_date is not null and c.due_date < now() then 1 else 0 end) as overdue_tasks
from trello_cards c
where c.user_id = :user_id
SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        $row = $stmt->fetch();
        $totalTasks = (int)($row['total_tasks'] ?? 0);
        $completedTasks = (int)($row['completed_tasks'] ?? 0);

        return [
            'workspaces' => (int)($row['workspaces'] ?? 0),
            'boards' => (int)($row['boards'] ?? 0),
            'lists' => (int)($row['lists'] ?? 0),
            'total_tasks' => $totalTasks,
            'completed_tasks' => $completedTasks,
            'pending_tasks' => (int)($row['pending_tasks'] ?? 0),
            'overdue_tasks' => (int)($row['overdue_tasks'] ?? 0),
            'progress_percentage' => $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 2) : 0.0,
        ];
    }

    public function getBoardBreakdown(string $userId, int $limit = 6): array
    {
        $stmt = $this->pdo->prepare(
            <<<SQL
select
  b.id as board_id,
  b.trello_board_id,
  b.name,
  w.name as workspace_name,
  count(c.id) as total_tasks,
  sum(case when c.closed then 1 else 0 end) as completed_tasks,
  sum(case when c.closed = false then 1 else 0 end) as pending_tasks,
  sum(case when c.closed = false and c.due_date is not null and c.due_date < now() then 1 else 0 end) as overdue_tasks
from trello_boards b
left join trello_workspaces w on w.id = b.workspace_id and w.user_id = :user_id
left join trello_cards c on c.board_id = b.id and c.user_id = :user_id
where b.user_id = :user_id and b.closed = false
group by b.id, b.trello_board_id, b.name, w.name
order by total_tasks desc, b.name asc
limit :limit
SQL
        );
        $stmt->bindValue(':user_id', $userId);
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        if (!is_array($rows)) {
            return [];
        }

        $result = [];
        foreach ($rows as $row) {
            $totalTasks = (int)($row['total_tasks'] ?? 0);
            $completedTasks = (int)($row['completed_tasks'] ?? 0);
            $result[] = [
                'board_id' => (int)($row['board_id'] ?? 0),
                'trello_board_id' => (string)($row['trello_board_id'] ?? ''),
                'name' => (string)($row['name'] ?? ''),
                'workspace_name' => isset($row['workspace_name']) ? (string)$row['workspace_name'] : null,
                'total_tasks' => $totalTasks,
                'completed_tasks' => $completedTasks,
                'pending_tasks' => (int)($row['pending_tasks'] ?? 0),
                'overdue_tasks' => (int)($row['overdue_tasks'] ?? 0),
                'progress_percentage' => $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 2) : 0.0,
            ];
        }

        return $result;
    }

    public function getWorkspaceBreakdown(string $userId, int $limit = 8): array
    {
        $stmt = $this->pdo->prepare(
            <<<SQL
select
  w.id as workspace_id,
  w.trello_workspace_id,
  w.name,
  count(distinct b.id) as boards,
  count(c.id) as total_tasks,
  sum(case when c.closed then 1 else 0 end) as completed_tasks,
  sum(case when c.closed = false then 1 else 0 end) as pending_tasks,
  sum(case when c.closed = false and c.due_date is not null and c.due_date < now() then 1 else 0 end) as overdue_tasks
from trello_workspaces w
left join trello_boards b on b.workspace_id = w.id and b.user_id = :user_id and b.closed = false
left join trello_cards c on c.board_id = b.id and c.user_id = :user_id
where w.user_id = :user_id
group by w.id, w.trello_workspace_id, w.name
order by total_tasks desc, w.name asc
limit :limit
SQL
        );
        $stmt->bindValue(':user_id', $userId);
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        if (!is_array($rows)) {
            return [];
        }

        return array_map(static function (array $row): array {
            $totalTasks = (int)($row['total_tasks'] ?? 0);
            $completedTasks = (int)($row['completed_tasks'] ?? 0);

            return [
                'workspace_id' => (int)($row['workspace_id'] ?? 0),
                'trello_workspace_id' => (string)($row['trello_workspace_id'] ?? ''),
                'name' => (string)($row['name'] ?? ''),
                'boards' => (int)($row['boards'] ?? 0),
                'total_tasks' => $totalTasks,
                'completed_tasks' => $completedTasks,
                'pending_tasks' => (int)($row['pending_tasks'] ?? 0),
                'overdue_tasks' => (int)($row['overdue_tasks'] ?? 0),
                'progress_percentage' => $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 2) : 0.0,
            ];
        }, $rows);
    }

    public function getListBreakdownForBoard(string $userId, int $boardId): array
    {
        $stmt = $this->pdo->prepare(
            <<<SQL
select
  l.id as list_id,
  l.trello_list_id,
  l.name,
  count(c.id) as total_tasks,
  sum(case when c.closed then 1 else 0 end) as completed_tasks,
  sum(case when c.closed = false then 1 else 0 end) as pending_tasks,
  sum(case when c.closed = false and c.due_date is not null and c.due_date < now() then 1 else 0 end) as overdue_tasks
from trello_lists l
left join trello_cards c on c.list_id = l.id and c.user_id = :user_id
where l.user_id = :user_id and l.board_id = :board_id
group by l.id, l.trello_list_id, l.name
order by l.name asc
SQL
        );
        $stmt->execute(['user_id' => $userId, 'board_id' => $boardId]);
        $rows = $stmt->fetchAll();
        if (!is_array($rows)) {
            return [];
        }

        return array_map(static fn (array $row): array => [
            'list_id' => (int)($row['list_id'] ?? 0),
            'trello_list_id' => (string)($row['trello_list_id'] ?? ''),
            'name' => (string)($row['name'] ?? ''),
            'total_tasks' => (int)($row['total_tasks'] ?? 0),
            'completed_tasks' => (int)($row['completed_tasks'] ?? 0),
            'pending_tasks' => (int)($row['pending_tasks'] ?? 0),
            'overdue_tasks' => (int)($row['overdue_tasks'] ?? 0),
        ], $rows);
    }

    public function getBoardById(string $userId, int $boardId): ?array
    {
        $stmt = $this->pdo->prepare(
            <<<SQL
select
  b.id as board_id,
  b.trello_board_id,
  b.name,
  b.description,
  b.url,
  b.closed,
  w.name as workspace_name,
  count(c.id) as total_tasks,
  sum(case when c.closed then 1 else 0 end) as completed_tasks,
  sum(case when c.closed = false then 1 else 0 end) as pending_tasks,
  sum(case when c.closed = false and c.due_date is not null and c.due_date < now() then 1 else 0 end) as overdue_tasks
from trello_boards b
left join trello_workspaces w on w.id = b.workspace_id and w.user_id = :user_id
left join trello_cards c on c.board_id = b.id and c.user_id = :user_id
where b.user_id = :user_id and b.id = :board_id
group by b.id, b.trello_board_id, b.name, b.description, b.url, b.closed, w.name
limit 1
SQL
        );
        $stmt->execute(['user_id' => $userId, 'board_id' => $boardId]);
        $row = $stmt->fetch();
        if (!is_array($row)) {
            return null;
        }

        $totalTasks = (int)($row['total_tasks'] ?? 0);
        $completedTasks = (int)($row['completed_tasks'] ?? 0);

        return [
            'board_id' => (int)($row['board_id'] ?? 0),
            'trello_board_id' => (string)($row['trello_board_id'] ?? ''),
            'name' => (string)($row['name'] ?? ''),
            'description' => isset($row['description']) ? (string)$row['description'] : null,
            'url' => isset($row['url']) ? (string)$row['url'] : null,
            'closed' => (bool)($row['closed'] ?? false),
            'workspace_name' => isset($row['workspace_name']) ? (string)$row['workspace_name'] : null,
            'total_tasks' => $totalTasks,
            'completed_tasks' => $completedTasks,
            'pending_tasks' => (int)($row['pending_tasks'] ?? 0),
            'overdue_tasks' => (int)($row['overdue_tasks'] ?? 0),
            'progress_percentage' => $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 2) : 0.0,
        ];
    }

    public function getStatusDistribution(string $userId): array
    {
        $stmt = $this->pdo->prepare(
            <<<SQL
select
  case
    when lower(coalesce(l.name, '')) like '%done%' or lower(coalesce(l.name, '')) like '%hecho%' or lower(coalesce(l.name, '')) like '%complet%' then 'Done'
    when lower(coalesce(l.name, '')) like '%block%' or lower(coalesce(l.name, '')) like '%bloq%' then 'Blocked'
    when lower(coalesce(l.name, '')) like '%progress%' or lower(coalesce(l.name, '')) like '%curso%' or lower(coalesce(l.name, '')) like '%doing%' or lower(coalesce(l.name, '')) like '%desarrollo%' then 'In Progress'
    else 'To Do'
  end as label,
  count(c.id) as value
from trello_cards c
left join trello_lists l on l.id = c.list_id and l.user_id = :user_id
where c.user_id = :user_id
group by 1
SQL
        );
        $stmt->execute(['user_id' => $userId]);
        $rows = $stmt->fetchAll();
        if (!is_array($rows) || $rows === []) {
            return [];
        }

        $tones = [
            'To Do' => 'bg-slate-400',
            'In Progress' => 'bg-pm-500',
            'Blocked' => 'bg-rose-500',
            'Done' => 'bg-emerald-500',
        ];

        return array_map(static fn (array $row): array => [
            'label' => (string)($row['label'] ?? 'To Do'),
            'value' => (int)($row['value'] ?? 0),
            'color' => $tones[(string)($row['label'] ?? 'To Do')] ?? 'bg-slate-400',
            'tone' => match ((string)($row['label'] ?? 'To Do')) {
                'Done' => 'emerald',
                'Blocked' => 'rose',
                'In Progress' => 'pm',
                default => 'slate',
            },
        ], $rows);
    }

    public function getLatestSyncForUser(string $userId): ?array
    {
        $stmt = $this->pdo->prepare(
            'select sync_type, boards_processed, lists_processed, cards_processed, errors_count, started_at, finished_at
             from sync_logs
             where user_id = :user_id
             order by started_at desc
             limit 1'
        );
        $stmt->execute(['user_id' => $userId]);
        $row = $stmt->fetch();
        if (!is_array($row)) {
            return null;
        }

        return [
            'sync_type' => (string)($row['sync_type'] ?? 'all'),
            'boards_processed' => (int)($row['boards_processed'] ?? 0),
            'lists_processed' => (int)($row['lists_processed'] ?? 0),
            'cards_processed' => (int)($row['cards_processed'] ?? 0),
            'errors_count' => (int)($row['errors_count'] ?? 0),
            'started_at' => (string)($row['started_at'] ?? ''),
            'finished_at' => isset($row['finished_at']) ? (string)$row['finished_at'] : null,
        ];
    }

    public function getRecentLogsForUser(string $userId, int $limit = 5): array
    {
        $stmt = $this->pdo->prepare(
            'select sync_type, boards_processed, lists_processed, cards_processed, errors_count, started_at, finished_at
             from sync_logs
             where user_id = :user_id
             order by started_at desc
             limit :limit'
        );
        $stmt->bindValue(':user_id', $userId);
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        if (!is_array($rows)) {
            return [];
        }

        return array_map(static fn (array $row): array => [
            'sync_type' => (string)($row['sync_type'] ?? 'all'),
            'boards_processed' => (int)($row['boards_processed'] ?? 0),
            'lists_processed' => (int)($row['lists_processed'] ?? 0),
            'cards_processed' => (int)($row['cards_processed'] ?? 0),
            'errors_count' => (int)($row['errors_count'] ?? 0),
            'started_at' => (string)($row['started_at'] ?? ''),
            'finished_at' => isset($row['finished_at']) ? (string)$row['finished_at'] : null,
        ], $rows);
    }
}
