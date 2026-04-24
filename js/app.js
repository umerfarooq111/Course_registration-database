// Dummy data
const courses = [
    { id: 1, code: 'MATH101', title: 'Math 101', prereq: 'None', credits: 3, capacity: 30, enrolled: 25 },
    { id: 2, code: 'PHYS201', title: 'Physics 201', prereq: 'Math 101', credits: 4, capacity: 25, enrolled: 20 },
    { id: 3, code: 'CS301', title: 'CS 301', prereq: 'CS 101', credits: 3, capacity: 40, enrolled: 35 },
    { id: 4, code: 'HIST101', title: 'History 101', prereq: 'None', credits: 2, capacity: 50, enrolled: 10 }
];

let enrolledCourses = [];

function renderCourses() {
    const container = document.getElementById('courses');
    container.innerHTML = `
        <table>
            <thead>
                <tr>
                    <th>Course Code</th>
                    <th>Course Name</th>
                    <th>Prerequisite</th>
                    <th>Credit Hours</th>
                    <th>Available Seats</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                ${courses.map(course => `
                    <tr>
                        <td>${course.code}</td>
                        <td>${course.title}</td>
                        <td>${course.prereq}</td>
                        <td>${course.credits}</td>
                        <td>${course.capacity - course.enrolled}</td>
                        <td>
                            <button onclick="enroll(${course.id})" ${course.enrolled >= course.capacity ? 'disabled' : ''}>
                                ${course.enrolled >= course.capacity ? 'Full' : 'Register'}
                            </button>
                        </td>
                    </tr>
                `).join('')}
            </tbody>
        </table>
    `;
}

function renderEnrolled() {
    const container = document.getElementById('enrolled');
    if (enrolledCourses.length === 0) {
        container.innerHTML = '<p>No courses registered yet.</p>';
        return;
    }
    container.innerHTML = `
        <table>
            <thead>
                <tr>
                    <th>Course Code</th>
                    <th>Course Name</th>
                    <th>Credit Hours</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                ${enrolledCourses.map(course => `
                    <tr>
                        <td>${course.code}</td>
                        <td>${course.title}</td>
                        <td>${course.credits}</td>
                        <td>
                            <button onclick="drop(${course.id})">Drop</button>
                        </td>
                    </tr>
                `).join('')}
            </tbody>
        </table>
    `;
}

function enroll(courseId) {
    const course = courses.find(c => c.id === courseId);
    if (!course || course.enrolled >= course.capacity) return;

    course.enrolled++;
    enrolledCourses.push(course);
    renderCourses();
    renderEnrolled();
}

function drop(courseId) {
    const index = enrolledCourses.findIndex(c => c.id === courseId);
    if (index === -1) return;

    const course = enrolledCourses[index];
    course.enrolled--;
    enrolledCourses.splice(index, 1);
    renderCourses();
    renderEnrolled();
}

// Init
renderCourses();
renderEnrolled();