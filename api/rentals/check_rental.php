<?php
session_start();
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../common/db.php';

if (!isset($_SESSION['utilizator_id'])) {
    echo json_encode(['success' => false, 'message' => 'Trebuie să fii autentificat.']);
    exit;
}

if ($_SESSION['rol'] === 'admin') {
    echo json_encode(['success' => false, 'message' => 'Administratorii nu pot actualiza profilul prin această pagină']);
    exit;
}

try {
    $conn = getDatabaseConnection();
    
    $bookId = (int)$_GET['book_id'];
    $userId = (int)$_SESSION['utilizator_id'];
    
    // Check if user has an active rental for this book
    $checkQuery = "SELECT inchiriere_id, data_scadenta FROM inchiriere 
                   WHERE utilizator_id = ? AND carte_id = ? AND stare = 'activa'";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->execute([$userId, $bookId]);
    $rental = $checkStmt->fetch();
    
    echo json_encode([
        'success' => true,
        'has_active_rental' => $rental !== false,
        'is_logged_in' => true,
        'data_scadenta' => $rental ? $rental['data_scadenta'] : null
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
