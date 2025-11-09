-- ============================================
-- MENTOR MANAGEMENT SYSTEM - COMPLETE DATABASE
-- ============================================
-- This file contains the complete database structure
-- and dummy data for immediate deployment
-- Password for all users: password123
-- ============================================

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

-- ============================================
-- TABLE STRUCTURES
-- ============================================

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
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (mentor_id) REFERENCES mentors(id)
);

-- Create mentees table
CREATE TABLE mentees (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    usn VARCHAR(20) NOT NULL UNIQUE,
    semester INT NOT NULL,
    department VARCHAR(100) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create mentor_mentee_relationship table
CREATE TABLE mentor_mentee_relationship (
    mentor_id INT,
    mentee_id INT,
    PRIMARY KEY (mentor_id, mentee_id),
    FOREIGN KEY (mentor_id) REFERENCES mentors(id) ON DELETE CASCADE,
    FOREIGN KEY (mentee_id) REFERENCES mentees(id) ON DELETE CASCADE
);

-- Create semesters table
CREATE TABLE semesters (
    id INT PRIMARY KEY AUTO_INCREMENT,
    semester_number INT NOT NULL,
    academic_year VARCHAR(20) NOT NULL,
    mentee_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mentee_id) REFERENCES mentees(id) ON DELETE CASCADE,
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
    project_marks DECIMAL(5,2) NOT NULL,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
);

-- Create activities table
CREATE TABLE activities (
    id INT PRIMARY KEY AUTO_INCREMENT,
    mentee_id INT NOT NULL,
    activity_type VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    date DATE NOT NULL,
    FOREIGN KEY (mentee_id) REFERENCES mentees(id) ON DELETE CASCADE
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
    FOREIGN KEY (mentee_id) REFERENCES mentees(id) ON DELETE CASCADE,
    FOREIGN KEY (mentor_id) REFERENCES mentors(id) ON DELETE CASCADE
);

-- Create detailed_marks table (legacy support)
CREATE TABLE detailed_marks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    mentee_id INT NOT NULL,
    semester INT NOT NULL,
    academic_year VARCHAR(20) NOT NULL,
    first_ia_marks DECIMAL(5,2) NOT NULL,
    second_ia_marks DECIMAL(5,2) NOT NULL,
    final_exam_marks DECIMAL(5,2) NOT NULL,
    project_cgpa DECIMAL(3,2) NOT NULL,
    FOREIGN KEY (mentee_id) REFERENCES mentees(id) ON DELETE CASCADE
);

-- Create mentee_cgpa table (for overall CGPA tracking)
CREATE TABLE mentee_cgpa (
    id INT PRIMARY KEY AUTO_INCREMENT,
    mentee_id INT NOT NULL,
    semester INT NOT NULL,
    cgpa DECIMAL(3,2) NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (mentee_id) REFERENCES mentees(id) ON DELETE CASCADE
);

-- ============================================
-- DUMMY DATA INSERTION
-- ============================================

-- Insert Users
-- Password for all: "password123" (hashed with MD5 for simplicity)
-- Hash: 482c811da5d5b4bc6d497ffa98491e38

-- MENTORS (3 mentors)
INSERT INTO users (name, email, password, role, mobile_number, parent_mobile_number) VALUES
('Dr. John Doe', 'john.doe@example.com', '482c811da5d5b4bc6d497ffa98491e38', 'mentor', '9876543210', NULL),
('Prof. Sarah Smith', 'sarah.smith@example.com', '482c811da5d5b4bc6d497ffa98491e38', 'mentor', '9876543211', NULL),
('Dr. Michael Brown', 'michael.brown@example.com', '482c811da5d5b4bc6d497ffa98491e38', 'mentor', '9876543212', NULL);

-- MENTEES (6 mentees - 2 per mentor)
INSERT INTO users (name, email, password, role, mobile_number, parent_mobile_number) VALUES
('Alice Johnson', 'alice.johnson@example.com', '482c811da5d5b4bc6d497ffa98491e38', 'mentee', '8765432100', '8765432101'),
('Bob Williams', 'bob.williams@example.com', '482c811da5d5b4bc6d497ffa98491e38', 'mentee', '8765432102', '8765432103'),
('Charlie Davis', 'charlie.davis@example.com', '482c811da5d5b4bc6d497ffa98491e38', 'mentee', '8765432104', '8765432105'),
('Diana Miller', 'diana.miller@example.com', '482c811da5d5b4bc6d497ffa98491e38', 'mentee', '8765432106', '8765432107'),
('Ethan Wilson', 'ethan.wilson@example.com', '482c811da5d5b4bc6d497ffa98491e38', 'mentee', '8765432108', '8765432109'),
('Fiona Martinez', 'fiona.martinez@example.com', '482c811da5d5b4bc6d497ffa98491e38', 'mentee', '8765432110', '8765432111');

