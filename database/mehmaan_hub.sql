-- Mehmaan Hub Database Schema
-- Created for XAMPP/MySQL

CREATE DATABASE IF NOT EXISTS mehmaan_hub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mehmaan_hub;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    role ENUM('tenant', 'owner', 'admin') DEFAULT 'tenant',
    avatar VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Properties table
CREATE TABLE IF NOT EXISTS properties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    owner_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    property_type ENUM('apartment', 'house', 'room', 'studio', 'villa') DEFAULT 'apartment',
    address VARCHAR(255),
    city VARCHAR(100),
    area VARCHAR(100),
    price DECIMAL(10,2) NOT NULL,
    price_period ENUM('per_day','per_month','both') NOT NULL DEFAULT 'per_month',
    price_per_day DECIMAL(10,2) DEFAULT NULL,
    bedrooms INT DEFAULT 1,
    bathrooms INT DEFAULT 1,
    area_sqft INT,
    is_furnished TINYINT(1) DEFAULT 0,
    has_parking TINYINT(1) DEFAULT 0,
    has_wifi TINYINT(1) DEFAULT 0,
    has_ac TINYINT(1) DEFAULT 0,
    has_generator TINYINT(1) DEFAULT 0,
    status ENUM('available', 'rented', 'inactive') DEFAULT 'available',
    featured TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Property images table
CREATE TABLE IF NOT EXISTS property_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    is_primary TINYINT(1) DEFAULT 0,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
);

