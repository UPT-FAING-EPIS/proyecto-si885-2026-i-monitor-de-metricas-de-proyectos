<?php
declare(strict_types=1);

namespace App\Repositories;

use App\DTOs\BoardDTO;
use PDO;

final class TrelloBoardRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function upsert(int $workspaceId, BoardDTO $dto): int
    {
        $stmt = $this->pdo->prepare(
            'insert into trello_boards (trello_board_id, workspace_id, name, description, url, closed, created_at, updated_at)
             values (:tid, :wid, :name, :description, :url, :closed, now(), now())
             on conflict (trello_board_id) do update set
               workspace_id = excluded.workspace_id,
               name = excluded.name,
               description = excluded.description,
               url = excluded.url,
               closed = excluded.closed,
               updated_at = now()
             returning id'
        );
        $stmt->execute([
            'tid' => $dto->trelloId,
            'wid' => $workspaceId,
            'name' => $dto->name,
            'description' => $dto->description,
            'url' => $dto->url,
            'closed' => $dto->closed,
        ]);
        $row = $stmt->fetch();
        return (int)($row['id'] ?? 0);
    }

    public function findIdByTrelloId(string $trelloBoardId): ?int
    {
        $stmt = $this->pdo->prepare('select id from trello_boards where trello_board_id = :tid limit 1');
        $stmt->execute(['tid' => $trelloBoardId]);
        $row = $stmt->fetch();
        if (!is_array($row)) {
            return null;
        }
        return (int)($row['id'] ?? 0);
    }

    public function listByWorkspaceId(int $workspaceId): array
    {
        $stmt = $this->pdo->prepare('select * from trello_boards where workspace_id = :wid order by name asc');
        $stmt->execute(['wid' => $workspaceId]);
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    /** @param list<string> $trelloBoardIds */
    public function markClosedNotIn(int $workspaceId, array $trelloBoardIds): int
    {
        $trelloBoardIds = array_values(array_filter(array_map('strval', $trelloBoardIds), static fn (string $v): bool => trim($v) !== ''));

        if ($trelloBoardIds === []) {
            $stmt = $this->pdo->prepare('update trello_boards set closed = true, updated_at = now() where workspace_id = :wid and closed = false');
            $stmt->execute(['wid' => $workspaceId]);
            return $stmt->rowCount();
        }

        $placeholders = [];
        $params = ['wid' => $workspaceId];
        foreach ($trelloBoardIds as $i => $id) {
            $key = 'id' . $i;
            $placeholders[] = ':' . $key;
            $params[$key] = $id;
        }

        $stmt = $this->pdo->prepare(
            'update trello_boards
             set closed = true, updated_at = now()
             where workspace_id = :wid and closed = false and trello_board_id not in (' . implode(',', $placeholders) . ')'
        );
        $stmt->execute($params);
        return $stmt->rowCount();
    }
}
