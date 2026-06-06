<?php
declare(strict_types=1);

namespace App\Models;

final class TrelloWorkspace
{
    public function __construct(
        public readonly int $id,
        public readonly string $trelloWorkspaceId,
        public readonly string $name,
        public readonly ?string $description,
    ) {
    }
}

