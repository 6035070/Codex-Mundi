<?php
require_once 'includes/user.php';
require_once 'includes/world_wonder.php';
require_once 'includes/media.php';

$userManager = new UserManager();
$worldWonderManager = new WorldWonderManager();
$mediaManager = new MediaManager();

// Check if user is logged in and is admin
$currentUser = $userManager->getCurrentUser();
if (!$currentUser || !$userManager->hasPermission('manage_users')) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

// Handle user role updates
if ($_POST['action'] ?? '' === 'update_role') {
    $userId = $_POST['user_id'] ?? null;
    $roleId = $_POST['role_id'] ?? null;
    
    if ($userId && $roleId) {
        try {
            $userManager->updateUserRole($userId, $roleId);
            $success = 'Gebruikersrol bijgewerkt!';
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

// Handle file restrictions update
if ($_POST['action'] ?? '' === 'update_file_restrictions') {
    $allowedTypes = $_POST['allowed_types'] ?? array();
    $maxFileSize = $_POST['max_file_size'] ?? 5242880;
    
    try {
        $mediaManager->updateFileRestrictions($allowedTypes, $maxFileSize);
        $success = 'Bestandsrestricties bijgewerkt!';
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Get data
$users = $userManager->getAllUsers();
$roles = $userManager->getAllRoles();
$statistics = $worldWonderManager->getStatistics();
$pendingMedia = $mediaManager->getPendingMedia();
$fileRestrictions = $mediaManager->getFileRestrictions();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beheer - Codex Mundi</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header class="main-header">
            <div class="header-content">
                <div class="logo">
                    <h1>Codex Mundi</h1>
                    <p>Beheer Panel</p>
                </div>
                <nav class="main-nav">
                    <a href="index.php">← Terug naar overzicht</a>
                    <a href="logout.php">Uitloggen</a>
                </nav>
            </div>
        </header>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <div class="admin-dashboard">
            <!-- Statistics -->
            <div class="admin-section">
                <h2>Statistieken</h2>
                <div class="stats-grid">
                    <div class="stat-item">
                        <h3><?= $statistics['total_wonders'] ?></h3>
                        <p>Wereldwonderen</p>
                    </div>
                    <div class="stat-item">
                        <h3><?= count($users) ?></h3>
                        <p>Gebruikers</p>
                    </div>
                    <div class="stat-item">
                        <h3><?= count($pendingMedia) ?></h3>
                        <p>Media wacht op goedkeuring</p>
                    </div>
                    <div class="stat-item">
                        <h3><?= count($roles) ?></h3>
                        <p>Rollen</p>
                    </div>
                </div>
            </div>

            <!-- User Management -->
            <div class="admin-section">
                <h2>Gebruikersbeheer</h2>
                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Gebruiker</th>
                                <th>Email</th>
                                <th>Rol</th>
                                <th>Actief</th>
                                <th>Geregistreerd</th>
                                <th>Acties</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></strong>
                                        <br>
                                        <small><?= htmlspecialchars($user['username']) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td>
                                        <form method="POST" class="inline-form">
                                            <input type="hidden" name="action" value="update_role">
                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                            <select name="role_id" onchange="this.form.submit()">
                                                <?php foreach ($roles as $role): ?>
                                                    <option value="<?= $role['id'] ?>" <?= $user['role_id'] == $role['id'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($role['name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </form>
                                    </td>
                                    <td>
                                        <span class="status <?= $user['is_active'] ? 'active' : 'inactive' ?>">
                                            <?= $user['is_active'] ? 'Actief' : 'Inactief' ?>
                                        </span>
                                    </td>
                                    <td><?= date('d-m-Y', strtotime($user['created_at'])) ?></td>
                                    <td>
                                        <a href="user_profile.php?id=<?= $user['id'] ?>" class="btn btn-sm">Bekijk</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pending Media -->
            <?php if (!empty($pendingMedia)): ?>
                <div class="admin-section">
                    <h2>Media Wacht op Goedkeuring</h2>
                    <div class="media-approval">
                        <?php foreach ($pendingMedia as $media): ?>
                            <div class="media-item">
                                <?php if (strpos($media['file_type'], 'image/') === 0): ?>
                                    <img src="<?= htmlspecialchars($media['file_path']) ?>" alt="<?= htmlspecialchars($media['original_name']) ?>" style="width: 100px; height: 100px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="file-icon">📄</div>
                                <?php endif; ?>
                                <div class="media-details">
                                    <h4><?= htmlspecialchars($media['original_name']) ?></h4>
                                    <p><strong>Wereldwonder:</strong> <?= htmlspecialchars($media['world_wonder_name']) ?></p>
                                    <p><strong>Geüpload door:</strong> <?= htmlspecialchars($media['uploaded_by_username']) ?></p>
                                    <p><strong>Datum:</strong> <?= date('d-m-Y H:i', strtotime($media['created_at'])) ?></p>
                                    <?php if ($media['description']): ?>
                                        <p><strong>Beschrijving:</strong> <?= htmlspecialchars($media['description']) ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="media-actions">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="approve_media">
                                        <input type="hidden" name="media_id" value="<?= $media['id'] ?>">
                                        <button type="submit" class="btn btn-success btn-sm">Goedkeuren</button>
                                    </form>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Weet je zeker dat je dit bestand wilt verwijderen?')">
                                        <input type="hidden" name="action" value="delete_media">
                                        <input type="hidden" name="media_id" value="<?= $media['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Verwijderen</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- File Restrictions -->
            <div class="admin-section">
                <h2>Bestandsrestricties</h2>
                <form method="POST" class="file-restrictions-form">
                    <input type="hidden" name="action" value="update_file_restrictions">
                    
                    <div class="form-group">
                        <label>Toegestane bestandstypes:</label>
                        <div class="checkbox-group">
                            <label><input type="checkbox" name="allowed_types[]" value="image/jpeg" <?= in_array('image/jpeg', $fileRestrictions['allowed_types']) ? 'checked' : '' ?>> JPEG</label>
                            <label><input type="checkbox" name="allowed_types[]" value="image/png" <?= in_array('image/png', $fileRestrictions['allowed_types']) ? 'checked' : '' ?>> PNG</label>
                            <label><input type="checkbox" name="allowed_types[]" value="image/gif" <?= in_array('image/gif', $fileRestrictions['allowed_types']) ? 'checked' : '' ?>> GIF</label>
                            <label><input type="checkbox" name="allowed_types[]" value="application/pdf" <?= in_array('application/pdf', $fileRestrictions['allowed_types']) ? 'checked' : '' ?>> PDF</label>
                            <label><input type="checkbox" name="allowed_types[]" value="text/plain" <?= in_array('text/plain', $fileRestrictions['allowed_types']) ? 'checked' : '' ?>> TXT</label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Maximale bestandsgrootte (bytes):</label>
                        <input type="number" name="max_file_size" value="<?= $fileRestrictions['max_file_size'] ?>" min="1048576" max="52428800">
                        <small>Huidige limiet: <?= round($fileRestrictions['max_file_size'] / 1024 / 1024, 1) ?> MB</small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Bijwerken</button>
                </form>
            </div>

            <!-- System Information -->
            <div class="admin-section">
                <h2>Systeeminformatie</h2>
                <div class="system-info">
                    <div class="info-item">
                        <strong>PHP Versie:</strong> <?= PHP_VERSION ?>
                    </div>
                    <div class="info-item">
                        <strong>Server:</strong> <?= $_SERVER['SERVER_SOFTWARE'] ?? 'Onbekend' ?>
                    </div>
                    <div class="info-item">
                        <strong>Database:</strong> MySQL
                    </div>
                    <div class="info-item">
                        <strong>Upload Directory:</strong> <?= is_writable('uploads/') ? 'Schrijfbaar' : 'Niet schrijfbaar' ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .admin-dashboard {
            display: grid;
            gap: 30px;
        }

        .admin-section {
            background: white;
            padding: 25px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .admin-section h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            border-bottom: 2px solid #ecf0f1;
            padding-bottom: 10px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .stat-item {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 5px;
        }

        .stat-item h3 {
            font-size: 2.5em;
            color: #3498db;
            margin-bottom: 5px;
        }

        .table-container {
            overflow-x: auto;
        }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .admin-table th,
        .admin-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ecf0f1;
        }

        .admin-table th {
            background: #f8f9fa;
            font-weight: bold;
            color: #2c3e50;
        }

        .admin-table tr:hover {
            background: #f8f9fa;
        }

        .inline-form {
            display: inline;
        }

        .inline-form select {
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 3px;
        }

        .status.active {
            color: #27ae60;
            font-weight: bold;
        }

        .status.inactive {
            color: #e74c3c;
            font-weight: bold;
        }

        .media-approval {
            display: grid;
            gap: 20px;
        }

        .media-item {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 20px;
            border: 1px solid #ecf0f1;
            border-radius: 5px;
            background: #f8f9fa;
        }

        .media-details {
            flex: 1;
        }

        .media-details h4 {
            margin-bottom: 10px;
            color: #2c3e50;
        }

        .media-details p {
            margin: 5px 0;
            color: #555;
        }

        .media-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .file-restrictions-form {
            max-width: 600px;
        }

        .checkbox-group {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
            margin-top: 10px;
        }

        .checkbox-group label {
            display: flex;
            align-items: center;
            gap: 5px;
            font-weight: normal;
        }

        .system-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }

        .info-item {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }

        @media (max-width: 768px) {
            .media-item {
                flex-direction: column;
                text-align: center;
            }
            
            .media-actions {
                flex-direction: row;
                justify-content: center;
            }
        }
    </style>
</body>
</html>
