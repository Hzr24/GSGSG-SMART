<?php
/**
 * Dashboard API Router
 * GSGSG Scholarship Tracking System
 *
 * Returns lightweight KPI metrics for the dashboard overview.
 * Faster than hitting students.php which loads full semester/course data.
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/controllers/DashboardController.php';

$controller = new DashboardController($pdo);
echo $controller->metrics();
