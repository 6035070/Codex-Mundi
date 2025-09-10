<?php
require_once 'includes/user.php';
require_once 'includes/world_wonder.php';

$userManager = new UserManager();
$worldWonderManager = new WorldWonderManager();

$currentUser = $userManager->getCurrentUser();

// Get world wonders for map
$wonders = $worldWonderManager->getWorldWondersForMap();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wereldkaart - Codex Mundi</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>
<body>
    <div class="container">
        <header class="main-header">
            <div class="header-content">
                <div class="logo">
                    <h1>Codex Mundi</h1>
                    <p>Wereldkaart van Wereldwonderen</p>
                </div>
                <nav class="main-nav">
                    <a href="index.php">← Terug naar overzicht</a>
                    <?php if ($currentUser): ?>
                        <a href="logout.php">Uitloggen</a>
                    <?php else: ?>
                        <a href="login.php">Inloggen</a>
                    <?php endif; ?>
                </nav>
            </div>
        </header>

        <div class="map-container">
            <div id="map" style="height: 600px; width: 100%; border-radius: 5px;"></div>
        </div>

        <div class="map-legend">
            <h3>Legenda</h3>
            <div class="legend-items">
                <div class="legend-item">
                    <span class="legend-color exists"></span>
                    <span>Bestaat nog</span>
                </div>
                <div class="legend-item">
                    <span class="legend-color destroyed"></span>
                    <span>Vernietigd</span>
                </div>
                <div class="legend-item">
                    <span class="legend-color unknown"></span>
                    <span>Onbekend</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Initialize map
        const map = L.map('map').setView([20, 0], 2);

        // Add tile layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // World wonders data
        const wonders = <?= json_encode($wonders) ?>;

        // Add markers for each wonder
        wonders.forEach(wonder => {
            if (wonder.latitude && wonder.longitude) {
                // Choose marker color based on status
                let markerColor = '#3498db'; // default blue
                if (wonder.status === 'exists') markerColor = '#27ae60'; // green
                else if (wonder.status === 'destroyed') markerColor = '#e74c3c'; // red
                else if (wonder.status === 'unknown') markerColor = '#f39c12'; // orange

                // Create custom icon
                const customIcon = L.divIcon({
                    className: 'custom-marker',
                    html: `<div style="background-color: ${markerColor}; width: 20px; height: 20px; border-radius: 50%; border: 2px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3);"></div>`,
                    iconSize: [20, 20],
                    iconAnchor: [10, 10]
                });

                // Create popup content
                const popupContent = `
                    <div style="min-width: 200px;">
                        <h3 style="margin: 0 0 10px 0; color: #2c3e50;">${wonder.name}</h3>
                        <p style="margin: 5px 0;"><strong>Categorie:</strong> ${wonder.category}</p>
                        <p style="margin: 5px 0;"><strong>Status:</strong> ${wonder.status}</p>
                        <div style="margin-top: 15px;">
                            <a href="world_wonder_detail.php?id=${wonder.id}" style="background: #3498db; color: white; padding: 8px 15px; text-decoration: none; border-radius: 3px; display: inline-block;">Bekijk Details</a>
                        </div>
                    </div>
                `;

                // Add marker to map
                L.marker([wonder.latitude, wonder.longitude], { icon: customIcon })
                    .addTo(map)
                    .bindPopup(popupContent);
            }
        });

        // Fit map to show all markers
        if (wonders.length > 0) {
            const group = new L.featureGroup();
            wonders.forEach(wonder => {
                if (wonder.latitude && wonder.longitude) {
                    group.addLayer(L.marker([wonder.latitude, wonder.longitude]));
                }
            });
            map.fitBounds(group.getBounds().pad(0.1));
        }
    </script>

    <style>
        .map-container {
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .map-legend {
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .map-legend h3 {
            color: #2c3e50;
            margin-bottom: 15px;
        }

        .legend-items {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            border: 2px solid white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.3);
        }

        .legend-color.exists {
            background-color: #27ae60;
        }

        .legend-color.destroyed {
            background-color: #e74c3c;
        }

        .legend-color.unknown {
            background-color: #f39c12;
        }

        .custom-marker {
            background: transparent !important;
            border: none !important;
        }

        @media (max-width: 768px) {
            .map-container {
                padding: 10px;
            }
            
            #map {
                height: 400px !important;
            }
            
            .legend-items {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</body>
</html>