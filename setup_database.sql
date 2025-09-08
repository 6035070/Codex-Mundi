-- Complete database setup script voor CRUD systeem
-- Voer dit uit in SQL Server Management Studio

-- 1. Database aanmaken
CREATE DATABASE ImageCRUD;
GO

-- 2. Database selecteren
USE ImageCRUD;
GO

-- 3. Tabel voor items met afbeeldingen aanmaken
CREATE TABLE items (
    id INT IDENTITY(1,1) PRIMARY KEY,
    title NVARCHAR(255) NOT NULL,
    description NVARCHAR(MAX),
    image_path NVARCHAR(500),
    created_at DATETIME2 DEFAULT GETDATE(),
    updated_at DATETIME2 DEFAULT GETDATE()
);
GO

-- 4. Index voor betere performance
CREATE INDEX IX_items_created_at ON items(created_at);
GO

-- 5. Voorbeeld data toevoegen (optioneel)
INSERT INTO items (title, description, image_path) VALUES 
('Voorbeeld Item 1', 'Dit is een voorbeeld beschrijving voor het eerste item.', 'uploads/sample1.jpg'),
('Voorbeeld Item 2', 'Dit is een voorbeeld beschrijving voor het tweede item.', 'uploads/sample2.jpg');
GO

-- 6. Controleer of alles correct is aangemaakt
SELECT 'Database en tabel succesvol aangemaakt!' as Status;
SELECT COUNT(*) as AantalItems FROM items;
