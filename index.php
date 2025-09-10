<?php
require_once 'includes/user.php';
require_once 'includes/world_wonder.php';
require_once 'includes/media.php';

// Initialize managers
$userManager = new UserManager();
$worldWonderManager = new WorldWonderManager();
$mediaManager = new MediaManager();

// Get current user
$currentUser = $userManager->getCurrentUser();

// Get filters from URL
$filters = array();
if (isset($_GET['search'])) {
    $filters['search'] = $_GET['search'];
}
if (isset($_GET['category'])) {
    $filters['category'] = $_GET['category'];
}
if (isset($_GET['continent'])) {
    $filters['continent'] = $_GET['continent'];
}
if (isset($_GET['status'])) {
    $filters['status'] = $_GET['status'];
}
if (isset($_GET['sort'])) {
    $filters['sort'] = $_GET['sort'];
}

// Only show approved wonders to visitors
if (!$currentUser || !$userManager->hasPermission('view_unapproved_wonders')) {
    $filters['is_approved'] = 1;
}

// Get world wonders
$worldWonders = $worldWonderManager->getAllWorldWonders($filters);

// Get statistics
$statistics = $worldWonderManager->getStatistics();

// Get recent activity (empty array if not available)
$recentActivity = array();
if ($currentUser) {
    // For now, we'll use an empty array. In a real implementation, 
    // you would fetch this from an activity log table
    $recentActivity = array();
}

