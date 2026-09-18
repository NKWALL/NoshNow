<?php

require_once __DIR__ . '/../includes/auth.php';
require_login('DeliveryPerson', '../login.php');
require_once __DIR__ . '/../db_connection.php';

$delivery_person_id = current_user_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
    if (!$order_id) {
        http_response_code(422);
        exit('訂單編號不正確。');
    }

    try {
        $pdo->beginTransaction();

        if (isset($_POST['update_status'])) {
            $requested_status = (string) $_POST['update_status'];
            $statusTransitions = [
                'ready_for_pickup' => 'delivering',
                'delivering' => 'completed',
            ];

            $statement = $pdo->prepare(
                'SELECT o.order_status
                 FROM `order` o
                 JOIN delivers d ON d.order_id = o.order_id
                 WHERE o.order_id = ? AND d.user_id = ?
                 FOR UPDATE'
            );
            $statement->execute([$order_id, $delivery_person_id]);
            $current_status = $statement->fetchColumn();

            if (!$current_status || ($statusTransitions[$current_status] ?? null) !== $requested_status) {
                throw new RuntimeException('訂單目前無法更新為指定狀態。');
            }

            $statement = $pdo->prepare(
                'UPDATE delivers
                 SET deliver_status = ?, deliver_time = IF(? = \'completed\', NOW(), deliver_time)
                 WHERE order_id = ? AND user_id = ?'
            );
            $statement->execute([
                $requested_status,
                $requested_status,
                $order_id,
                $delivery_person_id,
            ]);

            $statement = $pdo->prepare(
                'UPDATE `order` SET order_status = ? WHERE order_id = ?'
            );
            $statement->execute([$requested_status, $order_id]);
        } else {
            $statement = $pdo->prepare(
                "SELECT order_status, delivery_lat, delivery_lng
                 FROM `order`
                 WHERE order_id = ?
                   AND order_status IN ('preparing', 'ready_for_pickup')
                   AND NOT EXISTS (SELECT 1 FROM delivers WHERE delivers.order_id = `order`.order_id)
                 FOR UPDATE"
            );
            $statement->execute([$order_id]);
            $order = $statement->fetch();
            if (!$order) {
                throw new RuntimeException('此訂單已被承接或目前不可承接。');
            }

            $statement = $pdo->prepare(
                'SELECT r.latitude AS pickup_lat, r.longitude AS pickup_lng,
                        r.restaurantAddress
                 FROM contains c
                 JOIN menuitem m ON m.item_id = c.item_id
                 JOIN restaurant r ON r.restaurant_id = m.restaurant_id
                 WHERE c.order_id = ?
                 ORDER BY c.sqNo
                 LIMIT 1'
            );
            $statement->execute([$order_id]);
            $restaurant = $statement->fetch();
            if (!$restaurant || $restaurant['pickup_lat'] === null || $restaurant['pickup_lng'] === null) {
                throw new RuntimeException('餐廳尚未設定可供導航的座標。');
            }

            $newStatus = $order['order_status'] === 'preparing'
                ? 'assigned_to_deliveryman'
                : 'ready_for_pickup';

            $statement = $pdo->prepare(
                'INSERT INTO delivers
                    (order_id, user_id, deliver_status, deliver_time, deliver_location,
                     current_lat, current_lng, pickup_lat, pickup_lng, dropoff_lat, dropoff_lng)
                 VALUES (?, ?, ?, NULL, ?, ?, ?, ?, ?, ?, ?)'
            );
            $statement->execute([
                $order_id,
                $delivery_person_id,
                $newStatus,
                $restaurant['restaurantAddress'],
                $restaurant['pickup_lat'],
                $restaurant['pickup_lng'],
                $restaurant['pickup_lat'],
                $restaurant['pickup_lng'],
                $order['delivery_lat'],
                $order['delivery_lng'],
            ]);

            $statement = $pdo->prepare(
                'UPDATE `order` SET order_status = ? WHERE order_id = ?'
            );
            $statement->execute([$newStatus, $order_id]);
        }

        $pdo->commit();
        header('Location: delivery_available.php');
        exit;
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('NoshNow delivery update failed: ' . $exception->getMessage());
        http_response_code(409);
        exit($exception instanceof RuntimeException
            ? $exception->getMessage()
            : '配送狀態更新失敗，請重新整理後再試。');
    }
}

