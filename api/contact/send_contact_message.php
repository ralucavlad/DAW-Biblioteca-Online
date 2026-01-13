<?php
session_start();

// Include files
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../../helpers/email.php';

require_once __DIR__ . '/../../mail/class.phpmailer.php';
require_once __DIR__ . '/../../mail/class.smtp.php';

if (!isset($_SESSION['utilizator_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Trebuie să fii autentificat.']);
    exit;
}

// Admins don't have access to send contact messages
if ($_SESSION['rol'] === 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Adminii nu pot trimite mesaje de contact.']);
    exit;
}

try {
    $conn = getDatabaseConnection();
    
    $userId = (int)$_SESSION['utilizator_id'];
    $subiect = trim($_POST['subiect'] ?? '');
    $mesaj = trim($_POST['mesaj'] ?? '');
    
    // Validation
    if (empty($subiect)) {
        throw new Exception('Subiectul este obligatoriu');
    }
    
    if (empty($mesaj)) {
        throw new Exception('Mesajul este obligatoriu');
    }
    
    if (strlen($subiect) > 255) {
        throw new Exception('Subiectul este prea lung (maxim 255 caractere)');
    }
    
    if (strlen($mesaj) > 2000) {
        throw new Exception('Mesajul este prea lung (maxim 2000 caractere)');
    }
    
    // Get user info
    $userQuery = "SELECT email, nume, prenume FROM utilizator WHERE utilizator_id = ?";
    $userStmt = $conn->prepare($userQuery);
    $userStmt->execute([$userId]);
    $user = $userStmt->fetch();
    
    $userName = trim($user['nume'] . ' ' . $user['prenume']);
    $userEmail = $user['email'];
    
    // Insert message into database
    $insertQuery = "INSERT INTO contact_mesaj (utilizator_id, nume, email, subiect, mesaj, stare) 
                    VALUES (?, ?, ?, ?, ?, 'nou')";
    $insertStmt = $conn->prepare($insertQuery);
    $insertStmt->execute([$userId, $userName, $userEmail, $subiect, $mesaj]);
    
    $mesajId = $conn->lastInsertId();
    
    // Send notification to admin@rpirvulescu.daw.ssmr.ro (hardcoded admin email)
    $adminEmail = 'admin@rpirvulescu.daw.ssmr.ro';
    
    $emailSubject = "Mesaj nou: {$subiect}";
    $emailBody = getContactEmailTemplate($userName, $userEmail, $subiect, $mesaj, $mesajId);
    
    // Send email using PHPMailer
    $mail = new PHPMailer(true);
    
    try {
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USERNAME;
            $mail->Password = SMTP_PASSWORD;
            $mail->SMTPSecure = SMTP_SECURE;
            $mail->Port = SMTP_PORT;
            $mail->CharSet = SMTP_CHARSET;
            $mail->SMTPDebug = SMTP_DEBUG;
            
            $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $mail->addAddress($adminEmail);
            $mail->addReplyTo($userEmail, $userName);
            
            $mail->isHTML(true);
            $mail->Subject = $emailSubject;
            $mail->Body = $emailBody;
            $mail->AltBody = strip_tags($emailBody);
            
            $mail->send();
        } catch (Exception $e) {
            error_log("Eroare trimitere email către admin: " . $e->getMessage());
        }
        
        // Send confirmation email to user
        try {
            $mail->clearAddresses();
            $mail->clearReplyTos();
            
            $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $mail->addAddress($userEmail, $userName);
            $mail->addReplyTo(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            
            $confirmationSubject = "Confirmare mesaj trimis - Biblioteca Online";
            $confirmationBody = getUserConfirmationEmailTemplate($userName, $subiect, $mesaj, $mesajId);
            
            $mail->Subject = $confirmationSubject;
            $mail->Body = $confirmationBody;
            $mail->AltBody = strip_tags($confirmationBody);
            
            $mail->send();
        } catch (Exception $e) {
            error_log("Eroare trimitere email de confirmare către utilizator: " . $e->getMessage());
        }
    
    echo json_encode([
        'success' => true,
        'message' => 'Mesajul a fost trimis cu succes!',
        'mesaj_id' => $mesajId
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

/**
 * Generate email template for contact message
 */
function getContactEmailTemplate($userName, $userEmail, $subiect, $mesaj, $mesajId) {
    $date = date('d.m.Y H:i');
    
    return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f5f5f5; margin: 0; padding: 20px; }
        .card { max-width: 600px; margin: 0 auto; background: white; border: 1px solid #ddd; border-radius: 8px; overflow: hidden; }
        .header { background: #333; color: white; padding: 20px; }
        .content { padding: 30px; }
        .field { margin-bottom: 20px; }
        .label { font-weight: bold; color: #666; font-size: 12px; text-transform: uppercase; margin-bottom: 5px; }
        .value { color: #333; }
        .message { background: #f9f9f9; padding: 15px; border-left: 3px solid #333; margin-top: 10px; }
        .footer { background: #f5f5f5; padding: 15px; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <h2 style="margin: 0;">Mesaj nou de contact</h2>
        </div>
        
        <div class="content">
            <div class="field">
                <div class="label">De la</div>
                <div class="value"><strong>{$userName}</strong><br>{$userEmail}</div>
            </div>
            
            <div class="field">
                <div class="label">Data</div>
                <div class="value">{$date}</div>
            </div>
            
            <div class="field">
                <div class="label">Subiect</div>
                <div class="value"><strong>{$subiect}</strong></div>
            </div>
            
            <div class="field">
                <div class="label">Mesaj</div>
                <div class="message">{$mesaj}</div>
            </div>
        </div>
        
        <div class="footer">
            Biblioteca Online &copy; 2026 | ID: #{$mesajId}
        </div>
    </div>
</body>
</html>
HTML;
}

/**
 * Generate confirmation email template for user
 */
function getUserConfirmationEmailTemplate($userName, $subiect, $mesaj, $mesajId) {
    $date = date('d.m.Y H:i');
    
    return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f5f5f5; margin: 0; padding: 20px; }
        .card { max-width: 600px; margin: 0 auto; background: white; border: 1px solid #ddd; border-radius: 8px; overflow: hidden; }
        .header { background: #333; color: white; padding: 20px; }
        .content { padding: 30px; }
        .field { margin-bottom: 20px; }
        .label { font-weight: bold; color: #666; font-size: 12px; text-transform: uppercase; margin-bottom: 5px; }
        .value { color: #333; }
        .message { background: #f9f9f9; padding: 15px; border-left: 3px solid #333; margin-top: 10px; }
        .footer { background: #f5f5f5; padding: 15px; text-align: center; font-size: 12px; color: #666; }
        .info-box { background: #f9f9f9; padding: 20px; border-radius: 4px; margin: 20px 0; text-align: center; }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <h2 style="margin: 0;">Mesaj trimis cu succes</h2>
        </div>
        
        <div class="content">
            <p>Bună, <strong>{$userName}</strong>!</p>
            <p>Mesajul tău a fost trimis cu succes. Vei fi contactat în curând.</p>
            
            <div class="info-box">
                <p style="margin: 0; font-weight: bold;">Rezumatul mesajului tău:</p>
            </div>
            
            <div class="field">
                <div class="label">Data</div>
                <div class="value">{$date}</div>
            </div>
            
            <div class="field">
                <div class="label">Subiect</div>
                <div class="value"><strong>{$subiect}</strong></div>
            </div>
            
            <div class="field">
                <div class="label">Mesaj</div>
                <div class="message">{$mesaj}</div>
            </div>
        </div>
        
        <div class="footer">
            Biblioteca Online &copy; 2026 | ID: #{$mesajId}
        </div>
    </div>
</body>
</html>
HTML;
}
