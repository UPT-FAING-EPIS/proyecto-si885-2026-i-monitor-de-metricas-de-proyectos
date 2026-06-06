<?php
declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

final class TrelloApiException extends RuntimeException
{
    private int $status;

    public function __construct(string $message, int $status = 0)
    {
        parent::__construct($message, $status);
        $this->status = $status;
    }

    public function status(): int
    {
        return $this->status;
    }
}

