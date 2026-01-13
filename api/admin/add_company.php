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
    
    // Validate required fields
    $required = ['nume', 'cui', 'email'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Câmpul {$field} este obligatoriu");
        }
    }
    
    // Validate email format
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Adresa de email nu este validă');
    }
    
    $conn = getDatabaseConnection();
    
    // Check if CUI already exists
    $checkStmt = $conn->prepare("SELECT companie_id FROM companie WHERE cui = ?");
    $checkStmt->execute([$_POST['cui']]);
    if ($checkStmt->fetch()) {
        throw new Exception('CUI-ul există deja în sistem');
    }
        
    $stmt = $conn->prepare("INSERT INTO companie (nume, cui, adresa, email, telefon, data_inregistrare)
              VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->execute([
        $_POST['nume'],
        $_POST['cui'],
        $_POST['adresa'] ?? null,
        $_POST['email'],
        $_POST['telefon'] ?? null
    ]);
    
    echo json_encode([
        'success' => true,
        'companie_id' => $conn->lastInsertId(),
        'message' => 'Compania a fost adăugată cu succes'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
