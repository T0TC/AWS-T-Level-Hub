<?php
/**
 * Feedback Submission API
 * 
 * Handles feedback form submissions from the website.
 * Stores feedback in database for admin review.
 * 
 * Endpoint: POST /api/submit_feedback.php
 * 
 * @author Amazon T-Level Hub Backend
 * @version 1.0.0
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';

setCorsHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    errorResponse('Method not allowed. Use POST.', 405);
}

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

if (!$data) {
    errorResponse('Invalid JSON body.', 400);
}

// Validation
$errors = [];

if (empty($data['email']) || !isValidEmail($data['email'])) {
    $errors[] = 'Please provide a valid email address.';
}

if (empty($data['rating'])) {
    $errors[] = 'Please provide a rating.';
}

if (empty($data['feedback']) || trim($data['feedback']) === '') {
    $errors[] = 'Please write your feedback.';
}

if (!empty($errors)) {
    jsonResponse([
        'error' => true,
        'message' => 'Validation failed.',
        'validation_errors' => $errors
    ], 422);
}

// Sanitize
$email   = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
$rating  = null;
if (isset($data['rating'])) {
    // Extract numeric rating from strings like "⭐⭐⭐ Good" or just a number
    $ratingText = trim($data['rating']);
    $starCount = substr_count($ratingText, '⭐');
    $rating = $starCount > 0 ? $starCount : (int)$ratingText;
    $rating = max(1, min(5, $rating));
}
$feedback = sanitize($data['feedback']);

try {
    $db = Database::getConnection();
    Database::initializeTables();

    $stmt = $db->prepare("
        INSERT INTO course_feedback (course_name, email, rating, feedback_text)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute(['General Portal Feedback', $email, $rating, $feedback]);

    appLog("New feedback received from: {$email} - rating {$rating}/5");

    jsonResponse([
        'error' => false,
        'message' => 'Thank you for your feedback! Your input helps us improve the portal.',
        'feedback_id' => (int)$db->lastInsertId()
    ], 201);

} catch (PDOException $e) {
    appLog("Database error saving feedback: " . $e->getMessage(), 'ERROR');
    errorResponse('An error occurred. Please try again later.', 500);
}