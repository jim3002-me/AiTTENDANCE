<?php
declare(strict_types=1);

final class AuthController
{
    public function __construct(private PDO $db)
    {
    }

    public function login(): void
    {
        $error = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_error']);
        if (!(new User($this->db))->hasAccounts()) {
            require dirname(__DIR__) . '/Views/auth/setup.php';
            return;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::verifyCsrf($_POST['csrf_token'] ?? null)) {
                http_response_code(419);
                $error = 'Your session expired. Reload the page and try again.';
            } else {
                $user = (new User($this->db))->authenticate(trim($_POST['username'] ?? ''), $_POST['password'] ?? '');
                if ($user) {
                    session_regenerate_id(true);
                    $_SESSION['user'] = $user;
                    header('Location: index.php?action=dashboard');
                    exit;
                }
                $error = 'The username or password was not recognized.';
            }
        }
        require dirname(__DIR__) . '/Views/auth/login.php';
    }

    public function setup(): void
    {
        $error = null;
        $users = new User($this->db);
        if ($users->hasAccounts()) {
            header('Location: index.php?action=login');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirmation = $_POST['password_confirmation'] ?? '';
            if (!Security::verifyCsrf($_POST['csrf_token'] ?? null)) {
                http_response_code(419);
                $error = 'Your session expired. Reload the page and try again.';
            } elseif (strlen($username) < 3 || strlen($username) > 80) {
                $error = 'Username must be between 3 and 80 characters.';
            } elseif (strlen($password) < 12) {
                $error = 'Use a password with at least 12 characters.';
            } elseif ($password !== $confirmation) {
                $error = 'The passwords do not match.';
            } else {
                try {
                    if ($users->createFirstHrAccount($username, $password)) {
                        $account = $users->authenticate($username, $password);
                        session_regenerate_id(true);
                        $_SESSION['user'] = $account;
                        header('Location: index.php?action=dashboard');
                        exit;
                    }
                    header('Location: index.php?action=login');
                    return;
                } catch (PDOException $exception) {
                    error_log($exception->getMessage());
                    $error = 'The account could not be created. Try a different username.';
                }
            }
        }

        require dirname(__DIR__) . '/Views/auth/setup.php';
    }

    public function logout(): void
    {
        if (!Security::verifyCsrf($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            exit('Invalid session token.');
        }
        $_SESSION = [];
        session_destroy();
        header('Location: index.php?action=login');
    }
}