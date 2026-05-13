let courses = [];
let enrolledCourses = [];
let studentProfile = null;

function getApiUrl(path) {
    if (window.location.protocol === 'file:') {
        throw new Error('Please open this page using your local web server, for example http://localhost/course_registration_system/index.html');
    }
    return new URL(path, window.location.href).href;
}

async function loadAvailableCourses() {
    try {
        const response = await fetch(getApiUrl('php/get_courses.php'));
        if (!response.ok) throw new Error('Failed to fetch courses');
        const data = await response.json();

        courses = data.map(course => ({
            id: parseInt(course.section_id),
            course_id: parseInt(course.course_id),
            code: 'CRS-' + course.course_id,
            title: course.title,
            instructor: course.instructor_name,
            prereq: course.prereq || 'None',
            credits: parseInt(course.credit_hr),
            capacity: parseInt(course.max_capacity),
            enrolled: parseInt(course.enrollment_count)
        }));

        renderCourses();
    } catch (error) {
        console.error('Error:', error);
        document.getElementById('courses').innerHTML = `<p style="color:red">Failed to load courses. Reason: ${error.message}</p>`;
    }
}

async function loadEnrolledCourses() {
    try {
        const response = await fetch(getApiUrl('php/student/get_my_courses.php'));
        if (!response.ok) {
            // Authentication issue or server error
            if (response.status === 401) {
                window.location.href = 'login.php';
            }
            throw new Error('Failed to fetch enrolled courses');
        }
        const data = await response.json();

        enrolledCourses = data.map(course => ({
            id: parseInt(course.section_id),
            code: 'CRS-' + course.section_id,
            title: course.title,
            credits: parseInt(course.credit_hr),
            status: course.status
        }));

        renderEnrolled();
        renderCourses(); // Re-render available courses to update button states
    } catch (error) {
        console.error('Error:', error);
        document.getElementById('enrolled').innerHTML = '<p style="color:red">Failed to load your enrolled courses.</p>';
    }
}

function renderCourses() {
    const container = document.getElementById('courses');
    container.innerHTML = `
        <table class="table table-hover align-middle mb-0" aria-label="Available Courses">
            <thead class="table-light">
                <tr>
                    <th>Course Code</th>
                    <th>Course Name</th>
                    <th>Instructor</th>
                    <th>Prerequisite</th>
                    <th>Credit Hours</th>
                    <th>Available Seats</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                ${courses.map(course => {
        // Check if already registered
        const isEnrolled = enrolledCourses.some(e => e.id === course.id && e.status === 'REGISTERED');
        const isFull = course.enrolled >= course.capacity;
        const limitReached = studentProfile && studentProfile.current_registrations >= studentProfile.registration_limit;

        let btnDisabled = isEnrolled || isFull || limitReached ? 'disabled' : '';
        let btnText = isEnrolled ? 'Enrolled' : (isFull ? 'Full' : (limitReached ? 'Limit Reached' : 'Register'));
        let btnClass = isEnrolled ? 'btn-secondary' : (isFull ? 'btn-secondary' : (limitReached ? 'btn-danger' : 'btn-primary'));

        return `
                    <tr>
                        <td>${course.code}</td>
                        <td>${course.title}</td>
                        <td>${course.instructor}</td>
                        <td>${course.prereq}</td>
                        <td>${course.credits}</td>
                        <td>${course.capacity - course.enrolled}</td>
                        <td>
                            <button class="btn ${btnClass} btn-sm" onclick="enroll(${course.id})" ${btnDisabled}>
                                ${btnText}
                            </button>
                        </td>
                    </tr>
                `}).join('')}
            </tbody>
        </table>
    `;
}

function renderEnrolled() {
    const container = document.getElementById('enrolled');
    // Only show active courses
    const activeEnrolled = enrolledCourses.filter(c => c.status === 'REGISTERED');

    if (activeEnrolled.length === 0) {
        container.innerHTML = '<div class="p-5 text-center text-muted">No active courses registered yet.</div>';
        return;
    }
    container.innerHTML = `
        <table class="table table-hover align-middle mb-0" aria-label="My Enrolled Courses">
            <thead class="table-light">
                <tr>
                    <th>Course Code</th>
                    <th>Course Name</th>
                    <th>Credit Hours</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                ${activeEnrolled.map(course => `
                    <tr>
                        <td>${course.code}</td>
                        <td>${course.title}</td>
                        <td>${course.credits}</td>
                        <td>
                            <button class="btn btn-danger btn-sm" onclick="drop(${course.id})">Drop</button>
                        </td>
                    </tr>
                `).join('')}
            </tbody>
        </table>
    `;
}

