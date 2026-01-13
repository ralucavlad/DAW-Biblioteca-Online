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
    
    $searchTitle = isset($_GET['title']) ? trim($_GET['title']) : '';
    
    $query = "SELECT 
                c.carte_id,
                c.denumire,
                c.url_coperta,
                c.isbn,
                c.editura,
                c.an_publicare,
                c.nr_exemplare_disponibile,
                GROUP_CONCAT(DISTINCT a.nume ORDER BY a.nume SEPARATOR ', ') as autori,
                GROUP_CONCAT(DISTINCT d.denumire ORDER BY d.denumire SEPARATOR ', ') as domenii
              FROM carte c
              LEFT JOIN carte_autor ca ON c.carte_id = ca.carte_id
              LEFT JOIN autor a ON ca.autor_id = a.autor_id
              LEFT JOIN carte_domeniu cd ON c.carte_id = cd.carte_id
              LEFT JOIN domeniu d ON cd.domeniu_id = d.domeniu_id
              WHERE 1=1"; // Placeholder for dynamic conditions
    
    $params = [];
    
    if (!empty($searchTitle)) {
        $query .= " AND c.denumire LIKE :title";
        $params[':title'] = '%' . $searchTitle . '%';
    }    
    
    $query .= " GROUP BY c.carte_id
                ORDER BY c.denumire ASC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $books = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'data' => $books,
        'count' => count($books)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
