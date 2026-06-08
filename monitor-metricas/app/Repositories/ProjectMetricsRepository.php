<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class ProjectMetricsRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function getSummary(): array
    {
        $sql = <<<SQL
select
  (select count(*) from trello_workspaces) as workspaces,
  (select count(*) from trello_boards where closed = false) as boards,
  (select count(*) from trello_lists where closed = false) as lists,
  count(*) as total_tasks,
  sum(case when c.closed then 1 else 0 end) as completed_tasks,
  sum(case when c.closed = false then 1 else 0 end) as pending_tasks,
  sum(case when c.closed = false and c.due_date is not null and c.due_date < now() then 1 else 0 end) as overdue_tasks
from trello_cards c
SQL;

        $stmt = $this->pdo->query($sql);
        $row = $stmt !== false ? $stmt->fetch() : false;
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

    public function getBoardBreakdown(int $limit = 6): array
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
left join trello_workspaces w on w.id = b.workspace_id
left join trello_cards c on c.board_id = b.id
where b.closed = false
group by b.id, b.trello_board_id, b.name, w.name
order by total_tasks desc, b.name asc
limit :limit
SQL
        );
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
}
