-- Drop existing database if it exists
DROP DATABASE IF EXISTS mentor_management;

-- Create database
CREATE DATABASE mentor_management;
USE mentor_management;

-- Drop all tables in reverse order of dependencies
DROP TABLE IF EXISTS detailed_marks;
DROP TABLE IF EXISTS mentee_cgpa;
DROP TABLE IF EXISTS feedback;
DROP TABLE IF EXISTS subject_marks;
DROP TABLE IF EXISTS subjects;
DROP TABLE IF EXISTS semesters;
DROP TABLE IF EXISTS certifications;
DROP TABLE IF EXISTS activities;
DROP TABLE IF EXISTS mentor_mentee_relationship;
DROP TABLE IF EXISTS mentees;
DROP TABLE IF EXISTS mentors;
DROP TABLE IF EXISTS users;

-- Create users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('mentor', 'mentee') NOT NULL,
    mobile_number VARCHAR(15) NOT NULL,
    parent_mobile_number VARCHAR(15),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create mentors table
CREATE TABLE mentors (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    is_mentor_mentee BOOLEAN DEFAULT FALSE,
    mentor_id INT,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (mentor_id) REFERENCES mentors(id)
);

-- Create mentees table
CREATE TABLE mentees (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    usn VARCHAR(20) NOT NULL UNIQUE,
    semester INT NOT NULL,
    department VARCHAR(100) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Create mentor_mentee_relationship table
CREATE TABLE mentor_mentee_relationship (
    mentor_id INT,
    mentee_id INT,
    PRIMARY KEY (mentor_id, mentee_id),
    FOREIGN KEY (mentor_id) REFERENCES mentors(id),
    FOREIGN KEY (mentee_id) REFERENCES mentees(id)
);

-- Create semesters table
CREATE TABLE semesters (
    id INT PRIMARY KEY AUTO_INCREMENT,
    semester_number INT NOT NULL,
    academic_year VARCHAR(20) NOT NULL,
    mentee_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mentee_id) REFERENCES mentees(id),
    UNIQUE KEY unique_semester (semester_number, academic_year, mentee_id)
);

-- Create subjects table
CREATE TABLE subjects (
    id INT PRIMARY KEY AUTO_INCREMENT,
    semester_id INT NOT NULL,
    subject_name VARCHAR(100) NOT NULL,
    subject_code VARCHAR(20) NOT NULL,
    credits INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (semester_id) REFERENCES semesters(id) ON DELETE CASCADE
);

-- Create subject_marks table
CREATE TABLE subject_marks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    subject_id INT NOT NULL,
    first_ia_marks DECIMAL(5,2) NOT NULL,
    second_ia_marks DECIMAL(5,2) NOT NULL,
    final_exam_marks DECIMAL(5,2) NOT NULL,
    project_marks DECIMAL(5,2) NOT NULL
);

-- Create activities table
CREATE TABLE activities (
    id INT PRIMARY KEY AUTO_INCREMENT,
    mentee_id INT NOT NULL,
    activity_type VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    date DATE NOT NULL,
    FOREIGN KEY (mentee_id) REFERENCES mentees(id)
);

-- Create certifications table
CREATE TABLE certifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    issuer VARCHAR(255) NOT NULL,
    date DATE NOT NULL,
    description TEXT,
    file_path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create feedback table
CREATE TABLE feedback (
    id INT PRIMARY KEY AUTO_INCREMENT,
    mentee_id INT NOT NULL,
    mentor_id INT NOT NULL,
    feedback_text TEXT NOT NULL,
    date DATE NOT NULL,
    FOREIGN KEY (mentee_id) REFERENCES mentees(id),
    FOREIGN KEY (mentor_id) REFERENCES mentors(id)
);

-- Create mentee_cgpa table
CREATE TABLE mentee_cgpa (
    id INT PRIMARY KEY AUTO_INCREMENT,
    mentee_id INT NOT NULL,
    semester INT NOT NULL,
    cgpa DECIMAL(4,2) NOT NULL,
    FOREIGN KEY (mentee_id) REFERENCES mentees(id)
);

-- Create detailed_marks table
CREATE TABLE detailed_marks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    mentee_id INT NOT NULL,
    semester INT NOT NULL,
    academic_year VARCHAR(9) NOT NULL,
    first_ia_marks DECIMAL(5,2) NOT NULL,
    second_ia_marks DECIMAL(5,2) NOT NULL,
    final_exam_marks DECIMAL(5,2) NOT NULL,
    project_cgpa DECIMAL(4,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mentee_id) REFERENCES mentees(id) ON DELETE CASCADE
);

-- Insert dummy users
INSERT INTO users (name, email, password, role, mobile_number, parent_mobile_number) VALUES
('John Doe', 'john.doe@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mentor', '1234567890', NULL),
('Jane Smith', 'jane.smith@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mentee', '9876543210', '9876543211'),
('Alice Johnson', 'alice.johnson@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mentee', '8765432109', '8765432108');

-- Insert dummy mentors
INSERT INTO mentors (user_id, is_mentor_mentee) VALUES
(1, 0);

