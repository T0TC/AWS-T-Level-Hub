# Assets Log – Amazon T-Level Hub

## Purpose
This log records all assets used in the development of the Amazon T-Level Hub digital solution, including their sources, descriptions, and intended purposes. This ensures proper attribution and compliance with legal and regulatory guidelines.

---

## Digital Media Assets

### 1. Amazon-Web-Services-AWS-Logo.png
- **Source**: Amazon Web Services (AWS) Brand Assets
- **URL**: https://aws.amazon.com/brand/
- **Description**: Official AWS logo used as the site branding element.
- **Intended Purpose**: Displayed in the navigation header, footer, and as the primary branding element to establish the Amazon/AWS association with the T-Level Hub.
- **License**: AWS Trademark Guidelines – Used in accordance with Amazon's trademark policy for non-commercial educational purposes.

### 2. hero-bg.mp4
- **Source**: Stock video / custom-created for this project
- **Description**: Background video montage showing students in educational and technological settings.
- **Intended Purpose**: Used as the background video in the cards section (hero area) on the homepage to create an engaging, modern visual experience.
- **License**: Royalty-free / created for educational project use.

### 3. students.mp4
- **Source**: Stock video footage
- **Description**: Video clip showing students working on computers in a classroom-like setting.
- **Intended Purpose**: Used in the "Students" information section to visually reinforce the student-focused content.
- **License**: Royalty-free educational use.

### 4. teachers.mp4
- **Source**: Stock video footage
- **Description**: Video clip showing teachers and students in an educational environment.
- **Intended Purpose**: Used in the "Teachers & Schools" information section.
- **License**: Royalty-free educational use.

### 5. parents&guardians.mp4
- **Source**: Stock video footage
- **Description**: Video clip showing family and educational support contexts.
- **Intended Purpose**: Used in the "Parents & Guardians" information section.
- **License**: Royalty-free educational use.

---

## Front-End Code Assets

### 6. index.html (Homepage)
- **Source**: Custom-developed for this project
- **Description**: Main landing page for the Amazon T-Level Hub. Contains navigation, course cards, placement information, career opportunities, information sections for students/teachers/parents, accessibility features, chatbot, and modals for settings and contacts.
- **Intended Purpose**: Primary user-facing interface for the T-Level portal.

### 7. application.html (Application Form)
- **Source**: Custom-developed for this project
- **Description**: Multi-step application form with client-side validation. Collects personal details, T-Level course selection, placement preferences, supporting information, and declarations. Generates a reference number upon submission.
- **Intended Purpose**: Allow students to apply for T-Level placements with Amazon.

### 8. resources.html (Resource Centre)
- **Source**: Custom-developed for this project
- **Description**: Searchable resource centre with course information, study materials, AWS training links, career profiles, and sector-based browsing.
- **Intended Purpose**: Provide students and educators with curated resources for each T-Level pathway.

### 9. style.css (Stylesheet)
- **Source**: Custom-developed for this project
- **Description**: Complete stylesheet with dark theme, light theme, and high-contrast accessibility modes. Includes responsive design for mobile and desktop.
- **Intended Purpose**: Define the visual design and responsive layout of the entire portal.

### 10. script.js (Front-End JavaScript)
- **Source**: Custom-developed for this project
- **Description**: Comprehensive JavaScript implementing language switching (English/German), accessibility controls (font size, high contrast, reduced motion, underline links, light theme), chatbot with knowledge base, course search, scroll reveal animations, and modal management.
- **Intended Purpose**: Provide interactivity, accessibility, and offline chatbot functionality.

---

## Back-End Code Assets (PHP – Custom-Developed)

### 11. backend/config/database.php
- **Source**: Custom-developed for this project
- **Description**: Database configuration class using SQLite with PDO. Implements singleton pattern, WAL mode for concurrent access, and prepared statements for SQL injection prevention.
- **Intended Purpose**: Secure database connection management.

### 12. backend/config/app.php
- **Source**: Custom-developed for this project
- **Description**: Central configuration file with CORS headers, response helpers, rate limiting constants, course data, and authentication middleware.
- **Intended Purpose**: Application-wide configuration and utility functions.

### 13. backend/api/submit_application.php
- **Source**: Custom-developed for this project
- **Description**: POST endpoint for T-Level application submission. Validates all required fields, checks age eligibility (16-19), sanitizes input, stores in database, and returns a unique reference number.
- **Intended Purpose**: Process and store student applications securely.

### 14. backend/api/submit_contact.php
- **Source**: Custom-developed for this project
- **Description**: POST endpoint for contact form submissions. Validates email, subject, and message, then stores enquiries in the database.
- **Intended Purpose**: Handle contact form submissions from users.

### 15. backend/api/courses.php
- **Source**: Custom-developed for this project
- **Description**: GET endpoint returning course listings and placement locations.
- **Intended Purpose**: Serve course data to the frontend resource centre.

### 16. backend/api/auth.php
- **Source**: Custom-developed for this project
- **Description**: Authentication API with login, logout, and token verification. Uses bcrypt password hashing (cost 12), rate-limited login attempts (5 per 15 minutes), and token-based sessions (24-hour expiry).
- **Intended Purpose**: Secure admin authentication.

### 17. backend/api/admin_applications.php
- **Source**: Custom-developed for this project
- **Description**: CRUD API for admin application management. Supports listing with search/filter/pagination, viewing details, updating status, and audit logging.
- **Intended Purpose**: Allow administrators to manage applications.

### 18. backend/admin/index.php
- **Source**: Custom-developed for this project
- **Description**: Full admin dashboard with login screen, dashboard statistics, application management table, status updates, reporting, and system settings.
- **Intended Purpose**: Provide administrative interface for the T-Level Hub.

### 19. backend/install.php
- **Source**: Custom-developed for this project
- **Description**: Installation script that creates database tables and default admin user with random password.
- **Intended Purpose**: One-time setup of the backend system.

### 20. backend/.htaccess
- **Source**: Custom-developed for this project
- **Description**: Apache configuration for security headers (X-Content-Type-Options, X-Frame-Options, XSS-Protection), URL rewriting, and directory access control.
- **Intended Purpose**: Secure the backend directory and API endpoints.

---

## Third-Party / Reference Resources

### 21. Python: chatbot_server.py
- **Source**: Custom-developed for this project (Python alternative backend)
- **Description**: Flask-based chatbot server with rate limiting and knowledge base.
- **Intended Purpose**: Alternative backend chatbot implementation.

### CSS Framework References
- **None used.** All styling is custom CSS without frameworks.

### JavaScript Libraries
- **None used.** All JavaScript is vanilla/plain JS without external dependencies.

---

## Legal & Regulatory Compliance

- **GDPR Compliance**: The backend implements data retention limits (2 years for applications), anonymisation of expired records, and secure storage of personal data.
- **Accessibility (WCAG)**: The frontend includes high-contrast mode, font size controls, reduced motion, underline links, and screen-reader-friendly markup.
- **Data Protection**: All user passwords are hashed with bcrypt (cost 12). API endpoints use token-based authentication with 24-hour expiry.
- **Age Verification**: Application API validates that applicants are between 16 and 19 years old as per T-Level requirements.

---

*This assets log was last updated on 26 June 2026.*