-- Database setup voor Codex Mundi
-- Voer dit script uit in phpMyAdmin of MySQL command line

CREATE DATABASE IF NOT EXISTS `codex_mundi` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `codex_mundi`;

-- Roles tabel
CREATE TABLE IF NOT EXISTS `roles` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(50) NOT NULL UNIQUE,
    `description` varchar(255),
    `permissions` text, -- JSON string met permissions
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Users tabel
CREATE TABLE IF NOT EXISTS `users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `username` varchar(50) NOT NULL UNIQUE,
    `email` varchar(100) NOT NULL UNIQUE,
    `password` varchar(255) NOT NULL,
    `first_name` varchar(50) NOT NULL,
    `last_name` varchar(50) NOT NULL,
    `role_id` int(11) NOT NULL,
    `is_active` tinyint(1) DEFAULT 1,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sessions tabel
CREATE TABLE IF NOT EXISTS `sessions` (
    `id` varchar(128) NOT NULL,
    `user_id` int(11) NOT NULL,
    `ip_address` varchar(45) NOT NULL,
    `user_agent` text,
    `payload` longtext NOT NULL,
    `last_activity` int(11) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `sessions_user_id_index` (`user_id`),
    KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- World Wonders tabel
CREATE TABLE IF NOT EXISTS `world_wonders` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `description` text,
    `historical_info` text,
    `construction_year` int(11),
    `status` enum('exists','destroyed','unknown') DEFAULT 'exists',
    `category` enum('classical','modern','natural') DEFAULT 'classical',
    `continent` varchar(50),
    `country` varchar(100),
    `city` varchar(100),
    `latitude` decimal(10, 8),
    `longitude` decimal(11, 8),
    `created_by` int(11) NOT NULL,
    `approved_by` int(11),
    `is_approved` tinyint(1) DEFAULT 0,
    `is_public` tinyint(1) DEFAULT 1,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_created_by` (`created_by`),
    KEY `idx_approved_by` (`approved_by`),
    KEY `idx_category` (`category`),
    KEY `idx_continent` (`continent`),
    KEY `idx_status` (`status`),
    KEY `idx_is_approved` (`is_approved`),
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`approved_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Media tabel voor afbeeldingen
CREATE TABLE IF NOT EXISTS `media` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `world_wonder_id` int(11) NOT NULL,
    `filename` varchar(255) NOT NULL,
    `original_name` varchar(255),
    `file_path` varchar(500) NOT NULL,
    `file_type` varchar(50) NOT NULL,
    `file_size` int(11) NOT NULL,
    `uploaded_by` int(11) NOT NULL,
    `approved_by` int(11),
    `is_approved` tinyint(1) DEFAULT 0,
    `is_primary` tinyint(1) DEFAULT 0,
    `description` varchar(500),
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_world_wonder_id` (`world_wonder_id`),
    KEY `idx_uploaded_by` (`uploaded_by`),
    KEY `idx_approved_by` (`approved_by`),
    FOREIGN KEY (`world_wonder_id`) REFERENCES `world_wonders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`uploaded_by`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`approved_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tags tabel
CREATE TABLE IF NOT EXISTS `tags` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(50) NOT NULL UNIQUE,
    `description` varchar(255),
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- World Wonder Tags koppeling
CREATE TABLE IF NOT EXISTS `world_wonder_tags` (
    `world_wonder_id` int(11) NOT NULL,
    `tag_id` int(11) NOT NULL,
    PRIMARY KEY (`world_wonder_id`, `tag_id`),
    FOREIGN KEY (`world_wonder_id`) REFERENCES `world_wonders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`tag_id`) REFERENCES `tags`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Activity logs tabel
CREATE TABLE IF NOT EXISTS `activity_logs` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11),
    `action` varchar(100) NOT NULL,
    `table_name` varchar(50),
    `record_id` int(11),
    `old_values` text,
    `new_values` text,
    `ip_address` varchar(45),
    `user_agent` varchar(500),
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_created_at` (`created_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert roles
INSERT INTO `roles` (`name`, `description`, `permissions`) VALUES
('bezoeker', 'Kan alleen wereldwonderen bekijken', '["view_wonders"]'),
('onderzoeker', 'Kan nieuwe wereldwonderen aanmaken en eigen invoer bewerken', '["view_wonders","create_wonders","edit_own_wonders","upload_media"]'),
('redacteur', 'Kan bijdragen van anderen controleren en goedkeuren', '["view_wonders","create_wonders","edit_wonders","approve_wonders","manage_media","add_metadata"]'),
('archivaris', 'Kan historische gegevens toevoegen en sorteren', '["view_wonders","create_wonders","edit_wonders","add_historical_data","manage_coordinates","sort_data"]'),
('beheerder', 'Kan alles beheren en gebruikers aanmaken', '["all_permissions","manage_users","manage_roles","system_settings"]');

-- Insert default admin user (password: admin123)
INSERT INTO `users` (`username`, `email`, `password`, `first_name`, `last_name`, `role_id`) 
VALUES ('admin', 'admin@codexmundi.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', 5);

-- Insert some sample world wonders
INSERT INTO `world_wonders` (`name`, `description`, `historical_info`, `construction_year`, `status`, `category`, `continent`, `country`, `city`, `latitude`, `longitude`, `created_by`, `is_approved`) VALUES
('Piramide van Gizeh', 'De enige van de zeven klassieke wereldwonderen die nog steeds bestaat.', 'Gebouwd als grafmonument voor farao Khufu rond 2580-2560 v.Chr.', -2580, 'exists', 'classical', 'Africa', 'Egypte', 'Gizeh', 29.9792, 31.1342, 1, 1),
('Colosseum', 'Het grootste amfitheater ooit gebouwd in het Romeinse Rijk.', 'Gebouwd tussen 70-80 n.Chr. onder de Flavische keizers.', 80, 'exists', 'classical', 'Europe', 'Italië', 'Rome', 41.8902, 12.4922, 1, 1),
('Taj Mahal', 'Een mausoleum gebouwd door keizer Shah Jahan voor zijn vrouw Mumtaz Mahal.', 'Gebouwd tussen 1632-1653 in Agra, India.', 1653, 'exists', 'classical', 'Asia', 'India', 'Agra', 27.1751, 78.0421, 1, 1),
('Eiffeltoren', 'Een ijzeren toren in Parijs, Frankrijk.', 'Gebouwd voor de Wereldtentoonstelling van 1889.', 1889, 'exists', 'modern', 'Europe', 'Frankrijk', 'Parijs', 48.8584, 2.2945, 1, 1),
('Machu Picchu', 'Een Inca-citadel hoog in de Andes van Peru.', 'Gebouwd rond 1450 en verlaten tijdens de Spaanse verovering.', 1450, 'exists', 'classical', 'South America', 'Peru', 'Cusco', -13.1631, -72.5450, 1, 1);
