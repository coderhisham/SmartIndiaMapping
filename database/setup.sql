-- The database is already created by Docker so we don't need to create it
-- CREATE DATABASE IF NOT EXISTS smart_india_mapping;
USE smart_india_mapping;

-- Users table for admin authentication
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
);

-- Activity log table
CREATE TABLE IF NOT EXISTS activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action TEXT NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Regions table (states/districts)
CREATE TABLE IF NOT EXISTS regions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    type ENUM('state', 'district') NOT NULL,
    parent_id INT NULL,
    population BIGINT DEFAULT 0,
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    FOREIGN KEY (parent_id) REFERENCES regions(id) ON DELETE CASCADE
);

-- Schools resource table
CREATE TABLE IF NOT EXISTS resources_schools (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    state VARCHAR(100) NOT NULL,
    district VARCHAR(100),
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    level ENUM('primary', 'secondary', 'higher', 'university') NOT NULL,
    students INT DEFAULT 0,
    teachers INT DEFAULT 0,
    established_year INT,
    facilities TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Hospitals resource table
CREATE TABLE IF NOT EXISTS resources_hospitals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    state VARCHAR(100) NOT NULL,
    district VARCHAR(100),
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    hospital_type ENUM('government', 'private', 'charity') NOT NULL,
    beds INT DEFAULT 0,
    doctors INT DEFAULT 0,
    specialties TEXT,
    emergency_services BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Water resources table
CREATE TABLE IF NOT EXISTS resources_water (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    state VARCHAR(100) NOT NULL,
    district VARCHAR(100),
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    source_type ENUM('dam', 'lake', 'river', 'well', 'treatment_plant') NOT NULL,
    capacity DECIMAL(12, 2) DEFAULT 0,
    quality_index DECIMAL(5, 2),
    serves_population INT DEFAULT 0,
    year_established INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Electricity resources table
CREATE TABLE IF NOT EXISTS resources_electricity (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    state VARCHAR(100) NOT NULL,
    district VARCHAR(100),
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    power_type ENUM('hydro', 'thermal', 'nuclear', 'solar', 'wind', 'substation') NOT NULL,
    capacity DECIMAL(12, 2) DEFAULT 0,
    serves_areas TEXT,
    year_established INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default admin user (username: admin, password: admin123)
-- In a production environment, use a stronger password and store hashed passwords
INSERT INTO users (username, password, email) 
VALUES ('admin', '$2y$10$HVl3Cb8WvCIqbsUyRiuMlOhcRLWqpTDv9C5mDl5IMcCBRDvuQsAOC', 'admin@example.com')
ON DUPLICATE KEY UPDATE password = VALUES(password);

-- Insert some sample regions (Indian states with population data)
INSERT INTO regions (name, type, population, latitude, longitude) VALUES 
('Andhra Pradesh', 'state', 49577103, 15.9129, 79.7400),
('Arunachal Pradesh', 'state', 1383727, 27.1004, 93.6167),
('Assam', 'state', 31205576, 26.2006, 92.9376),
('Bihar', 'state', 104099452, 25.0961, 85.3131),
('Chhattisgarh', 'state', 25545198, 21.2787, 81.8661),
('Goa', 'state', 1458545, 15.2993, 74.1240),
('Gujarat', 'state', 60439692, 22.2587, 71.1924),
('Haryana', 'state', 25351462, 29.0588, 76.0856),
('Himachal Pradesh', 'state', 6864602, 31.1048, 77.1734),
('Jharkhand', 'state', 32988134, 23.6102, 85.2799),
('Karnataka', 'state', 61095297, 15.3173, 75.7139),
('Kerala', 'state', 33406061, 10.8505, 76.2711),
('Madhya Pradesh', 'state', 72626809, 22.9734, 78.6569),
('Maharashtra', 'state', 112374333, 19.7515, 75.7139),
('Manipur', 'state', 2855794, 24.6637, 93.9063),
('Meghalaya', 'state', 2966889, 25.4670, 91.3662),
('Mizoram', 'state', 1097206, 23.1645, 92.9376),
('Nagaland', 'state', 1978502, 26.1584, 94.5624),
('Odisha', 'state', 41974218, 20.9517, 85.0985),
('Punjab', 'state', 27743338, 31.1471, 75.3412),
('Rajasthan', 'state', 68548437, 27.0238, 74.2179),
('Sikkim', 'state', 610577, 27.5330, 88.5122),
('Tamil Nadu', 'state', 72147030, 11.1271, 78.6569),
('Telangana', 'state', 35003674, 18.1124, 79.0193),
('Tripura', 'state', 3673917, 23.9408, 91.9882),
('Uttar Pradesh', 'state', 199812341, 26.8467, 80.9462),
('Uttarakhand', 'state', 10086292, 30.0668, 79.0193),
('West Bengal', 'state', 91276115, 22.9868, 87.8550),
('Delhi', 'state', 16787941, 28.7041, 77.1025),
('Jammu and Kashmir', 'state', 12267032, 33.7782, 76.5762)
ON DUPLICATE KEY UPDATE population = VALUES(population), latitude = VALUES(latitude), longitude = VALUES(longitude); 