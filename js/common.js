/**
 * Common Shared Utilities & UI Handlers
 * GSGSG Scholarship Tracking System
 */

// HTML Escaping
function escHtml(str) {
  return String(str ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

// GPA Color Formatters
function gpaColor(v) {
  return v >= 3.75 ? 'fill-active' : v >= 3.50 ? 'fill-atrisk' : 'fill-revoked';
}

function gpaTextColor(v) {
  return v >= 3.75 ? 'var(--status-active)' : v >= 3.50 ? 'var(--status-atrisk)' : 'var(--status-revoked)';
}

// Toast Notifications
function showToast(msg, type = 'info') {
  const container = document.getElementById('toastContainer');
  if (!container) return;
  const icons = { success: '&#9989;', error: '&#10060;', info: '&#8505;&#65039;' };
  const el = document.createElement('div');
  el.className = `toast ${type}`;
  el.innerHTML = `<span class="toast-icon">${icons[type] || icons.info}</span> <span>${escHtml(msg)}</span>`;
  container.appendChild(el);
  setTimeout(() => {
    el.style.opacity = '0';
    el.style.transform = 'translateX(110%)';
    el.style.transition = '0.3s';
    setTimeout(() => el.remove(), 300);
  }, 3500);
}

// Update Nav At Risk Badge
async function updateSidebarBadge() {
  try {
    const res = await API.getStudents();
    if (res && res.metrics) {
      const badge = document.getElementById('atRiskNavBadge');
      if (badge) {
        badge.textContent = res.metrics.atRisk;
        badge.style.display = res.metrics.atRisk > 0 ? '' : 'none';
      }
    }
  } catch (e) {
    console.error('Badge update error:', e);
  }
}

// Reusable Custom Confirmation Dialog
function showConfirmDialog({ title, message, confirmText, isDanger, onConfirm }) {
  const dlg = document.getElementById('confirmDialog');
  const titleEl = document.getElementById('confirmDialogTitle');
  const msgEl = document.getElementById('confirmDialogMessage');
  const okBtn = document.getElementById('confirmDialogOkBtn');

  if (titleEl) titleEl.innerHTML = title || '&#128465; Confirm Action';
  if (msgEl) msgEl.innerHTML = message || 'Are you sure you want to proceed?';
  if (okBtn) {
    okBtn.innerHTML = confirmText || '&#128465; Confirm';
    okBtn.className = isDanger !== false ? 'btn btn-danger' : 'btn btn-primary';
    okBtn.onclick = async () => {
      closeConfirmDialog();
      if (typeof onConfirm === 'function') {
        await onConfirm();
      }
    };
  }

  if (dlg) dlg.classList.add('open');
}

function closeConfirmDialog() {
  const dlg = document.getElementById('confirmDialog');
  if (dlg) dlg.classList.remove('open');
}

// Add Student Dialog
function openAddStudentDialog() {
  ['addId', 'addName', 'addEmail'].forEach(fid => {
    const f = document.getElementById(fid);
    if (f) f.value = '';
  });
  const yr = document.getElementById('addYear');
  if (yr) yr.value = '';
  const prg = document.getElementById('addProgram');
  if (prg) prg.value = '';
  const customPrg = document.getElementById('addProgramCustom');
  if (customPrg) { customPrg.style.display = 'none'; customPrg.value = ''; }

  const dlg = document.getElementById('addStudentDialog');
  if (dlg) dlg.classList.add('open');
}

function closeAddStudentDialog() {
  const dlg = document.getElementById('addStudentDialog');
  if (dlg) dlg.classList.remove('open');
}

async function confirmAddStudent() {
  const id = document.getElementById('addId').value.trim();
  const name = document.getElementById('addName').value.trim();
  const programRaw = document.getElementById('addProgram').value;
  let program = programRaw;

  if (programRaw === '__other__') {
    const customVal = (document.getElementById('addProgramCustom').value || '').trim();
    if (!customVal) {
      showToast('Please type your custom programme name', 'error');
      document.getElementById('addProgramCustom').focus();
      return;
    }
    program = customVal;
  }

  const yearVal = document.getElementById('addYear').value;
  const year = parseInt(yearVal);
  const email = document.getElementById('addEmail').value.trim();

  if (!id || !name) {
    showToast('Student ID and Name are required', 'error');
    return;
  }

  if (!yearVal) {
    showToast('Please select an academic year', 'error');
    return;
  }

  if (!program || program === '__other__') {
    showToast('Please select or enter a programme', 'error');
    return;
  }

  try {
    await API.addStudent({ id, name, program, year, email });
    showToast(`${name.split(' ')[0]} added successfully`, 'success');
    closeAddStudentDialog();
    ['addId', 'addName', 'addEmail'].forEach(fid => {
      const f = document.getElementById(fid);
      if (f) f.value = '';
    });
    const yr = document.getElementById('addYear');
    if (yr) yr.value = '';
    const prg2 = document.getElementById('addProgram');
    if (prg2) prg2.value = '';
    const customPrg2 = document.getElementById('addProgramCustom');
    if (customPrg2) { customPrg2.style.display = 'none'; customPrg2.value = ''; }
    updateSidebarBadge();
    if (typeof loadData === 'function') loadData();
    else if (typeof loadDashboard === 'function') loadDashboard();
    else if (typeof initReports === 'function') initReports();
    else if (window.location.pathname.includes('students.php')) window.location.reload();
  } catch (err) {
    showToast(err.message, 'error');
  }
}

// CSV Export
async function exportCSV() {
  try {
    const res = await API.getStudents();
    const students = res.data || [];
    const rows = [['Student ID', 'Full Name', 'Program', 'Year', 'Email', 'Current GPA', 'CGPA', 'Status', 'Notes']];
    students.forEach(s => {
      rows.push([
        s.id,
        s.name,
        s.program,
        s.year,
        s.email || '',
        (s.gpa || 0).toFixed(2),
        (s.cgpa || 0).toFixed(2),
        s.status,
        (s.notes || '').replace(/\n/g, ' ')
      ]);
    });

    const csv = rows.map(r => r.map(v => `"${String(v).replace(/"/g, '""')}"`).join(',')).join('\n');
    const url = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8;' }));
    const a = Object.assign(document.createElement('a'), {
      href: url,
      download: `scholarship_report_${new Date().toISOString().slice(0, 10)}.csv`
    });
    document.body.appendChild(a);
    a.click();
    a.remove();
    URL.revokeObjectURL(url);
    showToast(`Exported ${students.length} records to CSV`, 'success');
  } catch (err) {
    showToast('Failed to export CSV: ' + err.message, 'error');
  }
}

// CSV Import Dialog & Parser
let pendingImportData = [];

function openImportDialog() {
  pendingImportData = [];
  const pv = document.getElementById('importPreview');
  const btn = document.getElementById('importConfirmBtn');
  if (pv) pv.style.display = 'none';
  if (btn) btn.style.display = 'none';
  const dlg = document.getElementById('importDialog');
  if (dlg) dlg.classList.add('open');
}

function closeImportDialog() {
  const dlg = document.getElementById('importDialog');
  if (dlg) dlg.classList.remove('open');
}

function importDragOver(e) { e.preventDefault(); document.getElementById('importZone')?.classList.add('dragover'); }
function importDragLeave() { document.getElementById('importZone')?.classList.remove('dragover'); }
function importDrop(e) {
  e.preventDefault();
  document.getElementById('importZone')?.classList.remove('dragover');
  const f = e.dataTransfer.files[0];
  if (f) processCSVFile(f);
}
function handleCSVFile(e) {
  const f = e.target.files[0];
  if (f) processCSVFile(f);
}

// Robust CSV Line Parser that respects quotes
function parseCSVLine(text) {
  const result = [];
  let cur = '';
  let inQuotes = false;
  for (let i = 0; i < text.length; i++) {
    const c = text[i];
    if (c === '"') {
      if (inQuotes && text[i + 1] === '"') {
        cur += '"';
        i++;
      } else {
        inQuotes = !inQuotes;
      }
    } else if (c === ',' && !inQuotes) {
      result.push(cur.trim());
      cur = '';
    } else {
      cur += c;
    }
  }
  result.push(cur.trim());
  return result;
}

function processCSVFile(file) {
  const reader = new FileReader();
  reader.onload = ev => {
    const raw = ev.target.result.trim();
    const lines = raw.split(/\r?\n/).filter(l => l.trim().length > 0);
    if (lines.length < 2) {
      showToast('CSV has no data rows', 'error');
      return;
    }

    const header = parseCSVLine(lines[0]).map(h => h.toLowerCase().replace(/[^a-z0-9]/g, ''));
    const gi = name => header.findIndex(h => h.includes(name));

    const idIdx = gi('id');
    const nameIdx = gi('name');
    const progIdx = gi('program');
    const yearIdx = gi('year');
    const emailIdx = gi('email');
    const cgpaIdx = gi('cgpa');

    pendingImportData = [];

    for (let i = 1; i < lines.length; i++) {
      const cols = parseCSVLine(lines[i]);
      const id = cols[idIdx] || '';
      const name = cols[nameIdx] || '';
      if (!id || !name) continue;

      pendingImportData.push({
        id,
        name,
        program: cols[progIdx] || 'Bachelor of Computer Science',
        year: parseInt(cols[yearIdx]) || 1,
        email: cols[emailIdx] || '',
        cgpa: parseFloat(cols[cgpaIdx]) || 0
      });
    }

    if (!pendingImportData.length) {
      showToast('No valid student records detected in CSV', 'error');
      return;
    }

    const pv = document.getElementById('importPreview');
    const btn = document.getElementById('importConfirmBtn');
    if (pv) {
      pv.style.display = '';
      pv.innerHTML =
        `<div style="font-size:12px;color:var(--text-secondary);margin-bottom:8px;"><strong style="color:var(--status-active);">${pendingImportData.length}</strong> records ready to import</div>` +
        `<div style="max-height:120px;overflow-y:auto;background:var(--bg-base);border-radius:var(--radius-sm);padding:10px;border:1px solid var(--border);">` +
        pendingImportData.slice(0, 5).map(s =>
          `<div style="font-size:12px;padding:3px 0;border-bottom:1px solid var(--border);">
            <span style="color:var(--text-muted);font-family:monospace;">${escHtml(s.id)}</span> &middot; ${escHtml(s.name)} &middot; CGPA ${s.cgpa.toFixed(2)}
          </div>`
        ).join('') +
        (pendingImportData.length > 5 ? `<div style="font-size:11px;color:var(--text-muted);padding-top:4px;">... and ${pendingImportData.length - 5} more records</div>` : '') +
        `</div>`;
    }
    if (btn) btn.style.display = '';
  };
  reader.readAsText(file);
}

async function confirmImport() {
  if (!pendingImportData.length) return;
  try {
    const res = await API.importStudents(pendingImportData);
    showToast(res.message, 'success');
    closeImportDialog();
    updateSidebarBadge();
    if (typeof loadData === 'function') loadData();
    else window.location.reload();
  } catch (err) {
    showToast('Import failed: ' + err.message, 'error');
  }
}

// Global Dialog Close Listeners
document.addEventListener('DOMContentLoaded', () => {
  updateSidebarBadge();

  ['addStudentDialog', 'importDialog'].forEach(id => {
    const el = document.getElementById(id);
    if (el) {
      el.addEventListener('click', function(e) {
        if (e.target === this) {
          if (id === 'addStudentDialog') closeAddStudentDialog();
          if (id === 'importDialog') closeImportDialog();
        }
      });
    }
  });
});

// ============================================================
//  PRINT: hide chrome before dialog renders, restore after
//  CSS @media print can't reliably hide position:fixed elements
//  in all browsers — JS inline override works every time.
// ============================================================
(function setupPrintHandlers() {
  const HIDE_SELECTORS = ['.sidebar', '.topbar', '.header-accent', '.report-controls-panel', '.no-print'];

  function beforePrint() {
    HIDE_SELECTORS.forEach(sel => {
      document.querySelectorAll(sel).forEach(el => {
        el.dataset._printDisplay = el.style.display;
        el.dataset._printVisibility = el.style.visibility;
        el.style.setProperty('display', 'none', 'important');
        el.style.setProperty('visibility', 'hidden', 'important');
      });
    });
    const main = document.querySelector('.main');
    if (main) {
      main.dataset._printMargin = main.style.marginLeft;
      main.style.setProperty('margin-left', '0', 'important');
    }
  }

  function afterPrint() {
    HIDE_SELECTORS.forEach(sel => {
      document.querySelectorAll(sel).forEach(el => {
        el.style.display = el.dataset._printDisplay || '';
        el.style.visibility = el.dataset._printVisibility || '';
        delete el.dataset._printDisplay;
        delete el.dataset._printVisibility;
      });
    });
    const main = document.querySelector('.main');
    if (main) {
      main.style.marginLeft = main.dataset._printMargin || '';
      delete main.dataset._printMargin;
    }
  }

  window.addEventListener('beforeprint', beforePrint);
  window.addEventListener('afterprint', afterPrint);
})();

