<?php
require_once 'includes/user.php';
require_once 'includes/world_wonder.php';
require_once 'includes/media.php';

$userManager = new UserManager();
$worldWonderManager = new WorldWonderManager();
$mediaManager = new MediaManager();

$currentUser = $userManager->getCurrentUser();

// Get wonder ID
$wonderId = $_GET['id'] ?? null;
if (!$wonderId) {
    header('Location: index.php');
    exit;
}

// Get world wonder
$wonder = $worldWonderManager->getWorldWonderById($wonderId);
if (!$wonder) {
    header('Location: index.php');
    exit;
}

// Check if user can view this wonder
if (!$wonder['is_approved'] && (!$currentUser || !$userManager->hasPermission('view_unapproved_wonders'))) {
    header('Location: index.php');
    exit;
}

// Get media for this wonder
$media = $mediaManager->getMediaByWorldWonderId($wonderId, true);

// Handle actions
if ($_POST['action'] ?? '' === 'approve' && $currentUser && $userManager->hasPermission('approve_wonders')) {
    $worldWonderManager->approveWorldWonder($wonderId, $currentUser['id']);
    header("Location: world_wonder_detail.php?id=$wonderId");
    exit;
}

if ($_POST['action'] ?? '' === 'delete' && $currentUser && $userManager->hasPermission('delete_wonders')) {
    $worldWonderManager->deleteWorldWonder($wonderId);
    header('Location: index.php');
    exit;
}

