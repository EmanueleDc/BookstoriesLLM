<?php

require_once 'config.php';

class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        
        $options = [
            // Impostiamo ERRMODE_SILENT per evitare che eccezioni PDO espongano credenziali a schermo
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_SILENT, 
            // Vogliamo risultati sotto forma di array associativi per comodità
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // Disabilitiamo le query emulate per forzare la preparazione nativa lato DB (più sicuro)
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->conn = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (\PDOException $e) {
            // Scriviamo l'errore reale nel log del server, ma mostriamo un messaggio generico all'utente
            error_log("Errore di connessione al DB: " . $e->getMessage());
            die("Errore di sistema: Servizio momentaneamente non disponibile. Riprova più tardi.");
        }
    }

    // Preveniamo la clonazione dell'istanza (requisito Singleton)
    private function __clone() {}

    // Preveniamo la deserializzazione dell'istanza (requisito Singleton)
    public function __wakeup() {
        throw new \Exception("Cannot unserialize a singleton.");
    }

    // Metodo principale per ottenere l'unica istanza consentita
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    // Metodo per estrarre l'oggetto PDO vero e proprio
    public function getConnection() {
        return $this->conn;
    }
}
