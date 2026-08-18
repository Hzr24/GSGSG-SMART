/**
 * Student Academic Detail Profile Logic
 * GSGSG Scholarship Tracking System
 */

let currentStudent = null;
let notesTimer = null;

function getStudentIdFromUrl() {
  const params = new URLSearchParams(window.location.search);
  return params.get('id');
}

async function loadStudentProfile() {
  const id = getStudentIdFromUrl();
  if (!id) {
    showToast('No student ID specified in URL', 'error');
    setTimeout(() => window.location.href = 'students.php', 1500);
    return;
  }

  try {
    const res = await API.getStudent(id);
    currentStudent = res.data;
    renderProfile();
  } catch (err) {
    showToast('Failed to load student: ' + err.message, 'error');
    document.getElementById('profileMainContent').innerHTML = `
      <div class="empty-state" style="padding:60px 20px;">
        <div class="empty-icon">&#10060;</div>
        <div class="empty-msg">Student Record Not Found</div>
        <div class="empty-sub">${escHtml(err.message)}</div>
        <a class="btn btn-secondary" href="students.php" style="margin-top:16px;">&larr; Return to Roster</a>
      </div>
    `;
  }
}

function renderProfile() {
  const s = currentStudent;
  if (!s) return;

  const initials = s.name.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase();
  const sc = { 'Active': 'active', 'At Risk': 'atrisk', 'Revoked': 'revoked' }[s.status] || 'active';

  // Breadcrumbs & Header
  document.getElementById('crumbName').textContent = s.name;
  document.getElementById('studentAvatar').textContent = initials;
  document.getElementById('studentName').textContent = s.name;
  document.getElementById('studentIdBadge').textContent = s.id;
  document.getElementById('studentProgram').textContent = s.program;
  document.getElementById('studentYear').textContent = `Year ${s.year}`;
  document.getElementById('studentEmail').textContent = s.email || 'No email provided';

  const badge = document.getElementById('studentStatusBadge');
  badge.className = `badge ${sc}`;
  badge.innerHTML = `<span class="badge-dot ${s.status === 'At Risk' ? 'pulse' : ''}"></span> ${escHtml(s.status)}`;

  // Performance Stats
  document.getElementById('statCGPA').textContent = (s.cgpa || 0).toFixed(2);
  document.getElementById('statCGPA').style.color = gpaTextColor(s.cgpa || 0);

  document.getElementById('statGPA').textContent = (s.gpa || 0).toFixed(2);
  document.getElementById('statGPA').style.color = gpaTextColor(s.gpa || 0);

  document.getElementById('statCredits').textContent = s.total_credits || 0;

  // Notes
  const notesArea = document.getElementById('notesArea');
  notesArea.value = s.notes || '';

  // Semesters Breakdown
  renderSemesters(s);
}

function renderSemesters(s) {
  const container = document.getElementById('semestersList');
  const gradeOpts = Object.keys(GRADE_POINTS).map(g => `<option value="${g}">${g}</option>`).join('');

  if (!s.semesters || s.semesters.length === 0) {
    container.innerHTML = `
      <div class="empty-state" style="padding:30px;background:var(--bg-elevated);border-radius:var(--radius-md);border:1px solid var(--border);">
        <div class="empty-icon">&#128218;</div>
        <div class="empty-msg">No Semester Records</div>
        <div class="empty-sub">Click "+ Add Semester" above to start adding courses.</div>
      </div>
    `;
    return;
  }

  container.innerHTML = s.semesters.map((sem, si) => {
    const rows = (sem.courses || []).map(c => {
      const gp = GRADE_POINTS[c.grade] ?? 0;
      const pts = (gp * c.credits).toFixed(1);
      const gc = c.grade.startsWith('A') ? 'grade-A' : c.grade.startsWith('B') ? 'grade-B' : c.grade.startsWith('C') ? 'grade-C' : c.grade === 'F' ? 'grade-F' : 'grade-D';
      return `
        <tr>
          <td style="color:var(--text-muted);font-family:monospace;font-size:11px;">${escHtml(c.code)}</td>
          <td>${escHtml(c.name)}</td>
          <td style="text-align:center;">${c.credits}</td>
          <td><span class="grade-badge ${gc}">${escHtml(c.grade)}</span></td>
          <td>${pts} <button class="btn btn-ghost no-print" style="padding:1px 5px;font-size:10px;margin-left:6px;" onclick="deleteCourse(${c.id})" title="Delete Course">&#10005;</button></td>
        </tr>
      `;
    }).join('');

    return `
      <div class="sem-section open" id="sem-${sem.id}">
        <div class="sem-header" onclick="toggleSem(${sem.id})">
          <div class="sem-title">
            <span>&#128218;</span>
            <span style="font-weight:600;">${escHtml(sem.label)}</span>
            <span class="sem-gpa-badge">GPA: ${(sem.gpa || 0).toFixed(2)} (${sem.total_credits || 0} Credits)</span>
          </div>
          <div style="display:flex;align-items:center;gap:8px;" class="no-print">
            <button class="btn btn-ghost btn-sm" onclick="event.stopPropagation();deleteSemester(${sem.id}, '${escHtml(sem.label)}')" style="padding:3px 7px;font-size:11px;" title="Delete semester">&#128465;</button>
            <span class="sem-chevron">&#9660;</span>
          </div>
        </div>
        <div class="sem-body">
          <table class="sem-table">
            <thead>
              <tr><th>Code</th><th>Course Name</th><th>Credits</th><th>Grade</th><th>Pts</th></tr>
            </thead>
            <tbody>${rows || '<tr><td colspan="5" style="text-align:center;color:var(--text-muted);padding:14px;">No courses recorded yet.</td></tr>'}</tbody>
          </table>
          <div class="form-row no-print" style="margin-top:12px;">
            <input class="form-input" id="newCode-${sem.id}" type="text" placeholder="Code" style="width:85px;" />
            <input class="form-input" id="newName-${sem.id}" type="text" placeholder="Course name" style="flex:1;min-width:120px;" />
            <input class="form-input" id="newCred-${sem.id}" type="number" placeholder="Cr" min="1" max="6" value="3" style="width:55px;" />
            <select class="form-input" id="newGrade-${sem.id}" style="width:70px;">${gradeOpts}</select>
            <button class="btn btn-primary btn-sm" onclick="addCourse(${sem.id})">+ Add Course</button>
          </div>
        </div>
      </div>
    `;
  }).join('');
}

