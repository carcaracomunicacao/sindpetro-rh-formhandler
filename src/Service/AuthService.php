<?php

namespace App\Service;

use App\Repository\UserRepository;
use App\Repository\UserRoleRepository;

class AuthService
{
    private const SESSION_USER_KEY   = 'auth_user';
    private const REMEMBER_COOKIE    = 'spfh_remember';
    private const REMEMBER_DURATION  = 60 * 60 * 24 * 30; // 30 dias
    private const REDIRECT_AFTER_LOGIN = '/admin/dashboard';

    public function __construct(
        private UserRepository     $userRepository,
        private UserRoleRepository $userRoleRepository,
    ) {
        $this->startSession();
    }

    // -------------------------------------------------------------------------
    // LOGIN / LOGOUT
    // -------------------------------------------------------------------------

    /**
     * Tenta autenticar o usuário com nickname e senha.
     */
    public function login(string $nickname, string $password, bool $remember = false): bool
    {
        $nickname = trim($nickname);

        if (empty($nickname) || empty($password)) {
            throw new \InvalidArgumentException('Usuário e senha são obrigatórios.');
        }

        $user = $this->userRepository->findByNickname($nickname);

        if (!$user) {
            return false;
        }

        if (!(bool) $user['is_active']) {
            throw new \RuntimeException('Usuário inativo. Entre em contato com o administrador.');
        }

        if (!password_verify($password, $user['password_hash'])) {
            return false;
        }

        session_regenerate_id(true);

        $roles = $this->userRoleRepository->findByUser($user['id']);
        $roleNames = array_column($roles, 'name');

        $_SESSION[self::SESSION_USER_KEY] = [
            'id'        => $user['id'],
            'uuid'      => $user['uuid'],
            'name'      => $user['name'],
            'nickname'  => $user['nickname'],
            'email'     => $user['email'],
            'roles'     => $roleNames,
            'is_active' => $user['is_active'],
        ];

        if ($remember) {
            $this->setRememberCookie($user['id']);
        }

        return true;
    }

    /**
     * Encerra a sessão do usuário e limpa o cookie de remember me
     */
    public function logout(): void
    {
        $this->clearRememberCookie();

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }

    // -------------------------------------------------------------------------
    // SESSÃO / USUÁRIO AUTENTICADO
    // -------------------------------------------------------------------------

    public function check(): bool
    {
        if (!empty($_SESSION[self::SESSION_USER_KEY])) {
            return true;
        }

        return $this->tryLoginFromCookie();
    }

    public function user(): ?array
    {
        return $_SESSION[self::SESSION_USER_KEY] ?? null;
    }

    public function userId(): ?int
    {
        return $_SESSION[self::SESSION_USER_KEY]['id'] ?? null;
    }

    public function redirectToDashboard(): void
    {
        header('Location: ' . self::REDIRECT_AFTER_LOGIN);
        exit;
    }

    public function requireAuth(string $redirectTo = '/login'): void
    {
        if (!$this->check()) {
            header('Location: ' . $redirectTo);
            exit;
        }
    }

    public function hasRole(string $role): bool
    {
        $user = $this->user();
        if (!$user) return false;
        return in_array($role, $user['roles'], true);
    }

    public function hasAnyRole(array $roles): bool
    {
        $user = $this->user();
        if (!$user) return false;
        foreach ($roles as $role) {
            if (in_array($role, $user['roles'], true)) return true;
        }
        return false;
    }

    public function requireRole(string $role, string $redirectTo = '/admin/unauthorized.php'): void
    {
        if (!$this->hasRole($role)) {
            header('Location: ' . $redirectTo);
            exit;
        }
    }

    public function requireAnyRole(array $roles, string $redirectTo = '/admin/unauthorized.php'): void
    {
        if (!$this->hasAnyRole($roles)) {
            header('Location: ' . $redirectTo);
            exit;
        }
    }

    // -------------------------------------------------------------------------
    // REMEMBER ME
    // -------------------------------------------------------------------------

    private function setRememberCookie(int $userId): void
    {
        $token   = bin2hex(random_bytes(32));
        $expires = time() + self::REMEMBER_DURATION;

        $this->userRepository->update($userId, [
            'remember_token'         => hash('sha256', $token),
            'remember_token_expires' => date('Y-m-d H:i:s', $expires),
        ]);

        setcookie(self::REMEMBER_COOKIE, $userId . '|' . $token, [
            'expires'  => $expires,
            'path'     => '/',
            'httponly' => true,
            'secure'   => isset($_SERVER['HTTPS']),
            'samesite' => 'Lax',
        ]);
    }

    private function tryLoginFromCookie(): bool
    {
        if (empty($_COOKIE[self::REMEMBER_COOKIE])) return false;

        $parts = explode('|', $_COOKIE[self::REMEMBER_COOKIE], 2);
        if (count($parts) !== 2) {
            $this->clearRememberCookie();
            return false;
        }

        [$userId, $token] = $parts;
        $userId = (int) $userId;

        $user = $this->userRepository->findBy(['id' => $userId], single: true);

        if (!$user || !(bool) $user['is_active']) {
            $this->clearRememberCookie();
            return false;
        }

        $tokenValid   = isset($user['remember_token']) && hash_equals($user['remember_token'], hash('sha256', $token));
        $tokenExpired = !isset($user['remember_token_expires']) || strtotime($user['remember_token_expires']) < time();

        if (!$tokenValid || $tokenExpired) {
            $this->clearRememberCookie();
            return false;
        }

        session_regenerate_id(true);

        $roles     = $this->userRoleRepository->findByUser($user['id']);
        $roleNames = array_column($roles, 'name');

        $_SESSION[self::SESSION_USER_KEY] = [
            'id'        => $user['id'],
            'uuid'      => $user['uuid'],
            'name'      => $user['name'],
            'nickname'  => $user['nickname'],
            'email'     => $user['email'],
            'roles'     => $roleNames,
            'is_active' => $user['is_active'],
        ];

        $this->setRememberCookie($user['id']);

        return true;
    }

    private function clearRememberCookie(): void
    {
        if (!empty($_COOKIE[self::REMEMBER_COOKIE])) {
            $parts  = explode('|', $_COOKIE[self::REMEMBER_COOKIE], 2);
            $userId = (int) ($parts[0] ?? 0);

            if ($userId) {
                $this->userRepository->update($userId, [
                    'remember_token'         => null,
                    'remember_token_expires' => null,
                ]);
            }
        }

        setcookie(self::REMEMBER_COOKIE, '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'httponly' => true,
            'secure'   => isset($_SERVER['HTTPS']),
            'samesite' => 'Lax',
        ]);
    }

    // -------------------------------------------------------------------------
    // HELPERS
    // -------------------------------------------------------------------------

    private function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => 0,
                'path'     => '/',
                'httponly' => true,
                'secure'   => isset($_SERVER['HTTPS']),
                'samesite' => 'Lax',
            ]);
            session_start();
        }
    }
}
