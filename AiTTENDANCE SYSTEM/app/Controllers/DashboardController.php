<?php
declare(strict_types=1);

final class DashboardController
{
    public function __construct(private PDO $db)
    {
    }

    public function index(): void
    {
        Security::requireRole(['HR', 'Payroll', 'DepartmentHead', 'Employee']);
        $user = $_SESSION['user'];
        $dashboard = new Dashboard($this->db);

        if ($user['role'] === 'Employee') {
            $employee = isset($user['employee_id'])
                ? $dashboard->employee((int) $user['employee_id'])
                : null;
            if (!$employee) {
                http_response_code(403);
                require dirname(__DIR__) . '/Views/errors/unlinked_employee.php';
                return;
            }
            require dirname(__DIR__) . '/Views/dashboard/employee.php';
            return;
        }

        $summary = $dashboard->administrator();
        require dirname(__DIR__) . '/Views/dashboard/admin.php';
    }
}