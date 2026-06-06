<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class SyncLogRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function start(string $userId, string $syncType): int
    {
        $stmt = $this->pdo->prepare(
            'insert into sync_logs (user_id, sync_type, boards_processed, lists_processed, cards_processed, errors_count, started_at)
             values (:user_id, :sync_type, 0, 0, 0, 0, now())
             returning id'
        );
        $stmt->execute(['user_id' => $userId, 'sync_type' => $syncType]);
        $row = $stmt->fetch();
        return (int)($row['id'] ?? 0);
    }

    public function finish(int $logId, int $boardsProcessed, int $listsProcessed, int $cardsProcessed, int $errorsCount, string $finishedAtIso): void
    {
        $stmt = $this->pdo->prepare(
            'update sync_logs
             set boards_processed = :b, lists_processed = :l, cards_processed = :c, errors_count = :e, finished_at = :finished_at
             where id = :id'
        );
        $stmt->execute([
            'b' => $boardsProcessed,
            'l' => $listsProcessed,
            'c' => $cardsProcessed,
            'e' => $errorsCount,
            'finished_at' => $finishedAtIso,
            'id' => $logId,
        ]);
    }
}
