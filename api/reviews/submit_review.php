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
    
    // Get data
    $bookId = (int)$_POST['book_id'];
    $rating = (int)$_POST['rating'];
    $comment = trim($_POST['comment']) ?? '';
    $userId = (int)$_SESSION['utilizator_id'];
    
    // Validations    
    if ($rating < 1 || $rating > 5) {
        throw new Exception('Evaluarea trebuie să fie între 1 și 5 stele');
    }
        
    // Check if user already has a review for this book
    $checkQuery = "SELECT recenzie_id FROM recenzie WHERE utilizator_id = ? AND carte_id = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->execute([$userId, $bookId]);
    $existingReview = $checkStmt->fetch();
    
    if ($existingReview) {
        // Update existing review
        $updateQuery = "UPDATE recenzie 
                       SET evaluare = ?, 
                           comentariu = ?, 
                           data_recenzie = CURRENT_TIMESTAMP 
                       WHERE utilizator_id = ? AND carte_id = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->execute([$rating, $comment, $userId, $bookId]);
        
        $message = 'Recenzia ta a fost actualizată cu succes!';
    } else {
        // Insert new review
        $insertQuery = "INSERT INTO recenzie (utilizator_id, carte_id, evaluare, comentariu) 
                       VALUES (?, ?, ?, ?)";
        $insertStmt = $conn->prepare($insertQuery);
        $insertStmt->execute([$userId, $bookId, $rating, $comment]);
        
        $message = 'Recenzia ta a fost adăugată cu succes!';
    }
    
    // Get updated average rating
    $avgQuery = "SELECT COUNT(*) as total_reviews, COALESCE(AVG(evaluare), 0) as avg_rating 
                FROM recenzie WHERE carte_id = ?";
    $avgStmt = $conn->prepare($avgQuery);
    $avgStmt->execute([$bookId]);
    $avgData = $avgStmt->fetch();
    
    echo json_encode([
        'success' => true,
        'message' => $message,
        'avg_rating' => round($avgData['avg_rating'], 1),
        'total_reviews' => $avgData['total_reviews']
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
