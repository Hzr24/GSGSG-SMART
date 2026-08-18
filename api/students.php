<?php
/**
 * Students API Router
 * GSGSG Scholarship Tracking System
 *
 * Thin router — delegates all logic to StudentController.
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/controllers/StudentController.php';

$controller = new StudentController($pdo);
$method     = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $id = trim($_GET['id'] ?? '');
    echo $id ? $controller->get($id) : $controller->list();
    exit;
}

if ($method === 'POST') {
    $input  = json_decode(file_get_contents('php://input'), true) ?? [];
    $action = $input['action'] ?? '';

    echo match ($action) {
        'create', 'add'          => $controller->create($input),
        'update_student', 'edit' => $controller->updateStudent($input),
        'update_notes'           => $controller->updateNotes($input),
        'update_year'            => $controller->updateYear($input),
        'toggle_status'          => $controller->toggleStatus($input),
        'delete'                 => $controller->delete($input),
        'import'                 => $controller->import($input),
        default         => (function () {
            http_response_code(400);
            return json_encode(['status' => 'error', 'message' => 'Unknown action']);
        })(),
    };
    exit;
}
