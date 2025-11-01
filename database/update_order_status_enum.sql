-- Update order_status ENUM to simplified categories
-- This migration updates existing statuses and changes the ENUM definition

-- First, update existing orders to map to new simplified statuses
-- Map 'processing' and 'delivered' to 'shipped'
UPDATE orders SET order_status = 'shipped' 
WHERE order_status IN ('processing', 'delivered');

-- Now alter the table to use only the 4 simplified statuses
ALTER TABLE orders 
MODIFY COLUMN order_status ENUM('pending', 'shipped', 'completed', 'cancelled') DEFAULT 'pending';