$statement = $pdo->query(
    "SELECT o.*
     FROM `order` o
     WHERE o.order_status IN ('preparing', 'ready_for_pickup')
       AND NOT EXISTS (SELECT 1 FROM delivers d WHERE d.order_id = o.order_id)
     ORDER BY o.order_time"
);
$available_orders = $statement->fetchAll();

$statement = $pdo->prepare(
    "SELECT o.*
     FROM `order` o
     JOIN delivers d ON d.order_id = o.order_id
     WHERE d.user_id = ?
       AND o.order_status IN ('assigned_to_deliveryman', 'ready_for_pickup', 'delivering')
     ORDER BY o.order_time"
);
$statement->execute([$delivery_person_id]);
$my_orders = $statement->fetchAll();
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>可接外送與目前配送中</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color:rgb(232, 228, 228);
            color: #f0d066;
            font-family: 'Segoe UI', sans-serif;
        }
        .card {
            background-color:rgb(255, 255, 255);
            border: 3px solid #f0d066;
        }
        .card-header {
            background-color:rgb(0, 0, 0);
            color:rgb(241, 177, 75);
            border-bottom: 1px solid rgb(241, 177, 75);
        }
        .btn-gold {
            background-color:rgb(241, 177, 75);
            color: #1e1e1e;
        }
        .btn-gold:hover {
            background-color: #ffe066;
            color: #000;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4 text-center">
            <div class="bg-dark p-3 shadow-sm rounded">
                <a class="navbar-brand fw-bold text-white" href="#" style="text-shadow: 0 0 5px #ffd700;">
                    <i class="bi bi-lightning-charge-fill me-1"></i> Nosh Now 我的外送紀錄
                </a>
            </div>
        </h2>
        <div class="text-center mt-4">
            <a href="../index.php" class="btn btn-gold w-100">回主頁</a>
        </div>

        <!-- 我的配送中訂單 -->
        <div class="card mt-5">
            <div class="card-body">
                <h5 class="card-title">我目前配送中的訂單</h5>
                <?php if (count($my_orders) > 0): ?>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>訂單編號</th>
                                <th>時間</th>
                                <th>使用者ID</th>
                                <th>總價</th>
                                <th>狀態</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($my_orders as $order): ?>
                                <tr>
                                    <td><?= htmlspecialchars($order['order_id']) ?></td>
                                    <td><?= htmlspecialchars($order['order_time']) ?></td>
                                    <td><?= htmlspecialchars($order['user_id']) ?></td>
                                    <td><?= htmlspecialchars($order['total_price']) ?></td>
                                    <td><?= htmlspecialchars($order['order_status']) ?></td>
                                    <td>
                                        <?php if ($order['order_status'] === 'ready_for_pickup'): ?>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                                <input type="hidden" name="update_status" value="delivering">
                                                <button type="submit" class="btn btn-sm btn-gold">已取餐</button>
                                            </form>
                                        <?php elseif ($order['order_status'] === 'delivering'): ?>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                                <input type="hidden" name="update_status" value="completed">
                                                <button type="submit" class="btn btn-sm btn-success">已送達</button>
                                            </form>
                                        <?php elseif ($order['order_status'] === 'assigned_to_deliveryman'): ?>
                                            <span>餐點製作中</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>目前沒有配送中的訂單。</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- 可接外送的訂單 -->
        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title">可接外送的訂單</h5>
                <?php if (count($available_orders) > 0): ?>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>訂單編號</th>
                                <th>時間</th>
                                <th>天氣</th>
                                <th>總價</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($available_orders as $order): ?>
                                <tr>
                                    <td><?= htmlspecialchars($order['order_id']) ?></td>
                                    <td><?= htmlspecialchars($order['order_time']) ?></td>
                                    <td><?= htmlspecialchars($order['weather']) ?></td>
                                    <td><?= htmlspecialchars($order['total_price']) ?></td>
                                    <td>
                                        <form method="POST">
                                            <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-gold">接單</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>目前沒有可接的訂單。</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