-- Bookings table
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    tenant_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
    payment_status ENUM('unpaid', 'paid', 'refunded') DEFAULT 'unpaid',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    FOREIGN KEY (tenant_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Wishlist table
CREATE TABLE IF NOT EXISTS wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    property_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_wishlist (user_id, property_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
);

-- Reviews table
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Contact messages
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    subject VARCHAR(200),
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =============================================
-- SAMPLE DATA
-- =============================================

-- Admin user (password: admin123)
INSERT IGNORE INTO users (id, name, email, password, role) VALUES
(1, 'Admin', 'admin@mehmaanhub.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Owner 1 (password: owner123)
INSERT IGNORE INTO users (id, name, email, password, phone, role) VALUES
(2, 'Ahmed Khan', 'owner@mehmaanhub.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '03001234567', 'owner');

-- Owner 2 (password: owner123)
INSERT IGNORE INTO users (id, name, email, password, phone, role) VALUES
(3, 'Bilal Raza', 'bilal@mehmaanhub.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '03019876543', 'owner');

-- Owner 3 (password: owner123)
INSERT IGNORE INTO users (id, name, email, password, phone, role) VALUES
(4, 'Fatima Sheikh', 'fatima@mehmaanhub.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '03214567890', 'owner');

-- Tenant (password: tenant123)
INSERT IGNORE INTO users (id, name, email, password, phone, role) VALUES
(5, 'Sara Ali', 'tenant@mehmaanhub.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '03009876543', 'tenant');

-- =============================================
-- PROPERTIES
-- =============================================

-- Property 1: Luxury Apartment by Ahmed Khan
INSERT IGNORE INTO properties (id, owner_id, title, description, property_type, address, city, area, price, price_period, price_per_day, bedrooms, bathrooms, area_sqft, is_furnished, has_parking, has_wifi, has_ac, has_generator, status, featured) VALUES
(1, 2, 'Luxury 2 Bed Apartment in Gulberg', 'Beautifully furnished apartment in the heart of Gulberg. Spacious living room, modern kitchen, and balcony with city view. Close to restaurants, shopping malls, and public transport. 24/7 security and backup generator.', 'apartment', 'Main Boulevard, Gulberg III', 'Lahore', 'Gulberg', 65000.00, 'per_month', NULL, 2, 2, 1200, 1, 1, 1, 1, 1, 'available', 1);

-- Property 2: Family House by Ahmed Khan
INSERT IGNORE INTO properties (id, owner_id, title, description, property_type, address, city, area, price, price_period, price_per_day, bedrooms, bathrooms, area_sqft, is_furnished, has_parking, has_wifi, has_ac, has_generator, status, featured) VALUES
(2, 2, 'Spacious 3 Bed House in DHA Phase 5', 'A beautiful family house in DHA Phase 5 with lush green garden, car porch, and modern amenities. Located in a peaceful and secure neighborhood. Close to schools, parks, and commercial area.', 'house', 'Block H, DHA Phase 5', 'Lahore', 'DHA', 120000.00, 'per_month', NULL, 3, 3, 2500, 0, 1, 1, 1, 1, 'available', 1);

-- Property 3: Studio Apartment by Bilal Raza
INSERT IGNORE INTO properties (id, owner_id, title, description, property_type, address, city, area, price, price_period, price_per_day, bedrooms, bathrooms, area_sqft, is_furnished, has_parking, has_wifi, has_ac, has_generator, status, featured) VALUES
(3, 3, 'Modern Studio Apartment in Bahria Town', 'Compact and modern studio apartment perfect for singles or couples. Fully furnished with modern appliances, located in Bahria Town with access to all amenities including gym, swimming pool, and shopping center.', 'studio', 'Sector C, Bahria Town', 'Rawalpindi', 'Bahria Town', 35000.00, 'both', 2000.00, 1, 1, 600, 1, 1, 1, 1, 0, 'available', 0);

-- Property 4: Villa by Bilal Raza
INSERT IGNORE INTO properties (id, owner_id, title, description, property_type, address, city, area, price, price_period, price_per_day, bedrooms, bathrooms, area_sqft, is_furnished, has_parking, has_wifi, has_ac, has_generator, status, featured) VALUES
(4, 3, '5 Bed Luxury Villa in DHA Phase 6', 'Stunning luxury villa with 5 bedrooms, private swimming pool, landscaped garden, and double car garage. Premium location in DHA Phase 6 with easy access to main roads and commercial areas.', 'villa', 'Block B, DHA Phase 6', 'Lahore', 'DHA', 250000.00, 'per_month', NULL, 5, 5, 5000, 1, 1, 1, 1, 1, 'available', 1);

-- Property 5: Single Room by Fatima Sheikh
INSERT IGNORE INTO properties (id, owner_id, title, description, property_type, address, city, area, price, price_period, price_per_day, bedrooms, bathrooms, area_sqft, is_furnished, has_parking, has_wifi, has_ac, has_generator, status, featured) VALUES
(5, 4, 'Furnished Single Room in Johar Town', 'Clean and furnished single room available for rent in Johar Town. Shared kitchen and bathroom. Ideal for students or working professionals. Close to universities and public transport.', 'room', 'Block A2, Johar Town', 'Lahore', 'Johar Town', 15000.00, 'per_day', NULL, 1, 1, 200, 1, 0, 1, 1, 0, 'available', 0);

-- Property 6: Apartment by Fatima Sheikh
INSERT IGNORE INTO properties (id, owner_id, title, description, property_type, address, city, area, price, price_period, price_per_day, bedrooms, bathrooms, area_sqft, is_furnished, has_parking, has_wifi, has_ac, has_generator, status, featured) VALUES
(6, 4, '2 Bed Apartment in Model Town', 'Well-maintained 2 bedroom apartment in Model Town with spacious rooms, attached bathrooms, and a modern kitchen. Balcony with garden view. Walking distance to Model Town Park and commercial market.', 'apartment', 'Block B, Model Town', 'Lahore', 'Model Town', 45000.00, 'per_month', NULL, 2, 2, 1100, 0, 1, 1, 1, 0, 'available', 0);

-- Property 7: House by Ahmed Khan
INSERT IGNORE INTO properties (id, owner_id, title, description, property_type, address, city, area, price, price_period, price_per_day, bedrooms, bathrooms, area_sqft, is_furnished, has_parking, has_wifi, has_ac, has_generator, status, featured) VALUES
(7, 2, '4 Bed Family House in Johar Town', 'Spacious 4 bedroom house with large drawing room, dining area, and kitchen. Car porch for 2 vehicles. Located in a prime location of Johar Town near Block A2 commercial area.', 'house', 'Block A3, Johar Town', 'Lahore', 'Johar Town', 95000.00, 'both', 4000.00, 4, 3, 3000, 0, 1, 0, 1, 1, 'available', 0);

-- Property 8: Studio by Bilal Raza
INSERT IGNORE INTO properties (id, owner_id, title, description, property_type, address, city, area, price, price_period, price_per_day, bedrooms, bathrooms, area_sqft, is_furnished, has_parking, has_wifi, has_ac, has_generator, status, featured) VALUES
(8, 3, 'Cozy Studio in Gulberg II', 'A cozy and affordable studio apartment in Gulberg II. Perfect for bachelors or students. Walking distance to Liberty Market and Main Boulevard. Fully furnished with AC and WiFi.', 'studio', 'Gulberg II, near Liberty Market', 'Lahore', 'Gulberg', 28000.00, 'per_day', NULL, 1, 1, 500, 1, 0, 1, 1, 0, 'available', 0);

-- =============================================
-- PROPERTY IMAGES (using Pexels stock photos)
-- =============================================

-- Property 1 images (Luxury Apartment)
INSERT IGNORE INTO property_images (id, property_id, image_path, is_primary, sort_order) VALUES
(1, 1, 'https://images.pexels.com/photos/1571460/pexels-photo-1571460.jpeg?auto=compress&cs=tinysrgb&w=800', 1, 0),
(2, 1, 'https://images.pexels.com/photos/1571468/pexels-photo-1571468.jpeg?auto=compress&cs=tinysrgb&w=800', 0, 1),
(3, 1, 'https://images.pexels.com/photos/1571453/pexels-photo-1571453.jpeg?auto=compress&cs=tinysrgb&w=800', 0, 2),
(4, 1, 'https://images.pexels.com/photos/1571463/pexels-photo-1571463.jpeg?auto=compress&cs=tinysrgb&w=800', 0, 3);

-- Property 2 images (Family House)
INSERT IGNORE INTO property_images (id, property_id, image_path, is_primary, sort_order) VALUES
(5, 2, 'https://images.pexels.com/photos/106399/pexels-photo-106399.jpeg?auto=compress&cs=tinysrgb&w=800', 1, 0),
(6, 2, 'https://images.pexels.com/photos/1396122/pexels-photo-1396122.jpeg?auto=compress&cs=tinysrgb&w=800', 0, 1),
(7, 2, 'https://images.pexels.com/photos/1396132/pexels-photo-1396132.jpeg?auto=compress&cs=tinysrgb&w=800', 0, 2),
(8, 2, 'https://images.pexels.com/photos/1396128/pexels-photo-1396128.jpeg?auto=compress&cs=tinysrgb&w=800', 0, 3);

-- Property 3 images (Studio Apartment)
INSERT IGNORE INTO property_images (id, property_id, image_path, is_primary, sort_order) VALUES
(9, 3, 'https://images.pexels.com/photos/3935350/pexels-photo-3935350.jpeg?auto=compress&cs=tinysrgb&w=800', 1, 0),
(10, 3, 'https://images.pexels.com/photos/3935352/pexels-photo-3935352.jpeg?auto=compress&cs=tinysrgb&w=800', 0, 1),
(11, 3, 'https://images.pexels.com/photos/3935354/pexels-photo-3935354.jpeg?auto=compress&cs=tinysrgb&w=800', 0, 2);

-- Property 4 images (Villa)
INSERT IGNORE INTO property_images (id, property_id, image_path, is_primary, sort_order) VALUES
(12, 4, 'https://images.pexels.com/photos/1396122/pexels-photo-1396122.jpeg?auto=compress&cs=tinysrgb&w=800', 1, 0),
(13, 4, 'https://images.pexels.com/photos/323780/pexels-photo-323780.jpeg?auto=compress&cs=tinysrgb&w=800', 0, 1),
(14, 4, 'https://images.pexels.com/photos/1396132/pexels-photo-1396132.jpeg?auto=compress&cs=tinysrgb&w=800', 0, 2),
(15, 4, 'https://images.pexels.com/photos/106399/pexels-photo-106399.jpeg?auto=compress&cs=tinysrgb&w=800', 0, 3),
(16, 4, 'https://images.pexels.com/photos/1396128/pexels-photo-1396128.jpeg?auto=compress&cs=tinysrgb&w=800', 0, 4);

-- Property 5 images (Single Room)
INSERT IGNORE INTO property_images (id, property_id, image_path, is_primary, sort_order) VALUES
(17, 5, 'https://images.pexels.com/photos/271624/pexels-photo-271624.jpeg?auto=compress&cs=tinysrgb&w=800', 1, 0),
(18, 5, 'https://images.pexels.com/photos/271639/pexels-photo-271639.jpeg?auto=compress&cs=tinysrgb&w=800', 0, 1),
(19, 5, 'https://images.pexels.com/photos/271624/pexels-photo-271624.jpeg?auto=compress&cs=tinysrgb&w=800', 0, 2);

-- Property 6 images (2 Bed Apartment)
INSERT IGNORE INTO property_images (id, property_id, image_path, is_primary, sort_order) VALUES
(20, 6, 'https://images.pexels.com/photos/7587425/pexels-photo-7587425.jpeg?auto=compress&cs=tinysrgb&w=800', 1, 0),
(21, 6, 'https://images.pexels.com/photos/7587426/pexels-photo-7587426.jpeg?auto=compress&cs=tinysrgb&w=800', 0, 1),
(22, 6, 'https://images.pexels.com/photos/7587427/pexels-photo-7587427.jpeg?auto=compress&cs=tinysrgb&w=800', 0, 2),
(23, 6, 'https://images.pexels.com/photos/7587428/pexels-photo-7587428.jpeg?auto=compress&cs=tinysrgb&w=800', 0, 3);

-- Property 7 images (4 Bed House)
INSERT IGNORE INTO property_images (id, property_id, image_path, is_primary, sort_order) VALUES
(24, 7, 'https://images.pexels.com/photos/259588/pexels-photo-259588.jpeg?auto=compress&cs=tinysrgb&w=800', 1, 0),
(25, 7, 'https://images.pexels.com/photos/259588/pexels-photo-259588.jpeg?auto=compress&cs=tinysrgb&w=800', 0, 1),
(26, 7, 'https://images.pexels.com/photos/1396132/pexels-photo-1396132.jpeg?auto=compress&cs=tinysrgb&w=800', 0, 2),
(27, 7, 'https://images.pexels.com/photos/106399/pexels-photo-106399.jpeg?auto=compress&cs=tinysrgb&w=800', 0, 3);

-- Property 8 images (Cozy Studio)
INSERT IGNORE INTO property_images (id, property_id, image_path, is_primary, sort_order) VALUES
(28, 8, 'https://images.pexels.com/photos/3935350/pexels-photo-3935350.jpeg?auto=compress&cs=tinysrgb&w=800', 1, 0),
(29, 8, 'https://images.pexels.com/photos/3935352/pexels-photo-3935352.jpeg?auto=compress&cs=tinysrgb&w=800', 0, 1),
(30, 8, 'https://images.pexels.com/photos/271624/pexels-photo-271624.jpeg?auto=compress&cs=tinysrgb&w=800', 0, 2);

-- =============================================
-- SAMPLE REVIEWS
-- =============================================

INSERT IGNORE INTO reviews (id, property_id, user_id, rating, comment) VALUES
(1, 1, 5, 5, 'Excellent apartment! Very clean and well-maintained. The owner is very cooperative.'),
(2, 1, 5, 4, 'Great location and nice facilities. WiFi and AC work perfectly.'),
(3, 3, 5, 5, 'Perfect studio for a single person. Everything you need in one place.'),
(4, 4, 5, 5, 'Amazing villa with beautiful pool. Highly recommended for families.');
