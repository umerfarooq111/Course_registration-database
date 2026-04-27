let adminCourses = [];
let adminStudents = [];
let adminInstructors = [];
let adminDepts = [];

function switchTab(tabName) {
    document.getElementById('tab-courses').style.display = 'none';
    document.getElementById('tab-students').style.display = 'none';
    document.getElementById('tab-instructors').style.display = 'none';
    document.getElementById('tab-departments').style.display = 'none';
    
    document.getElementById('tab-' + tabName).style.display = 'block';
    
    const links = document.getElementById('tab-links').getElementsByTagName('a');
    for(let i = 0; i < links.length; i++) {
        links[i].style.color = '#bdc3c7';
    }
    event.target.style.color = '#ecf0f1';
}

function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}

function escapeHtml(obj) {
    return JSON.stringify(obj).replace(/'/g, "&#39;").replace(/"/g, "&quot;");
}

/* ================= COURSES ================= */
async function loadCourses() {
    try {
        const res = await fetch('php/admin/courses.php');
        if (res.status === 401) { window.location.href = 'admin_login.php'; return; }
        const data = await res.json();
        
        if (res.ok) {
            adminCourses = data;
            const list = document.getElementById('course-list-container');
            if(adminCourses.length === 0) { list.innerHTML = '<p>No courses found.</p>'; return; }
            let html = `<table><thead><tr><th>ID</th><th>Course Title</th><th>Department</th><th>Credits</th><th>Capacity</th><th>Actions</th></tr></thead><tbody>`;
            adminCourses.forEach(c => {
                html += `<tr>
                    <td>${c.course_id}</td>
                    <td><strong>${c.title}</strong></td>
                    <td>${c.department_name} (ID: ${c.department_id})</td>
                    <td>${c.credit_hr}</td>
                    <td>${c.max_capacity}</td>
                    <td>
                        <button onclick="editCourse('${escapeHtml(c)}')" style="background:#f39c12; margin-right: 5px;">Edit</button>
                        <button onclick="deleteCourse(${c.course_id})" style="background:#c0392b;">Delete</button>
                    </td>
                </tr>`;
            });
            list.innerHTML = html + `</tbody></table>`;
        }
    } catch(e) { console.error(e); }
}

function openCourseModal() {
    document.getElementById('c-modal-title').innerText = 'Add Course';
    document.getElementById('c_id').value = '';
    document.getElementById('c_title').value = '';
    document.getElementById('c_credits').value = '';
    document.getElementById('c_capacity').value = '';
    document.getElementById('c_dept').value = '';
    document.getElementById('course-modal').style.display = 'flex';
}

function editCourse(dataJSON) {
    const c = JSON.parse(dataJSON);
    document.getElementById('c-modal-title').innerText = 'Edit Course';
    document.getElementById('c_id').value = c.course_id;
    document.getElementById('c_title').value = c.title;
    document.getElementById('c_credits').value = c.credit_hr;
    document.getElementById('c_capacity').value = c.max_capacity;
    document.getElementById('c_dept').value = c.department_id;
    document.getElementById('course-modal').style.display = 'flex';
}

async function saveCourse() {
    const id = document.getElementById('c_id').value;
    const payload = {
        title: document.getElementById('c_title').value,
        credit_hr: document.getElementById('c_credits').value,
        max_capacity: document.getElementById('c_capacity').value,
        department_id: document.getElementById('c_dept').value
    };
    if (id !== '') payload.course_id = id;
    
    try {
        const res = await fetch('php/admin/courses.php', { method: id ? 'PUT' : 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
        const data = await res.json();
        if (res.ok) { closeModal('course-modal'); loadCourses(); } else alert(data.error);
    } catch(e) { alert(e); }
}

async function deleteCourse(id) {
    if (!confirm('Delete this course?')) return;
    const res = await fetch('php/admin/courses.php', { method: 'DELETE', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ course_id: id }) });
    if (res.ok) loadCourses(); else alert((await res.json()).error);
}

/* ================= STUDENTS ================= */
async function loadStudents() {
    try {
        const res = await fetch('php/admin/students.php');
        const data = await res.json();
        if (res.ok) {
            adminStudents = data;
            const list = document.getElementById('student-list-container');
            if(adminStudents.length === 0) { list.innerHTML = '<p>No students found.</p>'; return; }
            let html = `<table><thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Status</th><th>Password (db)</th><th>Actions</th></tr></thead><tbody>`;
            adminStudents.forEach(s => {
                html += `<tr>
                    <td>STD-${s.student_id}</td>
                    <td><strong>${s.name}</strong></td>
                    <td>${s.email}</td>
                    <td>${s.status}</td>
                    <td>${s.password}</td>
                    <td>
                        <button onclick="editStudent('${escapeHtml(s)}')" style="background:#f39c12; margin-right: 5px;">Edit</button>
                        <button onclick="deleteStudent(${s.student_id})" style="background:#c0392b;">Delete</button>
                    </td>
                </tr>`;
            });
            list.innerHTML = html + `</tbody></table>`;
        }
    } catch(e) { console.error(e); }
}

function openStudentModal() {
    document.getElementById('s-modal-title').innerText = 'Add Student';
    document.getElementById('s_id').value = '';
    document.getElementById('s_name').value = '';
    document.getElementById('s_email').value = '';
    document.getElementById('s_phone').value = '';
    document.getElementById('s_password').value = '';
    document.getElementById('s_status').value = 'CURRENT';
    document.getElementById('student-modal').style.display = 'flex';
}

function editStudent(dataJSON) {
    const s = JSON.parse(dataJSON);
    document.getElementById('s-modal-title').innerText = 'Edit Student';
    document.getElementById('s_id').value = s.student_id;
    document.getElementById('s_name').value = s.name;
    document.getElementById('s_email').value = s.email;
    document.getElementById('s_phone').value = s.phone_no || '';
    document.getElementById('s_password').value = ''; // leave blank by default
    document.getElementById('s_status').value = s.status;
    document.getElementById('student-modal').style.display = 'flex';
}

async function saveStudent() {
    const id = document.getElementById('s_id').value;
    const payload = {
        name: document.getElementById('s_name').value,
        email: document.getElementById('s_email').value,
        phone_no: document.getElementById('s_phone').value,
        password: document.getElementById('s_password').value,
        status: document.getElementById('s_status').value
    };
    if (id !== '') payload.student_id = id;
    
    try {
        const res = await fetch('php/admin/students.php', { method: id ? 'PUT' : 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
        const data = await res.json();
        if (res.ok) { closeModal('student-modal'); loadStudents(); } else alert(data.error);
    } catch(e) { alert(e); }
}

async function deleteStudent(id) {
    if (!confirm('Delete this student?')) return;
    const res = await fetch('php/admin/students.php', { method: 'DELETE', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ student_id: id }) });
    if (res.ok) loadStudents(); else alert((await res.json()).error);
}

/* ================= INSTRUCTORS ================= */
async function loadInstructors() {
    try {
        const res = await fetch('php/admin/instructors.php');
        const data = await res.json();
        if (res.ok) {
            adminInstructors = data;
            const list = document.getElementById('instructor-list-container');
            if(adminInstructors.length === 0) { list.innerHTML = '<p>No instructors found.</p>'; return; }
            let html = `<table><thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Department</th><th>Actions</th></tr></thead><tbody>`;
            adminInstructors.forEach(i => {
                html += `<tr>
                    <td>${i.instructor_id}</td>
                    <td><strong>${i.instructor_name}</strong></td>
                    <td>${i.email}</td>
                    <td>${i.department_name || 'Unassigned'}</td>
                    <td>
                        <button onclick="editInstructor('${escapeHtml(i)}')" style="background:#f39c12; margin-right: 5px;">Edit</button>
                        <button onclick="deleteInstructor(${i.instructor_id})" style="background:#c0392b;">Delete</button>
                    </td>
                </tr>`;
            });
            list.innerHTML = html + `</tbody></table>`;
        }
    } catch(e) { console.error(e); }
}

function openInstructorModal() {
    document.getElementById('i-modal-title').innerText = 'Add Instructor';
    document.getElementById('i_id').value = '';
    document.getElementById('i_name').value = '';
    document.getElementById('i_email').value = '';
    document.getElementById('i_dept').value = '';
    document.getElementById('instructor-modal').style.display = 'flex';
}

function editInstructor(dataJSON) {
    const i = JSON.parse(dataJSON);
    document.getElementById('i-modal-title').innerText = 'Edit Instructor';
    document.getElementById('i_id').value = i.instructor_id;
    document.getElementById('i_name').value = i.instructor_name;
    document.getElementById('i_email').value = i.email;
    document.getElementById('i_dept').value = i.department_id;
    document.getElementById('instructor-modal').style.display = 'flex';
}

async function saveInstructor() {
    const id = document.getElementById('i_id').value;
    const payload = {
        instructor_name: document.getElementById('i_name').value,
        email: document.getElementById('i_email').value,
        department_id: document.getElementById('i_dept').value
    };
    if (id !== '') payload.instructor_id = id;
    
    try {
        const res = await fetch('php/admin/instructors.php', { method: id ? 'PUT' : 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
        const data = await res.json();
        if (res.ok) { closeModal('instructor-modal'); loadInstructors(); } else alert(data.error);
    } catch(e) { alert(e); }
}

async function deleteInstructor(id) {
    if (!confirm('Delete this instructor?')) return;
    const res = await fetch('php/admin/instructors.php', { method: 'DELETE', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ instructor_id: id }) });
    if (res.ok) loadInstructors(); else alert((await res.json()).error);
}

/* ================= DEPARTMENTS ================= */
async function loadDepartments() {
    try {
        const res = await fetch('php/admin/departments.php');
        const data = await res.json();
        if (res.ok) {
            adminDepts = data;
            const list = document.getElementById('dept-list-container');
            if(adminDepts.length === 0) { list.innerHTML = '<p>No departments found.</p>'; return; }
            let html = `<table><thead><tr><th>ID</th><th>Department Name</th><th>Actions</th></tr></thead><tbody>`;
            adminDepts.forEach(d => {
                html += `<tr>
                    <td>${d.department_id}</td>
                    <td><strong>${d.department_name}</strong></td>
                    <td>
                        <button onclick="editDept('${escapeHtml(d)}')" style="background:#f39c12; margin-right: 5px;">Edit</button>
                        <button onclick="deleteDept(${d.department_id})" style="background:#c0392b;">Delete</button>
                    </td>
                </tr>`;
            });
            list.innerHTML = html + `</tbody></table>`;
        }
    } catch(e) { console.error(e); }
}

function openDeptModal() {
    document.getElementById('d-modal-title').innerText = 'Add Department';
    document.getElementById('d_id').value = '';
    document.getElementById('d_name').value = '';
    document.getElementById('dept-modal').style.display = 'flex';
}

function editDept(dataJSON) {
    const d = JSON.parse(dataJSON);
    document.getElementById('d-modal-title').innerText = 'Edit Department';
    document.getElementById('d_id').value = d.department_id;
    document.getElementById('d_name').value = d.department_name;
    document.getElementById('dept-modal').style.display = 'flex';
}

async function saveDept() {
    const id = document.getElementById('d_id').value;
    const payload = { department_name: document.getElementById('d_name').value };
    if (id !== '') payload.department_id = id;
    
    try {
        const res = await fetch('php/admin/departments.php', { method: id ? 'PUT' : 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
        const data = await res.json();
        if (res.ok) { closeModal('dept-modal'); loadDepartments(); } else alert(data.error);
    } catch(e) { alert(e); }
}

async function deleteDept(id) {
    if (!confirm('Delete this department?')) return;
    const res = await fetch('php/admin/departments.php', { method: 'DELETE', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ department_id: id }) });
    if (res.ok) loadDepartments(); else alert((await res.json()).error);
}

// Initialization calls
loadCourses();
loadStudents();
loadInstructors();
loadDepartments();
