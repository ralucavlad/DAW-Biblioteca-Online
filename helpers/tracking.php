<?php
// Session and page tracking helper functions

/**
 * Get database connection for tracking (singleton pattern)
 */
function getTrackingConnection() {
    static $conn = null;
    
    if ($conn === null) {
        try {
            require_once __DIR__ . '/../config/config.php';
            $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            return null;
        }
    }
    
    return $conn;
}

/**
 * Start new session tracking record
 * @return int|false Session ID or false on failure
 */
function startSessionTracking($utilizator_id = null) {
    $conn = getTrackingConnection();
    if (!$conn) return false;
    
    try {
        $stmt = $conn->prepare("
            INSERT INTO sesiune (utilizator_id, adresa_ip, agent_utilizator, data_inceput)
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([
            $utilizator_id,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
        
        $_SESSION['tracking_session_id'] = $conn->lastInsertId();
        return $_SESSION['tracking_session_id'];
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * End session tracking
 */
function endSessionTracking($sessionId = null) {
    $conn = getTrackingConnection();
    if (!$conn) return false;
    
    $sessionId = $sessionId ?? $_SESSION['tracking_session_id'] ?? null;
    
    if ($sessionId) {
        try {
            $stmt = $conn->prepare("UPDATE sesiune SET data_sfarsit = NOW() WHERE sesiune_id = ?");
            $stmt->execute([$sessionId]);
            unset($_SESSION['tracking_session_id']);
        } catch (PDOException $e) {
            return false;
        }
    }
}

/**
 * Track page visit and auto-create session if needed
 */
function trackPageVisit($url = null, $pageTitle = null) {
    $conn = getTrackingConnection();
    if (!$conn) return false;
    
    // Auto-detect URL and generate title from filename
    $url = $url ?? $_SERVER['REQUEST_URI'] ?? '/';
    if (!$pageTitle) {
        $fileName = basename(parse_url($url, PHP_URL_PATH), '.php');
        $pageTitle = ucfirst(str_replace(['-', '_'], ' ', $fileName));
    }
    
    // Get or create tracking session
    $sessionId = $_SESSION['tracking_session_id'] ?? startSessionTracking($_SESSION['utilizator_id'] ?? null);
    $utilizatorId = $_SESSION['utilizator_id'] ?? null;
    
    try {
        // Insert page visit
        $stmt = $conn->prepare("
            INSERT INTO vizitare_pagina (sesiune_id, utilizator_id, url, titlu_pagina, data_vizitare)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$sessionId, $utilizatorId, $url, $pageTitle]);
        
        // Increment page count in session
        $stmt = $conn->prepare("UPDATE sesiune SET pagini_vizitate = pagini_vizitate + 1 WHERE sesiune_id = ?");
        $stmt->execute([$sessionId]);
        
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Auto-track current page (call at page start)
 */
function autoTrackPage() {
    trackPageVisit();
}
