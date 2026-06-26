<?php
/**
 * Admin Authentication API
 * 
 * Handles admin login, logout, and session verification.
 * Uses token-based authentication with expiry.
 * 
 * Endpoints:
 *   POST /api/auth.php?action=login  - Authenticate admin user
 *   POST /api/auth.php?action=logout - Invalidate session token
 *   GET  /api/auth.php?action=verify - Check if token is valid
 * 
 * @author Amazon T-Level Hub Backend
 * @version 1.0.0
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';

setCorsHeaders();

try {
    $db = Database::getConnection();
    Database::initializeTables();
} catch (PDOException $e) {
    errorResponse('Database connection failed.', 500);
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'login':
        handleLogin($db);
        break;
    case 'logout':
        handleLogout($db);
        break;
    case 'verify':
        handleVerify($db);
        break;
    default:
        errorResponse('Unknown action. Use ?action=login|logout|verify', 400);
}

/**
 * Handle admin login
 * 
 * @param PDO $db
 * @return void
 */
function handleLogin(PDO $db): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        errorResponse('Method not allowed. Use POST.', 405);
    }

    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);

    if (!$data || empty($data['username']) || empty($data['password'])) {
        errorResponse('Username and password are required.', 400);
    }

    $username = sanitize($data['username']);
    $password = $data['password']; // Don't sanitize passwords (may contain special chars)

    try {
        // Check for existing failed attempts
        $ip = getClientIP();
        $lockoutTime = date('Y-m-d H:i:s', strtotime('-' . LOGIN_LOCKOUT_MINUTES . ' minutes'));
        
        $stmt = $db->prepare("
            SELECT COUNT(*) as attempt_count FROM audit_log 
            WHERE action = 'login_failed' 
            AND ip_address = ? 
            AND created_at > ?
        ");
        $stmt->execute([$ip, $lockoutTime]);
        $attempts = $stmt->fetch()['attempt_count'];

        if ($attempts >= MAX_LOGIN_ATTEMPTS) {
            appLog("Login lockout for IP: {$ip} - too many attempts", 'WARN');
            errorResponse(
                'Too many login attempts. Please try again in ' . LOGIN_LOCKOUT_MINUTES . ' minutes.',
                429
            );
        }

        // Find user
        $stmt = $db->prepare("SELECT * FROM admin_users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            // Log failed attempt
            $stmt = $db->prepare("
                INSERT INTO audit_log (user_id, action, details, ip_address) 
                VALUES (NULL, 'login_failed', ?, ?)
            ");
            $stmt->execute(["Failed login for username: {$username}", $ip]);
            
            appLog("Failed login attempt for {$username} from IP: {$ip}", 'WARN');
            errorResponse('Invalid username or password.', 401);
        }

        // Generate auth token
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+' . TOKEN_EXPIRY_HOURS . ' hours'));

        $stmt = $db->prepare("
            INSERT INTO auth_tokens (user_id, token, expires_at)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$user['id'], $token, $expiresAt]);

        // Update last login
        $stmt = $db->prepare("UPDATE admin_users SET last_login = datetime('now') WHERE id = ?");
        $stmt->execute([$user['id']]);

        // Log successful login
        $stmt = $db->prepare("
            INSERT INTO audit_log (user_id, action, details, ip_address) 
            VALUES (?, 'login_success', ?, ?)
        ");
        $stmt->execute([$user['id'], "Successful login for {$username}", $ip]);

        appLog("Admin login: {$username}");

        jsonResponse([
            'error' => false,
            'message' => 'Login successful.',
            'token' => $token,
            'expires_at' => $expiresAt,
            'user' => [
                'id' => (int)$user['id'],
                'username' => $user['username'],
                'display_name' => $user['display_name'],
                'role' => $user['role']
            ]
        ]);

    } catch (PDOException $e) {
        appLog("Login error: " . $e->getMessage(), 'ERROR');
        errorResponse('Authentication service unavailable.', 500);
    }
}

/**
 * Handle admin logout
 * 
 * @param PDO $db
 * @return void
 */
function handleLogout(PDO $db): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        errorResponse('Method not allowed. Use POST.', 405);
    }

    try {
        $user = requireAuth();
        
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        preg_match('/^Bearer\s+(.+)$/i', $authHeader, $matches);
        $token = $matches[1] ?? '';

        // Delete the token
        $stmt = $db->prepare("DELETE FROM auth_tokens WHERE token = ?");
        $stmt->execute([$token]);

        // Log logout
        $stmt = $db->prepare("
            INSERT INTO audit_log (user_id, action, details, ip_address) 
            VALUES (?, 'logout', ?, ?)
        ");
        $stmt->execute([
            $user['id'],
            "User {$user['username']} logged out",
            getClientIP()
        ]);

        appLog("Admin logout: {$user['username']}");

        jsonResponse([
            'error' => false,
            'message' => 'Logged out successfully.'
        ]);

    } catch (PDOException $e) {
        appLog("Logout error: " . $e->getMessage(), 'ERROR');
        errorResponse('Logout service unavailable.', 500);
    }
}

/**
 * Verify authentication token validity
 * 
 * @param PDO $db
 * @return void
 */
function handleVerify(PDO $db): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        errorResponse('Method not allowed. Use GET.', 405);
    }

    try {
        $user = requireAuth();
        
        jsonResponse([
            'error' => false,
            'valid' => true,
            'user' => $user
        ]);

    } catch (PDOException $e) {
        appLog("Token verification error: " . $e->getMessage(), 'ERROR');
        errorResponse('Verification service unavailable.', 500);
    }
}