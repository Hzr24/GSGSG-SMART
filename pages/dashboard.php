<?php
/**
 * Executive Dashboard
 * GSGSG Scholarship Tracking System
 */
$pageTitle = 'Dashboard &mdash; GSGSG SMART';
$pageSubtitle = 'Academic Year 2025/2026 &mdash; Semester II';
$currentPage = 'dashboard';
$pageScript = 'js/dashboard.js';

require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/sidebar.php';
?>

<!-- ========= MAIN ========= -->
<main class="main">
  <header class="topbar">
    <div>
      <div class="topbar-title">Scholarship Dashboard</div>
      <div class="topbar-subtitle"><?php echo $pageSubtitle; ?></div>
    </div>
    <div class="topbar-actions">
      <button class="btn btn-secondary" onclick="openImportDialog()">&#128229; Import CSV</button>
      <button class="btn btn-secondary" onclick="exportCSV()">&#128228; Export Report</button>
      <button class="btn btn-primary" onclick="openAddStudentDialog()">&#10022; Add Student</button>
    </div>
  </header>

  <div class="page">
    <!-- Metric Cards -->
    <div class="metrics-grid">
      <div class="metric-card blue">
        <div class="metric-top">
          <div class="metric-icon">&#127891;</div>
          <span class="metric-trend trend-neu" id="totalTrend">&mdash;</span>
        </div>
        <div class="metric-value" id="metricTotal">0</div>
        <div class="metric-label">Total Students</div>
        <div class="metric-sub">Enrolled in scholarship roster</div>
      </div>

      <div class="metric-card green">
        <div class="metric-top">
          <div class="metric-icon">&#9989;</div>
          <span class="metric-trend trend-up" id="activeTrend">&mdash;</span>
        </div>
        <div class="metric-value" id="metricActive">0</div>
        <div class="metric-label">Active Scholarships</div>
        <div class="metric-sub">CGPA &ge; 3.75 (Good standing)</div>
      </div>

      <div class="metric-card yellow">
        <div class="metric-top">
          <div class="metric-icon">&#9888;&#65039;</div>
          <span class="metric-trend trend-neu" id="atRiskTrend">&mdash;</span>
        </div>
        <div class="metric-value" id="metricAtRisk">0</div>
        <div class="metric-label">At Risk Candidates</div>
        <div class="metric-sub">3.50 &le; CGPA &lt; 3.75 (Watchlist)</div>
      </div>

      <div class="metric-card red">
        <div class="metric-top">
          <div class="metric-icon">&#10060;</div>
          <span class="metric-trend trend-down" id="revokedTrend">&mdash;</span>
        </div>
        <div class="metric-value" id="metricRevoked">0</div>
        <div class="metric-label">Revoked / Suspended</div>
        <div class="metric-sub">CGPA &lt; 3.50 (Action required)</div>
      </div>
    </div>

    <!-- Two Column Layout: Watchlist & Program Distribution -->
    <div class="dashboard-grid">
      <!-- Priority Watchlist -->
      <div class="panel">
        <div class="panel-header">
          <div class="panel-title">
            <span>&#9888;&#65039; Priority Watchlist</span>
            <span style="font-size:11px;color:var(--text-muted);font-weight:400;">(At Risk &amp; Revoked cases requiring review)</span>
          </div>
          <div class="panel-actions">
            <a class="btn btn-ghost btn-sm" href="students.php?filter=At+Risk">View Roster &rarr;</a>
          </div>
        </div>
        <div class="panel-body" style="padding:16px;">
          <div id="watchlistContainer" class="watchlist-list"></div>
          <div id="watchlistEmpty" class="empty-state" style="display:none;padding:30px 10px;">
            <div class="empty-icon">&#9989;</div>
            <div class="empty-msg">All students in good standing</div>
            <div class="empty-sub">No students currently flagged as At Risk or Revoked.</div>
          </div>
        </div>
      </div>

      <!-- Program Breakdown -->
      <div class="panel">
        <div class="panel-header">
          <div class="panel-title">
            <span>&#128202; Performance by Academic Program</span>
          </div>
          <div class="panel-actions">
            <a class="btn btn-ghost btn-sm" href="reports.php">Detailed Reports &rarr;</a>
          </div>
        </div>
        <div class="panel-body" style="padding:16px;">
          <div id="programDistContainer" class="prog-dist-grid"></div>
        </div>
      </div>
    </div>

  </div><!-- /page -->
</main>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
