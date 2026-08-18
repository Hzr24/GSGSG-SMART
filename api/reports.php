<?php
/**
 * Reports API Router
 * GSGSG Scholarship Tracking System
 *
 * Returns filtered report data + program breakdown for the formal committee report page.
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/controllers/ReportController.php';

$controller = new ReportController($pdo);
$method     = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    echo $controller->generate($input);
    exit;
}

// Default GET — return all students (no filter)
echo $controller->generate([]);
