<?php
declare(strict_types=1);

final class PayrollController
{
    public function __construct(private PDO $db)
    {
    }

    public function index(): void
    {
        Security::requireRole(['HR', 'Payroll']);
        $from = $_GET['from'] ?? date('Y-m-01');
        $to = $_GET['to'] ?? date('Y-m-d');
        $validDates = DateTime::createFromFormat('!Y-m-d', $from) && DateTime::createFromFormat('!Y-m-d', $to)
            && $from <= $to;
        $rows = $validDates ? (new Payroll($this->db))->summary($from, $to) : [];
        if (!$validDates) {
            http_response_code(422);
        }

        if (($_GET['format'] ?? '') === 'csv' && $validDates) {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="jo-payroll-' . $from . '-to-' . $to . '.csv"');
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Employee code', 'Employee name', 'Department', 'Daily rate', 'Hours rendered', 'Gross pay']);
            foreach ($rows as $row) {
                fputcsv($output, [$row['employee_code'], $row['first_name'] . ' ' . $row['last_name'], $row['dept_name'], $row['daily_rate'], $row['total_hours'], $row['gross_pay']]);
            }
            fclose($output);
            return;
        }

        require dirname(__DIR__) . '/Views/payroll/index.php';
    }
}