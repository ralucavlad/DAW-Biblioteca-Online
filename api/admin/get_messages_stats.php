<?php
session_start();
require_once __DIR__ . '/../common/db.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Neautorizat']);
    exit;
}

$conn = getDatabaseConnection();
if (!$conn) {
    echo json_encode(['success' => false, 'error' => 'Conexiunea cu baza de date a esuat']);
    exit;
}

try {
    // Total messages
    $totalStmt = $conn->query("SELECT COUNT(*) FROM contact_mesaj");
    $total = $totalStmt->fetch(PDO::FETCH_COLUMN);
    
    // New messages (nou)
    $newStmt = $conn->query("SELECT COUNT(*) FROM contact_mesaj WHERE stare = 'nou'");
    $new = $newStmt->fetch(PDO::FETCH_COLUMN);
    
    // Read messages (citit)
    $readStmt = $conn->query("SELECT COUNT(*) FROM contact_mesaj WHERE stare = 'citit'");
    $read = $readStmt->fetch(PDO::FETCH_COLUMN);
    
    // Answered messages (raspuns)
    $answeredStmt = $conn->query("SELECT COUNT(*) FROM contact_mesaj WHERE stare = 'raspuns'");
    $answered = $answeredStmt->fetch(PDO::FETCH_COLUMN);
    
    echo json_encode([
        'success' => true,
        'total' => (int)$total,
        'new' => (int)$new,
        'read' => (int)$read,
        'answered' => (int)$answered
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
