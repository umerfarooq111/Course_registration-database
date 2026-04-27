let courses = [];
let enrolledCourses = [];

async function loadAvailableCourses() {
    try {
        const response = await fetch('php/get_courses.php');
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
        const response = await fetch('php/student/get_my_courses.php');
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
        <table aria-label="Available Courses">
            <thead>
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
        let btnDisabled = isEnrolled || isFull ? 'disabled' : '';
        let btnText = isEnrolled ? 'Enrolled' : (isFull ? 'Full' : 'Register');

        return `
                    <tr>
                        <td>${course.code}</td>
                        <td>${course.title}</td>
                        <td>${course.instructor}</td>
                        <td>${course.prereq}</td>
                        <td>${course.credits}</td>
                        <td>${course.capacity - course.enrolled}</td>
                        <td>
                            <button onclick="enroll(${course.id})" ${btnDisabled}>
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
        container.innerHTML = '<p>No active courses registered yet.</p>';
        return;
    }
    container.innerHTML = `
        <table aria-label="My Enrolled Courses">
            <thead>
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
                            <button style="background: #dc3545;" onclick="drop(${course.id})">Drop</button>
                        </td>
                    </tr>
                `).join('')}
            </tbody>
        </table>
    `;
}

async function enroll(sectionId) {
    try {
        const response = await fetch('php/student/enroll.php', {
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
        const response = await fetch('php/student/drop.php', {
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

// Init
loadAvailableCourses();
loadEnrolledCourses();