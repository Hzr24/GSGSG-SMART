/**
 * Dashboard Page Logic
 * GSGSG Scholarship Tracking System
 */

async function loadDashboard() {
  try {
    const res = await API.getStudents();
    const students = res.data || [];
    const metrics = res.metrics || { total: 0, active: 0, atRisk: 0, revoked: 0, activeRate: 0 };

    // 1. Render Metrics
    document.getElementById('metricTotal').textContent = metrics.total;
    document.getElementById('metricActive').textContent = metrics.active;
    document.getElementById('metricAtRisk').textContent = metrics.atRisk;
    document.getElementById('metricRevoked').textContent = metrics.revoked;

    document.getElementById('totalTrend').textContent = `${metrics.total} enrolled`;
    document.getElementById('activeTrend').textContent = `${metrics.activeRate}%`;
    document.getElementById('atRiskTrend').textContent = metrics.atRisk > 0 ? `${metrics.atRisk} flagged` : 'Clear';
    document.getElementById('revokedTrend').textContent = metrics.revoked > 0 ? `${metrics.revoked} cases` : 'None';

    // 2. Render Priority Watchlist (At Risk & Revoked)
    const watchlist = students.filter(s => s.status === 'At Risk' || s.status === 'Revoked');
    const watchlistContainer = document.getElementById('watchlistContainer');
    const watchlistEmpty = document.getElementById('watchlistEmpty');

    if (watchlist.length === 0) {
      watchlistContainer.innerHTML = '';
      watchlistEmpty.style.display = 'block';
    } else {
      watchlistEmpty.style.display = 'none';
      watchlistContainer.innerHTML = watchlist.map(s => {
        const sc = s.status === 'At Risk' ? 'atrisk' : 'revoked';
        const initials = s.name.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase();
        return `
          <div class="watchlist-item" onclick="window.location.href='student-detail.php?id=${encodeURIComponent(s.id)}'">
            <div class="watchlist-avatar">${initials}</div>
            <div class="watchlist-info">
              <div class="watchlist-name">${escHtml(s.name)}</div>
              <div class="watchlist-meta"><span style="font-family:monospace;">${escHtml(s.id)}</span> &middot; ${escHtml(s.program)}</div>
            </div>
            <div class="watchlist-gpa">
              <div style="font-weight:700;font-size:13px;color:${gpaTextColor(s.cgpa)};">CGPA ${s.cgpa.toFixed(2)}</div>
              <div style="font-size:10px;color:var(--text-muted);">GPA ${s.gpa.toFixed(2)}</div>
            </div>
            <span class="badge ${sc}"><span class="badge-dot ${s.status === 'At Risk' ? 'pulse' : ''}"></span> ${s.status}</span>
            <button class="btn btn-secondary btn-sm" style="padding:4px 8px;">View &rarr;</button>
          </div>
        `;
      }).join('');
    }

    // 3. Program Distribution Breakdown
    const programStats = {};
    students.forEach(s => {
      if (!programStats[s.program]) programStats[s.program] = { total: 0, active: 0, atRisk: 0, revoked: 0 };
      programStats[s.program].total++;
      if (s.status === 'Active') programStats[s.program].active++;
      else if (s.status === 'At Risk') programStats[s.program].atRisk++;
      else if (s.status === 'Revoked') programStats[s.program].revoked++;
    });

    const progContainer = document.getElementById('programDistContainer');
    progContainer.innerHTML = Object.keys(programStats).map(p => {
      const st = programStats[p];
      const activePct = Math.round((st.active / st.total) * 100);
      const atRiskPct = Math.round((st.atRisk / st.total) * 100);
      const revokedPct = 100 - activePct - atRiskPct;
      return `
        <div class="prog-dist-card">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
            <div style="font-weight:600;font-size:12px;color:var(--text-primary);">${escHtml(p)}</div>
            <div style="font-size:11px;color:var(--text-muted);">${st.total} students</div>
          </div>
          <div class="dist-stacked-bar">
            <div style="width:${activePct}%;background:var(--status-active);" title="Active: ${st.active} (${activePct}%)"></div>
            <div style="width:${atRiskPct}%;background:var(--status-atrisk);" title="At Risk: ${st.atRisk} (${atRiskPct}%)"></div>
            <div style="width:${revokedPct}%;background:var(--status-revoked);" title="Revoked: ${st.revoked} (${revokedPct}%)"></div>
          </div>
          <div style="display:flex;gap:12px;margin-top:6px;font-size:10px;color:var(--text-secondary);">
            <span><span style="color:var(--status-active);font-weight:700;">●</span> Active: ${st.active}</span>
            <span><span style="color:var(--status-atrisk);font-weight:700;">●</span> At Risk: ${st.atRisk}</span>
            <span><span style="color:var(--status-revoked);font-weight:700;">●</span> Revoked: ${st.revoked}</span>
          </div>
        </div>
      `;
    }).join('');

  } catch (err) {
    showToast('Failed to load dashboard: ' + err.message, 'error');
  }
}

document.addEventListener('DOMContentLoaded', loadDashboard);
