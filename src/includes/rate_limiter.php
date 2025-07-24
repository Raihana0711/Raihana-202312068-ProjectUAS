<?php
/**
 * Rate Limiter untuk mencegah brute force attacks
 * Membatasi jumlah percobaan login per IP dan per user
 */

class RateLimiter {
    private $pdo;
    private $maxAttempts;
    private $timeWindow;
    private $blockDuration;
    
    public function __construct($pdo, $maxAttempts = 5, $timeWindow = 900, $blockDuration = 1800) {
        $this->pdo = $pdo;
        $this->maxAttempts = $maxAttempts; // Max attempts (default: 5)
        $this->timeWindow = $timeWindow;   // Time window in seconds (default: 15 minutes)
        $this->blockDuration = $blockDuration; // Block duration in seconds (default: 30 minutes)
        
        $this->createTablesIfNotExist();
    }
    
    /**
     * Create rate limiting tables if they don't exist
     */
    private function createTablesIfNotExist() {
        try {
            // Table for login attempts by IP
            $sql1 = "CREATE TABLE IF NOT EXISTS login_attempts_ip (
                id INT AUTO_INCREMENT PRIMARY KEY,
                ip_address VARCHAR(45) NOT NULL,
                attempts INT DEFAULT 1,
                first_attempt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                last_attempt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                blocked_until TIMESTAMP NULL,
                INDEX idx_ip (ip_address),
                INDEX idx_blocked (blocked_until)
            )";
            
            // Table for login attempts by username
            $sql2 = "CREATE TABLE IF NOT EXISTS login_attempts_user (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(100) NOT NULL,
                attempts INT DEFAULT 1,
                first_attempt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                last_attempt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                blocked_until TIMESTAMP NULL,
                INDEX idx_username (username),
                INDEX idx_blocked (blocked_until)
            )";
            
