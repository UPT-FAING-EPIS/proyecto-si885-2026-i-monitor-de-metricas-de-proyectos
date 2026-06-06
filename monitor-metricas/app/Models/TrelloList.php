<?php
declare(strict_types=1);

namespace App\Models;

final class TrelloList
{
    public function __construct(
        public readonly int $id,
        public readonly string $trelloListId,
        public readonly int $boardId,
        public readonly string $name,
        public readonly bool $closed,
    ) {
    }
}

