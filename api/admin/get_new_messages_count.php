<?php
/**
 * Get New Messages Count API
 * Returns the number of unread contact messages
 */
session_start();
require_once __DIR__ . '/../common/db.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Neautorizat']);
    exit;
}

try {
    $conn = getDatabaseConnection();
    
    $stmt = $conn->query("SELECT COUNT(*) FROM contact_mesaj WHERE stare = 'nou'");
    $result = $stmt->fetch(PDO::FETCH_COLUMN);
    
    echo json_encode([
        'success' => true,
        'count' => (int)$result
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
