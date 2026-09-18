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
$itemId = filter_input(INPUT_POST, 'item_id', FILTER_VALIDATE_INT);
$quantity = max(1, (int) ($_POST['quantity'] ?? 1));

if (!$itemId) {
    http_response_code(422);
    exit('餐點資料不正確。');
}

$itemStatement = $pdo->prepare('SELECT item_id FROM menuitem WHERE item_id = ?');
$itemStatement->execute([$itemId]);
if (!$itemStatement->fetchColumn()) {
    http_response_code(404);
    exit('找不到此餐點。');
}

$cartStatement = $pdo->prepare(
    'SELECT c.cart_id
     FROM cart c
     LEFT JOIN `order` o ON o.cart_id = c.cart_id
     WHERE c.user_id = ? AND o.order_id IS NULL
     ORDER BY c.cart_id DESC
     LIMIT 1'
);
$cartStatement->execute([$userId]);
$cartId = $cartStatement->fetchColumn();

if (!$cartId) {
    $cartStatement = $pdo->prepare('INSERT INTO cart (user_id) VALUES (?)');
    $cartStatement->execute([$userId]);
    $cartId = (int) $pdo->lastInsertId();
}

$recordStatement = $pdo->prepare(
    'INSERT INTO records (cart_id, item_id, quantity)
     VALUES (?, ?, ?)
     ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)'
);
$recordStatement->execute([(int) $cartId, $itemId, $quantity]);
$_SESSION['cart_order_id'] = (int) $cartId;

header('Location: cart.php');
exit;
