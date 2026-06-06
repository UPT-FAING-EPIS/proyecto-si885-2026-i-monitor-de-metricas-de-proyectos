<?php
declare(strict_types=1);

namespace App\DTOs;

final class ListDTO
{
    public function __construct(
        public readonly string $trelloId,
        public readonly string $boardTrelloId,
        public readonly string $name,
        public readonly bool $closed,
    ) {
    }
}

