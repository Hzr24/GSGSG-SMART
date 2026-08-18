<?php
/**
 * Courses API Router
 * GSGSG Scholarship Tracking System
 *
 * Thin router — delegates all logic to CourseController.
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/controllers/CourseController.php';

$controller = new CourseController($pdo);
$method     = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $input  = json_decode(file_get_contents('php://input'), true) ?? [];
    $action = $input['action'] ?? '';

    echo match ($action) {
        'add_semester'    => $controller->addSemester($input),
        'delete_semester' => $controller->deleteSemester($input),
        'add_course'      => $controller->addCourse($input),
        'delete_course'   => $controller->deleteCourse($input),
        default           => (function () {
            http_response_code(400);
            return json_encode(['status' => 'error', 'message' => 'Unknown action']);
        })(),
    };
    exit;
}
