<?php
/**
 * Courses & Placements API
 * 
 * Returns course data and placement information for the frontend.
 * 
 * Endpoints:
 *   GET /api/courses.php       - List all courses
 *   GET /api/courses.php?id=X  - Get specific course details
 *   GET /api/courses.php?locations=true - Get placement locations
 * 
 * @author Amazon T-Level Hub Backend
 * @version 1.0.0
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';

setCorsHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    errorResponse('Method not allowed. Use GET.', 405);
}

try {
    $db = Database::getConnection();
    Database::initializeTables();

    // Return placement locations
    if (isset($_GET['locations'])) {
        jsonResponse([
            'error' => false,
            'locations' => getPlacementLocations()
        ]);
    }

    // Return specific course
    if (isset($_GET['id'])) {
        $courseId = sanitize($_GET['id']);
        $courses = getCourses();
        $found = null;
        foreach ($courses as $course) {
            if ($course['id'] === $courseId) {
                $found = $course;
                break;
            }
        }
        if ($found) {
            jsonResponse(['error' => false, 'course' => $found]);
        } else {
            errorResponse('Course not found.', 404);
        }
    }

    // Return all courses
    jsonResponse([
        'error' => false,
        'count' => count(getCourses()),
        'courses' => getCourses()
    ]);

} catch (PDOException $e) {
    appLog("Database error in courses API: " . $e->getMessage(), 'ERROR');
    errorResponse('An error occurred while fetching course data.', 500);
}