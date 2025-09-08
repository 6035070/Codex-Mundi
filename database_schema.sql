-- Database schema voor CRUD systeem met afbeeldingen
-- Voor Microsoft SQL Server Management Studio

-- Database aanmaken (optioneel - kan ook handmatig in SSMS)
-- CREATE DATABASE ImageCRUD;
-- USE ImageCRUD;

-- Tabel voor items met afbeeldingen
CREATE TABLE items (
    id INT IDENTITY(1,1) PRIMARY KEY,
    title NVARCHAR(255) NOT NULL,
    description NVARCHAR(MAX),
    image_path NVARCHAR(500),
    created_at DATETIME2 DEFAULT GETDATE(),
    updated_at DATETIME2 DEFAULT GETDATE()
);

-- Index voor betere performance
CREATE INDEX IX_items_created_at ON items(created_at);

-- Voorbeeld data (optioneel)
INSERT INTO items (title, description, image_path) VALUES 
('Voorbeeld Item 1', 'Dit is een voorbeeld beschrijving voor het eerste item.', 'uploads/sample1.jpg'),
('Voorbeeld Item 2', 'Dit is een voorbeeld beschrijving voor het tweede item.', 'uploads/sample2.jpg');
