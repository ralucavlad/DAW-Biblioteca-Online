<?php
session_start();
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../common/db.php';

if (!isset($_SESSION['utilizator_id'])) {
    echo json_encode(['success' => false, 'message' => 'Trebuie să fii autentificat.']);
    exit;
}

if ($_SESSION['rol'] !== 'manager') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Acces interzis. Doar managerii pot accesa această resursă.']);
    exit();
}

try {
    $conn = getDatabaseConnection();
    
    $managerId = (int)$_SESSION['utilizator_id'];
    
    // Get manager's company
    $stmt = $conn->prepare("SELECT companie_id FROM utilizator WHERE utilizator_id = ?");
    $stmt->execute([$managerId]);
    $manager = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $companieId = $manager['companie_id'];
    
    // Get total employees in company
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total 
        FROM utilizator 
        WHERE companie_id = ? AND stare = 'activ'
    ");
    $stmt->execute([$companieId]);
    $totalEmployees = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get employees active today
    $stmt = $conn->prepare("
        SELECT COUNT(DISTINCT u.utilizator_id) as active_today
        FROM utilizator u
        JOIN sesiune s ON u.utilizator_id = s.utilizator_id
        WHERE u.companie_id = ? 
        AND u.stare = 'activ'
        AND DATE(s.data_inceput) = CURDATE()
    ");
    $stmt->execute([$companieId]);
    $activeToday = $stmt->fetch(PDO::FETCH_ASSOC)['active_today'];
    
    // Get total books rented by company employees
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total_rented
        FROM inchiriere i
        JOIN utilizator u ON i.utilizator_id = u.utilizator_id
        WHERE u.companie_id = ?
    ");
    $stmt->execute([$companieId]);
    $totalBooksRented = $stmt->fetch(PDO::FETCH_ASSOC)['total_rented'];
    
    // Get total reviews written by company employees
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total_reviews
        FROM recenzie r
        JOIN utilizator u ON r.utilizator_id = u.utilizator_id
        WHERE u.companie_id = ?
    ");
    $stmt->execute([$companieId]);
    $totalReviews = $stmt->fetch(PDO::FETCH_ASSOC)['total_reviews'];
    
    echo json_encode([
        'success' => true,
        'stats' => [
            'total_employees' => (int)$totalEmployees,
            'active_today' => (int)$activeToday,
            'total_books_rented' => (int)$totalBooksRented,
            'total_reviews' => (int)$totalReviews
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
