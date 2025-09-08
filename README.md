# CRUD Systeem met Afbeeldingen

Een complete webapplicatie gebouwd met HTML, PHP, CSS en Microsoft SQL Server voor het beheren van items met afbeeldingen.

## Functies

- ✅ **CRUD Operaties**: Create, Read, Update, Delete items
- ✅ **Afbeelding Upload**: Upload en beheer afbeeldingen (JPEG, PNG, GIF, WebP)
- ✅ **Responsive Design**: Werkt op desktop, tablet en mobiel
- ✅ **Modern UI**: Mooie en gebruiksvriendelijke interface
- ✅ **SQL Server Database**: Volledige integratie met Microsoft SQL Server
- ✅ **Real-time Updates**: Directe feedback bij alle acties

## Vereisten

### Software
- **PHP 7.4+** met SQL Server extensie
- **Microsoft SQL Server** (2016 of nieuwer)
- **SQL Server Management Studio (SSMS)**
- **Web Server** (Apache/Nginx) of **XAMPP/WAMP**

### PHP Extensies
```bash
# Installeer de SQL Server extensie voor PHP
# Voor Windows:
# Download Microsoft Drivers for PHP for SQL Server
# Voor Linux:
sudo apt-get install php-sqlsrv php-pdo-sqlsrv
```

## Installatie

### 1. Database Setup

1. Open **SQL Server Management Studio**
2. Maak een nieuwe database aan:
   ```sql
   CREATE DATABASE ImageCRUD;
   ```
3. Voer het `database_schema.sql` bestand uit in de nieuwe database
4. Controleer of de tabel `items` is aangemaakt

### 2. Database Configuratie

Bewerk het bestand `config/database.php`:

```php
private $server = "localhost"; // of je SQL Server instance naam
private $database = "ImageCRUD";
private $username = "sa"; // je SQL Server username
private $password = "your_password"; // je SQL Server password
```

### 3. Web Server Setup

#### Optie A: XAMPP/WAMP
1. Download en installeer XAMPP of WAMP
2. Kopieer alle bestanden naar de `htdocs` of `www` directory
3. Start Apache en SQL Server services

#### Optie B: IIS (Windows)
1. Installeer IIS met PHP ondersteuning
2. Kopieer bestanden naar de web directory
3. Configureer PHP voor SQL Server

### 4. Permissies

Zorg ervoor dat de `uploads` directory schrijfrechten heeft:
```bash
# Windows
icacls uploads /grant Everyone:F

# Linux
chmod 755 uploads
```

## Project Structuur

```
project/
├── api/
│   └── items.php          # REST API endpoints
├── config/
│   └── database.php       # Database configuratie
├── css/
│   └── style.css          # Styling
├── includes/
│   └── item.php           # Item management class
├── js/
│   └── script.js          # Frontend JavaScript
├── uploads/               # Uploaded afbeeldingen
├── index.php              # Hoofdpagina
├── database_schema.sql    # Database schema
└── README.md             # Dit bestand
```

## Gebruik

### Items Toevoegen
1. Klik op "Nieuw Item Toevoegen"
2. Vul titel en beschrijving in
3. Selecteer een afbeelding (optioneel)
4. Klik op "Opslaan"

### Items Bewerken
1. Hover over een item kaart
2. Klik op het potlood icoon
3. Pas de gegevens aan
4. Klik op "Opslaan"

### Items Verwijderen
1. Hover over een item kaart
2. Klik op het prullenbak icoon
3. Bevestig de verwijdering

## API Endpoints

### GET /api/items.php
- **Alle items**: `GET /api/items.php`
- **Specifiek item**: `GET /api/items.php?id=1`

### POST /api/items.php
- **Nieuw item aanmaken**
- Content-Type: `multipart/form-data`
- Parameters: `title`, `description`, `image` (file)

### PUT /api/items.php
- **Item bijwerken**
- Content-Type: `application/json`
- Body: `{"id": 1, "title": "...", "description": "...", "image_path": "..."}`

### DELETE /api/items.php
- **Item verwijderen**
- Content-Type: `application/json`
- Body: `{"id": 1}`

## Database Schema

```sql
CREATE TABLE items (
    id INT IDENTITY(1,1) PRIMARY KEY,
    title NVARCHAR(255) NOT NULL,
    description NVARCHAR(MAX),
    image_path NVARCHAR(500),
    created_at DATETIME2 DEFAULT GETDATE(),
    updated_at DATETIME2 DEFAULT GETDATE()
);
```

## Veiligheid

- ✅ **SQL Injection Protection**: Gebruikt prepared statements
- ✅ **File Upload Validation**: Controleert bestandstype en grootte
- ✅ **XSS Protection**: HTML escaping in output
- ✅ **CSRF Protection**: Kan worden toegevoegd indien nodig

## Troubleshooting

### Database Connectie Problemen
1. Controleer SQL Server service status
2. Verificeer gebruikersnaam en wachtwoord
3. Controleer firewall instellingen
4. Test connectie in SSMS

### Upload Problemen
1. Controleer uploads directory permissies
2. Verificeer PHP upload_max_filesize
3. Controleer post_max_size in php.ini

### PHP SQL Server Extensie
```bash
# Controleer of extensie is geladen
php -m | grep sqlsrv

# Herstart web server na installatie
```

## Browser Ondersteuning

- ✅ Chrome 60+
- ✅ Firefox 55+
- ✅ Safari 12+
- ✅ Edge 79+

## Licentie

Dit project is gemaakt voor educatieve doeleinden.

## Support

Voor vragen of problemen, controleer:
1. PHP error logs
2. SQL Server logs
3. Web server logs
4. Browser developer console
