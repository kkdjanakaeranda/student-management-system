-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 22, 2026 at 09:47 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `student_management_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `content` text NOT NULL,
  `posted_by` int(11) NOT NULL,
  `target_audience` enum('all','students','teachers') DEFAULT 'all',
  `priority` enum('low','medium','high') DEFAULT 'medium',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `content`, `posted_by`, `target_audience`, `priority`, `created_at`) VALUES
(1, 'Welcome', 'Welcome to the Student Management System', 10, 'all', 'high', '2026-05-22 07:31:06'),
(2, 'Exam Notice', 'Mid exams start next week', 10, 'students', 'high', '2026-05-22 07:31:06'),
(3, 'Staff Meeting', 'Teachers meeting on Monday', 11, 'teachers', 'medium', '2026-05-22 07:31:06'),
(4, 'Assignment Deadline', 'Submit assignments before Friday', 10, 'students', 'medium', '2026-05-22 07:31:06'),
(5, 'Holiday Notice', 'Institute closed on Poya Day', 11, 'all', 'low', '2026-05-22 07:31:06'),
(6, 'New Course', 'AI course registrations are open', 10, 'students', 'high', '2026-05-22 07:31:06'),
(7, 'Library Update', 'New books added to library', 11, 'all', 'low', '2026-05-22 07:31:06'),
(8, 'Lab Maintenance', 'Computer lab under maintenance', 10, 'students', 'medium', '2026-05-22 07:31:06'),
(9, 'Workshop', 'Cyber security workshop next month', 11, 'students', 'high', '2026-05-22 07:31:06'),
(10, 'Results Published', 'Semester results released', 10, 'students', 'high', '2026-05-22 07:31:06'),
(11, 'hi all', 'hiii', 1, 'all', 'high', '2026-05-22 07:40:32');

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `status` enum('present','absent','late','excused') NOT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `student_id`, `class_id`, `date`, `status`, `remarks`, `created_at`) VALUES
(1, 1, 1, '2026-05-01', 'present', 'On Time', '2026-05-22 07:30:43'),
(2, 2, 2, '2026-05-01', 'late', 'Late Arrival', '2026-05-22 07:30:43'),
(3, 3, 3, '2026-05-01', 'present', 'Good', '2026-05-22 07:30:43'),
(4, 4, 4, '2026-05-01', 'absent', 'Medical', '2026-05-22 07:30:43'),
(5, 5, 5, '2026-05-01', 'present', 'On Time', '2026-05-22 07:30:43'),
(6, 6, 6, '2026-05-01', 'excused', 'Approved Leave', '2026-05-22 07:30:43'),
(7, 7, 7, '2026-05-01', 'present', 'Good', '2026-05-22 07:30:43'),
(8, 8, 8, '2026-05-01', 'late', 'Traffic', '2026-05-22 07:30:43'),
(9, 9, 9, '2026-05-01', 'present', 'Good', '2026-05-22 07:30:43'),
(10, 10, 10, '2026-05-01', 'present', 'On Time', '2026-05-22 07:30:43');

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `id` int(11) NOT NULL,
  `class_name` varchar(50) NOT NULL,
  `section` varchar(10) DEFAULT NULL,
  `course_id` int(11) DEFAULT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `academic_year` varchar(20) DEFAULT NULL,
  `room_number` varchar(20) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`id`, `class_name`, `section`, `course_id`, `teacher_id`, `academic_year`, `room_number`, `status`, `created_at`, `updated_at`) VALUES
(1, 'SE Batch 2026', 'A', 1, 1, '2026', 'R101', 'active', '2026-05-22 07:27:30', '2026-05-22 07:27:30'),
(2, 'IT Batch 2026', 'B', 2, 2, '2026', 'R102', 'active', '2026-05-22 07:27:30', '2026-05-22 07:27:30'),
(3, 'CS Batch 2026', 'A', 3, 3, '2026', 'R103', 'active', '2026-05-22 07:27:30', '2026-05-22 07:27:30'),
(4, 'AI Batch 2026', 'C', 4, 4, '2026', 'R104', 'active', '2026-05-22 07:27:30', '2026-05-22 07:27:30'),
(5, 'DS Batch 2026', 'A', 5, 5, '2026', 'R105', 'active', '2026-05-22 07:27:30', '2026-05-22 07:27:30'),
(6, 'Cyber Batch 2026', 'B', 6, 6, '2026', 'R106', 'active', '2026-05-22 07:27:30', '2026-05-22 07:27:30'),
(7, 'Web Dev Batch', 'A', 7, 7, '2026', 'R107', 'active', '2026-05-22 07:27:30', '2026-05-22 07:27:30'),
(8, 'Graphics Batch', 'B', 8, 8, '2026', 'R108', 'active', '2026-05-22 07:27:30', '2026-05-22 07:27:30'),
(9, 'Business Analytics', 'A', 9, 9, '2026', 'R109', 'active', '2026-05-22 07:27:30', '2026-05-22 07:27:30'),
(10, 'ML Batch', 'A', 10, 10, '2026', 'R110', 'active', '2026-05-22 07:27:30', '2026-05-22 07:27:30');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `course_code` varchar(20) NOT NULL,
  `course_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `credits` int(11) DEFAULT NULL,
  `duration` varchar(50) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `course_code`, `course_name`, `description`, `credits`, `duration`, `status`, `created_at`, `updated_at`) VALUES
