/**
 * Formal Academic Committee Report Logic
 * GSGSG Scholarship Tracking System
 */

let allStudents = [];
let reportType = 'all'; // 'all', 'audit', 'merit'
let selectedProgram = 'all';

const DEFAULT_SIGNATORIES = {
  sig1Title: 'PREPARED BY:',
  sig1Name: 'MOHD FAHMI BIN SAAD',
  sig1Role: 'Senior Assistant Registrar',
  sig1Dept: 'Academic Registry & Scholarship Unit',
  sig2Title: 'REVIEWED & VERIFIED BY:',
  sig2Name: 'PROF. DR. NORLIDA BINTI HASHIM',
  sig2Role: 'Chairperson, Scholarship Committee',
  sig2Dept: 'Office of the Deputy Vice-Chancellor (Academic)'
};

async function initReports() {
  try {
    const res = await API.getStudents();
    allStudents = res.data || [];
    populateProgramDropdown();
    updateLiveMetadata();
    loadSignatories();
    renderFullReport();
  } catch (err) {
    showToast('Failed to load report data: ' + err.message, 'error');
  }
}

function updateLiveMetadata() {
  const now = new Date();
  const dateStr = now.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
  const timeStr = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
  
  const dateEl = document.getElementById('reportGenDate');
  if (dateEl) dateEl.textContent = `${dateStr}, ${timeStr}`;

  const refEl = document.getElementById('reportRefNo');
  if (refEl) refEl.textContent = `GSGSG/SCH/${now.getFullYear()}-S2/${String(now.getMonth() + 1).padStart(2, '0')}${String(now.getDate()).padStart(2, '0')}`;
}

function populateProgramDropdown() {
  const select = document.getElementById('reportProgramFilter');
  if (!select) return;
  const programs = Array.from(new Set(allStudents.map(s => s.program))).sort();
  select.innerHTML = '<option value="all">All Academic Programs</option>' +
    programs.map(p => `<option value="${escHtml(p)}">${escHtml(p)}</option>`).join('');
}

function setReportType(type, btn) {
  reportType = type;
  document.querySelectorAll('.report-type-btn').forEach(b => b.classList.remove('active'));
  if (btn) btn.classList.add('active');
  renderFullReport();
}

function onProgramFilterChange(val) {
  selectedProgram = val;
  renderFullReport();
}

function getFilteredReportList() {
  let list = [...allStudents];
  if (selectedProgram !== 'all') {
    list = list.filter(s => s.program === selectedProgram);
  }
  if (reportType === 'audit') {
    list = list.filter(s => s.status === 'At Risk' || s.status === 'Revoked');
  } else if (reportType === 'merit') {
    list = list.filter(s => (s.cgpa || 0) >= 3.85);
  }
  return list;
}

