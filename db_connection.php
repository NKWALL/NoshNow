<?php

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';

$conn = database_connection();
$pdo = $conn;
