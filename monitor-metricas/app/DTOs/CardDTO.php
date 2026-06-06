<?php
declare(strict_types=1);

namespace App\DTOs;

final class CardDTO
{
    public function __construct(
        public readonly string $trelloId,
        public readonly string $boardTrelloId,
        public readonly string $listTrelloId,
        public readonly string $name,
        public readonly ?string $description,
        public readonly ?string $dueDateIso,
        public readonly bool $closed,
    ) {
    }
}

