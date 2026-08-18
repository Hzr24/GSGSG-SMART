/**
 * Student Roster Page Logic
 * GSGSG Scholarship Tracking System
 */

let allStudents = [];
let currentFilter = 'All';
let currentProgram = 'All';
let currentSearch = '';
let sortKey = null;
let sortDir = 1;
let currentPage = 1;
const PAGE_SIZE = 10;

async function loadData() {
  try {
    const res = await API.getStudents();
    allStudents = res.data || [];
    populateProgramFilter();
    renderTable();
  } catch (err) {
    showToast('Failed to load students: ' + err.message, 'error');
  }
}

function populateProgramFilter() {
  const sel = document.getElementById('programFilter');
  if (!sel) return;
  const programs = Array.from(new Set(allStudents.map(s => s.program))).sort();
  sel.innerHTML = '<option value="All">All Programs</option>' +
    programs.map(p => `<option value="${escHtml(p)}">${escHtml(p)}</option>`).join('');
}

function setFilter(f, btn) {
  currentFilter = f;
  currentPage = 1;
  document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active', 'green', 'yellow', 'red', 'all'));
  const cls = { 'Active': 'green', 'At Risk': 'yellow', 'Revoked': 'red', 'All': 'all' }[f] || 'all';
  if (btn) btn.classList.add('active', cls);
  renderTable();
}

function setProgramFilter(p) {
  currentProgram = p;
  currentPage = 1;
  renderTable();
}

function sortBy(key) {
  if (sortKey === key) sortDir *= -1;
  else { sortKey = key; sortDir = 1; }
  document.querySelectorAll('th').forEach(t => t.classList.remove('sorted'));
  const th = document.getElementById('th-' + key);
  if (th) th.classList.add('sorted');
  renderTable();
}

function resetSort() {
  sortKey = null;
  sortDir = 1;
  document.querySelectorAll('th').forEach(t => t.classList.remove('sorted'));
  renderTable();
}

function getFiltered() {
  let list = [...allStudents];
  if (currentFilter !== 'All') list = list.filter(s => s.status === currentFilter);
  if (currentProgram !== 'All') list = list.filter(s => s.program === currentProgram);
  if (currentSearch) {
    list = list.filter(s =>
      s.name.toLowerCase().includes(currentSearch) ||
      s.id.toLowerCase().includes(currentSearch) ||
      s.program.toLowerCase().includes(currentSearch)
    );
  }
  if (sortKey) {
    list.sort((a, b) => {
      let va = a[sortKey], vb = b[sortKey];
      if (typeof va === 'string') va = va.toLowerCase();
      if (typeof vb === 'string') vb = vb.toLowerCase();
      return va < vb ? -sortDir : va > vb ? sortDir : 0;
    });
  }
  return list;
}