-- Insert dummy mentees
INSERT INTO mentees (user_id, usn, semester, department) VALUES
(2, '1MS20CS001', 5, 'Computer Science'),
(3, '1MS20CS002', 6, 'Computer Science');

-- Insert dummy mentor-mentee relationships
INSERT INTO mentor_mentee_relationship (mentor_id, mentee_id) VALUES
(1, 1),
(1, 2);

-- Insert sample semester and subjects
INSERT INTO semesters (semester_number, academic_year, mentee_id) VALUES
(5, '2023-2024', 1),
(6, '2023-2024', 2);

INSERT INTO subjects (semester_id, subject_name, subject_code, credits) VALUES
(1, 'Database Management Systems', 'CS301', 4),
(1, 'Operating Systems', 'CS302', 4),
(1, 'Computer Networks', 'CS303', 4),
(2, 'Machine Learning', 'CS401', 4),
(2, 'Deep Learning', 'CS402', 4),
(2, 'Natural Language Processing', 'CS403', 4);

-- Insert sample marks
INSERT INTO subject_marks (subject_id, first_ia_marks, second_ia_marks, final_exam_marks, project_marks) VALUES
(1, 85.5, 88.0, 90.0, 92.0),
(2, 82.0, 85.5, 88.0, 90.0),
(3, 87.0, 89.5, 91.0, 93.0),
(4, 92.0, 90.0, 88.0, 95.0),
(5, 88.0, 85.0, 90.0, 93.0),
(6, 85.0, 88.0, 92.0, 90.0);

-- Insert dummy activities
INSERT INTO activities (mentee_id, activity_type, description, date) VALUES
(1, 'Workshop', 'Web Development Workshop', '2024-03-15'),
(1, 'Project', 'Database Management System Project', '2024-03-10'),
(1, 'Class', 'Data Structures and Algorithms', '2024-03-20'),
(1, 'Exam', 'Mid Semester Examination', '2024-03-25'),
(1, 'Assignment', 'SQL Queries Practice', '2024-03-18'),
(2, 'Workshop', 'Machine Learning Basics', '2024-03-16'),
(2, 'Project', 'AI Chatbot Development', '2024-03-12'),
(2, 'Class', 'Python Programming', '2024-03-21'),
(2, 'Exam', 'End Semester Examination', '2024-03-26'),
(2, 'Assignment', 'Data Analysis Project', '2024-03-19');

-- Insert dummy certifications
INSERT INTO certifications (user_id, title, issuer, date, description, file_path) VALUES
(2, 'AWS Certified Cloud Practitioner', 'Amazon Web Services', '2024-01-15', 'Cloud computing fundamentals certification', 'certificates/aws_cp.pdf'),
(2, 'Python Programming Certification', 'Coursera', '2024-02-20', 'Advanced Python programming skills', 'certificates/python_cert.pdf'),
(2, 'Web Development Bootcamp', 'Udemy', '2024-01-10', 'Full-stack web development certification', 'certificates/web_dev.pdf'),
(3, 'Machine Learning Specialization', 'Stanford Online', '2024-02-01', 'Machine learning and AI fundamentals', 'certificates/ml_spec.pdf'),
(3, 'Data Science Professional', 'IBM', '2024-01-25', 'Data science and analytics certification', 'certificates/ds_prof.pdf'),
(3, 'Full Stack Development', 'Meta', '2024-02-15', 'Full stack web development certification', 'certificates/full_stack.pdf');

-- Insert dummy feedback
INSERT INTO feedback (mentee_id, mentor_id, feedback_text, date) VALUES
(1, 1, 'Excellent progress in database concepts. Keep up the good work!', '2024-03-15'),
(1, 1, 'Need to improve in operating systems concepts. Consider additional practice.', '2024-03-20'),
(1, 1, 'Great performance in the web development project. Very innovative approach.', '2024-03-25'),
(2, 1, 'Outstanding work in machine learning assignments. Shows great potential.', '2024-03-16'),
(2, 1, 'Good understanding of deep learning concepts. Continue exploring advanced topics.', '2024-03-21'),
(2, 1, 'Excellent presentation skills in the project demonstration.', '2024-03-26');

-- Insert dummy CGPA records
INSERT INTO mentee_cgpa (mentee_id, semester, cgpa) VALUES
(1, 1, 8.5),
(1, 2, 8.7),
(1, 3, 8.9),
(1, 4, 9.0),
(1, 5, 8.8),
(2, 1, 9.2),
(2, 2, 9.4),
(2, 3, 9.1),
(2, 4, 9.3),
(2, 5, 9.5);

-- Insert dummy detailed marks
INSERT INTO detailed_marks (mentee_id, semester, academic_year, first_ia_marks, second_ia_marks, final_exam_marks, project_cgpa) VALUES
(1, 5, '2023-24', 85, 88, 82, 8.5),
(1, 5, '2023-24', 78, 82, 75, 7.8),
(1, 5, '2023-24', 90, 88, 85, 8.8),
(2, 6, '2023-24', 92, 90, 88, 9.0),
(2, 6, '2023-24', 88, 85, 90, 8.7),
(2, 6, '2023-24', 95, 92, 90, 9.2);

DESCRIBE subject_marks; 