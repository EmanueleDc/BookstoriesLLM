<?php

require_once __DIR__ . '/../include/Database.php';

class CartDAO {
    private \PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Recupera o crea il carrello per l'utente corrente.
     */
    public function getOrCreateCart(int $userId): int {
        $sql = "SELECT id FROM carts WHERE user_id = :user_id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        $cart = $stmt->fetch();

        if ($cart) {
            return (int) $cart['id'];
        }

        $sql = "INSERT INTO carts (user_id) VALUES (:user_id)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Aggiunge un libro al carrello o incrementa la quantità se già presente.
     */
    public function addItem(int $cartId, int $bookId, int $quantity = 1): bool {
        // Se il libro è già nel carrello incrementiamo la quantità
        $checkSql = "SELECT id, quantity FROM cart_items WHERE cart_id = :cart_id AND book_id = :book_id";
        $checkStmt = $this->db->prepare($checkSql);
        $checkStmt->execute([':cart_id' => $cartId, ':book_id' => $bookId]);
        $existing = $checkStmt->fetch();

        if ($existing) {
            $updateSql = "UPDATE cart_items SET quantity = quantity + :qty WHERE id = :id";
            $updateStmt = $this->db->prepare($updateSql);
            return $updateStmt->execute([':qty' => $quantity, ':id' => $existing['id']]);
        }

        $insertSql = "INSERT INTO cart_items (cart_id, book_id, quantity) VALUES (:cart_id, :book_id, :quantity)";
        $insertStmt = $this->db->prepare($insertSql);
        return $insertStmt->execute([
            ':cart_id'  => $cartId,
            ':book_id'  => $bookId,
            ':quantity' => $quantity
        ]);
    }

    /**
     * Recupera tutti gli articoli del carrello con i dettagli del libro.
     */
    public function getCartItems(int $cartId): array {
        $sql = "SELECT ci.*, b.title, b.price, b.cover_image, b.stock
                FROM cart_items ci
                JOIN books b ON ci.book_id = b.id
                WHERE ci.cart_id = :cart_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':cart_id' => $cartId]);
        return $stmt->fetchAll();
    }

    /**
     * Aggiorna la quantità di un articolo nel carrello.
     */
    public function updateQuantity(int $cartItemId, int $quantity): bool {
        if ($quantity <= 0) {
            return $this->removeItem($cartItemId);
        }
        $sql = "UPDATE cart_items SET quantity = :quantity WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':quantity' => $quantity, ':id' => $cartItemId]);
    }

    /**
     * Rimuove un articolo dal carrello.
     */
    public function removeItem(int $cartItemId): bool {
        $sql = "DELETE FROM cart_items WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $cartItemId]);
    }

    /**
     * Svuota l'intero carrello (dopo un ordine completato).
     */
    public function clearCart(int $cartId): bool {
        $sql = "DELETE FROM cart_items WHERE cart_id = :cart_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':cart_id' => $cartId]);
    }

    /**
     * Conta il numero totale di articoli nel carrello (per il badge nella navbar).
     */
    public function countItems(int $cartId): int {
        $sql = "SELECT COALESCE(SUM(quantity), 0) FROM cart_items WHERE cart_id = :cart_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':cart_id' => $cartId]);
        return (int) $stmt->fetchColumn();
    }
}
