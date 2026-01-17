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
    die('Acces interzis. Doar managerii pot genera rapoarte.');
}

try {
    $conn = getDatabaseConnection();
    
    $managerId = (int)$_SESSION['utilizator_id'];
    
    // Get manager's company
    $stmt = $conn->prepare("SELECT u.companie_id, c.nume FROM utilizator u JOIN companie c ON u.companie_id = c.companie_id WHERE u.utilizator_id = ?");
    $stmt->execute([$managerId]);
    $manager = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$manager || !$manager['companie_id']) {
        throw new Exception('Companie negăsită');
    }
    
    $companieId = $manager['companie_id'];
    $companieNume = $manager['nume'];
    
    // Get parameters
    $type = isset($_GET['type']) ? $_GET['type'] : 'pdf';
    $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
    $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
    
    // Get employee data
    $stmt = $conn->prepare("
        SELECT 
            u.nume,
            u.prenume,
            u.email,
            u.ultima_autentificare,
            (SELECT COUNT(*) FROM inchiriere i WHERE i.utilizator_id = u.utilizator_id AND DATE(i.data_inchiriere) BETWEEN ? AND ?) as total_inchirieri,
            (SELECT COUNT(*) FROM recenzie r WHERE r.utilizator_id = u.utilizator_id AND DATE(r.data_recenzie) BETWEEN ? AND ?) as total_recenzii,
            (SELECT COUNT(*) FROM sesiune s WHERE s.utilizator_id = u.utilizator_id AND DATE(s.data_inceput) BETWEEN ? AND ?) as total_sesiuni
        FROM utilizator u
        WHERE u.companie_id = ?
        AND u.stare = 'activ'
        ORDER BY u.nume, u.prenume
    ");
    
    $stmt->execute([$startDate, $endDate, $startDate, $endDate, $startDate, $endDate, $companieId]);
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    generatePDFReport($employees, $companieNume, $startDate, $endDate);
               
} catch (Exception $e) {
    http_response_code(500);
    die('Eroare: ' . $e->getMessage());
}

/**
 * Generate PDF Report
 */
function generatePDFReport($employees, $companieNume, $startDate, $endDate) {
    // For now, generate HTML that can be printed as PDF
    header('Content-Type: text/html; charset=utf-8');
    
    echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Raport Activitate Angajați - ' . htmlspecialchars($companieNume) . '</title>
    <link rel="icon" type="image/svg+xml" href="../../img/favicon.svg">
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .header { display: flex; align-items: center; gap: 15px; margin-bottom: 20px; }
        .header img { width: 50px; height: 50px; }
        h1 { color: #2c3e50; padding-bottom: 10px; margin: 0; }
        .info { background: #ecf0f1; padding: 15px; margin: 20px 0; border-radius: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background: #34495e; color: white; padding: 12px; text-align: left; }
        td { padding: 10px; border-bottom: 1px solid #ddd; }
        tr:nth-child(even) { background: #f8f9fa; }
        .footer { margin-top: 30px; text-align: center; color: #7f8c8d; font-size: 12px; }
        @media print {
            body { margin: 20px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="../../img/favicon.svg" alt="Logo">
        <h1>Raport Activitate Angajați</h1>
    </div>
    
    <div class="info">
        <strong>Companie:</strong> ' . htmlspecialchars($companieNume) . '<br>
        <strong>Perioada:</strong> ' . date('d.m.Y', strtotime($startDate)) . ' - ' . date('d.m.Y', strtotime($endDate)) . '<br>
        <strong>Data generare:</strong> ' . date('d.m.Y H:i') . '<br>
        <strong>Total angajați:</strong> ' . count($employees) . '
    </div>
    
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nume</th>
                <th>Email</th>
                <th>Ultima Autentificare</th>
                <th>Sesiuni</th>
                <th>Închirieri</th>
                <th>Recenzii</th>
            </tr>
        </thead>
        <tbody>';
    
    $nr = 1;
    foreach ($employees as $emp) {
        echo '<tr>
            <td>' . $nr++ . '</td>
            <td>' . htmlspecialchars($emp['nume'] . ' ' . $emp['prenume']) . '</td>
            <td>' . htmlspecialchars($emp['email']) . '</td>
            <td>' . ($emp['ultima_autentificare'] ? date('d.m.Y H:i', strtotime($emp['ultima_autentificare'])) : 'Niciodată') . '</td>
            <td>' . $emp['total_sesiuni'] . '</td>
            <td>' . $emp['total_inchirieri'] . '</td>
            <td>' . $emp['total_recenzii'] . '</td>
        </tr>';
    }
    
    echo '</tbody>
    </table>
    
    <div class="footer">
        <p>Raport generat de Biblioteca Online - © ' . date('Y') . '</p>
    </div>
    
    <div class="no-print" style="margin-top: 30px; text-align: center;">
        <button onclick="window.print()" style="padding: 10px 30px; background: #1a1c1d; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">
            Printează / Salvează ca PDF
        </button>
    </div>
</body>
</html>';
}
?>
