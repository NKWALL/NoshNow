<?php
session_start();
require_once __DIR__ . '/../db_connection.php';

// 確保使用者已經登入，且 user_id 存在於 session 中
if (isset($_SESSION['user_id']) && ($_SESSION['user_type'] ?? null) === 'Customer') {
    $user_id = $_SESSION['user_id'];

    try {
        // 查詢該使用者購物車中的商品
        $stmt = $pdo->prepare("
            SELECT menuitem.foodName, records.quantity
            FROM records
            JOIN menuitem ON records.item_id = menuitem.item_id
            JOIN cart ON cart.cart_id = records.cart_id
            LEFT JOIN `order` o ON o.cart_id = cart.cart_id
            WHERE cart.user_id = ? AND o.order_id IS NULL
        ");
        $stmt->execute([$user_id]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($items) {
            foreach ($items as $item) {
                echo '<li class="px-3 py-2">' . htmlspecialchars($item['foodName']) . ' x' . $item['quantity'] . '</li>';
            }
        } else {
            echo '<li class="px-3 py-2 text-center">Your Cart Is Empty</li>';
        }
    } catch (PDOException $e) {
        // 資料庫錯誤處理
        error_log('NoshNow cart lookup failed: ' . $e->getMessage());
        echo '<li class="px-3 py-2 text-center">購物車暫時無法載入</li>';
    }
} else {
    echo '<li class="px-3 py-2 text-center">Please log in</li>';
}
?>
