-- SQL to create the helper_locations table for tracking helper locations

CREATE TABLE IF NOT EXISTS `helper_locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `helper_email` varchar(255) NOT NULL,
  `post_id` int(11) NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `helper_email` (`helper_email`),
  KEY `post_id` (`post_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add some sample data for testing
INSERT INTO `helper_locations` (`helper_email`, `post_id`, `latitude`, `longitude`, `address`, `updated_at`)
VALUES 
('helper@example.com', 1, 3.1390, 101.6869, 'Jalan Bukit Bintang, Bukit Bintang, 55100 Kuala Lumpur', NOW());
