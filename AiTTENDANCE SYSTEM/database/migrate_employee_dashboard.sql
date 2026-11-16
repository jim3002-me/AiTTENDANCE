USE aittendance_db;

DROP INDEX idx_users_employee ON users;

ALTER TABLE users
    MODIFY role ENUM('HR', 'Payroll', 'DepartmentHead', 'Employee') NOT NULL,
    ADD COLUMN employee_id INT NULL AFTER role,
    ADD UNIQUE KEY uq_users_employee (employee_id),
    ADD CONSTRAINT fk_user_employee FOREIGN KEY (employee_id)
        REFERENCES employees(employee_id) ON DELETE CASCADE;