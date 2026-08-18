<?php
/**
 * DashboardController
 * GSGSG Scholarship Tracking System
 *
 * Returns lightweight KPI metrics without returning full student data.
 * Useful for the dashboard overview cards.
 */

class DashboardController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /** GET /api/dashboard.php — aggregated KPI metrics */
    public function metrics(): string
    {
        // Pull raw students
        $stmt = $this->pdo->query("SELECT id, status_override FROM students");
        $rows = $stmt->fetchAll();
        $total = count($rows);

        if ($total === 0) {
            return json_encode([
                'status'  => 'success',
                'metrics' => [
                    'total'      => 0,
                    'active'     => 0,
                    'atRisk'     => 0,
                    'revoked'    => 0,
                    'activeRate' => 0,
                    'avgCgpa'    => 0,
                ],
            ]);
        }

        // Compute CGPA per student via DB aggregation
        $cgpaStmt = $this->pdo->query("
            SELECT
                s.id,
                s.status_override,
                COALESCE(SUM(c.credits), 0) AS total_credits,
                COALESCE(
                    SUM(gp.points * c.credits) / NULLIF(SUM(c.credits), 0),
                    0
                ) AS cgpa
            FROM students s
            LEFT JOIN semesters sem ON sem.student_id = s.id
            LEFT JOIN courses c ON c.semester_id = sem.id
            LEFT JOIN (
                SELECT 'A+' AS grade, 4.0 AS points UNION ALL
                SELECT 'A',  4.0 UNION ALL SELECT 'A-', 3.7 UNION ALL
                SELECT 'B+', 3.3 UNION ALL SELECT 'B',  3.0 UNION ALL SELECT 'B-', 2.7 UNION ALL
                SELECT 'C+', 2.3 UNION ALL SELECT 'C',  2.0 UNION ALL SELECT 'C-', 1.7 UNION ALL
                SELECT 'D+', 1.3 UNION ALL SELECT 'D',  1.0 UNION ALL
                SELECT 'F',  0.0
            ) gp ON gp.grade = c.grade
            GROUP BY s.id, s.status_override
        ");
        $studentRows = $cgpaStmt->fetchAll();

        $activeCount = $atRiskCount = $revokedCount = 0;
        $cgpaSum = 0;

        foreach ($studentRows as $s) {
            $cgpa = (float)$s['cgpa'];
            $cgpaSum += $cgpa;

            // Honour manual override if set, else calculate
            if (!empty($s['status_override'])) {
                $status = $s['status_override'];
            } elseif ((int)$s['total_credits'] === 0) {
                $status = 'Active';
            } else {
                $status = calculateScholarshipStatus($cgpa);
            }

            match ($status) {
                'Active'  => $activeCount++,
                'At Risk' => $atRiskCount++,
                'Revoked' => $revokedCount++,
                default   => null,
            };
        }

        return json_encode([
            'status'  => 'success',
            'metrics' => [
                'total'      => $total,
                'active'     => $activeCount,
                'atRisk'     => $atRiskCount,
                'revoked'    => $revokedCount,
                'activeRate' => round(($activeCount / $total) * 100),
                'avgCgpa'    => round($cgpaSum / $total, 2),
            ],
        ]);
    }
}
