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
    
    // Get action and book ID
    $action = $_POST['action'] ?? '';
    $bookId = (int)$_POST['book_id'];
    $userId = (int)$_SESSION['utilizator_id'];
        
    if ($action === 'toggle') {
        // Check if already in favorites
        $checkStmt = $conn->prepare("SELECT favorite_id FROM favorite WHERE utilizator_id = ? AND carte_id = ?");
        $checkStmt->execute([$userId, $bookId]);
        $existing = $checkStmt->fetch();
        
        if ($existing) {
            // Remove from favorites
            $deleteStmt = $conn->prepare("DELETE FROM favorite WHERE utilizator_id = ? AND carte_id = ?");
            $deleteStmt->execute([$userId, $bookId]);
            
            echo json_encode([
                'success' => true,
                'action' => 'removed',
                'message' => 'Cartea a fost eliminată din favorite.'
            ]);
        } else {
            // Add to favorites
            $insertStmt = $conn->prepare("INSERT INTO favorite (utilizator_id, carte_id) VALUES (?, ?)");
            $insertStmt->execute([$userId, $bookId]);
            
            echo json_encode([
                'success' => true,
                'action' => 'added',
                'message' => 'Cartea a fost adăugată la favorite.'
            ]);
        }
    } elseif ($action === 'check') {
        // Check if book is in favorites
        $checkStmt = $conn->prepare("SELECT favorite_id FROM favorite WHERE utilizator_id = ? AND carte_id = ?");
        $checkStmt->execute([$userId, $bookId]);
        $isFavorite = $checkStmt->fetch() !== false;
        
        echo json_encode([
            'success' => true,
            'is_favorite' => $isFavorite
        ]);
    } else {
        throw new Exception('Acțiune invalidă');
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
