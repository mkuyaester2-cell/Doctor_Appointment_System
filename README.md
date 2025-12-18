# DocBook - Doctor Appointment System

DocBook is a modern, user-friendly web application designed to bridge the gap between patients and healthcare professionals. It simplifies the process of finding doctors, viewing their availability, and booking appointments in real-time.

**Live Demo:** [http://169.239.251.102:341/~ester.mkuya/](http://169.239.251.102:341/~ester.mkuya/)

# Key Features

For Patients
   Find Doctors: Search for specialists based on their name, specialization, or city.
   Real-time Booking: View interactive calendars and select available time slots.
   Appointment Management: Keep track of upcoming and past appointments through a personal dashboard.

 For Doctors
   Professional Profile: Register as a specialist and showcase your qualifications and fees.
   Schedule Management:Set your own working days and hours (Default: Mon-Fri, 9am-5pm).
   Patient Oversight: Manage appointment requests, confirm bookings, and mark them as completed.

For Administrators
   Doctor Verification: Secure approval system to verify and activate new doctor registrations.
   System Mastery: View all registered patients and every appointment scheduled in the system.
   Global Overview: Dashboard with system-wide statistics for better healthcare management.

Technology Stack
   Backend: PHP (Vanilla)
   Database: MySQL
   Frontend: HTML5, CSS3, Tailwind CSS (Design System)
   Icons: FontAwesome 6.0

Installation & Setup
1. Environment: Ensure you have XAMPP, WAMP, or a similar PHP/MySQL environment.
2. Database: 
    - Create a database named `docbook` (as specified in `config/config.php`).
    - Import the SQL schema located at `database/schema.sql`.
3. Configuration: Update `config/config.php` with your local database credentials.
4. Run: Place the project folder in your `htdocs` directory and access it via `localhost`.

Security Measures
- Role-Based Access Control (RBAC): Users are strictly limited to their roles (Patient, Doctor, Admin).
- Admin Approval: Doctors cannot be booked until they are reviewed and approved by an administrator.
- Password Hashing: All user passwords are encrypted using BCRYPT.




