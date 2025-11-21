CREATE TABLE IF NOT EXISTS users (
  user_id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) UNIQUE NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  user_type ENUM('student','admin','coordinator') NOT NULL,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS companies (
  company_id INT AUTO_INCREMENT PRIMARY KEY,
  company_name VARCHAR(150) NOT NULL,
  address VARCHAR(255),
  supervisor_name VARCHAR(100),
  contact_number VARCHAR(50),
  email VARCHAR(100)
);

CREATE TABLE IF NOT EXISTS students (
  student_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  first_name VARCHAR(100) NOT NULL,
  last_name VARCHAR(100) NOT NULL,
  middle_name VARCHAR(100),
  course VARCHAR(100),
  year_level VARCHAR(20),
  contact_number VARCHAR(50),
  email_address VARCHAR(100),
  address VARCHAR(255),
  company_id INT,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  FOREIGN KEY (company_id) REFERENCES companies(company_id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS daily_time_records (
  dtr_id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  record_date DATE NOT NULL,
  time_in TIME,
  time_out TIME,
  daily_hours DECIMAL(5,2) DEFAULT 0,
  status ENUM('present','absent','late','pending') DEFAULT 'pending',
  notes VARCHAR(255),
  FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
  UNIQUE KEY uniq_student_date (student_id, record_date)
);

CREATE TABLE IF NOT EXISTS activity_logs (
  activity_id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  week_starting DATE NOT NULL,
  week_ending DATE NOT NULL,
  task_description TEXT,
  hours_rendered DECIMAL(5,2) DEFAULT 0,
  accomplishments TEXT,
  status ENUM('draft','submitted','approved','rejected') DEFAULT 'draft',
  FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
  UNIQUE KEY uniq_student_week (student_id, week_starting, week_ending)
);

CREATE TABLE IF NOT EXISTS announcements (
  announcement_id INT AUTO_INCREMENT PRIMARY KEY,
  admin_id INT NOT NULL,
  title VARCHAR(150) NOT NULL,
  content TEXT NOT NULL,
  announcement_type ENUM('event','deadline','instruction','general') DEFAULT 'general',
  posted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  scheduled_date DATE,
  is_active TINYINT(1) DEFAULT 1,
  FOREIGN KEY (admin_id) REFERENCES users(user_id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS hours_summary (
  summary_id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL UNIQUE,
  total_hours DECIMAL(7,2) DEFAULT 0,
  last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE
);

