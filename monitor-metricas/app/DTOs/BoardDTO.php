<?php
declare(strict_types=1);

namespace App\DTOs;

final class BoardDTO
{
    public function __construct(
        public readonly string $trelloId,
        public readonly string $workspaceTrelloId,
        public readonly string $name,
        public readonly ?string $description,
        public readonly ?string $url,
        public readonly bool $closed,
    ) {
    }
}

