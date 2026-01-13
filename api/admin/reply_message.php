<?php
session_start();
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../auth/email_helpers.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Neautorizat']);
    exit;
}

$conn = getDatabaseConnection();
if (!$conn) {
    echo json_encode(['success' => false, 'error' => 'Conexiunea cu baza de date a esuat']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true); // Read JSON input

if (!isset($input['message_id']) || !isset($input['reply'])) {
    echo json_encode(['success' => false, 'error' => 'Lipsesc câmpuri obligatorii']);
    exit;
}

$messageId = $input['message_id'];
$reply = trim($input['reply']);

if (empty($reply)) {
    echo json_encode(['success' => false, 'error' => 'Răspunsul nu poate fi gol']);
    exit;
}

try {
    // Get message details
    $stmt = $conn->prepare("
        SELECT cm.*, 
            IFNULL(CONCAT(u.nume, ' ', u.prenume), cm.nume) as user_name
        FROM contact_mesaj cm
        LEFT JOIN utilizator u ON cm.utilizator_id = u.utilizator_id
        WHERE cm.mesaj_id = ?
    ");
    $stmt->execute([$messageId]);
    $message = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$message) {
        echo json_encode(['success' => false, 'error' => 'Mesajul nu a fost găsit']);
        exit;
    }
    
    // Update message with reply
    $updateStmt = $conn->prepare("
        UPDATE contact_mesaj 
        SET raspuns = ?, 
            data_raspuns = NOW(), 
            stare = 'raspuns'
        WHERE mesaj_id = ?
    ");
    $updateStmt->execute([$reply, $messageId]);
    
    // Send email notification to user
    $emailSent = sendContactReplyEmail(
        $message['email'],
        $message['user_name'],
        $message['subiect'],
        $message['mesaj'],
        $reply
    );
    
    echo json_encode([
        'success' => true,
        'message' => 'Răspuns trimis cu succes',
        'email_sent' => $emailSent
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
