# Mentor Management System

A web-based mentorship management system built with PHP and MySQL for tracking academic progress, activities, and feedback.

## Features

### For Mentors
- View and manage assigned mentees
- Track academic performance
- Provide feedback
- Monitor activities and certifications

### For Mentees
- View personal dashboard
- Upload certifications
- Log activities
- Track academic progress

## Tech Stack

- **Backend**: PHP
- **Database**: MySQL
- **Frontend**: HTML, CSS, JavaScript

- **Frontend**: HTML5, CSS3, Bootstrap 5.1.3

- **Icons**: Font Awesome 6.0.0bash

- **Server**: Apache (WAMP/XAMPP)# Step 3: Pull Project Files from GitHub

# ------------------------------------

## Project Structure

# Clone the repository (replace the link with your actual repo URL)

```git clone https://github.com/your-username/your-repo-name.git

Mentor-Management/

├── actions/              # Backend handlers for form submissions# OR if you've already cloned it, just pull the latest files

│   ├── add_mentee.phpgit pull origin main

│   ├── add_result.php

│   └── upload_certification.php# Note: You don’t need to run the db.sql file in the browser.

├── assets/              # Static assets# It should only be used for importing into the database.

## Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/pradeepapalleti/Mentor-Management.git
   cd Mentor-Management
   ```

2. **Setup Database**
   - Start WAMP/XAMPP server
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Import `mentorship_system_with_data.sql`

3. **Configure Database**
   - Edit `config/db.php` with your credentials

4. **Access Application**
   ```
   http://localhost/Mentor-Management/
   ```

## Default Login

**Password for all users:** `password123`

**Mentors:**
- john.doe@example.com
- sarah.smith@example.com
- michael.brown@example.com

**Mentees:**
- alice.johnson@example.com
- bob.williams@example.com
- (and 4 more)

## Project Structure

```
Mentor-Management/
├── actions/             # Backend processing
├── assets/              # CSS, JS files
├── config/              # Database config
├── includes/            # Sidebar, common includes
├── pages/               # Dashboard pages
├── uploads/             # Certificates, files
├── .gitignore
├── .htaccess
├── index.html           # Landing page
├── login.php            # Login handler
├── logout.php           # Logout handler
├── register.php         # Registration handler
├── mentorship_system_with_data.sql  # Complete DB with data
└── README.md
```

## License

This project is open-source and available under the MIT License.

## Author

**Pradeepa palleti**
- GitHub: [@pradeepapalleti](https://github.com/pradeepapalleti)
