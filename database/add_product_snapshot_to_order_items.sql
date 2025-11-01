-- Add product snapshot fields to order_items table
-- This preserves product information even if the product is deleted

ALTER TABLE order_items 
ADD COLUMN product_name VARCHAR(255) NULL AFTER product_id,
ADD COLUMN product_image VARCHAR(255) NULL AFTER product_name;

-- Update existing order_items with current product information
UPDATE order_items oi
INNER JOIN products p ON oi.product_id = p.id
SET oi.product_name = p.product_name,
    oi.product_image = p.img_path;

-- Now make product_name NOT NULL since all existing records are updated
ALTER TABLE order_items 
MODIFY COLUMN product_name VARCHAR(255) NOT NULL;
