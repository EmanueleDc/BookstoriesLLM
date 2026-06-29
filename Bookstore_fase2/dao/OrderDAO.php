<?php

require_once __DIR__ . '/../include/Database.php';

class OrderDAO {
    private \PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Crea un nuovo ordine e inserisce i relativi articoli.
     * Utilizza le transazioni SQL per garantire l'integrità dei dati (tutto o niente).
     * 
     * @param int $userId ID dell'utente
     * @param int|null $shippingAddressId ID dell'indirizzo di spedizione (opzionale)
     * @param float $totalAmount Importo totale dell'ordine
     * @param array $items Array di array associativi: [['book_id' => 1, 'quantity' => 2, 'unit_price' => 15.50], ...]
     * @return int ID dell'ordine appena creato
     * @throws \Exception Se la transazione fallisce
     */
    public function createOrder(int $userId, ?int $shippingAddressId, float $totalAmount, array $items): int {
        try {
            // Inizia la transazione (tutte le query successive o vanno a buon fine o vengono annullate)
            $this->db->beginTransaction();

            // 1. Inserisci il record dell'ordine principale
            $sqlOrder = "INSERT INTO orders (user_id, shipping_address_id, total_amount, status) 
                         VALUES (:user_id, :shipping_address_id, :total_amount, 'pending')";
            $stmtOrder = $this->db->prepare($sqlOrder);
            $stmtOrder->execute([
                ':user_id' => $userId,
                ':shipping_address_id' => $shippingAddressId,
                ':total_amount' => $totalAmount
            ]);
            
            $orderId = (int) $this->db->lastInsertId();

            // 2. Inserisci i singoli articoli (order_items) e decrementa lo stock
            $sqlItem = "INSERT INTO order_items (order_id, book_id, quantity, unit_price) 
                        VALUES (:order_id, :book_id, :quantity, :unit_price)";
            $stmtItem = $this->db->prepare($sqlItem);

            $sqlStock = "UPDATE books SET stock = stock - :quantity WHERE id = :book_id";
            $stmtStock = $this->db->prepare($sqlStock);

            foreach ($items as $item) {
                // Inserimento order_item
                $stmtItem->execute([
                    ':order_id' => $orderId,
                    ':book_id' => $item['book_id'],
                    ':quantity' => $item['quantity'],
                    ':unit_price' => $item['unit_price']
                ]);

                // Aggiornamento inventario
                $stmtStock->execute([
                    ':quantity' => $item['quantity'],
                    ':book_id' => $item['book_id']
                ]);
            }

            // Tutto è andato a buon fine, conferma i cambiamenti nel DB
            $this->db->commit();
            
            return $orderId;

        } catch (\Exception $e) {
            // Se c'è un errore, annulla tutte le modifiche fatte durante la transazione
            $this->db->rollBack();
            error_log("Errore creazione ordine: " . $e->getMessage());
            throw new \Exception("Impossibile processare l'ordine al momento.");
        }
    }

    /**
     * Recupera un ordine specifico e tutti i suoi articoli.
     * 
     * @param int $orderId
     * @return array|null Ordine con sotto-array 'items'
     */
    public function getOrderById(int $orderId): ?array {
        $sql = "SELECT * FROM orders WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $orderId]);
        
        $order = $stmt->fetch();
        if (!$order) {
            return null;
        }

        // Recupera gli articoli associati
        $sqlItems = "SELECT oi.*, b.title, b.cover_image 
                     FROM order_items oi
                     JOIN books b ON oi.book_id = b.id
                     WHERE oi.order_id = :order_id";
        $stmtItems = $this->db->prepare($sqlItems);
        $stmtItems->execute([':order_id' => $orderId]);
        
        $order['items'] = $stmtItems->fetchAll();

        return $order;
    }

    /**
     * Recupera lo storico ordini di un utente.
     * 
     * @param int $userId
     * @return array Lista degli ordini ordinati dal più recente
     */
    public function getUserOrders(int $userId): array {
        $sql = "SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        
        return $stmt->fetchAll();
    }

    /**
     * Aggiorna lo stato di spedizione/pagamento di un ordine.
     * 
     * @param int $orderId
     * @param string $status (es. 'shipped', 'delivered')
     * @return bool
     */
    public function updateStatus(int $orderId, string $status): bool {
        $sql = "UPDATE orders SET status = :status WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':status' => $status,
            ':id' => $orderId
        ]);
    }

    /**
     * Get total number of orders
     */
    public function getTotalOrders(): int {
        $sql = "SELECT COUNT(*) FROM orders";
        $stmt = $this->db->query($sql);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Recupera gli ultimi ordini.
     */
    public function getRecentOrders(int $limit = 5): array {
        $sql = "SELECT o.id, o.total_amount, o.status, o.created_at, u.username 
                FROM orders o
                JOIN users u ON o.user_id = u.id
                ORDER BY o.created_at DESC
                LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Recupera tutti gli ordini.
     */
    public function getAllOrders(): array {
        $sql = "SELECT o.*, u.username, u.email 
                FROM orders o
                JOIN users u ON o.user_id = u.id
                ORDER BY o.created_at DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Calcola i ricavi per un determinato intervallo di giorni.
     */
    public function getRevenueByPeriod(int $days): float {
        $sql = "SELECT SUM(total_amount) FROM orders
                WHERE status != 'cancelled'
                AND created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':days', $days, PDO::PARAM_INT);
        $stmt->execute();
        return (float) $stmt->fetchColumn();
    }
}
