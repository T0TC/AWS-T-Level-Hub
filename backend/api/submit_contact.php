<?php
/**
 * Contact Form Submission API
 * 
 * Handles contact/enquiry form submissions from the website.
 * Stores enquiries in database for admin review.
 * 
 * Endpoint: POST /api/submit_contact.php
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

if (empty($data['subject']) || trim($data['subject']) === '') {
    $errors[] = 'Please provide a subject for your enquiry.';
}

if (empty($data['message']) || trim($data['message']) === '') {
    $errors[] = 'Please write a message.';
}

if (strlen($data['message'] ?? '') > 5000) {
    $errors[] = 'Message is too long. Maximum 5000 characters.';
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
$subject = sanitize($data['subject']);
$message = sanitize($data['message']);

try {
    $db = Database::getConnection();
    Database::initializeTables();

    $stmt = $db->prepare("
        INSERT INTO contact_enquiries (email, subject, message)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$email, $subject, $message]);

    appLog("New contact enquiry from: {$email} - {$subject}");

    jsonResponse([
        'error' => false,
        'message' => 'Thank you for your enquiry. We will respond within 5-7 working days.',
        'enquiry_id' => (int)$db->lastInsertId()
    ], 201);

} catch (PDOException $e) {
    appLog("Database error saving contact enquiry: " . $e->getMessage(), 'ERROR');
    errorResponse('An error occurred. Please try again later.', 500);
}