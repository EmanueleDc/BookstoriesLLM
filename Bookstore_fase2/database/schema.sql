-- Database Schema for Bookstore

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Users, Groups, and Services (Access Control)
CREATE TABLE IF NOT EXISTS `groups` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL UNIQUE,
    `description` VARCHAR(255) DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS `services` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL UNIQUE, 
    `description` VARCHAR(255) DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `first_name` VARCHAR(100) DEFAULT NULL,
    `last_name` VARCHAR(100) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `users_groups` (
    `user_id` INT NOT NULL,
    `group_id` INT NOT NULL,
    PRIMARY KEY (`user_id`, `group_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`group_id`) REFERENCES `groups`(`id`) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `groups_services` (
    `group_id` INT NOT NULL,
    `service_id` INT NOT NULL,
    PRIMARY KEY (`group_id`, `service_id`),
    FOREIGN KEY (`group_id`) REFERENCES `groups`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`service_id`) REFERENCES `services`(`id`) ON DELETE CASCADE
);


-- 2. Catalog (Books, Authors, Categories, Publishers)
CREATE TABLE IF NOT EXISTS `publishers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL,
    `website` VARCHAR(255) DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS `authors` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `first_name` VARCHAR(100) NOT NULL,
    `last_name` VARCHAR(100) NOT NULL,
    `biography` TEXT DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL UNIQUE,
    `description` TEXT DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS `books` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `isbn` VARCHAR(20) UNIQUE NOT NULL,
    `price` DECIMAL(10, 2) NOT NULL,
    `stock` INT NOT NULL DEFAULT 0,
    `description` TEXT DEFAULT NULL,
    `cover_image` VARCHAR(255) DEFAULT NULL,
    `publication_year` INT DEFAULT NULL,
    `publisher_id` INT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`publisher_id`) REFERENCES `publishers`(`id`) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS `books_authors` (
    `book_id` INT NOT NULL,
    `author_id` INT NOT NULL,
    PRIMARY KEY (`book_id`, `author_id`),
    FOREIGN KEY (`book_id`) REFERENCES `books`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`author_id`) REFERENCES `authors`(`id`) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `books_categories` (
    `book_id` INT NOT NULL,
    `category_id` INT NOT NULL,
    PRIMARY KEY (`book_id`, `category_id`),
    FOREIGN KEY (`book_id`) REFERENCES `books`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
);


-- 3. Shopping Cart and Orders
CREATE TABLE IF NOT EXISTS `addresses` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `address_line1` VARCHAR(255) NOT NULL,
    `address_line2` VARCHAR(255) DEFAULT NULL,
    `city` VARCHAR(100) NOT NULL,
    `zip_code` VARCHAR(20) NOT NULL,
    `country` VARCHAR(100) NOT NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `carts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT DEFAULT NULL,
    `session_id` VARCHAR(100) DEFAULT NULL, -- For guest carts
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `cart_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `cart_id` INT NOT NULL,
    `book_id` INT NOT NULL,
    `quantity` INT NOT NULL DEFAULT 1,
    FOREIGN KEY (`cart_id`) REFERENCES `carts`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`book_id`) REFERENCES `books`(`id`) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `orders` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `shipping_address_id` INT DEFAULT NULL,
    `total_amount` DECIMAL(10, 2) NOT NULL,
    `status` ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`shipping_address_id`) REFERENCES `addresses`(`id`) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS `order_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT NOT NULL,
    `book_id` INT NOT NULL,
    `quantity` INT NOT NULL,
    `unit_price` DECIMAL(10, 2) NOT NULL, -- Preserves price at time of purchase
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`book_id`) REFERENCES `books`(`id`) ON DELETE NO ACTION
);

-- 4. Reviews (Additional Feature)
CREATE TABLE IF NOT EXISTS `reviews` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `book_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `rating` INT NOT NULL CHECK (`rating` >= 1 AND `rating` <= 5),
    `comment` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`book_id`) REFERENCES `books`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);

SET FOREIGN_KEY_CHECKS = 1;









-- ============================================
-- INITIAL DATA (Groups, Services, Admin User)
-- ============================================

-- Insert Groups
INSERT INTO `groups` (`name`, `description`) VALUES
('admin', 'Administrator group with full access'),
('customer', 'Regular customer group');

-- Insert Services
INSERT INTO `services` (`name`, `description`) VALUES
('admin_panel', 'Access to admin dashboard'),
('manage_books', 'Permission to manage books catalog');

-- Link admin group to services
INSERT INTO `groups_services` (`group_id`, `service_id`) 
SELECT g.id, s.id FROM `groups` g, `services` s 
WHERE g.name = 'admin' AND s.name IN ('admin_panel', 'manage_books');

-- Insert Admin User
-- Username: admin | Password: admin123 (hashed with PASSWORD_DEFAULT)
INSERT INTO `users` (`username`, `email`, `password`, `first_name`, `last_name`) VALUES
('admin', 'admin@bookstore.local', '$2y$10$tMIoiAXSM5vzH5dIvzhF2.gwC/84s9wq2jX17MfClJVmyFLid614W', 'Admin', 'User');

-- Link admin user to admin group
INSERT INTO `users_groups` (`user_id`, `group_id`)
SELECT u.id, g.id FROM `users` u, `groups` g
WHERE u.username = 'admin' AND g.name = 'admin';

-- Insert Sample Categories
INSERT INTO `categories` (`name`, `description`) VALUES
('Fiction', 'Narrative fiction and novels'),
('Non-Fiction', 'Educational and informational books'),
('Science Fiction', 'Science fiction and fantasy'),
('Biography', 'Biographies and memoirs'),
('Technology', 'Books about technology and programming');

-- Insert Sample Publishers
INSERT INTO `publishers` (`name`, `website`) VALUES
('Penguin Books', 'www.penguin.com'),
('HarperCollins', 'www.harpercollins.com'),
('Random House', 'www.randomhouse.com');

-- Insert Sample Authors
INSERT INTO `authors` (`first_name`, `last_name`, `biography`) VALUES
('J.K.', 'Rowling', 'British author, best known for Harry Potter series'),
('George', 'Orwell', 'English writer and journalist'),
('Jane', 'Austen', 'English novelist known for romantic fiction');

-- Insert Sample Books (you can add more later)
INSERT INTO `books` (`title`, `isbn`, `price`, `stock`, `description`, `publication_year`, `publisher_id`) VALUES
('Harry Potter and the Philosopher\'s Stone', '978-0747532699', 19.99, 10, 'The first book in the Harry Potter series', 1997, 1),
('1984', '978-0451524935', 14.99, 8, 'A dystopian novel set in a totalitarian state', 1949, 2),
('Pride and Prejudice', '978-0141439518', 9.99, 12, 'A romantic novel of manners', 1813, 1);

-- Link books to authors
INSERT INTO `books_authors` (`book_id`, `author_id`)
SELECT b.id, a.id FROM `books` b, `authors` a
WHERE (b.title LIKE 'Harry%' AND a.last_name = 'Rowling')
   OR (b.title = '1984' AND a.last_name = 'Orwell')
   OR (b.title = 'Pride and Prejudice' AND a.last_name = 'Austen');

-- Link books to categories
INSERT INTO `books_categories` (`book_id`, `category_id`)
SELECT b.id, c.id FROM `books` b, `categories` c
WHERE (b.title LIKE 'Harry%' AND c.name = 'Fiction')
   OR (b.title = '1984' AND c.name = 'Fiction')
   OR (b.title = 'Pride and Prejudice' AND c.name = 'Fiction');
