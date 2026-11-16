<?php
declare(strict_types=1);

final class Dashboard
{
    public function __construct(private PDO $db)
    {
    }

    public function administrator(): array
    {
        $summary = $this->db->query(
            'SELECT COUNT(*) AS recorded_today,
                    SUM(status = "Late") AS late_today,
                    SUM(time_out IS NULL) AS on_shift
             FROM attendances WHERE log_date = CURRENT_DATE'
        )->fetch();
        $summary['employee_count'] = (int) $this->db->query(
            'SELECT COUNT(*) FROM employees WHERE is_active = 1'
        )->fetchColumn();
        $summary['jo_count'] = (int) $this->db->query(
            'SELECT COUNT(*) FROM employees WHERE is_active = 1 AND employment_type = "Job Order"'
        )->fetchColumn();
        $summary['departments'] = (int) $this->db->query('SELECT COUNT(*) FROM departments')->fetchColumn();
        $summary['recent_logs'] = (new Attendance($this->db))->today();

        return $summary;
    }

    public function employee(int $employeeId): ?array
    {
        $statement = $this->db->prepare(
            'SELECT e.employee_id, e.employee_code, e.first_name, e.last_name,
                    e.employment_type, e.daily_rate, d.dept_name,
                    COUNT(CASE WHEN a.log_date >= DATE_FORMAT(CURRENT_DATE, "%Y-%m-01")
                        THEN a.attendance_id END) AS month_days,
                    SUM(CASE WHEN a.log_date >= DATE_FORMAT(CURRENT_DATE, "%Y-%m-01")
                        AND a.status = "Late" THEN 1 ELSE 0 END) AS month_late_days,
                    COALESCE(SUM(CASE WHEN a.log_date >= DATE_FORMAT(CURRENT_DATE, "%Y-%m-01")
                        THEN a.hours_rendered ELSE 0 END), 0) AS month_hours,
                    COALESCE(SUM(CASE WHEN a.log_date >= DATE_FORMAT(CURRENT_DATE, "%Y-%m-01")
                        AND e.employment_type = "Job Order" AND a.hours_rendered > 0
                        THEN (e.daily_rate / 8) * a.hours_rendered ELSE 0 END), 0) AS month_estimated_pay
             FROM employees e
             LEFT JOIN departments d ON d.department_id = e.department_id
             LEFT JOIN attendances a ON a.employee_id = e.employee_id
             WHERE e.employee_id = :employee_id AND e.is_active = 1
             GROUP BY e.employee_id, e.employee_code, e.first_name, e.last_name,
                      e.employment_type, e.daily_rate, d.dept_name LIMIT 1'
        );
        $statement->execute(['employee_id' => $employeeId]);
        $employee = $statement->fetch();
        if (!$employee) {
            return null;
        }

        $history = $this->db->prepare(
            'SELECT log_date, time_in, time_out, hours_rendered, status
             FROM attendances WHERE employee_id = :employee_id
             ORDER BY log_date DESC LIMIT 20'
        );
        $history->execute(['employee_id' => $employeeId]);
        $employee['history'] = $history->fetchAll();

        return $employee;
    }
}