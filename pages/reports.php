<?php
/**
 * Formal Academic Committee Scholarship Report View
 * GSGSG Scholarship Tracking System
 */
$pageTitle = 'Official Scholarship Performance &amp; Compliance Report &mdash; GSGSG SMART';
$pageSubtitle = 'Institutional Standing, Retention Metrics &amp; Committee Review';
$currentPage = 'reports';
$pageScript = 'js/reports.js';

require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/sidebar.php';
?>

<!-- ========= MAIN ========= -->
<main class="main">
  <!-- Top Control Bar (Hidden in Print) -->
  <header class="topbar no-print">
    <div>
      <div class="topbar-title">Official Scholarship Committee Report</div>
      <div class="topbar-subtitle"><?php echo $pageSubtitle; ?></div>
    </div>
    <div class="topbar-actions">
      <button class="btn btn-secondary" onclick="exportCSV()">&#128228; Export CSV</button>
      <button class="btn btn-primary" onclick="window.print()">&#128438;&#65039; Print / Export PDF</button>
    </div>
  </header>

  <div class="page">
    <!-- Interactive Report Scope Controls (Hidden in Print) -->
    <div class="report-controls-panel no-print">
      <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;justify-content:space-between;">
        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
          <span style="font-size:12px;font-weight:600;color:var(--text-muted);text-transform:uppercase;">Report Scope:</span>
          <div class="filter-tabs">
            <button class="filter-tab report-type-btn active" onclick="setReportType('all', this)">All Recipients</button>
            <button class="filter-tab report-type-btn" onclick="setReportType('audit', this)">Compliance Audit (At Risk / Revoked)</button>
            <button class="filter-tab report-type-btn" onclick="setReportType('merit', this)">Dean&rsquo;s Honor Roll (CGPA &ge; 3.85)</button>
          </div>
        </div>

        <div style="display:flex;align-items:center;gap:10px;">
          <label for="reportProgramFilter" style="font-size:12px;color:var(--text-muted);font-weight:500;">Filter Program:</label>
          <select id="reportProgramFilter" class="form-field" style="width:auto;padding:5px 12px;font-size:12px;" onchange="onProgramFilterChange(this.value)">
            <option value="all">All Academic Programs</option>
          </select>
        </div>
      </div>
    </div>

    <!-- ========================================================
         FORMAL COMMITTEE REPORT DOCUMENT SHEET (A4 Printable)
         ======================================================== -->
    <div class="report-document-sheet" id="reportDocument">
      
      <!-- 1. Formal University Header / Letterhead -->
      <div class="report-letterhead">
        <div class="letterhead-crest-row">
          <div class="letterhead-crest">&#127891;</div>
          <div class="letterhead-institution">
            <div class="inst-uni">UNIVERSITI UTARA MALAYSIA</div>
            <div class="inst-faculty">GHAZALI SHAFIE GRADUATE SCHOOL OF GOVERNMENT (GSGSG)</div>
            <div class="inst-dept">Academic Registry &bull; Office of Academic Affairs &amp; Student Financial Aid</div>
          </div>
        </div>

        <div class="letterhead-divider"></div>

        <div class="letterhead-doc-title">OFFICIAL SCHOLARSHIP PERFORMANCE &amp; COMPLIANCE AUDIT REPORT</div>
        <div class="letterhead-session">ACADEMIC SESSION 2025/2026 &mdash; SEMESTER II</div>

        <!-- Document Metadata Grid -->
        <table class="report-meta-table">
          <tr>
            <td class="meta-lbl">Document Ref:</td>
            <td class="meta-val" id="reportRefNo">GSGSG/SCH/2026-S2/001</td>
            <td class="meta-lbl">Classification:</td>
            <td class="meta-val"><span class="badge-confidential">OFFICIAL &bull; ACADEMIC BOARD</span></td>
          </tr>
          <tr>
            <td class="meta-lbl">Date of Generation:</td>
            <td class="meta-val" id="reportGenDate">&mdash;</td>
            <td class="meta-lbl">Review Board:</td>
            <td class="meta-val">Scholarship Standing &amp; Retention Committee</td>
          </tr>
        </table>
      </div>

      <!-- 2. Executive Summary & Narrative Block -->
      <div class="report-section">
        <div class="report-section-heading">&#128220; Executive Summary &amp; Cohort Standing</div>
        
        <!-- KPI Strip -->
        <div class="report-kpi-strip">
          <div class="report-kpi-box">
            <div class="kpi-num" id="kpiTotal">0</div>
            <div class="kpi-lbl">Total Enrolled</div>
          </div>
          <div class="report-kpi-box green">
            <div class="kpi-num" id="kpiActive">0</div>
            <div class="kpi-lbl">Good Standing (Active)</div>
          </div>
          <div class="report-kpi-box yellow">
            <div class="kpi-num" id="kpiAtRisk">0</div>
            <div class="kpi-lbl">At-Risk Watchlist</div>
          </div>
          <div class="report-kpi-box red">
            <div class="kpi-num" id="kpiRevoked">0</div>
            <div class="kpi-lbl">Revocation Review</div>
          </div>
          <div class="report-kpi-box blue">
            <div class="kpi-num" id="kpiMeanCGPA">0.00</div>
            <div class="kpi-lbl">Cohort Mean CGPA</div>
          </div>
        </div>

        <div class="report-narrative-card">
          <div id="reportNarrative">Loading cohort audit narrative&hellip;</div>
        </div>
      </div>

      <!-- 3. Section I: Program Distribution Analysis -->
      <div class="report-section">
        <div class="report-section-heading">&#128202; Section I: Academic Program Performance &amp; Retention Analysis</div>
        <table class="formal-report-table">
          <thead>
            <tr>
              <th style="width:36px;text-align:center;">#</th>
              <th>Academic Program</th>
              <th style="text-align:center;width:60px;">Total</th>
              <th style="text-align:center;width:70px;">Active</th>
              <th style="text-align:center;width:70px;">At Risk</th>
              <th style="text-align:center;width:75px;">Revoked</th>
              <th style="text-align:center;width:80px;">Mean CGPA</th>
              <th style="text-align:center;width:90px;">Retention %</th>
            </tr>
          </thead>
          <tbody id="reportProgramTableBody"></tbody>
        </table>
      </div>

      <!-- 4. Section II: Individual Candidate Roster / Audit Table -->
      <div class="report-section">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
          <div class="report-section-heading" id="rosterSectionTitle" style="margin-bottom:0;">
            &#128101; Section II: Scholarship Candidate Roster &amp; Academic Status
          </div>
          <span id="reportRosterCount" class="report-badge-count">0 candidates</span>
        </div>

        <table class="formal-report-table">
          <thead>
            <tr>
              <th style="width:36px;text-align:center;">#</th>
              <th style="width:110px;">Student ID</th>
              <th>Full Name</th>
              <th>Program</th>
              <th style="text-align:center;width:55px;">Year</th>
              <th style="text-align:center;width:65px;">Sem GPA</th>
              <th style="text-align:center;width:65px;">CGPA</th>
              <th style="text-align:center;width:95px;">Status</th>
              <th>Academic Notes &amp; Action Plan</th>
            </tr>
          </thead>
          <tbody id="reportRosterTableBody"></tbody>
        </table>
      </div>

      <!-- 5. Section III: Committee Endorsement & Signatures (Print-Ready) -->
      <div class="report-section page-break-inside-avoid">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
          <div class="report-section-heading" style="margin-bottom:0;">&#9997;&#65039; Section III: Verification &amp; Committee Endorsement</div>
          <button class="btn btn-secondary btn-sm no-print" onclick="openEditSignatoriesDialog()">&#9999;&#65039; Edit Signatories</button>
        </div>
        
        <div class="report-signatures-grid">
          <!-- Signature Box 1 -->
          <div class="signature-card">
            <div class="sig-title" id="sig1Title">PREPARED BY:</div>
            <div class="sig-space">
              <div class="sig-simulated-line"></div>
            </div>
            <div class="sig-name" id="sig1Name">MOHD FAHMI BIN SAAD</div>
            <div class="sig-role" id="sig1Role">Senior Assistant Registrar</div>
            <div class="sig-dept" id="sig1Dept">Academic Registry &amp; Scholarship Unit</div>
            <div class="sig-date">Date: ________________________</div>
          </div>

          <!-- Signature Box 2 -->
          <div class="signature-card">
            <div class="sig-title" id="sig2Title">REVIEWED &amp; VERIFIED BY:</div>
            <div class="sig-space">
              <div class="sig-simulated-line"></div>
            </div>
            <div class="sig-name" id="sig2Name">PROF. DR. NORLIDA BINTI HASHIM</div>
            <div class="sig-role" id="sig2Role">Chairperson, Scholarship Committee</div>
            <div class="sig-dept" id="sig2Dept">Office of the Deputy Vice-Chancellor (Academic)</div>
            <div class="sig-date">Date: ________________________</div>
          </div>

          <!-- Official Stamp Box -->
          <div class="stamp-card">
            <div class="stamp-title">OFFICIAL BOARD ENDORSEMENT</div>
            <div class="stamp-seal-circle">
              <span>GSGSG</span>
              <small>OFFICIAL SEAL</small>
            </div>
            <div class="stamp-note">Document valid only with authorized stamp &amp; signatures</div>
          </div>
        </div>
      </div>

    </div><!-- /report-document-sheet -->

  </div><!-- /page -->
