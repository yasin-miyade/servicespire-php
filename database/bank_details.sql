-- Drop the table if it exists to avoid conflicts
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    wallet_balance DECIMAL(10,2) DEFAULT 0.00,
    bank_name VARCHAR(100),
    account_number VARCHAR(50),
    ifsc_code VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default records for existing users
INSERT INTO users (email)
SELECT DISTINCT email FROM sign_up
ON DUPLICATE KEY UPDATE email = VALUES(email);

-- Set initial wallet balance to 0 for all users
UPDATE users SET wallet_balance = 0.00 WHERE wallet_balance IS NULL;
