<?php

declare(strict_types=1);

require_once __DIR__ . '/app.php';

function database_connection(): PDO
{
    static $connection = null;

    if ($connection instanceof PDO) {
        return $connection;
    }

    $db = app_config()['db'];
    $dsn = $db['socket'] !== ''
        ? sprintf('mysql:unix_socket=%s;dbname=%s;charset=utf8mb4', $db['socket'], $db['name'])
        : sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            $db['host'],
            $db['port'],
            $db['name']
        );

    try {
        $connection = new PDO($dsn, $db['user'], $db['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (PDOException $exception) {
        error_log('NoshNow database connection failed: ' . $exception->getMessage());
        http_response_code(500);
        exit('資料庫連線失敗，請確認本機設定。');
    }

    return $connection;
}
