<?php
declare(strict_types=1);

namespace App\DTOs;

final class WorkspaceDTO
{
    public function __construct(
        public readonly string $trelloId,
        public readonly string $name,
        public readonly ?string $description,
    ) {
    }
}