if ($_POST['action'] ?? '' === 'set_primary' && $currentUser) {
    $mediaId = $_POST['media_id'] ?? null;
    if ($mediaId) {
        $mediaManager->setPrimaryImage($mediaId, $wonderId);
        header("Location: world_wonder_detail.php?id=$wonderId");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($wonder['name']) ?> - Codex Mundi</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header class="main-header">
            <div class="header-content">
                <div class="logo">
                    <h1>Codex Mundi</h1>
                    <p>Wereldwonder Details</p>
                </div>
                <nav class="main-nav">
                    <a href="index.php">← Terug naar overzicht</a>
                    <?php if ($currentUser && $userManager->canEditOwn($wonder['created_by'])): ?>
                        <a href="world_wonder_form.php?id=<?= $wonder['id'] ?>">Bewerken</a>
                    <?php endif; ?>
                </nav>
            </div>
        </header>

        <div class="wonder-detail">
            <!-- Wonder Header -->
            <div class="wonder-header">
                <h1><?= htmlspecialchars($wonder['name']) ?></h1>
                
                <div class="wonder-meta">
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
                    <span class="category"><?= htmlspecialchars($wonder['category']) ?></span>
                    <?php if ($wonder['construction_year']): ?>
                        <span class="year"><?= $wonder['construction_year'] ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="wonder-location">
                    <strong>📍 Locatie:</strong>
                    <?= htmlspecialchars($wonder['city'] . ', ' . $wonder['country']) ?>
                    <?php if ($wonder['continent']): ?>
                        (<?= htmlspecialchars($wonder['continent']) ?>)
                    <?php endif; ?>
                </div>
                
                <?php if (!$wonder['is_approved']): ?>
                    <div class="approval-status">
                        ⏳ Wacht op goedkeuring
                    </div>
                <?php endif; ?>
            </div>

            <!-- Wonder Content -->
            <div class="wonder-content">
                <div class="wonder-description">
                    <h2>Beschrijving</h2>
                    <p><?= nl2br(htmlspecialchars($wonder['description'])) ?></p>
                </div>
                
                <?php if ($wonder['historical_info']): ?>
                    <div class="wonder-historical">
                        <h2>Historische Informatie</h2>
                        <p><?= nl2br(htmlspecialchars($wonder['historical_info'])) ?></p>
                    </div>
                <?php endif; ?>
                
                <?php if ($wonder['latitude'] && $wonder['longitude']): ?>
                    <div class="wonder-map">
                        <h2>Locatie op Kaart</h2>
                        <div id="map" style="height: 300px; width: 100%; background: #f0f0f0; display: flex; align-items: center; justify-content: center;">
                            <p>Kaart wordt geladen...</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Media Gallery -->
            <?php if (!empty($media)): ?>
                <div class="media-gallery">
                    <h2>Media</h2>
                    <div class="media-grid">
                        <?php foreach ($media as $m): ?>
                            <div class="media-item">
                                <?php if (strpos($m['file_type'], 'image/') === 0): ?>
                                    <img src="<?= htmlspecialchars($m['file_path']) ?>" 
                                         alt="<?= htmlspecialchars($m['original_name']) ?>"
                                         onclick="openImageModal('<?= htmlspecialchars($m['file_path']) ?>', '<?= htmlspecialchars($m['original_name']) ?>')">
                                <?php else: ?>
                                    <div class="file-item">
                                        <div class="file-icon">📄</div>
                                        <div class="file-name"><?= htmlspecialchars($m['original_name']) ?></div>
                                        <a href="<?= htmlspecialchars($m['file_path']) ?>" target="_blank" class="btn btn-sm">Bekijken</a>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($m['description']): ?>
                                    <div class="media-description">
                                        <?= htmlspecialchars($m['description']) ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($currentUser && $userManager->hasPermission('manage_media')): ?>
                                    <div class="media-actions">
                                        <?php if (strpos($m['file_type'], 'image/') === 0): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="set_primary">
                                                <input type="hidden" name="media_id" value="<?= $m['id'] ?>">
                                                <button type="submit" class="btn btn-sm">Hoofdafbeelding</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Wonder Info -->
            <div class="wonder-info">
                <h2>Informatie</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <strong>Toegevoegd door:</strong>
                        <?= htmlspecialchars($wonder['created_by_username']) ?>
                    </div>
                    <div class="info-item">
                        <strong>Datum toegevoegd:</strong>
                        <?= date('d-m-Y H:i', strtotime($wonder['created_at'])) ?>
                    </div>
                    <?php if ($wonder['updated_at'] !== $wonder['created_at']): ?>
                        <div class="info-item">
                            <strong>Laatst bijgewerkt:</strong>
                            <?= date('d-m-Y H:i', strtotime($wonder['updated_at'])) ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($wonder['approved_by_username']): ?>
                        <div class="info-item">
                            <strong>Goedgekeurd door:</strong>
                            <?= htmlspecialchars($wonder['approved_by_username']) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Admin Actions -->
            <?php if ($currentUser && $userManager->hasPermission('approve_wonders') && !$wonder['is_approved']): ?>
                <div class="admin-actions">
                    <h2>Beheerder Acties</h2>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="approve">
                        <button type="submit" class="btn btn-success">Goedkeuren</button>
                    </form>
                </div>
            <?php endif; ?>

            <?php if ($currentUser && $userManager->hasPermission('delete_wonders')): ?>
                <div class="admin-actions">
                    <form method="POST" style="display: inline;" onsubmit="return confirm('Weet je zeker dat je dit wereldwonder wilt verwijderen?')">
                        <input type="hidden" name="action" value="delete">
                        <button type="submit" class="btn btn-danger">Verwijderen</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Image Modal -->
    <div id="imageModal" class="modal" onclick="closeImageModal()">
        <div class="modal-content">
            <span class="close" onclick="closeImageModal()">&times;</span>
            <img id="modalImage" src="" alt="">
            <div id="modalCaption"></div>
        </div>
    </div>

    <script>
        function openImageModal(src, caption) {
            document.getElementById('modalImage').src = src;
            document.getElementById('modalCaption').textContent = caption;
            document.getElementById('imageModal').style.display = 'block';
        }

        function closeImageModal() {
            document.getElementById('imageModal').style.display = 'none';
        }

        // Simple map display (you can enhance this with a real map library)
        <?php if ($wonder['latitude'] && $wonder['longitude']): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const mapDiv = document.getElementById('map');
            if (mapDiv) {
                mapDiv.innerHTML = `
                    <div style="text-align: center;">
                        <p><strong>GPS Coördinaten:</strong></p>
                        <p>Latitude: <?= $wonder['latitude'] ?></p>
                        <p>Longitude: <?= $wonder['longitude'] ?></p>
                        <p><a href="https://www.google.com/maps?q=<?= $wonder['latitude'] ?>,<?= $wonder['longitude'] ?>" target="_blank">Bekijk op Google Maps</a></p>
                    </div>
                `;
            }
        });
        <?php endif; ?>
    </script>

    <style>
        .wonder-detail {
            background: white;
            border-radius: 5px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .wonder-header {
            border-bottom: 2px solid #ecf0f1;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .wonder-header h1 {
            color: #2c3e50;
            margin-bottom: 15px;
        }

        .wonder-meta {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }

        .wonder-meta span {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: bold;
        }

        .status-exists { background: #27ae60; color: white; }
        .status-destroyed { background: #e74c3c; color: white; }
        .status-unknown { background: #f39c12; color: white; }

        .category, .year {
            background: #3498db;
            color: white;
        }

        .wonder-location {
            color: #7f8c8d;
            font-size: 1.1em;
        }

        .approval-status {
            background: #f39c12;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            margin-top: 15px;
            display: inline-block;
        }

        .wonder-content {
            margin-bottom: 30px;
        }

        .wonder-content h2 {
            color: #2c3e50;
            margin-bottom: 15px;
            margin-top: 25px;
        }

        .wonder-content h2:first-child {
            margin-top: 0;
        }

        .media-gallery {
            margin-bottom: 30px;
        }

        .media-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 15px;
        }

        .media-item {
            border: 1px solid #ecf0f1;
            border-radius: 5px;
            overflow: hidden;
            background: white;
        }

        .media-item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            cursor: pointer;
            transition: transform 0.3s;
        }

        .media-item img:hover {
            transform: scale(1.05);
        }

        .file-item {
            padding: 20px;
            text-align: center;
        }

        .file-icon {
            font-size: 3em;
            margin-bottom: 10px;
        }

        .file-name {
            font-weight: bold;
            margin-bottom: 10px;
        }

        .media-description {
            padding: 10px;
            background: #f8f9fa;
            font-size: 0.9em;
            color: #555;
        }

        .media-actions {
            padding: 10px;
            border-top: 1px solid #ecf0f1;
        }

        .wonder-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }

        .info-item {
            padding: 10px;
            background: white;
            border-radius: 5px;
        }

        .admin-actions {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .admin-actions h2 {
            color: #856404;
            margin-bottom: 15px;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.9);
        }

        .modal-content {
            margin: auto;
            display: block;
            width: 80%;
            max-width: 700px;
            position: relative;
            top: 50%;
            transform: translateY(-50%);
        }

        .modal-content img {
            width: 100%;
            height: auto;
        }

        .close {
            position: absolute;
            top: 15px;
            right: 35px;
            color: #f1f1f1;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: #bbb;
        }

        #modalCaption {
            margin: auto;
            display: block;
            width: 80%;
            max-width: 700px;
            text-align: center;
            color: #ccc;
            padding: 10px 0;
        }
    </style>
</body>
</html>