</main>

<!-- ========= EDIT SIGNATORIES DIALOG ========= -->
<div class="dialog-overlay" id="editSignatoriesDialog">
  <div class="dialog-box" style="max-width:700px;width:min(700px, 95vw);">
    <div class="dialog-title">&#9999;&#65039; Edit Report Signatories</div>
    
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-top:16px;">
      <!-- Signatory 1 -->
      <div style="background:var(--bg-base);padding:14px;border-radius:var(--radius-md);border:1px solid var(--border);">
        <div style="font-size:12px;font-weight:700;color:var(--accent-blue);margin-bottom:10px;">Signatory 1 (Preparer)</div>
        <div class="form-group" style="margin-bottom:8px;">
          <label class="form-label" for="inputSig1Title">Header Label</label>
          <input class="form-field" id="inputSig1Title" type="text" placeholder="e.g. PREPARED BY:" />
        </div>
        <div class="form-group" style="margin-bottom:8px;">
          <label class="form-label" for="inputSig1Name">Full Name</label>
          <input class="form-field" id="inputSig1Name" type="text" placeholder="e.g. MOHD FAHMI BIN SAAD" />
        </div>
        <div class="form-group" style="margin-bottom:8px;">
          <label class="form-label" for="inputSig1Role">Title / Designation</label>
          <input class="form-field" id="inputSig1Role" type="text" placeholder="e.g. Senior Assistant Registrar" />
        </div>
        <div class="form-group">
          <label class="form-label" for="inputSig1Dept">Department / Office</label>
          <input class="form-field" id="inputSig1Dept" type="text" placeholder="e.g. Academic Registry & Scholarship Unit" />
        </div>
      </div>

      <!-- Signatory 2 -->
      <div style="background:var(--bg-base);padding:14px;border-radius:var(--radius-md);border:1px solid var(--border);">
        <div style="font-size:12px;font-weight:700;color:var(--accent-violet);margin-bottom:10px;">Signatory 2 (Reviewer / Chair)</div>
        <div class="form-group" style="margin-bottom:8px;">
          <label class="form-label" for="inputSig2Title">Header Label</label>
          <input class="form-field" id="inputSig2Title" type="text" placeholder="e.g. REVIEWED & VERIFIED BY:" />
        </div>
        <div class="form-group" style="margin-bottom:8px;">
          <label class="form-label" for="inputSig2Name">Full Name</label>
          <input class="form-field" id="inputSig2Name" type="text" placeholder="e.g. PROF. DR. NORLIDA BINTI HASHIM" />
        </div>
        <div class="form-group" style="margin-bottom:8px;">
          <label class="form-label" for="inputSig2Role">Title / Designation</label>
          <input class="form-field" id="inputSig2Role" type="text" placeholder="e.g. Chairperson, Scholarship Committee" />
        </div>
        <div class="form-group">
          <label class="form-label" for="inputSig2Dept">Department / Office</label>
          <input class="form-field" id="inputSig2Dept" type="text" placeholder="e.g. Office of the Deputy Vice-Chancellor" />
        </div>
      </div>
    </div>

    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:22px;">
      <button class="btn btn-ghost" onclick="resetSignatories()" style="font-size:12px;">&#8634; Reset to Default</button>
      <div style="display:flex;gap:10px;">
        <button class="btn btn-ghost" onclick="closeEditSignatoriesDialog()">Cancel</button>
        <button class="btn btn-primary" onclick="saveSignatories()">&#128190; Save Signatories</button>
      </div>
    </div>
  </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
