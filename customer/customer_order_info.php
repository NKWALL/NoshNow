<?php

require_once __DIR__ . '/../includes/auth.php';
require_login('Customer', '../login.php');
require_once __DIR__ . '/../db_connection.php';

$user_id = current_user_id();

$order_status_filter = (string) ($_GET['status'] ?? 'all');
$order_id_detail = filter_input(INPUT_GET, 'order_id', FILTER_VALIDATE_INT);
$order_info = null;
$showDeliveryMap = false;

$status_map = [
    'pending' => '待接單',
    'preparing' => '餐廳製作中',
    'assigned_to_deliveryman' => '已分配外送員',
    'ready_for_pickup' => '等待取餐',
    'picked_up' => '外送員已取餐',
    'delivering' => '配送中',
    'delivered' => '已送達',
    'completed' => '已完成',
    'refund_requested' => '申請退款',
    'cancelled' => '已取消'
];

if ($order_status_filter !== 'all' && !array_key_exists($order_status_filter, $status_map)) {
    $order_status_filter = 'all';
}

// 訂單清單
$sql_orders = "SELECT * FROM `order` WHERE user_id = ?";
$params = [$user_id];
if ($order_status_filter !== 'all') {
    $sql_orders .= " AND order_status = ?";
    $params[] = $order_status_filter;
}
$sql_orders .= " ORDER BY order_time DESC";
$stmt_orders = $conn->prepare($sql_orders);
$stmt_orders->execute($params);
$order_list = $stmt_orders->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>我的訂單 - NoshNow</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
                body {
            background-color: rgb(232, 228, 228);
            color: #f0d066;
            font-family: 'Segoe UI', sans-serif;
        }
        .table th, .table td {
            vertical-align: middle;
        }
        .btn-gold {
            background-color: rgb(241, 177, 75);
            color: black;
        }
        .btn-gold:hover {
            background-color: #ffe066;
            color: black;
        }
        .form-select {
            max-width: 200px;
        }
        .card {
            background-color: rgb(255, 255, 255);
            border: 3px solid #f0d066;
        }
        .card-header {
            background-color: rgb(0, 0, 0);
            color: rgb(241, 177, 75);
            border-bottom: 1px solid rgb(241, 177, 75);
        }
        .list-group-item {
            background-color: rgb(255, 255, 255);
            color: rgb(241, 177, 75);
            border: 2px solid #2a2a2a;
        }
        .alert-danger {
            background-color: #661212;
            color: #ffdede;
            border: 1px solid #ff4e4e;
        }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <h2 class="mb-2 text-center">
                    <div class="bg-dark p-3 shadow-sm rounded">
                        <a class="navbar-brand fw-bold text-white" href="#" style="text-shadow: 0 0 5px #ffd700;">
                            <i class="bi bi-lightning-charge-fill me-1"></i> Nosh Now 我的訂單
                        </a>
                    </div>
                </h2>

                <div class="card-body">
                    <a href="../index.php" class="btn btn-sm btn-gold mb-3">返回主頁</a>
                    <form method="get" class="mb-3">
                        <label for="status" class="form-label">篩選狀態：</label>
                        <select name="status" id="status" class="form-select" onchange="this.form.submit()">
                            <option value="all" <?= $order_status_filter === 'all' ? 'selected' : '' ?>>全部</option>
                            <?php foreach ($status_map as $code => $label): ?>
                                <option value="<?= $code ?>" <?= $order_status_filter === $code ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </form>

                    <table class="table table-bordered table-hover">
                        <thead class="table-secondary text-dark">
                        <tr>
                            <th>訂單編號</th>
                            <th>狀態</th>
                            <th>時間</th>
                            <th>總金額</th>
                            <th>詳情</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($order_list as $order): ?>
                            <tr>
                                <td><?= $order['order_id'] ?></td>
                                <td><?= $status_map[$order['order_status']] ?? $order['order_status'] ?></td>
                                <td><?= $order['order_time'] ?></td>
                                <td>$<?= $order['total_price'] ?></td>
                                <td>
                                    <a href="?order_id=<?= $order['order_id'] ?>&status=<?= $order_status_filter ?>" class="btn btn-sm btn-gold">詳情</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>

                    <?php if ($order_id_detail):
                        // 取訂單詳情，加入 delivers 資料
                        $sql_detail = "
                            SELECT o.*, u.username, c.customer_address, c.phone,
                                   d.deliver_status, d.deliver_time, d.deliver_location,
                                   d.current_lat, d.current_lng,
                                   d.pickup_lat, d.pickup_lng,
                                   d.dropoff_lat, d.dropoff_lng
                            FROM `order` o
                            JOIN users u ON o.user_id = u.user_id
                            JOIN customer c ON u.user_id = c.user_id
                            LEFT JOIN delivers d ON o.order_id = d.order_id
                            WHERE o.order_id = ? AND o.user_id = ?
                        ";
                        $stmt_detail = $conn->prepare($sql_detail);
                        $stmt_detail->execute([$order_id_detail, $user_id]);
                        $order_info = $stmt_detail->fetch(PDO::FETCH_ASSOC);

                        $showDeliveryMap = $order_info
                            && $order_info['current_lat'] !== null
                            && $order_info['current_lng'] !== null
                            && $order_info['pickup_lat'] !== null
                            && $order_info['pickup_lng'] !== null
                            && $order_info['dropoff_lat'] !== null
                            && $order_info['dropoff_lng'] !== null;

                        if ($order_info):
                            // 取訂單品項
                            $sql_items = "
                                SELECT m.foodName, m.price, oi.quantity, (m.price * oi.quantity) AS subtotal
                                FROM orderitem oi
                                JOIN contains c ON oi.order_id = c.order_id AND oi.sqNo = c.sqNo
                                JOIN menuitem m ON c.item_id = m.item_id
                                WHERE oi.order_id = ?
                            ";
                            $stmt_items = $conn->prepare($sql_items);
                            $stmt_items->execute([$order_id_detail]);
                            $order_items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

                            // 取評論（如果有）
                            $sql_review = "SELECT rating, comment FROM review WHERE order_id = ? AND user_id = ?";
                            $stmt_review = $conn->prepare($sql_review);
                            $stmt_review->execute([$order_id_detail, $user_id]);
                            $review = $stmt_review->fetch(PDO::FETCH_ASSOC);
                            ?>
                            <div class="mt-5">
                                <h3>訂單詳情（#<?= htmlspecialchars($order_info['order_id']) ?>）</h3>
                                <div class="mb-3">
                                    <strong>顧客：</strong><?= htmlspecialchars($order_info['username']) ?><br>
                                    <strong>電話：</strong><?= htmlspecialchars($order_info['phone']) ?><br>
                                    <strong>地址：</strong><?= htmlspecialchars($order_info['customer_address']) ?><br>
                                </div>
                                <div class="mb-3">
                                    <strong>狀態：</strong><?= $status_map[$order_info['order_status']] ?? $order_info['order_status'] ?><br>
                                    <strong>時間：</strong><?= htmlspecialchars($order_info['order_time']) ?><br>
                                    <strong>付款方式：</strong><?= htmlspecialchars($order_info['orderPayMethod']) ?><br>
                                    <strong>備註：</strong><?= htmlspecialchars($order_info['order_ps']) ?><br>
                                    <strong>總卡路里：</strong><?= htmlspecialchars($order_info['total_calories']) ?> kcal<br>
                                    <strong>總金額：</strong>$<?= htmlspecialchars($order_info['total_price']) ?><br>
                                </div>

                                <!-- 配送資訊 -->
                                <div class="mb-3">
                                    <h4>配送資訊</h4>
                                    <strong>配送時間：</strong><?= htmlspecialchars($order_info['deliver_time'] ?? '無') ?><br>
                                    <strong>配送位置描述：</strong><?= htmlspecialchars($order_info['deliver_location'] ?? '無') ?><br>
                                    <strong>配送資料中記錄的外送員位置：</strong>
                                    緯度: <?= htmlspecialchars($order_info['current_lat'] ?? '無') ?>,
                                    經度: <?= htmlspecialchars($order_info['current_lng'] ?? '無') ?><br>
                                    <strong>餐廳位置：</strong>
                                    緯度: <?= htmlspecialchars($order_info['pickup_lat'] ?? '無') ?>,
                                    經度: <?= htmlspecialchars($order_info['pickup_lng'] ?? '無') ?><br>
                                    <strong>顧客指定送餐位置：</strong>
                                    緯度: <?= htmlspecialchars($order_info['dropoff_lat'] ?? '無') ?>,
                                    經度: <?= htmlspecialchars($order_info['dropoff_lng'] ?? '無') ?><br>
                                    <?php if ($showDeliveryMap && google_maps_api_key() !== ''): ?>
                                        <div id="map" style="height: 300px; width: 100%;"></div>
                                    <?php elseif (!$showDeliveryMap): ?>
                                        <p class="text-muted mt-2">尚無完整的配送座標，路線地圖暫時無法顯示。</p>
                                    <?php else: ?>
                                        <p class="text-muted mt-2">需設定 Google Maps API Key 才能顯示路線地圖。</p>
                                    <?php endif; ?>

                                </div>


                                <h4 class="mt-4">訂購項目</h4>
                                <table class="table table-bordered table-hover">
                                    <thead class="table-secondary text-dark">
                                    <tr>
                                        <th>品項</th>
                                        <th>單價</th>
                                        <th>數量</th>
                                        <th>小計</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($order_items as $item): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($item['foodName']) ?></td>
                                            <td>$<?= htmlspecialchars($item['price']) ?></td>
                                            <td><?= htmlspecialchars($item['quantity']) ?></td>
                                            <td>$<?= htmlspecialchars($item['subtotal']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>

                                <?php if ($order_info['order_status'] === 'completed'): ?>
                                    <div class="mt-4">
                                        <h4>撰寫評論與退款申請</h4>
                                        <form action="submit_review.php" method="POST" class="mb-3">
                                            <input type="hidden" name="order_id" value="<?= htmlspecialchars($order_info['order_id']) ?>">
                                            <input type="hidden" name="user_id" value="<?= htmlspecialchars($user_id) ?>">

                                            <div class="mb-3">
                                                <label for="rating" class="form-label">評分（1-5 分）：</label>
                                                <select class="form-select" name="rating" id="rating" required
                                                    <?= $review ? 'disabled' : '' ?>>
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <option value="<?= $i ?>" <?= ($review && $review['rating'] == $i) ? 'selected' : '' ?>><?= $i ?> 分</option>
                                                    <?php endfor; ?>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label for="comment" class="form-label">評論內容：</label>
                                                <textarea class="form-control" name="comment" id="comment" rows="3" required <?= $review ? 'readonly' : '' ?>><?= $review ? htmlspecialchars($review['comment']) : '' ?></textarea>
                                            </div>

                                            <?php if (!$review): ?>
                                                <button type="submit" class="btn btn-gold">送出評論</button>
                                            <?php else: ?>
                                                <div class="alert alert-success">您已提交過評論，無法再次修改。</div>
                                            <?php endif; ?>
                                        </form>

                                        <a href="refund_application.php?order_id=<?= htmlspecialchars($order_info['order_id']) ?>" class="btn btn-danger">
                                            申請退款
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-danger mt-4">
                                找不到該訂單詳情。
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($showDeliveryMap && google_maps_api_key() !== ''): ?>
<script>
    function initMap() {
        const currentLocation = {
            lat: <?= (float) $order_info['current_lat'] ?>,
            lng: <?= (float) $order_info['current_lng'] ?>
        };
        const pickupLocation = {
            lat: <?= (float) $order_info['pickup_lat'] ?>,
            lng: <?= (float) $order_info['pickup_lng'] ?>
        };
        const dropoffLocation = {
            lat: <?= (float) $order_info['dropoff_lat'] ?>,
            lng: <?= (float) $order_info['dropoff_lng'] ?>
        };

        const map = new google.maps.Map(document.getElementById('map'), {
            center: currentLocation,
            zoom: 14
        });
        const directionsService = new google.maps.DirectionsService();
        const directionsRenderer = new google.maps.DirectionsRenderer({ map });

        new google.maps.Marker({
            position: currentLocation,
            map,
            title: '配送資料中記錄的外送員位置'
        });

        directionsService.route({
            origin: currentLocation,
            destination: dropoffLocation,
            waypoints: [{ location: pickupLocation, stopover: true }],
            travelMode: google.maps.TravelMode.DRIVING
        }, (response, status) => {
            if (status === 'OK') {
                directionsRenderer.setDirections(response);
            } else {
                console.warn('路線規劃失敗：' + status);
            }
        });
    }
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=<?= urlencode(google_maps_api_key()) ?>&callback=initMap" async defer></script>
<?php endif; ?>

</body>
</html>
