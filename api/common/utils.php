<?php
/**
 * Verify reCAPTCHA response from Google
 * @param string $response reCAPTCHA token from client
 * @return array ['success' => bool, 'error' => string|null]
 */
function verifyRecaptcha($response) {
    // Validate inputs
    if (empty($response)) {
        return ['success' => false, 'error' => 'reCAPTCHA response is empty'];
    }
    
    // Send verification request to Google
    $url = "https://www.google.com/recaptcha/api/siteverify?secret=" . RECAPTCHA_SECRET_KEY . "&response={$response}";
    $verify = @file_get_contents($url);
    
    if ($verify === false) {
        return ['success' => false, 'error' => 'Failed to verify reCAPTCHA'];
    }
    
    // Parse Google's response
    $result = json_decode($verify);
    
    if (!$result || !isset($result->success)) {
        return ['success' => false, 'error' => 'Invalid reCAPTCHA response'];
    }
    
    // Return success or error details from Google
    return $result->success === true 
        ? ['success' => true]
        : ['success' => false, 'error' => implode(', ', $result->{'error-codes'} ?? ['Unknown error'])];
}
