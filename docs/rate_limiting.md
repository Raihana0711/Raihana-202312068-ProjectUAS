# 🛡️ Rate Limiting Implementation - Nun's Dimsum

> Dokumentasi implementasi sistem Rate Limiting untuk mencegah brute force attacks dan meningkatkan keamanan aplikasi.

## 📖 Daftar Isi

- [Overview](#-overview)
- [Cara Kerja](#-cara-kerja)
- [Fitur Utama](#-fitur-utama)
- [Konfigurasi](#-konfigurasi)
- [Monitoring & Management](#-monitoring--management)
- [Database Schema](#-database-schema)
- [Implementasi](#-implementasi)
- [Best Practices](#-best-practices)

## 🌟 Overview

Rate Limiting adalah sistem keamanan yang membatasi jumlah percobaan login yang gagal dari IP address atau username tertentu dalam periode waktu tertentu. Sistem ini secara otomatis memblokir IP/user yang melakukan percobaan login berlebihan untuk mencegah serangan brute force.

### 🎯 Tujuan

- **Mencegah Brute Force Attacks**: Melindungi dari percobaan login otomatis
- **Melindungi Sistem**: Mengurangi beban server dari request berlebihan
- **Meningkatkan Keamanan**: Layer tambahan untuk authentication system
- **Monitoring**: Tracking dan logging attempt yang mencurigakan

## ⚙️ Cara Kerja

### 1. **Tracking Attempts**

- Setiap percobaan login yang gagal dicatat ke database
- Tracking berdasarkan IP address dan username
- Timestamp dicatat untuk periode waktu

### 2. **Blocking Logic**

- Setelah 5 percobaan gagal dalam 15 menit → Blokir 30 menit
- Blocking bisa berdasarkan IP atau username
- Otomatis reset counter setelah login berhasil

### 3. **Recovery System**

- Block secara otomatis berakhir setelah periode tertentu
- Admin dapat manual unblock melalui admin panel
- Cleanup otomatis untuk data lama

## 🌟 Fitur Utama

### 🔒 **Protection Features**

- ✅ **IP-based Blocking**: Blokir IP address yang mencurigakan
- ✅ **Username-based Blocking**: Blokir username specific
- ✅ **Dual Protection**: Kombinasi IP dan username blocking
- ✅ **Configurable Thresholds**: Atur limits sesuai kebutuhan
- ✅ **Whitelist Support**: IP tertentu bisa bypass rate limiting

### 📊 **Monitoring Features**

- ✅ **Real-time Statistics**: Monitoring IP dan user yang diblokir
- ✅ **Attempt History**: Tracking percobaan login gagal
- ✅ **Admin Dashboard**: Interface untuk monitoring dan management
- ✅ **Security Logging**: Log semua aktivitas keamanan
- ✅ **Alerts & Notifications**: Peringatan untuk admin

### 🎛️ **Management Features**

- ✅ **Manual Unblock**: Admin bisa unblock IP/user
- ✅ **Configuration Management**: Atur parameter melalui config
- ✅ **Database Cleanup**: Otomatis cleanup data lama
- ✅ **Performance Optimization**: Index dan query optimization

## 🔧 Konfigurasi

### **Security Configuration** (`src/includes/security_config.php`)

```php
// Rate Limiting Settings
define('RATE_LIMIT_MAX_ATTEMPTS', 5);      // Max percobaan (default: 5)
define('RATE_LIMIT_TIME_WINDOW', 900);     // Time window dalam detik (15 menit)
define('RATE_LIMIT_BLOCK_DURATION', 1800); // Durasi blokir dalam detik (30 menit)

// Security Settings
define('PASSWORD_MIN_LENGTH', 8);           // Min panjang password
define('SESSION_TIMEOUT', 3600);           // Timeout session (1 jam)
define('LOGIN_ATTEMPT_LOG', true);         // Enable logging

// IP Whitelist (bypass rate limiting)
$SECURITY_IP_WHITELIST = [
    '127.0.0.1',        // Localhost
    '::1',              // IPv6 localhost
    // Tambahkan IP trusted lainnya
];
```

### **Custom Configuration**

```php
// Inisialisasi dengan custom settings
$rateLimiter = new RateLimiter(
    $pdo,               // PDO connection
    3,                  // Max attempts: 3
    600,                // Time window: 10 menit
    1200                // Block duration: 20 menit
);
```

## 📊 Monitoring & Management

### **Admin Panel** (`/admin/security`)

**📈 Statistics Dashboard**

- IP yang sedang diblokir
- Username yang sedang diblokir
- Percobaan login dalam 1 jam terakhir
- Konfigurasi sistem

**🔓 Management Tools**

- Manual unblock IP address
- Manual unblock username
- View attempt history
- Security event logs

**⚙️ Configuration Info**

- Maximum attempts setting
- Time window setting
- Block duration setting
- Session timeout setting

### **Real-time Monitoring**

```php
// Get statistics
$stats = $rateLimiter->getStatistics();
echo "Blocked IPs: " . $stats['blocked_ips'];
echo "Blocked Users: " . $stats['blocked_users'];
echo "Recent Attempts: " . $stats['recent_attempts'];

// Check if IP is blocked
$blockInfo = $rateLimiter->isBlocked($clientIP, $username);
if ($blockInfo['blocked']) {
    echo $rateLimiter->getBlockedMessage($blockInfo);
}
```

## 🗄️ Database Schema

### **login_attempts_ip** (IP-based tracking)

```sql
CREATE TABLE login_attempts_ip (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    attempts INT DEFAULT 1,
    first_attempt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_attempt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    blocked_until TIMESTAMP NULL,
    INDEX idx_ip (ip_address),
    INDEX idx_blocked (blocked_until)
);
```

### **login_attempts_user** (Username-based tracking)

```sql
CREATE TABLE login_attempts_user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    attempts INT DEFAULT 1,
    first_attempt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_attempt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    blocked_until TIMESTAMP NULL,
    INDEX idx_username (username),
    INDEX idx_blocked (blocked_until)
);
```

## 💻 Implementasi

### **1. Login Integration** (`src/views/auth/login.php`)

```php
// Initialize Rate Limiter
require_once __DIR__ . '/../../includes/rate_limiter.php';
require_once __DIR__ . '/../../includes/security_config.php';

$rateLimiter = initializeRateLimiter($pdo);
$clientIP = getClientIP();

// Check if blocked
$blockInfo = $rateLimiter->isBlocked($clientIP, $username);
if ($blockInfo['blocked']) {
    $error = $rateLimiter->getBlockedMessage($blockInfo);
    $isBlocked = true;
}

// On failed login
if (!$user) {
    $rateLimiter->recordFailedAttempt($clientIP, $username);
    $remainingAttempts = $rateLimiter->getRemainingAttempts($clientIP, $username);
}

// On successful login
if ($user) {
    $rateLimiter->recordSuccessfulLogin($clientIP, $username);
}
```

### **2. UI Features**

- **Warning Messages**: Tampilkan sisa percobaan
- **Blocked State**: Disable form ketika diblokir
- **Progress Indicators**: Visual feedback untuk user
- **Error Handling**: User-friendly error messages

### **3. Security Logging**

```php
// Enhanced logging
logSecurityEvent('failed_login_attempt', [
    'username' => $username,
    'ip' => $clientIP,
    'user_agent' => $_SERVER['HTTP_USER_AGENT'],
    'remaining_attempts' => $remainingAttempts
]);
```

## 🏆 Best Practices

### **🔒 Security**

- ✅ Gunakan prepared statements untuk semua database query
- ✅ Validate dan sanitize semua input
- ✅ Implement IP whitelisting untuk admin
- ✅ Monitor dan log semua security events
- ✅ Regular cleanup data lama

### **📊 Performance**

- ✅ Gunakan database indexing untuk query performance
- ✅ Implement connection pooling
- ✅ Cache statistics untuk mengurangi database load
- ✅ Batch cleanup operations
- ✅ Monitor database performance

### **👥 User Experience**

- ✅ Berikan feedback yang jelas tentang remaining attempts
- ✅ Tampilkan estimasi waktu unblock
- ✅ Provide alternative contact method ketika diblokir
- ✅ Implement progressive blocking (bertahap)

### **🛠️ Maintenance**

- ✅ Regular monitoring blocked IPs dan patterns
- ✅ Periodic review dan adjustment configuration
- ✅ Backup rate limiting data
- ✅ Monitor false positives
- ✅ Update whitelist secara berkala

## 🚀 Advanced Features

### **Geo-blocking Integration**

```php
// Implementasi geo-blocking berdasarkan negara
function isBlockedCountry($ip) {
    $country = getCountryFromIP($ip);
    $blockedCountries = ['CN', 'RU', 'KP']; // Contoh
    return in_array($country, $blockedCountries);
}
```

### **Machine Learning Integration**

```php
// Analisis pattern untuk deteksi anomali
function analyzeLoginPattern($ip, $attempts) {
    // Implement ML model untuk deteksi pattern
    // Return risk score 0-100
}
```

### **API Rate Limiting**

```php
// Extend untuk API endpoints
class APIRateLimiter extends RateLimiter {
    public function checkAPILimit($apiKey, $endpoint) {
        // Implement API-specific rate limiting
    }
}
```

## 📈 Metrics & Analytics

### **Key Metrics**

- **Attack Attempts**: Jumlah percobaan serangan per hari
- **Block Rate**: Persentase IP yang diblokir
- **False Positives**: User legitimate yang ter-blokir
- **Geographic Distribution**: Asal serangan berdasarkan lokasi
- **Time Patterns**: Pattern waktu serangan

### **Reporting**

- Daily security reports
- Weekly trend analysis
- Monthly security assessment
- Alert notifications untuk admin

---

## 🎉 Kesimpulan

Rate Limiting implementation di Nun's Dimsum menyediakan:

✨ **Comprehensive Protection**: Perlindungan berlapis untuk login system  
🎛️ **Easy Management**: Interface admin yang user-friendly  
📊 **Real-time Monitoring**: Dashboard untuk tracking security events  
⚙️ **Configurable**: Setting yang bisa disesuaikan kebutuhan  
🚀 **Performance Optimized**: Database design yang efficient

Sistem ini secara signifikan meningkatkan keamanan aplikasi dan memberikan tools yang diperlukan admin untuk monitoring dan management security threats.

**🔐 Stay Secure, Stay Protected!**
