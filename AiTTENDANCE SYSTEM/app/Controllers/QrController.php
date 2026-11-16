<?php
declare(strict_types=1);

final class QrController
{
    public function __construct(private PDO $db)
    {
    }

    public function index(): void
    {
        Security::requireRole(['HR']);
        $employees = (new Employee($this->db))->activeForQr();
        require dirname(__DIR__) . '/Views/qr/index.php';
    }
}