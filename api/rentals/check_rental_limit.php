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
    
    // Count active rentals
    $countQuery = "SELECT COUNT(*) as total FROM inchiriere 
                   WHERE utilizator_id = ? AND stare = 'activa'";
    $countStmt = $conn->prepare($countQuery);
    $countStmt->execute([$userId]);
    $result = $countStmt->fetch();
    
    $activeRentals = (int)$result['total'];
    $canRent = $activeRentals < 3;
    
    echo json_encode([
        'success' => true,
        'active_rentals' => $activeRentals,
        'can_rent' => $canRent,
        'is_logged_in' => true,
        'message' => $canRent ? null : 'Ai atins limita maximă de 3 cărți închiriate simultan.'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
