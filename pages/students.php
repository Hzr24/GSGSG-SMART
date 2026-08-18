<?php
/**
 * Student Roster View
 * GSGSG Scholarship Tracking System
 */
$pageTitle = 'Student Roster &mdash; GSGSG-SMART';
$pageSubtitle = 'Academic Registry &bull; Scholarship Enrollees';
$currentPage = 'students';
$pageScript = 'js/students.js';

require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/sidebar.php';
?>

<!-- ========= MAIN ========= -->
<main class="main">
  <header class="topbar">
    <div>
      <div class="topbar-title">Student Roster</div>
      <div class="topbar-subtitle"><?php echo $pageSubtitle; ?></div>
    </div>
    <div class="topbar-actions">
      <div class="search-wrap">
        <span class="search-icon">&#128269;</span>
        <input class="search-input" id="searchInput" type="text" placeholder="Search name, ID, program&hellip;" autocomplete="off" />
      </div>
      <button class="btn btn-secondary" onclick="openImportDialog()">&#128229; Import</button>
      <button class="btn btn-secondary" onclick="exportCSV()">&#128228; Export</button>
      <button class="btn btn-primary" onclick="openAddStudentDialog()">&#10022; Add Student</button>
    </div>
  </header>

  <div class="page">
    <!-- Student Table Panel -->
    <div class="panel" id="studentPanel">
      <div class="panel-header">
        <div class="panel-title">
          <span>&#128101; Active Roster</span>
          <span style="font-size:11px;font-weight:500;color:var(--text-muted);background:var(--bg-base);padding:2px 8px;border-radius:99px;border:1px solid var(--border);" id="rowCount">0 students</span>
        </div>
        <div class="panel-actions">
          <select id="programFilter" class="form-field" style="width:auto;padding:5px 10px;font-size:12px;" onchange="setProgramFilter(this.value)">
            <option value="All">All Programs</option>
          </select>

          <div class="filter-tabs" role="tablist">
            <button class="filter-tab active all" data-filter="All" onclick="setFilter('All',this)">All</button>
            <button class="filter-tab" data-filter="Active" onclick="setFilter('Active',this)">Active</button>
            <button class="filter-tab" data-filter="At Risk" onclick="setFilter('At Risk',this)">At Risk</button>
            <button class="filter-tab" data-filter="Revoked" onclick="setFilter('Revoked',this)">Revoked</button>
          </div>
          <button class="btn btn-ghost btn-sm" onclick="resetSort()">&#8693; Reset Sort</button>
        </div>
      </div>

      <div class="table-wrap">
        <table id="studentTable">
          <thead>
            <tr>
              <th class="sortable" onclick="sortBy('id')" id="th-id">Student ID &#8597;</th>
              <th class="sortable" onclick="sortBy('name')" id="th-name">Full Name &#8597;</th>
              <th class="sortable" onclick="sortBy('program')" id="th-program">Program &#8597;</th>
              <th class="sortable" onclick="sortBy('gpa')" id="th-gpa">Current GPA &#8597;</th>
              <th class="sortable" onclick="sortBy('cgpa')" id="th-cgpa">CGPA &#8597;</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="tableBody"></tbody>
        </table>
        <div id="emptyState" class="empty-state" style="display:none;">
          <div class="empty-icon">&#128269;</div>
          <div class="empty-msg">No student records found</div>
          <div class="empty-sub">Try adjusting your search criteria or filter tabs.</div>
        </div>
      </div>

      <div class="pagination" id="paginationBar">
        <span class="pag-info" id="pagInfo">Showing 0&ndash;0 of 0</span>
        <div class="pag-controls" id="pagControls"></div>
      </div>
    </div>

  </div><!-- /page -->
</main>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
