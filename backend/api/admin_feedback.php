<?php
/**
 * Admin Feedback Management API
 * 
 * Manages course/portal feedback submitted by users.
 * All endpoints require admin authentication.
 * 
 * Endpoints:
 *   GET    /api/admin_feedback.php          - List feedback (with filters)
 *   GET    /api/admin_feedback.php?id=X     - Get single feedback entry
 *   PUT    /api/admin_feedback.php?id=X     - Update feedback status
 *   DELETE /api/admin_feedback.php?id=X     - Delete a feedback entry
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
$feedbackId = $_GET['id'] ?? null;

switch ($method) {
    case 'GET':
        if ($feedbackId) {
            getFeedback($db, (int)$feedbackId);
        } else {
            listFeedback($db);
        }
        break;
    case 'PUT':
        if (!$feedbackId) errorResponse('Feedback ID is required.', 400);
        updateFeedback($db, (int)$feedbackId, $adminUser);
        break;
    case 'DELETE':
        if (!$feedbackId) errorResponse('Feedback ID is required.', 400);
        deleteFeedback($db, (int)$feedbackId, $adminUser);
        break;
    default:
        errorResponse('Method not allowed.', 405);
}

/**
 * List all feedback with optional filters
 */
function listFeedback(PDO $db): void {
    $status = $_GET['status'] ?? '';
    $rating = $_GET['rating'] ?? '';
    $search = $_GET['search'] ?? '';
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = min(100, max(1, (int)($_GET['limit'] ?? 20)));
    $offset = ($page - 1) * $limit;
    $sortBy = in_array($_GET['sort_by'] ?? '', ['submitted_at', 'rating', 'status']) ? $_GET['sort_by'] : 'submitted_at';
    $sortOrder = strtoupper($_GET['sort_order'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';

    $where = [];
    $params = [];

    if ($status) {
        $where[] = "status = ?";
        $params[] = $status;
    }

    if ($rating !== '') {
        $where[] = "rating = ?";
        $params[] = (int)$rating;
    }

    if ($search) {
        $where[] = "(email LIKE ? OR feedback_text LIKE ? OR course_name LIKE ?)";
        $searchTerm = "%{$search}%";
        $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
    }

    $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    // Count total
    $countStmt = $db->prepare("SELECT COUNT(*) as total FROM course_feedback {$whereClause}");
    $countStmt->execute($params);
    $total = $countStmt->fetch()['total'];

    // Average rating (over the filtered set)
    $avgStmt = $db->prepare("SELECT AVG(rating) as avg_rating FROM course_feedback {$whereClause}");
    $avgStmt->execute($params);
    $avgRating = $avgStmt->fetch()['avg_rating'];

    // Fetch page
    $stmt = $db->prepare("
        SELECT id, course_name, email, rating, feedback_text, status, submitted_at
        FROM course_feedback 
        {$whereClause}
        ORDER BY {$sortBy} {$sortOrder}
        LIMIT ? OFFSET ?
    ");

    $allParams = array_merge($params, [$limit, $offset]);
    $stmt->execute($allParams);
    $feedback = $stmt->fetchAll();

    jsonResponse([
        'error' => false,
        'feedback' => $feedback,
        'average_rating' => $avgRating !== null ? round((float)$avgRating, 2) : null,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => (int)$total,
            'total_pages' => (int)ceil($total / $limit)
        ]
    ]);
}

/**
 * Get single feedback entry
 */
function getFeedback(PDO $db, int $id): void {
    $stmt = $db->prepare("SELECT * FROM course_feedback WHERE id = ?");
    $stmt->execute([$id]);
    $feedback = $stmt->fetch();

    if (!$feedback) {
        errorResponse('Feedback not found.', 404);
    }

    jsonResponse([
        'error' => false,
        'feedback' => $feedback
    ]);
}

/**
 * Update feedback status (e.g. mark as reviewed/archived)
 */
function updateFeedback(PDO $db, int $id, array $adminUser): void {
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
                $validStatuses = ['new', 'reviewed', 'archived'];
                if (!in_array($data[$field], $validStatuses)) {
                    errorResponse("Invalid status. Allowed: " . implode(', ', $validStatuses), 400);
                }
                $params[] = $data[$field];
            }
        }
    }

    if (empty($updates)) {
        errorResponse('No valid fields to update.', 400);
    }

    $params[] = $id;
    $stmt = $db->prepare("UPDATE course_feedback SET " . implode(', ', $updates) . " WHERE id = ?");
    $stmt->execute($params);

    // Log the update
    $stmt = $db->prepare("
        INSERT INTO audit_log (user_id, action, details, ip_address) 
        VALUES (?, 'update_feedback', ?, ?)
    ");
    $stmt->execute([
        $adminUser['id'],
        "Updated feedback #{$id}: " . json_encode($data),
        getClientIP()
    ]);

    appLog("Admin {$adminUser['username']} updated feedback #{$id}");

    jsonResponse([
        'error' => false,
        'message' => 'Feedback updated successfully.'
    ]);
}

/**
 * Delete a feedback entry
 */
function deleteFeedback(PDO $db, int $id, array $adminUser): void {
    $stmt = $db->prepare("SELECT id FROM course_feedback WHERE id = ?");
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        errorResponse('Feedback not found.', 404);
    }

    $stmt = $db->prepare("DELETE FROM course_feedback WHERE id = ?");
    $stmt->execute([$id]);

    $stmt = $db->prepare("
        INSERT INTO audit_log (user_id, action, details, ip_address) 
        VALUES (?, 'delete_feedback', ?, ?)
    ");
    $stmt->execute([
        $adminUser['id'],
        "Deleted feedback #{$id}",
        getClientIP()
    ]);

    appLog("Admin {$adminUser['username']} deleted feedback #{$id}");

    jsonResponse([
        'error' => false,
        'message' => 'Feedback deleted successfully.'
    ]);
}
