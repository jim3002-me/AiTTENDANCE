<?php
declare(strict_types=1);

final class Attendance
{
    public function __construct(private PDO $db)
    {
    }

    public function processScan(int $employeeId): array
    {
        $this->db->beginTransaction();
        try {
            $query = $this->db->prepare(
                'SELECT attendance_id, time_in FROM attendances
                 WHERE employee_id = :employee_id AND log_date = CURRENT_DATE AND time_out IS NULL
                 ORDER BY attendance_id DESC LIMIT 1 FOR UPDATE'
            );
            $query->execute(['employee_id' => $employeeId]);
            $openLog = $query->fetch();
            $now = new DateTimeImmutable();

            if (!$openLog) {
                $completedQuery = $this->db->prepare(
                    'SELECT attendance_id FROM attendances
                     WHERE employee_id = :employee_id AND log_date = CURRENT_DATE LIMIT 1 FOR UPDATE'
                );
                $completedQuery->execute(['employee_id' => $employeeId]);
                if ($completedQuery->fetch()) {
                    $this->db->commit();
                    return ['status' => 'error', 'message' => 'Attendance is already complete for today.'];
                }
            }

            if ($openLog) {
                $timeIn = new DateTimeImmutable($openLog['time_in']);
                $hours = max(0, ($now->getTimestamp() - $timeIn->getTimestamp()) / 3600);
                if ($hours >= 5) {
                    $hours -= 1;
                }
                $hours = round($hours, 2);
                $update = $this->db->prepare(
                    'UPDATE attendances SET time_out = :time_out, hours_rendered = :hours
                     WHERE attendance_id = :id'
                );
                $update->execute([
                    'time_out' => $now->format('Y-m-d H:i:s'),
                    'hours' => number_format($hours, 2, '.', ''),
                    'id' => $openLog['attendance_id'],
                ]);
                $this->db->commit();
                return ['status' => 'success', 'message' => 'Time-out recorded.', 'type' => 'Out', 'time' => $now->format('H:i:s'), 'hours' => $hours];
            }

            $status = $now->format('H:i:s') > '08:15:00' ? 'Late' : 'On-Time';
            $insert = $this->db->prepare(
                'INSERT INTO attendances (employee_id, log_date, time_in, status)
                 VALUES (:employee_id, CURRENT_DATE, :time_in, :status)'
            );
            $insert->execute([
                'employee_id' => $employeeId,
                'time_in' => $now->format('Y-m-d H:i:s'),
                'status' => $status,
            ]);
            $this->db->commit();
            return ['status' => 'success', 'message' => 'Time-in recorded.', 'type' => 'In', 'time' => $now->format('H:i:s')];
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $exception;
        }
    }

    public function today(): array
    {
        $query = $this->db->query(
            'SELECT a.*, e.employee_code, CONCAT(e.first_name, " ", e.last_name) AS full_name,
                    d.dept_name
             FROM attendances a JOIN employees e ON e.employee_id = a.employee_id
             LEFT JOIN departments d ON d.department_id = e.department_id
             WHERE a.log_date = CURRENT_DATE ORDER BY a.created_at DESC'
        );
        return $query->fetchAll();
    }
}