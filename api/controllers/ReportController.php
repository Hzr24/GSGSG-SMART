<?php
/**
 * ReportController
 * GSGSG Scholarship Tracking System
 *
 * Generates filtered, pre-aggregated report data for the formal
 * committee report page. Supports three report types:
 *   all    — all scholarship recipients
 *   audit  — At Risk and Revoked students
 *   merit  — Students with CGPA >= 3.85 (Dean's Honor Roll)
 *
 * Also returns a per-program breakdown summary.
 */

require_once __DIR__ . '/StudentController.php';

class ReportController
{
    private PDO               $pdo;
    private StudentController $students;

    public function __construct(PDO $pdo)
    {
        $this->pdo      = $pdo;
        $this->students = new StudentController($pdo);
    }

    /**
     * POST /api/reports.php
     *
     * Input (optional):
     *   type    — 'all' | 'audit' | 'merit'  (default: 'all')
     *   program — program name or 'all'       (default: 'all')
     *
     * Returns:
     *   data            — filtered student array (with semesters/courses)
     *   programSummary  — per-program count + avg CGPA
     *   totals          — aggregate totals for the report header
     */
    public function generate(array $input): string
    {
        $type    = $input['type']    ?? 'all';
        $program = $input['program'] ?? 'all';

        // Load all students with full details
        $stmt = $this->pdo->query("SELECT id FROM students ORDER BY name ASC");
        $ids  = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $all = [];
        foreach ($ids as $id) {
            $s = $this->students->getStudentDetails($id);
            if ($s) $all[] = $s;
        }

        // Filter by report type
        $filtered = match ($type) {
            'audit' => array_filter($all, fn($s) => in_array($s['status'], ['At Risk', 'Revoked'])),
            'merit' => array_filter($all, fn($s) => $s['cgpa'] >= 3.85),
            default => $all,
        };

        // Filter by program
        if ($program !== 'all' && !empty($program)) {
            $filtered = array_filter($filtered, fn($s) => $s['program'] === $program);
        }

        $filtered = array_values($filtered);

        // Per-program breakdown
        $programMap = [];
        foreach ($all as $s) {
            $prog = $s['program'];
            if (!isset($programMap[$prog])) {
                $programMap[$prog] = ['program' => $prog, 'count' => 0, 'cgpaSum' => 0, 'active' => 0, 'atRisk' => 0, 'revoked' => 0];
            }
            $programMap[$prog]['count']++;
            $programMap[$prog]['cgpaSum'] += $s['cgpa'];
            match ($s['status']) {
                'Active'  => $programMap[$prog]['active']++,
                'At Risk' => $programMap[$prog]['atRisk']++,
                'Revoked' => $programMap[$prog]['revoked']++,
                default   => null,
            };
        }

        $programSummary = array_map(function ($p) {
            $p['avgCgpa'] = $p['count'] > 0 ? round($p['cgpaSum'] / $p['count'], 2) : 0;
            unset($p['cgpaSum']);
            return $p;
        }, array_values($programMap));

        // Aggregate totals from filtered set
        $total   = count($filtered);
        $cgpaSum = array_sum(array_column($filtered, 'cgpa'));

        return json_encode([
            'status'         => 'success',
            'data'           => $filtered,
            'programSummary' => $programSummary,
            'totals'         => [
                'total'   => $total,
                'avgCgpa' => $total > 0 ? round($cgpaSum / $total, 2) : 0,
                'active'  => count(array_filter($filtered, fn($s) => $s['status'] === 'Active')),
                'atRisk'  => count(array_filter($filtered, fn($s) => $s['status'] === 'At Risk')),
                'revoked' => count(array_filter($filtered, fn($s) => $s['status'] === 'Revoked')),
            ],
        ]);
    }
}
