<?php
/**
 * Student Academic Detail Profile View
 * GSGSG Scholarship Tracking System
 */
$pageTitle = 'Student Profile &mdash; GSGSG-SMART';
$pageSubtitle = 'Academic History &amp; Transcript Performance';
$currentPage = 'detail';
$pageScript = 'js/student-detail.js';

require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/sidebar.php';
?>

<!-- ========= MAIN ========= -->
<main class="main">
  <header class="topbar">
    <div style="display:flex;align-items:center;gap:12px;">
      <a class="btn btn-ghost btn-sm" href="students.php" title="Back to Roster">&larr; Back</a>
      <div>
        <div class="topbar-title" style="display:flex;align-items:center;gap:8px;">
          <span>Student Academic Record</span>
          <span style="font-size:11px;color:var(--text-muted);">&bull;</span>
          <span id="crumbName" style="color:var(--accent-blue);font-weight:600;">Loading&hellip;</span>
        </div>
        <div class="topbar-subtitle"><?php echo $pageSubtitle; ?></div>
      </div>
    </div>
    <div class="topbar-actions no-print">
      <button class="btn btn-secondary" onclick="openEditStudentDialog()">&#9999;&#65039; Edit Details</button>
      <button class="btn btn-secondary" onclick="window.print()">&#128438;&#65039; Print Summary</button>
      <button class="btn btn-secondary" onclick="toggleCurrentStatus()">&#8646; Override Status</button>
      <button class="btn btn-danger" onclick="confirmDeleteStudent()">&#128465; Remove</button>
    </div>
  </header>

  <div class="page" id="profileMainContent">
    <!-- Student Header Identity Card -->
    <div class="student-profile-banner">
      <div class="profile-avatar" id="studentAvatar">?</div>
      <div class="profile-details">
        <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
          <h1 class="profile-name" id="studentName">&mdash;</h1>
          <span id="studentStatusBadge" class="badge active">&mdash;</span>
        </div>
        <div class="profile-meta-row">
          <span>&#129413; <strong id="studentIdBadge" style="font-family:monospace;color:var(--text-primary);">&mdash;</strong></span>
          <span>&bull;</span>
          <span>&#127891; <span id="studentProgram">&mdash;</span></span>
          <span>&bull;</span>
          <span>&#128197; <span id="studentYear">&mdash;</span></span>
          <span>&bull;</span>
          <span>&#9993;&#65039; <span id="studentEmail">&mdash;</span></span>
        </div>
      </div>
    </div>

    <!-- Academic KPI Metrics Grid -->
    <div class="cgpa-overview">
      <div class="cgpa-stat">
        <div class="cgpa-stat-value" id="statCGPA">&mdash;</div>
        <div class="cgpa-stat-label">Cumulative CGPA</div>
        <div style="font-size:10px;color:var(--text-muted);margin-top:4px;">Across all completed semesters</div>
      </div>
      <div class="cgpa-stat">
        <div class="cgpa-stat-value" id="statGPA">&mdash;</div>
        <div class="cgpa-stat-label">Current Semester GPA</div>
        <div style="font-size:10px;color:var(--text-muted);margin-top:4px;">Latest semester standing</div>
      </div>
      <div class="cgpa-stat">
        <div class="cgpa-stat-value" id="statCredits">&mdash;</div>
        <div class="cgpa-stat-label">Total Credit Hours</div>
        <div style="font-size:10px;color:var(--text-muted);margin-top:4px;">Earned credits to date</div>
      </div>
    </div>

    <!-- Two-Column Section: Semesters & Admin Remarks -->
    <div class="detail-grid">
      <!-- Semester Breakdown List -->
      <div class="panel">
        <div class="panel-header">
          <div class="panel-title">
            <span>&#128218; Semester Breakdown &amp; Courses</span>
          </div>
          <div class="panel-actions no-print">
            <button class="btn btn-primary btn-sm" onclick="addSemester()">+ Add Semester</button>
          </div>
        </div>
        <div class="panel-body" style="padding:16px;">
          <div id="semestersList" style="display:flex;flex-direction:column;gap:12px;"></div>
        </div>
      </div>

      <!-- Admin Notes Panel -->
      <div class="panel">
        <div class="panel-header">
          <div class="panel-title">
            <span>&#128221; Administrative Notes &amp; Remarks</span>
          </div>
          <span id="notesSaved" style="font-size:11px;color:var(--text-muted);font-weight:500;"></span>
        </div>
        <div class="panel-body" style="padding:16px;display:flex;flex-direction:column;gap:10px;">
          <textarea
            class="notes-textarea"
            id="notesArea"
            rows="9"
            placeholder="Document academic committee reviews, formal warnings, appeals, or special commendations&hellip;"
            oninput="autoSaveNotes()"></textarea>
        </div>
      </div>
    </div>

  </div><!-- /page -->
</main>

