<?php
require_once 'includes/user.php';
require_once 'includes/world_wonder.php';

$userManager = new UserManager();
$worldWonderManager = new WorldWonderManager();

$currentUser = $userManager->getCurrentUser();

// Get statistics
$statistics = $worldWonderManager->getStatistics();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistieken - Codex Mundi</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container">
        <header class="main-header">
            <div class="header-content">
                <div class="logo">
                    <h1>Codex Mundi</h1>
                    <p>Statistieken en Rapporten</p>
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

        <div class="statistics-dashboard">
            <!-- Overview Stats -->
            <div class="stats-overview">
                <h2>Overzicht</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3><?= $statistics['total_wonders'] ?></h3>
                        <p>Totaal Wereldwonderen</p>
                    </div>
                    <div class="stat-card">
                        <h3><?= array_sum(array_column($statistics['by_status'], 'count')) ?></h3>
                        <p>Goedgekeurd</p>
                    </div>
                    <div class="stat-card">
                        <h3><?= count($statistics['by_category']) ?></h3>
                        <p>Categorieën</p>
                    </div>
                    <div class="stat-card">
                        <h3><?= count($statistics['by_continent']) ?></h3>
                        <p>Continenten</p>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="charts-row">
                <!-- By Category Chart -->
                <div class="chart-container">
                    <h3>Per Categorie</h3>
                    <canvas id="categoryChart"></canvas>
                </div>

                <!-- By Continent Chart -->
                <div class="chart-container">
                    <h3>Per Continent</h3>
                    <canvas id="continentChart"></canvas>
                </div>
            </div>

            <!-- Status Chart -->
            <div class="chart-container full-width">
                <h3>Status Verdeling</h3>
                <canvas id="statusChart"></canvas>
            </div>

            <!-- Detailed Tables -->
            <div class="tables-row">
                <!-- By Category Table -->
                <div class="table-container">
                    <h3>Wereldwonderen per Categorie</h3>
                    <table class="stats-table">
                        <thead>
                            <tr>
                                <th>Categorie</th>
                                <th>Aantal</th>
                                <th>Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $total = $statistics['total_wonders'];
                            foreach ($statistics['by_category'] as $category): 
                                $percentage = $total > 0 ? round(($category['count'] / $total) * 100, 1) : 0;
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($category['category']) ?></td>
                                    <td><?= $category['count'] ?></td>
                                    <td><?= $percentage ?>%</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- By Continent Table -->
                <div class="table-container">
                    <h3>Wereldwonderen per Continent</h3>
                    <table class="stats-table">
                        <thead>
                            <tr>
                                <th>Continent</th>
                                <th>Aantal</th>
                                <th>Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($statistics['by_continent'] as $continent): 
                                $percentage = $total > 0 ? round(($continent['count'] / $total) * 100, 1) : 0;
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($continent['continent']) ?></td>
                                    <td><?= $continent['count'] ?></td>
                                    <td><?= $percentage ?>%</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Activity -->
            <?php if (!empty($statistics['recent'])): ?>
                <div class="recent-activity">
                    <h3>Meest Recente Wereldwonderen</h3>
                    <div class="recent-list">
                        <?php foreach ($statistics['recent'] as $wonder): ?>
                            <div class="recent-item">
                                <div class="recent-info">
                                    <h4><?= htmlspecialchars($wonder['name']) ?></h4>
                                    <p><?= htmlspecialchars($wonder['category']) ?> • <?= htmlspecialchars($wonder['continent']) ?></p>
                                </div>
                                <div class="recent-meta">
                                    <span class="status status-<?= $wonder['status'] ?>">
                                        <?php
                                        $statusLabels = array(
                                            'exists' => 'Bestaat nog',
                                            'destroyed' => 'Vernietigd',
                                            'unknown' => 'Onbekend'
                                        );
                                        echo $statusLabels[$wonder['status']] ?? $wonder['status'];
                                        ?>
                                    </span>
                                    <small><?= date('d-m-Y', strtotime($wonder['updated_at'])) ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Export Options -->
            <?php if ($currentUser && $userManager->hasPermission('export_data')): ?>
                <div class="export-section">
                    <h3>Export Opties</h3>
                    <div class="export-buttons">
                        <a href="export.php?format=pdf" class="btn btn-primary">Exporteer als PDF</a>
                        <a href="export.php?format=csv" class="btn btn-secondary">Exporteer als CSV</a>
                        <a href="export.php?format=json" class="btn btn-secondary">Exporteer als JSON</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Category Chart
        const categoryData = <?= json_encode($statistics['by_category']) ?>;
        const categoryLabels = categoryData.map(item => item.category);
        const categoryCounts = categoryData.map(item => item.count);

        new Chart(document.getElementById('categoryChart'), {
            type: 'doughnut',
            data: {
                labels: categoryLabels,
                datasets: [{
                    data: categoryCounts,
                    backgroundColor: ['#3498db', '#e74c3c', '#f39c12', '#27ae60', '#9b59b6']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Continent Chart
        const continentData = <?= json_encode($statistics['by_continent']) ?>;
        const continentLabels = continentData.map(item => item.continent);
        const continentCounts = continentData.map(item => item.count);

        new Chart(document.getElementById('continentChart'), {
            type: 'bar',
            data: {
                labels: continentLabels,
                datasets: [{
                    label: 'Aantal Wereldwonderen',
                    data: continentCounts,
                    backgroundColor: '#3498db'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Status Chart
        const statusData = <?= json_encode($statistics['by_status']) ?>;
        const statusLabels = statusData.map(item => {
            const labels = {
                'exists': 'Bestaat nog',
                'destroyed': 'Vernietigd',
                'unknown': 'Onbekend'
            };
            return labels[item.status] || item.status;
        });
        const statusCounts = statusData.map(item => item.count);

        new Chart(document.getElementById('statusChart'), {
            type: 'pie',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusCounts,
                    backgroundColor: ['#27ae60', '#e74c3c', '#f39c12']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>

    <style>
        .statistics-dashboard {
            display: grid;
            gap: 30px;
        }

        .stats-overview {
            background: white;
            padding: 25px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .stats-overview h2 {
            color: #2c3e50;
            margin-bottom: 20px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .stat-card {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 5px;
        }

        .stat-card h3 {
            font-size: 2.5em;
            color: #3498db;
            margin-bottom: 10px;
        }

        .stat-card p {
            color: #7f8c8d;
            font-size: 1.1em;
        }

        .charts-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .chart-container {
            background: white;
            padding: 25px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .chart-container.full-width {
            grid-column: 1 / -1;
        }

        .chart-container h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            text-align: center;
        }

        .tables-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .table-container {
            background: white;
            padding: 25px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .table-container h3 {
            color: #2c3e50;
            margin-bottom: 20px;
        }

        .stats-table {
            width: 100%;
            border-collapse: collapse;
        }

        .stats-table th,
        .stats-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ecf0f1;
        }

        .stats-table th {
            background: #f8f9fa;
            font-weight: bold;
            color: #2c3e50;
        }

        .stats-table tr:hover {
            background: #f8f9fa;
        }

        .recent-activity {
            background: white;
            padding: 25px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .recent-activity h3 {
            color: #2c3e50;
            margin-bottom: 20px;
        }

        .recent-list {
            display: grid;
            gap: 15px;
        }

        .recent-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border: 1px solid #ecf0f1;
            border-radius: 5px;
            background: #f8f9fa;
        }

        .recent-info h4 {
            margin: 0 0 5px 0;
            color: #2c3e50;
        }

        .recent-info p {
            margin: 0;
            color: #7f8c8d;
        }

        .recent-meta {
            text-align: right;
        }

        .recent-meta small {
            display: block;
            color: #7f8c8d;
            margin-top: 5px;
        }

        .export-section {
            background: white;
            padding: 25px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .export-section h3 {
            color: #2c3e50;
            margin-bottom: 20px;
        }

        .export-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .status {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: bold;
            color: white;
        }

        .status-exists { background: #27ae60; }
        .status-destroyed { background: #e74c3c; }
        .status-unknown { background: #f39c12; }

        @media (max-width: 768px) {
            .charts-row,
            .tables-row {
                grid-template-columns: 1fr;
            }
            
            .recent-item {
                flex-direction: column;
                text-align: center;
                gap: 10px;
            }
            
            .recent-meta {
                text-align: center;
            }
            
            .export-buttons {
                flex-direction: column;
            }
        }
    </style>
</body>
</html>