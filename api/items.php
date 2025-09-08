<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../includes/item.php';

$itemManager = new ItemManager();
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                // Specifiek item ophalen
                $item = $itemManager->getItemById($_GET['id']);
                if ($item) {
                    echo json_encode(array('success' => true, 'data' => $item));
                } else {
                    echo json_encode(array('success' => false, 'message' => 'Item niet gevonden'));
                }
            } else {
                // Alle items ophalen
                $items = $itemManager->getAllItems();
                echo json_encode(array('success' => true, 'data' => $items));
            }
            break;
            
        case 'POST':
            // Nieuw item aanmaken
            $title = $_POST['title'] ?? '';
            $description = $_POST['description'] ?? '';
            $imagePath = null;
            
            // Afbeelding uploaden als deze is meegegeven
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $imagePath = $itemManager->uploadImage($_FILES['image']);
            }
            
            if (empty($title)) {
                throw new Exception('Titel is verplicht');
            }
            
            $id = $itemManager->createItem($title, $description, $imagePath);
            echo json_encode(array('success' => true, 'message' => 'Item succesvol aangemaakt', 'id' => $id));
            break;
            
        case 'PUT':
            // Item bijwerken
            $input = json_decode(file_get_contents('php://input'), true);
            $id = $input['id'] ?? '';
            $title = $input['title'] ?? '';
            $description = $input['description'] ?? '';
            $imagePath = $input['image_path'] ?? null;
            
            if (empty($id) || empty($title)) {
                throw new Exception('ID en titel zijn verplicht');
            }
            
            $itemManager->updateItem($id, $title, $description, $imagePath);
            echo json_encode(array('success' => true, 'message' => 'Item succesvol bijgewerkt'));
            break;
            
        case 'DELETE':
            // Item verwijderen
            $input = json_decode(file_get_contents('php://input'), true);
            $id = $input['id'] ?? '';
            
            if (empty($id)) {
                throw new Exception('ID is verplicht');
            }
            
            $itemManager->deleteItem($id);
            echo json_encode(array('success' => true, 'message' => 'Item succesvol verwijderd'));
            break;
            
        default:
            throw new Exception('Methode niet ondersteund');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(array('success' => false, 'message' => $e->getMessage()));
}
?>
