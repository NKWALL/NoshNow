<?php

declare(strict_types=1);

function start_session_if_needed(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function require_login(?string $requiredRole = null, string $loginPath = 'login.php'): void
{
    start_session_if_needed();

    if (empty($_SESSION['user_id']) || empty($_SESSION['user_type'])) {
        header('Location: ' . $loginPath);
        exit;
    }

    if ($requiredRole !== null && $_SESSION['user_type'] !== $requiredRole) {
        http_response_code(403);
        exit('您沒有權限使用此功能。');
    }
}

function current_user_id(): int
{
    start_session_if_needed();
    return (int) ($_SESSION['user_id'] ?? 0);
}

function csrf_token(): string
{
    start_session_if_needed();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function require_valid_csrf(): void
{
    $submittedToken = (string) ($_POST['csrf_token'] ?? '');
    if ($submittedToken === '' || !hash_equals(csrf_token(), $submittedToken)) {
        http_response_code(403);
        exit('表單已過期，請重新載入頁面。');
    }
}
