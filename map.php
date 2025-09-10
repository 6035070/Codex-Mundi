<?php
session_start();
require_once 'config/database.php';
require_once 'includes/user.php';
require_once 'includes/world_wonder.php';

// Initialize managers
$userManager = new UserManager();
$worldWonderManager = new WorldWonderManager();

// Get current user
$currentUser = null;
if (isset($_SESSION['user_id'])) {
    $currentUser = $userManager->getUserById($_SESSION['user_id']);
}

// Get world wonders for map
$worldWonders = $worldWonderManager->getWorldWondersForMap();
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wereldwonderen Kaart - Codex Mundi</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #map {
            height: 70vh;
            width: 100%;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .map-container {
            position: relative;
            margin: 20px 0;
        }
        
        .map-controls {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 1000;
            background: white;
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .map-legend {
            position: absolute;
            bottom: 10px;
            left: 10px;
            z-index: 1000;
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            margin: 5px 0;
        }
        
        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            margin-right: 10px;
        }
        
        .wonder-popup {
            max-width: 300px;
        }
        
        .wonder-popup h3 {
            margin: 0 0 10px 0;
            color: #2c3e50;
        }
        
        .wonder-popup p {
            margin: 5px 0;
            color: #6c757d;
        }
        
        .wonder-popup .status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-top: 10px;
        }
        
        .status-exists {
            background: #d4edda;
            color: #155724;
        }
        
        .status-destroyed {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-unknown {
            background: #fff3cd;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="main-header">
            <div class="header-content">
                <div class="logo">
                    <h1><i class="fas fa-map"></i> Wereldwonderen Kaart</h1>
                    <p>Bekijk wereldwonderen op de kaart</p>
                </div>
                
                <nav class="main-nav">
                    <a href="index.php" class="nav-link">
                        <i class="fas fa-home"></i> Home
                    </a>
                    <a href="map.php" class="nav-link active">
                        <i class="fas fa-map"></i> Kaart
                    </a>
                    <a href="statistics.php" class="nav-link">
                        <i class="fas fa-chart-bar"></i> Statistieken
                    </a>
                    
                    <?php if ($currentUser): ?>
                        <div class="user-menu">
                            <div class="user-info">
                                <i class="fas fa-user"></i>
                                <?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </nav>
            </div>
        </header>

        <!-- Map Container -->
        <div class="map-container">
            <div id="map"></div>
            
            <!-- Map Controls -->
            <div class="map-controls">
                <button id="centerMap" class="btn btn-sm btn-primary">
                    <i class="fas fa-crosshairs"></i> Center
                </button>
                <button id="toggleFullscreen" class="btn btn-sm btn-secondary">
                    <i class="fas fa-expand"></i> Volledig scherm
                </button>
            </div>
            
            <!-- Map Legend -->
            <div class="map-legend">
                <h4>Legenda</h4>
                <div class="legend-item">
                    <div class="legend-color" style="background: #28a745;"></div>
                    <span>Bestaat nog</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: #dc3545;"></div>
                    <span>Vernietigd</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: #ffc107;"></div>
                    <span>Onbekend</span>
                </div>
            </div>
        </div>

        <!-- World Wonders List -->
        <div class="world-wonders-list">
            <h3><i class="fas fa-list"></i> Wereldwonderen op de Kaart</h3>
            <div class="wonders-grid">
                <?php foreach ($worldWonders as $wonder): ?>
                    <div class="wonder-item" data-lat="<?php echo $wonder['latitude']; ?>" data-lng="<?php echo $wonder['longitude']; ?>">
                        <div class="wonder-info">
                            <h4><?php echo htmlspecialchars($wonder['name']); ?></h4>
                            <p class="wonder-category"><?php echo htmlspecialchars($wonder['category']); ?></p>
                            <p class="wonder-status status-<?php echo $wonder['status']; ?>">
                                <?php
                                $statusLabels = array(
                                    'exists' => 'Bestaat nog',
                                    'destroyed' => 'Vernietigd',
                                    'unknown' => 'Onbekend'
                                );
                                echo $statusLabels[$wonder['status']] ?? $wonder['status'];
                                ?>
                            </p>
                        </div>
                        <div class="wonder-actions">
                            <a href="world_wonder_detail.php?id=<?php echo $wonder['id']; ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i> Bekijken
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Initialize map
        const map = L.map('map').setView([20, 0], 2);
        
        // Add tile layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);
        
        // World wonders data
        const worldWonders = <?php echo json_encode($worldWonders); ?>;
        
        // Status colors
        const statusColors = {
            'exists': '#28a745',
            'destroyed': '#dc3545',
            'unknown': '#ffc107'
        };
        
        // Add markers for each world wonder
        worldWonders.forEach(wonder => {
            const marker = L.circleMarker([wonder.latitude, wonder.longitude], {
                radius: 8,
                fillColor: statusColors[wonder.status] || '#6c757d',
                color: '#fff',
                weight: 2,
                opacity: 1,
                fillOpacity: 0.8
            }).addTo(map);
            
            // Add popup
            marker.bindPopup(`
                <div class="wonder-popup">
                    <h3>${wonder.name}</h3>
                    <p><strong>Categorie:</strong> ${wonder.category}</p>
                    <p><strong>Status:</strong> 
                        <span class="status status-${wonder.status}">
                            ${wonder.status === 'exists' ? 'Bestaat nog' : 
                              wonder.status === 'destroyed' ? 'Vernietigd' : 'Onbekend'}
                        </span>
                    </p>
                    <a href="world_wonder_detail.php?id=${wonder.id}" class="btn btn-sm btn-primary">
                        <i class="fas fa-eye"></i> Bekijken
                    </a>
                </div>
            `);
            
            // Add click event to wonder items
            const wonderItem = document.querySelector(`[data-lat="${wonder.latitude}"][data-lng="${wonder.longitude}"]`);
            if (wonderItem) {
                wonderItem.addEventListener('click', () => {
                    map.setView([wonder.latitude, wonder.longitude], 10);
                    marker.openPopup();
                });
            }
        });
        
        // Center map button
        document.getElementById('centerMap').addEventListener('click', () => {
            if (worldWonders.length > 0) {
                const group = new L.featureGroup();
                worldWonders.forEach(wonder => {
                    group.addLayer(L.marker([wonder.latitude, wonder.longitude]));
                });
                map.fitBounds(group.getBounds().pad(0.1));
            } else {
                map.setView([20, 0], 2);
            }
        });
        
        // Fullscreen toggle
        document.getElementById('toggleFullscreen').addEventListener('click', () => {
            const mapContainer = document.getElementById('map');
            if (!document.fullscreenElement) {
                mapContainer.requestFullscreen();
            } else {
                document.exitFullscreen();
            }
        });
        
        // Fit map to show all markers on load
        if (worldWonders.length > 0) {
            const group = new L.featureGroup();
            worldWonders.forEach(wonder => {
                group.addLayer(L.marker([wonder.latitude, wonder.longitude]));
            });
            map.fitBounds(group.getBounds().pad(0.1));
        }
    </script>
</body>
</html>
