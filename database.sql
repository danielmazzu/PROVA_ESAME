-- ============================================
-- Template Esame - Database Setup
-- ============================================
-- Eseguire questo script in phpMyAdmin o MySQL CLI
-- per creare il database e le tabelle necessarie.
-- ============================================

CREATE DATABASE IF NOT EXISTS temeplate
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE temeplate;

-- ============================================
-- Tabella: users
-- ============================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================
-- Utente admin di default
-- Username: admin
-- Password: admin123
-- ============================================
INSERT INTO users (username, email, password, role) VALUES (
    'admin',
    'admin@example.com',
    '$2y$10$yakGqvIeIBGcKYeL8XedAucrzEWYcZqp5kcFWuVOgtp6XxKKNtY2K', -- password: admin123
    'admin'
);

-- ============================================
-- Tabella: todos
-- ============================================
CREATE TABLE IF NOT EXISTS todos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    completed TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
