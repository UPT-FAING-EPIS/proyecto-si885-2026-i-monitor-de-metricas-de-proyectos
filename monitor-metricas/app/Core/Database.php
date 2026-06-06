<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

final class Database
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }

    public static function fromEnv(): self
    {
        $host = (string)($_ENV['SUPABASE_DB_HOST'] ?? $_SERVER['SUPABASE_DB_HOST'] ?? getenv('SUPABASE_DB_HOST') ?: '');
        $port = (string)($_ENV['SUPABASE_DB_PORT'] ?? $_SERVER['SUPABASE_DB_PORT'] ?? getenv('SUPABASE_DB_PORT') ?: '5432');
        $db = (string)($_ENV['SUPABASE_DB_NAME'] ?? $_SERVER['SUPABASE_DB_NAME'] ?? getenv('SUPABASE_DB_NAME') ?: 'postgres');
        $user = (string)($_ENV['SUPABASE_DB_USER'] ?? $_SERVER['SUPABASE_DB_USER'] ?? getenv('SUPABASE_DB_USER') ?: 'postgres');
        $pass = (string)($_ENV['SUPABASE_DB_PASSWORD'] ?? $_SERVER['SUPABASE_DB_PASSWORD'] ?? getenv('SUPABASE_DB_PASSWORD') ?: '');
        $sslmode = (string)($_ENV['SUPABASE_DB_SSLMODE'] ?? $_SERVER['SUPABASE_DB_SSLMODE'] ?? getenv('SUPABASE_DB_SSLMODE') ?: 'require');

        if ($host === '' || $pass === '') {
            throw new PDOException('Supabase DB no está configurado (SUPABASE_DB_HOST / SUPABASE_DB_PASSWORD).');
        }

        $dsn = 'pgsql:host=' . $host . ';port=' . $port . ';dbname=' . $db . ';sslmode=' . $sslmode;
        return new self(new PDO($dsn, $user, $pass));
    }
}

