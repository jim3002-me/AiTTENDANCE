<?php
declare(strict_types=1);

final class Dtr
{
    public function __construct(private PDO $db)
    {
    }

    public function activeEmployees(): array
    {
        return $this->db->query(
            'SELECT e.employee_id, e.employee_code, e.first_name, e.last_name,
                    e.employment_type, e.daily_rate, d.dept_name
             FROM employees e
             LEFT JOIN departments d ON d.department_id = e.department_id
             WHERE e.is_active = 1 ORDER BY e.last_name, e.first_name'
        )->fetchAll();
    }

    public function employeeMonth(int $employeeId, string $monthStart, string $monthEnd): ?array
    {
        $employeeQuery = $this->db->prepare(
            'SELECT e.employee_id, e.employee_code, e.first_name, e.last_name,
                    e.employment_type, e.daily_rate, d.dept_name
             FROM employees e LEFT JOIN departments d ON d.department_id = e.department_id
             WHERE e.employee_id = :employee_id AND e.is_active = 1 LIMIT 1'
        );
        $employeeQuery->execute(['employee_id' => $employeeId]);
        $employee = $employeeQuery->fetch();
        if (!$employee) {
            return null;
        }

        $attendanceQuery = $this->db->prepare(
            'SELECT log_date, time_in, time_out, hours_rendered, status
             FROM attendances
             WHERE employee_id = :employee_id AND log_date BETWEEN :month_start AND :month_end
             ORDER BY log_date'
        );
        $attendanceQuery->execute([
            'employee_id' => $employeeId,
            'month_start' => $monthStart,
            'month_end' => $monthEnd,
        ]);

        return ['employee' => $employee, 'records' => $attendanceQuery->fetchAll()];
    }
}