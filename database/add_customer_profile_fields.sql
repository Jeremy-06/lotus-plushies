-- Add customer profile fields to users table
ALTER TABLE users 
ADD COLUMN first_name VARCHAR(100) NULL AFTER email,
ADD COLUMN last_name VARCHAR(100) NULL AFTER first_name,
ADD COLUMN phone VARCHAR(20) NULL AFTER last_name,
ADD COLUMN address TEXT NULL AFTER phone,
ADD COLUMN city VARCHAR(100) NULL AFTER address,
ADD COLUMN postal_code VARCHAR(20) NULL AFTER city,
ADD COLUMN country VARCHAR(100) DEFAULT 'Philippines' AFTER postal_code,
ADD COLUMN profile_picture VARCHAR(255) NULL AFTER country;
