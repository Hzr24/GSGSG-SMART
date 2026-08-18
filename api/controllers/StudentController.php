<?php
/**
 * StudentController
 * GSGSG Scholarship Tracking System
 *
 * Handles all student CRUD operations:
 * list, get, create, update_notes, toggle_status, delete, import
 */

class StudentController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // ----------------------------------------------------------
    //  Public API methods
    // ----------------------------------------------------------

    /** GET /api/students.php — all students + aggregate metrics */
    public function list(): string
    {
        $stmt = $this->pdo->query("SELECT id FROM students ORDER BY name ASC");
        $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $students = [];
        $total = count($ids);
        $activeCount = $atRiskCount = $revokedCount = 0;
        $cgpaSum = 0;

        foreach ($ids as $id) {
            $s = $this->getStudentDetails($id);
            if (!$s) continue;

            match ($s['status']) {
                'Active'  => $activeCount++,
                'At Risk' => $atRiskCount++,
                'Revoked' => $revokedCount++,
                default   => null,
            };

            $cgpaSum += $s['cgpa'];
            $students[] = $s;
        }

        return json_encode([
            'status' => 'success',
            'data'   => $students,
            'metrics' => [
                'total'      => $total,
                'active'     => $activeCount,
                'atRisk'     => $atRiskCount,
                'revoked'    => $revokedCount,
                'activeRate' => $total > 0 ? round(($activeCount / $total) * 100) : 0,
                'avgCgpa'    => $total > 0 ? round($cgpaSum / $total, 2) : 0,
            ],
        ]);
    }

    /** GET /api/students.php?id=X — single student with semesters/courses */
    public function get(string $id): string
    {
        $student = $this->getStudentDetails($id);
        if (!$student) {
            http_response_code(404);
            return json_encode(['status' => 'error', 'message' => 'Student not found']);
        }
        return json_encode(['status' => 'success', 'data' => $student]);
    }

    /** action=create|add */
    public function create(array $input): string
    {
        $id      = trim($input['id']   ?? '');
        $name    = trim($input['name'] ?? '');
        $program = trim($input['program'] ?? '');
        $year    = (int)($input['year'] ?? 1);
        $email   = trim($input['email'] ?? '');
        $notes   = trim($input['notes'] ?? '');

        if (empty($id) || empty($name)) {
            http_response_code(400);
            return json_encode(['status' => 'error', 'message' => 'Student ID and Name are required']);
        }

        if (empty($program) || $program === '__other__') {
            http_response_code(400);
            return json_encode(['status' => 'error', 'message' => 'Please provide a valid programme name']);
        }

        // Duplicate check
        $check = $this->pdo->prepare("SELECT id FROM students WHERE id = ?");
        $check->execute([$id]);
        if ($check->fetch()) {
            http_response_code(400);
            return json_encode(['status' => 'error', 'message' => 'Student ID already exists']);
        }

        $this->pdo
            ->prepare("INSERT INTO students (id, name, program, year, email, notes) VALUES (?, ?, ?, ?, ?, ?)")
            ->execute([$id, $name, $program, $year, $email, $notes]);

        return json_encode([
            'status'  => 'success',
            'message' => 'Student added successfully',
            'data'    => $this->getStudentDetails($id),
        ]);
    }

    /** action=update_notes */
    public function updateNotes(array $input): string
    {
        $id    = trim($input['id'] ?? '');
        $notes = $input['notes'] ?? '';

        if (empty($id)) {
            http_response_code(400);
            return json_encode(['status' => 'error', 'message' => 'Student ID required']);
        }

        $this->pdo->prepare("UPDATE students SET notes = ? WHERE id = ?")->execute([$notes, $id]);
        return json_encode(['status' => 'success', 'message' => 'Notes saved']);
    }

    /** action=update_year */
    public function updateYear(array $input): string
    {
        $id   = trim($input['id'] ?? '');
        $year = (int)($input['year'] ?? 1);

        if (empty($id) || $year < 1 || $year > 10) {
            http_response_code(400);
            return json_encode(['status' => 'error', 'message' => 'Valid Student ID and Academic Year (1-10) required']);
        }

        $this->pdo->prepare("UPDATE students SET year = ? WHERE id = ?")->execute([$year, $id]);
        return json_encode(['status' => 'success', 'message' => "Academic Year updated to Year $year", 'year' => $year]);
    }

    /** action=update_student|edit */
    public function updateStudent(array $input): string
    {
        $id      = trim($input['id'] ?? '');
        $name    = trim($input['name'] ?? '');
        $program = trim($input['program'] ?? '');
        $year    = (int)($input['year'] ?? 1);
        $email   = trim($input['email'] ?? '');

        if (empty($id) || empty($name)) {
            http_response_code(400);
            return json_encode(['status' => 'error', 'message' => 'Student ID and Full Name are required']);
        }

        if (empty($program) || $program === '__other__') {
            http_response_code(400);
            return json_encode(['status' => 'error', 'message' => 'Please provide a valid programme name']);
        }

        if ($year < 1 || $year > 10) {
            http_response_code(400);
            return json_encode(['status' => 'error', 'message' => 'Academic year must be between 1 and 10']);
        }

        $stmt = $this->pdo->prepare("UPDATE students SET name = ?, program = ?, year = ?, email = ? WHERE id = ?");
        $stmt->execute([$name, $program, $year, $email, $id]);

        return json_encode([
            'status'  => 'success',
            'message' => 'Student details updated successfully',
            'data'    => $this->getStudentDetails($id),
        ]);
    }

    /** action=toggle_status — cycles Active → At Risk → Revoked → Active */
    public function toggleStatus(array $input): string
    {
        $id = trim($input['id'] ?? '');
        if (empty($id)) {
            http_response_code(400);
            return json_encode(['status' => 'error', 'message' => 'Student ID required']);
        }

        $student = $this->getStudentDetails($id);
        if (!$student) {
            http_response_code(404);
            return json_encode(['status' => 'error', 'message' => 'Student not found']);
        }

        $seq  = ['Active', 'At Risk', 'Revoked'];
        $idx  = array_search($student['status'], $seq);
        $next = $seq[($idx === false ? 0 : $idx + 1) % count($seq)];

        $this->pdo->prepare("UPDATE students SET status_override = ? WHERE id = ?")->execute([$next, $id]);

        return json_encode([
            'status'     => 'success',
            'message'    => "Status toggled to $next",
            'new_status' => $next,
        ]);
    }

    /** action=delete */
    public function delete(array $input): string
    {
        $id = trim($input['id'] ?? '');
        if (empty($id)) {
            http_response_code(400);
            return json_encode(['status' => 'error', 'message' => 'Student ID required']);
        }

        $this->pdo->prepare("DELETE FROM students WHERE id = ?")->execute([$id]);
        return json_encode(['status' => 'success', 'message' => 'Student deleted successfully']);
    }

    /** action=import — bulk CSV import */
    public function import(array $input): string
    {
        $records = $input['students'] ?? [];
        if (!is_array($records) || empty($records)) {
            http_response_code(400);
            return json_encode(['status' => 'error', 'message' => 'No records to import']);
        }

        $added   = 0;
        $skipped = 0;

        $checkStmt  = $this->pdo->prepare("SELECT id FROM students WHERE id = ?");
        $insertStmt = $this->pdo->prepare("INSERT INTO students (id, name, program, year, email, notes) VALUES (?, ?, ?, ?, ?, ?)");

        foreach ($records as $r) {
            $id   = trim($r['id']   ?? '');
            $name = trim($r['name'] ?? '');
            if (empty($id) || empty($name)) continue;

            $checkStmt->execute([$id]);
            if ($checkStmt->fetch()) { $skipped++; continue; }

            $program = trim($r['program'] ?? 'Master of Public Management (MPM)');
            if ($program === '__other__' || empty($program)) {
                $program = 'Master of Public Management (MPM)';
            }
            $year    = (int)($r['year'] ?? 1);
            $email   = trim($r['email'] ?? '');

            $insertStmt->execute([$id, $name, $program, $year, $email, '']);
            $added++;
        }

        return json_encode([
            'status'  => 'success',
            'message' => "Import complete: $added imported, $skipped skipped (duplicates).",
            'added'   => $added,
            'skipped' => $skipped,
        ]);
    }

    // ----------------------------------------------------------
    //  Public helper — used by CourseController too
    // ----------------------------------------------------------

    public function getStudentDetails(string $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM students WHERE id = ?");
        $stmt->execute([$id]);
        $student = $stmt->fetch();
        if (!$student) return null;

        $stmtSem = $this->pdo->prepare(
            "SELECT * FROM semesters WHERE student_id = ? ORDER BY sort_order ASC, id ASC"
        );
        $stmtSem->execute([$id]);
        $semesters = $stmtSem->fetchAll();

        $totalPts     = 0;
        $totalCredits = 0;
        $lastSemGpa   = 0;

        foreach ($semesters as &$sem) {
            $stmtC = $this->pdo->prepare("SELECT * FROM courses WHERE semester_id = ? ORDER BY id ASC");
            $stmtC->execute([$sem['id']]);
            $courses = $stmtC->fetchAll();
            $sem['courses'] = $courses;

            $semPts = $semCredits = 0;
            foreach ($courses as $c) {
                $gp = getGradePoints($c['grade']);
                $cr = (int)$c['credits'];
                $semPts     += $gp * $cr;
                $semCredits += $cr;
            }

            $semGpa           = $semCredits > 0 ? round($semPts / $semCredits, 2) : 0.0;
            $sem['gpa']           = $semGpa;
            $sem['total_credits'] = $semCredits;

            $totalPts     += $semPts;
            $totalCredits += $semCredits;
            $lastSemGpa    = $semGpa;
        }
        unset($sem);

        $cgpa             = $totalCredits > 0 ? round($totalPts / $totalCredits, 2) : 0.0;
        $calculatedStatus = $totalCredits > 0 ? calculateScholarshipStatus($cgpa) : 'Active';
        $status           = !empty($student['status_override']) ? $student['status_override'] : $calculatedStatus;

        $student['semesters']         = $semesters;
        $student['cgpa']              = $cgpa;
        $student['gpa']               = $lastSemGpa;
        $student['total_credits']     = $totalCredits;
        $student['calculated_status'] = $calculatedStatus;
        $student['status']            = $status;

        return $student;
    }
}
