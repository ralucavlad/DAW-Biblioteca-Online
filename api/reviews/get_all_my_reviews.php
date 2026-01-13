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
       
    $userId = (int)$_SESSION['utilizator_id'];
    
    // Get all user's reviews with book details
    $query = "SELECT 
                r.recenzie_id,
                r.carte_id,
                r.evaluare,
                r.comentariu,
                r.data_recenzie,
                c.denumire as carte_denumire,
                c.url_coperta,
                GROUP_CONCAT(DISTINCT a.nume ORDER BY a.nume SEPARATOR ', ') as autori
              FROM recenzie r
              JOIN carte c ON r.carte_id = c.carte_id
              LEFT JOIN carte_autor ca ON c.carte_id = ca.carte_id
              LEFT JOIN autor a ON ca.autor_id = a.autor_id
              WHERE r.utilizator_id = ?
              GROUP BY r.recenzie_id, r.carte_id, r.evaluare, r.comentariu, r.data_recenzie, c.denumire, c.url_coperta
              ORDER BY r.data_recenzie DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute([$userId]);
    $reviews = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'reviews' => $reviews,
        'count' => count($reviews)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
