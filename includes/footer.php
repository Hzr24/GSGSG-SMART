<?php
/**
 * Shared Footer & Modals Include
 * GSGSG Scholarship Tracking System
 */
?>
<!-- ========= ADD STUDENT DIALOG ========= -->
<div class="dialog-overlay" id="addStudentDialog">
  <div class="dialog-box">
    <div class="dialog-title">&#10022; Add New Student</div>
    <div class="form-grid">
      <div class="form-group">
        <label class="form-label" for="addId">Student ID</label>
        <input class="form-field" id="addId" type="text" placeholder="e.g. 901234" />
      </div>
      <div class="form-group">
        <label class="form-label" for="addYear">Year</label>
        <select class="form-field" id="addYear">
          <option value="" disabled selected>Please select year</option>
          <option value="1">Year 1</option>
          <option value="2">Year 2</option>
          <option value="3">Year 3</option>
          <option value="4">Year 4</option>
        </select>
      </div>
      <div class="form-group full">
        <label class="form-label" for="addName">Full Name</label>
        <input class="form-field" id="addName" type="text"/>
      </div>
      <div class="form-group full">
        <label class="form-label" for="addProgram">Program</label>
        <select class="form-field" id="addProgram"
                onchange="document.getElementById('addProgramCustom').style.display=this.value==='__other__'?'':'none';">
          <option value="" disabled selected>Please select programme</option>
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
        <input class="form-field" id="addProgramCustom" type="text"
               placeholder="Type programme name"
               style="margin-top:8px;display:none;" />
      </div>
      <div class="form-group full">
        <label class="form-label" for="addEmail">Email</label>
        <input class="form-field" id="addEmail" type="email" placeholder="e.g. student@gsgsg.edu.my" />
      </div>
    </div>
    <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:22px;">
      <button class="btn btn-ghost" onclick="closeAddStudentDialog()">Cancel</button>
      <button class="btn btn-primary" onclick="confirmAddStudent()">&#10022; Add Student</button>
    </div>
  </div>
</div>

<!-- ========= IMPORT CSV DIALOG ========= -->
<div class="dialog-overlay" id="importDialog">
  <div class="dialog-box">
    <div class="dialog-title">&#128229; Import Students from CSV</div>
    <div class="import-zone" id="importZone"
         ondragover="importDragOver(event)"
         ondragleave="importDragLeave(event)"
         ondrop="importDrop(event)">
      <div class="import-zone-icon">&#128193;</div>
      <div class="import-zone-text">Drag &amp; drop a CSV file here</div>
      <div class="import-zone-sub">or</div>
      <button class="btn btn-secondary" style="margin-top:10px;" onclick="document.getElementById('csvFileInput').click()">Browse File</button>
      <input type="file" id="csvFileInput" accept=".csv" style="display:none;" onchange="handleCSVFile(event)" />
    </div>
    <div style="margin-top:14px;padding:12px;background:var(--bg-base);border-radius:var(--radius-sm);border:1px solid var(--border);">
      <div style="font-size:11px;font-weight:700;color:var(--text-muted);margin-bottom:6px;">Expected CSV Format:</div>
      <code style="font-size:11px;color:var(--text-secondary);line-height:1.8;">
        id, name, program, year, email, cgpa<br>
        901234, Ahmad Razali, Bachelor of CS, 3, ahmad@mail.edu, 3.85
      </code>
    </div>
    <div id="importPreview" style="margin-top:12px;display:none;"></div>
    <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:16px;">
      <button class="btn btn-ghost" onclick="closeImportDialog()">Cancel</button>
      <button class="btn btn-primary" id="importConfirmBtn" onclick="confirmImport()" style="display:none;">&#128229; Import Students</button>
    </div>
  </div>
</div>

<!-- ========= CONFIRMATION DIALOG ========= -->
<div class="dialog-overlay" id="confirmDialog">
  <div class="dialog-box" style="max-width:440px;">
    <div class="dialog-title" id="confirmDialogTitle" style="color:var(--status-revoked);">&#128465; Confirm Action</div>
    <div id="confirmDialogMessage" style="font-size:13px;color:var(--text-secondary);line-height:1.6;margin-top:14px;">
      Are you sure you want to proceed?
    </div>
    <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:22px;">
      <button class="btn btn-ghost" onclick="closeConfirmDialog()">Cancel</button>
      <button class="btn btn-danger" id="confirmDialogOkBtn">&#128465; Confirm</button>
    </div>
  </div>
</div>

<!-- ========= TOAST CONTAINER ========= -->
<div class="toast-container" id="toastContainer"></div>

<!-- Shared JavaScript -->
<script src="../js/api.js?v=<?php echo time(); ?>"></script>
<script src="../js/common.js?v=<?php echo time(); ?>"></script>
<?php if (isset($pageScript)): ?>
<script src="../<?php echo $pageScript; ?>?v=<?php echo time(); ?>"></script>
<?php endif; ?>

</body>
</html>
