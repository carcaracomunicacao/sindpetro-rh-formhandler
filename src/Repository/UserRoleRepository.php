<?php

namespace App\Repository;

class UserRoleRepository extends Repository
{
    protected string $table = 'spfh_user_roles';

    /**
     * Busca todas as roles de um usuário
     */
    public function findByUser(int $userId): array
    {
        $sql = "
            SELECT r.id, r.name, r.description
            FROM spfh_user_roles ur
            INNER JOIN spfh_roles r ON r.id = ur.role_id
            WHERE ur.user_id = :user_id
            ORDER BY r.name ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll();
    }

    /**
     * Verifica se o usuário possui uma role específica
     */
    public function userHasRole(int $userId, string $roleName): bool
    {
        $sql = "
            SELECT 1
            FROM spfh_user_roles ur
            INNER JOIN spfh_roles r ON r.id = ur.role_id
            WHERE ur.user_id = :user_id AND r.name = :role_name
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['user_id' => $userId, 'role_name' => $roleName]);

        return (bool) $stmt->fetchColumn();
    }

    /**
     * Atribui uma role a um usuário (evita duplicata pela UNIQUE KEY do banco)
     */
    public function assignRole(int $userId, int $roleId): int
    {
        return $this->create([
            'user_id' => $userId,
            'role_id' => $roleId,
        ]);
    }

    /**
     * Remove uma role específica de um usuário
     */
    public function removeRole(int $userId, int $roleId): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE user_id = :user_id AND role_id = :role_id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['user_id' => $userId, 'role_id' => $roleId]);
    }

    /**
     * Remove todas as roles de um usuário
     */
    public function removeAllRoles(int $userId): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE user_id = :user_id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['user_id' => $userId]);
    }

    /**
     * Sincroniza as roles de um usuário (remove todas e reinsere)
     * @param int $userId
     * @param int[] $roleIds
     */
    public function syncRoles(int $userId, array $roleIds): void
    {
        $this->removeAllRoles($userId);

        foreach ($roleIds as $roleId) {
            $this->assignRole($userId, (int) $roleId);
        }
    }
}
