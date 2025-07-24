<?php
/**
 * Security Configuration for Nun's Dimsum
 * Configuration untuk rate limiting dan security features
 */

// Rate Limiting Configuration
define('RATE_LIMIT_MAX_ATTEMPTS', 5);           // Maximum login attempts
define('RATE_LIMIT_TIME_WINDOW', 900);          // Time window in seconds (15 minutes)
define('RATE_LIMIT_BLOCK_DURATION', 1800);     // Block duration in seconds (30 minutes)

// Security Settings
define('PASSWORD_MIN_LENGTH', 8);               // Minimum password length
define('SESSION_TIMEOUT', 3600);               // Session timeout in seconds (1 hour)
define('LOGIN_ATTEMPT_LOG', true);             // Enable login attempt logging

// IP Whitelist (Admin IPs that bypass rate limiting)
$SECURITY_IP_WHITELIST = [
    '127.0.0.1',        // Localhost
    '::1',              // IPv6 localhost
    // Add more trusted IPs here
];

/**
 * Check if IP is whitelisted
 */
function isWhitelistedIP($ip) {
    global $SECURITY_IP_WHITELIST;
    return in_array($ip, $SECURITY_IP_WHITELIST);
}

/**
 * Initialize RateLimiter with configuration
 */
function initializeRateLimiter($pdo) {
    return new RateLimiter(
        $pdo, 
        RATE_LIMIT_MAX_ATTEMPTS, 
        RATE_LIMIT_TIME_WINDOW, 
        RATE_LIMIT_BLOCK_DURATION
    );
}

/**
 * Security headers for better protection
 */
function setSecurityHeaders() {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // Only set HTTPS headers if on HTTPS
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

/**
 * Enhanced password validation
 */
function validatePassword($password) {
    $errors = [];
    
    if (strlen($password) < PASSWORD_MIN_LENGTH) {
        $errors[] = "Password minimal " . PASSWORD_MIN_LENGTH . " karakter";
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password harus mengandung minimal 1 huruf besar";
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password harus mengandung minimal 1 huruf kecil";
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password harus mengandung minimal 1 angka";
    }
    
    return empty($errors) ? true : $errors;
}

/**
 * Generate secure session ID
 */
function generateSecureSessionId() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
}

/**
 * Check session timeout
 */
function checkSessionTimeout() {
    if (isset($_SESSION['last_activity']) && 
        (time() - $_SESSION['last_activity']) > SESSION_TIMEOUT) {
        session_destroy();
        return true;
    }
    $_SESSION['last_activity'] = time();
    return false;
}

/**
 * Enhanced logging for security events
 */
function logSecurityEvent($event, $details = []) {
    $timestamp = date('Y-m-d H:i:s');
    $ip = getClientIP();
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    
    $logData = [
        'timestamp' => $timestamp,
        'ip' => $ip,
        'user_agent' => $userAgent,
        'event' => $event,
        'details' => $details
    ];
    
    $logMessage = json_encode($logData) . PHP_EOL;
    $logFile = BASE_PATH . '/logs/security.log';
    
    file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
}
?>
