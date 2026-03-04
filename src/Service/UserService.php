<?php

namespace App\Service;

use App\Repository\UserRepository;
use App\Repository\RoleRepository;
use App\Repository\UserRoleRepository;

class UserService extends Service
{
    public function __construct(
        private UserRepository     $userRepository,
        private RoleRepository     $roleRepository,
        private UserRoleRepository $userRoleRepository,
    ) {
        parent::__construct($userRepository);
    }

    // -------------------------------------------------------------------------
    // USUÁRIOS
    // -------------------------------------------------------------------------

    /**
     * Retorna todos os usuários com suas roles
     */
    public function getAllWithRoles(): array
    {
        return $this->userRepository->findAllWithRoles();
    }

    /**
     * Retorna um usuário pelo ID com suas roles
     */
    public function getByIdWithRoles(int $id): array|false
    {
        return $this->userRepository->findWithRoles($id);
    }

    /**
     * Retorna um usuário pelo email
     */
    public function getByEmail(string $email): array|false
    {
        return $this->userRepository->findByEmail($email);
    }

    /**
     * Retorna um usuário pelo UUID
     */
    public function getByUuid(string $uuid): array|false
    {
        return $this->userRepository->findByUuid($uuid);
    }

    /**
     * Cria um novo usuário e já atribui as roles informadas
     *
     * @param array $data  ['name' => '', 'email' => '', 'password' => '', 'is_active' => true]
     * @param int[] $roleIds  IDs das roles a atribuir
     * @return int ID do usuário criado
     * @throws \InvalidArgumentException
     */
    public function createUser(array $data, array $roleIds = []): int
    {
        $this->validateUserData($data);

        if ($this->userRepository->emailExists($data['email'])) {
            throw new \InvalidArgumentException('Este e-mail já está em uso.');
        }

        $userId = $this->userRepository->create([
            'uuid'          => $this->generateUuid(),
            'name'          => trim($data['name']),
            'email'         => strtolower(trim($data['email'])),
            'password_hash' => password_hash($data['password'], PASSWORD_BCRYPT),
            'is_active'     => $data['is_active'] ?? true,
        ]);

        if (!empty($roleIds)) {
            $this->userRoleRepository->syncRoles($userId, $roleIds);
        }

        return $userId;
    }

    /**
     * Atualiza os dados de um usuário
     *
     * @param int   $id
     * @param array $data  Campos a atualizar (name, email, is_active)
     * @param int[]|null $roleIds  Se informado, sincroniza as roles
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function updateUser(int $id, array $data, ?array $roleIds = null): bool
    {
        $user = $this->userRepository->findBy(['id' => $id], single: true);

        if (!$user) {
            throw new \InvalidArgumentException('Usuário não encontrado.');
        }

        $updateData = [];

        if (!empty($data['name'])) {
            $updateData['name'] = trim($data['name']);
        }

        if (!empty($data['email'])) {
            $email = strtolower(trim($data['email']));
            if ($this->userRepository->emailExists($email, $id)) {
                throw new \InvalidArgumentException('Este e-mail já está em uso por outro usuário.');
            }
            $updateData['email'] = $email;
        }

        if (!empty($data['password'])) {
            $updateData['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        if (isset($data['is_active'])) {
            $updateData['is_active'] = (bool) $data['is_active'];
        }

        $updated = !empty($updateData)
            ? $this->userRepository->update($id, $updateData)
            : true;

        if ($roleIds !== null) {
            $this->userRoleRepository->syncRoles($id, $roleIds);
        }

        return $updated;
    }

    /**
     * Remove um usuário (as roles somem por CASCADE)
     */
    public function deleteUser(int $id): bool
    {
        $user = $this->userRepository->findBy(['id' => $id], single: true);

        if (!$user) {
            throw new \InvalidArgumentException('Usuário não encontrado.');
        }

        return $this->userRepository->delete($id);
    }

    /**
     * Ativa ou desativa um usuário
     */
    public function toggleActive(int $id): bool
    {
        $user = $this->userRepository->findBy(['id' => $id], single: true);

        if (!$user) {
            throw new \InvalidArgumentException('Usuário não encontrado.');
        }

        return $this->userRepository->update($id, [
            'is_active' => !$user['is_active'],
        ]);
    }

    // -------------------------------------------------------------------------
    // ROLES
    // -------------------------------------------------------------------------

    /**
     * Retorna todas as roles disponíveis
     */
    public function getAllRoles(): array
    {
        return $this->roleRepository->findAll();
    }

    /**
     * Retorna as roles de um usuário específico
     */
    public function getUserRoles(int $userId): array
    {
        return $this->userRoleRepository->findByUser($userId);
    }

    /**
     * Verifica se um usuário possui uma role específica
     */
    public function userHasRole(int $userId, string $roleName): bool
    {
        return $this->userRoleRepository->userHasRole($userId, $roleName);
    }

    /**
     * Atribui uma role a um usuário pelo nome da role
     */
    public function assignRoleByName(int $userId, string $roleName): int
    {
        $role = $this->roleRepository->findByName($roleName);

        if (!$role) {
            throw new \InvalidArgumentException("Role '{$roleName}' não encontrada.");
        }

        return $this->userRoleRepository->assignRole($userId, $role['id']);
    }

    /**
     * Remove uma role de um usuário pelo nome da role
     */
    public function removeRoleByName(int $userId, string $roleName): bool
    {
        $role = $this->roleRepository->findByName($roleName);

        if (!$role) {
            throw new \InvalidArgumentException("Role '{$roleName}' não encontrada.");
        }

        return $this->userRoleRepository->removeRole($userId, $role['id']);
    }

    /**
     * Sincroniza as roles de um usuário por IDs
     *
     * @param int[] $roleIds
     */
    public function syncRoles(int $userId, array $roleIds): void
    {
        $this->userRoleRepository->syncRoles($userId, $roleIds);
    }

    // -------------------------------------------------------------------------
    // HELPERS PRIVADOS
    // -------------------------------------------------------------------------

    /**
     * Valida os campos obrigatórios para criação de usuário
     */
    private function validateUserData(array $data): void
    {
        if (empty($data['name'])) {
            throw new \InvalidArgumentException('O nome é obrigatório.');
        }

        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('E-mail inválido.');
        }

        if (empty($data['password']) || strlen($data['password']) < 8) {
            throw new \InvalidArgumentException('A senha deve ter pelo menos 8 caracteres.');
        }
    }

    /**
     * Gera um UUID v4
     */
    private function generateUuid(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }
}
