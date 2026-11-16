<?php
declare(strict_types=1);

ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_httponly', '1');
$isHttps = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== '' && strtolower((string) $_SERVER['HTTPS']) !== 'off';
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => $isHttps,
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
spl_autoload_register(static function (string $class): void {
    foreach (['Core', 'Config', 'Models', 'Controllers'] as $directory) {
        $file = dirname(__DIR__) . '/app/' . $directory . '/' . $class . '.php';
        if (is_file($file)) {
            require_once $file;
            return;
        }
    }
});

try {
    $db = Database::connection();
    $sessionUser = $_SESSION['user'] ?? null;
    if (($sessionUser['role'] ?? '') === 'Employee'
        && !(new User($db))->isEmployeeAccountActive((int) ($sessionUser['user_id'] ?? 0))) {
        unset($_SESSION['user']);
        session_regenerate_id(true);
        $_SESSION['flash_error'] = 'Your employee portal account is disabled. Contact HR for access.';
        header('Location: index.php?action=login');
        exit;
    }

    $action = $_GET['action'] ?? 'attendance';
    switch ($action) {
        case 'login':
            (new AuthController($db))->login();
            break;
        case 'setup':
            (new AuthController($db))->setup();
            break;
        case 'logout':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                exit('Method not allowed.');
            }
            (new AuthController($db))->logout();
            break;
        case 'kiosk':
            require dirname(__DIR__) . '/app/Views/attendance/kiosk.php';
            break;
        case 'scan':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Security::verifyCsrf($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
                http_response_code(419);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['status' => 'error', 'message' => 'Invalid or expired scanner session. Reload the page.']);
                break;
            }
            (new AttendanceController($db))->scan();
            break;
        case 'employees':
            (new EmployeeController($db))->index();
            break;
        case 'employee-update':
            $employeeController = new EmployeeController($db);
            $employeeController->updateDetails();
            break;
        case 'qr-generator':
            (new QrController($db))->index();
            break;
        case 'employee-account':
            $employeeController = new EmployeeController($db);
            $employeeController->createAccount();
            break;
        case 'employee-account-status':
            $employeeController = new EmployeeController($db);
            $employeeController->setAccountStatus();
            break;
        case 'payroll':
            (new PayrollController($db))->index();
            break;
        case 'dtr':
            (new DtrController($db))->index();
            break;
        case 'attendance':
        case 'dashboard':
            (new DashboardController($db))->index();
            break;
        case 'attendance-list':
            Security::requireRole(['HR', 'Payroll', 'DepartmentHead']);
            (new AttendanceController($db))->index();
            break;
        default:
            http_response_code(404);
            echo 'Page not found.';
    }
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    http_response_code(503);
    require dirname(__DIR__) . '/app/Views/errors/database.php';
}