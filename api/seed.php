<?php
/**
 * Database Seeder - GSGSG Postgraduate Programmes
 * Ghazali Shafie Graduate School of Government (GSGSG)
 * Universiti Utara Malaysia
 */

require_once __DIR__ . '/db.php';

$stmt = $pdo->query("SELECT COUNT(*) as count FROM students");
$row = $stmt->fetch();

$force = (isset($_GET['force']) && $_GET['force'] === '1') || (isset($force) && $force === true) || (isset($argv[1]) && $argv[1] === '--force');

if ($row['count'] > 0 && !$force) {
    echo json_encode([
        'status' => 'info',
        'message' => 'Database already populated with ' . $row['count'] . ' students.',
        'seeded' => false
    ]);
    exit;
}

if ($force) {
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $pdo->exec("TRUNCATE TABLE courses;");
    $pdo->exec("TRUNCATE TABLE semesters;");
    $pdo->exec("TRUNCATE TABLE students;");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
}

$mockStudents = [
    [
        'id' => 'STU-2022-001',
        'name' => 'Ahmad Hafizuddin bin Razali',
        'program' => 'PhD (Public Administration)',
        'year' => 3,
        'email' => 'ahmad.hafiz@gsgsg.edu.my',
        'notes' => 'Passed Proposal Defense with Distinction. Research topic: Decentralized Governance Models in Public Sector.',
        'semesters' => [
            [
                'label' => 'Semester 1 (2022/2023)',
                'courses' => [
                    ['code'=>'SZZ6013', 'name'=>'Advanced Research Methodology', 'credits'=>3, 'grade'=>'A'],
                    ['code'=>'SZP6013', 'name'=>'Seminar in Public Administration Theory', 'credits'=>3, 'grade'=>'A'],
                    ['code'=>'SZP6023', 'name'=>'Public Policy Formulation & Evaluation', 'credits'=>3, 'grade'=>'A-']
                ]
            ],
            [
                'label' => 'Semester 2 (2022/2023)',
                'courses' => [
                    ['code'=>'SZZ6023', 'name'=>'Advanced Qualitative & Quantitative Analysis', 'credits'=>3, 'grade'=>'A'],
                    ['code'=>'SZP6033', 'name'=>'Comparative Public Management', 'credits'=>3, 'grade'=>'A'],
                    ['code'=>'THS6996', 'name'=>'Doctoral Research Proposal Defense', 'credits'=>6, 'grade'=>'A']
                ]
            ],
            [
                'label' => 'Semester 1 (2023/2024)',
                'courses' => [
                    ['code'=>'THS7012', 'name'=>'Doctoral Thesis Progress: Fieldwork & Data Collection', 'credits'=>9, 'grade'=>'A'],
                    ['code'=>'PUB7003', 'name'=>'Doctoral Colloquium & Scopus Publication I', 'credits'=>3, 'grade'=>'A']
                ]
            ],
            [
                'label' => 'Semester 2 (2023/2024)',
                'courses' => [
                    ['code'=>'THS7022', 'name'=>'Doctoral Thesis Progress: Data Analysis & Chapter Drafts', 'credits'=>9, 'grade'=>'A'],
                    ['code'=>'PUB7004', 'name'=>'High-Impact Journal Publication II', 'credits'=>3, 'grade'=>'A-']
                ]
            ]
        ]
    ],
    [
        'id' => 'STU-2022-002',
        'name' => 'Nurul Ain binti Khalid',
        'program' => 'Master of Public Management (MPM)',
        'year' => 2,
        'email' => 'nurul.ain@gsgsg.edu.my',
        'notes' => 'Highest CGPA in MPM cohort. Recipient of Dean\'s Honor Roll for 3 consecutive semesters.',
        'semesters' => [
            [
                'label' => 'Semester 1 (2023/2024)',
                'courses' => [
                    ['code'=>'MPM5013', 'name'=>'Public Policy Analysis', 'credits'=>3, 'grade'=>'A'],
                    ['code'=>'MPM5023', 'name'=>'Strategic Human Resource Management in Public Sector', 'credits'=>3, 'grade'=>'A'],
                    ['code'=>'MPM5033', 'name'=>'Organizational Behavior & Leadership', 'credits'=>3, 'grade'=>'A'],
                    ['code'=>'MPM5043', 'name'=>'Public Financial Management & Budgeting', 'credits'=>3, 'grade'=>'A-']
                ]
            ],
            [
                'label' => 'Semester 2 (2023/2024)',
                'courses' => [
                    ['code'=>'MPM6013', 'name'=>'Governance, Ethics & Accountability', 'credits'=>3, 'grade'=>'A'],
                    ['code'=>'MPM6023', 'name'=>'E-Government & Digital Public Transformation', 'credits'=>3, 'grade'=>'A'],
                    ['code'=>'MPM6033', 'name'=>'Applied Research Methods for Public Managers', 'credits'=>3, 'grade'=>'A'],
                    ['code'=>'MPM6043', 'name'=>'Crisis & Risk Management in Government', 'credits'=>3, 'grade'=>'A']
                ]
            ],
            [
                'label' => 'Semester 1 (2024/2025)',
                'courses' => [
                    ['code'=>'MPM6996', 'name'=>'MPM Consulting Practicum & Policy Project', 'credits'=>6, 'grade'=>'A'],
                    ['code'=>'MPM6053', 'name'=>'Project Management in Government Sector', 'credits'=>3, 'grade'=>'A']
                ]
            ]
        ]
    ],
    [
        'id' => 'STU-2022-003',
        'name' => 'Muhammad Irfan bin Hassan',
        'program' => 'PhD (Strategic Studies)',
        'year' => 3,
        'email' => 'irfan.hassan@gsgsg.edu.my',
        'notes' => 'Scholarship status under Academic Committee Review. CGPA dropped to 3.48 due to semester 2 course results.',
        'semesters' => [
            [
                'label' => 'Semester 1 (2022/2023)',
                'courses' => [
                    ['code'=>'SZZ6013', 'name'=>'Advanced Research Methodology', 'credits'=>3, 'grade'=>'B+'],
                    ['code'=>'SZS6013', 'name'=>'Theories of Strategy & Geopolitics', 'credits'=>3, 'grade'=>'B+'],
                    ['code'=>'SZS6023', 'name'=>'Maritime Security in Indo-Pacific', 'credits'=>3, 'grade'=>'B']
                ]
            ],
            [
                'label' => 'Semester 2 (2022/2023)',
                'courses' => [
                    ['code'=>'SZS6033', 'name'=>'Contemporary Warfare & Cyber Security', 'credits'=>3, 'grade'=>'B-'],
                    ['code'=>'SZS6043', 'name'=>'Intelligence & National Security Policy', 'credits'=>3, 'grade'=>'C+'],
                    ['code'=>'THS6996', 'name'=>'Doctoral Research Proposal Drafting', 'credits'=>6, 'grade'=>'B']
                ]
            ],
            [
                'label' => 'Semester 1 (2023/2024)',
                'courses' => [
                    ['code'=>'THS7012', 'name'=>'Doctoral Fieldwork & Case Studies', 'credits'=>9, 'grade'=>'B+'],
                    ['code'=>'SZS6053', 'name'=>'Regional Security Frameworks in ASEAN', 'credits'=>3, 'grade'=>'B']
                ]
            ]
        ]
    ],
    [
        'id' => 'STU-2023-001',
        'name' => 'Siti Zulaikha binti Othman',
        'program' => 'Master of Commercial Law',
        'year' => 2,
        'email' => 'zulaikha.othman@gsgsg.edu.my',
        'notes' => 'Outstanding moot court representation for GSGSG. Excellent academic standing.',
        'semesters' => [
            [
                'label' => 'Semester 1 (2023/2024)',
                'courses' => [
                    ['code'=>'GLM5013', 'name'=>'Advanced Commercial Contracts', 'credits'=>3, 'grade'=>'A'],
                    ['code'=>'GLM5023', 'name'=>'International Trade Law & WTO', 'credits'=>3, 'grade'=>'A-'],
                    ['code'=>'GLM5033', 'name'=>'Corporate Governance & Securities Regulation', 'credits'=>3, 'grade'=>'A'],
                    ['code'=>'GLM5043', 'name'=>'Legal Research & Jurisprudence', 'credits'=>3, 'grade'=>'A']
                ]
            ],
            [
                'label' => 'Semester 2 (2023/2024)',
                'courses' => [
                    ['code'=>'GLM6013', 'name'=>'Banking & Islamic Financial Law', 'credits'=>3, 'grade'=>'A'],
                    ['code'=>'GLM6023', 'name'=>'Cross-Border Insolvency Law', 'credits'=>3, 'grade'=>'A'],
                    ['code'=>'GLM6033', 'name'=>'Alternative Dispute Resolution & Arbitration', 'credits'=>3, 'grade'=>'A-']
                ]
            ],
            [
                'label' => 'Semester 1 (2024/2025)',
                'courses' => [
                    ['code'=>'GLM6996', 'name'=>'Commercial Law Master Dissertation', 'credits'=>6, 'grade'=>'A']
                ]
            ]
        ]
    ],
    [
        'id' => 'STU-2023-002',
        'name' => 'Daniel Tan Wei Kang',
        'program' => 'PhD (International Business/Studies)',
        'year' => 2,
        'email' => 'daniel.tan@gsgsg.edu.my',
        'notes' => 'Co-funded scholarship with ASEAN Institute. Research on Bilateral Trade Tariffs.',
        'semesters' => [
            [
                'label' => 'Semester 1 (2023/2024)',
                'courses' => [
                    ['code'=>'SZZ6013', 'name'=>'Advanced Research Methodology', 'credits'=>3, 'grade'=>'A'],
                    ['code'=>'SZB6013', 'name'=>'Seminar in Global Business Strategies', 'credits'=>3, 'grade'=>'A'],
                    ['code'=>'SZB6023', 'name'=>'Emerging Markets & FDI Governance', 'credits'=>3, 'grade'=>'A-']
                ]
            ],
            [
                'label' => 'Semester 2 (2023/2024)',
                'courses' => [
                    ['code'=>'SZB6033', 'name'=>'International Supply Chain Geopolitics', 'credits'=>3, 'grade'=>'A'],
                    ['code'=>'THS6996', 'name'=>'Proposal Defense Examination', 'credits'=>6, 'grade'=>'A']
                ]
            ]
        ]
    ],
    [
        'id' => 'STU-2023-003',
        'name' => 'Priya a/p Mohan',
        'program' => 'Master of Science (Strategic Studies)',
        'year' => 2,
        'email' => 'priya.mohan@gsgsg.edu.my',
        'notes' => 'Status Revoked. Missed minimum credit threshold and semester CGPA below 3.00.',
        'semesters' => [
            [
                'label' => 'Semester 1 (2023/2024)',
                'courses' => [
                    ['code'=>'SZS5013', 'name'=>'Strategic Thought & Military Theory', 'credits'=>3, 'grade'=>'C+'],
                    ['code'=>'SZS5023', 'name'=>'Diplomacy & Conflict Resolution', 'credits'=>3, 'grade'=>'C'],
                    ['code'=>'SZS5033', 'name'=>'Defense Resource Management', 'credits'=>3, 'grade'=>'D+']
                ]
            ],
            [
                'label' => 'Semester 2 (2023/2024)',
                'courses' => [
                    ['code'=>'SZS5043', 'name'=>'National Security Decision Making', 'credits'=>3, 'grade'=>'C-'],
                    ['code'=>'SZS5053', 'name'=>'Intelligence Analysis Practicum', 'credits'=>3, 'grade'=>'F']
                ]
            ]
        ]
    ],
    [
        'id' => 'STU-2024-001',
        'name' => 'Farah Nabilah binti Mansor',
        'program' => 'Master of Arts (International Relations)',
        'year' => 1,
        'email' => 'farah.nabilah@gsgsg.edu.my',
        'notes' => 'First year scholar. High performance in Multilateral Diplomacy seminar.',
        'semesters' => [
            [
                'label' => 'Semester 1 (2024/2025)',
                'courses' => [
                    ['code'=>'SIR5013', 'name'=>'Theories of International Relations', 'credits'=>3, 'grade'=>'A'],
                    ['code'=>'SIR5023', 'name'=>'Foreign Policy Analysis', 'credits'=>3, 'grade'=>'A'],
                    ['code'=>'SIR5033', 'name'=>'International Organizations & Governance', 'credits'=>3, 'grade'=>'A-'],
                    ['code'=>'SZZ5013', 'name'=>'Graduate Research Methods', 'credits'=>3, 'grade'=>'A']
                ]
            ]
        ]
    ],
    [
        'id' => 'STU-2024-002',
        'name' => 'Arif Danial bin Suhaimi',
        'program' => 'PhD (Peace and Conflict Transformation)',
        'year' => 2,
        'email' => 'arif.suhaimi@gsgsg.edu.my',
        'notes' => 'Conducting fieldwork on Mindanao peacebuilding mechanisms. Maintained 3.89 CGPA.',
        'semesters' => [
            [
                'label' => 'Semester 1 (2023/2024)',
                'courses' => [
                    ['code'=>'SZZ6013', 'name'=>'Advanced Qualitative Research', 'credits'=>3, 'grade'=>'A'],
                    ['code'=>'SZC6013', 'name'=>'Seminar in Peace & Conflict Theories', 'credits'=>3, 'grade'=>'A'],
                    ['code'=>'SZC6023', 'name'=>'Mediation & Negotiation Techniques', 'credits'=>3, 'grade'=>'A-']
                ]
            ],
            [
                'label' => 'Semester 2 (2023/2024)',
                'courses' => [
                    ['code'=>'SZC6033', 'name'=>'Post-Conflict Reconstruction & Transitional Justice', 'credits'=>3, 'grade'=>'A'],
                    ['code'=>'THS6996', 'name'=>'PhD Proposal Defense', 'credits'=>6, 'grade'=>'A']
                ]
            ]
        ]
    ],
    [
        'id' => 'STU-2024-003',
        'name' => 'Wan Farisha binti Wan Ibrahim',
        'program' => 'PhD (Law)',
        'year' => 2,
        'email' => 'farisha.wi@gsgsg.edu.my',
        'notes' => 'Distinguished legal research in Constitutional Law & Federalism. 4.00 CGPA.',
        'semesters' => [
            [
                'label' => 'Semester 1 (2023/2024)',
                'courses' => [
                    ['code'=>'GLM6013', 'name'=>'Advanced Legal Research Methodology', 'credits'=>3, 'grade'=>'A'],
                    ['code'=>'GLM6023', 'name'=>'Comparative Constitutional Jurisprudence', 'credits'=>3, 'grade'=>'A'],
                    ['code'=>'GLM6033', 'name'=>'Human Rights & Administrative Justice', 'credits'=>3, 'grade'=>'A']
                ]
            ],
            [
                'label' => 'Semester 2 (2023/2024)',
                'courses' => [
                    ['code'=>'GLM6043', 'name'=>'Statutory Interpretation & Judicial Review', 'credits'=>3, 'grade'=>'A'],
                    ['code'=>'THS6996', 'name'=>'Doctoral Proposal Defense Examination', 'credits'=>6, 'grade'=>'A']
                ]
            ]
        ]
    ],
    [
        'id' => 'STU-2024-004',
        'name' => 'Khairul Anuar bin Basri',
        'program' => 'Master of Arts in Nationhood Studies',
        'year' => 1,
        'email' => 'khairul.anuar@gsgsg.edu.my',
        'notes' => 'New intake scholar. Good academic start.',
        'semesters' => [
            [
                'label' => 'Semester 1 (2024/2025)',
                'courses' => [
                    ['code'=>'MNS5013', 'name'=>'Malaysian Political History & Nation Building', 'credits'=>3, 'grade'=>'A-'],
                    ['code'=>'MNS5023', 'name'=>'Ethnicity, Culture & National Integration', 'credits'=>3, 'grade'=>'A'],
                    ['code'=>'MNS5033', 'name'=>'Federal Constitution & Institutional Governance', 'credits'=>3, 'grade'=>'B+']
                ]
            ]
        ]
    ]
];

$pdo->beginTransaction();

$stmtStudent = $pdo->prepare("INSERT INTO students (id, name, program, year, email, notes) VALUES (?, ?, ?, ?, ?, ?)");
$stmtSem = $pdo->prepare("INSERT INTO semesters (student_id, label, sort_order) VALUES (?, ?, ?)");
$stmtCourse = $pdo->prepare("INSERT INTO courses (semester_id, code, name, credits, grade) VALUES (?, ?, ?, ?, ?)");

foreach ($mockStudents as $s) {
    $stmtStudent->execute([$s['id'], $s['name'], $s['program'], $s['year'], $s['email'], $s['notes']]);
    foreach ($s['semesters'] as $si => $sem) {
        $stmtSem->execute([$s['id'], $sem['label'], $si]);
        $semId = $pdo->lastInsertId();
        foreach ($sem['courses'] as $c) {
            $stmtCourse->execute([$semId, $c['code'], $c['name'], $c['credits'], $c['grade']]);
        }
    }
}

$pdo->commit();

echo json_encode([
    'status' => 'success',
    'message' => 'Successfully seeded ' . count($mockStudents) . ' GSGSG postgraduate scholars into MySQL.',
    'count' => count($mockStudents)
]);
