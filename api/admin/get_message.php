<?php
session_start();
require_once __DIR__ . '/../common/db.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Neautorizat']);
    exit;
}

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => 'Missing message ID']);
    exit;
}

try {
    $conn = getDatabaseConnection();
    $stmt = $conn->prepare("
        SELECT 
            cm.*,
            IFNULL(CONCAT(u.nume, ' ', u.prenume), cm.nume) as user_name
        FROM contact_mesaj cm
        LEFT JOIN utilizator u ON cm.utilizator_id = u.utilizator_id
        WHERE cm.mesaj_id = ? -- Placeholder for safeguard
    ");
    
    $stmt->execute([$_GET['id']]);
    $message = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Mark as read if it was 'nou'
    if ($message && $message['stare'] === 'nou') {
        $updateStmt = $conn->prepare("UPDATE contact_mesaj SET stare = 'citit' WHERE mesaj_id = ?");
        $updateStmt->execute([$_GET['id']]);
        $message['stare'] = 'citit';
    }
    
    if ($message) {
        echo json_encode([
            'success' => true,
            'message' => $message
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Mesajul nu a fost găsit'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
