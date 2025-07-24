<?php
/**
 * Script untuk migrasi password lama ke bcrypt hash
 * Hanya jalankan sekali untuk memigrasikan password yang belum di-hash
 */

require_once 'koneksi.php';
require_once 'functions.php';

// Prevent direct access from browser
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && !defined('MIGRATION_ALLOWED')) {
    die('Access denied. This script should only be run from command line or with proper authorization.');
}

function migratePasswords() {
    global $pdo;
    
    try {
        // Start transaction
        $pdo->beginTransaction();
        
        // Get all users with potentially unhashed passwords
        // Bcrypt hashes always start with $2y$ and are 60 characters long
        $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE LENGTH(password) != 60 OR password NOT LIKE '$2y$%'");
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $migrated = 0;
        $skipped = 0;
        
        foreach ($users as $user) {
            $userId = $user['id'];
            $username = $user['username'];
            $oldPassword = $user['password'];
            
            // Skip if already properly hashed
            if (strlen($oldPassword) == 60 && substr($oldPassword, 0, 4) === '$2y$') {
                $skipped++;
                continue;
            }
            
            // Check if this looks like a plain text password or weak hash
            // For security, we'll require admin confirmation for each user
            echo "User: {$username} has password: " . substr($oldPassword, 0, 10) . "...\n";
            echo "This appears to be an unhashed password. Type 'yes' to hash it, 'skip' to skip: ";
            
            if (php_sapi_name() === 'cli') {
                $handle = fopen("php://stdin", "r");
                $confirm = trim(fgets($handle));
                fclose($handle);
            } else {
                // For web interface (if needed)
                $confirm = $_POST["confirm_user_{$userId}"] ?? 'skip';
            }
            
            if (strtolower($confirm) === 'yes') {
                // Hash the password
                $hashedPassword = password_hash($oldPassword, PASSWORD_DEFAULT);
                
                // Update the database
                $updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $updateStmt->execute([$hashedPassword, $userId]);
                
                $migrated++;
                echo "✓ Password hashed for user: {$username}\n";
            } else {
                $skipped++;
                echo "- Skipped user: {$username}\n";
            }
        }
        
        // Commit transaction
        $pdo->commit();
        
        echo "\n=== Migration Summary ===\n";
        echo "Passwords migrated: {$migrated}\n";
        echo "Users skipped: {$skipped}\n";
        echo "Total users processed: " . count($users) . "\n";
        
        return ['migrated' => $migrated, 'skipped' => $skipped, 'total' => count($users)];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Error during migration: " . $e->getMessage() . "\n";
        return false;
    }
}

function validateAllPasswords() {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT id, username, password FROM users");
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $valid = 0;
        $invalid = 0;
        
        foreach ($users as $user) {
            $password = $user['password'];
            
            // Check if password is properly hashed with bcrypt
            if (strlen($password) == 60 && substr($password, 0, 4) === '$2y$') {
                $valid++;
                echo "✓ User {$user['username']}: Password properly hashed\n";
            } else {
                $invalid++;
                echo "✗ User {$user['username']}: Password NOT properly hashed\n";
            }
        }
        
        echo "\n=== Validation Summary ===\n";
        echo "Valid passwords: {$valid}\n";
        echo "Invalid passwords: {$invalid}\n";
        echo "Total users: " . count($users) . "\n";
        
        return $invalid === 0;
        
    } catch (Exception $e) {
        echo "Error during validation: " . $e->getMessage() . "\n";
        return false;
    }
}

// Command line interface
if (php_sapi_name() === 'cli') {
    echo "=== Password Migration Tool ===\n\n";
    
    if ($argc < 2) {
        echo "Usage: php migrate_passwords.php [migrate|validate]\n";
        echo "  migrate  - Migrate unhashed passwords to bcrypt\n";
        echo "  validate - Check all passwords are properly hashed\n";
        exit(1);
    }
    
    $action = $argv[1];
    
    switch ($action) {
        case 'migrate':
            echo "Starting password migration...\n\n";
            migratePasswords();
            break;
            
        case 'validate':
            echo "Validating all passwords...\n\n";
            validateAllPasswords();
            break;
            
        default:
            echo "Invalid action: {$action}\n";
            echo "Use 'migrate' or 'validate'\n";
            exit(1);
    }
}

// Web interface (if needed)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && defined('MIGRATION_ALLOWED')) {
    $action = $_POST['action'];
    
    if ($action === 'validate') {
        echo "<pre>";
        $result = validateAllPasswords();
        echo "</pre>";
        
        if ($result) {
            echo "<div style='color: green; font-weight: bold;'>✓ All passwords are properly hashed!</div>";
        } else {
            echo "<div style='color: red; font-weight: bold;'>✗ Some passwords need migration!</div>";
        }
    }
}
?>