-- Insert Mentors
INSERT INTO mentors (user_id, is_mentor_mentee, mentor_id) VALUES
(1, FALSE, NULL),  -- Dr. John Doe
(2, FALSE, NULL),  -- Prof. Sarah Smith
(3, FALSE, NULL);  -- Dr. Michael Brown

-- Insert Mentees with USN, Semester, and Department
INSERT INTO mentees (user_id, usn, semester, department) VALUES
(4, '1MS21CS001', 6, 'Computer Science'),     -- Alice Johnson
(5, '1MS21CS002', 6, 'Computer Science'),     -- Bob Williams
(6, '1MS21EC003', 5, 'Electronics'),          -- Charlie Davis
(7, '1MS21EC004', 5, 'Electronics'),          -- Diana Miller
(8, '1MS21ME005', 4, 'Mechanical'),           -- Ethan Wilson
(9, '1MS21ME006', 4, 'Mechanical');           -- Fiona Martinez

-- Assign Mentors to Mentees (Each mentor gets 2 mentees)
INSERT INTO mentor_mentee_relationship (mentor_id, mentee_id) VALUES
(1, 1),  -- Dr. John Doe -> Alice Johnson
(1, 2),  -- Dr. John Doe -> Bob Williams
(2, 3),  -- Prof. Sarah Smith -> Charlie Davis
(2, 4),  -- Prof. Sarah Smith -> Diana Miller
(3, 5),  -- Dr. Michael Brown -> Ethan Wilson
(3, 6);  -- Dr. Michael Brown -> Fiona Martinez

-- Insert Activities for each Mentee
INSERT INTO activities (mentee_id, activity_type, description, date) VALUES
-- Alice Johnson's activities (mentee_id = 1)
(1, 'Workshop', 'Attended AWS Cloud Computing Workshop at TechHub Bangalore. Learned about EC2, S3, Lambda services and cloud architecture patterns.', '2024-10-15'),
(1, 'Hackathon', 'Participated in Smart India Hackathon 2024. Developed a healthcare monitoring app using React Native and Firebase. Team secured top 10 position.', '2024-09-20'),
(1, 'Seminar', 'Presented research paper on "Machine Learning Applications in Healthcare" at National Conference on Emerging Technologies, IIT Mumbai.', '2024-08-10'),
(1, 'Competition', 'Won first prize in College Level Web Development Competition. Built an e-commerce platform using MERN stack.', '2024-07-25'),

-- Bob Williams' activities (mentee_id = 2)
(2, 'Competition', 'Secured 2nd position in Inter-College Coding Competition at IIT Delhi. Solved 8/10 algorithmic problems in competitive programming.', '2024-10-01'),
(2, 'Workshop', 'Completed 3-day Full Stack Web Development Bootcamp. Covered Node.js, Express, MongoDB, React, and deployment strategies.', '2024-09-15'),
(2, 'Internship', 'Summer internship at TCS as Software Developer Intern. Worked on Java Spring Boot applications and microservices architecture.', '2024-07-30'),

-- Charlie Davis' activities (mentee_id = 3)
(3, 'Project', 'Developed IoT-based Smart Home Automation System using ESP32, MQTT protocol, and mobile app. Implemented voice control features.', '2024-10-20'),
(3, 'Workshop', 'Attended 5-day Embedded Systems and Robotics Workshop at NIT Trichy. Built line-following robot using Arduino.', '2024-09-05'),
(3, 'Competition', 'Participated in State Level Electronics Project Exhibition. Showcased automated plant watering system.', '2024-08-18'),

-- Diana Miller's activities (mentee_id = 4)
(4, 'Internship', '8-week Summer Internship at Texas Instruments, Bangalore. Worked on VLSI Design and chip verification using Verilog.', '2024-07-30'),
(4, 'Seminar', 'Attended International Seminar on "5G Technology and Its Applications". Learned about network slicing and edge computing.', '2024-10-10'),
(4, 'Workshop', 'Completed PCB Design Workshop. Learned Altium Designer and fabricated custom circuit boards.', '2024-09-12'),

