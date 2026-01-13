<?php
/**
 * Verify Code API
 * Step 2: Verify the code entered by user
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

session_start();

require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../../helpers/tracking.php';

// Set JSON header early
header('Content-Type: application/json');

$code = $_POST['code'] ?? '';
$sessionToken = $_POST['session_token'] ?? '';

// Validate input
if (empty($code)) {
    sendJsonResponse(false, 'Codul de verificare este obligatoriu');
    exit;
}

// Verify session token
if (!isset($_SESSION['session_token']) || $_SESSION['session_token'] !== $sessionToken) {
    sendJsonResponse(false, 'Sesiune invalidă. Începeți din nou.');
    exit;
}

// Check if code expired
if (!isset($_SESSION['verification_expires']) || time() > $_SESSION['verification_expires']) {
    sendJsonResponse(false, 'Codul a expirat. Solicitați un cod nou.');
    exit;
}

// Verify code
if (!isset($_SESSION['verification_code']) || $_SESSION['verification_code'] !== $code) {
    sendJsonResponse(false, 'Cod de verificare incorect');
    exit;
}

// Code is correct, log user in
$conn = getDatabaseConnection();

try {
    // Get full user data
    $stmt = $conn->prepare("
        SELECT u.utilizator_id, u.nume, u.prenume, u.email, u.telefon, u.data_nasterii, tu.denumire as rol
        FROM utilizator u
        JOIN tip_utilizator tu ON u.tip_utilizator_id = tu.tip_utilizator_id
        WHERE u.utilizator_id = ?
    ");
    $stmt->execute([$_SESSION['verification_user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        // Set session variables
        $_SESSION['user_id'] = $user['utilizator_id'];
        $_SESSION['utilizator_id'] = $user['utilizator_id'];
        $_SESSION['nume'] = $user['nume'];
        $_SESSION['prenume'] = $user['prenume'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['rol'] = $user['rol'];
        $_SESSION['logged_in'] = true;
        
        // Clear verification data
        unset($_SESSION['verification_code']);
        unset($_SESSION['verification_email']);
        unset($_SESSION['verification_user_id']);
        unset($_SESSION['verification_expires']);
        unset($_SESSION['session_token']);
        
        // Update last login time
        $updateStmt = $conn->prepare("
            UPDATE utilizator 
            SET ultima_autentificare = NOW() 
            WHERE utilizator_id = ?
        ");
        $updateStmt->execute([$user['utilizator_id']]);
        
        // Start session tracking
        startSessionTracking($user['utilizator_id']);
        
        sendJsonResponse(true, 'Autentificare reușită!', [
            'redirect_url' => 'dashboard.php',
            'user' => [
                'name' => $user['nume'],
                'email' => $user['email'],
                'role' => $user['rol']
            ]
        ]);
    } else {
        sendJsonResponse(false, 'Utilizator nu a fost găsit');
    }
    
} catch (Exception $e) {
    error_log("Verify code error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    sendJsonResponse(false, 'Eroare la procesarea codului: ' . $e->getMessage());
} catch (Error $e) {
    error_log("Verify code fatal error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    sendJsonResponse(false, 'Eroare fatală: ' . $e->getMessage());
}
?>
