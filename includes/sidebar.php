<?php
/**
 * Shared Sidebar Include
 * GSGSG Scholarship Tracking System
 */
if (!isset($currentPage)) $currentPage = 'dashboard';
?>
<!-- ========= SIDEBAR ========= -->
<aside class="sidebar">
  <div class="sidebar-brand">
    <a class="brand-logo" href="dashboard.php">
      <div class="brand-icon">&#127891;</div>
      <div class="brand-text">
        <span class="brand-name">GSGSG-SMART</span>
        <span class="brand-sub">Scholarship System</span>
      </div>
    </a>
  </div>

  <nav class="sidebar-nav">
    <span class="nav-section-label">Main</span>
    <a class="nav-item <?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>" href="dashboard.php">
      <span class="nav-icon">&#128202;</span> Dashboard
    </a>
    <a class="nav-item <?php echo $currentPage === 'students' ? 'active' : ''; ?>" href="students.php">
      <span class="nav-icon">&#128101;</span> Student Roster
    </a>
    <a class="nav-item <?php echo $currentPage === 'reports' ? 'active' : ''; ?>" href="reports.php">
      <span class="nav-icon">&#128203;</span> Reports &amp; Analytics
    </a>

    <span class="nav-section-label" style="margin-top:8px;">Management</span>
    <a class="nav-item" href="javascript:void(0)" onclick="openAddStudentDialog()">
      <span class="nav-icon">&#10133;</span> Add Student
    </a>
    <a class="nav-item" href="javascript:void(0)" onclick="openImportDialog()">
      <span class="nav-icon">&#128229;</span> Import CSV
    </a>
    <a class="nav-item" href="javascript:void(0)" onclick="exportCSV()">
      <span class="nav-icon">&#128228;</span> Export Report
    </a>

    <span class="nav-section-label" style="margin-top:8px;">Alerts</span>
    <a class="nav-item" href="students.php?filter=At+Risk" id="nav-alerts-link">
      <span class="nav-icon">&#9888;&#65039;</span> At Risk Students
      <span class="nav-badge" id="atRiskNavBadge" style="display:none;">0</span>
    </a>
  </nav>

  <div class="sidebar-footer">
    <div class="admin-card">
      <div class="admin-avatar">AD</div>
      <div class="admin-info">
        <div class="admin-name">Admin Panel</div>
        <div class="admin-role">Academic Registry (MySQL)</div>
      </div>
    </div>
  </div>
</aside>
