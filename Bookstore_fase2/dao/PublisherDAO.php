<?php

require_once __DIR__ . '/../include/Database.php';

class PublisherDAO {
    private \PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll(): array {
        $sql = "SELECT * FROM publishers ORDER BY name ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function read(int $id): ?array {
        $sql = "SELECT * FROM publishers WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $publisher = $stmt->fetch();
        return $publisher ?: null;
    }

    public function create(array $data): int {
        $sql = "INSERT INTO publishers (name, website) VALUES (:name, :website)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':name' => $data['name'],
            ':website' => $data['website'] ?? null
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $sql = "UPDATE publishers SET name = :name, website = :website WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':name' => $data['name'],
            ':website' => $data['website'] ?? null
        ]);
    }

    public function delete(int $id): bool {
        $sql = "DELETE FROM publishers WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function getTotalPublishers(): int {
        $sql = "SELECT COUNT(*) FROM publishers";
        $stmt = $this->db->query($sql);
        return (int) $stmt->fetchColumn();
    }
}