-- Ethan Wilson's activities (mentee_id = 5)
(5, 'Competition', 'Participated in BAJA SAE India 2024 with college team. Designed and built an all-terrain vehicle. Team ranked in top 20.', '2024-09-25'),
(5, 'Workshop', 'Attended CAD/CAM and CNC Machining Workshop at Mahindra Research Valley, Chennai. Hands-on experience with industrial tools.', '2024-08-15'),
(5, 'Internship', '6-week internship at Bosch India. Worked on automotive component design and quality testing.', '2024-07-20'),

-- Fiona Martinez's activities (mentee_id = 6)
(6, 'Project', 'Design and Fabrication of Solar Powered Water Pump for rural areas. Used photovoltaic panels and DC motor system.', '2024-10-05'),
(6, 'Competition', 'Won Best Project Award at Karnataka State Level Technical Fest for innovative sustainable engineering solution.', '2024-09-18'),
(6, 'Workshop', 'Completed Renewable Energy Systems Workshop. Studied solar, wind energy conversion and hybrid power systems.', '2024-08-22');

-- Insert Academic Records in detailed_marks table
INSERT INTO detailed_marks (mentee_id, semester, academic_year, first_ia_marks, second_ia_marks, final_exam_marks, project_cgpa) VALUES
-- Alice Johnson (Computer Science, Semester 6) - mentee_id = 1
(1, 5, '2023-24', 85.00, 88.00, 90.00, 9.20),
(1, 6, '2023-24', 92.00, 90.00, 88.00, 9.00),

-- Bob Williams (Computer Science, Semester 6) - mentee_id = 2
(2, 5, '2023-24', 78.00, 82.00, 85.00, 8.50),
(2, 6, '2023-24', 88.00, 85.00, 90.00, 8.70),

-- Charlie Davis (Electronics, Semester 5) - mentee_id = 3
(3, 4, '2023-24', 90.00, 87.00, 92.00, 9.10),
(3, 5, '2024-25', 88.00, 91.00, 89.00, 9.00),

-- Diana Miller (Electronics, Semester 5) - mentee_id = 4
(4, 4, '2023-24', 92.00, 90.00, 94.00, 9.30),
(4, 5, '2024-25', 85.00, 88.00, 87.00, 8.90),

-- Ethan Wilson (Mechanical, Semester 4) - mentee_id = 5
(5, 3, '2023-24', 80.00, 83.00, 85.00, 8.40),
(5, 4, '2024-25', 82.00, 86.00, 88.00, 8.60),

-- Fiona Martinez (Mechanical, Semester 4) - mentee_id = 6
(6, 3, '2023-24', 88.00, 85.00, 90.00, 8.80),
(6, 4, '2024-25', 90.00, 88.00, 92.00, 9.00);

-- Insert Feedback from Mentors to Mentees
INSERT INTO feedback (mentee_id, mentor_id, feedback_text, date) VALUES
-- Feedback for Alice Johnson from Dr. John Doe
(1, 1, 'Alice has shown exceptional performance in Cloud Computing and Web Development. Her participation in the AWS workshop and subsequent project implementations demonstrate great initiative and deep understanding. The healthcare app developed during Smart India Hackathon shows excellent problem-solving skills. Keep up the excellent work! Consider exploring DevOps practices next.', '2024-10-20'),
(1, 1, 'Great presentation at the National Conference on Machine Learning. Your research on healthcare applications was impressive and well-received. I recommend you consider publishing this work in a peer-reviewed journal. Also, focus on building a strong GitHub portfolio with your projects.', '2024-08-15'),

-- Feedback for Bob Williams from Dr. John Doe
(2, 1, 'Congratulations on securing 2nd position in the coding competition at IIT Delhi! Your algorithmic thinking and problem-solving skills are improving consistently. The Full Stack bootcamp completion is commendable. I suggest you focus more on advanced data structures and system design concepts for placement preparation.', '2024-10-05'),
(2, 1, 'Your internship experience at TCS with Spring Boot and microservices is excellent. Make sure to document your learnings and build personal projects using these technologies. Consider contributing to open-source projects to enhance your profile.', '2024-08-10'),

