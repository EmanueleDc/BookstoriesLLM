<?php

require_once __DIR__ . '/../include/Database.php';

class AuthorDAO {
    private \PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll(): array {
        $sql = "SELECT * FROM authors ORDER BY last_name ASC, first_name ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function read(int $id): ?array {
        $sql = "SELECT * FROM authors WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $author = $stmt->fetch();
        return $author ?: null;
    }

    public function create(array $data): int {
        $sql = "INSERT INTO authors (first_name, last_name, biography) VALUES (:first_name, :last_name, :biography)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':first_name' => $data['first_name'],
            ':last_name' => $data['last_name'],
            ':biography' => $data['biography'] ?? null
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $sql = "UPDATE authors SET first_name = :first_name, last_name = :last_name, biography = :biography WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':first_name' => $data['first_name'],
            ':last_name' => $data['last_name'],
            ':biography' => $data['biography'] ?? null
        ]);
    }

    public function delete(int $id): bool {
        $sql = "DELETE FROM authors WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function getTotalAuthors(): int {
        $sql = "SELECT COUNT(*) FROM authors";
        $stmt = $this->db->query($sql);
        return (int) $stmt->fetchColumn();
    }
}
