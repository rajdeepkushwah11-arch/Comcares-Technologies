-- Run this in phpMyAdmin (XAMPP -> MySQL -> Admin -> SQL tab)
-- It creates the comcares database and the two tables the site uses.

CREATE DATABASE IF NOT EXISTS comcares
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE comcares;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    status ENUM('active','suspended','pending') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token_hash CHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_password_resets_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_password_resets_lookup (token_hash, expires_at, used_at),
    INDEX idx_password_resets_user (user_id)
);

-- Migration for databases created with the older schema (password column, no status):
-- ALTER TABLE users ADD COLUMN status ENUM('active','suspended','pending') NOT NULL DEFAULT 'active' AFTER password;
-- ALTER TABLE users CHANGE password password_hash VARCHAR(255) NOT NULL;

CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_contact_messages_created_at (created_at)
);

-- Separate admin accounts (NOT the same as regular users)
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Default admin: admin@comcares.com / admin123 (change after first login!)
INSERT INTO admins (name, email, password)
SELECT 'Comcares Admin', 'admin@comcares.com', '$2y$10$ke2Q7YJMNHFZy6nyzM5xO.kjpcn4CAhT8Q6mEhOBBXI1XSASy06YC'
WHERE NOT EXISTS (SELECT 1 FROM admins WHERE email = 'admin@comcares.com');

-- Chatbot conversation storage
CREATE TABLE IF NOT EXISTS chat_sessions (
    session_id VARCHAR(40) PRIMARY KEY,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(40) NOT NULL,
    sender ENUM('user','bot') NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (session_id)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
