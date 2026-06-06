<?php
declare(strict_types=1);

namespace App\Repositories;

use App\DTOs\CardDTO;
use PDO;

final class TrelloCardRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function upsert(int $boardId, int $listId, CardDTO $dto): int
    {
        $due = null;
        if ($dto->dueDateIso !== null && trim($dto->dueDateIso) !== '') {
            $due = $dto->dueDateIso;
        }

        $stmt = $this->pdo->prepare(
            'insert into trello_cards (trello_card_id, list_id, board_id, name, description, due_date, closed, created_at, updated_at)
             values (:tid, :lid, :bid, :name, :description, :due_date, :closed, now(), now())
             on conflict (trello_card_id) do update set
               list_id = excluded.list_id,
               board_id = excluded.board_id,
               name = excluded.name,
               description = excluded.description,
               due_date = excluded.due_date,
               closed = excluded.closed,
               updated_at = now()
             returning id'
        );
        $stmt->execute([
            'tid' => $dto->trelloId,
            'lid' => $listId,
            'bid' => $boardId,
            'name' => $dto->name,
            'description' => $dto->description,
            'due_date' => $due,
            'closed' => $dto->closed,
        ]);
        $row = $stmt->fetch();
        return (int)($row['id'] ?? 0);
    }

    /** @param list<string> $trelloCardIds */
    public function markClosedNotIn(int $boardId, array $trelloCardIds): int
    {
        $trelloCardIds = array_values(array_filter(array_map('strval', $trelloCardIds), static fn (string $v): bool => trim($v) !== ''));

        if ($trelloCardIds === []) {
            $stmt = $this->pdo->prepare('update trello_cards set closed = true, updated_at = now() where board_id = :bid and closed = false');
            $stmt->execute(['bid' => $boardId]);
            return $stmt->rowCount();
        }

        $placeholders = [];
        $params = ['bid' => $boardId];
        foreach ($trelloCardIds as $i => $id) {
            $key = 'id' . $i;
            $placeholders[] = ':' . $key;
            $params[$key] = $id;
        }

        $stmt = $this->pdo->prepare(
            'update trello_cards
             set closed = true, updated_at = now()
             where board_id = :bid and closed = false and trello_card_id not in (' . implode(',', $placeholders) . ')'
        );
        $stmt->execute($params);
        return $stmt->rowCount();
    }
}
