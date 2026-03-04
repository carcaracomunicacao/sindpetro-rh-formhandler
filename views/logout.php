<?php

use App\Connection\PDOConnection;
use App\Repository\UserRepository;
use App\Repository\UserRoleRepository;
use App\Service\AuthService;

require_once __DIR__ . '../../autoload.php';

$db           = (new PDOConnection())->getPDO();
$userRepo     = new UserRepository($db);
$userRoleRepo = new UserRoleRepository($db);
$auth         = new AuthService($userRepo, $userRoleRepo);

$auth->logout();

header('Location: /login');
exit;