(1, 'CS101', 'Computer Science', 'Computer Science Degree', 4, '4 Years', 'active', '2026-05-22 07:20:10', '2026-05-22 07:20:10'),
(2, 'IT102', 'Information Technology', 'IT Program', 3, '3 Years', 'active', '2026-05-22 07:20:10', '2026-05-22 07:20:10'),
(3, 'SE103', 'Software Engineering', 'SE Degree', 4, '4 Years', 'active', '2026-05-22 07:20:10', '2026-05-22 07:20:10'),
(4, 'DS104', 'Data Science', 'Data Science Program', 4, '4 Years', 'active', '2026-05-22 07:20:10', '2026-05-22 07:20:10'),
(5, 'AI105', 'Artificial Intelligence', 'AI Degree', 4, '4 Years', 'active', '2026-05-22 07:20:10', '2026-05-22 07:20:10'),
(6, 'CY106', 'Cyber Security', 'Cyber Security Program', 3, '3 Years', 'active', '2026-05-22 07:20:10', '2026-05-22 07:20:10'),
(7, 'WD107', 'Web Development', 'Web Development Course', 2, '2 Years', 'active', '2026-05-22 07:20:10', '2026-05-22 07:20:10'),
(8, 'GD108', 'Graphic Design', 'Graphic Design Course', 2, '2 Years', 'active', '2026-05-22 07:20:10', '2026-05-22 07:20:10'),
(9, 'BA109', 'Business Analytics', 'Business Analytics Program', 3, '3 Years', 'active', '2026-05-22 07:20:10', '2026-05-22 07:20:10'),
(10, 'ML110', 'Machine Learning', 'Machine Learning Degree', 4, '4 Years', 'active', '2026-05-22 07:20:10', '2026-05-22 07:20:10');

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `enrollment_date` date NOT NULL,
  `status` enum('enrolled','completed','dropped') DEFAULT 'enrolled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`id`, `student_id`, `class_id`, `enrollment_date`, `status`, `created_at`) VALUES
(1, 1, 1, '2025-01-20', 'enrolled', '2026-05-22 07:29:06'),
(2, 2, 2, '2025-01-20', 'enrolled', '2026-05-22 07:29:06'),
(3, 3, 3, '2025-01-20', 'enrolled', '2026-05-22 07:29:06'),
(4, 4, 4, '2025-01-20', 'enrolled', '2026-05-22 07:29:06'),
(5, 5, 5, '2025-01-20', 'enrolled', '2026-05-22 07:29:06'),
(6, 6, 6, '2025-01-20', 'enrolled', '2026-05-22 07:29:06'),
(7, 7, 7, '2025-01-20', 'enrolled', '2026-05-22 07:29:06'),
(8, 8, 8, '2025-01-20', 'enrolled', '2026-05-22 07:29:06'),
(9, 9, 9, '2025-01-20', 'enrolled', '2026-05-22 07:29:06'),
(10, 10, 10, '2025-01-20', 'enrolled', '2026-05-22 07:29:06');

