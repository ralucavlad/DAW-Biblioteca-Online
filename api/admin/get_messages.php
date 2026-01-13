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
    $stmt = $conn->query("
        SELECT 
            cm.mesaj_id,
            cm.utilizator_id,
            IFNULL(CONCAT(u.nume, ' ', u.prenume), cm.nume) as user_name,
            cm.email,
            cm.subiect,
            cm.mesaj,
            cm.data_trimitere,
            cm.stare,
            cm.raspuns,
            cm.data_raspuns
        FROM contact_mesaj cm
        LEFT JOIN utilizator u ON cm.utilizator_id = u.utilizator_id
        ORDER BY cm.data_trimitere DESC
    ");
    
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $messages
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