function renderFullReport() {
  const filtered = getFilteredReportList();
  const totalEnrolled = allStudents.length;

  const activeCount = allStudents.filter(s => s.status === 'Active').length;
  const atRiskCount = allStudents.filter(s => s.status === 'At Risk').length;
  const revokedCount = allStudents.filter(s => s.status === 'Revoked').length;
  const activeRate = totalEnrolled > 0 ? Math.round((activeCount / totalEnrolled) * 100) : 0;
  
  const cgpaSum = allStudents.reduce((acc, s) => acc + (s.cgpa || 0), 0);
  const meanCGPA = totalEnrolled > 0 ? (cgpaSum / totalEnrolled).toFixed(2) : '0.00';

  // 1. Render Executive KPI Bar
  document.getElementById('kpiTotal').textContent = totalEnrolled;
  document.getElementById('kpiActive').textContent = `${activeCount} (${activeRate}%)`;
  document.getElementById('kpiAtRisk').textContent = atRiskCount;
  document.getElementById('kpiRevoked').textContent = revokedCount;
  document.getElementById('kpiMeanCGPA').textContent = meanCGPA;

  // 2. Render Executive Summary Narrative
  const narrativeEl = document.getElementById('reportNarrative');
  if (narrativeEl) {
    let narrativeText = `During the Academic Year 2025/2026 (Semester II), a total of <strong>${totalEnrolled} recipients</strong> are actively registered under the GSGSG Scholarship Scheme. Overall cohort performance remains steady with a mean cumulative grade point average of <strong>${meanCGPA}</strong>. `;
    narrativeText += `<strong>${activeCount} recipients (${activeRate}%)</strong> successfully maintained compliance with the First-Class/Honor retention criteria (CGPA &ge; 3.75). `;
    
    if (atRiskCount > 0) {
      narrativeText += `Currently, <strong>${atRiskCount} candidate(s)</strong> are placed on <em>At-Risk Academic Watch</em> (3.50 &le; CGPA &lt; 3.75) requiring faculty advisory intervention. `;
    } else {
      narrativeText += `Zero recipients are placed on At-Risk status. `;
    }

    if (revokedCount > 0) {
      narrativeText += `A total of <strong>${revokedCount} candidate(s)</strong> have breached minimum retention thresholds (CGPA &lt; 3.50) and are formally submitted to the Board for scholarship suspension or revocation review.`;
    } else {
      narrativeText += `No scholarship revocations or suspensions are recorded for this review cycle.`;
    }
    narrativeEl.innerHTML = narrativeText;
  }

  // 3. Render Program Distribution Table
  const progMap = {};
  allStudents.forEach(s => {
    if (!progMap[s.program]) {
      progMap[s.program] = { total: 0, active: 0, atRisk: 0, revoked: 0, cgpaSum: 0 };
    }
    progMap[s.program].total++;
    progMap[s.program].cgpaSum += (s.cgpa || 0);
    if (s.status === 'Active') progMap[s.program].active++;
    else if (s.status === 'At Risk') progMap[s.program].atRisk++;
    else if (s.status === 'Revoked') progMap[s.program].revoked++;
  });

  const progBody = document.getElementById('reportProgramTableBody');
  if (progBody) {
    progBody.innerHTML = Object.keys(progMap).sort().map((progName, idx) => {
      const st = progMap[progName];
      const progMean = (st.cgpaSum / st.total).toFixed(2);
      const progActivePct = Math.round((st.active / st.total) * 100);
      return `
        <tr>
          <td style="text-align:center;color:var(--text-muted);font-size:11px;">${idx + 1}</td>
          <td style="font-weight:600;">${escHtml(progName)}</td>
          <td style="text-align:center;font-weight:700;">${st.total}</td>
          <td style="text-align:center;color:var(--status-active);font-weight:600;">${st.active}</td>
          <td style="text-align:center;color:var(--status-atrisk);font-weight:600;">${st.atRisk}</td>
          <td style="text-align:center;color:var(--status-revoked);font-weight:600;">${st.revoked}</td>
          <td style="text-align:center;font-weight:700;">${progMean}</td>
          <td style="text-align:center;font-weight:600;color:${progActivePct >= 70 ? 'var(--status-active)' : 'var(--status-atrisk)'};">${progActivePct}%</td>
        </tr>
      `;
    }).join('');
  }

  // 4. Render Filtered Candidate Table
  const rosterBody = document.getElementById('reportRosterTableBody');
  const rosterTitle = document.getElementById('rosterSectionTitle');
  const rosterCountBadge = document.getElementById('reportRosterCount');

  if (rosterTitle) {
    if (reportType === 'audit') rosterTitle.innerHTML = '&#9888;&#65039; Section II: Compliance Watchlist &amp; Actionable Audit';
    else if (reportType === 'merit') rosterTitle.innerHTML = '&#127942; Section II: High Achievers &amp; Dean&rsquo;s Honor Roll (CGPA &ge; 3.85)';
    else rosterTitle.innerHTML = '&#128101; Section II: Individual Scholarship Candidate Roster';
  }

  if (rosterCountBadge) {
    rosterCountBadge.textContent = `${filtered.length} candidate${filtered.length !== 1 ? 's' : ''}`;
  }

  if (rosterBody) {
    if (filtered.length === 0) {
      rosterBody.innerHTML = `
        <tr>
          <td colspan="8" style="text-align:center;padding:30px;color:var(--text-muted);">
            No candidate records found matching selected criteria.
          </td>
        </tr>
      `;
    } else {
      rosterBody.innerHTML = filtered.map((s, idx) => {
        const sc = { 'Active': 'active', 'At Risk': 'atrisk', 'Revoked': 'revoked' }[s.status] || 'active';
        return `
          <tr>
            <td style="text-align:center;color:var(--text-muted);font-size:11px;">${idx + 1}</td>
            <td style="font-family:monospace;font-weight:600;font-size:12px;">${escHtml(s.id)}</td>
            <td style="font-weight:600;">
              <a href="student-detail.php?id=${encodeURIComponent(s.id)}" class="report-student-link" title="Open student profile">${escHtml(s.name)}</a>
            </td>
            <td style="font-size:12px;color:var(--text-secondary);">${escHtml(s.program)}</td>
            <td style="text-align:center;">Yr ${s.year}</td>
            <td style="text-align:center;font-weight:600;">${(s.gpa || 0).toFixed(2)}</td>
            <td style="text-align:center;font-weight:700;color:${gpaTextColor(s.cgpa || 0)};">${(s.cgpa || 0).toFixed(2)}</td>
            <td style="text-align:center;">
              <span class="badge ${sc}"><span class="badge-dot ${s.status === 'At Risk' ? 'pulse' : ''}"></span> ${escHtml(s.status)}</span>
            </td>
            <td style="font-size:11px;color:var(--text-muted);max-width:240px;line-height:1.4;">
              ${escHtml(s.notes || 'Good standing. Regular progression.')}
            </td>
          </tr>
        `;
      }).join('');
    }
  }
}

// Signatories Management (Section III)
function getSavedSignatories() {
  try {
    const saved = localStorage.getItem('scholartrack_report_signatories');
    if (saved) return JSON.parse(saved);
  } catch (e) {
    console.error('Failed to load saved signatories:', e);
  }
  return DEFAULT_SIGNATORIES;
}

function loadSignatories() {
  const sigs = getSavedSignatories();
  renderSignatories(sigs);
}

