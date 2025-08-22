-- TDD ITERATION 2: Database Schema - CORRECT SEQUENCE
-- Tables must be created in dependency order to avoid foreign key errors

-- ================================================================
-- STEP 1: Create base tables WITHOUT foreign key dependencies
-- ================================================================

-- 1.1 Users table (no dependencies)
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` INT(11) NOT NULL AUTO_INCREMENT,
  `school_id` VARCHAR(50) NOT NULL UNIQUE,
  `full_name` VARCHAR(100) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin','faculty','student') NOT NULL,
  `year_level` INT(11) DEFAULT NULL,
  `section` VARCHAR(10) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 1.2 Subjects table (no dependencies)
CREATE TABLE IF NOT EXISTS `subjects` (
    `subject_id` INT(11) NOT NULL AUTO_INCREMENT,
    `subject_code` VARCHAR(20) NOT NULL UNIQUE,
    `subject_name` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `units` INT(11) NOT NULL DEFAULT 3,
    `year_level` VARCHAR(20) NOT NULL,
    `semester` VARCHAR(20) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`subject_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ================================================================
-- STEP 2: Insert base data (before creating dependent tables)
-- ================================================================

-- 2.1 Insert users with properly hashed passwords
INSERT INTO `users` (`user_id`, `school_id`, `full_name`, `password`, `role`, `year_level`, `section`) VALUES
(1, 'ADMIN001', 'Admin User', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NULL, NULL),
(2, 'FAC001', 'Dr. John Smith', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'faculty', NULL, NULL),
(3, 'FAC002', 'Dr. Jane Doe', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'faculty', NULL, NULL),
(4, '2020-001', 'Student One', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 2, 'A'),
(5, '2020-002', 'Student Two', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 2, 'A'),
(6, '2021-001', 'Student Three', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 3, 'A'),
(7, '2021-002', 'Student Four', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 3, 'A')
ON DUPLICATE KEY UPDATE 
    `full_name` = VALUES(`full_name`),
    `password` = VALUES(`password`),
    `role` = VALUES(`role`),
    `year_level` = VALUES(`year_level`),
    `section` = VALUES(`section`);

-- 2.2 Insert subjects data
INSERT INTO `subjects` (`subject_code`, `subject_name`, `description`, `units`, `year_level`, `semester`) VALUES
('CS101', 'Introduction to Computer Science', 'Basic computer science concepts', 3, '1st Year', '1st Semester'),
('MATH101', 'College Algebra', 'Fundamental algebraic concepts', 3, '1st Year', '1st Semester'),
('ENG101', 'English Communication', 'Basic English communication skills', 3, '1st Year', '1st Semester')
ON DUPLICATE KEY UPDATE 
    `subject_name` = VALUES(`subject_name`),
    `description` = VALUES(`description`);

-- ================================================================
-- STEP 3: Create tables with foreign key dependencies
-- ================================================================

