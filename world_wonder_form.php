<?php
require_once 'includes/user.php';
require_once 'includes/world_wonder.php';
require_once 'includes/media.php';

$userManager = new UserManager();
$worldWonderManager = new WorldWonderManager();
$mediaManager = new MediaManager();

// Check if user is logged in and has permission
$currentUser = $userManager->getCurrentUser();
if (!$currentUser) {
    header('Location: login.php');
    exit;
}

if (!$userManager->hasPermission('create_wonders') && !$userManager->hasPermission('edit_wonders')) {
    header('Location: index.php');
    exit;
}

$wonder = null;
$isEdit = false;
$error = '';
$success = '';

// Get wonder ID if editing
$wonderId = $_GET['id'] ?? null;
if ($wonderId) {
    $wonder = $worldWonderManager->getWorldWonderById($wonderId);
    if (!$wonder) {
        header('Location: index.php');
        exit;
    }
    
    // Check if user can edit this wonder
    if (!$userManager->hasPermission('edit_wonders') && !$userManager->canEditOwn($wonder['created_by'])) {
        header('Location: index.php');
        exit;
    }
    
    $isEdit = true;
}

// Process form submission
if ($_POST['action'] ?? '' === 'save') {
    try {
        $data = array(
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'historical_info' => $_POST['historical_info'] ?? '',
            'construction_year' => $_POST['construction_year'] ?? null,
            'status' => $_POST['status'] ?? 'exists',
            'category' => $_POST['category'] ?? 'classical',
            'continent' => $_POST['continent'] ?? '',
            'country' => $_POST['country'] ?? '',
            'city' => $_POST['city'] ?? '',
            'latitude' => $_POST['latitude'] ?? null,
            'longitude' => $_POST['longitude'] ?? null,
            'created_by' => $currentUser['id']
        );
        
        if ($isEdit) {
            $worldWonderManager->updateWorldWonder($wonderId, $data);
            $success = 'Wereldwonder bijgewerkt!';
        } else {
            $wonderId = $worldWonderManager->createWorldWonder($data);
            $success = 'Wereldwonder toegevoegd!';
        }
        
        // Handle file uploads
        if (isset($_FILES['media']) && !empty($_FILES['media']['name'][0])) {
            $uploadCount = 0;
            for ($i = 0; $i < count($_FILES['media']['name']); $i++) {
                if ($_FILES['media']['error'][$i] === UPLOAD_ERR_OK) {
                    $file = array(
                        'name' => $_FILES['media']['name'][$i],
                        'type' => $_FILES['media']['type'][$i],
                        'tmp_name' => $_FILES['media']['tmp_name'][$i],
                        'error' => $_FILES['media']['error'][$i],
                        'size' => $_FILES['media']['size'][$i]
                    );
                    
                    $description = $_POST['media_description'][$i] ?? '';
                    $mediaManager->uploadMedia($wonderId, $file, $currentUser['id'], $description);
                    $uploadCount++;
                }
            }
            
            if ($uploadCount > 0) {
                $success .= " $uploadCount bestand(en) geüpload.";
            }
        }
        
        // Redirect to detail page
        header("Location: world_wonder_detail.php?id=$wonderId");
        exit;
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Get existing media for this wonder
$existingMedia = array();
if ($isEdit) {
    $existingMedia = $mediaManager->getMediaByWorldWonderId($wonderId, false);
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEdit ? 'Bewerk' : 'Nieuw' ?> Wereldwonder - Codex Mundi</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header class="main-header">
            <div class="header-content">
                <div class="logo">
                    <h1>Codex Mundi</h1>
                    <p><?= $isEdit ? 'Bewerk Wereldwonder' : 'Nieuw Wereldwonder' ?></p>
                </div>
                <nav class="main-nav">
                    <a href="index.php">← Terug naar overzicht</a>
                </nav>
            </div>
        </header>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="wonder-form">
            <input type="hidden" name="action" value="save">
            
            <div class="form-section">
                <h2>Basis Informatie</h2>
                
                <div class="form-group">
                    <label>Naam *</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($wonder['name'] ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Beschrijving *</label>
                    <textarea name="description" rows="4" required><?= htmlspecialchars($wonder['description'] ?? '') ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Historische Informatie</label>
                    <textarea name="historical_info" rows="4"><?= htmlspecialchars($wonder['historical_info'] ?? '') ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Bouwjaar</label>
                        <input type="number" name="construction_year" value="<?= htmlspecialchars($wonder['construction_year'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status">
                            <option value="exists" <?= ($wonder['status'] ?? '') === 'exists' ? 'selected' : '' ?>>Bestaat nog</option>
                            <option value="destroyed" <?= ($wonder['status'] ?? '') === 'destroyed' ? 'selected' : '' ?>>Vernietigd</option>
                            <option value="unknown" <?= ($wonder['status'] ?? '') === 'unknown' ? 'selected' : '' ?>>Onbekend</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Categorie</label>
                        <select name="category">
                            <option value="classical" <?= ($wonder['category'] ?? '') === 'classical' ? 'selected' : '' ?>>Klassiek</option>
                            <option value="modern" <?= ($wonder['category'] ?? '') === 'modern' ? 'selected' : '' ?>>Modern</option>
                            <option value="natural" <?= ($wonder['category'] ?? '') === 'natural' ? 'selected' : '' ?>>Natuurlijk</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h2>Locatie</h2>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Continent</label>
                        <select name="continent">
                            <option value="">Selecteer continent</option>
                            <option value="Africa" <?= ($wonder['continent'] ?? '') === 'Africa' ? 'selected' : '' ?>>Afrika</option>
                            <option value="Asia" <?= ($wonder['continent'] ?? '') === 'Asia' ? 'selected' : '' ?>>Azië</option>
                            <option value="Europe" <?= ($wonder['continent'] ?? '') === 'Europe' ? 'selected' : '' ?>>Europa</option>
                            <option value="North America" <?= ($wonder['continent'] ?? '') === 'North America' ? 'selected' : '' ?>>Noord-Amerika</option>
                            <option value="South America" <?= ($wonder['continent'] ?? '') === 'South America' ? 'selected' : '' ?>>Zuid-Amerika</option>
                            <option value="Oceania" <?= ($wonder['continent'] ?? '') === 'Oceania' ? 'selected' : '' ?>>Oceanië</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Land</label>
                        <input type="text" name="country" value="<?= htmlspecialchars($wonder['country'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Stad</label>
                        <input type="text" name="city" value="<?= htmlspecialchars($wonder['city'] ?? '') ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Latitude (GPS)</label>
                        <input type="number" step="any" name="latitude" value="<?= htmlspecialchars($wonder['latitude'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Longitude (GPS)</label>
                        <input type="number" step="any" name="longitude" value="<?= htmlspecialchars($wonder['longitude'] ?? '') ?>">
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h2>Media</h2>
                
                <?php if ($isEdit && !empty($existingMedia)): ?>
                    <div class="existing-media">
                        <h3>Bestaande Media</h3>
                        <?php foreach ($existingMedia as $media): ?>
                            <div class="media-item">
                                <?php if (strpos($media['file_type'], 'image/') === 0): ?>
                                    <img src="<?= htmlspecialchars($media['file_path']) ?>" alt="<?= htmlspecialchars($media['original_name']) ?>" style="max-width: 100px; max-height: 100px;">
                                <?php else: ?>
                                    <div class="file-icon">📄</div>
                                <?php endif; ?>
                                <div class="media-info">
                                    <strong><?= htmlspecialchars($media['original_name']) ?></strong>
                                    <br>
                                    <small>Geüpload door: <?= htmlspecialchars($media['uploaded_by_username']) ?></small>
                                    <br>
                                    <small>Status: <?= $media['is_approved'] ? 'Goedgekeurd' : 'Wacht op goedkeuring' ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <div class="form-group">
                    <label>Nieuwe Bestanden</label>
                    <input type="file" name="media[]" multiple accept="image/*,.pdf,.txt">
                    <small>Maximaal 5MB per bestand. Toegestane types: JPG, PNG, GIF, PDF, TXT</small>
                </div>
                
                <div id="media-descriptions"></div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <?= $isEdit ? 'Bijwerken' : 'Toevoegen' ?>
                </button>
                <a href="index.php" class="btn btn-secondary">Annuleren</a>
            </div>
        </form>
    </div>

    <script>
        // Add description fields for each uploaded file
        document.querySelector('input[name="media[]"]').addEventListener('change', function() {
            const descriptionsDiv = document.getElementById('media-descriptions');
            descriptionsDiv.innerHTML = '';
            
            for (let i = 0; i < this.files.length; i++) {
                const div = document.createElement('div');
                div.className = 'form-group';
                div.innerHTML = `
                    <label>Beschrijving voor ${this.files[i].name}:</label>
                    <input type="text" name="media_description[]" placeholder="Optionele beschrijving">
                `;
                descriptionsDiv.appendChild(div);
            }
        });
    </script>
</body>
</html>
