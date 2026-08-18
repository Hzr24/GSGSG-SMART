<?php
/**
 * CourseController
 * GSGSG Scholarship Tracking System
 *
 * Handles semester and course CRUD:
 * add_semester, delete_semester, add_course, delete_course
 */

require_once __DIR__ . '/StudentController.php';

class CourseController
{
    private PDO               $pdo;
    private StudentController $students;

    public function __construct(PDO $pdo)
    {
        $this->pdo      = $pdo;
        $this->students = new StudentController($pdo);
    }

    // ----------------------------------------------------------
    //  Public API methods
    // ----------------------------------------------------------

    /** action=add_semester */
    public function addSemester(array $input): string
    {
        $studentId = trim($input['student_id'] ?? '');
        $label     = trim($input['label'] ?? '');

        if (empty($studentId) || empty($label)) {
            http_response_code(400);
            return json_encode(['status' => 'error', 'message' => 'Student ID and Semester label required']);
        }

        $countStmt = $this->pdo->prepare("SELECT COUNT(*) AS c FROM semesters WHERE student_id = ?");
        $countStmt->execute([$studentId]);
        $order = (int)$countStmt->fetch()['c'];

        $this->pdo
            ->prepare("INSERT INTO semesters (student_id, label, sort_order) VALUES (?, ?, ?)")
            ->execute([$studentId, $label, $order]);

        $newSemId = $this->pdo->lastInsertId();

        return json_encode([
            'status'      => 'success',
            'message'     => 'Semester added',
            'semester_id' => $newSemId,
            'student'     => $this->students->getStudentDetails($studentId),
        ]);
    }

    /** action=delete_semester */
    public function deleteSemester(array $input): string
    {
        $semId     = (int)($input['semester_id'] ?? 0);
        $studentId = trim($input['student_id'] ?? '');

        if ($semId <= 0) {
            http_response_code(400);
            return json_encode(['status' => 'error', 'message' => 'Invalid semester ID']);
        }

        $this->pdo->prepare("DELETE FROM semesters WHERE id = ?")->execute([$semId]);

        return json_encode([
            'status'  => 'success',
            'message' => 'Semester deleted',
            'student' => !empty($studentId) ? $this->students->getStudentDetails($studentId) : null,
        ]);
    }

    /** action=add_course */
    public function addCourse(array $input): string
    {
        $semId     = (int)($input['semester_id'] ?? 0);
        $studentId = trim($input['student_id'] ?? '');
        $code      = trim($input['code']    ?? 'CRS');
        $name      = trim($input['name']    ?? '');
        $credits   = (int)($input['credits'] ?? 3);
        $grade     = trim($input['grade']   ?? 'A');

        if ($semId <= 0 || empty($name)) {
            http_response_code(400);
            return json_encode(['status' => 'error', 'message' => 'Valid Semester ID and Course Name required']);
        }

        $this->pdo
            ->prepare("INSERT INTO courses (semester_id, code, name, credits, grade) VALUES (?, ?, ?, ?, ?)")
            ->execute([$semId, $code, $name, $credits, $grade]);

        $newCourseId = $this->pdo->lastInsertId();

        return json_encode([
            'status'    => 'success',
            'message'   => 'Course added',
            'course_id' => $newCourseId,
            'student'   => !empty($studentId) ? $this->students->getStudentDetails($studentId) : null,
        ]);
    }

    /** action=delete_course */
    public function deleteCourse(array $input): string
    {
        $courseId  = (int)($input['course_id'] ?? 0);
        $studentId = trim($input['student_id'] ?? '');

        if ($courseId <= 0) {
            http_response_code(400);
            return json_encode(['status' => 'error', 'message' => 'Invalid course ID']);
        }

        $this->pdo->prepare("DELETE FROM courses WHERE id = ?")->execute([$courseId]);

        return json_encode([
            'status'  => 'success',
            'message' => 'Course removed',
            'student' => !empty($studentId) ? $this->students->getStudentDetails($studentId) : null,
        ]);
    }
}
