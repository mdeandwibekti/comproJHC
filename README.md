# comproJHC Web Project

This project is a comprehensive web application for a healthcare or medical center (JHC), featuring both a public-facing website for visitors and a robust administration panel for managing content and operations.

## Table of Contents
- [Features](#features)
  - [Public Website Features](#public-website-features)
  - [Admin Panel Features](#admin-panel-features)
- [Technologies Used](#technologies-used)
- [Installation](#installation)
- [Usage](#usage)
- [Project Structure Overview](#project-structure-overview)
- [Contributing](#contributing)
- [License](#license)

## Features

### Public Website Features

The public website is designed to provide information to visitors, allow them to interact with the facility, and showcase its services.

*   **Homepage (`index.php`):**
    *   Dynamic banners/sliders (managed via Admin Panel).
    *   Overview of services, doctors, news, and facilities.
    *   Call-to-action sections (e.g., "Make an Appointment", "Apply for a Job").
*   **Doctor Profiles (`doctor_details.php`):**
    *   Detailed profiles for each doctor, including specialization, biography, and contact information.
    *   List of all doctors with search/filter capabilities.
*   **News & Articles (`news_article.php`):**
    *   Display latest news, health articles, and announcements.
    *   Individual news article pages with full content.
*   **Career/Job Application (`apply.php`):**
    *   List available job vacancies.
    *   Online application form for submitting resumes/CVs.
    *   API endpoint (`public/api/submit_application.php`) for processing applications.
*   **Appointment Booking:**
    *   Form for users to request appointments.
    *   API endpoint (`public/api/submit_appointment.php`) for handling appointment submissions.
*   **Facilities & Departments:**
    *   Information about various facilities and medical departments.
*   **MCU Packages:**
    *   Details on available Medical Check-Up (MCU) packages.
*   **Contact Information:**
    *   Display contact details, location, and possibly a contact form.
*   **Responsive Design:**
    *   Optimized for various devices (desktop, tablet, mobile).

### Admin Panel Features

The administration panel provides a comprehensive content management system (CMS) for managing all aspects of the website. Accessible via `public/admin/index.php`.

*   **Dashboard (`dashboard.php`):**
    *   Overview of key metrics (e.g., new applicants, appointments, news count).
*   **Applicant Management (`applicants.php`):**
    *   View and manage job applications.
    *   Update applicant status (e.g., pending, reviewed, accepted, rejected) via `public/api/update_applicant_status.php`.
*   **Appointment Management (`appointments.php`):**
    *   View and manage submitted appointment requests.
*   **Doctor Management (`doctors.php`, `doctor_edit.php`):**
    *   Add, edit, and delete doctor profiles.
    *   Manage doctor details, specializations, and images.
*   **News Management (`news.php`, `news_edit.php`):**
    *   Create, edit, and publish news articles and announcements.
    *   Manage news categories and images.
*   **Career Management (`careers.php`, `career_edit.php`):**
    *   Add, edit, and remove job vacancies.
    *   Manage job descriptions, requirements, and application deadlines.
*   **Department Management (`departments.php`, `department_edit.php`):**
    *   Manage medical departments and their descriptions.
*   **Facility Management (`facilities.php`, `facility_edit.php`):**
    *   Manage information about various facilities.
*   **MCU Package Management (`mcu_packages.php`, `mcu_package_edit.php`):**
    *   Add, edit, and delete Medical Check-Up packages.
    *   Manage package details, inclusions, and pricing.
*   **Banner Management (`banners.php`, `banner_edit.php`):**
    *   Upload and manage homepage banners/sliders.
*   **Website Settings:**
    *   **Contact Settings (`contact_settings.php`):** Update contact information (phone, email, address).
    *   **Logo Settings (`logo_settings.php`):** Upload and change website logos.
    *   **Popup Settings (`popup_settings.php`):** Manage pop-up messages or announcements.
*   **Virtual Room (`virtual_room.php`):**
    *   (Specific functionality needs further context, but likely related to managing virtual consultation links or resources).
*   **User Authentication:**
    *   Secure login/logout for administrators.

## Technologies Used

*   **Backend:** PHP
*   **Database:** MySQL (indicated by `applicants.sql`)
*   **Frontend:**
    *   HTML5, CSS3, JavaScript
    *   Bootstrap (indicated by `build/vendors/bootstrap`)
    *   Sass/SCSS (indicated by `src/scss`)
    *   Pug (indicated by `src/pug`)
    *   Gulp.js (for frontend asset compilation: `gulpfile.js`, `gulp/`)
*   **Libraries/Vendors:**
    *   PHPMailer (for email functionality, `vendor/phpmailer`)
    *   Popper.js, Feather Icons, Font Awesome, Is.js, Lodash, Prism, Rellax (from `build/vendors` and `public/vendors`)

## Installation

1.  **Clone the repository:**
    ```bash
    git clone <repository_url> comproJHC
    cd comproJHC
    ```
2.  **Set up Web Server:**
    *   Place the `comproJHC` folder in your web server's document root (e.g., `C:\xampp\htdocs\` for XAMPP).
3.  **Database Setup:**
    *   Create a MySQL database (e.g., `comprojhc`).
    *   Import the `applicants.sql` file into your newly created database. This file likely contains the schema for applicant data and potentially other core tables.
4.  **Configure Database Connection:**
    *   Edit `config.php` to update your database connection details (hostname, username, password, database name).
5.  **Install PHP Dependencies:**
    *   Ensure Composer is installed.
    *   Run `composer install` in the project root to install PHP dependencies (e.g., PHPMailer).
6.  **Install Node.js Dependencies (for frontend build):**
    *   Ensure Node.js and npm are installed.
    *   Run `npm install` in the project root to install frontend build tools and libraries.
7.  **Build Frontend Assets:**
    *   Run `gulp build` (or similar command defined in `gulpfile.js`) to compile SCSS, Pug, and JS files into the `build/` and `public/assets/` directories.

## Usage

*   **Public Website:** Access the public website by navigating to `http://localhost/comproJHC/` (or your configured domain).
*   **Admin Panel:** Access the admin panel by navigating to `http://localhost/comproJHC/public/admin/` (or your configured domain). Log in with appropriate credentials.

## Project Structure Overview

*   `.babelrc`, `.eslintrc`, `.gitignore`, `.prettierignore`: Configuration files for development tools.
*   `applicants.sql`: Database schema and initial data for applicants.
*   `composer.json`, `composer.lock`: PHP dependency management (Composer).
*   `config.php`: Main configuration file (e.g., database connection).
*   `gulpfile.js`, `gulp/`: Gulp.js build system for frontend assets.
*   `index.php`: Main entry point for the public website.
*   `package.json`, `package-lock.json`: Node.js dependency management (npm).
*   `README.md`: This file.
*   `build/`: Compiled frontend assets (CSS, JS, images, vendor libraries).
*   `node_modules/`: Node.js dependencies.
*   `public/`:
    *   `apply.php`, `doctor_details.php`, `news_article.php`: Public-facing pages.
    *   `admin/`: Admin panel files.
    *   `api/`: API endpoints for applications, appointments, etc.
    *   `assets/`: Publicly accessible static assets (CSS, JS, images, fonts).
    *   `layout/`: Common layout files (headers, footers).
    *   `uploads/`: Directory for uploaded files (e.g., CVs).
    *   `vendors/`: Frontend vendor libraries (copied during build).
*   `src/`: Source files for frontend development (Pug, SCSS, JS).
*   `vendor/`: PHP dependencies (managed by Composer).

## Contributing

Contributions are welcome! Please follow standard Git workflow: fork the repository, create a new branch, make your changes, and submit a pull request.

## License

[Specify your project's license here, e.g., MIT, Apache 2.0, etc.]