async function enroll(sectionId) {
    try {
        const response = await fetch(getApiUrl('php/student/enroll.php'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ section_id: sectionId })
        });

        const result = await response.json();

        if (!response.ok) {
            alert(result.error || 'Failed to enroll');
            return;
        }

        alert(result.message || 'Successfully enrolled!');
        // Refresh data dynamically
        await loadAvailableCourses();
        await loadEnrolledCourses();

    } catch (error) {
        console.error('Enroll Error:', error);
        alert('An error occurred while enrolling.');
    }
}

async function drop(sectionId) {
    if (!confirm('Are you sure you want to drop this course?')) return;

    try {
        const response = await fetch(getApiUrl('php/student/drop.php'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ section_id: sectionId })
        });

        const result = await response.json();

        if (!response.ok) {
            alert(result.error || 'Failed to drop course');
            return;
        }

        alert(result.message || 'Course dropped successfully!');
        // Refresh data dynamically
        await loadAvailableCourses();
        await loadEnrolledCourses();

    } catch (error) {
        console.error('Drop Error:', error);
        alert('An error occurred while dropping the course.');
    }
}

async function loadProfile() {
    try {
        const response = await fetch(getApiUrl('php/student/get_profile.php'));
        if (response.ok) {
            const data = await response.json();
            studentProfile = data;
            document.getElementById('st-name').innerText = data.name;
            document.getElementById('st-email').innerText = data.email;
            document.getElementById('st-semester').innerText = data.semester;
            document.getElementById('reg-semester').innerText = data.semester;
            document.getElementById('st-status').innerText = data.status;

            // Set Badge Color based on status
            const badge = document.getElementById('st-status');
            badge.className = 'badge fs-6 mb-2 px-3 py-2';
            if (data.status === 'CURRENT') badge.classList.add('bg-success');
            else if (data.status === 'DROPPED') badge.classList.add('bg-danger');
            else if (data.status === 'GRADUATED') badge.classList.add('bg-primary');
            else badge.classList.add('bg-secondary');

            document.getElementById('sum-cgpa').innerText = data.cgpa || '0.00';

            // Registration Limit Data
            document.getElementById('sum-reg-count').innerText = data.current_registrations || 0;
            document.getElementById('sum-reg-limit').innerText = data.registration_limit || 5;

            // Re-calculate registered courses and credits
            const activeEnrolled = enrolledCourses.filter(c => c.status === 'REGISTERED');
            document.getElementById('sum-courses').innerText = activeEnrolled.length;

            const totalCredits = activeEnrolled.reduce((sum, course) => sum + course.credits, 0);
            document.getElementById('sum-credits').innerText = totalCredits + parseInt(data.credits_completed || 0);
        }
    } catch (e) {
        console.error('Profile load error:', e);
    }
}

// Intercept loads to update profile too
const origLoadEnrolled = loadEnrolledCourses;
loadEnrolledCourses = async function () {
    await origLoadEnrolled();
    await loadProfile();
};

async function loadNotices() {
    try {
        const response = await fetch(getApiUrl('php/student/notices.php'));
        if (!response.ok) throw new Error('Failed to fetch notices');
        const notices = await response.json();
        const container = document.getElementById('student-notice-container');
        if (!container) return;
        if (notices.length === 0) {
            container.innerHTML = '<div class="col-12 text-center py-4 text-muted">No notices at this time.</div>';
            return;
        }
        let html = '';
        notices.forEach(n => {
            const date = new Date(n.created_at).toLocaleDateString();
            html += `
                <div class="col-md-6 mb-3">
                    <div class="card h-100 border-0 shadow-sm" style="border-left: 4px solid #3498db !important;">
                        <div class="card-body">
                            <h6 class="fw-bold text-primary mb-2">${n.title}</h6>
                            <p class="small mb-2 text-dark">${n.content}</p>
                            <div class="text-muted" style="font-size: 0.75rem;"><i class="bi bi-calendar3"></i> Posted on ${date}</div>
                        </div>
                    </div>
                </div>`;
        });
        container.innerHTML = html;
    } catch (error) {
        console.error('Notices load error:', error);
    }
}

// Init
loadAvailableCourses();
loadEnrolledCourses();
loadNotices();