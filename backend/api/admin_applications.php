<?php
/**
 * Admin Applications Management API
 * 
 * CRUD operations for managing applications.
 * All endpoints require admin authentication.
 * 
 * Endpoints:
 *   GET    /api/admin_applications.php          - List applications (with filters)
 *   GET    /api/admin_applications.php?id=X     - Get single application details
 *   PUT    /api/admin_applications.php?id=X     - Update application status/notes
 *   DELETE /api/admin_applications.php?id=X     - Delete an application
 * 
 * @author Amazon T-Level Hub Backend
 * @version 1.0.0
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';

setCorsHeaders();

// All endpoints require authentication
try {
    $db = Database::getConnection();
    Database::initializeTables();
} catch (PDOException $e) {
    errorResponse('Database connection failed.', 500);
}

$adminUser = requireAuth();

$method = $_SERVER['REQUEST_METHOD'];
$appId = $_GET['id'] ?? null;

switch ($method) {
    case 'GET':
        if ($appId) {
            getApplication($db, (int)$appId);
        } else {
            listApplications($db);
        }
        break;
    case 'PUT':
        if (!$appId) errorResponse('Application ID is required.', 400);
        updateApplication($db, (int)$appId, $adminUser);
        break;
    case 'DELETE':
        if (!$appId) errorResponse('Application ID is required.', 400);
        deleteApplication($db, (int)$appId, $adminUser);
        break;
    default:
        errorResponse('Method not allowed.', 405);
}

/**
 * List all applications with optional filters
 * 
 * @param PDO $db
 * @return void
 */
function listApplications(PDO $db): void {
    $status = $_GET['status'] ?? '';
    $search = $_GET['search'] ?? '';
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = min(100, max(1, (int)($_GET['limit'] ?? 20)));
    $offset = ($page - 1) * $limit;
    $sortBy = in_array($_GET['sort_by'] ?? '', ['submitted_at', 'last_name', 'status']) ? $_GET['sort_by'] : 'submitted_at';
    $sortOrder = strtoupper($_GET['sort_order'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';

    $where = [];
    $params = [];

    if ($status) {
        $where[] = "status = ?";
        $params[] = $status;
    }

    if ($search) {
        $where[] = "(first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR ref_number LIKE ? OR tlevel_course LIKE ?)";
        $searchTerm = "%{$search}%";
        $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    }

    $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    // Count total
    $countStmt = $db->prepare("SELECT COUNT(*) as total FROM applications {$whereClause}");
    $countStmt->execute($params);
    $total = $countStmt->fetch()['total'];

    // Fetch page
    $stmt = $db->prepare("
        SELECT id, ref_number, first_name, last_name, email, tlevel_course, 
               preferred_location, status, submitted_at
        FROM applications 
        {$whereClause}
        ORDER BY {$sortBy} {$sortOrder}
        LIMIT ? OFFSET ?
    ");
    
    $allParams = array_merge($params, [$limit, $offset]);
    $stmt->execute($allParams);
    $applications = $stmt->fetchAll();

    // Anonymize personal data for GDPR compliance if older than 2 years
    $cutoff = date('Y-m-d', strtotime('-' . APPLICATION_RETENTION_DAYS . ' days'));
    foreach ($applications as &$app) {
        if ($app['submitted_at'] < $cutoff) {
            $app['first_name'] = '[REDACTED]';
            $app['last_name'] = '[REDACTED]';
            $app['email'] = '[REDACTED]';
            $app['ref_number'] = '[REDACTED]';
        }
    }

    jsonResponse([
        'error' => false,
        'applications' => $applications,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => (int)$total,
            'total_pages' => (int)ceil($total / $limit)
        ]
    ]);
}

/**
 * Get single application details
 * 
 * @param PDO $db
 * @param int $id
 * @return void
 */
function getApplication(PDO $db, int $id): void {
    $stmt = $db->prepare("SELECT * FROM applications WHERE id = ?");
    $stmt->execute([$id]);
    $application = $stmt->fetch();

    if (!$application) {
        errorResponse('Application not found.', 404);
    }

    // Anonymize if older than retention period
    $cutoff = date('Y-m-d', strtotime('-' . APPLICATION_RETENTION_DAYS . ' days'));
    if ($application['submitted_at'] < $cutoff) {
        foreach (['first_name', 'last_name', 'email', 'phone', 'ref_number', 'school', 'motivation', 'skills', 'additional_needs'] as $field) {
            $application[$field] = '[REDACTED]';
        }
    }

    jsonResponse([
        'error' => false,
        'application' => $application
    ]);
}

/**
 * Update application status and notes
 * 
 * @param PDO $db
 * @param int $id
 * @param array $adminUser
 * @return void
 */
function updateApplication(PDO $db, int $id, array $adminUser): void {
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);

    if (!$data) {
        errorResponse('Invalid request body.', 400);
    }

    // Build update query dynamically
    $allowedFields = ['status', 'admin_notes'];
    $updates = [];
    $params = [];

    foreach ($allowedFields as $field) {
        if (isset($data[$field])) {
            $updates[] = "{$field} = ?";
            if ($field === 'status') {
                $validStatuses = ['received', 'reviewing', 'shortlisted', 'interviewed', 'offered', 'accepted', 'rejected', 'withdrawn'];
                if (!in_array($data[$field], $validStatuses)) {
                    errorResponse("Invalid status. Allowed: " . implode(', ', $validStatuses), 400);
                }
                $params[] = $data[$field];
                // Auto-set reviewed_at when status changes from 'received'
                if ($data[$field] !== 'received') {
                    $updates[] = "reviewed_at = datetime('now')";
                }
            } else {
                $params[] = sanitize($data[$field]);
            }
        }
    }

    if (empty($updates)) {
        errorResponse('No valid fields to update.', 400);
    }

    $params[] = $id;
    $stmt = $db->prepare("UPDATE applications SET " . implode(', ', $updates) . " WHERE id = ?");
    $stmt->execute($params);

    // Log the update
    $stmt = $db->prepare("
        INSERT INTO audit_log (user_id, action, details, ip_address) 
        VALUES (?, 'update_application', ?, ?)
    ");
    $stmt->execute([
        $adminUser['id'],
        "Updated application #{$id}: " . json_encode($data),
        getClientIP()
    ]);

    appLog("Admin {$adminUser['username']} updated application #{$id}");

    jsonResponse([
        'error' => false,
        'message' => 'Application updated successfully.'
    ]);
}

/**
 * Delete an application (soft-delete by setting status to 'withdrawn')
 * 
 * @param PDO $db
 * @param int $id
 * @param array $adminUser
 * @return void
 */
function deleteApplication(PDO $db, int $id, array $adminUser): void {
    // Check existence
    $stmt = $db->prepare("SELECT ref_number FROM applications WHERE id = ?");
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        errorResponse('Application not found.', 404);
    }

    // Instead of hard delete, set status to withdrawn for audit trail
    $stmt = $db->prepare("UPDATE applications SET status = 'withdrawn' WHERE id = ?");
    $stmt->execute([$id]);

    // Log deletion
    $stmt = $db->prepare("
        INSERT INTO audit_log (user_id, action, details, ip_address) 
        VALUES (?, 'delete_application', ?, ?)
    ");
    $stmt->execute([
        $adminUser['id'],
        "Deleted (withdrew) application #{$id}",
        getClientIP()
    ]);

    appLog("Admin {$adminUser['username']} deleted application #{$id}");

    jsonResponse([
        'error' => false,
        'message' => 'Application withdrawn successfully.'
    ]);
}