-- Feedback for Charlie Davis from Prof. Sarah Smith
(3, 2, 'Your IoT project on Smart Home Automation shows excellent practical application of embedded systems concepts. The integration of ESP32, MQTT, and voice control is impressive. This demonstrates strong technical skills and innovation. Consider showcasing this project in more competitions and tech fests.', '2024-10-25'),
(3, 2, 'Great participation in the Electronics Project Exhibition. The automated plant watering system shows creativity in solving real-world problems. Keep working on more IoT projects and explore advanced sensors and actuators.', '2024-09-01'),

-- Feedback for Diana Miller from Prof. Sarah Smith
(4, 2, 'Excellent work during your internship at Texas Instruments! The hands-on experience in VLSI design and chip verification using Verilog will be extremely valuable for your career in semiconductor industry. I recommend you pursue advanced courses in digital design and take up VLSI as your specialization.', '2024-08-05'),
(4, 2, 'Your consistent academic performance with 9.3 CGPA and practical skills in PCB design make you a strong candidate for core electronics companies. Start preparing for GATE examination if you are considering higher studies. Keep exploring cutting-edge technologies like 5G and IoT.', '2024-10-15'),

-- Feedback for Ethan Wilson from Dr. Michael Brown
(5, 3, 'Your participation in BAJA SAE India shows great team spirit and practical engineering skills. The experience of designing and building an all-terrain vehicle is invaluable. This kind of hands-on project work is highly valued by automotive companies. Continue working on vehicle dynamics and automotive design projects.', '2024-10-01'),
(5, 3, 'The internship at Bosch India and CAD/CAM workshop experience demonstrates your commitment to learning industry-relevant skills. Your performance is good. Focus on improving your CGPA in the upcoming semester while maintaining your practical project work. Balance is key.', '2024-08-20'),

-- Feedback for Fiona Martinez from Dr. Michael Brown
(6, 3, 'Congratulations on winning the Best Project Award for your Solar Powered Water Pump! This project demonstrates not just technical skills but also social awareness and sustainable thinking. The application of renewable energy for solving rural problems is commendable. Excellent work! Consider applying for government-sponsored innovation schemes.', '2024-09-22'),
(6, 3, 'Your strong academic performance (9.0 CGPA) combined with practical renewable energy projects makes you stand out. The workshop on renewable energy systems adds great value to your profile. I encourage you to pursue research opportunities in sustainable energy. Also, consider participating in more national-level competitions.', '2024-10-10');

-- Note: Certifications table is left empty intentionally
-- Students can upload their own certifications through the web interface

-- ============================================
-- DISPLAY SETUP SUMMARY
-- ============================================

SELECT '============================================' AS '';
SELECT 'DATABASE SETUP COMPLETED SUCCESSFULLY!' AS '';
SELECT '============================================' AS '';

SELECT 'SUMMARY:' AS '';
SELECT COUNT(*) AS Total_Users FROM users;
SELECT COUNT(*) AS Total_Mentors FROM mentors;
SELECT COUNT(*) AS Total_Mentees FROM mentees;
SELECT COUNT(*) AS Total_Mentor_Mentee_Relations FROM mentor_mentee_relationship;
SELECT COUNT(*) AS Total_Activities FROM activities;
SELECT COUNT(*) AS Total_Feedback FROM feedback;
SELECT COUNT(*) AS Total_Academic_Records FROM detailed_marks;
SELECT COUNT(*) AS Total_Certifications FROM certifications;

SELECT '============================================' AS '';
SELECT 'LOGIN CREDENTIALS' AS '';
SELECT '============================================' AS '';
SELECT 'Password for ALL users: password123' AS '';
SELECT '============================================' AS '';

SELECT 'MENTORS:' AS '';
SELECT name AS Name, email AS Email, 'password123' AS Password, mobile_number AS Mobile 
FROM users WHERE role = 'mentor';

SELECT '--------------------------------------------' AS '';
SELECT 'MENTEES:' AS '';
SELECT u.name AS Name, u.email AS Email, 'password123' AS Password, m.usn AS USN, 
       m.semester AS Semester, m.department AS Department
FROM users u
JOIN mentees m ON u.id = m.user_id
WHERE u.role = 'mentee';

SELECT '============================================' AS '';
SELECT 'You can now login to the system!' AS '';
SELECT '============================================' AS '';
