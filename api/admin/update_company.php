<?php
session_start();
require_once __DIR__ . '/../common/db.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Neautorizat']);
    exit;
}


try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }
    
    // Validate required fields
    $required = ['companie_id', 'nume', 'email'];
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
      
    $stmt = $conn->prepare("UPDATE companie 
              SET nume = :nume,
                  adresa = :adresa,
                  email = :email,
                  telefon = :telefon
              WHERE companie_id = :id");
    $stmt->execute([
        'nume' => $_POST['nume'],
        'adresa' => $_POST['adresa'] ?? null,
        'email' => $_POST['email'],
        'telefon' => $_POST['telefon'] ?? null,
        'id' => $_POST['companie_id']
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Compania a fost actualizată cu succes'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
