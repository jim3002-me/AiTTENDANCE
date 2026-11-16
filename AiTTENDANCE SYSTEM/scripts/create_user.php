<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require dirname(__DIR__) . '/app/Config/Database.php';
$username = trim($argv[1] ?? readline('Username: '));
$role = trim($argv[2] ?? readline('Role (HR, Payroll, DepartmentHead, Employee): '));
$employeeCode = $role === 'Employee'
    ? trim($argv[3] ?? readline('Linked employee code: '))
    : '';
$password = readline('Password: ');
if ($username === '' || !in_array($role, ['HR', 'Payroll', 'DepartmentHead', 'Employee'], true) || strlen($password) < 12) {
    fwrite(STDERR, "Use a valid username, role, and password with at least 12 characters.\n");
    exit(1);
}
$db = Database::connection();
$employeeId = null;
if ($role === 'Employee') {
    $lookup = $db->prepare('SELECT employee_id FROM employees WHERE employee_code = :code AND is_active = 1 LIMIT 1');
    $lookup->execute(['code' => $employeeCode]);
    $employeeId = $lookup->fetchColumn();
    if (!$employeeId) {
        fwrite(STDERR, "No active employee matches that employee code.\n");
        exit(1);
    }
}
$statement = $db->prepare('INSERT INTO users (username, password_hash, role, employee_id) VALUES (:username, :password_hash, :role, :employee_id)');
$statement->execute(['username' => $username, 'password_hash' => password_hash($password, PASSWORD_DEFAULT), 'role' => $role, 'employee_id' => $employeeId]);
fwrite(STDOUT, "User created.\n");