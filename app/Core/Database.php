<?php

namespace App\Core;

use PDO;
use PDOStatement;

class Database {
    private static ?Database $instance = null;
    private PDO $pdo;

    private function __construct() {
        $cfg = require ROOT_PATH . '/config/database.php';
        $dsn = "mysql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['dbname']};charset={$cfg['charset']}";
        $this->pdo = new PDO($dsn, $cfg['username'], $cfg['password'], $cfg['options']);
    }

    public static function getInstance(): static {
        if (self::$instance === null) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    public function query(string $sql, array $params = []): PDOStatement {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function fetch(string $sql, array $params = []): array|false {
        return $this->query($sql, $params)->fetch();
    }

    public function fetchAll(string $sql, array $params = []): array {
        return $this->query($sql, $params)->fetchAll();
    }

    public function insert(string $table, array $data): string|false {
        $cols = implode(', ', array_map(fn($k) => "`$k`", array_keys($data)));
        $vals = implode(', ', array_map(fn($k) => ":$k", array_keys($data)));
        $this->query("INSERT INTO `$table` ($cols) VALUES ($vals)", $data);
        return $this->pdo->lastInsertId();
    }

    public function update(string $table, array $data, string $where, array $whereParams = []): int {
        $keys = array_keys($data);
        $set = implode(', ', array_map(fn($k) => "`$k` = ?", $keys));
        $params = array_merge(array_values($data), array_values($whereParams));
        return $this->query("UPDATE `$table` SET $set WHERE $where", $params)->rowCount();
    }

    public function delete(string $table, string $where, array $params = []): int {
        return $this->query("DELETE FROM `$table` WHERE $where", $params)->rowCount();
    }

    public function count(string $sql, array $params = []): int {
        return (int) $this->query($sql, $params)->fetchColumn();
    }

    public function beginTransaction(): void { $this->pdo->beginTransaction(); }
    public function commit(): void { $this->pdo->commit(); }
    public function rollback(): void { $this->pdo->rollBack(); }

    public function paginate(string $sql, array $params, int $page, int $perPage = 15): array {
        $total = $this->count("SELECT COUNT(*) FROM ($sql) AS t", $params);
        $offset = ($page - 1) * $perPage;
        $items  = $this->fetchAll("$sql LIMIT $perPage OFFSET $offset", $params);
        return [
            'data'         => $items,
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => (int) ceil($total / $perPage),
        ];
    }
}
