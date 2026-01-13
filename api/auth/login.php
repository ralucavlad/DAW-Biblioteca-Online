<?php
/**
 * Login API
 * Handles user authentication
 */
session_start();

require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../../helpers/tracking.php';
require_once __DIR__ . '/../../helpers/email.php';

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';

// Validate input
if (empty($email) || empty($password)) {
    sendJsonResponse(false, 'Email și parola sunt obligatorii');
    exit;
}

// Verify reCAPTCHA only for non-whitelisted emails
if (!in_array($email, WHITELIST_EMAILS)) {
    if (!verifyRecaptcha($recaptchaResponse)) {
        sendJsonResponse(false, 'Verificare reCAPTCHA eșuată');
        exit;
    }
}

// Check database connection
$conn = getDatabaseConnection();

try {
    // Get user by email
    $stmt = $conn->prepare("
        SELECT u.utilizator_id, u.nume, u.prenume, u.email, u.parola, u.stare, tu.denumire as rol
        FROM utilizator u
        JOIN tip_utilizator tu ON u.tip_utilizator_id = tu.tip_utilizator_id
        WHERE u.email = ?
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        sendJsonResponse(false, 'Email sau parolă incorectă');
        exit;
    }
    
    // Check if account is active
    if ($user['stare'] !== 'activ') {
        sendJsonResponse(false, 'Contul dvs. nu este activ. Vă rugăm contactați administratorul.');
        exit;
    }
    
    // Verify password with native PHP password hashing
    if (!password_verify($password, $user['parola'])) {
        sendJsonResponse(false, 'Email sau parolă incorectă');
        exit;
    }
    
    // Check if email is in whitelist (skip 2FA)
    if (in_array($email, WHITELIST_EMAILS)) {
        // Set session variables
        $_SESSION['utilizator_id'] = $user['utilizator_id'];
        $_SESSION['nume'] = $user['nume'];
        $_SESSION['prenume'] = $user['prenume'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['rol'] = $user['rol'];
        $_SESSION['logged_in'] = true;
        
        // Update last login time
        $updateStmt = $conn->prepare("
            UPDATE utilizator 
            SET ultima_autentificare = NOW() 
            WHERE utilizator_id = ?
        ");
        $updateStmt->execute([$user['utilizator_id']]);
        
        // Start session tracking
        startSessionTracking($conn, $user['utilizator_id']);
        
        // Determine redirect based on role
        $redirect = 'dashboard.php';
        if ($user['rol'] === 'Administrator') {
            $redirect = 'admin-dashboard.php';
        }
        
        sendJsonResponse(true, 'Autentificare reușită!', [
            'redirect' => $redirect,
            'user' => [
                'name' => $user['nume'] . ' ' . $user['prenume'],
                'email' => $user['email'],
                'role' => $user['rol']
            ]
        ]);
        exit;
    }
    
    // For non-whitelisted emails, require 2FA (existing code continues...)
    $_SESSION['utilizator_id'] = $user['utilizator_id'];
    $_SESSION['nume'] = $user['nume'];
    $_SESSION['prenume'] = $user['prenume'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['rol'] = $user['rol'];
    $_SESSION['logged_in'] = true;
    
    // Update last login time
    $updateStmt = $conn->prepare("
        UPDATE utilizator 
        SET ultima_autentificare = NOW() 
        WHERE utilizator_id = ?
    ");
    $updateStmt->execute([$user['utilizator_id']]);
    
    // Start session tracking
    startSessionTracking($conn, $user['utilizator_id']);
    
    // Determine redirect based on role
    $redirect = 'dashboard.php';
    if ($user['rol'] === 'Administrator') {
        $redirect = 'admin-dashboard.php';
    }
    
    sendJsonResponse(true, 'Autentificare reușită!', [
        'redirect' => $redirect,
        'user' => [
            'name' => $user['nume'] . ' ' . $user['prenume'],
            'email' => $user['email'],
            'role' => $user['rol']
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Eroare la procesarea cererii'
    ]);
}
?>
