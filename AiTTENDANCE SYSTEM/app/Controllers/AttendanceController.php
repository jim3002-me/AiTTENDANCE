<?php
declare(strict_types=1);

final class AttendanceController
{
    public function __construct(private PDO $db)
    {
    }

    public function index(): void
    {
        $attendance = new Attendance($this->db);
        $logs = $attendance->today();
        require dirname(__DIR__) . '/Views/attendance/index.php';
    }

    public function scan(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $input = json_decode(file_get_contents('php://input'), true);
        $code = trim((string) ($input['employee_code'] ?? ''));
        if ($code === '') {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'A valid employee QR code is required.']);
            return;
        }

        try {
            $employee = (new Employee($this->db))->findActiveByCode($code);
            if (!$employee) {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'QR code is not assigned to an active employee.']);
                return;
            }
            $result = (new Attendance($this->db))->processScan((int) $employee['employee_id']);
            $result['employee'] = [
                'name' => $employee['first_name'] . ' ' . $employee['last_name'],
                'department' => $employee['dept_name'] ?? 'Unassigned',
            ];
            echo json_encode($result);
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Attendance could not be saved. Check the database connection.']);
        }
    }
}