<?php
// debug_login.php — APAGUE APÓS O TESTE!
require_once __DIR__ . '/autoload.php';

use App\Connection\PDOConnection;

echo password_hash('sind2026PETRO!f', PASSWORD_BCRYPT);