-- 3.1 Subject-Faculty assignment table (depends on users + subjects)
CREATE TABLE IF NOT EXISTS `subject_faculty` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `subject_id` INT(11) NOT NULL,
    `faculty_id` INT(11) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_assignment` (`subject_id`, `faculty_id`),
    CONSTRAINT `fk_subject_faculty_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`subject_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_subject_faculty_faculty` FOREIGN KEY (`faculty_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3.2 Exams table (depends on subjects + users)
CREATE TABLE IF NOT EXISTS `exams` (
    `exam_id` INT(11) NOT NULL AUTO_INCREMENT,
    `exam_title` VARCHAR(255) NOT NULL,
    `subject_id` INT(11) NOT NULL,
    `created_by` INT(11) NOT NULL,
    `duration` INT(11) NOT NULL DEFAULT 60, -- in minutes
    `total_points` INT(11) NOT NULL DEFAULT 100,
    `instructions` TEXT,
    `exam_date` DATE NOT NULL,
    `start_time` TIME NOT NULL,
    `end_time` TIME NOT NULL,
    `status` ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`exam_id`),
    CONSTRAINT `fk_exams_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`subject_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_exams_creator` FOREIGN KEY (`created_by`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ================================================================
-- STEP 4: Create tables that depend on exams
-- ================================================================

-- 4.1 Questions table (depends on exams)
CREATE TABLE IF NOT EXISTS `questions` (
    `question_id` INT(11) NOT NULL AUTO_INCREMENT,
    `exam_id` INT(11) NOT NULL,
    `question_text` TEXT NOT NULL,
    `question_type` ENUM('multiple_choice', 'true_false', 'essay', 'fill_blank') NOT NULL,
    `points` INT(11) NOT NULL DEFAULT 1,
    `order_number` INT(11) NOT NULL DEFAULT 1,
    `options` JSON, -- For multiple choice options
    `correct_answer` TEXT, -- Correct answer(s)
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`question_id`),
    INDEX `idx_exam_order` (`exam_id`, `order_number`),
    CONSTRAINT `fk_questions_exam` FOREIGN KEY (`exam_id`) REFERENCES `exams`(`exam_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 4.2 Exam results table (depends on exams + users)
CREATE TABLE IF NOT EXISTS `exam_results` (
    `result_id` INT(11) NOT NULL AUTO_INCREMENT,
    `exam_id` INT(11) NOT NULL,
    `student_id` INT(11) NOT NULL,
    `answers` JSON NOT NULL, -- Student's answers
    `score` INT(11) NOT NULL DEFAULT 0,
    `total_points` INT(11) NOT NULL,
    `time_taken` INT(11), -- in minutes
    `status` ENUM('in_progress', 'completed', 'submitted') DEFAULT 'in_progress',
    `submitted_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`result_id`),
    UNIQUE KEY `unique_student_exam` (`exam_id`, `student_id`),
    CONSTRAINT `fk_exam_results_exam` FOREIGN KEY (`exam_id`) REFERENCES `exams`(`exam_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_exam_results_student` FOREIGN KEY (`student_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ================================================================
-- STEP 5: Insert sample data for dependent tables
-- ================================================================

-- 5.1 Sample exam data
INSERT INTO `exams` (`exam_title`, `subject_id`, `created_by`, `duration`, `total_points`, `instructions`, `exam_date`, `start_time`, `end_time`, `status`) VALUES
('Midterm Examination', 1, 2, 120, 100, 'Read all questions carefully. Choose the best answer.', '2024-03-15', '09:00:00', '11:00:00', 'published'),
('Final Examination', 1, 2, 180, 150, 'This is the final exam. Good luck!', '2024-05-15', '09:00:00', '12:00:00', 'draft')
ON DUPLICATE KEY UPDATE 
    `exam_title` = VALUES(`exam_title`),
    `status` = VALUES(`status`);

-- 5.2 Sample questions
INSERT INTO `questions` (`exam_id`, `question_text`, `question_type`, `points`, `order_number`, `options`, `correct_answer`) VALUES
(1, 'What is the capital of France?', 'multiple_choice', 5, 1, '{"A": "London", "B": "Berlin", "C": "Paris", "D": "Madrid"}', 'C'),
(1, 'PHP stands for PHP: Hypertext Preprocessor', 'true_false', 3, 2, '{"A": "True", "B": "False"}', 'A'),
(1, 'What does MVC stand for?', 'multiple_choice', 7, 3, '{"A": "Model View Controller", "B": "Multiple View Control", "C": "Main Visual Component", "D": "Modern Version Control"}', 'A')
ON DUPLICATE KEY UPDATE 
    `question_text` = VALUES(`question_text`),
    `points` = VALUES(`points`);

-- 5.3 Sample subject-faculty assignments
INSERT INTO `subject_faculty` (`subject_id`, `faculty_id`) VALUES
(1, 2), -- CS101 assigned to Dr. John Smith
(2, 3), -- MATH101 assigned to Dr. Jane Doe
(3, 2)  -- ENG101 assigned to Dr. John Smith
ON DUPLICATE KEY UPDATE 
    `updated_at` = CURRENT_TIMESTAMP;