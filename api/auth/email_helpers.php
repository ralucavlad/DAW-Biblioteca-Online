<?php
/**
 * Email Helper Functions for Authentication
 */

require_once __DIR__ . '/../../mail/class.phpmailer.php';
require_once __DIR__ . '/../../helpers/email.php';
require_once __DIR__ . '/../common/utils.php';

/**
 * Configure and return PHPMailer instance
 */
function getConfiguredMailer() {
    $mail = new PHPMailer(true);
    $mail->IsSMTP();
    $mail->SMTPDebug = SMTP_DEBUG;
    $mail->SMTPAuth = true;
    $mail->SMTPSecure = SMTP_SECURE;
    $mail->Host = SMTP_HOST;
    $mail->Port = SMTP_PORT;
    $mail->Username = SMTP_USERNAME;
    $mail->Password = SMTP_PASSWORD;
    $mail->CharSet = 'UTF-8';
    $mail->IsHTML(true);
    $mail->SetFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
    $mail->AddReplyTo(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
    return $mail;
}

/**
 * Simple email template wrapper
 */
function getSimpleEmailTemplate($title, $content) {
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
        .footer { background: #f5f5f5; padding: 15px; text-align: center; font-size: 12px; color: #666; }
        .code-box { background: #f9f9f9; padding: 20px; text-align: center; border: 2px solid #333; border-radius: 4px; margin: 20px 0; font-size: 32px; font-weight: bold; letter-spacing: 8px; }
        .button { display: inline-block; padding: 12px 30px; background: #333; color: white; text-decoration: none; border-radius: 4px; margin: 20px 0; }
        .link-box { background: #f9f9f9; padding: 15px; border-left: 3px solid #333; word-break: break-all; font-size: 12px; color: #666; margin: 15px 0; }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <h2 style="margin: 0;">{$title}</h2>
        </div>
        <div class="content">
            {$content}
        </div>
        <div class="footer">
            Biblioteca Online &copy; 2026
        </div>
    </div>
</body>
</html>
HTML;
}

/**
 * Send verification code email
 */
function sendVerificationEmail($to, $name, $code) {
    try {
        $mail = getConfiguredMailer();
        $mail->AddAddress($to, $name);
        $mail->Subject = 'Cod de verificare - Biblioteca Online';
        
        $content = <<<HTML
<p>Bună, <strong>{$name}</strong>!</p>
<p>Codul tău de verificare este:</p>
<div class="code-box">{$code}</div>
<p>Acest cod este valabil <strong>5 minute</strong>.</p>
HTML;
        
        $mail->Body = getSimpleEmailTemplate('Cod de verificare', $content);
        $mail->AltBody = "Codul de verificare: {$code}. Valabil 5 minute.";
        $mail->Send();
        return true;
    } catch (Exception $e) {
        error_log("Email Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Send activation email with link
 */
function sendActivationEmail($to, $nume, $prenume, $token) {
    try {
        $mail = getConfiguredMailer();
        $mail->AddAddress($to, $nume . ' ' . $prenume);
        $mail->Subject = 'Activare cont - Biblioteca Online';
        
        $activationLink = "https://rpirvulescu.daw.ssmr.ro/activate.php?token=" . $token;
        
        $content = <<<HTML
<p>Bună, <strong>{$nume} {$prenume}</strong>!</p>
<p>Contul tău a fost creat cu succes. Pentru a-l activa, dă click pe butonul de mai jos:</p>
<div style="text-align: center;">
    <a href="{$activationLink}" class="button">Activează cont</a>
</div>
<p>Sau copiază acest link:</p>
<div class="link-box">{$activationLink}</div>
HTML;
        
        $mail->Body = getSimpleEmailTemplate('Activare cont', $content);
        $mail->AltBody = "Activeaza contul: {$activationLink}";
        $mail->Send();
        return true;
    } catch (Exception $e) {
        error_log("Email Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Send contact reply email to user
 */
function sendContactReplyEmail($to, $name, $subject, $originalMessage, $reply) {
    try {
        $mail = getConfiguredMailer();
        $mail->AddAddress($to, $name);
        $mail->Subject = 'Re: ' . $subject . ' - Biblioteca Online';
        
        $content = <<<HTML
<p>Bună, <strong>{$name}</strong>!</p>
<p>Am primit mesajul tău și îți răspundem:</p>
<div style="background: #f9f9f9; padding: 15px; border-left: 3px solid #333; margin: 20px 0;">
    <strong>Răspunsul nostru:</strong><br><br>
    {$reply}
</div>
<hr style="border: none; border-top: 1px solid #ddd; margin: 30px 0;">
<div style="background: #f9f9f9; padding: 15px; border-left: 3px solid #999; margin: 20px 0;">
    <strong>Mesajul tău:</strong><br>
    <strong>Subiect:</strong> {$subject}<br><br>
    {$originalMessage}
</div>
HTML;
        
        $mail->Body = getSimpleEmailTemplate('Răspuns la mesaj', $content);
        $mail->AltBody = "Raspuns: {$reply}\n\nMesajul tau: {$originalMessage}";
        $mail->Send();
        return true;
    } catch (Exception $e) {
        error_log("Email Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Send rental confirmation email
 */
function sendRentalConfirmation($to, $name, $bookName, $dataPrimire, $dataScadenta) {
    try {
        $mail = getConfiguredMailer();
        $mail->AddAddress($to, $name);
        $mail->Subject = 'Confirmare inchiriere - Biblioteca Online';
        
        $dataPrimireFormatted = date('d.m.Y', strtotime($dataPrimire));
        $dataScadentaFormatted = date('d.m.Y', strtotime($dataScadenta));
        
        $content = <<<HTML
<p>Bună, <strong>{$name}</strong>!</p>
<p>Ai închiriat cu succes cartea:</p>
<div style="background: #f9f9f9; padding: 15px; border-left: 3px solid #333; margin: 20px 0; font-size: 18px; font-weight: bold;">
    {$bookName}
</div>
<div style="margin: 20px 0;">
    <div style="background: #f9f9f9; padding: 15px; margin: 10px 0; border-radius: 4px;">
        <strong>Data estimată de primire:</strong><br>
        {$dataPrimireFormatted}
        <div style="font-size: 12px; color: #666; margin-top: 5px;">Cartea va ajunge în aproximativ 4 zile</div>
    </div>
    <div style="background: #f9f9f9; padding: 15px; margin: 10px 0; border-radius: 4px;">
        <strong>Data de returnare:</strong><br>
        {$dataScadentaFormatted}
        <div style="font-size: 12px; color: #666; margin-top: 5px;">Ai la dispoziție 30 de zile</div>
    </div>
</div>
<p style="font-size: 14px; color: #666;"><strong>Important:</strong> Returnează cartea în stare bună până la data de scadență.</p>
HTML;
        
        $mail->Body = getSimpleEmailTemplate('Închiriere confirmată', $content);
        $mail->AltBody = "Ai inchiriat '{$bookName}'. Data primire: {$dataPrimireFormatted}. Data returnare: {$dataScadentaFormatted}.";
        $mail->Send();
        return true;
    } catch (Exception $e) {
        error_log("Email Error: " . $e->getMessage());
        return false;
    }
}
?>
