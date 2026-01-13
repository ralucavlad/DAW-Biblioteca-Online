<?php
session_start();
require_once __DIR__ . '/../common/db.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Neautorizat']);
    exit;
}

try {
    $companie_id = $_GET['id'] ?? null;
    
    if (!$companie_id) {
        throw new Exception('ID companie necesar');
    }
    
    $conn = getDatabaseConnection();
    
    $stmt = $conn->prepare("SELECT * FROM companie WHERE companie_id = ?");
    $stmt->execute([$companie_id]);
    $company = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$company) {
        throw new Exception('Compania nu a fost găsită');
    }
    
    // Count managers for this company
    $managerStmt = $conn->prepare("
        SELECT COUNT(*) 
        FROM utilizator u
        JOIN tip_utilizator t ON u.tip_utilizator_id = t.tip_utilizator_id
        WHERE u.companie_id = ? AND t.denumire = 'manager'
    ");
    $managerStmt->execute([$companie_id]);
    $company['managers_count'] = (int)$managerStmt->fetch(PDO::FETCH_COLUMN);
    
    echo json_encode([
        'success' => true,
        'company' => $company
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
