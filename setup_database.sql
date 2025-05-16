-- Drop database if exists and create new one
DROP DATABASE IF EXISTS mentor_management;
CREATE DATABASE mentor_management;
USE mentor_management;

-- Create users table
CREATE TABLE users (
    id INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('mentor', 'mentee') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);

-- Create mentors table
CREATE TABLE mentors (
    id INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    is_mentor_mentee TINYINT(1) DEFAULT 0,
    mentor_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);

-- Create mentees table
CREATE TABLE mentees (
    id INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);

-- Create mentor_mentee_relationship table
CREATE TABLE mentor_mentee_relationship (
    id INT NOT NULL AUTO_INCREMENT,
    mentor_id INT NOT NULL,
    mentee_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (mentor_id) REFERENCES mentors(id),
    FOREIGN KEY (mentee_id) REFERENCES mentees(id)
);

-- Create activities table
CREATE TABLE activities (
    id INT NOT NULL AUTO_INCREMENT,
    mentee_id INT NOT NULL,
    activity_type VARCHAR(50) NOT NULL,
    description TEXT,
    date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (mentee_id) REFERENCES mentees(id)
);

-- Create certifications table
CREATE TABLE certifications (
    id INT NOT NULL AUTO_INCREMENT,
    mentee_id INT NOT NULL,
    certification_name VARCHAR(100) NOT NULL,
    issuing_organization VARCHAR(100) NOT NULL,
    issue_date DATE NOT NULL,
    expiry_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (mentee_id) REFERENCES mentees(id)
);

-- Create semester_results table
CREATE TABLE semester_results (
    id INT NOT NULL AUTO_INCREMENT,
    mentee_id INT NOT NULL,
    semester VARCHAR(20) NOT NULL,
    gpa DECIMAL(4,2) NOT NULL,
    academic_year VARCHAR(9) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (mentee_id) REFERENCES mentees(id)
);

-- Create progress_tracking table
CREATE TABLE progress_tracking (
    id INT NOT NULL AUTO_INCREMENT,
    mentee_id INT NOT NULL,
    tracking_date DATE NOT NULL,
    academic_progress TEXT,
    personal_development TEXT,
    challenges TEXT,
    next_steps TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (mentee_id) REFERENCES mentees(id)
);

-- Insert dummy data for users
INSERT INTO users (name, email, password, role) VALUES
('John Smith', 'john.smith@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mentor'),
('Sarah Johnson', 'sarah.j@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mentor'),
('Mike Wilson', 'mike.w@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mentee'),
('Emma Davis', 'emma.d@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mentee');

-- Insert dummy data for mentors
INSERT INTO mentors (name, email, password, is_mentor_mentee) VALUES
('John Smith', 'john.smith@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 0),
('Sarah Johnson', 'sarah.j@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 0);

-- Insert dummy data for mentees
INSERT INTO mentees (name, email, password) VALUES
('Mike Wilson', 'mike.w@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Emma Davis', 'emma.d@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Insert dummy mentor-mentee relationships
INSERT INTO mentor_mentee_relationship (mentor_id, mentee_id) VALUES
(1, 1), -- John Smith mentors Mike Wilson
(2, 2); -- Sarah Johnson mentors Emma Davis

-- Insert dummy activities
INSERT INTO activities (mentee_id, activity_type, description, date) VALUES
(1, 'Workshop', 'Attended Python Programming Workshop', '2024-03-15'),
(2, 'Seminar', 'Participated in Career Development Seminar', '2024-03-10');

-- Insert dummy certifications
INSERT INTO certifications (mentee_id, certification_name, issuing_organization, issue_date) VALUES
(1, 'Python Programming', 'Coursera', '2024-02-01'),
(2, 'Web Development', 'Udemy', '2024-01-15');

-- Insert dummy semester results
INSERT INTO semester_results (mentee_id, semester, gpa, academic_year) VALUES
(1, 'Fall 2023', 3.75, '2023-2024'),
(2, 'Fall 2023', 3.85, '2023-2024');

-- Insert dummy progress tracking
INSERT INTO progress_tracking (mentee_id, tracking_date, academic_progress, personal_development, challenges, next_steps) VALUES
(1, '2024-03-01', 'Good progress in programming courses', 'Improved communication skills', 'Time management', 'Focus on advanced topics'),
(2, '2024-03-01', 'Excellent performance in web development', 'Enhanced leadership skills', 'Work-life balance', 'Start internship search');

-- Note: All users have the password 'password' (hashed using bcrypt)
-- You can use these credentials to test the system:
-- Mentor: john.smith@example.com / password
-- Mentor: sarah.j@example.com / password
-- Mentee: mike.w@example.com / password
-- Mentee: emma.d@example.com / password 