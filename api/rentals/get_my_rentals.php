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
    
    // Get all user's rentals with book details
    $query = "SELECT 
                i.inchiriere_id,
                i.carte_id,
                i.data_inchiriere,
                i.data_scadenta,
                i.data_returnare,
                i.stare,
                c.denumire as carte_denumire,
                c.url_coperta,
                GROUP_CONCAT(DISTINCT a.nume ORDER BY a.nume SEPARATOR ', ') as autori
              FROM inchiriere i
              JOIN carte c ON i.carte_id = c.carte_id
              LEFT JOIN carte_autor ca ON c.carte_id = ca.carte_id
              LEFT JOIN autor a ON ca.autor_id = a.autor_id
              WHERE i.utilizator_id = ?
              GROUP BY i.inchiriere_id, i.carte_id, i.data_inchiriere, i.data_scadenta, i.data_returnare, i.stare, c.denumire, c.url_coperta
              ORDER BY i.data_inchiriere DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute([$userId]);
    $rentals = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'rentals' => $rentals,
        'count' => count($rentals)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