function renderTable() {
  const filtered = getFiltered();
  const total = filtered.length;
  const pages = Math.max(1, Math.ceil(total / PAGE_SIZE));
  if (currentPage > pages) currentPage = pages;
  const start = (currentPage - 1) * PAGE_SIZE;
  const end = Math.min(start + PAGE_SIZE, total);
  const visible = filtered.slice(start, end);

  const tbody = document.getElementById('tableBody');
  const empty = document.getElementById('emptyState');
  document.getElementById('rowCount').textContent = `${total} student${total !== 1 ? 's' : ''}`;

  if (visible.length === 0) {
    tbody.innerHTML = '';
    empty.style.display = '';
  } else {
    empty.style.display = 'none';
    tbody.innerHTML = visible.map(s => {
      const sc = { 'Active': 'active', 'At Risk': 'atrisk', 'Revoked': 'revoked' }[s.status] || 'active';
      const barW = Math.min(100, ((s.cgpa || 0) / 4.0) * 100).toFixed(1);
      const gpaW = Math.min(100, ((s.gpa || 0) / 4.0) * 100).toFixed(1);
      const initials = s.name.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase();
      const detailUrl = `student-detail.php?id=${encodeURIComponent(s.id)}`;

      return `
        <tr onclick="window.location.href='${detailUrl}'">
          <td style="color:var(--text-muted);font-family:monospace;font-size:12px;">${escHtml(s.id)}</td>
          <td>
            <div style="display:flex;align-items:center;gap:10px;">
              <div style="width:32px;height:32px;border-radius:8px;background:linear-gradient(135deg,var(--accent-blue),var(--accent-violet));display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;flex-shrink:0;">${initials}</div>
              <div>
                <div style="font-weight:600;color:var(--text-primary);">${escHtml(s.name)}</div>
                <div style="font-size:11px;color:var(--text-muted);">Year ${s.year} &middot; ${escHtml(s.email || 'No email')}</div>
              </div>
            </div>
          </td>
          <td style="color:var(--text-secondary);font-size:12px;">${escHtml(s.program)}</td>
          <td>
            <div class="gpa-bar-wrap">
              <span style="font-weight:700;font-size:12px;color:${gpaTextColor(s.gpa || 0)};min-width:32px;">${(s.gpa || 0).toFixed(2)}</span>
              <div class="gpa-bar"><div class="gpa-bar-fill ${gpaColor(s.gpa || 0)}" style="width:${gpaW}%"></div></div>
            </div>
          </td>
          <td>
            <div class="gpa-bar-wrap">
              <span style="font-weight:700;font-size:13px;color:${gpaTextColor(s.cgpa || 0)};min-width:32px;">${(s.cgpa || 0).toFixed(2)}</span>
              <div class="gpa-bar" style="width:70px;"><div class="gpa-bar-fill ${gpaColor(s.cgpa || 0)}" style="width:${barW}%"></div></div>
            </div>
          </td>
          <td><span class="badge ${sc}"><span class="badge-dot ${s.status === 'At Risk' ? 'pulse' : ''}"></span>${escHtml(s.status)}</span></td>
          <td onclick="event.stopPropagation()">
            <div class="td-actions">
              <a class="btn btn-secondary btn-sm" href="${detailUrl}">View</a>
              <button class="btn btn-ghost btn-sm" onclick="quickToggleStatus('${escHtml(s.id)}', event)" title="Cycle status">&hArr;</button>
            </div>
          </td>
        </tr>
      `;
    }).join('');
  }

  renderPagination(total, pages, start, end);
}

function renderPagination(total, pages, start, end) {
  document.getElementById('pagInfo').textContent =
    total === 0 ? 'No records' : `Showing ${start + 1}\u2013${end} of ${total}`;

  const ctrl = document.getElementById('pagControls');
  if (pages <= 1) { ctrl.innerHTML = ''; return; }

  let html = `<button class="pag-btn" onclick="goPage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>&lsaquo; Prev</button>`;
  for (let i = 1; i <= pages; i++) {
    if (pages > 7 && Math.abs(i - currentPage) > 2 && i !== 1 && i !== pages) {
      if (i === currentPage - 3 || i === currentPage + 3) html += '<span style="color:var(--text-muted);padding:0 2px;">&hellip;</span>';
      continue;
    }
    html += `<button class="pag-btn ${i === currentPage ? 'active' : ''}" onclick="goPage(${i})">${i}</button>`;
  }
  html += `<button class="pag-btn" onclick="goPage(${currentPage + 1})" ${currentPage === pages ? 'disabled' : ''}>Next &rsaquo;</button>`;
  ctrl.innerHTML = html;
}

function goPage(p) {
  const pages = Math.max(1, Math.ceil(getFiltered().length / PAGE_SIZE));
  currentPage = Math.max(1, Math.min(p, pages));
  renderTable();
}

async function quickToggleStatus(id, e) {
  if (e) e.stopPropagation();
  try {
    const res = await API.toggleStatus(id);
    showToast(res.message, 'info');
    loadData();
    updateSidebarBadge();
  } catch (err) {
    showToast('Failed to toggle status: ' + err.message, 'error');
  }
}

// Initializer
document.addEventListener('DOMContentLoaded', () => {
  // Check URL search params for initial filter
  const params = new URLSearchParams(window.location.search);
  const filterParam = params.get('filter');
  if (filterParam) {
    currentFilter = filterParam;
    const btn = document.querySelector(`.filter-tab[data-filter="${filterParam}"]`);
    if (btn) {
      document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active', 'green', 'yellow', 'red', 'all'));
      const cls = { 'Active': 'green', 'At Risk': 'yellow', 'Revoked': 'red', 'All': 'all' }[filterParam] || 'all';
      btn.classList.add('active', cls);
    }
  }

  const searchInput = document.getElementById('searchInput');
  if (searchInput) {
    searchInput.addEventListener('input', e => {
      currentSearch = e.target.value.toLowerCase().trim();
      currentPage = 1;
      renderTable();
    });
  }

  loadData();
});
