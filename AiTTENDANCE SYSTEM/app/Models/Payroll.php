<?php
declare(strict_types=1);

final class Payroll
{
    public function __construct(private PDO $db)
    {
    }

    public function summary(string $from, string $to): array
    {
        $statement = $this->db->prepare(
            'SELECT e.employee_code, e.first_name, e.last_name, d.dept_name,
                    e.daily_rate, SUM(a.hours_rendered) AS total_hours,
                    ROUND((e.daily_rate / 8) * SUM(a.hours_rendered), 2) AS gross_pay
             FROM employees e
             JOIN attendances a ON a.employee_id = e.employee_id
             LEFT JOIN departments d ON d.department_id = e.department_id
             WHERE e.employment_type = "Job Order" AND e.is_active = 1
               AND a.log_date BETWEEN :date_from AND :date_to
               AND a.hours_rendered IS NOT NULL
             GROUP BY e.employee_id, e.employee_code, e.first_name, e.last_name, d.dept_name, e.daily_rate
             ORDER BY e.last_name, e.first_name'
        );
        $statement->execute(['date_from' => $from, 'date_to' => $to]);
        return $statement->fetchAll();
    }
}