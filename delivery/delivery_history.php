<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'DeliveryPerson') {
    header("Location: ../login.php");
    exit();
}

require_once __DIR__ . '/../db_connection.php';
$delivery_person_id = $_SESSION['user_id'];

$sql = "
    SELECT
        o.order_id, o.order_time, o.order_status,
        d.deliver_location, d.deliver_status,
        u.username AS customer_username,
        c.user_id AS customer_id,
        c.phone AS customer_phone,
        e.email AS customer_email,
        c.customer_address
    FROM Delivers d
    JOIN `Order` o ON d.order_id = o.order_id
    JOIN Customer c ON o.user_id = c.user_id
    JOIN Users u ON u.user_id = c.user_id
    LEFT JOIN Email e ON e.user_id = c.user_id
    WHERE d.user_id = :user_id
      AND (o.order_status = 'completed' OR o.order_status = 'refund_requested')
    ORDER BY o.order_time DESC
";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':user_id', $delivery_person_id);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$order_items = [];
foreach ($orders as $order) {
    $order_id = $order['order_id'];
    $sql_items = "
        SELECT
            mi.foodName,
            r.restaurantName AS restaurant_name
        FROM OrderItem oi
        JOIN contains c ON oi.order_id = c.order_id AND oi.sqNo = c.sqNo
        JOIN MenuItem mi ON c.item_id = mi.item_id
        LEFT JOIN Has h ON mi.item_id = h.item_id
        LEFT JOIN Restaurant r ON h.restaurant_id = r.restaurant_id
        WHERE oi.order_id = :order_id
    ";
    $stmt_items = $pdo->prepare($sql_items);
    $stmt_items->bindParam(':order_id', $order_id);
    $stmt_items->execute();
    $order_items[$order_id] = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>我的外送紀錄</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #e8e4e4;
            color: #f0d066;
            font-family: 'Segoe UI', sans-serif;
        }
        .card {
            background-color: #fff;
            border: 3px solid #f0d066;
        }
        .card-header {
            background-color: #000;
            color: #f1b14b;
            border-bottom: 1px solid #f1b14b;
        }
        .accordion-button {
            background-color: #000;
            color: #f0d066;
        }
        .accordion-button:not(.collapsed) {
            background-color: #1e1e1e;
            color: #ffe066;
        }
        .accordion-body {
            color: #000;
            background-color: #fffdf4;
            border-top: 1px solid #ffe066;
        }
        .btn-gold {
            background-color: #f1b14b;
            color: #1e1e1e;

        }
        .btn-gold:hover {
            background-color: #ffe066;
            color: #000;
        }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow">
            <h2 class="mb-4 text-center">
                <div class="bg-dark p-3 shadow-sm rounded">
                    <a class="navbar-brand fw-bold text-white" href="#" style="text-shadow: 0 0 5px #ffd700;">
                        <i class="bi bi-lightning-charge-fill me-1"></i> Nosh Now 我的外送紀錄
                    </a>
                </div>
            </h2>
                <div class="card-body">
                    <div class="text-center">
                        <a href="../index.php" class="btn btn-gold w-100">回主頁</a>
                    </div>
                    <?php if (count($orders) > 0): ?>
                        <div class="accordion mt-4" id="orderAccordion">
                            <?php foreach ($orders as $index => $order): ?>
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="heading<?= $index ?>">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $index ?>">
                                            訂單編號: <?= $order['order_id'] ?> | 狀態: <?= $order['order_status'] ?> | 時間: <?= $order['order_time'] ?>
                                        </button>
                                    </h2>
                                    <div id="collapse<?= $index ?>" class="accordion-collapse collapse" data-bs-parent="#orderAccordion">
                                        <div class="accordion-body">
                                            <p><strong>送達地點:</strong> <?= htmlspecialchars($order['deliver_location']) ?></p>
                                            <p><strong>顧客名稱:</strong> <?= htmlspecialchars($order['customer_username']) ?></p>
                                            <p><strong>顧客ID:</strong> <?= htmlspecialchars($order['customer_id']) ?></p>
                                            <p><strong>電話:</strong> <?= htmlspecialchars($order['customer_phone']) ?></p>
                                            <p><strong>Email:</strong> <?= $order['customer_email'] ? htmlspecialchars($order['customer_email']) : 'N/A' ?></p>
                                            <p><strong>地址:</strong> <?= htmlspecialchars($order['customer_address']) ?></p>

                                            <h6>餐點清單：</h6>
                                            <ul class="list-group">
                                                <?php foreach ($order_items[$order['order_id']] as $item): ?>
                                                    <li class="list-group-item">
                                                        <?= htmlspecialchars($item['foodName']) ?>
                                                        <small class="text-muted">
                                                            （來自 <?= $item['restaurant_name'] ?? '未知餐廳' ?>）
                                                        </small>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning mt-4">目前沒有外送紀錄。</div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
