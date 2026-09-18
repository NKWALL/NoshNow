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
$cartId = filter_var($_SESSION['cart_order_id'] ?? null, FILTER_VALIDATE_INT);
$paymentMethod = trim((string) ($_POST['payment_method'] ?? ''));
$orderNote = trim((string) ($_POST['order_ps'] ?? '')) ?: null;
$deliveryLat = filter_input(INPUT_POST, 'selected_lat', FILTER_VALIDATE_FLOAT);
$deliveryLng = filter_input(INPUT_POST, 'selected_lng', FILTER_VALIDATE_FLOAT);

if (!$cartId || $paymentMethod === '' || $deliveryLat === false || $deliveryLat === null
    || $deliveryLng === false || $deliveryLng === null) {
    http_response_code(422);
    exit('結帳資料不完整，請重新確認付款方式與配送位置。');
}

if ($deliveryLat < -90 || $deliveryLat > 90 || $deliveryLng < -180 || $deliveryLng > 180) {
    http_response_code(422);
    exit('配送座標格式不正確。');
}

try {
    $conn->beginTransaction();

    $cartStatement = $conn->prepare(
        'SELECT cart_id FROM cart WHERE cart_id = ? AND user_id = ? FOR UPDATE'
    );
    $cartStatement->execute([$cartId, $userId]);
    if (!$cartStatement->fetchColumn()) {
        throw new RuntimeException('找不到目前購物車。');
    }

    $itemStatement = $conn->prepare(
        'SELECT r.item_id, r.quantity, mi.price, mi.calories
         FROM records r
         JOIN menuitem mi ON mi.item_id = r.item_id
         WHERE r.cart_id = ?'
    );
    $itemStatement->execute([$cartId]);
    $items = $itemStatement->fetchAll();

    if (!$items) {
        throw new RuntimeException('購物車目前沒有餐點。');
    }

    $totalPrice = 0.0;
    $totalCalories = 0;
    foreach ($items as $item) {
        $quantity = max(1, (int) $item['quantity']);
        $totalPrice += (float) $item['price'] * $quantity;
        $totalCalories += (int) $item['calories'] * $quantity;
    }

    $orderStatement = $conn->prepare(
        "INSERT INTO `order`
            (cart_id, orderPayMethod, order_ps, weather, order_status, order_time,
             total_price, total_calories, orderCustomer, user_id, delivery_lat, delivery_lng)
         VALUES (?, ?, ?, NULL, 'pending', NOW(), ?, ?, ?, ?, ?, ?)"
    );
    $orderStatement->execute([
        $cartId,
        $paymentMethod,
        $orderNote,
        $totalPrice,
        $totalCalories,
        (string) $_SESSION['username'],
        $userId,
        $deliveryLat,
        $deliveryLng,
    ]);
    $orderId = (int) $conn->lastInsertId();

    $orderItemStatement = $conn->prepare(
        'INSERT INTO orderitem (order_id, sqNo, quantity) VALUES (?, ?, ?)'
    );
    $containsStatement = $conn->prepare(
        'INSERT INTO contains (order_id, sqNo, item_id) VALUES (?, ?, ?)'
    );

    foreach ($items as $index => $item) {
        $sequenceNumber = $index + 1;
        $orderItemStatement->execute([$orderId, $sequenceNumber, (int) $item['quantity']]);
        $containsStatement->execute([$orderId, $sequenceNumber, (int) $item['item_id']]);
    }

    // 已結帳的購物車保留作為訂單依據；另建一個空購物車供後續使用。
    $newCartStatement = $conn->prepare('INSERT INTO cart (user_id) VALUES (?)');
    $newCartStatement->execute([$userId]);
    $_SESSION['cart_order_id'] = (int) $conn->lastInsertId();

    $conn->commit();
    header('Location: customer_order_info.php?order_id=' . $orderId . '&created=1');
    exit;
} catch (Throwable $exception) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log('NoshNow checkout failed: ' . $exception->getMessage());
    http_response_code(422);
    exit($exception instanceof RuntimeException ? $exception->getMessage() : '訂單建立失敗，請稍後再試。');
}
