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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metodă invalidă']);
    exit;
}

$telefon = trim($_POST['telefon'] ?? '');
$adresa = trim($_POST['adresa'] ?? '');
$departament = trim($_POST['departament'] ?? '');
$data_nasterii = trim($_POST['data_nasterii'] ?? '');

// Validate phone number if provided
if (!empty($telefon) && !preg_match('/^07[0-9]{8}$/', $telefon)) {
    echo json_encode(['success' => false, 'message' => 'Numărul de telefon trebuie să înceapă cu 07 și să aibă 10 cifre']);
    exit;
}

try {
    $conn = getDatabaseConnection();
    
    $sql = "UPDATE utilizator SET 
            telefon = ?,
            adresa = ?,
            departament = ?,
            data_nasterii = ?
            WHERE utilizator_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        !empty($telefon) ? $telefon : null,
        !empty($adresa) ? $adresa : null,
        !empty($departament) ? $departament : null,
        !empty($data_nasterii) ? $data_nasterii : null,
        $_SESSION['utilizator_id']
    ]);
    
    echo json_encode(['success' => true, 'message' => 'Profilul a fost actualizat cu succes!']);
    
} catch (Exception $e) {
    error_log("Profile update error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Eroare la actualizarea profilului']);
}
?>
