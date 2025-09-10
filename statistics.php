<?php
session_start();
require_once 'config/database.php';
require_once 'includes/user.php';
require_once 'includes/world_wonder.php';
require_once 'includes/activity_log.php';

// Initialize managers
$userManager = new UserManager();
$worldWonderManager = new WorldWonderManager();
$activityLogManager = new ActivityLogManager();

// Get current user
$currentUser = null;
if (isset($_SESSION['user_id'])) {
    $currentUser = $userManager->getUserById($_SESSION['user_id']);
}

// Get statistics
$statistics = $worldWonderManager->getStatistics();

// Get recent activity
$recentActivity = $activityLogManager->getRecentActivity(10);

// Get activity by user
$userActivity = array();
if ($currentUser) {
    $userActivity = $activityLogManager->getUserActivity($currentUser['id'], 10);
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistieken - Codex Mundi</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .stat-card h3 {
            margin: 0 0 15px 0;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .chart-container {
            position: relative;
            height: 300px;
            margin: 20px 0;
        }
        
        .activity-list {
            max-height: 400px;
            overflow-y: auto;
        }
        
        .activity-item {
            display: flex;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }
        
        .activity-content {
            flex: 1;
        }
        
        .activity-content p {
            margin: 0 0 5px 0;
            font-weight: 600;
        }
        
        .activity-content small {
            color: #6c757d;
        }
        
        .export-buttons {
            margin: 20px 0;
            text-align: center;
        }
        
        .export-buttons .btn {
            margin: 0 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="main-header">
            <div class="header-content">
                <div class="logo">
                    <h1><i class="fas fa-chart-bar"></i> Statistieken</h1>
                    <p>Overzicht van Codex Mundi data</p>
                </div>
                
                <nav class="main-nav">
                    <a href="index.php" class="nav-link">
                        <i class="fas fa-home"></i> Home
                    </a>
                    <a href="map.php" class="nav-link">
                        <i class="fas fa-map"></i> Kaart
                    </a>
                    <a href="statistics.php" class="nav-link active">
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

        <!-- Export Buttons -->
        <div class="export-buttons">
            <button class="btn btn-primary" onclick="exportToPDF()">
                <i class="fas fa-file-pdf"></i> Exporteer naar PDF
            </button>
            <button class="btn btn-secondary" onclick="exportToCSV()">
                <i class="fas fa-file-csv"></i> Exporteer naar CSV
            </button>
        </div>

        <!-- Overview Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3><i class="fas fa-globe"></i> Totaal Wereldwonderen</h3>
                <div class="stat-number"><?php echo $statistics['total_wonders']; ?></div>
            </div>
            
            <div class="stat-card">
                <h3><i class="fas fa-check-circle"></i> Goedgekeurd</h3>
                <div class="stat-number">
                    <?php 
                    $approved = array_sum(array_column($statistics['by_status'], 'count'));
                    echo $approved;
                    ?>
                </div>
            </div>
            
            <div class="stat-card">
                <h3><i class="fas fa-users"></i> Actieve Gebruikers</h3>
                <div class="stat-number"><?php echo count($userManager->getAllUsers()); ?></div>
            </div>
        </div>

        <!-- Charts -->
        <div class="stats-grid">
            <!-- Category Chart -->
            <div class="stat-card">
                <h3><i class="fas fa-chart-pie"></i> Per Categorie</h3>
                <div class="chart-container">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
            
            <!-- Continent Chart -->
            <div class="stat-card">
                <h3><i class="fas fa-chart-bar"></i> Per Continent</h3>
                <div class="chart-container">
                    <canvas id="continentChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Status Chart -->
        <div class="stat-card">
            <h3><i class="fas fa-chart-donut"></i> Per Status</h3>
            <div class="chart-container">
                <canvas id="statusChart"></canvas>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3><i class="fas fa-clock"></i> Recente Activiteit</h3>
                <div class="activity-list">
                    <?php foreach ($recentActivity as $activity): ?>
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-<?php echo getActivityIcon($activity['action']); ?>"></i>
                            </div>
                            <div class="activity-content">
                                <p><?php echo htmlspecialchars($activity['action']); ?></p>
                                <small>
                                    door <?php echo htmlspecialchars($activity['username']); ?> 
                                    op <?php echo date('d-m-Y H:i', strtotime($activity['created_at'])); ?>
                                </small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <?php if ($currentUser && count($userActivity) > 0): ?>
                <div class="stat-card">
                    <h3><i class="fas fa-user"></i> Jouw Activiteit</h3>
                    <div class="activity-list">
                        <?php foreach ($userActivity as $activity): ?>
                            <div class="activity-item">
                                <div class="activity-icon">
                                    <i class="fas fa-<?php echo getActivityIcon($activity['action']); ?>"></i>
                                </div>
                                <div class="activity-content">
                                    <p><?php echo htmlspecialchars($activity['action']); ?></p>
                                    <small>
                                        op <?php echo date('d-m-Y H:i', strtotime($activity['created_at'])); ?>
                                    </small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Recent Wonders -->
        <div class="stat-card">
            <h3><i class="fas fa-star"></i> Meest Recente Wereldwonderen</h3>
            <div class="world-wonders-grid">
                <?php foreach ($statistics['recent'] as $wonder): ?>
                    <div class="world-wonder-card">
                        <div class="wonder-content">
                            <h4><?php echo htmlspecialchars($wonder['name']); ?></h4>
                            <p class="wonder-location">
                                <i class="fas fa-map-marker-alt"></i>
                                <?php echo htmlspecialchars($wonder['city'] . ', ' . $wonder['country']); ?>
                            </p>
                            <p class="wonder-meta">
                                <span class="category"><?php echo htmlspecialchars($wonder['category']); ?></span>
                                <span class="status status-<?php echo $wonder['status']; ?>">
                                    <?php
                                    $statusLabels = array(
                                        'exists' => 'Bestaat nog',
                                        'destroyed' => 'Vernietigd',
                                        'unknown' => 'Onbekend'
                                    );
                                    echo $statusLabels[$wonder['status']] ?? $wonder['status'];
                                    ?>
                                </span>
                            </p>
                            <small class="wonder-date">
                                Toegevoegd op <?php echo date('d-m-Y', strtotime($wonder['created_at'])); ?>
                                door <?php echo htmlspecialchars($wonder['created_by_username']); ?>
                            </small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script>
        // Category Chart
        const categoryData = <?php echo json_encode($statistics['by_category']); ?>;
        const categoryChart = new Chart(document.getElementById('categoryChart'), {
            type: 'doughnut',
            data: {
                labels: categoryData.map(item => item.category),
                datasets: [{
                    data: categoryData.map(item => item.count),
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // Continent Chart
        const continentData = <?php echo json_encode($statistics['by_continent']); ?>;
        const continentChart = new Chart(document.getElementById('continentChart'), {
            type: 'bar',
            data: {
                labels: continentData.map(item => item.continent),
                datasets: [{
                    label: 'Aantal wereldwonderen',
                    data: continentData.map(item => item.count),
                    backgroundColor: '#36A2EB'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Status Chart
        const statusData = <?php echo json_encode($statistics['by_status']); ?>;
        const statusChart = new Chart(document.getElementById('statusChart'), {
            type: 'pie',
            data: {
                labels: statusData.map(item => item.status === 'exists' ? 'Bestaat nog' : 
                                              item.status === 'destroyed' ? 'Vernietigd' : 'Onbekend'),
                datasets: [{
                    data: statusData.map(item => item.count),
                    backgroundColor: ['#28a745', '#dc3545', '#ffc107']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // Export functions
        function exportToPDF() {
            window.print();
        }

        function exportToCSV() {
            // Simple CSV export
            const data = [
                ['Categorie', 'Aantal'],
                ...categoryData.map(item => [item.category, item.count])
            ];
            
            const csvContent = data.map(row => row.join(',')).join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'codex_mundi_statistics.csv';
            a.click();
        }
    </script>
</body>
</html>

<?php
// Helper function for activity icons
function getActivityIcon($action) {
    $icons = array(
        'create' => 'plus',
        'update' => 'edit',
        'delete' => 'trash',
        'approve' => 'check',
        'upload' => 'upload',
        'login' => 'sign-in-alt',
        'logout' => 'sign-out-alt'
    );
    
    foreach ($icons as $key => $icon) {
        if (strpos(strtolower($action), $key) !== false) {
            return $icon;
        }
    }
    
    return 'info';
}
?>
