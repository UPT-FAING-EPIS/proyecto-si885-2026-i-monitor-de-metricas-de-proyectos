<?php
declare(strict_types=1);

namespace App\Models;

final class TrelloConnection
{
    public function __construct(
        public readonly int $id,
        public readonly string $userId,
        public readonly ?string $trelloMemberId,
        public readonly string $tokenEncrypted,
        public readonly string $status,
        public readonly ?string $connectedAt,
        public readonly ?string $lastSyncAt,
    ) {
    }
}

