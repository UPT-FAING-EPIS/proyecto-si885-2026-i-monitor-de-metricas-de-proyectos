<?php
declare(strict_types=1);

namespace App\Models;

final class TrelloCard
{
    public function __construct(
        public readonly int $id,
        public readonly string $userId,
        public readonly string $trelloCardId,
        public readonly int $listId,
        public readonly int $boardId,
        public readonly string $name,
        public readonly ?string $description,
        public readonly ?string $dueDate,
        public readonly bool $closed,
    ) {
    }
}