function renderSignatories(sigs) {
  const s1Title = document.getElementById('sig1Title');
  const s1Name  = document.getElementById('sig1Name');
  const s1Role  = document.getElementById('sig1Role');
  const s1Dept  = document.getElementById('sig1Dept');

  const s2Title = document.getElementById('sig2Title');
  const s2Name  = document.getElementById('sig2Name');
  const s2Role  = document.getElementById('sig2Role');
  const s2Dept  = document.getElementById('sig2Dept');

  if (s1Title) s1Title.textContent = sigs.sig1Title || DEFAULT_SIGNATORIES.sig1Title;
  if (s1Name)  s1Name.textContent  = sigs.sig1Name  || DEFAULT_SIGNATORIES.sig1Name;
  if (s1Role)  s1Role.textContent  = sigs.sig1Role  || DEFAULT_SIGNATORIES.sig1Role;
  if (s1Dept)  s1Dept.textContent  = sigs.sig1Dept  || DEFAULT_SIGNATORIES.sig1Dept;

  if (s2Title) s2Title.textContent = sigs.sig2Title || DEFAULT_SIGNATORIES.sig2Title;
  if (s2Name)  s2Name.textContent  = sigs.sig2Name  || DEFAULT_SIGNATORIES.sig2Name;
  if (s2Role)  s2Role.textContent  = sigs.sig2Role  || DEFAULT_SIGNATORIES.sig2Role;
  if (s2Dept)  s2Dept.textContent  = sigs.sig2Dept  || DEFAULT_SIGNATORIES.sig2Dept;
}

function openEditSignatoriesDialog() {
  const sigs = getSavedSignatories();

  document.getElementById('inputSig1Title').value = sigs.sig1Title || DEFAULT_SIGNATORIES.sig1Title;
  document.getElementById('inputSig1Name').value  = sigs.sig1Name  || DEFAULT_SIGNATORIES.sig1Name;
  document.getElementById('inputSig1Role').value  = sigs.sig1Role  || DEFAULT_SIGNATORIES.sig1Role;
  document.getElementById('inputSig1Dept').value  = sigs.sig1Dept  || DEFAULT_SIGNATORIES.sig1Dept;

  document.getElementById('inputSig2Title').value = sigs.sig2Title || DEFAULT_SIGNATORIES.sig2Title;
  document.getElementById('inputSig2Name').value  = sigs.sig2Name  || DEFAULT_SIGNATORIES.sig2Name;
  document.getElementById('inputSig2Role').value  = sigs.sig2Role  || DEFAULT_SIGNATORIES.sig2Role;
  document.getElementById('inputSig2Dept').value  = sigs.sig2Dept  || DEFAULT_SIGNATORIES.sig2Dept;

  const dlg = document.getElementById('editSignatoriesDialog');
  if (dlg) dlg.classList.add('open');
}

function closeEditSignatoriesDialog() {
  const dlg = document.getElementById('editSignatoriesDialog');
  if (dlg) dlg.classList.remove('open');
}

function saveSignatories() {
  const sig1Name = document.getElementById('inputSig1Name').value.trim();
  const sig2Name = document.getElementById('inputSig2Name').value.trim();

  if (!sig1Name || !sig2Name) {
    showToast('Signatory full names cannot be empty', 'error');
    return;
  }

  const sigs = {
    sig1Title: document.getElementById('inputSig1Title').value.trim() || DEFAULT_SIGNATORIES.sig1Title,
    sig1Name:  sig1Name,
    sig1Role:  document.getElementById('inputSig1Role').value.trim()  || DEFAULT_SIGNATORIES.sig1Role,
    sig1Dept:  document.getElementById('inputSig1Dept').value.trim()  || DEFAULT_SIGNATORIES.sig1Dept,

    sig2Title: document.getElementById('inputSig2Title').value.trim() || DEFAULT_SIGNATORIES.sig2Title,
    sig2Name:  sig2Name,
    sig2Role:  document.getElementById('inputSig2Role').value.trim()  || DEFAULT_SIGNATORIES.sig2Role,
    sig2Dept:  document.getElementById('inputSig2Dept').value.trim()  || DEFAULT_SIGNATORIES.sig2Dept,
  };

  try {
    localStorage.setItem('scholartrack_report_signatories', JSON.stringify(sigs));
    renderSignatories(sigs);
    closeEditSignatoriesDialog();
    showToast('Report signatories updated successfully', 'success');
  } catch (e) {
    showToast('Failed to save signatories: ' + e.message, 'error');
  }
}

function resetSignatories() {
  showConfirmDialog({
    title: '&#8634; Reset Signatories',
    message: 'Are you sure you want to reset report signatories back to the default university officers?',
    confirmText: '&#8634; Reset to Default',
    isDanger: false,
    onConfirm: () => {
      localStorage.removeItem('scholartrack_report_signatories');
      renderSignatories(DEFAULT_SIGNATORIES);
      closeEditSignatoriesDialog();
      showToast('Signatories reset to default', 'info');
    }
  });
}

document.addEventListener('DOMContentLoaded', initReports);
