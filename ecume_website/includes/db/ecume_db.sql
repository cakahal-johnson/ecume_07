-- Create Database
CREATE DATABASE IF NOT EXISTS ecume_db;
USE ecume_db;

-- Create Users Table (For Members & Admins)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(15),
    profile_picture VARCHAR(255) DEFAULT 'default.png',
    role ENUM('admin', 'member') DEFAULT 'member',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create Members Table (For Additional Member Info)
CREATE TABLE members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    occupation VARCHAR(100),
    location VARCHAR(255),
    bio TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert Default Admin Account (Password: admin123 - Hash in PHP)
INSERT INTO users (full_name, email, password, phone, role) 
VALUES ('Admin User', 'admin@ecume.com', 'admin123', '08000000000', 'admin');

-- Sample User Entry (Password: user123 - Hash in PHP)
INSERT INTO users (full_name, email, password, phone, role) 
VALUES ('John Doe', 'johndoe@example.com', 'user123', '08123456789', 'member');

-- Sample Member Info (Ensure user ID 2 exists)
INSERT INTO members (user_id, occupation, location, bio) 
VALUES (2, 'Software Engineer', 'Lagos, Nigeria', 'Passionate about technology and community growth.');
