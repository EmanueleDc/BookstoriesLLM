<?php

require_once __DIR__ . '/../include/Database.php';

class BookDAO {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create a new book
     */
    public function create($data) {
        $this->db->beginTransaction();
        try {
            $sql = "INSERT INTO books (title, isbn, price, stock, description, cover_image, publication_year, publisher_id)
                    VALUES (:title, :isbn, :price, :stock, :description, :cover_image, :publication_year, :publisher_id)";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':title' => $data['title'],
                ':isbn' => $data['isbn'],
                ':price' => $data['price'],
                ':stock' => $data['stock'] ?? 0,
                ':description' => $data['description'] ?? null,
                ':cover_image' => $data['cover_image'] ?? null,
                ':publication_year' => $data['publication_year'] ?? null,
                ':publisher_id' => $data['publisher_id'] ?? null
            ]);

            $bookId = $this->db->lastInsertId();

            // Sync Authors
            $this->syncAuthors($bookId, $data['author_ids'] ?? []);

            // Sync Categories
            $this->syncCategories($bookId, $data['category_ids'] ?? []);

            $this->db->commit();
            return $bookId;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Read a book by ID
     */
    public function read($id) {
        $sql = "SELECT b.*, p.name as publisher_name 
                FROM books b 
                LEFT JOIN publishers p ON b.publisher_id = p.id 
                WHERE b.id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        
        return $stmt->fetch();
    }

    /**
     * Update an existing book and its relationships
     */
    public function update($id, $data) {
        $this->db->beginTransaction();
        try {
            $sql = "UPDATE books
                    SET title = :title, isbn = :isbn, price = :price, stock = :stock,
                        description = :description, cover_image = :cover_image,
                        publication_year = :publication_year, publisher_id = :publisher_id
                    WHERE id = :id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':id' => $id,
                ':title' => $data['title'],
                ':isbn' => $data['isbn'],
                ':price' => $data['price'],
                ':stock' => $data['stock'],
                ':description' => $data['description'],
                ':cover_image' => $data['cover_image'],
                ':publication_year' => $data['publication_year'],
                ':publisher_id' => $data['publisher_id']
            ]);

            // Sync Authors
            $this->syncAuthors($id, $data['author_ids'] ?? []);

            // Sync Categories
            $this->syncCategories($id, $data['category_ids'] ?? []);

