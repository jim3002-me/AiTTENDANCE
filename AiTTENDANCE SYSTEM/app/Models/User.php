<?php
declare(strict_types=1);

final class User
{
    public function __construct(private PDO $db)
    {
    }

    public function authenticate(string $username, string $password): ?array
    {
        $statement = $this->db->prepare(
            'SELECT user_id, username, password_hash, role, employee_id FROM users WHERE username = :username AND is_active = 1 LIMIT 1'
        );
        $statement->execute(['username' => $username]);
        $user = $statement->fetch();
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return null;
        }
        unset($user['password_hash']);
        return $user;
    }

    public function hasAccounts(): bool
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM users')->fetchColumn() > 0;
    }

    public function createFirstHrAccount(string $username, string $password): bool
    {
        $this->db->beginTransaction();
        try {
            if ($this->hasAccounts()) {
                $this->db->rollBack();
                return false;
            }
            $statement = $this->db->prepare(
                'INSERT INTO users (username, password_hash, role)
                 VALUES (:username, :password_hash, "HR")'
            );
            $statement->execute([
                'username' => $username,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            ]);
            $this->db->commit();
            return true;
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $exception;
        }
    }

    public function createEmployeeAccount(int $employeeId, string $username, string $password): void
    {
        $employee = $this->db->prepare(
            'SELECT employee_id FROM employees WHERE employee_id = :employee_id AND is_active = 1 LIMIT 1'
        );
        $employee->execute(['employee_id' => $employeeId]);
        if (!$employee->fetchColumn()) {
            throw new RuntimeException('The selected active employee could not be found.');
        }

        $statement = $this->db->prepare(
            'INSERT INTO users (username, password_hash, role, employee_id)
             VALUES (:username, :password_hash, "Employee", :employee_id)'
        );
        $statement->execute([
            'username' => $username,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'employee_id' => $employeeId,
        ]);
    }

    public function setEmployeeAccountActive(int $employeeId, bool $isActive): bool
    {
        $lookup = $this->db->prepare(
            'SELECT user_id FROM users WHERE employee_id = :employee_id AND role = "Employee" LIMIT 1'
        );
        $lookup->execute(['employee_id' => $employeeId]);
        $userId = $lookup->fetchColumn();
        if (!$userId) {
            return false;
        }

        $update = $this->db->prepare(
            'UPDATE users SET is_active = :is_active WHERE user_id = :user_id AND role = "Employee"'
        );
        $update->execute(['is_active' => (int) $isActive, 'user_id' => $userId]);
        return true;
    }

    public function isEmployeeAccountActive(int $userId): bool
    {
        $statement = $this->db->prepare(
            'SELECT 1 FROM users WHERE user_id = :user_id AND role = "Employee" AND is_active = 1 LIMIT 1'
        );
        $statement->execute(['user_id' => $userId]);
        return (bool) $statement->fetchColumn();
    }
}