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
    
    // Get date filters
    $startDate = $_GET['start_date'];
    $endDate = $_GET['end_date'];
    
    // Get all employees with their activity stats
    $stmt = $conn->prepare("
        SELECT 
            u.utilizator_id,
            u.nume,
            u.prenume,
            u.email,
            u.ultima_autentificare,
            u.stare,
            (SELECT COUNT(*) 
             FROM inchiriere i 
             WHERE i.utilizator_id = u.utilizator_id
             AND DATE(i.data_inchiriere) BETWEEN ? AND ?) as total_inchirieri,
            (SELECT COUNT(*) 
             FROM recenzie r 
             WHERE r.utilizator_id = u.utilizator_id
             AND DATE(r.data_recenzie) BETWEEN ? AND ?) as total_recenzii,
            (SELECT COUNT(*) 
             FROM sesiune s 
             WHERE s.utilizator_id = u.utilizator_id 
             AND DATE(s.data_inceput) = CURDATE()) > 0 as active_today
        FROM utilizator u
        WHERE u.companie_id = ?
        AND u.stare = 'activ'
        ORDER BY u.ultima_autentificare DESC
    ");
    
    $stmt->execute([
        $startDate, $endDate,  // for inchiriere
        $startDate, $endDate,  // for recenzie
        $companieId
    ]);
    
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Convert active_today to boolean
    foreach ($employees as &$emp) {
        $emp['active_today'] = (bool)$emp['active_today'];
        $emp['total_inchirieri'] = (int)$emp['total_inchirieri'];
        $emp['total_recenzii'] = (int)$emp['total_recenzii'];
    }
    
    echo json_encode([
        'success' => true,
        'employees' => $employees,
        'count' => count($employees),
        'filters' => [
            'start_date' => $startDate,
            'end_date' => $endDate
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