// Helper function for activity icons
function getActivityIcon($action) {
    $icons = array(
        'created' => 'plus',
        'updated' => 'edit',
        'approved' => 'check',
        'deleted' => 'trash',
        'uploaded' => 'upload',
        'commented' => 'comment'
    );
    
    $action = strtolower($action);
    foreach ($icons as $key => $icon) {
        if (strpos($action, $key) !== false) {
            return $icon;
        }
    }
    
    return 'info'; // Default icon
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Codex Mundi - Database van Wereldwonderen</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="main-header">
            <div class="header-content">
                <div class="logo">
                    <h1><i class="fas fa-globe-americas"></i> Codex Mundi</h1>
                    <p>Database van Wereldwonderen</p>
                </div>
                
                <nav class="main-nav">
                    <a href="index.php" class="nav-link active">
                        <i class="fas fa-home"></i> Home
                    </a>
                    <a href="map.php" class="nav-link">
                        <i class="fas fa-map"></i> Kaart
                    </a>
                    <a href="statistics.php" class="nav-link">
                        <i class="fas fa-chart-bar"></i> Statistieken
                    </a>
                    <?php if ($currentUser && $userManager->hasPermission('manage_users')): ?>
                        <a href="admin.php" class="nav-link">
                            <i class="fas fa-cog"></i> Beheer
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($currentUser): ?>
                        <div class="user-menu">
                            <span>Welkom, <?php echo htmlspecialchars($currentUser['first_name']); ?> (<?php echo htmlspecialchars($currentUser['role_name']); ?>)</span>
                            <a href="logout.php">Uitloggen</a>
                        </div>
                    <?php else: ?>
                        <div class="auth-links">
                            <a href="login.php">Inloggen</a>
                            <a href="register.php">Registreren</a>
                        </div>
                    <?php endif; ?>
                </nav>
            </div>
        </header>

        <!-- Search and Filters -->
        <div class="search-section">
            <form method="GET" class="search-form">
                <div class="search-input-group">
                    <input type="text" name="search" placeholder="Zoek wereldwonderen..." 
                           value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                
                <div class="filters">
                    <select name="category">
                        <option value="">Alle categorieën</option>
                        <option value="classical" <?php echo ($_GET['category'] ?? '') === 'classical' ? 'selected' : ''; ?>>Klassiek</option>
                        <option value="modern" <?php echo ($_GET['category'] ?? '') === 'modern' ? 'selected' : ''; ?>>Modern</option>
                        <option value="natural" <?php echo ($_GET['category'] ?? '') === 'natural' ? 'selected' : ''; ?>>Natuurlijk</option>
                    </select>
                    
                    <select name="continent">
                        <option value="">Alle continenten</option>
                        <option value="Africa" <?php echo ($_GET['continent'] ?? '') === 'Africa' ? 'selected' : ''; ?>>Afrika</option>
                        <option value="Asia" <?php echo ($_GET['continent'] ?? '') === 'Asia' ? 'selected' : ''; ?>>Azië</option>
                        <option value="Europe" <?php echo ($_GET['continent'] ?? '') === 'Europe' ? 'selected' : ''; ?>>Europa</option>
                        <option value="North America" <?php echo ($_GET['continent'] ?? '') === 'North America' ? 'selected' : ''; ?>>Noord-Amerika</option>
                        <option value="South America" <?php echo ($_GET['continent'] ?? '') === 'South America' ? 'selected' : ''; ?>>Zuid-Amerika</option>
                        <option value="Oceania" <?php echo ($_GET['continent'] ?? '') === 'Oceania' ? 'selected' : ''; ?>>Oceanië</option>
                    </select>
                    
                    <select name="status">
                        <option value="">Alle statussen</option>
                        <option value="exists" <?php echo ($_GET['status'] ?? '') === 'exists' ? 'selected' : ''; ?>>Bestaat nog</option>
                        <option value="destroyed" <?php echo ($_GET['status'] ?? '') === 'destroyed' ? 'selected' : ''; ?>>Vernietigd</option>
                        <option value="unknown" <?php echo ($_GET['status'] ?? '') === 'unknown' ? 'selected' : ''; ?>>Onbekend</option>
                    </select>
                    
                    <select name="sort">
                        <option value="">Sorteren op...</option>
                        <option value="name_asc" <?php echo ($_GET['sort'] ?? '') === 'name_asc' ? 'selected' : ''; ?>>Naam A-Z</option>
                        <option value="name_desc" <?php echo ($_GET['sort'] ?? '') === 'name_desc' ? 'selected' : ''; ?>>Naam Z-A</option>
                        <option value="year_asc" <?php echo ($_GET['sort'] ?? '') === 'year_asc' ? 'selected' : ''; ?>>Bouwjaar Oud-Nieuw</option>
                        <option value="year_desc" <?php echo ($_GET['sort'] ?? '') === 'year_desc' ? 'selected' : ''; ?>>Bouwjaar Nieuw-Oud</option>
                        <option value="updated_desc" <?php echo ($_GET['sort'] ?? '') === 'updated_desc' ? 'selected' : ''; ?>>Laatst bijgewerkt</option>
                    </select>
                </div>
            </form>
        </div>

        <!-- Quick Stats -->
        <div class="quick-stats">
            <div class="stat-card">
                <i class="fas fa-globe"></i>
                <div class="stat-info">
                    <h3><?php echo $statistics['total_wonders']; ?></h3>
                    <p>Wereldwonderen</p>
                </div>
            </div>
            <div class="stat-card">
                <i class="fas fa-check-circle"></i>
                <div class="stat-info">
                    <h3><?php echo array_sum(array_column($statistics['by_status'], 'count')); ?></h3>
                    <p>Goedgekeurd</p>
                </div>
            </div>
            <div class="stat-card">
                <i class="fas fa-users"></i>
                <div class="stat-info">
                    <h3><?php echo count($worldWonders); ?></h3>
                    <p>Gevonden</p>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <?php if ($currentUser && $userManager->hasPermission('create_wonders')): ?>
            <div class="actions">
                <a href="world_wonder_form.php">+ Nieuw Wereldwonder</a>
            </div>
        <?php endif; ?>

        <!-- World Wonders Grid -->
        <div class="world-wonders-grid">
            <?php if (empty($worldWonders)): ?>
                <div class="no-results">
                    <i class="fas fa-search"></i>
                    <h3>Geen wereldwonderen gevonden</h3>
                    <p>Probeer andere zoektermen of filters</p>
                </div>
            <?php else: ?>
                <?php foreach ($worldWonders as $wonder): ?>
                    <div class="world-wonder-card">
                        <div class="wonder-image">
                            <?php
                            $media = $mediaManager->getMediaByWorldWonderId($wonder['id']);
                            $primaryImage = null;
                            foreach ($media as $m) {
                                if ($m['is_primary'] && $m['is_approved'] && strpos($m['file_type'], 'image/') === 0) {
                                    $primaryImage = $m;
                                    break;
                                }
                            }
                            if (!$primaryImage && count($media) > 0) {
                                foreach ($media as $m) {
                                    if ($m['is_approved'] && strpos($m['file_type'], 'image/') === 0) {
                                        $primaryImage = $m;
                                        break;
                                    }
                                }
                            }
                            ?>
                            
                            <?php if ($primaryImage): ?>
                                <img src="<?php echo htmlspecialchars($primaryImage['file_path']); ?>" 
                                     alt="<?php echo htmlspecialchars($wonder['name']); ?>">
                            <?php else: ?>
                                <div class="no-image">
                                    <i class="fas fa-image"></i>
                                    <span>Geen afbeelding</span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="wonder-status status-<?php echo $wonder['status']; ?>">
                                <?php
                                $statusLabels = array(
                                    'exists' => 'Bestaat nog',
                                    'destroyed' => 'Vernietigd',
                                    'unknown' => 'Onbekend'
                                );
                                echo $statusLabels[$wonder['status']] ?? $wonder['status'];
                                ?>
                            </div>
                        </div>
                        
                        <div class="wonder-content">
                            <h3><?php echo htmlspecialchars($wonder['name']); ?></h3>
                            <p class="wonder-location">
                                <i class="fas fa-map-marker-alt"></i>
                                <?php echo htmlspecialchars($wonder['city'] . ', ' . $wonder['country']); ?>
                            </p>
                            <p class="wonder-description">
                                <?php echo htmlspecialchars(substr($wonder['description'], 0, 150)) . '...'; ?>
                            </p>
                            
                            <div class="wonder-meta">
                                <span class="category"><?php echo htmlspecialchars($wonder['category']); ?></span>
                                <?php if ($wonder['construction_year']): ?>
                                    <span class="year"><?php echo $wonder['construction_year']; ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="wonder-actions">
                            <a href="world_wonder_detail.php?id=<?php echo $wonder['id']; ?>">Bekijken</a>
                            
                            <?php if ($currentUser && $userManager->hasPermission('edit_wonders')): ?>
                                <a href="world_wonder_form.php?id=<?php echo $wonder['id']; ?>">Bewerken</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Recent Activity -->
        <?php if ($currentUser && is_array($recentActivity) && count($recentActivity) > 0): ?>
            <div class="recent-activity">
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
        <?php endif; ?>
    </div>

    <script src="js/script.js"></script>
</body>
</html>
