<?php

namespace App\Service;

use App\Repository\Repository;

abstract class Service
{
    protected Repository $repository;

    public function __construct(
        Repository $repository,
        )
    {
        $this->repository = $repository;
    }

    /**
     * Retorna todos os registros
     */
    public function getAll(): array
    {
        return $this->repository->findAll();
    }

    /**
     * Busca um registro por ID
     */
    public function getById(int $id): ?array
    {
        return $this->repository->findBy(['id'=>$id]);
    }

    /**
     * Busca registros por qualquer critério
     *
     * @param array $criteria ['coluna' => 'valor', ...]
     * @param bool $single Se true, retorna apenas o primeiro registro
     * @return array|null
     */
    public function getBy(array $criteria, array $orderBy = [], bool $single = false)
    {
        return $this->repository->findBy($criteria, $orderBy, $single);
    }

    /**
     * Cria um novo registro
     */
    public function create(array $data): int
    {
        // Aqui você pode fazer validações antes de criar
        return $this->repository->create($data);
    }

    /**
     * Atualiza um registro existente
     */
    public function update(int $id, array $data): bool
    {
        // Aqui você pode fazer validações antes de atualizar
        return $this->repository->update($id, $data);
    }

    /**
     * Remove um registro existente
     */
    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
