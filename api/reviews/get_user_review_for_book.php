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
        
    // Get user's review if exists
    $query = "SELECT evaluare, comentariu, data_recenzie 
              FROM recenzie 
              WHERE utilizator_id = ? AND carte_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$userId, $bookId]);
    $review = $stmt->fetch();
    
    if ($review) {
        echo json_encode([
            'success' => true,
            'has_review' => true,
            'rating' => (int)$review['evaluare'],
            'comment' => $review['comentariu'],
            'date' => $review['data_recenzie']
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'has_review' => false
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
