<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\TrelloConnection;
use PDO;

final class TrelloConnectionRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function getByUserId(string $userId): ?TrelloConnection
    {
        $stmt = $this->pdo->prepare('select * from trello_connections where user_id = :user_id limit 1');
        $stmt->execute(['user_id' => $userId]);
        $row = $stmt->fetch();
        if (!is_array($row)) {
            return null;
        }
        return $this->map($row);
    }

    public function upsertConnected(string $userId, string $trelloMemberId, string $tokenEncrypted): TrelloConnection
    {
        $stmt = $this->pdo->prepare(
            'insert into trello_connections (user_id, trello_member_id, token, status, connected_at, created_at, updated_at)
             values (:user_id, :trello_member_id, :token, :status, now(), now(), now())
             on conflict (user_id) do update set
               trello_member_id = excluded.trello_member_id,
               token = excluded.token,
               status = excluded.status,
               connected_at = now(),
               updated_at = now()
             returning *'
        );
        $stmt->execute([
            'user_id' => $userId,
            'trello_member_id' => $trelloMemberId,
            'token' => $tokenEncrypted,
            'status' => 'connected',
        ]);
        $row = $stmt->fetch();
        return $this->map(is_array($row) ? $row : []);
    }

    public function markDisconnected(string $userId, string $tokenEncryptedEmpty): void
    {
        $stmt = $this->pdo->prepare(
            'update trello_connections
             set status = :status, token = :token, updated_at = now()
             where user_id = :user_id'
        );
        $stmt->execute([
            'status' => 'disconnected',
            'token' => $tokenEncryptedEmpty,
            'user_id' => $userId,
        ]);
    }

    public function setLastSyncAt(string $userId, string $finishedAtIso): void
    {
        $stmt = $this->pdo->prepare('update trello_connections set last_sync_at = :ts, updated_at = now() where user_id = :user_id');
        $stmt->execute(['ts' => $finishedAtIso, 'user_id' => $userId]);
    }

    /** @param array<string,mixed> $row */
    private function map(array $row): TrelloConnection
    {
        return new TrelloConnection(
            (int)($row['id'] ?? 0),
            (string)($row['user_id'] ?? ''),
            isset($row['trello_member_id']) ? (string)$row['trello_member_id'] : null,
            (string)($row['token'] ?? ''),
            (string)($row['status'] ?? ''),
            isset($row['connected_at']) ? (string)$row['connected_at'] : null,
            isset($row['last_sync_at']) ? (string)$row['last_sync_at'] : null,
        );
    }
}

