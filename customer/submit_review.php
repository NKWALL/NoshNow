<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_login('Customer', '../login.php');
require_once __DIR__ . '/../db_connection.php';

$user_id = current_user_id();
$order_id = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
$rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_FLOAT);
$comment = trim((string) ($_POST['comment'] ?? ''));

if (!$order_id || !$rating || $rating < 1 || $rating > 5) {
    echo "資料不完整或評分不合法";
    exit;
}

// 確認該筆訂單是屬於此使用者且已完成
$stmt = $conn->prepare("SELECT order_id FROM `order` WHERE order_id = ? AND user_id = ? AND order_status = 'completed'");
$stmt->execute([$order_id, $user_id]);
$valid_order = $stmt->fetchColumn();

if (!$valid_order) {
    echo "無法對此訂單評價。";
    exit;
}

// 檢查是否已經評過 (同一使用者同一訂單只能評一次)
$stmt = $conn->prepare("SELECT COUNT(*) FROM review WHERE order_id = ? AND user_id = ?");
$stmt->execute([$order_id, $user_id]);
$already_reviewed = $stmt->fetchColumn();

if ($already_reviewed > 0) {
    echo "此訂單您已經提交過評論。";
    exit;
}

// 插入 review
try {
    $stmt = $conn->prepare("INSERT INTO review (user_id, order_id, rating, comment) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $order_id, $rating, $comment]);
} catch (PDOException $e) {
    error_log('NoshNow review failed: ' . $e->getMessage());
    echo "評論送出失敗，請稍後再試。";
    exit;
}

header("Location: customer_order_info.php?order_id=$order_id&status=completed&review_submitted=1");
exit;
?>
