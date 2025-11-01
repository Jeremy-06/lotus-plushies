-- Add 'completed' status to orders table
-- Run this query in your database to update the schema

ALTER TABLE orders 
MODIFY COLUMN order_status ENUM('pending', 'processing', 'shipped', 'delivered', 'completed', 'cancelled') DEFAULT 'pending';
