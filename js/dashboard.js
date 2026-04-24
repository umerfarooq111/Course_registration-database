const availableCoursesContainer = document.getElementById('availableCourses');
const myCoursesContainer = document.getElementById('myCourses');
const courseMessage = document.getElementById('courseMessage');
const myCoursesMessage = document.getElementById('myCoursesMessage');
const logoutButton = document.getElementById('logoutButton');

window.addEventListener('DOMContentLoaded', initDashboard);
logoutButton.addEventListener('click', logout);

async function initDashboard() {
  try {
    const session = await fetchJson('php/auth/session.php');
    if (!session || !session.student_id) {
      window.location.href = 'index.html';
      return;
    }

    await Promise.all([loadAvailableCourses(), loadMyCourses()]);
  } catch (error) {
    showCourseMessage(error.message, 'error');
  }
}

async function loadAvailableCourses() {
  clearCourseMessage();
  try {
    const data = await fetchJson('php/student/get_available_courses.php');
    if (!Array.isArray(data)) {
      throw new Error('Invalid course data');
    }

    if (data.length === 0) {
      availableCoursesContainer.innerHTML = '<p>No course sections available at this time.</p>';
      return;
    }

    availableCoursesContainer.innerHTML = data
      .map((section) => {
        const full = section.enrollment_count >= section.max_capacity;
        const statusLabel = full ? 'Full' : 'Open';
        const prereqs = section.prereq_titles ? section.prereq_titles : 'None';

        return `
          <article class="course-card">
            <div class="info-row">
              <h3>${escapeHtml(section.title)}</h3>
              <small>${escapeHtml(section.section_label)}</small>
            </div>
            <p><strong>Credits:</strong> ${section.credit_hr}</p>
            <p><strong>Instructor:</strong> ${escapeHtml(section.instructor_name || 'TBA')}</p>
            <p><strong>Seats:</strong> ${section.enrollment_count}/${section.max_capacity}</p>
            <p><strong>Status:</strong> ${statusLabel}</p>
            <p><strong>Prerequisites:</strong> ${escapeHtml(prereqs)}</p>
            <button class="btn" onclick="enrollSection(${section.section_id})" ${full ? 'disabled' : ''}>Enroll</button>
          </article>
        `;
      })
      .join('');
  } catch (error) {
    availableCoursesContainer.innerHTML = '';
    showCourseMessage(error.message, 'error');
  }
}

async function loadMyCourses() {
  clearMyCoursesMessage();
  try {
    const data = await fetchJson('php/student/get_my_courses.php');
    if (!Array.isArray(data)) {
      throw new Error('Invalid registration data');
    }

    if (data.length === 0) {
      myCoursesContainer.innerHTML = '<p>You are not enrolled in any course sections yet.</p>';
      return;
    }

    myCoursesContainer.innerHTML = `
      <table>
        <thead>
          <tr>
            <th>Course</th>
            <th>Section</th>
            <th>Instructor</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          ${data
            .map((row) => {
              const canDrop = row.status === 'REGISTERED';
              return `
                <tr>
                  <td>${escapeHtml(row.title)}</td>
                  <td>${escapeHtml(row.section_label)}</td>
                  <td>${escapeHtml(row.instructor_name || 'TBA')}</td>
                  <td>${escapeHtml(row.status)}</td>
                  <td>${
                    canDrop
                      ? `<button class="btn secondary" onclick="dropSection(${row.section_id})">Drop</button>`
                      : '<span style="color: #4c5f7f;">No action</span>'
                  }</td>
                </tr>
              `;
            })
            .join('')}
        </tbody>
      </table>
    `;
  } catch (error) {
    myCoursesContainer.innerHTML = '';
    showMyCoursesMessage(error.message, 'error');
  }
}

async function enrollSection(sectionId) {
  clearCourseMessage();
  try {
    const response = await fetch('php/student/enroll.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ section_id: sectionId }),
    });

    const result = await response.json();
    if (!response.ok) throw new Error(result.error || 'Enrollment failed');

    showCourseMessage(result.message, 'success');
    await Promise.all([loadAvailableCourses(), loadMyCourses()]);
  } catch (error) {
    showCourseMessage(error.message, 'error');
  }
}

async function dropSection(sectionId) {
  clearMyCoursesMessage();
  try {
    const response = await fetch('php/student/drop.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ section_id: sectionId }),
    });

    const result = await response.json();
    if (!response.ok) throw new Error(result.error || 'Drop request failed');

    showMyCoursesMessage(result.message, 'success');
    await Promise.all([loadAvailableCourses(), loadMyCourses()]);
  } catch (error) {
    showMyCoursesMessage(error.message, 'error');
  }
}

async function logout() {
  await fetch('php/auth/logout.php', { method: 'POST' });
  window.location.href = 'index.html';
}

async function fetchJson(url, options = {}) {
  const response = await fetch(url, options);
  if (response.status === 401) {
    window.location.href = 'index.html';
    return null;
  }
  const payload = await response.json();
  if (!response.ok) {
    throw new Error(payload.error || 'Request failed');
  }
  return payload;
}

function showCourseMessage(text, type) {
  courseMessage.textContent = text;
  courseMessage.className = `message show ${type}`;
}

function clearCourseMessage() {
  courseMessage.textContent = '';
  courseMessage.className = 'message';
}

function showMyCoursesMessage(text, type) {
  myCoursesMessage.textContent = text;
  myCoursesMessage.className = `message show ${type}`;
}

function clearMyCoursesMessage() {
  myCoursesMessage.textContent = '';
  myCoursesMessage.className = 'message';
}

function escapeHtml(value) {
  if (!value && value !== 0) return '';
  return String(value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}
