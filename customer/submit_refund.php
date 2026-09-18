<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_login('Customer', '../login.php');
require_once __DIR__ . '/../db_connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('不允許的請求方式。');
}

$userId = current_user_id();
$orderId = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
$reason = trim((string) ($_POST['reason'] ?? ''));

if (!$orderId || $reason === '') {
    http_response_code(422);
    exit('請填寫退款原因。');
}

try {
    $conn->beginTransaction();

    $statement = $conn->prepare(
        "SELECT total_price
         FROM `order`
         WHERE order_id = ? AND user_id = ? AND order_status = 'completed'
         FOR UPDATE"
    );
    $statement->execute([$orderId, $userId]);
    $order = $statement->fetch();
    if (!$order) {
        throw new RuntimeException('只有本人已完成的訂單可以申請退款。');
    }

    $statement = $conn->prepare(
        "INSERT INTO refundapplication
            (refund_price, refundStatus, reason, user_id, order_id)
         VALUES (?, 'pending', ?, ?, ?)"
    );
    $statement->execute([$order['total_price'], $reason, $userId, $orderId]);
    $refundId = (int) $conn->lastInsertId();

    $statement = $conn->prepare(
        'INSERT INTO requires (user_id, refund_id, apply_time) VALUES (?, ?, NOW())'
    );
    $statement->execute([$userId, $refundId]);

    $statement = $conn->prepare(
        "UPDATE `order` SET order_status = 'refund_requested' WHERE order_id = ? AND user_id = ?"
    );
    $statement->execute([$orderId, $userId]);

    $conn->commit();
    header('Location: refund_application.php?order_id=' . $orderId);
    exit;
} catch (PDOException $exception) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log('NoshNow refund failed: ' . $exception->getMessage());
    http_response_code($exception->getCode() === '23000' ? 409 : 500);
    exit($exception->getCode() === '23000'
        ? '此訂單已提交過退款申請。'
        : '退款申請失敗，請稍後再試。');
} catch (RuntimeException $exception) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    http_response_code(422);
    exit($exception->getMessage());
}
