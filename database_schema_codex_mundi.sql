-- Codex Mundi Database Schema
-- Complete database voor wereldwonderen beheersysteem

-- 1. Database aanmaken
CREATE DATABASE CodexMundi;
GO

-- 2. Database selecteren
USE CodexMundi;
GO

-- 3. Gebruikers tabel
CREATE TABLE users (
    id INT IDENTITY(1,1) PRIMARY KEY,
    username NVARCHAR(50) UNIQUE NOT NULL,
    email NVARCHAR(100) UNIQUE NOT NULL,
    password_hash NVARCHAR(255) NOT NULL,
    first_name NVARCHAR(50),
    last_name NVARCHAR(50),
    role_id INT NOT NULL,
    is_active BIT DEFAULT 1,
    created_at DATETIME2 DEFAULT GETDATE(),
    updated_at DATETIME2 DEFAULT GETDATE()
);
GO

-- 4. Rollen tabel
CREATE TABLE roles (
    id INT IDENTITY(1,1) PRIMARY KEY,
    name NVARCHAR(50) UNIQUE NOT NULL,
    description NVARCHAR(255),
    permissions NVARCHAR(MAX), -- JSON string met permissions
    created_at DATETIME2 DEFAULT GETDATE()
);
GO

-- 5. Wereldwonderen tabel
CREATE TABLE world_wonders (
    id INT IDENTITY(1,1) PRIMARY KEY,
    name NVARCHAR(255) NOT NULL,
    description NVARCHAR(MAX),
    historical_info NVARCHAR(MAX),
    construction_year INT,
    status NVARCHAR(50) DEFAULT 'exists', -- exists, destroyed, unknown
    category NVARCHAR(50), -- classical, modern, natural
    continent NVARCHAR(50),
    country NVARCHAR(100),
    city NVARCHAR(100),
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    created_by INT NOT NULL,
    approved_by INT,
    is_approved BIT DEFAULT 0,
    is_public BIT DEFAULT 1,
    created_at DATETIME2 DEFAULT GETDATE(),
    updated_at DATETIME2 DEFAULT GETDATE(),
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (approved_by) REFERENCES users(id)
);
GO

-- 6. Media tabel
CREATE TABLE media (
    id INT IDENTITY(1,1) PRIMARY KEY,
    world_wonder_id INT NOT NULL,
    filename NVARCHAR(255) NOT NULL,
    original_name NVARCHAR(255),
    file_path NVARCHAR(500) NOT NULL,
    file_type NVARCHAR(50) NOT NULL,
    file_size INT NOT NULL,
    uploaded_by INT NOT NULL,
    approved_by INT,
    is_approved BIT DEFAULT 0,
    is_primary BIT DEFAULT 0,
    description NVARCHAR(500),
    created_at DATETIME2 DEFAULT GETDATE(),
    FOREIGN KEY (world_wonder_id) REFERENCES world_wonders(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id),
    FOREIGN KEY (approved_by) REFERENCES users(id)
);
GO

-- 7. Tags tabel
CREATE TABLE tags (
    id INT IDENTITY(1,1) PRIMARY KEY,
    name NVARCHAR(50) UNIQUE NOT NULL,
    description NVARCHAR(255),
    created_at DATETIME2 DEFAULT GETDATE()
);
GO

