<?php
/**
 * Verify Email API
 * Step 1: Verify email and password, then send verification code
 */

header('Content-Type: application/json');

session_start();

require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/email.php';
require_once __DIR__ . '/../common/utils.php';
require_once __DIR__ . '/email_helpers.php';
require_once __DIR__ . '/../../helpers/tracking.php';

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';

// Validate input
if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Email sau parolă lipsește']);
    exit;
}

// Check if email is whitelisted (no reCAPTCHA or email verification needed)
$isWhitelisted = in_array($email, WHITELIST_EMAILS);

// Verify reCAPTCHA only for non-whitelisted emails
if (!$isWhitelisted) {
    $recaptchaResult = verifyRecaptcha($recaptchaResponse);
    if (!$recaptchaResult['success']) {
        echo json_encode(['success' => false, 'message' => 'Verificare reCAPTCHA eșuată: ' . ($recaptchaResult['error'] ?? 'Unknown error')]);
        exit;
    }
}

// Check if user exists in database
$conn = getDatabaseConnection();

try {
    $stmt = $conn->prepare("
        SELECT u.utilizator_id, u.nume, u.prenume, u.email, u.parola, u.stare as status, tu.denumire as rol
        FROM utilizator u
        JOIN tip_utilizator tu ON u.tip_utilizator_id = tu.tip_utilizator_id
        WHERE u.email = ?
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Email sau parola incorecta']);
        exit;
    }
    
    // Verify password with native PHP password hashing
    if (!password_verify($password, $user['parola'])) {
        echo json_encode(['success' => false, 'message' => 'Email sau parola incorecta']);
        exit;
    }
    
    if ($user['status'] !== 'activ') {
        echo json_encode(['success' => false, 'message' => 'Contul dvs. nu este activ. Va rugam contactati administratorul.']);
        exit;
    }
    
    // Check if email is whitelisted (skip 2FA)
    if (in_array($email, WHITELIST_EMAILS)) {
        // Update last login time
        $updateStmt = $conn->prepare("
            UPDATE utilizator 
            SET ultima_autentificare = NOW() 
            WHERE utilizator_id = ?
        ");
        $updateStmt->execute([$user['utilizator_id']]);
        
        // Set session variables
        $_SESSION['user_id'] = $user['utilizator_id'];
        $_SESSION['user_name'] = $user['nume'] . ' ' . $user['prenume'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['rol'];
        $_SESSION['utilizator_id'] = $user['utilizator_id'];
        $_SESSION['nume'] = $user['nume'];
        $_SESSION['prenume'] = $user['prenume'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['rol'] = $user['rol'];
        $_SESSION['logged_in'] = true;
        
        // Start session tracking
        startSessionTracking($user['utilizator_id']);
        
        echo json_encode([
            'success' => true,
            'message' => 'Autentificare reușită',
            'skip_verification' => true,
            'redirect' => 'dashboard.php'
        ]);
        exit;
    }
    
    // Generate 6-digit verification code
    $verificationCode = sprintf('%06d', rand(0, 999999));
    
    // Store code in session with expiration (5 minutes)
    $_SESSION['verification_code'] = $verificationCode;
    $_SESSION['verification_email'] = $email;
    $_SESSION['verification_user_id'] = $user['utilizator_id'];
    $_SESSION['verification_expires'] = time() + 300; // 5 minutes
    
    // Generate session token - Used to validate the verification code submission in step 2
    // This ensures the code verification request comes from the same session
    $sessionToken = bin2hex(random_bytes(32));
    $_SESSION['session_token'] = $sessionToken;
    
    // Send verification code via email
    if (sendVerificationEmail($email, $user['nume'], $verificationCode)) {
        echo json_encode([
            'success' => true,
            'message' => 'Cod de verificare trimis cu succes',
            'session_token' => $sessionToken
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Eroare la trimiterea email-ului. Vă rugăm încercați din nou.']);
    }
    
} catch (Exception $e) {
    error_log("Error in verify_email.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Eroare la procesarea cererii'
    ]);
}
?>