function toggleSem(semId) {
  const el = document.getElementById(`sem-${semId}`);
  if (el) el.classList.toggle('open');
}

// Course & Semester Actions
async function addCourse(semId) {
  const code = document.getElementById(`newCode-${semId}`).value.trim();
  const name = document.getElementById(`newName-${semId}`).value.trim();
  const credits = parseInt(document.getElementById(`newCred-${semId}`).value) || 3;
  const grade = document.getElementById(`newGrade-${semId}`).value;

  if (!name) {
    showToast('Course name is required', 'error');
    return;
  }

  try {
    const res = await API.addCourse(semId, currentStudent.id, { code: code || 'CRS', name, credits, grade });
    currentStudent = res.student;
    renderProfile();
    updateSidebarBadge();
    showToast(`Course "${name}" added`, 'success');
  } catch (err) {
    showToast('Failed to add course: ' + err.message, 'error');
  }
}

async function deleteCourse(courseId) {
  try {
    const res = await API.deleteCourse(courseId, currentStudent.id);
    currentStudent = res.student;
    renderProfile();
    updateSidebarBadge();
    showToast('Course removed', 'info');
  } catch (err) {
    showToast('Failed to delete course: ' + err.message, 'error');
  }
}

// Change Student Academic Year
async function changeStudentYear(year) {
  if (!currentStudent) return;
  const y = parseInt(year);
  try {
    await API.updateYear(currentStudent.id, y);
    currentStudent.year = y;
    showToast(`Academic Year updated to Year ${y}`, 'success');
  } catch (err) {
    showToast('Failed to update academic year: ' + err.message, 'error');
    const sel = document.getElementById('studentYearSelect');
    if (sel) sel.value = currentStudent.year;
  }
}

// Custom Add Semester Modal Handlers
function openAddSemesterDialog() {
  const defaultLabel = `Semester ${(currentStudent?.semesters?.length || 0) + 1} (2025/2026)`;
  const input = document.getElementById('semLabelInput');
  if (input) input.value = defaultLabel;
  const dlg = document.getElementById('addSemesterDialog');
  if (dlg) {
    dlg.classList.add('open');
    setTimeout(() => input?.focus(), 50);
  }
}

function closeAddSemesterDialog() {
  const dlg = document.getElementById('addSemesterDialog');
  if (dlg) dlg.classList.remove('open');
}

function addSemester() {
  openAddSemesterDialog();
}

async function confirmAddSemester() {
  const label = (document.getElementById('semLabelInput')?.value || '').trim();
  if (!label) {
    showToast('Please enter a semester label', 'error');
    return;
  }

  try {
    const res = await API.addSemester(currentStudent.id, label);
    currentStudent = res.student;
    renderProfile();
    closeAddSemesterDialog();
    showToast(`Semester "${label}" added`, 'success');
  } catch (err) {
    showToast('Failed to add semester: ' + err.message, 'error');
  }
}

