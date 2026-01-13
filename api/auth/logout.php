<?php
/**
 * Logout API
 * Handles user logout
 */

session_start();

try {
    // Check if user is logged in
    if (!isset($_SESSION['utilizator_id'])) {
        echo json_encode(['success' => false, 'message' => 'Nu sunteți autentificat']);
        exit();
    }
    
    // End session tracking
    if (isset($_SESSION['tracking_session_id'])) {
        require_once __DIR__ . '/../../helpers/tracking.php';
        endSessionTracking($_SESSION['tracking_session_id']);
    }

    // Clear all session variables
    $_SESSION = array();

    // Destroy the session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time()-42000, '/');
    }

    // Destroy the session
    session_destroy();

    // Return success response
    echo json_encode(['success' => true, 'message' => 'Delogare reușită']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Eroare la delogare: ' . $e->getMessage()]);
}
exit();
?>
