<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed.']);
    exit;
}

$username = trim((string) ($_POST['username'] ?? ''));
$email = filter_var(trim((string) ($_POST['email'] ?? '')), FILTER_VALIDATE_EMAIL);
$password = (string) ($_POST['password'] ?? '');
$confirmPassword = (string) ($_POST['confirm_password'] ?? '');
$accountType = (string) ($_POST['account_type'] ?? '');
$allowedRoles = ['Customer', 'DeliveryPerson', 'RestaurantOwner'];

if ($username === '' || !$email || strlen($password) < 8
    || $password !== $confirmPassword || !in_array($accountType, $allowedRoles, true)) {
    http_response_code(422);
    echo json_encode([
        'status' => 'error',
        'message' => '請填妥欄位，並確認兩次密碼相同且至少 8 個字元。',
    ]);
    exit;
}

$customerAddress = trim((string) ($_POST['customer_address'] ?? ''));
$phone = trim((string) ($_POST['phone'] ?? ''));
$vehicleType = trim((string) ($_POST['vehicle_type'] ?? ''));
$calorieGoalInput = trim((string) ($_POST['daily_calorie_goal'] ?? ''));
$calorieGoal = $calorieGoalInput === ''
    ? null
    : filter_var($calorieGoalInput, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

if ($accountType === 'Customer' && $calorieGoalInput !== '' && $calorieGoal === false) {
    http_response_code(422);
    echo json_encode(['status' => 'error', 'message' => '每日熱量目標必須為正整數。']);
    exit;
}

if ($accountType === 'Customer' && ($customerAddress === '' || $phone === '')) {
    http_response_code(422);
    echo json_encode(['status' => 'error', 'message' => '顧客地址與電話為必填欄位。']);
    exit;
}

if ($accountType === 'DeliveryPerson' && $vehicleType === '') {
    http_response_code(422);
    echo json_encode(['status' => 'error', 'message' => '請選擇外送交通工具。']);
    exit;
}

try {
    $conn->beginTransaction();

    $statement = $conn->prepare(
        'INSERT INTO users (username, password, user_type) VALUES (?, ?, ?)'
    );
    $statement->execute([
        $username,
        password_hash($password, PASSWORD_DEFAULT),
        $accountType,
    ]);
    $userId = (int) $conn->lastInsertId();

    $statement = $conn->prepare('INSERT INTO email (user_id, email) VALUES (?, ?)');
    $statement->execute([$userId, $email]);

    if ($accountType === 'Customer') {
        $statement = $conn->prepare(
            'INSERT INTO customer (user_id, customer_address, phone, daily_calorie_goal)
             VALUES (?, ?, ?, ?)'
        );
        $statement->execute([$userId, $customerAddress, $phone, $calorieGoal]);
    } elseif ($accountType === 'DeliveryPerson') {
        $statement = $conn->prepare(
            'INSERT INTO deliveryperson (user_id, vehicle_type) VALUES (?, ?)'
        );
        $statement->execute([$userId, $vehicleType]);
    } else {
        $statement = $conn->prepare('INSERT INTO restaurantowner (user_id) VALUES (?)');
        $statement->execute([$userId]);
    }

    $conn->commit();
    echo json_encode(['status' => 'success', 'message' => '註冊成功。']);
} catch (PDOException $exception) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log('NoshNow registration failed: ' . $exception->getMessage());
    http_response_code($exception->getCode() === '23000' ? 409 : 500);
    echo json_encode([
        'status' => 'error',
        'message' => $exception->getCode() === '23000'
            ? '帳號已被使用，或資料與既有紀錄衝突。'
            : '註冊失敗，請稍後再試。',
    ]);
}
