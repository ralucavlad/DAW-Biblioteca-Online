<?php
session_start();
require_once __DIR__ . '/../common/db.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Neautorizat']);
    exit;
}

try {
    $conn = getDatabaseConnection();
    
    // Total companies
    $stmtTotal = $conn->query("SELECT COUNT(*) FROM companie");
    $total = $stmtTotal->fetch(PDO::FETCH_COLUMN);
    
    // Total managers
    $stmtManagers = $conn->query("SELECT COUNT(DISTINCT u.utilizator_id) 
                      FROM utilizator u
                      JOIN tip_utilizator t ON u.tip_utilizator_id = t.tip_utilizator_id
                      WHERE t.denumire = 'manager' AND u.stare = 'activ'");
    $managers = $stmtManagers->fetch(PDO::FETCH_COLUMN);
    
    echo json_encode([
        'success' => true,
        'stats' => [
            'total' => (int)$total,
            'managers' => (int)$managers
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
