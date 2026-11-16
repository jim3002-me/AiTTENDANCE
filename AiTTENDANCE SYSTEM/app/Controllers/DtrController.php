<?php
declare(strict_types=1);

final class DtrController
{
    public function __construct(private PDO $db)
    {
    }

    public function index(): void
    {
        Security::requireRole(['HR']);
        $model = new Dtr($this->db);
        $employees = $model->activeEmployees();
        $monthInput = $_GET['month'] ?? date('Y-m');
        $month = is_string($monthInput) ? $monthInput : '';
        $employeeId = filter_var($_GET['employee_id'] ?? null, FILTER_VALIDATE_INT) ?: null;
        $error = null;
        $report = null;

        $monthDate = preg_match('/^\d{4}-\d{2}$/', $month)
            ? DateTimeImmutable::createFromFormat('!Y-m', $month)
            : false;
        if (!$monthDate || $monthDate->format('Y-m') !== $month) {
            $error = 'Choose a valid report month.';
        } elseif ($employeeId) {
            $monthStart = $monthDate->format('Y-m-01');
            $monthEnd = $monthDate->modify('last day of this month')->format('Y-m-d');
            $report = $model->employeeMonth($employeeId, $monthStart, $monthEnd);
            if (!$report) {
                $error = 'That active employee could not be found.';
            } else {
                $report['month'] = $monthDate;
                $report['days'] = (int) $monthDate->format('t');
                $recordsByDate = [];
                foreach ($report['records'] as $record) {
                    $recordsByDate[$record['log_date']] = $record;
                }
                $report['records_by_date'] = $recordsByDate;
            }
        }

        require dirname(__DIR__) . '/Views/dtr/index.php';
    }
}