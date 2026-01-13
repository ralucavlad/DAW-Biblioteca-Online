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
    
    // Get all user's favorites with book details
    $query = "SELECT 
                f.favorite_id,
                f.carte_id,
                f.data_adaugare,
                c.denumire as carte_denumire,
                c.url_coperta,
                c.nr_exemplare_disponibile,
                GROUP_CONCAT(DISTINCT a.nume ORDER BY a.nume SEPARATOR ', ') as autori
              FROM favorite f
              JOIN carte c ON f.carte_id = c.carte_id
              LEFT JOIN carte_autor ca ON c.carte_id = ca.carte_id
              LEFT JOIN autor a ON ca.autor_id = a.autor_id
              WHERE f.utilizator_id = ?
              GROUP BY f.favorite_id, f.carte_id, f.data_adaugare, c.denumire, c.url_coperta, c.nr_exemplare_disponibile
              ORDER BY f.data_adaugare DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute([$userId]);
    $favorites = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'favorites' => $favorites,
        'count' => count($favorites)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
