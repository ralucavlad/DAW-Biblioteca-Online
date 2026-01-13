<?php
require_once __DIR__ . '/../common/db.php';

try {
    // Get database connection
    $conn = getDatabaseConnection();
    
    // Fetch all companies
    $stmt = $conn->prepare("
        SELECT companie_id, nume
        FROM companie 
        ORDER BY nume ASC
    ");
    
    $stmt->execute();
    $companies = $stmt->fetchAll(PDO::FETCH_ASSOC);
          
    echo json_encode([
        'success' => true,
        'count' => count($companies),
        'companies' => $companies
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