            $this->pdo->exec($sql1);
            $this->pdo->exec($sql2);
            
        } catch (PDOException $e) {
            error_log("Failed to create rate limiting tables: " . $e->getMessage());
        }
    }
    
    /**
     * Check if IP or username is currently blocked
     */
    public function isBlocked($ip, $username = null) {
        $currentTime = date('Y-m-d H:i:s');
        
        // Check IP blocking
        $stmt = $this->pdo->prepare("
            SELECT blocked_until 
            FROM login_attempts_ip 
            WHERE ip_address = ? AND blocked_until > ?
        ");
        $stmt->execute([$ip, $currentTime]);
        
        if ($stmt->rowCount() > 0) {
            return [
                'blocked' => true,
                'type' => 'ip',
                'blocked_until' => $stmt->fetchColumn()
            ];
        }
        
        // Check username blocking if provided
        if ($username) {
            $stmt = $this->pdo->prepare("
                SELECT blocked_until 
                FROM login_attempts_user 
                WHERE username = ? AND blocked_until > ?
            ");
            $stmt->execute([$username, $currentTime]);
            
            if ($stmt->rowCount() > 0) {
                return [
                    'blocked' => true,
                    'type' => 'user',
                    'blocked_until' => $stmt->fetchColumn()
                ];
            }
        }
        
        return ['blocked' => false];
    }
    
    /**
     * Record failed login attempt
     */
    public function recordFailedAttempt($ip, $username = null) {
        $this->cleanupOldAttempts();
        
        // Record IP attempt
        $this->recordIpAttempt($ip);
        
        // Record username attempt if provided
        if ($username) {
            $this->recordUserAttempt($username);
        }
    }
    
    /**
     * Record successful login (reset counters)
     */
    public function recordSuccessfulLogin($ip, $username) {
        // Clear IP attempts
        $stmt = $this->pdo->prepare("DELETE FROM login_attempts_ip WHERE ip_address = ?");
        $stmt->execute([$ip]);
        
        // Clear username attempts
        $stmt = $this->pdo->prepare("DELETE FROM login_attempts_user WHERE username = ?");
        $stmt->execute([$username]);
    }
    
    /**
     * Record IP-based attempt
     */
    private function recordIpAttempt($ip) {
        $cutoffTime = date('Y-m-d H:i:s', time() - $this->timeWindow);
        
        // Check existing attempts within time window
        $stmt = $this->pdo->prepare("
            SELECT id, attempts 
            FROM login_attempts_ip 
            WHERE ip_address = ? AND last_attempt > ?
        ");
        $stmt->execute([$ip, $cutoffTime]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing) {
            // Update existing record
            $newAttempts = $existing['attempts'] + 1;
            $stmt = $this->pdo->prepare("
                UPDATE login_attempts_ip 
                SET attempts = ?, last_attempt = CURRENT_TIMESTAMP, blocked_until = ?
                WHERE id = ?
            ");
            
            $blockedUntil = null;
            if ($newAttempts >= $this->maxAttempts) {
                $blockedUntil = date('Y-m-d H:i:s', time() + $this->blockDuration);
            }
            
            $stmt->execute([$newAttempts, $blockedUntil, $existing['id']]);
        } else {
            // Create new record
            $stmt = $this->pdo->prepare("
                INSERT INTO login_attempts_ip (ip_address, attempts) 
                VALUES (?, 1)
            ");
            $stmt->execute([$ip]);
        }
    }
    
    /**
     * Record username-based attempt
     */
    private function recordUserAttempt($username) {
        $cutoffTime = date('Y-m-d H:i:s', time() - $this->timeWindow);
        
        // Check existing attempts within time window
        $stmt = $this->pdo->prepare("
            SELECT id, attempts 
            FROM login_attempts_user 
            WHERE username = ? AND last_attempt > ?
        ");
        $stmt->execute([$username, $cutoffTime]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing) {
            // Update existing record
            $newAttempts = $existing['attempts'] + 1;
            $stmt = $this->pdo->prepare("
                UPDATE login_attempts_user 
                SET attempts = ?, last_attempt = CURRENT_TIMESTAMP, blocked_until = ?
                WHERE id = ?
            ");
            
            $blockedUntil = null;
            if ($newAttempts >= $this->maxAttempts) {
                $blockedUntil = date('Y-m-d H:i:s', time() + $this->blockDuration);
            }
            
            $stmt->execute([$newAttempts, $blockedUntil, $existing['id']]);
        } else {
            // Create new record
            $stmt = $this->pdo->prepare("
                INSERT INTO login_attempts_user (username, attempts) 
                VALUES (?, 1)
            ");
            $stmt->execute([$username]);
        }
    }
    
    /**
     * Clean up old attempt records
     */
    private function cleanupOldAttempts() {
        $cutoffTime = date('Y-m-d H:i:s', time() - ($this->timeWindow * 2));
        
        $stmt1 = $this->pdo->prepare("
            DELETE FROM login_attempts_ip 
            WHERE last_attempt < ? AND (blocked_until IS NULL OR blocked_until < NOW())
        ");
        $stmt1->execute([$cutoffTime]);
        
        $stmt2 = $this->pdo->prepare("
            DELETE FROM login_attempts_user 
            WHERE last_attempt < ? AND (blocked_until IS NULL OR blocked_until < NOW())
        ");
        $stmt2->execute([$cutoffTime]);
    }
    
    /**
     * Get remaining attempts for IP or username
     */
    public function getRemainingAttempts($ip, $username = null) {
        $cutoffTime = date('Y-m-d H:i:s', time() - $this->timeWindow);
        
        // Check IP attempts
        $stmt = $this->pdo->prepare("
            SELECT attempts 
            FROM login_attempts_ip 
            WHERE ip_address = ? AND last_attempt > ?
        ");
        $stmt->execute([$ip, $cutoffTime]);
        $ipAttempts = $stmt->fetchColumn() ?: 0;
        
        $userAttempts = 0;
        if ($username) {
            // Check username attempts
            $stmt = $this->pdo->prepare("
                SELECT attempts 
                FROM login_attempts_user 
                WHERE username = ? AND last_attempt > ?
            ");
            $stmt->execute([$username, $cutoffTime]);
            $userAttempts = $stmt->fetchColumn() ?: 0;
        }
        
        $maxUsedAttempts = max($ipAttempts, $userAttempts);
        return max(0, $this->maxAttempts - $maxUsedAttempts);
    }
    
    /**
     * Get user-friendly error message
     */
    public function getBlockedMessage($blockInfo) {
        $blockedUntil = new DateTime($blockInfo['blocked_until']);
        $now = new DateTime();
        $diff = $now->diff($blockedUntil);
        
        $timeLeft = '';
        if ($diff->h > 0) {
            $timeLeft = $diff->h . ' jam ' . $diff->i . ' menit';
        } else {
            $timeLeft = $diff->i . ' menit';
        }
        
        $type = $blockInfo['type'] === 'ip' ? 'IP address' : 'username';
        
        return "Terlalu banyak percobaan login yang gagal. {$type} Anda diblokir selama {$timeLeft}.";
    }
    
    /**
     * Manually unblock IP or username (admin function)
     */
    public function unblock($ip = null, $username = null) {
        if ($ip) {
            $stmt = $this->pdo->prepare("
                UPDATE login_attempts_ip 
                SET blocked_until = NULL, attempts = 0 
                WHERE ip_address = ?
            ");
            $stmt->execute([$ip]);
        }
        
        if ($username) {
            $stmt = $this->pdo->prepare("
                UPDATE login_attempts_user 
                SET blocked_until = NULL, attempts = 0 
                WHERE username = ?
            ");
            $stmt->execute([$username]);
        }
    }
    
    /**
     * Get statistics about blocked attempts
     */
    public function getStatistics() {
        $currentTime = date('Y-m-d H:i:s');
        
        // Active blocks
        $stmt = $this->pdo->query("
            SELECT COUNT(*) as blocked_ips 
            FROM login_attempts_ip 
            WHERE blocked_until > '{$currentTime}'
        ");
        $blockedIps = $stmt->fetchColumn();
        
        $stmt = $this->pdo->query("
            SELECT COUNT(*) as blocked_users 
            FROM login_attempts_user 
            WHERE blocked_until > '{$currentTime}'
        ");
        $blockedUsers = $stmt->fetchColumn();
        
        // Recent attempts (last hour)
        $lastHour = date('Y-m-d H:i:s', time() - 3600);
        $stmt = $this->pdo->query("
            SELECT COUNT(*) as recent_attempts 
            FROM login_attempts_ip 
            WHERE last_attempt > '{$lastHour}'
        ");
        $recentAttempts = $stmt->fetchColumn();
        
        return [
            'blocked_ips' => $blockedIps,
            'blocked_users' => $blockedUsers,
            'recent_attempts' => $recentAttempts
        ];
    }
}

/**
 * Helper function to get client IP address
 */
function getClientIP() {
    $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
    
    foreach ($ipKeys as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = $_SERVER[$key];
            
            // Handle comma-separated IPs (from proxies)
            if (strpos($ip, ',') !== false) {
                $ip = trim(explode(',', $ip)[0]);
            }
            
            // Validate IP
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
    }
    
    // Fallback to REMOTE_ADDR even if it's private/reserved
    return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
}
?>
