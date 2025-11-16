-- Add multiple categories support for products
-- Create junction table for many-to-many relationship between products and categories

CREATE TABLE `product_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_product_category` (`product_id`, `category_id`),
  KEY `idx_product` (`product_id`),
  KEY `idx_category` (`category_id`),
  CONSTRAINT `product_categories_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_categories_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Migrate existing category_id data to the new junction table
INSERT INTO `product_categories` (product_id, category_id)
SELECT id, category_id FROM `products` WHERE category_id IS NOT NULL;

-- Remove the category_id column from products table (optional, but recommended for data integrity)
-- ALTER TABLE `products` DROP COLUMN `category_id`;