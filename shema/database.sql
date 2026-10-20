-- ============================================================
-- Script de création de la base de données
-- Application : Simulation de Paiement
-- ============================================================

CREATE DATABASE IF NOT EXISTS payment_simulation
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE payment_simulation;

-- ============================================================
-- Table : clients
-- ============================================================
CREATE TABLE IF NOT EXISTS clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Table : orders
-- ============================================================
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(50) NOT NULL UNIQUE,
    client_id INT NOT NULL,
    amount DECIMAL(12, 2) NOT NULL,
    status ENUM('PENDING', 'PAID', 'FAILED', 'CANCELLED') NOT NULL DEFAULT 'PENDING',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Table : payments
-- ============================================================
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id VARCHAR(100) NOT NULL UNIQUE,
    order_id INT NOT NULL,
    amount DECIMAL(12, 2) NOT NULL,
    status ENUM('PENDING', 'SUCCESS', 'FAILED') NOT NULL DEFAULT 'PENDING',
    provider VARCHAR(50) NOT NULL DEFAULT 'MOBILE_MONEY',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    paid_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Index
-- ============================================================
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_orders_client ON orders(client_id);
CREATE INDEX idx_payments_order ON payments(order_id);
CREATE INDEX idx_payments_status ON payments(status);

-- ============================================================
-- Données de démonstration
-- ============================================================
INSERT INTO clients (name, email) VALUES
    ('Amadou Diallo', 'amadou.diallo@exemple.com'),
    ('Fatou Sow', 'fatou.sow@exemple.com'),
    ('Moussa Koné', 'moussa.kone@exemple.com');

INSERT INTO orders (order_number, client_id, amount, status, created_at, updated_at) VALUES
    ('CMD-A1B2C3D4-1700000001', 1, 15000.00, 'PAID',    NOW() - INTERVAL 3 DAY, NOW() - INTERVAL 3 DAY),
    ('CMD-E5F6G7H8-1700000002', 2, 25000.00, 'PENDING', NOW() - INTERVAL 1 DAY, NOW() - INTERVAL 1 DAY),
    ('CMD-I9J0K1L2-1700000003', 3, 8500.00,  'FAILED',  NOW() - INTERVAL 2 DAY, NOW() - INTERVAL 2 DAY);

INSERT INTO payments (transaction_id, order_id, amount, status, provider, created_at, paid_at) VALUES
    ('TXN-ABCDEF123456-1700000001', 1, 15000.00, 'SUCCESS', 'MOBILE_MONEY', NOW() - INTERVAL 3 DAY, NOW() - INTERVAL 3 DAY),
    ('TXN-GHIJKL789012-1700000003', 3, 8500.00,  'FAILED',  'MOBILE_MONEY', NOW() - INTERVAL 2 DAY, NULL);