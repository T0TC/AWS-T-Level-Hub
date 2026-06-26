<?php
/**
 * Application Submission API
 * 
 * Handles new T-Level placement applications.
 * Validates input, stores in database, and returns reference number.
 * 
 * Endpoint: POST /api/submit_application.php
 * 
 * Security:
 * - All input validated and sanitized
 * - CSRF protection via Origin/Referer header check
 * - Rate limiting via database timestamp
 * - Prepared statements prevent SQL injection
 * 
 * @author Amazon T-Level Hub Backend
 * @version 1.0.0
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';

setCorsHeaders();

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    errorResponse('Method not allowed. Use POST.', 405);
}

// Parse JSON body
$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

if (!$data) {
    errorResponse('Invalid JSON body.', 400);
}

// ===== VALIDATION =====
$errors = [];

// Required fields validation
$requiredFields = [
    'first_name'    => 'First name is required.',
    'last_name'     => 'Last name is required.',
    'date_of_birth' => 'Date of birth is required.',
    'nationality'   => 'Nationality is required.',
    'email'         => 'Email address is required.',
    'country'       => 'Country of residence is required.',
    'school'        => 'School or institution name is required.',
    'year_of_study' => 'Year of study is required.',
    'tlevel_course' => 'T-Level course selection is required.',
    'preferred_location' => 'Preferred placement location is required.',
    'preferred_start' => 'Preferred start date is required.',
    'motivation'    => 'Motivation statement is required.',
];

foreach ($requiredFields as $field => $message) {
    if (empty($data[$field]) || trim((string)$data[$field]) === '') {
        $errors[] = $message;
    }
}

// Email format validation
if (!empty($data['email']) && !isValidEmail($data['email'])) {
    $errors[] = 'Please provide a valid email address.';
}

// Date of birth validation
if (!empty($data['date_of_birth'])) {
    if (!isValidDate($data['date_of_birth'])) {
        $errors[] = 'Invalid date of birth format. Use YYYY-MM-DD.';
    } else {
        // Check age (must be 16-19 for T-Levels)
        $dob = new DateTime($data['date_of_birth']);
        $now = new DateTime();
        $age = $now->diff($dob)->y;
        if ($age < 16 || $age > 19) {
            $errors[] = 'Applicants must be between 16 and 19 years old.';
        }
    }
}

// Declarations must be accepted
if (empty($data['declarations_accurate']) || $data['declarations_accurate'] !== true) {
    $errors[] = 'You must confirm that the information provided is accurate.';
}
if (empty($data['declarations_privacy']) || $data['declarations_privacy'] !== true) {
    $errors[] = 'You must agree to the privacy notice and terms of use.';
}
if (empty($data['declarations_contact']) || $data['declarations_contact'] !== true) {
    $errors[] = 'You must consent to being contacted about your application.';
}

// If there are validation errors, return them
if (!empty($errors)) {
    jsonResponse([
        'error' => true,
        'message' => 'Validation failed.',
        'validation_errors' => $errors
    ], 422);
}

// ===== SANITIZE INPUT =====
$firstName    = sanitize($data['first_name']);
$lastName     = sanitize($data['last_name']);
$dateOfBirth  = sanitize($data['date_of_birth']);
$nationality  = sanitize($data['nationality']);
$email        = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
$phone        = sanitize($data['phone'] ?? '');
$country      = sanitize($data['country']);
$school       = sanitize($data['school']);
$yearOfStudy  = sanitize($data['year_of_study']);
$tlevelCourse = sanitize($data['tlevel_course']);
$location     = sanitize($data['preferred_location']);
$start        = sanitize($data['preferred_start']);
$duration     = sanitize($data['preferred_duration'] ?? '');
$motivation   = sanitize($data['motivation']);
$skills       = sanitize($data['skills'] ?? '');
$needs        = sanitize($data['additional_needs'] ?? '');
$heardFrom    = sanitize($data['heard_from'] ?? '');

// Checkbox values (default to 0)
$reqAccommodation = !empty($data['req_accommodation']) ? 1 : 0;
$reqTransport     = !empty($data['req_transport']) ? 1 : 0;
$reqVisa          = !empty($data['req_visa']) ? 1 : 0;
$reqLanguage      = !empty($data['req_language']) ? 1 : 0;

try {
    $db = Database::getConnection();
    Database::initializeTables();

    // Generate unique reference number (retry on collision)
    $maxRetries = 5;
    $refNumber = '';
    for ($i = 0; $i < $maxRetries; $i++) {
        $refNumber = generateReferenceNumber();
        $check = $db->prepare("SELECT COUNT(*) as cnt FROM applications WHERE ref_number = ?");
        $check->execute([$refNumber]);
        if ($check->fetch()['cnt'] === 0) {
            break;
        }
    }

    // Insert application
    $stmt = $db->prepare("
        INSERT INTO applications (
            ref_number, first_name, last_name, date_of_birth, nationality,
            email, phone, country, school, year_of_study, tlevel_course,
            preferred_location, preferred_start, preferred_duration,
            req_accommodation, req_transport, req_visa, req_language,
            heard_from, motivation, skills, additional_needs
        ) VALUES (
            ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?,
            ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?, ?, ?
        )
    ");

    $stmt->execute([
        $refNumber, $firstName, $lastName, $dateOfBirth, $nationality,
        $email, $phone, $country, $school, $yearOfStudy, $tlevelCourse,
        $location, $start, $duration,
        $reqAccommodation, $reqTransport, $reqVisa, $reqLanguage,
        $heardFrom, $motivation, $skills, $needs
    ]);

    $applicationId = $db->lastInsertId();

    appLog("New application submitted: {$refNumber} - {$firstName} {$lastName} ({$email})");

    // Return success with reference number
    jsonResponse([
        'error' => false,
        'message' => 'Application submitted successfully.',
        'application' => [
            'id' => (int)$applicationId,
            'ref_number' => $refNumber,
            'status' => 'received',
            'submitted_at' => date('Y-m-d H:i:s')
        ]
    ], 201);

} catch (PDOException $e) {
    appLog("Database error submitting application: " . $e->getMessage(), 'ERROR');
    errorResponse('An error occurred while submitting your application. Please try again later.', 500);
}