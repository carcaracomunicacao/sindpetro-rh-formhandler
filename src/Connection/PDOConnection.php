<?php

namespace App\Connection;

use PDO;
use PDOException;

class PDOConnection {
    protected $pdo;

    public function __construct() {
        // Load .env manually (adjust path if .env is mounted elsewhere)
        $this->loadEnv(__DIR__ . '/../../.env');

        try {
            // Connect using $_ENV variables (customize DSN or options as needed)
            $this->pdo = new PDO(
                "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']};charset=utf8mb4",
                $_ENV['DB_USER'],
                $_ENV['DB_PASS'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            // Handle connection errors (customize error handling for production)
            throw new PDOException("Database connection failed: " . $e->getMessage());
        }
    }

    /**
     * Manual .env loader: Parses key=value lines, ignores # comments and empty lines.
     * Sets values in $_ENV.
     */
    private function loadEnv($filePath) {
        if (!file_exists($filePath)) {
            throw new \Exception(".env file not found at: " . $filePath);
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue; // Skip comments
            }

            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            if (!empty($key)) {
                $_ENV[$key] = $value;
            }
        }
    }

    /**
     * Get the PDO instance for queries.
     * Extend this class in repositories to inherit the connection.
     */
    public function getPDO(): PDO {
        return $this->pdo;
    }
}