-- --------------------------------------------------------

--
-- Table structure for table `exams`
--

CREATE TABLE `exams` (
  `id` int(11) NOT NULL,
  `exam_name` varchar(100) NOT NULL,
  `exam_type` enum('midterm','final','quiz','assignment') NOT NULL,
  `class_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `exam_date` date NOT NULL,
  `total_marks` int(11) NOT NULL,
  `duration` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `exams`
--

INSERT INTO `exams` (`id`, `exam_name`, `exam_type`, `class_id`, `subject_id`, `exam_date`, `total_marks`, `duration`, `created_at`) VALUES
(1, 'Midterm Exam', 'midterm', 1, 1, '2026-06-10', 100, '2 Hours', '2026-05-22 07:29:31'),
(2, 'Final Exam', 'final', 2, 2, '2026-06-11', 100, '2 Hours', '2026-05-22 07:29:31'),
(3, 'Quiz 1', 'quiz', 3, 3, '2026-06-12', 50, '1 Hour', '2026-05-22 07:29:31'),
(4, 'Assignment 1', 'assignment', 4, 4, '2026-06-13', 100, '1 Week', '2026-05-22 07:29:31'),
(5, 'Midterm AI', 'midterm', 5, 5, '2026-06-14', 100, '2 Hours', '2026-05-22 07:29:31'),
(6, 'Cyber Final', 'final', 6, 6, '2026-06-15', 100, '2 Hours', '2026-05-22 07:29:31'),
(7, 'Cloud Quiz', 'quiz', 7, 7, '2026-06-16', 50, '1 Hour', '2026-05-22 07:29:31'),
(8, 'DS Assignment', 'assignment', 8, 8, '2026-06-17', 100, '1 Week', '2026-05-22 07:29:31'),
(9, 'Networking Mid', 'midterm', 9, 9, '2026-06-18', 100, '2 Hours', '2026-05-22 07:29:31'),
(10, 'SE Final', 'final', 10, 10, '2026-06-19', 100, '2 Hours', '2026-05-22 07:29:31');

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

CREATE TABLE `grades` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `exam_id` int(11) NOT NULL,
  `marks_obtained` decimal(5,2) NOT NULL,
  `grade` varchar(5) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `grades`
--

INSERT INTO `grades` (`id`, `student_id`, `exam_id`, `marks_obtained`, `grade`, `remarks`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 85.00, 'A', 'Excellent', '2026-05-22 07:30:10', '2026-05-22 07:30:10'),
(2, 2, 2, 78.00, 'B+', 'Very Good', '2026-05-22 07:30:10', '2026-05-22 07:30:10'),
(3, 3, 3, 91.00, 'A+', 'Outstanding', '2026-05-22 07:30:10', '2026-05-22 07:30:10'),
(4, 4, 4, 67.00, 'B', 'Good', '2026-05-22 07:30:10', '2026-05-22 07:30:10'),
(5, 5, 5, 88.00, 'A', 'Excellent', '2026-05-22 07:30:10', '2026-05-22 07:30:10'),
(6, 6, 6, 74.00, 'B', 'Good', '2026-05-22 07:30:10', '2026-05-22 07:30:10'),
(7, 7, 7, 95.00, 'A+', 'Excellent', '2026-05-22 07:30:10', '2026-05-22 07:30:10'),
(8, 8, 8, 69.00, 'B', 'Average', '2026-05-22 07:30:10', '2026-05-22 07:30:10'),
(9, 9, 9, 81.00, 'A', 'Very Good', '2026-05-22 07:30:10', '2026-05-22 07:30:10'),
(10, 10, 10, 72.00, 'B', 'Good', '2026-05-22 07:30:10', '2026-05-22 07:30:10');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `student_id` varchar(20) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `guardian_name` varchar(100) DEFAULT NULL,
  `guardian_phone` varchar(20) DEFAULT NULL,
  `admission_date` date DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive','graduated') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `user_id`, `student_id`, `first_name`, `last_name`, `date_of_birth`, `gender`, `phone`, `address`, `guardian_name`, `guardian_phone`, `admission_date`, `photo`, `status`, `created_at`, `updated_at`) VALUES
(1, 16, 'ST001', 'Janaka', 'Eranda', '2003-08-15', 'Male', '0771111111', 'Colombo', 'Sunil Eranda', '0719000001', '2025-01-10', '', 'active', '2026-05-22 07:28:39', '2026-05-22 07:28:39'),
(2, 17, 'ST002', 'Kavindu', 'Perera', '2002-02-12', 'Male', '0771111112', 'Kandy', 'Nimal Perera', '0719000002', '2025-01-10', '', 'active', '2026-05-22 07:28:39', '2026-05-22 07:28:39'),
(3, 18, 'ST003', 'Shehani', 'Silva', '2004-03-10', 'Female', '0771111113', 'Galle', 'Kamal Silva', '0719000003', '2025-01-10', '', 'active', '2026-05-22 07:28:39', '2026-05-22 07:28:39'),
(4, 19, 'ST004', 'Pasindu', 'Fernando', '2003-11-21', 'Male', '0771111114', 'Matara', 'Ajith Fernando', '0719000004', '2025-01-10', '', 'active', '2026-05-22 07:28:39', '2026-05-22 07:28:39'),
(5, 20, 'ST005', 'Tharushi', 'Peris', '2002-09-09', 'Female', '0771111115', 'Kurunegala', 'Ranjith Peris', '0719000005', '2025-01-10', '', 'active', '2026-05-22 07:28:39', '2026-05-22 07:28:39'),
(6, 21, 'ST006', 'Isuru', 'Lakshan', '2003-06-18', 'Male', '0771111116', 'Jaffna', 'Nihal Lakshan', '0719000006', '2025-01-10', '', 'active', '2026-05-22 07:28:39', '2026-05-22 07:28:39'),
(7, 16, 'ST007', 'Dinuka', 'Dias', '2004-04-17', 'Male', '0771111117', 'Negombo', 'Saman Dias', '0719000007', '2025-01-10', '', 'active', '2026-05-22 07:28:39', '2026-05-22 07:28:39'),
(8, 17, 'ST008', 'Anudi', 'Kumari', '2003-05-08', 'Female', '0771111118', 'Anuradhapura', 'Kumara Kumari', '0719000008', '2025-01-10', '', 'active', '2026-05-22 07:28:39', '2026-05-22 07:28:39'),
(9, 18, 'ST009', 'Yasas', 'Rathnayake', '2002-07-19', 'Male', '0771111119', 'Badulla', 'Rohana Rathnayake', '0719000009', '2025-01-10', '', 'active', '2026-05-22 07:28:39', '2026-05-22 07:28:39'),
(10, 19, 'ST010', 'Piumi', 'Fernando', '2004-12-01', 'Female', '0771111120', 'Ratnapura', 'Anura Fernando', '0719000010', '2025-01-10', '', 'active', '2026-05-22 07:28:39', '2026-05-22 07:28:39');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `subject_code` varchar(20) NOT NULL,
  `subject_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `class_id` int(11) DEFAULT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `subject_code`, `subject_name`, `description`, `class_id`, `teacher_id`, `created_at`) VALUES
