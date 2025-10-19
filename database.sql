-- Create the database
CREATE DATABASE IF NOT EXISTS `adwadifo_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `adwadifo_db`;

--
-- Table structure for table `users`
--
CREATE TABLE `users` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `full_name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `hashed_password` VARCHAR(255) NOT NULL,
  `phone_number` VARCHAR(20) DEFAULT NULL,
  `profile_picture` VARCHAR(255) DEFAULT '',
  `farm_name` VARCHAR(255) DEFAULT NULL,
  `bio` TEXT DEFAULT NULL,
  `location` VARCHAR(255) DEFAULT NULL,
  `user_type` ENUM('customer', 'vendor', 'admin') NOT NULL,
  `is_verified` BOOLEAN DEFAULT TRUE,
  `verification_token` VARCHAR(64) DEFAULT NULL,
  `is_approved` BOOLEAN DEFAULT TRUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `products`
--
CREATE TABLE `products` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `vendor_id` INT NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL,
  `price` DECIMAL(10, 2) NOT NULL,
  `category` ENUM('vegetable', 'fruit', 'livestock', 'other') NOT NULL,
  `image_url` VARCHAR(255) NOT NULL,
  `stock_quantity` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`vendor_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `orders`
--
CREATE TABLE `orders` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `customer_id` INT NOT NULL,
  `total_amount` DECIMAL(10, 2) NOT NULL,
  `status` ENUM('pending', 'processing', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
  `payment_method` VARCHAR(50) NOT NULL,
  `payment_status` ENUM('paid', 'unpaid') NOT NULL DEFAULT 'unpaid',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`customer_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `order_items`
--
CREATE TABLE `order_items` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `order_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `quantity` INT NOT NULL,
  `price` DECIMAL(10, 2) NOT NULL,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


--
-- Add some sample data for testing
--
-- Sample Vendor
INSERT INTO `users` (`full_name`, `email`, `hashed_password`, `phone_number`, `farm_name`, `bio`, `location`, `user_type`) VALUES
('Ama Serwaa', 'ama.s@example.com', '$2y$10$examplehashedpassword...', '0244123456', 'Serwaa Farms', 'Providing the freshest organic vegetables from the Ashanti Region.', 'Kumasi', 'vendor');

-- Sample Customer
INSERT INTO `users` (`full_name`, `email`, `hashed_password`, `user_type`) VALUES
('System Administrator', 'admin@example.com', '$2y$10$c34dmBmCchmFamdFxqDEseyKOUdx8uWdwZBeZx/RArwfVR.asbYg6', 'admin');

-- Sample Products from Vendor (vendor_id = 1)
INSERT INTO `products` (`vendor_id`, `name`, `description`, `price`, `category`, `image_url`, `stock_quantity`) VALUES
(1, 'Fresh Tomatoes', 'Juicy, red tomatoes grown organically. Perfect for stews and salads.', 15.00, 'vegetable', 'https://media.istockphoto.com/id/140453734/photo/fresh-tomatoes.jpg?s=612x612&w=0&k=20&c=b6XySPuRKF6opBf0bexh9AhkWck-c7TaoJvRdVNBgT0=', 100),
(1, 'Garden Eggs', 'Freshly harvested garden eggs, a staple in Ghanaian cuisine.', 10.00, 'vegetable', 'https://favyafricanmarket.com/cdn/shop/files/07B50FF5-9E02-4E0A-AD77-423AD9555A11.jpg?v=1724452421&width=1100', 150),
(1, 'Fresh Tomatoes', 'Juicy, red tomatoes grown organically. Perfect for stews and salads.', 15.00, 'vegetable', 'https://media.istockphoto.com/id/140453734/photo/fresh-tomatoes.jpg?s=612x612&w=0&k=20&c=b6XySPuRKF6opBf0bexh9AhkWck-c7TaoJvRdVNBgT0=', 100),
(1, 'Garden Eggs', 'Freshly harvested garden eggs, a staple in Ghanaian cuisine.', 10.00, 'vegetable', 'https://favyafricanmarket.com/cdn/shop/files/07B50FF5-9E02-4E0A-AD77-423AD9555A11.jpg?v=1724452421&width=1100', 150),
(1, 'Sweet Mangoes', 'Sweet and juicy Kent mangoes, straight from the farm.', 20.00, 'fruit', 'https://i.pinimg.com/1200x/79/c0/b5/79c0b52c5c9271b9b0358471e120a9fb.jpg', 80);
(1, 'Sweet Mangoes', 'Sweet and juicy Kent mangoes, straight from the farm.', 20.00, 'fruit', 'https://i.pinimg.com/1200x/79/c0/b5/79c0b52c5c9271b9b0358471e120a9fb.jpg', 80);


ALTER TABLE orders 
ADD COLUMN full_name VARCHAR(100),
ADD COLUMN address VARCHAR(255),
ADD COLUMN city VARCHAR(100),
ADD COLUMN order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
ADD total_price DECIMAL(10, 2) NOT NULL,
ADD user_id INT(11) NOT NULL AFTER id;
