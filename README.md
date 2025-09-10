# Codex Mundi - Database van Wereldwonderen

Een complete webapplicatie voor het beheren van wereldwonderen met rollenbeheer, media upload, kaart functionaliteit en uitgebreide statistieken.

## 🚀 **Snelle Start**

### **1. Database Setup**
1. Open **SQL Server Management Studio**
2. Voer `database_schema_codex_mundi.sql` uit (F5)
3. Database `CodexMundi` wordt aangemaakt met alle tabellen en voorbeelddata

### **2. Laragon Starten**
1. Start **Laragon**
2. Ga naar: `http://localhost/project%20testen%20en%20acceptatie/`

### **3. Testen**
- **Bezoeker**: Bekijk wereldwonderen, zoek en filter
- **Registreren**: Maak account aan als Onderzoeker
- **Inloggen**: Test alle rollen en functionaliteiten

## ✨ **Functionaliteiten**

### **🔐 Gebruikersrollen & Rechten**
- **Bezoeker**: Alleen wereldwonderen bekijken
- **Onderzoeker**: Nieuwe wereldwonderen aanmaken, eigen bijdragen bewerken
- **Redacteur**: Bijdragen controleren en goedkeuren, metadata toevoegen
- **Archivaris**: Historische gegevens toevoegen, GPS-coördinaten, sorteren
- **Beheerder**: Alles beheren, gebruikers en rollen aanmaken

### **🌍 Wereldwonderen Beheer**
- **CRUD Operaties**: Aanmaken, bekijken, bewerken, verwijderen
- **Volledige Informatie**: Naam, beschrijving, historische info, bouwjaar, status
- **Media Upload**: Foto's en documenten met goedkeuringssysteem
- **GPS Locaties**: Coördinaten opslaan en op kaart bekijken

### **🔍 Zoeken & Filteren**
- **Zoeken op naam** van wereldwonder
- **Filteren op categorie** (klassiek, modern, natuurlijk)
- **Filteren op werelddeel** (Afrika, Azië, Europa, etc.)
- **Sorteren op alfabet en bouwjaar**
- **Status filter** (bestaat nog wel/niet)

### **📊 Statistieken & Rapporten**
- **Overzicht per werelddeel** (aantal wonderen)
- **Meest bekeken wereldwonderen**
- **Lijst van laatst bewerkte wonderen**
- **Export mogelijkheden** (PDF/CSV)
- **Interactieve grafieken** met Chart.js

### **🗺️ Kaart & Locatie**
- **Interactieve kaart** met Leaflet.js
- **GPS-coördinaten opslaan**
- **Wereldwonderen op kaart bekijken**
- **Klik op kaart = detailpagina**
- **Locatie aanpassen en controleren**

### **🔒 Beveiliging & Logging**
- **Veilige login** met authenticatie en rollenbeheer
- **Website veilig te gebruiken zonder login** (voor bezoekers)
- **Logboek van wijzigingen** (wie heeft wat aangepast)
- **Notificaties/meldingen** bij nieuwe bijdragen

## 📁 **Project Structuur**

```
project/
├── config/
│   └── database.php              # Database configuratie
├── includes/
│   ├── user.php                 # Gebruikerssysteem
│   ├── world_wonder.php         # Wereldwonderen CRUD
│   ├── media.php                # Media upload systeem
│   ├── activity_log.php         # Activiteit logging
│   └── notification.php         # Notificatie systeem
├── css/
│   └── style.css                # Responsive styling
├── js/
│   └── script.js                # Frontend functionaliteit
├── uploads/                     # Geüploade bestanden
├── index.php                    # Hoofdpagina
├── map.php                      # Kaart functionaliteit
├── statistics.php               # Statistieken en rapporten
├── login.php                    # Inloggen
├── register.php                 # Registreren
├── logout.php                   # Uitloggen
└── database_schema_codex_mundi.sql  # Complete database schema
```

## 🎯 **Hoe te Gebruiken**

### **Wereldwonderen Toevoegen**
1. **Registreer** als Onderzoeker
2. **Klik op "Nieuw Wereldwonder"**
3. **Vul alle gegevens in** (naam, beschrijving, locatie, etc.)
4. **Upload afbeeldingen** (optioneel)
5. **Klik op "Opslaan"**

### **Wereldwonderen Bewerken**
1. **Hover over een wereldwonder kaart**
2. **Klik op het potlood icoon**
3. **Pas de gegevens aan**
4. **Klik op "Opslaan"**

### **Media Goedkeuren (Redacteur)**
1. **Log in als Redacteur**
2. **Ga naar beheer sectie**
3. **Controleer geüploade media**
4. **Klik op "Goedkeuren"**

### **Kaart Bekijken**
1. **Ga naar "Kaart" in het menu**
2. **Bekijk wereldwonderen op de kaart**
3. **Klik op markers voor details**
4. **Gebruik filters voor specifieke categorieën**

## 🔧 **Technische Details**

### **Backend**
- **PHP 7.4+** (geen framework)
- **Microsoft SQL Server** database
- **Rollenbeheer** met permissions
- **Activiteit logging** en notificaties

### **Frontend**
- **HTML5, CSS3, JavaScript** (responsive)
- **Leaflet.js** voor kaart functionaliteit
- **Chart.js** voor statistieken
- **Font Awesome** voor iconen

### **Database**
- **Complete tabelstructuur** voor alle functionaliteiten
- **Foreign keys** en relaties
- **Indexen** voor performance
- **Voorbeelddata** voor testing

## 🌐 **Browser Ondersteuning**

- ✅ Chrome 60+
- ✅ Firefox 55+
- ✅ Safari 12+
- ✅ Edge 79+

## 📱 **Responsive Design**

- **Desktop**: Volledige functionaliteit met grid layout
- **Tablet**: Aangepaste layout voor touch devices
- **Mobile**: Single column layout met touch-friendly interface

## 🚀 **Klaar voor Gebruik!**

Het Codex Mundi systeem is volledig functioneel en klaar voor gebruik. Start Laragon, voer de database setup uit en begin met het beheren van wereldwonderen!

## 📞 **Support**

Voor vragen of problemen:
1. Controleer of Laragon draait
2. Controleer of database `CodexMundi` bestaat
3. Controleer de browser console voor errors
4. Herstart Laragon als nodig

**Veel plezier met Codex Mundi!** 🎉