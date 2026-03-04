<?php

namespace App\Repository;

use PDO;

class UserRepository extends Repository
{
    protected string $table = 'spfh_users';

    /**
     * Busca um usuário pelo nickname
     */
    public function findByNickname(string $nickname): array|false
    {
        return $this->findBy(['nickname' => $nickname], single: true);
    }

    /**
     * Busca um usuário pelo email
     */
    public function findByEmail(string $email): array|false
    {
        return $this->findBy(['email' => $email], single: true);
    }

    /**
     * Busca um usuário pelo UUID
     */
    public function findByUuid(string $uuid): array|false
    {
        return $this->findBy(['uuid' => $uuid], single: true);
    }

    /**
     * Busca um usuário pelo ID com suas roles
     */
    public function findWithRoles(int $id): array|false
    {
        $sql = "
            SELECT 
                u.*,
                GROUP_CONCAT(r.name ORDER BY r.name ASC SEPARATOR ',') AS roles
            FROM spfh_users u
            LEFT JOIN spfh_user_roles ur ON ur.user_id = u.id
            LEFT JOIN spfh_roles r ON r.id = ur.role_id
            WHERE u.id = :id
            GROUP BY u.id
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch();

        if ($row && $row['roles']) {
            $row['roles'] = explode(',', $row['roles']);
        } else if ($row) {
            $row['roles'] = [];
        }

        return $row;
    }

    /**
     * Busca todos os usuários com suas roles
     */
    public function findAllWithRoles(): array
    {
        $sql = "
            SELECT 
                u.*,
                GROUP_CONCAT(r.name ORDER BY r.name ASC SEPARATOR ',') AS roles
            FROM spfh_users u
            LEFT JOIN spfh_user_roles ur ON ur.user_id = u.id
            LEFT JOIN spfh_roles r ON r.id = ur.role_id
            GROUP BY u.id
            ORDER BY u.created_at DESC
        ";

        $stmt = $this->pdo->query($sql);
        $rows = $stmt->fetchAll();

        return array_map(function ($row) {
            $row['roles'] = $row['roles'] ? explode(',', $row['roles']) : [];
            return $row;
        }, $rows);
    }

    /**
     * Verifica se um nickname já está em uso (opcionalmente excluindo um ID)
     */
    public function nicknameExists(string $nickname, ?int $excludeId = null): bool
    {
        if ($excludeId) {
            $sql = "SELECT 1 FROM {$this->table} WHERE nickname = :nickname AND id != :id LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['nickname' => $nickname, 'id' => $excludeId]);
            return (bool) $stmt->fetchColumn();
        }

        return $this->existsBy(['nickname' => $nickname]);
    }

    /**
     * Verifica se um email já está em uso (opcionalmente excluindo um ID)
     */
    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        if ($excludeId) {
            $sql = "SELECT 1 FROM {$this->table} WHERE email = :email AND id != :id LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['email' => $email, 'id' => $excludeId]);
            return (bool) $stmt->fetchColumn();
        }

        return $this->existsBy(['email' => $email]);
    }
}
