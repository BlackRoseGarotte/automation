<?php

declare(strict_types=1);

class Database
{
    private PDO $connection;

    public function __construct(string $path)
    {
        $directory = dirname($path);
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException('Cannot create database directory');
        }
        $this->connection = new PDO('sqlite:' . $path);
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    public function Execute(string $sql): int
    {
        return $this->connection->exec($sql);
    }

    public function Fetch(string $sql): array
    {
        return $this->connection->query($sql)->fetchAll();
    }

    private function quotedTable(string $table): string
    {
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/D', $table)) {
            throw new InvalidArgumentException('Invalid table name');
        }
        return '"' . $table . '"';
    }

    private function quotedColumns(array $data): array
    {
        if ($data === []) {
            throw new InvalidArgumentException('Data cannot be empty');
        }
        return array_map(fn($column) => $this->quotedTable((string)$column), array_keys($data));
    }

    public function Create(string $table, array $data): int
    {
        $columns = $this->quotedColumns($data);
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = 'INSERT INTO ' . $this->quotedTable($table)
            . ' (' . implode(', ', $columns) . ') VALUES (' . $placeholders . ')';
        $statement = $this->connection->prepare($sql);
        $statement->execute(array_values($data));
        return (int)$this->connection->lastInsertId();
    }

    public function Read(string $table, int $id): ?array
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM ' . $this->quotedTable($table) . ' WHERE id = ?'
        );
        $statement->execute([$id]);
        $result = $statement->fetch();
        return $result === false ? null : $result;
    }

    public function Update(string $table, int $id, array $data): int
    {
        $columns = $this->quotedColumns($data);
        $assignments = implode(', ', array_map(fn($column) => $column . ' = ?', $columns));
        $statement = $this->connection->prepare(
            'UPDATE ' . $this->quotedTable($table) . ' SET ' . $assignments . ' WHERE id = ?'
        );
        $statement->execute([...array_values($data), $id]);
        return $statement->rowCount();
    }

    public function Delete(string $table, int $id): int
    {
        $statement = $this->connection->prepare(
            'DELETE FROM ' . $this->quotedTable($table) . ' WHERE id = ?'
        );
        $statement->execute([$id]);
        return $statement->rowCount();
    }

    public function Count(string $table): int
    {
        return (int)$this->connection->query(
            'SELECT COUNT(*) FROM ' . $this->quotedTable($table)
        )->fetchColumn();
    }
}
