<?php
/**
 * Application Configuration
 * 
 * Central configuration for the T-Level Hub backend.
 * Contains CORS settings, rate limiting limits, email config, and app constants.
 * 
 * @author Amazon T-Level Hub Backend
 * @version 1.0.0
 */

// Application settings
define('APP_NAME', 'Amazon T-Level Hub');
define('APP_VERSION', '1.0.0');
define('APP_EMAIL', 'tlevel@amazon.co.uk');
define('SUPPORT_PHONE', '0800 000 1234');

// Security settings
define('TOKEN_EXPIRY_HOURS', 24);
define('BCRYPT_COST', 12);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_MINUTES', 15);
define('RATE_LIMIT_PER_MINUTE', 60);
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB

// Allowed origins for CORS
define('CORS_ORIGINS', '*'); // In production, restrict to actual domain

// Data retention
define('AUDIT_LOG_RETENTION_DAYS', 365);
define('APPLICATION_RETENTION_DAYS', 730); // 2 years (GDPR compliance)

// Reference number prefix
define('REF_PREFIX', 'TLH-');

/**
 * Set CORS headers for API responses
 * 
 * @return void
 */
function setCorsHeaders(): void {
    header('Access-Control-Allow-Origin: ' . CORS_ORIGINS);
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    header('Access-Control-Max-Age: 86400');
    header('Content-Type: application/json; charset=UTF-8');

    // Handle preflight OPTIONS request
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit();
    }
}

/**
 * Send JSON response
 * 
 * @param mixed $data Response data
 * @param int $statusCode HTTP status code
 * @return void
 */
function jsonResponse(mixed $data, int $statusCode = 200): void {
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit();
}

/**
 * Send error JSON response
 * 
 * @param string $message Error message
 * @param int $statusCode HTTP status code
 * @return void
 */
function errorResponse(string $message, int $statusCode = 400): void {
    jsonResponse(['error' => true, 'message' => $message], $statusCode);
}

/**
 * Get client IP address
 * 
 * @return string
 */
function getClientIP(): string {
    $headers = [
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_REAL_IP',
        'HTTP_CLIENT_IP',
        'REMOTE_ADDR'
    ];
    
    foreach ($headers as $header) {
        if (!empty($_SERVER[$header])) {
            $ip = explode(',', $_SERVER[$header])[0];
            return trim($ip);
        }
    }
    
    return '0.0.0.0';
}

/**
 * Validate email format
 * 
 * @param string $email
 * @return bool
 */
function isValidEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Sanitize input string
 * 
 * @param string $input
 * @return string
 */
function sanitize(string $input): string {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Generate a cryptographically secure reference number
 * 
 * @return string
 */
function generateReferenceNumber(): string {
    $bytes = random_bytes(4);
    $hex = strtoupper(bin2hex($bytes));
    return REF_PREFIX . $hex;
}

/**
 * Validate date string
 * 
 * @param string $date Date string
 * @param string $format Expected format
 * @return bool
 */
function isValidDate(string $date, string $format = 'Y-m-d'): bool {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

/**
 * Log application event to error log
 * 
 * @param string $message Log message
 * @param string $level Log level
 * @return void
 */
function appLog(string $message, string $level = 'INFO'): void {
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;
    error_log($logEntry, 3, __DIR__ . '/../data/app.log');
}

/**
 * Get all T-Level courses with their details
 * 
 * @return array
 */
function getCourses(): array {
    return [
        [
            'id' => 'digital-prod',
            'name' => 'Digital Production, Design and Development',
            'sector' => 'Digital',
            'salary_range' => '£30,000 - £60,000'
        ],
        [
            'id' => 'digital-support',
            'name' => 'Digital Support Services',
            'sector' => 'Digital',
            'salary_range' => '£35,000 - £70,000'
        ],
        [
            'id' => 'digital-business',
            'name' => 'Digital Business Services',
            'sector' => 'Digital',
            'salary_range' => '£35,000 - £65,000'
        ],
        [
            'id' => 'education',
            'name' => 'Education and Childcare',
            'sector' => 'Education',
            'salary_range' => '£20,000 - £30,000'
        ],
        [
            'id' => 'health',
            'name' => 'Health',
            'sector' => 'Health & Science',
            'salary_range' => '£22,000 - £35,000'
        ],
        [
            'id' => 'healthcare-science',
            'name' => 'Healthcare Science',
            'sector' => 'Health & Science',
            'salary_range' => '£25,000 - £40,000'
        ],
        [
            'id' => 'building-services',
            'name' => 'Building Services Engineering',
            'sector' => 'Construction',
            'salary_range' => '£28,000 - £50,000'
        ],
        [
            'id' => 'design-surveying',
            'name' => 'Design, Surveying and Planning',
            'sector' => 'Construction',
            'salary_range' => '£28,000 - £50,000'
        ],
        [
            'id' => 'accounting',
            'name' => 'Accounting',
            'sector' => 'Legal & Finance',
            'salary_range' => '£25,000 - £55,000'
        ],
        [
            'id' => 'finance',
            'name' => 'Finance',
            'sector' => 'Legal & Finance',
            'salary_range' => '£28,000 - £60,000'
        ]
    ];
}

/**
 * Get placement locations
 * 
 * @return array
 */
function getPlacementLocations(): array {
    return [
        'London',
        'Manchester',
        'Birmingham',
        'Leeds',
        'Edinburgh',
        'Bristol',
        'No preference'
    ];
}

/**
 * Check if request requires authentication and validate token
 * 
 * @return array User data array or sends 401 response
 */
function requireAuth(): array {
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    
    if (!preg_match('/^Bearer\s+(.+)$/i', $authHeader, $matches)) {
        errorResponse('Authentication required. Please provide a valid Bearer token.', 401);
    }
    
    $token = $matches[1];
    
    try {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT au.id, au.username, au.display_name, au.role 
            FROM auth_tokens at
            JOIN admin_users au ON au.id = at.user_id
            WHERE at.token = ? AND at.expires_at > datetime('now')
        ");
        $stmt->execute([$token]);
        $user = $stmt->fetch();
        
        if (!$user) {
            errorResponse('Invalid or expired session token.', 401);
        }
        
        return $user;
    } catch (PDOException $e) {
        errorResponse('Authentication service unavailable.', 500);
    }
    
    // Unreachable but required for static analysis
    errorResponse('Authentication failed.', 401);
    return [];
}