<?php

namespace App\Repository;

use App\Connection\PDOConnection;
use PDO;

abstract class Repository
{
    protected PDO $pdo;
    protected string $table;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;

        if (!isset($this->table)) {
            throw new \Exception("A propriedade \$table deve ser definida na classe filha.");
        }
    }

    /**
     * Busca todos os registros da tabela
     */
    public function findAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM {$this->table}");
        return $stmt->fetchAll();
    }

    /**
     * Busca registros por uma ou mais condições
     *
     * @param array $criteria ['coluna' => 'valor', ...]
     * @param bool $single Se true, retorna apenas o primeiro registro
     * @return array|false
     */
    public function findBy(array $criteria, array $orderBy = [], bool $single = false)
    {
        if (empty($criteria)) {
            throw new \InvalidArgumentException('O array de critérios não pode ser vazio.');
        }

        $conditions = [];
        $params = [];

        foreach ($criteria as $key => $value) {
            if (is_array($value)) {
                $placeholders = [];
                foreach ($value as $index => $item) {
                    $paramName = "{$key}_{$index}"; // Cria um nome de parâmetro único
                    $placeholders[] = ":{$paramName}";
                    $params[$paramName] = $item; // Armazena o valor no novo parâmetro
                }
                $conditions[] = "{$key} IN (" . implode(', ', $placeholders) . ")";
            } else {
                $conditions[] = "{$key} = :{$key}";
                $params[$key] = $value; // Armazena o valor
            }
        }

        $conditionString = implode(' AND ', $conditions);

        $sql = "SELECT * FROM {$this->table} WHERE $conditionString";

        // Adicionando ORDER BY se fornecido
        if (!empty($orderBy)) {
            $orderParts = [];
            foreach ($orderBy as $column => $direction) {
                $dir = strtoupper($direction);
                if (!in_array($dir, ['ASC', 'DESC'])) {
                    throw new \InvalidArgumentException("Direção inválida para ORDER BY: $dir");
                }
                $orderParts[] = "$column $dir";
            }
            $sql .= " ORDER BY " . implode(', ', $orderParts);
        }

        if ($single) {
            $sql .= " LIMIT 1";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $single ? $stmt->fetch() : $stmt->fetchAll();
    }

    /**
     * Remove um registro pelo ID
     */
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Cria um novo registro
     * @param array $data ['coluna' => 'valor', ...]
     * @return int ID do registro criado
     */
    public function create(array $data): int
    {
        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ':' . $col, $columns);

        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $this->table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Atualiza um registro existente
     * @param int $id
     * @param array $data ['coluna' => 'valor', ...]
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $columns = array_keys($data);
        $assignments = implode(', ', array_map(fn($col) => "$col = :$col", $columns));

        $sql = sprintf(
            "UPDATE %s SET %s WHERE id = :id",
            $this->table,
            $assignments
        );

        $stmt = $this->pdo->prepare($sql);

        // Adiciona o ID ao array de parâmetros
        $data['id'] = $id;

        return $stmt->execute($data);
    }

    public function existsBy(array $conditions): bool
    {
        $whereClauses = [];
        $params = [];

        foreach ($conditions as $column => $value) {
            $whereClauses[] = "{$column} = :{$column}";
            $params[$column] = $value;
        }

        $whereSql = implode(' AND ', $whereClauses);
        $sql = "SELECT 1 FROM {$this->table} WHERE {$whereSql} LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return (bool) $stmt->fetchColumn();
    }
}
