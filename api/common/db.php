<?php
/**
 * Database utilities
 * Common database connection and helper functions for all API endpoints
 */

require_once __DIR__ . '/../../config/config.php';

/**
 * Get database connection
 * @return PDO|null Returns PDO connection or null on failure
 */
function getDatabaseConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);
        return $pdo;
    } catch (PDOException $e) {
        error_log("Database connection error: " . $e->getMessage());
        return null;
    }
}

/**
 * Send JSON response (extended version)
 * @param bool $success Success status
 * @param string $message Response message
 * @param array $additionalData Additional data to include
 * @param int $statusCode HTTP status code
 */
function sendJsonResponse($success, $message, $additionalData = [], $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    
    $response = [
        'success' => $success,
        'message' => $message
    ];
    
    if (!empty($additionalData)) {
        $response = array_merge($response, $additionalData);
    }
    
    echo json_encode($response);
    exit;
}
