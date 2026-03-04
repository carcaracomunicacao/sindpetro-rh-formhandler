<?php

namespace App\Repository;

class RoleRepository extends Repository
{
    protected string $table = 'spfh_roles';

    /**
     * Busca uma role pelo nome
     */
    public function findByName(string $name): array|false
    {
        return $this->findBy(['name' => $name], single: true);
    }
}
