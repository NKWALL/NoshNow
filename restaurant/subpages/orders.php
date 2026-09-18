<?php
$stmtR = $pdo->prepare("
    SELECT r.restaurant_id, r.restaurantName AS name
    FROM owns o
    JOIN restaurant r USING(restaurant_id)
    WHERE o.user_id = ?
");
$stmtR->execute([$ownerId]);
$myRestaurants = $stmtR->fetchAll(PDO::FETCH_ASSOC);

$rid = $restaurantId;

$statusOrder = [
    'pending', 'preparing', 'assigned_to_deliveryman', 'ready_for_pickup',
    'picked_up', 'delivering', 'delivered', 'completed', 'refund_requested', 'cancelled'
];
$statusMap = [
    'pending' => '待接單',
    'assigned_to_deliveryman' => '已分配外送員',
    'preparing' => '餐廳製作中',
    'ready_for_pickup' => '等待取餐',
    'picked_up' => '外送員已取餐',
    'delivering' => '配送中',
    'delivered' => '已送達',
    'completed' => '已完成',
    'refund_requested' => '申請退款',
    'cancelled' => '已取消',
];

echo '<form method="get" class="mb-4">';
echo '<label for="rid" class="form-label fw-bold">選擇餐廳：</label>';
echo '<select name="rid" id="rid" class="form-select" onchange="this.form.submit()" style="max-width: 300px;">';
foreach ($myRestaurants as $r) {
    $selected = $r['restaurant_id'] == $rid ? 'selected' : '';
    echo "<option value='{$r['restaurant_id']}' $selected>" . htmlspecialchars($r['name']) . "</option>";
}
echo '</select>';
echo '<input type="hidden" name="module" value="orders">';
echo '</form>';

if ($rid) {
    $restaurantName = '';
    foreach ($myRestaurants as $r) {
        if ($r['restaurant_id'] == $rid) {
            $restaurantName = $r['name'];
            break;
        }
    }

    echo "<div class='order-card'>";
    echo "<h4 class='section-title'>餐廳：" . htmlspecialchars($restaurantName) . "</h4>";

    $in = str_repeat('?,', count($statusOrder) - 1) . '?';
    $sql = "
    SELECT
      o.order_id,
      o.order_status,
      o.order_time,
      o.total_price,
      GROUP_CONCAT(m.foodName SEPARATOR ', ') AS items
    FROM `order` o
    JOIN orderitem oi ON oi.order_id = o.order_id
    JOIN contains c ON c.order_id = oi.order_id AND c.sqNo = oi.sqNo
    JOIN menuitem m ON m.item_id = c.item_id
    WHERE m.restaurant_id = ?
      AND o.order_status IN ($in)
    GROUP BY o.order_id, o.order_status, o.order_time, o.total_price
    ORDER BY FIELD(o.order_status, " . implode(',', array_map(fn($s)=>"'$s'", $statusOrder)) . "),
             o.order_time DESC
";

    $params = array_merge([$rid], $statusOrder);
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    echo '<table class="table table-sm table-order-dark w-100">';
    echo '<thead><tr>
            <th>ID</th><th>時間</th><th>狀態</th><th>餐點</th><th class="text-end">金額</th><th>操作</th>
          </tr></thead><tbody>';

    foreach ($stmt as $row) {
        $os = $row['order_status'];
        echo '<tr>';
        echo '<td>' . htmlspecialchars($row['order_id']) . '</td>';
        echo '<td>' . htmlspecialchars($row['order_time']) . '</td>';
        echo '<td>' . htmlspecialchars($statusMap[$os] ?? $os) . '</td>';
        echo '<td>' . htmlspecialchars($row['items']) . '</td>';
        echo '<td class="text-end">$' . htmlspecialchars($row['total_price']) . '</td>';
        echo '<td>';

        if ($os === 'pending') {
          echo '<form method="post" class="d-inline">
                  <input type="hidden" name="order_id" value="' . $row['order_id'] . '">
                  <input type="hidden" name="new_status" value="preparing">
                  <button class="btn btn-sm btn-accept">接單</button>
                </form>';
      } elseif ($os === 'preparing' || $os === 'assigned_to_deliveryman') {
          echo '<form method="post" class="d-inline">
                  <input type="hidden" name="order_id" value="' . $row['order_id'] . '">
                  <input type="hidden" name="new_status" value="ready_for_pickup">
                  <button class="btn btn-sm btn-complete">餐點完成</button>
                </form>';
      }


        echo '</td>';
        echo '</tr>';
    }

    echo '</tbody></table>';
    echo '</div>';
}
?>
