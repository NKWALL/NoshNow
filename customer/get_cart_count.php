<?php
session_start();
require_once __DIR__ . '/../db_connection.php';

header('Content-Type: application/json');

if (isset($_SESSION['user_id']) && ($_SESSION['user_type'] ?? null) === 'Customer') {
    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("
        SELECT SUM(records.quantity) AS total_quantity
        FROM records
        JOIN cart ON records.cart_id = cart.cart_id
        LEFT JOIN `order` o ON o.cart_id = cart.cart_id
        WHERE cart.user_id = ? AND o.order_id IS NULL
    ");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'count' => $result['total_quantity'] ?? 0
    ]);
} else {
    echo json_encode(['count' => 0]);
}
?>