(1, 'SUB101', 'Programming Fundamentals', 'Programming Basics', 1, 1, '2026-05-22 07:27:56'),
(2, 'SUB102', 'Database Systems', 'MySQL and SQL', 2, 2, '2026-05-22 07:27:56'),
(3, 'SUB103', 'Web Development', 'HTML CSS JS PHP', 3, 3, '2026-05-22 07:27:56'),
(4, 'SUB104', 'Artificial Intelligence', 'AI Concepts', 4, 4, '2026-05-22 07:27:56'),
(5, 'SUB105', 'Machine Learning', 'ML Algorithms', 5, 5, '2026-05-22 07:27:56'),
(6, 'SUB106', 'Cyber Security', 'Security Fundamentals', 6, 6, '2026-05-22 07:27:56'),
(7, 'SUB107', 'Cloud Computing', 'Cloud Basics', 7, 7, '2026-05-22 07:27:56'),
(8, 'SUB108', 'Data Structures', 'Algorithms and DS', 8, 8, '2026-05-22 07:27:56'),
(9, 'SUB109', 'Networking', 'Computer Networks', 9, 9, '2026-05-22 07:27:56'),
(10, 'SUB110', 'Software Engineering', 'SE Principles', 10, 10, '2026-05-22 07:27:56');

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `teacher_id` varchar(20) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `qualification` varchar(100) DEFAULT NULL,
  `specialization` varchar(100) DEFAULT NULL,
  `joining_date` date DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teachers`
--

INSERT INTO `teachers` (`id`, `user_id`, `teacher_id`, `first_name`, `last_name`, `date_of_birth`, `gender`, `phone`, `email`, `address`, `qualification`, `specialization`, `joining_date`, `photo`, `status`, `created_at`, `updated_at`) VALUES
(1, 12, 'T001', 'Kasun', 'Perera', '1985-05-12', 'Male', '0711234561', 'teacher1@sms.com', 'Colombo', 'BSc IT', 'Software Engineering', '2022-01-10', '', 'active', '2026-05-22 07:26:40', '2026-05-22 07:26:40'),
(2, 13, 'T002', 'Nimal', 'Fernando', '1982-08-15', 'Male', '0711234562', 'teacher2@sms.com', 'Kandy', 'MSc CS', 'Networking', '2021-02-11', '', 'active', '2026-05-22 07:26:40', '2026-05-22 07:26:40'),
(3, 14, 'T003', 'Amali', 'Silva', '1990-02-10', 'Female', '0711234563', 'teacher3@sms.com', 'Galle', 'BSc SE', 'Web Development', '2020-05-15', '', 'active', '2026-05-22 07:26:40', '2026-05-22 07:26:40'),
(4, 15, 'T004', 'Ruwan', 'Dias', '1987-03-20', 'Male', '0711234564', 'teacher4@sms.com', 'Kurunegala', 'MBA', 'Management', '2023-01-12', '', 'active', '2026-05-22 07:26:40', '2026-05-22 07:26:40'),
(5, 12, 'T005', 'Saman', 'Jayasuriya', '1988-07-01', 'Male', '0711234565', 'saman@sms.com', 'Matara', 'BSc IT', 'Databases', '2022-07-20', '', 'active', '2026-05-22 07:26:40', '2026-05-22 07:26:40'),
(6, 13, 'T006', 'Dilani', 'Peris', '1991-11-25', 'Female', '0711234566', 'dilani@sms.com', 'Jaffna', 'MSc AI', 'AI', '2024-01-10', '', 'active', '2026-05-22 07:26:40', '2026-05-22 07:26:40'),
(7, 14, 'T007', 'Tharindu', 'Lakshan', '1989-04-17', 'Male', '0711234567', 'tharindu@sms.com', 'Negombo', 'BSc CS', 'Cyber Security', '2021-09-01', '', 'active', '2026-05-22 07:26:40', '2026-05-22 07:26:40'),
(8, 15, 'T008', 'Nadeesha', 'Kumari', '1992-06-14', 'Female', '0711234568', 'nadeesha@sms.com', 'Anuradhapura', 'BSc DS', 'Data Science', '2020-10-10', '', 'active', '2026-05-22 07:26:40', '2026-05-22 07:26:40'),
(9, 12, 'T009', 'Chamara', 'Rathnayake', '1986-09-19', 'Male', '0711234569', 'chamara@sms.com', 'Badulla', 'MSc SE', 'Programming', '2022-03-05', '', 'active', '2026-05-22 07:26:40', '2026-05-22 07:26:40'),
(10, 13, 'T010', 'Iresha', 'Fernando', '1993-12-01', 'Female', '0711234570', 'iresha@sms.com', 'Ratnapura', 'BSc IT', 'Cloud Computing', '2023-06-18', '', 'active', '2026-05-22 07:26:40', '2026-05-22 07:26:40');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('admin','teacher','student') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `role`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$iScVlqCAn5ZNXM2THHeynOkqpNOmCkTq/JymF6VRfg6zK6TwGTKGW', 'admin@sms.com', 'admin', '2026-05-20 16:46:29', '2026-05-22 07:01:38'),
(3, 'admin1', '$2y$10$xb049gWNhFzw/3sQehreMO/GzPZhAk/uPQQi/yESYlSRnM78kr3NG', '', 'admin', '2026-05-21 02:21:55', '2026-05-22 07:05:40'),
(5, 'teacher1', '$2y$10$Ujyg7Wq3tXa6eW7rZBnhb.5ui.aFaU2jj2fH3dCumjwd3jqU4SGNa', 'teacher1@gmail.com', 'teacher', '2026-05-21 02:23:17', '2026-05-22 07:06:07'),
(6, 'student1', '$2y$10$xb049gWNhFzw/3sQehreMO/GzPZhAk/uPQQi/yESYlSRnM78kr3NG', 'student1@gmail.com', 'student', '2026-05-21 02:24:01', '2026-05-22 07:43:12'),
(11, 'admin2', '$2y$10$xb049gWNhFzw/3sQehreMO/GzPZhAk/uPQQi/yESYlSRnM78kr3NG', 'admin2@sms.com', 'admin', '2026-05-22 07:19:35', '2026-05-22 07:43:20'),
(13, 'teacher2', '$2y$10$xb049gWNhFzw/3sQehreMO/GzPZhAk/uPQQi/yESYlSRnM78kr3NG', 'teacher2@sms.com', 'teacher', '2026-05-22 07:19:35', '2026-05-22 07:43:28'),
(14, 'teacher3', '$2y$10$xb049gWNhFzw/3sQehreMO/GzPZhAk/uPQQi/yESYlSRnM78kr3NG', 'teacher3@sms.com', 'teacher', '2026-05-22 07:19:35', '2026-05-22 07:43:34'),
(15, 'teacher4', '$2y$10$xb049gWNhFzw/3sQehreMO/GzPZhAk/uPQQi/yESYlSRnM78kr3NG', 'teacher4@sms.com', 'teacher', '2026-05-22 07:19:35', '2026-05-22 07:43:38'),
(17, 'student2', '$2y$10$xb049gWNhFzw/3sQehreMO/GzPZhAk/uPQQi/yESYlSRnM78kr3NG', 'student2@sms.com', 'student', '2026-05-22 07:19:35', '2026-05-22 07:43:45'),
(18, 'student3', '$2y$10$xb049gWNhFzw/3sQehreMO/GzPZhAk/uPQQi/yESYlSRnM78kr3NG', 'student3@sms.com', 'student', '2026-05-22 07:19:35', '2026-05-22 07:43:53'),
(19, 'student4', '$2y$10$xb049gWNhFzw/3sQehreMO/GzPZhAk/uPQQi/yESYlSRnM78kr3NG', 'student4@sms.com', 'student', '2026-05-22 07:19:35', '2026-05-22 07:43:58'),
(20, 'student5', '$2y$10$xb049gWNhFzw/3sQehreMO/GzPZhAk/uPQQi/yESYlSRnM78kr3NG', 'student5@sms.com', 'student', '2026-05-22 07:19:35', '2026-05-22 07:44:03'),
(21, 'student6', '$2y$10$xb049gWNhFzw/3sQehreMO/GzPZhAk/uPQQi/yESYlSRnM78kr3NG', 'student6@sms.com', 'student', '2026-05-22 07:19:35', '2026-05-22 07:44:08');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `posted_by` (`posted_by`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_attendance` (`student_id`,`class_id`,`date`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `course_code` (`course_code`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `exams`
--
ALTER TABLE `exams`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_grade` (`student_id`,`exam_id`),
  ADD KEY `exam_id` (`exam_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_id` (`student_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `subject_code` (`subject_code`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `teacher_id` (`teacher_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `exams`
--
ALTER TABLE `exams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`posted_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `classes`
--
ALTER TABLE `classes`
  ADD CONSTRAINT `classes_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `classes_ibfk_2` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exams`
--
ALTER TABLE `exams`
  ADD CONSTRAINT `exams_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exams_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `grades`
--
ALTER TABLE `grades`
  ADD CONSTRAINT `grades_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `grades_ibfk_2` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subjects`
--
ALTER TABLE `subjects`
  ADD CONSTRAINT `subjects_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `subjects_ibfk_2` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `teachers`
--
ALTER TABLE `teachers`
  ADD CONSTRAINT `teachers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
