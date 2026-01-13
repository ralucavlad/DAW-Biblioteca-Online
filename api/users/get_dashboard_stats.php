<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../common/db.php';

session_start();

if (!isset($_SESSION['utilizator_id'])) {
    echo json_encode(['success' => false, 'error' => 'Trebuie să fii autentificat.']);
    exit;
}

try {
    $conn = getDatabaseConnection();
    
    if (!$conn) {
        throw new Exception('Conexiune la baza de date eșuată');
    }
    
    $userId = (int)$_SESSION['utilizator_id'];
    
    // Get counts
    $reviewsStmt = $conn->prepare("SELECT COUNT(*) FROM recenzie WHERE utilizator_id = ?");
    $reviewsStmt->execute([$userId]);
    $reviewsCount = $reviewsStmt->fetchColumn();
    
    $favoritesStmt = $conn->prepare("SELECT COUNT(*) FROM favorite WHERE utilizator_id = ?");
    $favoritesStmt->execute([$userId]);
    $favoritesCount = $favoritesStmt->fetchColumn();
    
    $rentalsStmt = $conn->prepare("SELECT COUNT(*) FROM inchiriere WHERE utilizator_id = ? AND stare = 'activa'");
    $rentalsStmt->execute([$userId]);
    $rentalsCount = $rentalsStmt->fetchColumn();
    
    echo json_encode([
        'success' => true,
        'reviews' => (int)$reviewsCount,
        'favorites' => (int)$favoritesCount,
        'rentals' => (int)$rentalsCount
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
