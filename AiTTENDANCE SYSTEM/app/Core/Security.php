<?php
declare(strict_types=1);

final class Security
{
    public static function csrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCsrf(?string $token): bool
    {
        return is_string($token) && isset($_SESSION['csrf_token'])
            && hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function requireRole(array $roles): void
    {
        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            header('Location: index.php?action=login');
            exit;
        }
        if (!in_array($user['role'], $roles, true)) {
            http_response_code(403);
            require dirname(__DIR__) . '/Views/errors/forbidden.php';
            exit;
        }
    }

    public static function escape(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}