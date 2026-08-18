/**
 * Central API Client Layer
 * GSGSG Scholarship Tracking System
 */

const GRADE_POINTS = {
  'A+': 4.0, 'A': 4.0, 'A-': 3.7,
  'B+': 3.3, 'B': 3.0, 'B-': 2.7,
  'C+': 2.3, 'C': 2.0, 'C-': 1.7,
  'D+': 1.3, 'D': 1.0,
  'F': 0.0
};

const API = {
  async getStudents() {
    const res = await fetch('../api/students.php');
    if (!res.ok) throw new Error((await res.json()).message || 'Failed to load students');
    return await res.json();
  },

  async getStudent(id) {
    const res = await fetch(`../api/students.php?id=${encodeURIComponent(id)}`);
    if (!res.ok) throw new Error((await res.json()).message || 'Failed to load student');
    return await res.json();
  },

  async addStudent(payload) {
    const res = await fetch('../api/students.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'create', ...payload })
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.message || 'Failed to add student');
    return data;
  },

  async updateNotes(id, notes) {
    const res = await fetch('../api/students.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'update_notes', id, notes })
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.message || 'Failed to save notes');
    return data;
  },

  async updateStudent(payload) {
    const res = await fetch('../api/students.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'update_student', ...payload })
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.message || 'Failed to update student details');
    return data;
  },

  async updateYear(id, year) {
    const res = await fetch('../api/students.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'update_year', id, year })
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.message || 'Failed to update year');
    return data;
  },

  async toggleStatus(id) {
    const res = await fetch('../api/students.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'toggle_status', id })
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.message || 'Failed to toggle status');
    return data;
  },

  async deleteStudent(id) {
    const res = await fetch('../api/students.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'delete', id })
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.message || 'Failed to delete student');
    return data;
  },

  async importStudents(students) {
    const res = await fetch('../api/students.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'import', students })
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.message || 'Failed to import students');
    return data;
  },

  async addSemester(studentId, label) {
    const res = await fetch('../api/courses.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'add_semester', student_id: studentId, label })
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.message || 'Failed to add semester');
    return data;
  },

  async deleteSemester(semesterId, studentId) {
    const res = await fetch('../api/courses.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'delete_semester', semester_id: semesterId, student_id: studentId })
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.message || 'Failed to delete semester');
    return data;
  },

  async addCourse(semesterId, studentId, course) {
    const res = await fetch('../api/courses.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'add_course', semester_id: semesterId, student_id: studentId, ...course })
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.message || 'Failed to add course');
    return data;
  },

  async deleteCourse(courseId, studentId) {
    const res = await fetch('../api/courses.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'delete_course', course_id: courseId, student_id: studentId })
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.message || 'Failed to remove course');
    return data;
  }
};
