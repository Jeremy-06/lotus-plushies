<?php
// Simple script to run the database migration
require_once __DIR__ . '/src/config/Database.php';

$db = Database::getInstance();
$conn = $db->getConnection();

$sql = "
CREATE TABLE IF NOT EXISTS `product_categories` (
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
";

if ($conn->query($sql) === TRUE) {
    echo "Table product_categories created successfully.\n";

    // Migrate existing data
    $migrateSql = "INSERT IGNORE INTO `product_categories` (product_id, category_id)
                   SELECT id, category_id FROM `products` WHERE category_id IS NOT NULL";

    if ($conn->query($migrateSql) === TRUE) {
        echo "Existing data migrated successfully.\n";
        
        // Now make category_id nullable in products table
        $alterSql = "ALTER TABLE `products` MODIFY COLUMN `category_id` int(11) DEFAULT NULL";
        if ($conn->query($alterSql) === TRUE) {
            echo "Products table updated to make category_id nullable.\n";
        } else {
            echo "Error updating products table: " . $conn->error . "\n";
        }
    } else {
        echo "Error migrating data: " . $conn->error . "\n";
    }
} else {
    echo "Error creating table: " . $conn->error . "\n";
}

$conn->close();
?>