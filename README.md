# Course Registration & University Management System
# UMER FAROOQ (23P-0039) & MUHAMMAD IMRAN (23P-0683) 

https://github.com/umerfarooq111/Course_registration-database
A comprehensive, web-based platform designed to streamline university operations, from course scheduling and instructor management to student enrollment and academic tracking. This system provides a robust interface for both administrators and students.

---

## Features

### Administrator Portal
*   **Real-time Statistics:** Instant overview of total students, courses, instructors, and departments.
*   **Department Management:** Create and organize academic departments.
*   **Instructor Management:** Manage faculty profiles and assign them to departments.
*   **Course Inventory:** Define courses with credit hours, maximum capacity, and prerequisites.
*   **Course Offerings:** Schedule specific course sections for different semesters.
*   **Student Administration:** Manage student records, track academic progress (CGPA/Credits).
*   **Notice Board:** Post institutional announcements and updates directly to the student dashboard.

### Student Portal
*   **Academic Dashboard:** View personal academic standing, including current semester, CGPA, and completed credits.
*   **Course Enrollment:** Browse available course offerings and enroll in sections based on the current semester.
*   **Institutional Notices:** Stay updated with the latest news and announcements from the administration.
*   **Secure Login:** Individual student accounts for personalized data management.

---

## Tech Stack

*   **Frontend:** HTML5, CSS3 (Modern UI with Glassmorphism & Responsive Design), JavaScript (Vanilla ES6).
*   **Backend:** PHP 8.x (Modular architecture).
*   **Database:** MySQL / MariaDB (Relational schema).
*   **Environment:** XAMPP / Apache Server.

---

## Prerequisites

Before you begin, ensure you have the following installed:
*   [XAMPP](https://www.apachefriends.org/index.html) (Apache & MySQL).
*   A modern web browser (Chrome, Firefox, Edge).

---

## Installation & Setup

1.  **Clone the Repository:**
    Place the project folder inside your XAMPP `htdocs` directory:
    ```bash
    C:\xampp\htdocs\course_registration_system
    ```

2.  **Database Setup:**
    *   Open XAMPP Control Panel and start **Apache** and **MySQL**.
    *   Go to [http://localhost/phpmyadmin/](http://localhost/phpmyadmin/).
    *   Create a new database named **`university.db`**.
    *   Import the SQL files in the following order:
        1.  Initial Schema (Any base table definitions).
        2.  `update_schema.sql` (Schema enhancements).
        3.  `migration.sql` (Semester logic & Offerings).
        4.  `dummy_data.sql` (Initial admin, instructors, and courses).

3.  **Configuration:**
    *   Verify the database credentials in `php/db.php`:
        ```php
        $host = 'localhost';
        $user = 'root';
        $password = '';
        $database = 'university.db';
        ```

4.  **Run the Application:**
    *   Open your browser and navigate to:
        `http://localhost/course_registration_system/`

---

## Default Credentials

| Role | Email | Password |
| :--- | :--- | :--- |
| **Admin** | `admin@university.com` | `admin123` |
| **Student** | *(Use existing student email from DB)* | `password` |

---

## Project Structure

```text
course_registration_system/
├── css/                    # Custom stylesheets (Dashboard, Login, Admin)
├── js/                     # Frontend logic (AJAX, Modals, UI Interactions)
├── php/
│   ├── admin/             # Admin-specific API endpoints
│   ├── auth/              # Login & Session management
│   ├── student/           # Student-specific API endpoints
│   └── db.php             # Database connection configuration
├── admin_dashboard.html    # Admin main interface
├── dashboard.html          # Student main interface
├── index.html              # Landing/Login redirect page
├── ERD.png                 # Database Entity Relationship Diagram
└── *.sql                   # Database migrations and seed data
```

---

## Database Schema (ERD)
You can find the detailed Entity Relationship Diagram in the root directory as `ERD.png`. It illustrates the relationships between Students, Courses, Instructors, and Enrollments.

---

## License
This project is for educational purposes. Feel free to modify and adapt it for your own institutional needs.
