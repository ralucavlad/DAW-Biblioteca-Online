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
    
    $reviewId = (int)$_POST['review_id'];
    $userId = (int)$_SESSION['utilizator_id'];

    // Delete review
    $deleteQuery = "DELETE FROM recenzie WHERE recenzie_id = ? AND utilizator_id = ?";
    $deleteStmt = $conn->prepare($deleteQuery);
    $deleteStmt->execute([$reviewId, $userId]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Recenzia a fost ștearsă cu succes!'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
