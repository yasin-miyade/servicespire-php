CREATE DATABASE location_tracking;
USE location_tracking;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    user_type ENUM('helper', 'seeker') NOT NULL,
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create some sample users
INSERT INTO users (username, user_type) VALUES 
('helper1', 'helper'),
('seeker1', 'seeker');