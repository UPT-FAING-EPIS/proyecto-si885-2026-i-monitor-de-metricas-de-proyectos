<?php
declare(strict_types=1);

namespace App\Repositories;

use App\DTOs\ListDTO;
use PDO;

final class TrelloListRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function upsert(int $boardId, ListDTO $dto): int
    {
        $stmt = $this->pdo->prepare(
            'insert into trello_lists (trello_list_id, board_id, name, closed, created_at, updated_at)
             values (:tid, :bid, :name, :closed, now(), now())
             on conflict (trello_list_id) do update set
               board_id = excluded.board_id,
               name = excluded.name,
               closed = excluded.closed,
               updated_at = now()
             returning id'
        );
        $stmt->execute([
            'tid' => $dto->trelloId,
            'bid' => $boardId,
            'name' => $dto->name,
            'closed' => $dto->closed,
        ]);
        $row = $stmt->fetch();
        return (int)($row['id'] ?? 0);
    }

    public function findIdByTrelloId(string $trelloListId): ?int
    {
        $stmt = $this->pdo->prepare('select id from trello_lists where trello_list_id = :tid limit 1');
        $stmt->execute(['tid' => $trelloListId]);
        $row = $stmt->fetch();
        if (!is_array($row)) {
            return null;
        }
        return (int)($row['id'] ?? 0);
    }

    /** @param list<string> $trelloListIds */
    public function markClosedNotIn(int $boardId, array $trelloListIds): int
    {
        $trelloListIds = array_values(array_filter(array_map('strval', $trelloListIds), static fn (string $v): bool => trim($v) !== ''));

        if ($trelloListIds === []) {
            $stmt = $this->pdo->prepare('update trello_lists set closed = true, updated_at = now() where board_id = :bid and closed = false');
            $stmt->execute(['bid' => $boardId]);
            return $stmt->rowCount();
        }

        $placeholders = [];
        $params = ['bid' => $boardId];
        foreach ($trelloListIds as $i => $id) {
            $key = 'id' . $i;
            $placeholders[] = ':' . $key;
            $params[$key] = $id;
        }

        $stmt = $this->pdo->prepare(
            'update trello_lists
             set closed = true, updated_at = now()
             where board_id = :bid and closed = false and trello_list_id not in (' . implode(',', $placeholders) . ')'
        );
        $stmt->execute($params);
        return $stmt->rowCount();
    }
}
