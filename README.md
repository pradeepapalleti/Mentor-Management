# Mentor Management System# Mentor-Management



A comprehensive web-based mentorship management system built with PHP, MySQL, and Bootstrap. This system enables mentors to manage mentees, track academic progress, provide feedback, and monitor various activities.#  Setup Instructions



## Featuresbash

# Step 1: Create the Database

### For Mentors# ------------------------------------

- **Dashboard**: Overview of all assigned mentees and their performance# - Open your SQL tool (e.g., phpMyAdmin or MySQL CLI).

- **Mentee Management**: Add, view, and manage mentee details# - Import the `db.sql` file to create the required database and tables.

- **Academic Tracking**: Record and monitor semester-wise marks and subject performance

- **Activity Logging**: Track mentee activities (workshops, projects, assignments, exams, etc.)# Example using phpMyAdmin:

- **Feedback System**: Provide detailed feedback to mentees# 1. Create a new database (e.g., project_db)

- **Certification Management**: View mentee certifications and achievements# 2. Go to the Import tab

# 3. Upload and execute the `db.sql` file

### For Mentees

- **Personal Dashboard**: View personal academic records and feedback

- **Activity Management**: Log personal activities and achievementsbash

- **Progress Tracking**: Monitor semester-wise academic performance# Step 2: Setup Project Files

- **Feedback Access**: View mentor feedback and guidance# ------------------------------------

- **Certification Upload**: Upload and manage certifications# - If you're using WAMP, place the project folder in:

C:/wamp/www/your-project-folder/

## Tech Stack

# - If you're using XAMPP, place the folder in:

- **Backend**: PHP 7.4+C:/xampp/htdocs/your-project-folder/

- **Database**: MySQL/MariaDB

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

│   ├── css/

│   │   └── style.css

│   └── js/bash

│       └── login.js# Step 4: Run the Project

├── config/              # Configuration files# ------------------------------------

│   └── db.php          # Database connection# - Start your WAMP or XAMPP server.

├── includes/            # Reusable components# - Open your browser and go to:

│   └── sidebar.php     # Navigation sidebarhttp://localhost/your-project-folder/

├── pages/              # Main application pages

│   ├── activities.php

│   ├── certifications.php
│   ├── feedback.php
│   ├── login_form.php
│   ├── marks.php
│   ├── mentee_dashboard.php
│   ├── mentee_details.php
│   ├── mentee_list.php
│   ├── mentor_dashboard.php
│   ├── profile.php
│   └── subject_marks.php
├── setup/              # Database setup scripts
│   ├── setup_database.php
│   └── verify_db.php
├── uploads/            # User uploaded files
│   └── certificates/
├── index.html          # Landing page
├── login.php           # Login handler
├── logout.php          # Logout handler
├── register.php        # Registration page
└── mentorship_system.sql  # Database schema
```

## Installation & Setup

### Prerequisites
- WAMP/XAMPP server installed
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Modern web browser

### Step 1: Clone the Repository

```bash
git clone https://github.com/pradeepapalleti/Mentor-Management.git
cd Mentor-Management
```

### Step 2: Setup Database

1. Start your WAMP/XAMPP server
2. Open phpMyAdmin (http://localhost/phpmyadmin)
3. Create a new database named `mentor_management`
4. Import the database schema:
   - Click on the `mentor_management` database
   - Go to the "Import" tab
   - Choose file: `mentorship_system.sql`
   - Click "Go" to import

### Step 3: Configure Database Connection

Edit `config/db.php` and update the database credentials if needed:

```php
$servername = "localhost";
$username = "root";        // Your MySQL username
$password = "";            // Your MySQL password
$dbname = "mentor_management";
```

### Step 4: Setup Project Location

**For WAMP:**
```
C:/wamp64/www/Mentor-Management/
```

**For XAMPP:**
```
C:/xampp/htdocs/Mentor-Management/
```

### Step 5: Access the Application

1. Start your Apache and MySQL services
2. Open your browser and navigate to:
   ```
   http://localhost/Mentor-Management/
   ```

## Default Login Credentials

After importing the database, you can create your first mentor account by:
1. Going to `http://localhost/Mentor-Management/register.php`
2. Fill in the registration form
3. Use those credentials to log in

## Usage Guide

### For Mentors

1. **Register/Login**: Create a mentor account or login with existing credentials
2. **Add Mentees**: Navigate to "Mentee List" and click "Add New Mentee"
3. **Record Marks**: Go to "Subject Marks" → Select a mentee → Add semester results
4. **Track Activities**: Use the "Activities" section to log mentee activities
5. **Provide Feedback**: Visit mentee details page and add feedback

### For Mentees

1. **Login**: Use credentials provided by your mentor or system admin
2. **View Dashboard**: See your academic overview and recent feedback
3. **Log Activities**: Add your personal activities, workshops, projects
4. **Upload Certifications**: Upload achievement certificates
5. **Check Feedback**: View mentor feedback and guidance

## Database Schema

Key tables:
- `users` - Authentication and user information
- `mentors` - Mentor profile details
- `mentees` - Mentee profile details
- `mentor_mentee_relationship` - Links mentors to their mentees
- `semesters` - Semester information
- `subjects` - Subject details
- `subject_marks` - Academic marks records
- `activities` - Activity logs
- `feedback` - Mentor feedback
- `certifications` - Mentee certifications

## Security Features

- Password hashing using PHP `password_hash()`
- Session-based authentication
- SQL injection prevention using prepared statements
- Input validation and sanitization
- Protected configuration directories via `.htaccess`

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## Troubleshooting

### Common Issues

**Database connection error:**
- Verify MySQL service is running
- Check credentials in `config/db.php`
- Ensure database `mentor_management` exists

**Login not working:**
- Clear browser cache and cookies
- Check PHP error logs in WAMP/XAMPP
- Verify user exists in `users` table

**Pages not loading:**
- Ensure Apache is running
- Check file permissions
- Verify `.htaccess` is enabled in Apache config

**File upload errors:**
- Check `uploads/certificates/` folder exists
- Verify folder has write permissions
- Check `php.ini` upload limits

## Support

For issues, questions, or contributions, please open an issue on GitHub:
https://github.com/pradeepapalleti/Mentor-Management/issues

## License

This project is open-source and available under the MIT License.

## Author

**Pradeep Apalleti**
- GitHub: [@pradeepapalleti](https://github.com/pradeepapalleti)

## Acknowledgments

- Bootstrap team for the UI framework
- Font Awesome for icons
- PHP and MySQL communities

---

**Note**: This is a development version. For production deployment, ensure to:
- Change database credentials
- Disable error display in PHP
- Enable HTTPS
- Implement additional security measures
- Regular database backups
