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

    // Get all reviews for this book
    $query = "SELECT 
                r.recenzie_id,
                r.evaluare,
                r.comentariu,
                r.data_recenzie,
                u.nume,
                u.prenume
              FROM recenzie r
              JOIN utilizator u ON r.utilizator_id = u.utilizator_id
              WHERE r.carte_id = ?
              ORDER BY r.data_recenzie DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute([$bookId]);
    $reviews = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'reviews' => $reviews,
        'count' => count($reviews)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
