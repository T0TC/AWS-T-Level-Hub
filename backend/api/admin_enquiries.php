<?php
/**
 * Admin Enquiries Management API
 * 
 * Manages contact enquiries from the website.
 * All endpoints require admin authentication.
 * 
 * Endpoints:
 *   GET    /api/admin_enquiries.php          - List enquiries (with filters)
 *   GET    /api/admin_enquiries.php?id=X     - Get single enquiry details
 *   PUT    /api/admin_enquiries.php?id=X     - Update enquiry status
 *   DELETE /api/admin_enquiries.php?id=X     - Delete an enquiry
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

$adminUser = requireAuth();

$method = $_SERVER['REQUEST_METHOD'];
$enquiryId = $_GET['id'] ?? null;

switch ($method) {
    case 'GET':
        if ($enquiryId) {
            getEnquiry($db, (int)$enquiryId);
        } else {
            listEnquiries($db);
        }
        break;
    case 'PUT':
        if (!$enquiryId) errorResponse('Enquiry ID is required.', 400);
        updateEnquiry($db, (int)$enquiryId, $adminUser);
        break;
    case 'DELETE':
        if (!$enquiryId) errorResponse('Enquiry ID is required.', 400);
        deleteEnquiry($db, (int)$enquiryId, $adminUser);
        break;
    default:
        errorResponse('Method not allowed.', 405);
}

/**
 * List all enquiries with optional filters
 */
function listEnquiries(PDO $db): void {
    $status = $_GET['status'] ?? '';
    $search = $_GET['search'] ?? '';
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = min(100, max(1, (int)($_GET['limit'] ?? 20)));
    $offset = ($page - 1) * $limit;
    $sortBy = in_array($_GET['sort_by'] ?? '', ['submitted_at', 'email', 'status', 'subject']) ? $_GET['sort_by'] : 'submitted_at';
    $sortOrder = strtoupper($_GET['sort_order'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';

    $where = [];
    $params = [];

    if ($status) {
        $where[] = "status = ?";
        $params[] = $status;
    }

    if ($search) {
        $where[] = "(email LIKE ? OR subject LIKE ? OR message LIKE ?)";
        $searchTerm = "%{$search}%";
        $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
    }

    $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    // Count total
    $countStmt = $db->prepare("SELECT COUNT(*) as total FROM contact_enquiries {$whereClause}");
    $countStmt->execute($params);
    $total = $countStmt->fetch()['total'];

    // Fetch page
    $stmt = $db->prepare("
        SELECT id, email, subject, message, status, submitted_at
        FROM contact_enquiries 
        {$whereClause}
        ORDER BY {$sortBy} {$sortOrder}
        LIMIT ? OFFSET ?
    ");
    
    $allParams = array_merge($params, [$limit, $offset]);
    $stmt->execute($allParams);
    $enquiries = $stmt->fetchAll();

    jsonResponse([
        'error' => false,
        'enquiries' => $enquiries,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => (int)$total,
            'total_pages' => (int)ceil($total / $limit)
        ]
    ]);
}

/**
 * Get single enquiry details
 */
function getEnquiry(PDO $db, int $id): void {
    $stmt = $db->prepare("SELECT * FROM contact_enquiries WHERE id = ?");
    $stmt->execute([$id]);
    $enquiry = $stmt->fetch();

    if (!$enquiry) {
        errorResponse('Enquiry not found.', 404);
    }

    jsonResponse([
        'error' => false,
        'enquiry' => $enquiry
    ]);
}

/**
 * Update enquiry status
 */
function updateEnquiry(PDO $db, int $id, array $adminUser): void {
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);

    if (!$data) {
        errorResponse('Invalid request body.', 400);
    }

    $allowedFields = ['status'];
    $updates = [];
    $params = [];

    foreach ($allowedFields as $field) {
        if (isset($data[$field])) {
            $updates[] = "{$field} = ?";
            if ($field === 'status') {
                $validStatuses = ['unread', 'read', 'replied', 'archived'];
                if (!in_array($data[$field], $validStatuses)) {
                    errorResponse("Invalid status. Allowed: " . implode(', ', $validStatuses), 400);
                }
                $params[] = $data[$field];
                if ($data[$field] === 'replied') {
                    $updates[] = "replied_at = datetime('now')";
                }
            }
        }
    }

    if (empty($updates)) {
        errorResponse('No valid fields to update.', 400);
    }

    $params[] = $id;
    $stmt = $db->prepare("UPDATE contact_enquiries SET " . implode(', ', $updates) . " WHERE id = ?");
    $stmt->execute($params);

    // Log the update
    $stmt = $db->prepare("
        INSERT INTO audit_log (user_id, action, details, ip_address) 
        VALUES (?, 'update_enquiry', ?, ?)
    ");
    $stmt->execute([
        $adminUser['id'],
        "Updated enquiry #{$id}: " . json_encode($data),
        getClientIP()
    ]);

    appLog("Admin {$adminUser['username']} updated enquiry #{$id}");

    jsonResponse([
        'error' => false,
        'message' => 'Enquiry updated successfully.'
    ]);
}

/**
 * Delete an enquiry
 */
function deleteEnquiry(PDO $db, int $id, array $adminUser): void {
    $stmt = $db->prepare("SELECT id FROM contact_enquiries WHERE id = ?");
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        errorResponse('Enquiry not found.', 404);
    }

    $stmt = $db->prepare("DELETE FROM contact_enquiries WHERE id = ?");
    $stmt->execute([$id]);

    $stmt = $db->prepare("
        INSERT INTO audit_log (user_id, action, details, ip_address) 
        VALUES (?, 'delete_enquiry', ?, ?)
    ");
    $stmt->execute([
        $adminUser['id'],
        "Deleted enquiry #{$id}",
        getClientIP()
    ]);

    appLog("Admin {$adminUser['username']} deleted enquiry #{$id}");

    jsonResponse([
        'error' => false,
        'message' => 'Enquiry deleted successfully.'
    ]);
}