function deleteSemester(semId, label) {
  showConfirmDialog({
    title: '&#128465; Delete Semester',
    message: `Are you sure you want to delete <strong>${escHtml(label)}</strong> and all its courses?<br><br><span style="color:var(--status-revoked);font-size:12px;">This action cannot be undone.</span>`,
    confirmText: '&#128465; Delete Semester',
    isDanger: true,
    onConfirm: async () => {
      try {
        const res = await API.deleteSemester(semId, currentStudent.id);
        currentStudent = res.student;
        renderProfile();
        updateSidebarBadge();
        showToast('Semester deleted', 'info');
      } catch (err) {
        showToast('Failed to delete semester: ' + err.message, 'error');
      }
    }
  });
}

// Auto-Save Notes
function autoSaveNotes() {
  clearTimeout(notesTimer);
  const statusEl = document.getElementById('notesSaved');
  statusEl.textContent = 'Saving...';

  notesTimer = setTimeout(async () => {
    const notes = document.getElementById('notesArea').value;
    try {
      await API.updateNotes(currentStudent.id, notes);
      currentStudent.notes = notes;
      statusEl.textContent = '\u2713 Notes auto-saved to database';
      setTimeout(() => { if (statusEl.textContent.includes('saved')) statusEl.textContent = ''; }, 2500);
    } catch (err) {
      statusEl.textContent = '\u2717 Save error';
      showToast('Failed to save notes: ' + err.message, 'error');
    }
  }, 800);
}

// Quick Status Toggle
async function toggleCurrentStatus() {
  if (!currentStudent) return;
  try {
    const res = await API.toggleStatus(currentStudent.id);
    currentStudent.status = res.new_status;
    renderProfile();
    updateSidebarBadge();
    showToast(`Status updated to "${res.new_status}"`, 'info');
  } catch (err) {
    showToast('Failed to update status: ' + err.message, 'error');
  }
}

// Delete Entire Student Record
function confirmDeleteStudent() {
  if (!currentStudent) return;
  showConfirmDialog({
    title: '&#128465; Delete Student Record',
    message: `Are you sure you want to permanently delete <strong>${escHtml(currentStudent.name)}</strong> (${escHtml(currentStudent.id)}) from the database?<br><br><span style="color:var(--status-revoked);font-size:12px;">All associated semesters, courses, and performance records will be permanently removed.</span>`,
    confirmText: '&#128465; Remove Student',
    isDanger: true,
    onConfirm: async () => {
      try {
        await API.deleteStudent(currentStudent.id);
        showToast(`${currentStudent.name} deleted`, 'info');
        updateSidebarBadge();
        setTimeout(() => window.location.href = 'students.php', 800);
      } catch (err) {
        showToast('Failed to delete student: ' + err.message, 'error');
      }
    }
  });
}

// Edit Student Details Modal Handlers
function openEditStudentDialog() {
  if (!currentStudent) return;
  const s = currentStudent;

  document.getElementById('editId').value = s.id || '';
  document.getElementById('editName').value = s.name || '';
  document.getElementById('editYear').value = s.year || 1;
  document.getElementById('editEmail').value = s.email || '';

  const programSelect = document.getElementById('editProgram');
  const customInput = document.getElementById('editProgramCustom');

  // Check if currentStudent.program exists in standard options
  const optionExists = Array.from(programSelect.options).some(opt => opt.value === s.program);
  if (optionExists) {
    programSelect.value = s.program;
    if (customInput) {
      customInput.style.display = 'none';
      customInput.value = '';
    }
  } else {
    programSelect.value = '__other__';
    if (customInput) {
      customInput.style.display = '';
      customInput.value = s.program || '';
    }
  }

  const dlg = document.getElementById('editStudentDialog');
  if (dlg) dlg.classList.add('open');
}

function closeEditStudentDialog() {
  const dlg = document.getElementById('editStudentDialog');
  if (dlg) dlg.classList.remove('open');
}

async function confirmEditStudent() {
  if (!currentStudent) return;

  const id = currentStudent.id;
  const name = document.getElementById('editName').value.trim();
  const yearVal = document.getElementById('editYear').value;
  const year = parseInt(yearVal);
  const email = document.getElementById('editEmail').value.trim();
  const programRaw = document.getElementById('editProgram').value;
  let program = programRaw;

  if (programRaw === '__other__') {
    const customVal = (document.getElementById('editProgramCustom')?.value || '').trim();
    if (!customVal) {
      showToast('Please type your custom programme name', 'error');
      document.getElementById('editProgramCustom')?.focus();
      return;
    }
    program = customVal;
  }

  if (!name) {
    showToast('Full Name is required', 'error');
    return;
  }

  if (!program || program === '__other__') {
    showToast('Please select or enter a programme', 'error');
    return;
  }

  try {
    const res = await API.updateStudent({ id, name, program, year, email });
    currentStudent = res.data;
    renderProfile();
    closeEditStudentDialog();
    showToast('Student details updated successfully', 'success');
  } catch (err) {
    showToast('Failed to update student: ' + err.message, 'error');
  }
}

document.addEventListener('DOMContentLoaded', loadStudentProfile);
