let adminCourses = [];
let adminStudents = [];
let adminInstructors = [];
let adminDepts = [];

function switchTab(tabName) {
    const tabs = ['dashboard', 'courses', 'students', 'instructors', 'departments', 'offerings'];
    tabs.forEach(tab => {
        const el = document.getElementById('tab-' + tab);
        if (el) {
            el.classList.remove('d-block');
            el.classList.add('d-none');
        }
    });
    
    const activeTab = document.getElementById('tab-' + tabName);
    if (activeTab) {
        activeTab.classList.remove('d-none');
        activeTab.classList.add('d-block');
    }
    
    if (event && event.currentTarget) {
        const links = document.getElementById('tab-links').getElementsByTagName('a');
        for(let i = 0; i < links.length; i++) {
            links[i].classList.remove('active');
        }
        event.currentTarget.classList.add('active');
        const titleText = event.currentTarget.innerText.trim();
        document.getElementById('page-title').innerText = titleText;
    }
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
            let html = `<table class="table table-bordered table-striped table-hover align-middle">
                <thead class="table-light"><tr><th>ID</th><th>Course Title</th><th>Type</th><th>Prereq ID</th><th>Department</th><th>Credits</th><th>Capacity</th><th>Actions</th></tr></thead><tbody>`;
            adminCourses.forEach(c => {
                html += `<tr>
                    <td>${c.course_id}</td>
                    <td><strong>${c.title}</strong></td>
                    <td>${c.course_type}</td>
                    <td>${c.prereq_id || 'None'}</td>
                    <td>${c.department_name} (ID: ${c.department_id})</td>
                    <td>${c.credit_hr}</td>
                    <td>${c.max_capacity}</td>
                    <td>
                        <button onclick="editCourse('${escapeHtml(c)}')" class="btn btn-warning btn-sm me-1"><i class="bi bi-pencil-square"></i> Edit</button>
                        <button onclick="deleteCourse(${c.course_id})" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i> Delete</button>
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
    document.getElementById('c_type').value = 'Core';
    document.getElementById('c_prereq').value = '';
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
    document.getElementById('c_type').value = c.course_type;
    document.getElementById('c_prereq').value = c.prereq_id || '';
    document.getElementById('course-modal').style.display = 'flex';
}

async function saveCourse() {
    const id = document.getElementById('c_id').value;
    const payload = {
        title: document.getElementById('c_title').value,
        credit_hr: document.getElementById('c_credits').value,
        max_capacity: document.getElementById('c_capacity').value,
        department_id: document.getElementById('c_dept').value,
        course_type: document.getElementById('c_type').value,
        prereq_id: document.getElementById('c_prereq').value || 0
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
            let html = `<table class="table table-bordered table-striped table-hover align-middle">
                <thead class="table-light"><tr><th>ID</th><th>Name</th><th>Email</th><th>Status</th><th>Semester</th><th>CGPA</th><th>Cr. Compl.</th><th>Actions</th></tr></thead><tbody>`;
            adminStudents.forEach(s => {
                html += `<tr>
                    <td>STD-${s.student_id}</td>
                    <td><strong>${s.name}</strong></td>
                    <td>${s.email}</td>
                    <td>${s.status}</td>
                    <td>${s.semester}</td>
                    <td>${s.cgpa}</td>
                    <td>${s.credits_completed}</td>
                    <td>
                        <button onclick="editStudent('${escapeHtml(s)}')" class="btn btn-warning btn-sm me-1"><i class="bi bi-pencil-square"></i> Edit</button>
                        <button onclick="deleteStudent(${s.student_id})" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i> Delete</button>
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
    document.getElementById('s_semester').value = 1;
    document.getElementById('s_cgpa').value = 0.00;
    document.getElementById('s_credits').value = 0;
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
    document.getElementById('s_semester').value = s.semester || 1;
    document.getElementById('s_cgpa').value = s.cgpa || 0.00;
    document.getElementById('s_credits').value = s.credits_completed || 0;
    document.getElementById('student-modal').style.display = 'flex';
}

async function saveStudent() {
    const id = document.getElementById('s_id').value;
    const payload = {
        name: document.getElementById('s_name').value,
        email: document.getElementById('s_email').value,
        phone_no: document.getElementById('s_phone').value,
        password: document.getElementById('s_password').value,
        status: document.getElementById('s_status').value,
        semester: document.getElementById('s_semester').value,
        cgpa: document.getElementById('s_cgpa').value,
        credits_completed: document.getElementById('s_credits').value
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
            let html = `<table class="table table-bordered table-striped table-hover align-middle">
                <thead class="table-light"><tr><th>ID</th><th>Name</th><th>Email</th><th>Department</th><th>Actions</th></tr></thead><tbody>`;
            adminInstructors.forEach(i => {
                html += `<tr>
                    <td>${i.instructor_id}</td>
                    <td><strong>${i.instructor_name}</strong></td>
                    <td>${i.email}</td>
                    <td>${i.department_name || 'Unassigned'}</td>
                    <td>
                        <button onclick="editInstructor('${escapeHtml(i)}')" class="btn btn-warning btn-sm me-1"><i class="bi bi-pencil-square"></i> Edit</button>
                        <button onclick="deleteInstructor(${i.instructor_id})" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i> Delete</button>
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
            let html = `<table class="table table-bordered table-striped table-hover align-middle">
                <thead class="table-light"><tr><th>ID</th><th>Department Name</th><th>Actions</th></tr></thead><tbody>`;
            adminDepts.forEach(d => {
                html += `<tr>
                    <td>${d.department_id}</td>
                    <td><strong>${d.department_name}</strong></td>
                    <td>
                        <button onclick="editDept('${escapeHtml(d)}')" class="btn btn-warning btn-sm me-1"><i class="bi bi-pencil-square"></i> Edit</button>
                        <button onclick="deleteDept(${d.department_id})" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i> Delete</button>
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

/* ================= OFFERINGS ================= */
async function loadOfferings() {
    try {
        const res = await fetch('php/admin/offerings.php');
        const data = await res.json();
        if (res.ok) {
            const list = document.getElementById('offering-list-container');
            if(data.length === 0) { list.innerHTML = '<p>No offerings found.</p>'; return; }
            let html = `<table class="table table-bordered table-striped table-hover align-middle">
                <thead class="table-light"><tr><th>Offering ID</th><th>Course ID</th><th>Semester/Batch</th><th>Instructor</th><th>Actions</th></tr></thead><tbody>`;
            data.forEach(o => {
                html += `<tr>
                    <td>${o.offering_id}</td>
                    <td>${o.course_id} - ${o.title}</td>
                    <td>Semester ${o.semester}</td>
                    <td>${o.instructor_name || 'TBA'}</td>
                    <td>
                        <button onclick="editOffering('${escapeHtml(o)}')" class="btn btn-warning btn-sm me-1"><i class="bi bi-pencil-square"></i> Edit</button>
                        <button onclick="deleteOffering(${o.offering_id})" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i> Delete</button>
                    </td>
                </tr>`;
            });
            list.innerHTML = html + `</tbody></table>`;
        }
    } catch(e) { console.error(e); }
}

function populateOfferingDropdowns() {
    const courseSelect = document.getElementById('o_course');
    courseSelect.innerHTML = '<option value="">Select a Course</option>';
    adminCourses.forEach(c => {
        courseSelect.innerHTML += `<option value="${c.course_id}">${c.course_id} - ${c.title}</option>`;
    });

    const instructorSelect = document.getElementById('o_instructor');
    instructorSelect.innerHTML = '<option value="">Select an Instructor</option>';
    adminInstructors.forEach(i => {
        instructorSelect.innerHTML += `<option value="${i.instructor_id}">${i.instructor_name} (${i.department_name || 'No Dept'})</option>`;
    });
}

function openOfferingModal() {
    document.getElementById('o-modal-title').innerText = 'Add Course Offering';
    document.getElementById('o_id').value = '';
    populateOfferingDropdowns();
    document.getElementById('o_course').value = '';
    document.getElementById('o_instructor').value = '';
    document.getElementById('o_semester').value = '';
    document.getElementById('offering-modal').style.display = 'flex';
}

function editOffering(dataJSON) {
    const o = JSON.parse(dataJSON);
    document.getElementById('o-modal-title').innerText = 'Edit Course Offering';
    document.getElementById('o_id').value = o.offering_id;
    populateOfferingDropdowns();
    document.getElementById('o_course').value = o.course_id;
    document.getElementById('o_instructor').value = o.instructor_id || '';
    document.getElementById('o_semester').value = o.semester;
    document.getElementById('offering-modal').style.display = 'flex';
}

async function saveOffering() {
    const id = document.getElementById('o_id').value;
    const payload = { 
        course_id: document.getElementById('o_course').value,
        semester: document.getElementById('o_semester').value,
        instructor_id: document.getElementById('o_instructor').value
    };
    if (id !== '') payload.offering_id = id;
    
    try {
        const res = await fetch('php/admin/offerings.php', { method: id ? 'PUT' : 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
        const data = await res.json();
        if (res.ok) { closeModal('offering-modal'); loadOfferings(); } else alert(data.error);
    } catch(e) { alert(e); }
}

async function deleteOffering(id) {
    if (!confirm('Delete this offering?')) return;
    const res = await fetch('php/admin/offerings.php', { method: 'DELETE', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ offering_id: id }) });
    if (res.ok) loadOfferings(); else alert((await res.json()).error);
}

async function loadDashboardStats() {
    try {
        const res = await fetch('php/admin/dashboard_stats.php');
        if (res.status === 401) return;
        const data = await res.json();
        
        if (res.ok) {
            document.getElementById('stat-students').innerText = data.total_students;
            document.getElementById('stat-courses').innerText = data.total_courses;
            document.getElementById('stat-instructors').innerText = data.total_instructors;
        }
    } catch(e) { console.error(e); }
}

function openNoticeModal() {
    const modal = document.getElementById('notice-modal');
    if (!modal) {
        console.error('Notice modal element not found!');
        return;
    }
    const titleInput = document.getElementById('n_title');
    const contentInput = document.getElementById('n_content');
    
    if (titleInput) titleInput.value = '';
    if (contentInput) contentInput.value = '';
    
    modal.style.display = 'flex';
}

async function saveNotice() {
    const title = document.getElementById('n_title').value;
    const content = document.getElementById('n_content').value;
    if (!title || !content) { alert('Please fill all fields'); return; }

    try {
        const res = await fetch('php/admin/notices.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ title, content })
        });
        const data = await res.json();
        if (res.ok) {
            closeModal('notice-modal');
            loadNotices();
        } else alert(data.error);
    } catch (e) { alert(e); }
}

async function loadNotices() {
    try {
        const res = await fetch('php/admin/notices.php');
        const notices = await res.json();
        const container = document.getElementById('notice-board-container');
        if (!container) return;
        if (notices.length === 0) {
            container.innerHTML = '<div class="col-12 text-center py-4 text-muted">No notices posted yet.</div>';
            return;
        }
        let html = '';
        notices.forEach(n => {
            const date = new Date(n.created_at).toLocaleString();
            html += `
                <div class="col-md-6 mb-3">
                    <div class="card h-100 border-light shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <h6 class="fw-bold text-primary">${n.title}</h6>
                                <button onclick="deleteNotice(${n.id})" class="btn btn-sm btn-link text-danger p-0"><i class="bi bi-trash"></i></button>
                            </div>
                            <p class="small mb-2 text-dark">${n.content}</p>
                            <div class="text-muted" style="font-size: 0.75rem;"><i class="bi bi-calendar3"></i> ${date}</div>
                        </div>
                    </div>
                </div>`;
        });
        container.innerHTML = html;
    } catch (e) { console.error(e); }
}

async function deleteNotice(id) {
    if (!confirm('Delete this notice?')) return;
    try {
        const res = await fetch('php/admin/notices.php', {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        if (res.ok) loadNotices();
    } catch (e) { alert(e); }
}

// Initialization calls
loadCourses();
loadStudents();
loadInstructors();
loadDepartments();
loadOfferings();
loadDashboardStats();
loadNotices();
