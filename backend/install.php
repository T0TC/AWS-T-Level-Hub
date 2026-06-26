<?php
/**
 * Installation Script
 * 
 * Run this script ONCE to set up the database and create the default admin user.
 * 
 * Usage: php install.php
 * Or visit this file in a browser (then delete it after installation).
 * 
 * IMPORTANT: Delete this file after installation for security!
 * 
 * @author Amazon T-Level Hub Backend
 * @version 1.0.0
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/app.php';

// Only allow CLI or localhost access
$isCLI = (php_sapi_name() === 'cli');
$isLocal = in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1', 'localhost']);

if (!$isCLI && !$isLocal) {
    die("Installation can only be run from CLI or localhost.\n");
}

echo "========================================\n";
echo "  Amazon T-Level Hub - Installation\n";
echo "========================================\n\n";

try {
    // Step 1: Initialize database and tables
    echo "[1/4] Creating database tables...\n";
    Database::initializeTables();
    echo "  ✓ Tables created successfully.\n\n";

    // Step 2: Set up default admin user
    echo "[2/4] Setting up default admin user...\n";
    $db = Database::getConnection();

    // Check if admin user already exists
    $stmt = $db->prepare("SELECT COUNT(*) as cnt FROM admin_users WHERE username = 'admin'");
    $stmt->execute();
    $adminExists = $stmt->fetch()['cnt'] > 0;

    if ($adminExists) {
        echo "  - Admin user 'admin' already exists. Skipping.\n";
    } else {
        // Generate a secure random password
        $defaultPassword = bin2hex(random_bytes(4)) . '!A' . rand(100, 999);
        
        $passwordHash = password_hash($defaultPassword, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);

        $stmt = $db->prepare("
            INSERT INTO admin_users (username, password_hash, display_name, role)
            VALUES ('admin', ?, 'System Administrator', 'superadmin')
        ");
        $stmt->execute([$passwordHash]);

        echo "  ✓ Admin user created.\n";
        echo "\n  ╔══════════════════════════════════════╗\n";
        echo "  ║       DEFAULT ADMIN CREDENTIALS       ║\n";
        echo "  ╠══════════════════════════════════════╣\n";
        echo "  ║  Username: admin                      ║\n";
        echo "  ║  Password: {$defaultPassword}         ║\n";
        echo "  ╚══════════════════════════════════════╝\n";
        echo "\n  IMPORTANT: Change this password after first login!\n\n";
    }

    // Step 3: Create data directory with proper permissions
    echo "[3/4] Setting up data directory...\n";
    $dataDir = __DIR__ . '/data';
    if (!is_dir($dataDir)) {
        mkdir($dataDir, 0750, true);
        echo "  ✓ Data directory created.\n";
    } else {
        echo "  - Data directory already exists.\n";
    }

    // Set permissions on database file
    $dbFile = $dataDir . '/tlevel_hub.sqlite';
    if (file_exists($dbFile)) {
        chmod($dbFile, 0640);
    }

    // Create .gitkeep in data dir
    file_put_contents($dataDir . '/.gitkeep', '');
    
    echo "\n";

    // Step 4: Test the connection
    echo "[4/4] Testing database connection...\n";
    $test = Database::getConnection();
    $stmt = $test->query("SELECT COUNT(*) as table_count FROM sqlite_master WHERE type='table'");
    $tableCount = $stmt->fetch()['table_count'];
    echo "  ✓ Database connected. {$tableCount} tables created.\n";

    echo "\n========================================\n";
    echo "  Installation Complete!\n";
    echo "========================================\n\n";
    echo "Next steps:\n";
    echo "  1. Delete this install.php file for security\n";
    echo "  2. Log in to the admin panel at /admin/\n";
    echo "  3. Change the default admin password\n";
    echo "  4. Review application settings in config/app.php\n\n";

    appLog("Installation completed successfully. {$tableCount} tables created.");

} catch (Exception $e) {
    echo "\n  ✗ ERROR: " . $e->getMessage() . "\n\n";
    echo "Installation failed. Please check the error and try again.\n";
    appLog("Installation FAILED: " . $e->getMessage(), 'ERROR');
    exit(1);
}