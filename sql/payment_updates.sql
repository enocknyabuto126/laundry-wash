-- Add payment-related columns to orders table
ALTER TABLE orders
ADD COLUMN payment_status ENUM('pending', 'paid', 'failed', 'refunded') NOT NULL DEFAULT 'pending' AFTER status,
ADD COLUMN payment_method VARCHAR(50) NULL AFTER payment_status,
ADD COLUMN payment_reference VARCHAR(100) NULL AFTER payment_method,
ADD COLUMN payment_date DATETIME NULL AFTER payment_reference;

-- Create payments table
CREATE TABLE payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    payment_reference VARCHAR(100) NULL,
    status ENUM('pending', 'completed', 'failed', 'refunded') NOT NULL DEFAULT 'pending',
    transaction_id VARCHAR(100) NULL,
    payment_date DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create payment_logs table for debugging
CREATE TABLE payment_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    payment_id INT NULL,
    order_id INT NULL,
    log_type VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
    data JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE SET NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4; 