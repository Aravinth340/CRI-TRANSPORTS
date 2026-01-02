-- CRI Travels Database Schema
-- Run this file to create the database and tables

CREATE DATABASE IF NOT EXISTS cri_travels;
USE cri_travels;

-- Users table (for clients, drivers, and admin)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    user_type ENUM('client', 'driver', 'admin') NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Client details table
CREATE TABLE IF NOT EXISTS clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE NOT NULL,
    address TEXT,
    city VARCHAR(50),
    state VARCHAR(50),
    pincode VARCHAR(10),
    emergency_contact VARCHAR(20),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Added vehicles table for vehicle management
-- Vehicles table
CREATE TABLE IF NOT EXISTS vehicles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vehicle_number VARCHAR(20) UNIQUE NOT NULL,
    vehicle_type ENUM('auto', 'maxicab', 'car', 'coach') NOT NULL,
    make VARCHAR(50),
    model VARCHAR(50),
    year INT,
    capacity INT NOT NULL,
    color VARCHAR(30),
    registration_date DATE,
    insurance_expiry DATE,
    status ENUM('active', 'maintenance', 'inactive') DEFAULT 'active',
    current_driver_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (current_driver_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Updated drivers table to reference vehicles table
-- Driver details table
CREATE TABLE IF NOT EXISTS drivers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE NOT NULL,
    license_number VARCHAR(50) UNIQUE NOT NULL,
    vehicle_id INT,
    experience_years INT,
    rating DECIMAL(3,2) DEFAULT 0.00,
    availability ENUM('available', 'busy', 'offline') DEFAULT 'available',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE SET NULL
);

-- Updated trips table to reference vehicles table
-- Trips table
CREATE TABLE IF NOT EXISTS trips (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    driver_id INT,
    vehicle_id INT,
    service_type ENUM('auto', 'maxicab', 'car', 'coach') NOT NULL,
    pickup_location VARCHAR(255) NOT NULL,
    destination VARCHAR(255) NOT NULL,
    travel_date DATE NOT NULL,
    travel_time TIME NOT NULL,
    passengers INT NOT NULL,
    status ENUM('pending', 'confirmed', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    fare DECIMAL(10,2),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES users(id),
    FOREIGN KEY (driver_id) REFERENCES users(id),
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE SET NULL
);

-- Insert default admin account (password: admin123)
INSERT INTO users (username, email, password, full_name, phone, user_type) 
VALUES ('admin', 'admin@critravels.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin User', '+917558198405', 'admin');

-- Sample vehicles data
-- Insert sample vehicles
INSERT INTO vehicles (vehicle_number, vehicle_type, make, model, year, capacity, color, registration_date, insurance_expiry, status) VALUES
('KA01AB1234', 'auto', 'Bajaj', 'RE', 2022, 3, 'Yellow', '2022-01-15', '2026-01-15', 'active'),
('KA01CD5678', 'car', 'Toyota', 'Etios', 2021, 4, 'White', '2021-03-20', '2026-03-20', 'active'),
('KA02EF9012', 'maxicab', 'Tata', 'Winger', 2023, 10, 'White', '2023-05-10', '2027-05-10', 'active'),
('KA03GH3456', 'coach', 'Ashok Leyland', 'Viking', 2020, 35, 'Blue', '2020-07-25', '2025-07-25', 'active');
