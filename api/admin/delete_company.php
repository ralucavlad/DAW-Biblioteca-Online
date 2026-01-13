<?php
session_start();
require_once __DIR__ . '/../common/db.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Neautorizat']);
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Metoda cererii nu este validă');
    }
    
    $companie_id = $_POST['companie_id'] ?? null;
    
    if (!$companie_id) {
        throw new Exception('ID-ul companiei este obligatoriu');
    }
    
    $conn = getDatabaseConnection();
    
    // Check if company has managers
    $checkStmt = $conn->prepare("SELECT COUNT(*) FROM utilizator WHERE companie_id = ?");
    $checkStmt->execute([$companie_id]);
    $result = $checkStmt->fetch(PDO::FETCH_COLUMN);
    
    if ($result > 0) {
        throw new Exception('Nu puteți șterge o companie care are manageri asociați. Eliminați mai întâi managerii.');
    }
    
    $stmt = $conn->prepare("DELETE FROM companie WHERE companie_id = ?");
    $stmt->execute([$companie_id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Compania a fost ștearsă cu succes'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
