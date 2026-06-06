<?php
declare(strict_types=1);

namespace App\Models;

final class TrelloBoard
{
    public function __construct(
        public readonly int $id,
        public readonly string $trelloBoardId,
        public readonly int $workspaceId,
        public readonly string $name,
        public readonly ?string $description,
        public readonly ?string $url,
        public readonly bool $closed,
    ) {
    }
}