-- 8. Wereldwonder tags koppeling
CREATE TABLE world_wonder_tags (
    world_wonder_id INT NOT NULL,
    tag_id INT NOT NULL,
    PRIMARY KEY (world_wonder_id, tag_id),
    FOREIGN KEY (world_wonder_id) REFERENCES world_wonders(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
);
GO

-- 9. Activiteit log tabel
CREATE TABLE activity_logs (
    id INT IDENTITY(1,1) PRIMARY KEY,
    user_id INT,
    action NVARCHAR(100) NOT NULL,
    table_name NVARCHAR(50),
    record_id INT,
    old_values NVARCHAR(MAX),
    new_values NVARCHAR(MAX),
    ip_address NVARCHAR(45),
    user_agent NVARCHAR(500),
    created_at DATETIME2 DEFAULT GETDATE(),
    FOREIGN KEY (user_id) REFERENCES users(id)
);
GO

-- 10. Notificaties tabel
CREATE TABLE notifications (
    id INT IDENTITY(1,1) PRIMARY KEY,
    user_id INT NOT NULL,
    title NVARCHAR(255) NOT NULL,
    message NVARCHAR(MAX),
    type NVARCHAR(50) DEFAULT 'info', -- info, warning, success, error
    is_read BIT DEFAULT 0,
    related_id INT,
    related_type NVARCHAR(50),
    created_at DATETIME2 DEFAULT GETDATE(),
    FOREIGN KEY (user_id) REFERENCES users(id)
);
GO

-- 11. Instellingen tabel
CREATE TABLE settings (
    id INT IDENTITY(1,1) PRIMARY KEY,
    setting_key NVARCHAR(100) UNIQUE NOT NULL,
    setting_value NVARCHAR(MAX),
    description NVARCHAR(255),
    updated_by INT,
    updated_at DATETIME2 DEFAULT GETDATE(),
    FOREIGN KEY (updated_by) REFERENCES users(id)
);
GO

-- 12. Indexen voor performance
CREATE INDEX IX_world_wonders_name ON world_wonders(name);
CREATE INDEX IX_world_wonders_category ON world_wonders(category);
CREATE INDEX IX_world_wonders_continent ON world_wonders(continent);
CREATE INDEX IX_world_wonders_created_by ON world_wonders(created_by);
CREATE INDEX IX_world_wonders_is_approved ON world_wonders(is_approved);
CREATE INDEX IX_media_world_wonder_id ON media(world_wonder_id);
CREATE INDEX IX_media_is_approved ON media(is_approved);
CREATE INDEX IX_activity_logs_user_id ON activity_logs(user_id);
CREATE INDEX IX_activity_logs_created_at ON activity_logs(created_at);
CREATE INDEX IX_notifications_user_id ON notifications(user_id);
CREATE INDEX IX_notifications_is_read ON notifications(is_read);
GO

-- 13. Standaard rollen invoegen
INSERT INTO roles (name, description, permissions) VALUES 
('Bezoeker', 'Kan alleen wereldwonderen bekijken', '{"view_wonders": true}'),
('Onderzoeker', 'Kan nieuwe wereldwonderen aanmaken en eigen bijdragen bewerken', '{"view_wonders": true, "create_wonders": true, "edit_own_wonders": true, "upload_media": true}'),
('Redacteur', 'Kan bijdragen van anderen controleren en goedkeuren', '{"view_wonders": true, "create_wonders": true, "edit_wonders": true, "approve_wonders": true, "approve_media": true, "add_tags": true}'),
('Archivaris', 'Kan historische gegevens toevoegen en sorteren', '{"view_wonders": true, "edit_wonders": true, "add_historical_data": true, "manage_locations": true, "upload_documents": true}'),
('Beheerder', 'Kan alles beheren', '{"all_permissions": true}');
GO

-- 14. Standaard beheerder gebruiker
INSERT INTO users (username, email, password_hash, first_name, last_name, role_id) VALUES 
('admin', 'admin@codexmundi.nl', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', 5);
GO

-- 15. Standaard instellingen
INSERT INTO settings (setting_key, setting_value, description) VALUES 
('max_file_size', '10485760', 'Maximum bestandsgrootte in bytes (10MB)'),
('allowed_file_types', 'jpg,jpeg,png,gif,pdf,doc,docx', 'Toegestane bestandstypen'),
('site_name', 'Codex Mundi', 'Naam van de website'),
('site_description', 'Database van wereldwonderen', 'Beschrijving van de website'),
('require_approval', '1', 'Vereis goedkeuring voor nieuwe wereldwonderen'),
('max_upload_per_user', '50', 'Maximum aantal uploads per gebruiker per dag');
GO

-- 16. Voorbeeld wereldwonderen
INSERT INTO world_wonders (name, description, historical_info, construction_year, status, category, continent, country, city, latitude, longitude, created_by, is_approved) VALUES 
('Piramide van Gizeh', 'De Grote Piramide van Gizeh is de oudste en grootste van de drie piramides in de Gizeh necropolis.', 'Gebouwd rond 2580-2560 v.Chr. tijdens de 4e dynastie van het Oude Rijk van Egypte.', -2580, 'exists', 'classical', 'Africa', 'Egypte', 'Gizeh', 29.9792, 31.1342, 1, 1),
('Colosseum', 'Het Colosseum is een amfitheater in het centrum van Rome, Italië.', 'Gebouwd tussen 70-80 n.Chr. onder de Flavische keizers. Kon 50.000 toeschouwers herbergen.', 80, 'exists', 'classical', 'Europe', 'Italië', 'Rome', 41.8902, 12.4922, 1, 1),
('Machu Picchu', 'Machu Picchu is een 15e-eeuwse Inca-citadel in de Peruaanse Andes.', 'Gebouwd rond 1450 door de Inca-keizer Pachacuti. Verlaten tijdens de Spaanse verovering.', 1450, 'exists', 'classical', 'South America', 'Peru', 'Cusco', -13.1631, -72.5450, 1, 1);
GO

-- 17. Voorbeeld tags
INSERT INTO tags (name, description) VALUES 
('Antiek', 'Oude bouwwerken uit de klassieke oudheid'),
('Religieus', 'Religieuze of spirituele betekenis'),
('Architectuur', 'Bijzondere architectonische waarde'),
('Natuur', 'Natuurlijke wonderen'),
('Modern', 'Moderne bouwwerken'),
('Verloren', 'Niet meer bestaande wonderen');
GO

-- 18. Koppel tags aan wereldwonderen
INSERT INTO world_wonder_tags (world_wonder_id, tag_id) VALUES 
(1, 1), (1, 3), -- Piramide: Antiek, Architectuur
(2, 1), (2, 3), -- Colosseum: Antiek, Architectuur  
(3, 1), (3, 3), (3, 2); -- Machu Picchu: Antiek, Architectuur, Religieus
GO

-- 19. Controle query
SELECT 'Database Codex Mundi succesvol aangemaakt!' as Status;
SELECT COUNT(*) as AantalGebruikers FROM users;
SELECT COUNT(*) as AantalRollen FROM roles;
SELECT COUNT(*) as AantalWereldwonderen FROM world_wonders;
SELECT COUNT(*) as AantalTags FROM tags;
