<?php

require_once __DIR__ . '/../include/Database.php';

class UserDAO {
    private \PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Registra un nuovo utente nel database.
     * La password deve essere già hashata prima di chiamare questo metodo.
     * 
     * @param array $data ['username', 'email', 'password', 'first_name', 'last_name']
     * @return int ID del nuovo utente
     */
    public function create(array $data): int {
        $sql = "INSERT INTO users (username, email, password, first_name, last_name) 
                VALUES (:username, :email, :password, :first_name, :last_name)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':username' => $data['username'],
            ':email' => $data['email'],
            ':password' => $data['password'], // Hash password should be passed here
            ':first_name' => $data['first_name'] ?? null,
            ':last_name' => $data['last_name'] ?? null
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Recupera un utente tramite l'username.
     * 
     * @param string $username
     * @return array|null I dati dell'utente o null se non trovato
     */
    public function getByUsername(string $username): ?array {
        $sql = "SELECT * FROM users WHERE username = :username LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':username' => $username]);
        
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Recupera un utente tramite l'email.
     * 
     * @param string $email
     * @return array|null
     */
    public function getByEmail(string $email): ?array {
        $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email]);
        
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Recupera un utente tramite l'id.
     * 
     * @param int $userId
     * @return array|null
     */
    public function getById(int $userId): ?array {
        $sql = "SELECT * FROM users WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $userId]);
        
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Verifica le credenziali di un utente.
     * 
     * @param string $username
     * @param string $password Password in chiaro
     * @return array|null I dati dell'utente se la password è corretta, null altrimenti
     */
    public function verifyCredentials(string $username, string $password): ?array {
        $user = $this->getByUsername($username);
        
        if ($user && password_verify($password, $user['password'])) {
            // Rimuoviamo la password dall'array prima di restituirlo per sicurezza
            unset($user['password']);
            return $user;
        }
        
        return null;
    }

    /**
     * Assegna un utente a un gruppo.
     * 
     * @param int $userId
     * @param int $groupId
     * @return bool
     */
    public function assignGroup(int $userId, int $groupId): bool {
        $sql = "INSERT IGNORE INTO users_groups (user_id, group_id) VALUES (:user_id, :group_id)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':user_id' => $userId,
            ':group_id' => $groupId
        ]);
    }

    /**
     * Recupera tutti i gruppi a cui appartiene un utente.
     * 
     * @param int $userId
     * @return array Lista dei gruppi
     */
    public function getUserGroups(int $userId): array {
        $sql = "SELECT g.* 
                FROM groups g
                JOIN users_groups ug ON g.id = ug.group_id
                WHERE ug.user_id = :user_id";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        
        return $stmt->fetchAll();
    }

    /**
     * Recupera tutti i servizi accessibili da un utente in base ai suoi gruppi.
     * 
     * @param int $userId
     * @return array Lista dei nomi dei servizi accessibili
     */
    public function getUserServices(int $userId): array {
        $sql = "SELECT DISTINCT s.name 
                FROM services s
                JOIN groups_services gs ON s.id = gs.service_id
                JOIN users_groups ug ON gs.group_id = ug.group_id
                WHERE ug.user_id = :user_id";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        
        return $stmt->fetchAll(PDO::FETCH_COLUMN); // Ritorna un array piatto di nomi stringa
    }

    /**
     * Get total number of users
     */
    public function getTotalUsers(): int {
        $sql = "SELECT COUNT(*) FROM users";
        $stmt = $this->db->query($sql);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Recupera tutti gli utenti.
     */
    public function getAllUsers(): array {
        $sql = "SELECT * FROM users ORDER BY created_at DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Recupera tutti i gruppi disponibili.
     */
    public function getAllGroups(): array {
        $sql = "SELECT * FROM groups ORDER BY name ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Imposta il gruppo principale di un utente (rimuove i vecchi e imposta il nuovo).
     */
    public function setPrimaryGroup(int $userId, int $groupId): bool {
        try {
            $this->db->beginTransaction();
            $delSql = "DELETE FROM users_groups WHERE user_id = :user_id";
            $delStmt = $this->db->prepare($delSql);
            $delStmt->execute([':user_id' => $userId]);

            $insSql = "INSERT INTO users_groups (user_id, group_id) VALUES (:user_id, :group_id)";
            $insStmt = $this->db->prepare($insSql);
            $insStmt->execute([':user_id' => $userId, ':group_id' => $groupId]);
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * Elimina un utente dal sistema e i suoi legami ai gruppi.
     */
    public function deleteUser(int $userId): bool {
        try {
            $sql = "DELETE FROM users WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $userId]);
        } catch (\Exception $e) {
            return false;
        }
    }
}
