<?php
declare(strict_types=1);

final class EmployeeController
{
    public function __construct(private PDO $db)
    {
    }

    public function index(): void
    {
        Security::requireRole(['HR']);
        $error = $_SESSION['flash_error'] ?? null;
        $success = $_SESSION['flash_success'] ?? null;
        unset($_SESSION['flash_error'], $_SESSION['flash_success']);
        $model = new Employee($this->db);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::verifyCsrf($_POST['csrf_token'] ?? null)) {
                http_response_code(419);
                $error = 'Your session expired. Reload the page and try again.';
            } else {
                $data = [
                    'employee_code' => trim($_POST['employee_code'] ?? ''),
                    'first_name' => trim($_POST['first_name'] ?? ''),
                    'last_name' => trim($_POST['last_name'] ?? ''),
                    'employment_type' => $_POST['employment_type'] ?? '',
                    'department_id' => $_POST['department_id'] ?? '',
                    'daily_rate' => $_POST['daily_rate'] ?? '',
                ];
                $rateValid = is_numeric($data['daily_rate']) && (float) $data['daily_rate'] > 0
                    && (float) $data['daily_rate'] <= 99999999.99;
                if ($data['employment_type'] === 'JO') {
                    $rateValid = $rateValid && (float) $data['daily_rate'] >= 401 && (float) $data['daily_rate'] <= 414;
                }
                if ($data['employee_code'] === '' || $data['first_name'] === '' || $data['last_name'] === ''
                    || !in_array($data['employment_type'], ['Permanent', 'JO'], true) || !$rateValid) {
                    $error = 'Enter valid employee details and daily salary. JO rates must be ₱401.00 to ₱414.00.';
                } else {
                    try {
                        $model->create($data);
                        $success = 'Employee added.';
                    } catch (Throwable $exception) {
                        $error = $exception->getCode() === '23000'
                            ? 'That employee code already exists.'
                            : ($exception instanceof RuntimeException ? $exception->getMessage() : 'Employee could not be saved.');
                        error_log($exception->getMessage());
                    }
                }
            }
        }
        $employees = $model->all();
        $departments = $model->departments();
        require dirname(__DIR__) . '/Views/employees/index.php';
    }

    public function updateDetails(): void
    {
        Security::requireRole(['HR']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Security::verifyCsrf($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            exit('Invalid or expired form session. Reload the page and try again.');
        }

        $employeeId = filter_var($_POST['employee_id'] ?? null, FILTER_VALIDATE_INT);
        $departmentId = filter_var($_POST['department_id'] ?? null, FILTER_VALIDATE_INT);
        $data = [
            'department_id' => $departmentId,
            'employment_type' => $_POST['employment_type'] ?? '',
            'daily_rate' => $_POST['daily_rate'] ?? '',
        ];
        $rateValid = is_numeric($data['daily_rate']) && (float) $data['daily_rate'] > 0
            && (float) $data['daily_rate'] <= 99999999.99;
        if ($data['employment_type'] === 'JO') {
            $rateValid = $rateValid && (float) $data['daily_rate'] >= 401 && (float) $data['daily_rate'] <= 414;
        }

        if (!$employeeId || !$departmentId
            || !in_array($data['employment_type'], ['Permanent', 'JO'], true) || !$rateValid) {
            $_SESSION['flash_error'] = 'Choose a valid department and salary. Daily rate must be positive; JO rates must be ₱401.00 to ₱414.00.';
        } else {
            try {
                (new Employee($this->db))->updateDetails($employeeId, $data);
                $_SESSION['flash_success'] = 'Employee department and daily salary updated.';
            } catch (Throwable $exception) {
                $_SESSION['flash_error'] = $exception instanceof RuntimeException
                    ? $exception->getMessage()
                    : 'Employee details could not be updated.';
                error_log($exception->getMessage());
            }
        }

        header('Location: index.php?action=employees');
    }

    public function createAccount(): void
    {
        Security::requireRole(['HR']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Security::verifyCsrf($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            exit('Invalid or expired form session. Reload the page and try again.');
        }

        $employeeId = filter_var($_POST['employee_id'] ?? null, FILTER_VALIDATE_INT);
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        if (!$employeeId || strlen($username) < 3 || strlen($username) > 80 || strlen($password) < 12) {
            $_SESSION['flash_error'] = 'Use a valid employee, a username of 3 to 80 characters, and a password of at least 12 characters.';
        } else {
            try {
                (new User($this->db))->createEmployeeAccount($employeeId, $username, $password);
                $_SESSION['flash_success'] = 'Employee login created. Share the temporary password with the employee securely.';
            } catch (PDOException $exception) {
                $_SESSION['flash_error'] = $exception->getCode() === '23000'
                    ? 'That username is already in use, or this employee already has a login.'
                    : 'Employee login could not be created.';
                error_log($exception->getMessage());
            } catch (RuntimeException $exception) {
                $_SESSION['flash_error'] = $exception->getMessage();
            }
        }

        header('Location: index.php?action=employees');
    }

    public function setAccountStatus(): void
    {
        Security::requireRole(['HR']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Security::verifyCsrf($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            exit('Invalid or expired form session. Reload the page and try again.');
        }

        $employeeId = filter_var($_POST['employee_id'] ?? null, FILTER_VALIDATE_INT);
        $status = $_POST['status'] ?? '';
        if (!$employeeId || !in_array($status, ['enable', 'disable'], true)) {
            $_SESSION['flash_error'] = 'Choose a valid employee account action.';
        } else {
            $isActive = $status === 'enable';
            if ((new User($this->db))->setEmployeeAccountActive($employeeId, $isActive)) {
                $_SESSION['flash_success'] = $isActive
                    ? 'Employee portal account enabled.'
                    : 'Employee portal account disabled. The employee must contact HR to restore access.';
            } else {
                $_SESSION['flash_error'] = 'This employee does not have a portal account to update.';
            }
        }

        header('Location: index.php?action=employees');
    }
}