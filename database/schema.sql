-- NoshNow portfolio schema
-- Only table structure is included. No original account or order data is published.

CREATE DATABASE IF NOT EXISTS `noshnow`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE `noshnow`;

CREATE TABLE `users` (
  `user_id` INT NOT NULL AUTO_INCREMENT,
  `password` VARCHAR(255) NOT NULL,
  `username` VARCHAR(255) NOT NULL,
  `user_type` ENUM('Customer', 'DeliveryPerson', 'RestaurantOwner') NOT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `uq_users_username` (`username`)
) ENGINE=InnoDB;

CREATE TABLE `customer` (
  `user_id` INT NOT NULL,
  `daily_calorie_goal` INT DEFAULT NULL,
  `customer_address` VARCHAR(255) DEFAULT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  CONSTRAINT `fk_customer_user` FOREIGN KEY (`user_id`)
    REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `deliveryperson` (
  `user_id` INT NOT NULL,
  `rating` DECIMAL(3,2) DEFAULT NULL,
  `vehicle_type` VARCHAR(50) DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  CONSTRAINT `fk_deliveryperson_user` FOREIGN KEY (`user_id`)
    REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `restaurantowner` (
  `user_id` INT NOT NULL,
  PRIMARY KEY (`user_id`),
  CONSTRAINT `fk_restaurantowner_user` FOREIGN KEY (`user_id`)
    REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `email` (
  `user_id` INT NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`user_id`, `email`),
  CONSTRAINT `fk_email_user` FOREIGN KEY (`user_id`)
    REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `cmnpaymethod` (
  `user_id` INT NOT NULL,
  `cmnPayMethod` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`user_id`, `cmnPayMethod`),
  CONSTRAINT `fk_payment_customer` FOREIGN KEY (`user_id`)
    REFERENCES `customer` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- The original course schema used the name "allegren"; it is preserved for compatibility.
CREATE TABLE `allegren` (
  `user_id` INT NOT NULL,
  `allegren` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`user_id`, `allegren`),
  CONSTRAINT `fk_allergen_customer` FOREIGN KEY (`user_id`)
    REFERENCES `customer` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `restaurant` (
  `restaurant_id` INT NOT NULL AUTO_INCREMENT,
  `restaurantAddress` VARCHAR(255) DEFAULT NULL,
  `restaurantName` VARCHAR(255) DEFAULT NULL,
  `operationTime` VARCHAR(100) DEFAULT NULL,
  `latitude` DECIMAL(10,6) DEFAULT NULL,
  `longitude` DECIMAL(10,6) DEFAULT NULL,
  PRIMARY KEY (`restaurant_id`)
) ENGINE=InnoDB;

CREATE TABLE `owns` (
  `restaurant_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  PRIMARY KEY (`restaurant_id`, `user_id`),
  CONSTRAINT `fk_owns_restaurant` FOREIGN KEY (`restaurant_id`)
    REFERENCES `restaurant` (`restaurant_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_owns_owner` FOREIGN KEY (`user_id`)
    REFERENCES `restaurantowner` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `menuitem` (
  `item_id` INT NOT NULL AUTO_INCREMENT,
  `restaurant_id` INT DEFAULT NULL,
  `price` DECIMAL(10,2) DEFAULT NULL,
  `foodName` VARCHAR(255) DEFAULT NULL,
  `calories` INT DEFAULT NULL,
  `dessert` VARCHAR(255) DEFAULT NULL,
  `drink` VARCHAR(255) DEFAULT NULL,
  `main_dish` VARCHAR(255) DEFAULT NULL,
  `food_image` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`item_id`),
  KEY `idx_menuitem_restaurant` (`restaurant_id`),
  CONSTRAINT `fk_menuitem_restaurant` FOREIGN KEY (`restaurant_id`)
    REFERENCES `restaurant` (`restaurant_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Kept because it appeared in the original ERD; seed data keeps it consistent with menuitem.restaurant_id.
CREATE TABLE `has` (
  `restaurant_id` INT NOT NULL,
  `item_id` INT NOT NULL,
  PRIMARY KEY (`restaurant_id`, `item_id`),
  UNIQUE KEY `uq_has_item` (`item_id`),
  CONSTRAINT `fk_has_restaurant` FOREIGN KEY (`restaurant_id`)
    REFERENCES `restaurant` (`restaurant_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_has_item` FOREIGN KEY (`item_id`)
    REFERENCES `menuitem` (`item_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `ingredients` (
  `item_id` INT NOT NULL,
  `ingredient` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`item_id`, `ingredient`),
  CONSTRAINT `fk_ingredients_item` FOREIGN KEY (`item_id`)
    REFERENCES `menuitem` (`item_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `category` (
  `category_id` INT NOT NULL AUTO_INCREMENT,
  `category` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `uq_category_name` (`category`)
) ENGINE=InnoDB;

CREATE TABLE `menuitem_category` (
  `item_id` INT NOT NULL,
  `category_id` INT NOT NULL,
  PRIMARY KEY (`item_id`, `category_id`),
  CONSTRAINT `fk_menuitem_category_item` FOREIGN KEY (`item_id`)
    REFERENCES `menuitem` (`item_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_menuitem_category_category` FOREIGN KEY (`category_id`)
    REFERENCES `category` (`category_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `cart` (
  `cart_id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT DEFAULT NULL,
  PRIMARY KEY (`cart_id`),
  KEY `idx_cart_customer` (`user_id`),
  CONSTRAINT `fk_cart_customer` FOREIGN KEY (`user_id`)
    REFERENCES `customer` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `records` (
  `cart_id` INT NOT NULL,
  `item_id` INT NOT NULL,
  `quantity` INT DEFAULT NULL,
  PRIMARY KEY (`cart_id`, `item_id`),
  CONSTRAINT `fk_records_cart` FOREIGN KEY (`cart_id`)
    REFERENCES `cart` (`cart_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_records_item` FOREIGN KEY (`item_id`)
    REFERENCES `menuitem` (`item_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `order` (
  `order_id` INT NOT NULL AUTO_INCREMENT,
  `weather` VARCHAR(50) DEFAULT NULL,
  `order_status` VARCHAR(50) DEFAULT NULL,
  `order_time` DATETIME DEFAULT NULL,
  `total_price` DECIMAL(10,2) DEFAULT NULL,
  `orderCustomer` VARCHAR(255) DEFAULT NULL,
  `orderPayMethod` VARCHAR(50) DEFAULT NULL,
  `total_calories` INT DEFAULT NULL,
  `user_id` INT DEFAULT NULL,
  `cart_id` INT DEFAULT NULL,
  `order_ps` TEXT DEFAULT NULL,
  `delivery_lat` DOUBLE NOT NULL,
  `delivery_lng` DOUBLE NOT NULL,
  PRIMARY KEY (`order_id`),
  UNIQUE KEY `uq_order_cart` (`cart_id`),
  KEY `idx_order_customer_status` (`user_id`, `order_status`),
  CONSTRAINT `fk_order_customer` FOREIGN KEY (`user_id`)
    REFERENCES `customer` (`user_id`),
  CONSTRAINT `fk_order_cart` FOREIGN KEY (`cart_id`)
    REFERENCES `cart` (`cart_id`)
) ENGINE=InnoDB;

CREATE TABLE `orderitem` (
  `order_id` INT NOT NULL,
  `sqNo` INT NOT NULL,
  `quantity` INT DEFAULT NULL,
  PRIMARY KEY (`order_id`, `sqNo`),
  CONSTRAINT `fk_orderitem_order` FOREIGN KEY (`order_id`)
    REFERENCES `order` (`order_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `contains` (
  `order_id` INT NOT NULL,
  `sqNo` INT NOT NULL,
  `item_id` INT NOT NULL,
  PRIMARY KEY (`order_id`, `sqNo`, `item_id`),
  KEY `idx_contains_item` (`item_id`),
  CONSTRAINT `fk_contains_orderitem` FOREIGN KEY (`order_id`, `sqNo`)
    REFERENCES `orderitem` (`order_id`, `sqNo`) ON DELETE CASCADE,
  CONSTRAINT `fk_contains_item` FOREIGN KEY (`item_id`)
    REFERENCES `menuitem` (`item_id`)
) ENGINE=InnoDB;

CREATE TABLE `delivers` (
  `order_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `deliver_status` VARCHAR(50) DEFAULT NULL,
  `deliver_time` DATETIME DEFAULT NULL,
  `deliver_location` VARCHAR(255) DEFAULT NULL,
  `current_lat` DECIMAL(10,6) DEFAULT NULL,
  `current_lng` DECIMAL(10,6) DEFAULT NULL,
  `pickup_lat` DECIMAL(10,6) DEFAULT NULL,
  `pickup_lng` DECIMAL(10,6) DEFAULT NULL,
  `dropoff_lat` DECIMAL(10,6) DEFAULT NULL,
  `dropoff_lng` DECIMAL(10,6) DEFAULT NULL,
  PRIMARY KEY (`order_id`, `user_id`),
  UNIQUE KEY `uq_delivers_order` (`order_id`),
  KEY `idx_delivers_deliveryperson` (`user_id`),
  CONSTRAINT `fk_delivers_order` FOREIGN KEY (`order_id`)
    REFERENCES `order` (`order_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_delivers_deliveryperson` FOREIGN KEY (`user_id`)
    REFERENCES `deliveryperson` (`user_id`)
) ENGINE=InnoDB;

CREATE TABLE `review` (
  `review_id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT DEFAULT NULL,
  `order_id` INT DEFAULT NULL,
  `rating` DECIMAL(3,2) DEFAULT NULL,
  `comment` TEXT DEFAULT NULL,
  PRIMARY KEY (`review_id`),
  UNIQUE KEY `uq_review_customer_order` (`user_id`, `order_id`),
  CONSTRAINT `fk_review_customer` FOREIGN KEY (`user_id`)
    REFERENCES `customer` (`user_id`),
  CONSTRAINT `fk_review_order` FOREIGN KEY (`order_id`)
    REFERENCES `order` (`order_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `refundapplication` (
  `refund_id` INT NOT NULL AUTO_INCREMENT,
  `refund_price` DECIMAL(10,2) DEFAULT NULL,
  `refundStatus` VARCHAR(50) DEFAULT NULL,
  `reason` TEXT DEFAULT NULL,
  `user_id` INT DEFAULT NULL,
  `order_id` INT DEFAULT NULL,
  PRIMARY KEY (`refund_id`),
  UNIQUE KEY `uq_refund_customer_order` (`user_id`, `order_id`),
  CONSTRAINT `fk_refund_customer` FOREIGN KEY (`user_id`)
    REFERENCES `customer` (`user_id`),
  CONSTRAINT `fk_refund_order` FOREIGN KEY (`order_id`)
    REFERENCES `order` (`order_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `requires` (
  `user_id` INT NOT NULL,
  `refund_id` INT NOT NULL,
  `apply_time` DATETIME DEFAULT NULL,
  PRIMARY KEY (`user_id`, `refund_id`),
  CONSTRAINT `fk_requires_customer` FOREIGN KEY (`user_id`)
    REFERENCES `customer` (`user_id`),
  CONSTRAINT `fk_requires_refund` FOREIGN KEY (`refund_id`)
    REFERENCES `refundapplication` (`refund_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `recommendation` (
  `user_id` INT NOT NULL,
  `item_id` INT NOT NULL,
  `score` FLOAT NOT NULL,
  PRIMARY KEY (`user_id`, `item_id`),
  CONSTRAINT `fk_recommendation_customer` FOREIGN KEY (`user_id`)
    REFERENCES `customer` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_recommendation_item` FOREIGN KEY (`item_id`)
    REFERENCES `menuitem` (`item_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `weighted_recommendation` (
  `user_id` INT NOT NULL,
  `item_id` INT NOT NULL,
  `score` FLOAT NOT NULL,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`, `item_id`),
  CONSTRAINT `fk_weighted_customer` FOREIGN KEY (`user_id`)
    REFERENCES `customer` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_weighted_item` FOREIGN KEY (`item_id`)
    REFERENCES `menuitem` (`item_id`) ON DELETE CASCADE
) ENGINE=InnoDB;
