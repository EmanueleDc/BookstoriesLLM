<?php

require_once __DIR__ . '/../include/Database.php';

class AddressDAO {
    private \PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create(array $data): int {
        $sql = "INSERT INTO addresses (user_id, address_line1, city, zip_code, country)
                VALUES (:user_id, :address_line1, :city, :zip_code, :country)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':user_id' => $data['user_id'],
            ':address_line1' => $data['address_line1'],
            ':city' => $data['city'],
            ':zip_code' => $data['zip_code'],
            ':country' => $data['country'] ?? 'Italia'
        ]);
        return (int) $this->db->lastInsertId();
    }
}