            $this->db->commit();
            return true;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            error_log($e->getMessage());
            return false;
        }
    }

    private function syncAuthors($bookId, $authorIds) {
        $this->db->prepare("DELETE FROM books_authors WHERE book_id = :id")->execute([':id' => $bookId]);
        if (!empty($authorIds)) {
            $stmt = $this->db->prepare("INSERT INTO books_authors (book_id, author_id) VALUES (:bid, :aid)");
            foreach ($authorIds as $aid) {
                $stmt->execute([':bid' => $bookId, ':aid' => $aid]);
            }
        }
    }

    private function syncCategories($bookId, $categoryIds) {
        $this->db->prepare("DELETE FROM books_categories WHERE book_id = :id")->execute([':id' => $bookId]);
        if (!empty($categoryIds)) {
            $stmt = $this->db->prepare("INSERT INTO books_categories (book_id, category_id) VALUES (:bid, :cid)");
            foreach ($categoryIds as $cid) {
                $stmt->execute([':bid' => $bookId, ':cid' => $cid]);
            }
        }
    }

    /**
     * Delete a book by ID
     */
    public function delete($id) {
        $sql = "DELETE FROM books WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Get all books (with optional limit for pagination/display)
     */
    public function getAll($limit = null, $offset = 0) {
        $sql = "SELECT b.*, p.name as publisher_name 
                FROM books b 
                LEFT JOIN publishers p ON b.publisher_id = p.id 
                ORDER BY b.created_at DESC";
        
        if ($limit !== null) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }

        $stmt = $this->db->prepare($sql);
        
        if ($limit !== null) {
            // Dobbiamo usare bindValue con PDO::PARAM_INT quando usiamo LIMIT con i prepared statements emulati disattivati
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get books by Category ID
     */
    public function getByCategory($category_id) {
        $sql = "SELECT b.* 
                FROM books b
                JOIN books_categories bc ON b.id = bc.book_id
                WHERE bc.category_id = :category_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':category_id' => $category_id]);
        return $stmt->fetchAll();
    }

    /**
     * Get books by Author ID
     */
    public function getByAuthor($author_id) {
        $sql = "SELECT b.* 
                FROM books b
                JOIN books_authors ba ON b.id = ba.book_id
                WHERE ba.author_id = :author_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':author_id' => $author_id]);
        return $stmt->fetchAll();
    }

    /**
     * Get featured/latest books
     */
    public function getFeatured($limit = 8) {
        return $this->getAll($limit, 0);
    }

    public function getLatest($limit = 4) {
        $sql = "SELECT b.*, GROUP_CONCAT(DISTINCT CONCAT(a.first_name, ' ', a.last_name) SEPARATOR ', ') AS authors
                FROM books b
                LEFT JOIN books_authors ba ON b.id = ba.book_id
                LEFT JOIN authors a ON ba.author_id = a.id
                GROUP BY b.id
                ORDER BY b.created_at DESC LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Recupera i dettagli completi di un libro (autori, categorie, editore) con una sola query.
     */
    public function getFullDetail(int $id): ?array {
        $sql = "SELECT b.*,
                    p.name AS publisher_name,
                    GROUP_CONCAT(DISTINCT CONCAT(a.first_name, ' ', a.last_name) SEPARATOR ', ') AS authors,
                    GROUP_CONCAT(DISTINCT c.name SEPARATOR ', ') AS categories
                FROM books b
                LEFT JOIN publishers p ON b.publisher_id = p.id
                LEFT JOIN books_authors ba ON b.id = ba.book_id
                LEFT JOIN authors a ON ba.author_id = a.id
                LEFT JOIN books_categories bc ON b.id = bc.book_id
                LEFT JOIN categories c ON bc.category_id = c.id
                WHERE b.id = :id
                GROUP BY b.id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $book = $stmt->fetch();
        return $book ?: null;
    }

    /**
     * Search books by title or author name, optionally filtered by category.
     */
    public function search(string $query = '', ?int $categoryId = null): array {
        $params = [];

        $sql = "SELECT DISTINCT b.*, 
                    GROUP_CONCAT(DISTINCT CONCAT(a.first_name, ' ', a.last_name) SEPARATOR ', ') AS authors
                FROM books b
                LEFT JOIN books_authors ba ON b.id = ba.book_id
                LEFT JOIN authors a ON ba.author_id = a.id
                LEFT JOIN books_categories bc ON b.id = bc.book_id";

        $wheres = [];

        if ($categoryId) {
            $wheres[] = "bc.category_id = :category_id";
            $params[':category_id'] = $categoryId;
        }

        if (!empty($query)) {
            $wheres[] = "(b.title LIKE :query OR CONCAT(a.first_name, ' ', a.last_name) LIKE :query2)";
            $params[':query'] = '%' . $query . '%';
            $params[':query2'] = '%' . $query . '%';
        }

        if (!empty($wheres)) {
            $sql .= " WHERE " . implode(" AND ", $wheres);
        }

        $sql .= " GROUP BY b.id ORDER BY b.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get total number of books
     */
    public function getTotalBooks(): int {
        $sql = "SELECT COUNT(*) FROM books";
        $stmt = $this->db->query($sql);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Get count of low-stock books.
     */
    public function getLowStockCount(int $threshold = 20): int {
        $sql = "SELECT COUNT(*) FROM books WHERE stock < :threshold";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':threshold' => $threshold]);
        return (int) $stmt->fetchColumn();
    }

    public function getAuthorIds($bookId) {
        $sql = "SELECT author_id FROM books_authors WHERE book_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $bookId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getCategoryIds($bookId) {
        $sql = "SELECT category_id FROM books_categories WHERE book_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $bookId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
