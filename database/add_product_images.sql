-- Migration to add product_images table for multiple product images
-- Run this SQL script in your database

-- Create product_images table
CREATE TABLE IF NOT EXISTS `product_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_primary` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_product` (`product_id`),
  KEY `idx_display_order` (`display_order`),
  CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Migrate existing product images to the new table
INSERT INTO `product_images` (`product_id`, `image_path`, `display_order`, `is_primary`)
SELECT `id`, `img_path`, 0, 1
FROM `products`
WHERE `img_path` IS NOT NULL AND `img_path` != '';

-- Note: The img_path column in products table is kept for backward compatibility
-- You can optionally drop it later after ensuring everything works:
-- ALTER TABLE `products` DROP COLUMN `img_path`;
