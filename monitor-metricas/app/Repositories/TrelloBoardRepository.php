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

    public function upsert(string $userId, int $workspaceId, BoardDTO $dto): int
    {
        $stmt = $this->pdo->prepare(
            'insert into trello_boards (user_id, trello_board_id, workspace_id, name, description, url, closed, created_at, updated_at)
             values (:user_id, :tid, :wid, :name, :description, :url, :closed, now(), now())
             on conflict (user_id, trello_board_id) do update set
               workspace_id = excluded.workspace_id,
               name = excluded.name,
               description = excluded.description,
               url = excluded.url,
               closed = excluded.closed,
               updated_at = now()
             returning id'
        );
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_STR);
        $stmt->bindValue(':tid', $dto->trelloId, PDO::PARAM_STR);
        $stmt->bindValue(':wid', $workspaceId, PDO::PARAM_INT);
        $stmt->bindValue(':name', $dto->name, PDO::PARAM_STR);
        $stmt->bindValue(':description', $dto->description, $dto->description === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':url', $dto->url, $dto->url === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':closed', $dto->closed, PDO::PARAM_BOOL);
        $stmt->execute();
        $row = $stmt->fetch();
        return (int)($row['id'] ?? 0);
    }

    public function findIdByTrelloId(string $userId, string $trelloBoardId): ?int
    {
        $stmt = $this->pdo->prepare('select id from trello_boards where user_id = :user_id and trello_board_id = :tid limit 1');
        $stmt->execute(['user_id' => $userId, 'tid' => $trelloBoardId]);
        $row = $stmt->fetch();
        if (!is_array($row)) {
            return null;
        }
        return (int)($row['id'] ?? 0);
    }

    public function listByWorkspaceId(string $userId, int $workspaceId): array
    {
        $stmt = $this->pdo->prepare('select * from trello_boards where user_id = :user_id and workspace_id = :wid order by name asc');
        $stmt->execute(['user_id' => $userId, 'wid' => $workspaceId]);
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    /** @param list<string> $trelloBoardIds */
    public function markClosedNotIn(string $userId, int $workspaceId, array $trelloBoardIds): int
    {
        $trelloBoardIds = array_values(array_filter(array_map('strval', $trelloBoardIds), static fn (string $v): bool => trim($v) !== ''));

        if ($trelloBoardIds === []) {
            $stmt = $this->pdo->prepare('update trello_boards set closed = true, updated_at = now() where user_id = :user_id and workspace_id = :wid and closed = false');
            $stmt->execute(['user_id' => $userId, 'wid' => $workspaceId]);
            return $stmt->rowCount();
        }

        $placeholders = [];
        $params = ['user_id' => $userId, 'wid' => $workspaceId];
        foreach ($trelloBoardIds as $i => $id) {
            $key = 'id' . $i;
            $placeholders[] = ':' . $key;
            $params[$key] = $id;
        }

        $stmt = $this->pdo->prepare(
            'update trello_boards
             set closed = true, updated_at = now()
             where user_id = :user_id and workspace_id = :wid and closed = false and trello_board_id not in (' . implode(',', $placeholders) . ')'
        );
        $stmt->execute($params);
        return $stmt->rowCount();
    }
}