<!-- ========= ADD SEMESTER CUSTOM DIALOG ========= -->
<div class="dialog-overlay" id="addSemesterDialog">
  <div class="dialog-box" style="max-width:440px;">
    <div class="dialog-title">&#128218; Add New Semester</div>
    <div class="form-group full" style="margin-top:14px;">
      <label class="form-label" for="semLabelInput">Semester Academic Label</label>
      <input class="form-field" id="semLabelInput" type="text" placeholder="e.g. Semester 1 (2025/2026)" />
    </div>
    <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px;">
      <button class="btn btn-ghost" onclick="closeAddSemesterDialog()">Cancel</button>
      <button class="btn btn-primary" onclick="confirmAddSemester()">&#10022; Add Semester</button>
    </div>
  </div>
</div>

<!-- ========= EDIT STUDENT DETAILS DIALOG ========= -->
<div class="dialog-overlay" id="editStudentDialog">
  <div class="dialog-box">
    <div class="dialog-title">&#9999;&#65039; Edit Student Details</div>
    <div class="form-grid">
      <div class="form-group">
        <label class="form-label" for="editId">Student ID</label>
        <input class="form-field" id="editId" type="text" disabled style="opacity:0.6;cursor:not-allowed;" />
      </div>
      <div class="form-group">
        <label class="form-label" for="editYear">Academic Year</label>
        <select class="form-field" id="editYear">
          <option value="1">Year 1</option>
          <option value="2">Year 2</option>
          <option value="3">Year 3</option>
          <option value="4">Year 4</option>
        </select>
      </div>
      <div class="form-group full">
        <label class="form-label" for="editName">Full Name</label>
        <input class="form-field" id="editName" type="text" />
      </div>
      <div class="form-group full">
        <label class="form-label" for="editProgram">Programme</label>
        <select class="form-field" id="editProgram"
                onchange="document.getElementById('editProgramCustom').style.display=this.value==='__other__'?'':'none';">
          <optgroup label="Doctor of Philosophy (PhD - Research)">
            <option value="PhD (Public Administration)">PhD (Public Administration)</option>
            <option value="PhD (Public/Development Management)">PhD (Public/Development Management)</option>
            <option value="PhD (Political Science)">PhD (Political Science)</option>
            <option value="PhD (Strategic Studies)">PhD (Strategic Studies)</option>
            <option value="PhD (International Business/Studies)">PhD (International Business/Studies)</option>
            <option value="PhD (Law)">PhD (Law)</option>
            <option value="PhD (Sociology)">PhD (Sociology)</option>
            <option value="PhD (Nationhood Studies)">PhD (Nationhood Studies)</option>
            <option value="PhD (Environmental Studies)">PhD (Environmental Studies)</option>
            <option value="PhD (Tourism and Hospitality Management)">PhD (Tourism and Hospitality Management)</option>
            <option value="PhD (Islamic Jerusalem Studies)">PhD (Islamic Jerusalem Studies)</option>
            <option value="PhD (Border Management)">PhD (Border Management)</option>
            <option value="PhD (Peace and Conflict Transformation)">PhD (Peace and Conflict Transformation)</option>
          </optgroup>
          <optgroup label="Master's Programmes">
            <option value="Master of Public Management (MPM)">Master of Public Management (MPM)</option>
            <option value="Master of Law">Master of Law</option>
            <option value="Master of Human Resource Law">Master of Human Resource Law</option>
            <option value="Master of Commercial Law">Master of Commercial Law</option>
            <option value="Master of Corporate Law">Master of Corporate Law</option>
            <option value="Master of Science (Strategic Studies)">Master of Science (Strategic Studies)</option>
            <option value="Master of Science (International Business)">Master of Science (International Business)</option>
            <option value="Master of Arts (Political Science)">Master of Arts (Political Science)</option>
            <option value="Master of Arts (International Relations)">Master of Arts (International Relations)</option>
            <option value="Master of Arts (Sociology)">Master of Arts (Sociology)</option>
            <option value="Master of Arts in Nationhood Studies">Master of Arts in Nationhood Studies</option>
            <option value="Master in International Political Economy">Master in International Political Economy</option>
            <option value="Master of Science (Tourism and Hospitality Management)">Master of Science (Tourism and Hospitality Management)</option>
            <option value="Master of Tourism Management">Master of Tourism Management</option>
          </optgroup>
          <option value="__other__">Other (specify…)</option>
        </select>
        <input class="form-field" id="editProgramCustom" type="text"
               placeholder="Type programme name"
               style="margin-top:8px;display:none;" />
      </div>
      <div class="form-group full">
        <label class="form-label" for="editEmail">Email</label>
        <input class="form-field" id="editEmail" type="email" />
      </div>
    </div>
    <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:22px;">
      <button class="btn btn-ghost" onclick="closeEditStudentDialog()">Cancel</button>
      <button class="btn btn-primary" onclick="confirmEditStudent()">&#128190; Save Changes</button>
    </div>
  </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
