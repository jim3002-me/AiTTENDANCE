<?php
declare(strict_types=1);

final class Employee
{
    public function __construct(private PDO $db)
    {
    }

    public function findActiveByCode(string $code): ?array
    {
        $query = 'SELECT e.employee_id, e.employee_code, e.first_name, e.last_name,
                         e.employment_type, e.daily_rate, d.dept_name
                  FROM employees e
                  LEFT JOIN departments d ON d.department_id = e.department_id
                  WHERE e.employee_code = :code AND e.is_active = 1 LIMIT 1';
        $statement = $this->db->prepare($query);
        $statement->execute(['code' => $code]);
        $employee = $statement->fetch();

        return $employee ?: null;
    }

    public function all(): array
    {
        return $this->db->query(
            'SELECT e.*, d.dept_name,
                CASE WHEN u.user_id IS NULL THEN 0 ELSE 1 END AS has_login,
                COALESCE(u.is_active, 0) AS login_active
             FROM employees e
             LEFT JOIN departments d ON d.department_id = e.department_id
             LEFT JOIN users u ON u.employee_id = e.employee_id AND u.role = "Employee"
             ORDER BY e.last_name, e.first_name'
        )->fetchAll();
    }

    public function activeForQr(): array
    {
        return $this->db->query(
            'SELECT employee_id, employee_code, first_name, last_name, employment_type, dept_name
             FROM employees e
             LEFT JOIN departments d ON d.department_id = e.department_id
             WHERE e.is_active = 1 ORDER BY e.last_name, e.first_name'
        )->fetchAll();
    }

    public function departments(): array
    {
        return $this->db->query('SELECT department_id, dept_name FROM departments ORDER BY dept_name')->fetchAll();
    }

    public function create(array $data): void
    {
        $roleStatement = $this->db->prepare('SELECT role_id FROM roles WHERE role_name = :role_name LIMIT 1');
        $roleStatement->execute(['role_name' => 'Employee']);
        $roleId = $roleStatement->fetchColumn();
        if (!$roleId) {
            throw new RuntimeException('The Employee role is missing. Import database/schema.sql first.');
        }

        $departmentId = $data['department_id'] ?: $this->db->query('SELECT department_id FROM departments ORDER BY department_id LIMIT 1')->fetchColumn();
        if (!$departmentId) {
            throw new RuntimeException('Create at least one department before adding employees.');
        }
        $statement = $this->db->prepare(
              'INSERT INTO employees (employee_code, first_name, last_name, employment_type,
               department_id, role_id, daily_rate) VALUES (:code, :first_name, :last_name, :type, :department, :role_id, :rate)'
        );
        $statement->execute([
            'code' => $data['employee_code'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'type' => $data['employment_type'] === 'JO' ? 'Job Order' : 'Plantilla',
            'department' => $departmentId,
            'role_id' => $roleId,
            'rate' => $data['daily_rate'],
        ]);
    }

    public function updateDetails(int $employeeId, array $data): void
    {
        $statement = $this->db->prepare(
            'UPDATE employees
             SET department_id = :department,
                 employment_type = :employment_type,
                 daily_rate = :daily_rate
             WHERE employee_id = :employee_id'
        );
        $statement->execute([
            'department' => $data['department_id'],
            'employment_type' => $data['employment_type'] === 'JO' ? 'Job Order' : 'Plantilla',
            'daily_rate' => $data['daily_rate'],
            'employee_id' => $employeeId,
        ]);
        if ($statement->rowCount() === 0) {
            $exists = $this->db->prepare('SELECT 1 FROM employees WHERE employee_id = :employee_id LIMIT 1');
            $exists->execute(['employee_id' => $employeeId]);
            if (!$exists->fetchColumn()) {
                throw new RuntimeException('Employee record could not be found.');
            }
        }
    }
}