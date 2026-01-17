<?php
/**
 * Register API
 * Handles new user registration
 */

session_start();
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/utils.php';
require_once __DIR__ . '/email_helpers.php';

// Set JSON header early
header('Content-Type: application/json');

$email = $_POST['email'] ?? '';
$password = $_POST['parola'] ?? '';
$confirm_password = $_POST['parola_confirm'] ?? '';
$nume = $_POST['nume'] ?? '';
$prenume = $_POST['prenume'] ?? '';
$companie_id = $_POST['companie_id'] ?? null;
$recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';

// Validate required fields
if (empty($email) || empty($password) || empty($confirm_password) || empty($nume) || empty($prenume) || empty($companie_id)) {
    sendJsonResponse(false, 'Va rugam completati toate campurile obligatorii');
    exit;
}

// Verify reCAPTCHA
$recaptchaResult = verifyRecaptcha($recaptchaResponse);
if (!$recaptchaResult['success']) {
    sendJsonResponse(false, 'Verificare reCAPTCHA eșuată: ' . ($recaptchaResult['error'] ?? 'Unknown error'));
    exit;
}

// Validate password match
if ($password !== $confirm_password) {
    sendJsonResponse(false, 'Parolele nu coincid');
    exit;
}

// Validate email format using PHP's built-in filter
// FILTER_VALIDATE_EMAIL checks if the email follows RFC 5321 standard (e.g., user@domain.com)
// Returns the email if valid, or false if invalid format
// This is server-side validation - prevents invalid emails even if client-side validation is bypassed
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendJsonResponse(false, 'Adresa de email nu este valida');
    exit;
}

// Get database connection
$conn = getDatabaseConnection();

try {
    // Check if email already exists
    $stmt = $conn->prepare("SELECT utilizator_id FROM utilizator WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        sendJsonResponse(false, 'Acest email este deja inregistrat');
        exit;
    }
    
    // Generate username from email (before @)
    $nume_utilizator = explode('@', $email)[0];
    
    // Check if username already exists, if so, add a number
    $original_username = $nume_utilizator;
    $counter = 1;
    while (true) {
        $stmt = $conn->prepare("SELECT utilizator_id FROM utilizator WHERE nume_utilizator = ?");
        $stmt->execute([$nume_utilizator]);
        if (!$stmt->fetch()) {
            break; // Username is available
        }
        $nume_utilizator = $original_username . $counter;
        $counter++;
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Generate activation token
    $activationToken = bin2hex(random_bytes(32));
    
    // Insert new user (tip_utilizator_id = 3 for regular user)
    $stmt = $conn->prepare("
        INSERT INTO utilizator (
            tip_utilizator_id, companie_id, nume_utilizator, email, parola, 
            nume, prenume, stare, data_inregistrare, token_activare_cont
        ) VALUES (3, ?, ?, ?, ?, ?, ?, 'in_asteptare', NOW(), ?)
    ");
    
    $stmt->execute([
        $companie_id,
        $nume_utilizator,
        $email,
        $hashed_password,
        $nume,
        $prenume,
        $activationToken
    ]);
    
    // Send activation email
    if (sendActivationEmail($email, $nume, $prenume, $activationToken)) {
        sendJsonResponse(true, 'Contul a fost creat cu succes! Va rugam verificati emailul pentru a activa contul.');
    } else {
        sendJsonResponse(false, 'Cont creat, dar eroare la trimiterea emailului de activare. Contactati administratorul.');
    }
    
} catch (Exception $e) {
    error_log("Registration error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    sendJsonResponse(false, 'Eroare la crearea contului: ' . $e->getMessage());
} catch (Error $e) {
    error_log("Registration fatal error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    sendJsonResponse(false, 'Eroare fatală: ' . $e->getMessage());
}
?>
