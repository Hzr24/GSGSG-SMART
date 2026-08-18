<?php
/**
 * Database Connection & Migration Helper
 * GSGSG Scholarship Tracking System
 */

header('Content-Type: application/json; charset=utf-8');

// Load .env if present
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value, " \t\n\r\0\x0B\"'");
            $_ENV[$name] = $value;
            putenv("$name=$value");
        }
    }
}

$host   = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: '127.0.0.1';
$port   = $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: '3306';
$user   = $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'root';
$pass   = $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?: '';
$dbname = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'smart_db';

try {
    // 1. Connect without db to ensure database exists
    $pdoInit = new PDO("mysql:host=$host;port=$port;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    $pdoInit->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

    // 2. Connect to database
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // 3. Ensure tables exist
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS students (
            id VARCHAR(50) PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            program VARCHAR(255) NOT NULL,
            year INT NOT NULL DEFAULT 1,
            email VARCHAR(255),
            notes TEXT,
            status_override ENUM('Active', 'At Risk', 'Revoked') NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB;

        CREATE TABLE IF NOT EXISTS semesters (
            id INT AUTO_INCREMENT PRIMARY KEY,
            student_id VARCHAR(50) NOT NULL,
            label VARCHAR(150) NOT NULL,
            sort_order INT DEFAULT 0,
            FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
        ) ENGINE=InnoDB;

        CREATE TABLE IF NOT EXISTS courses (
            id INT AUTO_INCREMENT PRIMARY KEY,
            semester_id INT NOT NULL,
            code VARCHAR(50) NOT NULL,
            name VARCHAR(255) NOT NULL,
            credits INT NOT NULL DEFAULT 3,
            grade VARCHAR(5) NOT NULL DEFAULT 'A',
            FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE CASCADE
        ) ENGINE=InnoDB;
    ");

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Grade points map
function getGradePoints($grade) {
    $points = [
        'A+' => 4.0, 'A' => 4.0, 'A-' => 3.7,
        'B+' => 3.3, 'B' => 3.0, 'B-' => 2.7,
        'C+' => 2.3, 'C' => 2.0, 'C-' => 1.7,
        'D+' => 1.3, 'D' => 1.0,
        'F'  => 0.0
    ];
    return $points[$grade] ?? 0.0;
}

function calculateScholarshipStatus($cgpa) {
    if ($cgpa >= 3.75) return 'Active';
    if ($cgpa >= 3.50) return 'At Risk';
    return 'Revoked';
}
