CREATE DATABASE IF NOT EXISTS aittendance_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE aittendance_db;

CREATE TABLE IF NOT EXISTS departments (
    department_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    dept_name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS roles (
    role_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL UNIQUE,
    description VARCHAR(255) NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS employees (
    employee_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    employee_code VARCHAR(50) NOT NULL UNIQUE,
    department_id INT NOT NULL,
    role_id INT NOT NULL DEFAULT 3,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    employment_type ENUM('Plantilla', 'Job Order') NOT NULL DEFAULT 'Job Order',
    daily_rate DECIMAL(10,2) NOT NULL DEFAULT 401.00,
    hourly_rate DECIMAL(10,4) GENERATED ALWAYS AS (daily_rate / 8.0000) STORED,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_employee_department FOREIGN KEY (department_id) REFERENCES departments(department_id) ON DELETE CASCADE,
    CONSTRAINT fk_employee_role FOREIGN KEY (role_id) REFERENCES roles(role_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS users (
    user_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(80) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('HR', 'Payroll', 'DepartmentHead', 'Employee') NOT NULL,
    employee_id INT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_users_employee (employee_id),
    CONSTRAINT fk_user_employee FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS attendances (
    attendance_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    log_date DATE NOT NULL,
    time_in DATETIME NULL,
    time_out DATETIME NULL,
    hours_rendered DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    status ENUM('On-Time', 'Late', 'Incomplete') NOT NULL DEFAULT 'On-Time',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_emp_date (employee_id, log_date),
    CONSTRAINT fk_attendance_employee FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS payroll_records (
    payroll_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    total_hours DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    daily_rate DECIMAL(10,2) NOT NULL,
    hourly_rate DECIMAL(10,4) NOT NULL,
    gross_salary DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    computed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_payroll_employee FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT IGNORE INTO roles (role_id, role_name, description) VALUES
    (1, 'HR', 'Human resources administrator'),
    (2, 'Payroll', 'Payroll report access'),
    (3, 'Employee', 'Municipal employee attendance');

INSERT IGNORE INTO departments (dept_name) VALUES
    ('Office of the Mayor'),
    ('Human Resource Management Office'),
    ('Municipal Accounting Office'),
    ('Municipal Social Welfare and Development Office');