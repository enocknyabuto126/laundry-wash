-- Add missing columns to orders table
ALTER TABLE orders
ADD COLUMN phone VARCHAR(20) NULL AFTER address,
ADD COLUMN delivery_date DATETIME NULL AFTER phone,
ADD COLUMN notes TEXT NULL AFTER expected_delivery,
ADD COLUMN payment_status ENUM('pending', 'paid', 'failed', 'refunded') NOT NULL DEFAULT 'pending' AFTER status,
ADD COLUMN payment_reference VARCHAR(100) NULL AFTER payment_status,
ADD COLUMN payment_date DATETIME NULL AFTER payment_reference;

-- Update order_items table to use service_id instead of product_id
ALTER TABLE order_items
CHANGE COLUMN product_id service_id INT NOT NULL,
DROP FOREIGN KEY order_items_ibfk_2,
ADD CONSTRAINT order_items_ibfk_2 FOREIGN KEY (service_id) REFERENCES products(id); 