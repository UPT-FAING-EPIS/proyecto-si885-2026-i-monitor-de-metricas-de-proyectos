<?php
declare(strict_types=1);

namespace App\Repositories;

use App\DTOs\WorkspaceDTO;
use PDO;

final class TrelloWorkspaceRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function upsert(string $userId, WorkspaceDTO $dto): int
    {
        $stmt = $this->pdo->prepare(
            'insert into trello_workspaces (user_id, trello_workspace_id, name, description, created_at, updated_at)
             values (:user_id, :tid, :name, :description, now(), now())
             on conflict (user_id, trello_workspace_id) do update set
               name = excluded.name,
               description = excluded.description,
               updated_at = now()
             returning id'
        );
        $stmt->execute([
            'user_id' => $userId,
            'tid' => $dto->trelloId,
            'name' => $dto->name,
            'description' => $dto->description,
        ]);
        $row = $stmt->fetch();
        return (int)($row['id'] ?? 0);
    }

    public function findIdByTrelloId(string $userId, string $trelloWorkspaceId): ?int
    {
        $stmt = $this->pdo->prepare('select id from trello_workspaces where user_id = :user_id and trello_workspace_id = :tid limit 1');
        $stmt->execute(['user_id' => $userId, 'tid' => $trelloWorkspaceId]);
        $row = $stmt->fetch();
        if (!is_array($row)) {
            return null;
        }
        return (int)($row['id'] ?? 0);
    }
}
