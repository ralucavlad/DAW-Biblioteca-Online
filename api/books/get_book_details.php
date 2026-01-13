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
    
    $bookId = (int)$_GET['id'];
    
    // Get book details with authors and domains
    $query = "SELECT 
                c.carte_id,
                c.denumire,
                c.isbn,
                c.descriere,
                c.editura,
                c.an_publicare,
                c.nr_pagini,
                c.limba,
                c.tip_format,
                c.url_coperta,
                c.nr_exemplare_totale,
                c.nr_exemplare_disponibile,
                c.data_adaugare,
                GROUP_CONCAT(DISTINCT a.nume ORDER BY a.nume SEPARATOR ', ') as autori,
                GROUP_CONCAT(DISTINCT d.denumire ORDER BY d.denumire SEPARATOR ', ') as domenii
              FROM carte c
              LEFT JOIN carte_autor ca ON c.carte_id = ca.carte_id
              LEFT JOIN autor a ON ca.autor_id = a.autor_id
              LEFT JOIN carte_domeniu cd ON c.carte_id = cd.carte_id
              LEFT JOIN domeniu d ON cd.domeniu_id = d.domeniu_id
              WHERE c.carte_id = :book_id
              GROUP BY c.carte_id";
    
    $stmt = $conn->prepare($query);
    $stmt->execute([':book_id' => $bookId]);
    $book = $stmt->fetch();
    
    if (!$book) {
        throw new Exception('Cartea nu a fost găsită');
    }
    
    // Get average rating and review count
    $ratingQuery = "SELECT 
                        COUNT(*) as total_reviews,
                        COALESCE(AVG(evaluare), 0) as avg_rating
                    FROM recenzie
                    WHERE carte_id = :book_id";
    
    $ratingStmt = $conn->prepare($ratingQuery);
    $ratingStmt->execute([':book_id' => $bookId]);
    $ratingData = $ratingStmt->fetch();
    
    $book['total_reviews'] = $ratingData['total_reviews'];
    $book['avg_rating'] = round($ratingData['avg_rating'], 1);
    
    echo json_encode([
        'success' => true,
        'data' => $book
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
