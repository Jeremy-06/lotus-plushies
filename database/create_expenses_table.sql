-- Create expenses table for tracking business expenses
CREATE TABLE IF NOT EXISTS expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    expense_date DATE NOT NULL,
    category VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    payment_method VARCHAR(50) DEFAULT 'cash',
    receipt_number VARCHAR(100),
    vendor_name VARCHAR(255),
    notes TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_expense_date (expense_date),
    INDEX idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample expense categories data
INSERT INTO expenses (expense_date, category, description, amount, payment_method, vendor_name, created_by) VALUES
('2025-10-01', 'Inventory', 'Purchase of plushie stock from supplier', 15000.00, 'bank_transfer', 'Plushie Wholesale Co.', 1),
('2025-10-05', 'Shipping', 'Shipping costs for October deliveries', 2500.00, 'cash', 'FastShip Logistics', 1),
('2025-10-10', 'Marketing', 'Social media advertising campaign', 3000.00, 'credit_card', 'Facebook Ads', 1),
('2025-10-15', 'Utilities', 'Office electricity and internet bill', 1200.00, 'bank_transfer', 'City Utilities', 1),
('2025-10-20', 'Packaging', 'Boxes, bubble wrap, and packaging materials', 800.00, 'cash', 'PackPro Supply', 1),
('2025-10-25', 'Maintenance', 'Website hosting and maintenance', 500.00, 'credit_card', 'WebHost Pro', 1);
