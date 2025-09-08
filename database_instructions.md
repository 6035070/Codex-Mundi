# Database Setup Instructies

## Stap-voor-stap Database Aanmaken

### 1. Open SQL Server Management Studio (SSMS)
- Start SSMS op je computer
- Verbind met je SQL Server instance

### 2. Database Aanmaken
Voer de volgende SQL commando's uit in SSMS:

```sql
-- Database aanmaken
CREATE DATABASE ImageCRUD;
GO

-- Database selecteren
USE ImageCRUD;
GO
```

### 3. Tabel Aanmaken
```sql
-- Tabel voor items met afbeeldingen
CREATE TABLE items (
    id INT IDENTITY(1,1) PRIMARY KEY,
    title NVARCHAR(255) NOT NULL,
    description NVARCHAR(MAX),
    image_path NVARCHAR(500),
    created_at DATETIME2 DEFAULT GETDATE(),
    updated_at DATETIME2 DEFAULT GETDATE()
);
GO
```

### 4. Index Toevoegen
```sql
-- Index voor betere performance
CREATE INDEX IX_items_created_at ON items(created_at);
GO
```

### 5. Test Data (Optioneel)
```sql
-- Voorbeeld data toevoegen
INSERT INTO items (title, description, image_path) VALUES 
('Voorbeeld Item 1', 'Dit is een voorbeeld beschrijving voor het eerste item.', 'uploads/sample1.jpg'),
('Voorbeeld Item 2', 'Dit is een voorbeeld beschrijving voor het tweede item.', 'uploads/sample2.jpg');
GO
```

### 6. Verificatie
```sql
-- Controleer of alles correct is aangemaakt
SELECT 'Database en tabel succesvol aangemaakt!' as Status;
SELECT COUNT(*) as AantalItems FROM items;
```

## Alternatief: Gebruik het Setup Script

Je kunt ook het bestand `setup_database.sql` gebruiken:

1. Open het bestand `setup_database.sql` in SSMS
2. Klik op "Execute" (F5) om het hele script uit te voeren
3. Controleer of er geen fouten zijn

## Database Configuratie

Na het aanmaken van de database, bewerk je `config/database.php`:

```php
private $server = "localhost"; // of je SQL Server instance naam
private $database = "ImageCRUD";
private $username = "sa"; // je SQL Server username  
private $password = "your_password"; // je SQL Server password
```

## Problemen Oplossen

### Fout: "Cannot connect to server"
- Controleer of SQL Server service draait
- Verificeer de server naam (localhost of instance naam)
- Controleer firewall instellingen

### Fout: "Login failed"
- Controleer gebruikersnaam en wachtwoord
- Zorg dat de gebruiker rechten heeft op de database

### Fout: "Database does not exist"
- Voer eerst het CREATE DATABASE commando uit
- Controleer of je de juiste database hebt geselecteerd met USE ImageCRUD
