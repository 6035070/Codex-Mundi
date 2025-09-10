# Database Setup Instructies

## Probleem Opgelost
De originele error `Call to undefined function sqlsrv_connect()` is opgelost door over te schakelen van SQL Server naar MySQL.

## Stappen om de database in te stellen:

### 1. Start Laragon
- Open Laragon
- Start Apache en MySQL services

### 2. Voer het database script uit in HeidiSQL
- Open HeidiSQL (zoals je al hebt gedaan)
- Selecteer de `codexmundi` database
- Klik op "Query" knop
- Kopieer en plak de inhoud van `database_setup.sql`
- Klik op "Execute" (groene pijl) of druk F9

### 3. Controleer de tabellen
Na het uitvoeren zou je moeten zien:
- `users` tabel (met admin gebruiker)
- `sessions` tabel
- `world_wonders` tabel (met 5 voorbeeld wereldwonderen)
- `media` tabel
- `tags` tabel
- `world_wonder_tags` tabel
- `activity_logs` tabel

### 4. Test de verbinding
- Open je website in de browser
- De database error zou nu opgelost moeten zijn

## Database Configuratie
- **Host**: localhost
- **Database**: codex_mundi
- **Username**: root
- **Password**: (leeg)

## Default Login
- **Username**: admin
- **Password**: admin123

## Voordelen van MySQL over SQL Server:
- ✅ Geen extra drivers nodig
- ✅ Werkt out-of-the-box met Laragon
- ✅ Sneller en lichter
- ✅ Gratis en open source
- ✅ Betere ondersteuning voor web development
