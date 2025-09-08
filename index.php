<?php
require_once 'config/database.php';
require_once 'includes/item.php';

$itemManager = new ItemManager();
$items = $itemManager->getAllItems();
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD Systeem met Afbeeldingen</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <header>
            <h1><i class="fas fa-images"></i> CRUD Systeem met Afbeeldingen</h1>
            <p>Beheer je items en afbeeldingen</p>
        </header>

        <div class="actions">
            <button class="btn btn-primary" onclick="openModal('add')">
                <i class="fas fa-plus"></i> Nieuw Item Toevoegen
            </button>
        </div>

        <div class="items-grid">
            <?php if (empty($items)): ?>
                <div class="no-items">
                    <i class="fas fa-image"></i>
                    <h3>Geen items gevonden</h3>
                    <p>Voeg je eerste item toe om te beginnen!</p>
                </div>
            <?php else: ?>
                <?php foreach ($items as $item): ?>
                    <div class="item-card">
                        <div class="item-image">
                            <?php if ($item['image_path'] && file_exists($item['image_path'])): ?>
                                <img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                            <?php else: ?>
                                <div class="no-image">
                                    <i class="fas fa-image"></i>
                                    <span>Geen afbeelding</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="item-content">
                            <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                            <p><?php echo htmlspecialchars($item['description']); ?></p>
                            <div class="item-meta">
                                <small>
                                    <i class="fas fa-calendar"></i>
                                    <?php echo date('d-m-Y H:i', strtotime($item['created_at'])); ?>
                                </small>
                            </div>
                        </div>
                        <div class="item-actions">
                            <button class="btn btn-sm btn-edit" onclick="editItem(<?php echo $item['id']; ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-delete" onclick="deleteItem(<?php echo $item['id']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal voor Add/Edit -->
    <div id="itemModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Nieuw Item</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <form id="itemForm" enctype="multipart/form-data">
                <input type="hidden" id="itemId" name="id">
                
                <div class="form-group">
                    <label for="title">Titel *</label>
                    <input type="text" id="title" name="title" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Beschrijving</label>
                    <textarea id="description" name="description" rows="4"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="image">Afbeelding</label>
                    <input type="file" id="image" name="image" accept="image/*">
                    <div id="currentImage" class="current-image"></div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Annuleren</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Opslaan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Loading overlay -->
    <div id="loadingOverlay" class="loading-overlay">
        <div class="spinner"></div>
    </div>

    <script src="js/script.js"></script>
</body>
</html>
