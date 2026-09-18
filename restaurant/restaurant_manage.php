<?php
session_start();
require_once __DIR__ . '/../db_connection.php';


if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'RestaurantOwner') {
    header('Location: ../index.php');
    exit;
}
$ownerId = $_SESSION['user_id'];


$stmtR = $pdo->prepare("
  SELECT r.restaurant_id
    FROM owns      o
    JOIN restaurant r ON r.restaurant_id = o.restaurant_id
   WHERE o.user_id = ?
");
$stmtR->execute([$ownerId]);
$myRestaurants = $stmtR->fetchAll(PDO::FETCH_COLUMN);


$myRestaurants = array_filter($myRestaurants, fn($id) => $id > 0);


$rid = (isset($_GET['rid']) && in_array((int)$_GET['rid'], $myRestaurants))
        ? (int)$_GET['rid']
       : ($myRestaurants[0] ?? null);


if (!$rid) {
    $noRestaurantMessage = "<div class='alert alert-warning'>您尚未擁有任何餐廳，請先新增。</div>";
    $restaurantExists = false;
} else {
    $restaurantExists = true;
}


$restaurantId = $rid;


$module = $_GET['module'] ?? 'profile';
$allow  = ['profile', 'orders', 'reviews'];
if (!in_array($module, $allow)) $module = 'profile';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['new_status'])) {
    $orderId = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
    $newStatus = (string) $_POST['new_status'];
    $allowedTransitions = [
        'pending' => ['preparing'],
        'preparing' => ['ready_for_pickup'],
        'assigned_to_deliveryman' => ['ready_for_pickup'],
    ];

    if ($restaurantId && $orderId) {
        $statement = $pdo->prepare(
            'SELECT o.order_status
             FROM `order` o
             WHERE o.order_id = ?
               AND EXISTS (
                   SELECT 1 FROM contains c
                   JOIN menuitem m ON m.item_id = c.item_id
                   JOIN owns ow ON ow.restaurant_id = m.restaurant_id
                   WHERE c.order_id = o.order_id
                     AND m.restaurant_id = ?
                     AND ow.user_id = ?
               )'
        );
        $statement->execute([$orderId, $restaurantId, $ownerId]);
        $currentStatus = $statement->fetchColumn();

        if ($currentStatus && in_array($newStatus, $allowedTransitions[$currentStatus] ?? [], true)) {
            $statement = $pdo->prepare(
                'UPDATE `order` SET order_status = ? WHERE order_id = ? AND order_status = ?'
            );
            $statement->execute([$newStatus, $orderId, $currentStatus]);
        }
    }

    header('Location: restaurant_manage.php?module=orders&rid=' . (int) $restaurantId);
    exit;
}
?>

<?php
$deleteError = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_restaurant_id'])) {
    $deleteRid = filter_input(INPUT_POST, 'delete_restaurant_id', FILTER_VALIDATE_INT);
    if ($deleteRid && in_array($deleteRid, array_map('intval', $myRestaurants), true)) {
        $statement = $pdo->prepare(
            'SELECT COUNT(*)
             FROM menuitem m
             JOIN contains c ON c.item_id = m.item_id
             WHERE m.restaurant_id = ?'
        );
        $statement->execute([$deleteRid]);

        if ((int) $statement->fetchColumn() > 0) {
            $deleteError = '這間餐廳已有訂單紀錄，為保留歷史資料不可刪除。';
        } else {
            $statement = $pdo->prepare('DELETE FROM restaurant WHERE restaurant_id = ?');
            $statement->execute([$deleteRid]);
            header('Location: restaurant_manage.php');
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="UTF-8">
  <title>餐廳管理 | NOSH NOW</title>
  <link href="../assets/css/navbar_custom.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link href="../assets/css/restaurant_manage.css" rel="stylesheet">

</head>
<body>
  <?php include __DIR__ . '/../components/navbar.php'; ?>

  <div class="container py-4">
    <?php if ($deleteError): ?>
      <div class="alert alert-warning"><?= htmlspecialchars($deleteError) ?></div>
    <?php endif; ?>
    <div class="row justify-content-center">
      <div class="col-lg-10">

      <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="restaurant-title neon-title mb-0">餐廳管理</h2>
          <div>
            <?php if ($restaurantExists): ?>
          <form method="post" action="" class="d-inline" onsubmit="return confirm('確定要刪除這家餐廳嗎？這會一併刪除相關資料！');">
            <input type="hidden" name="delete_restaurant_id" value="<?= $rid ?>">
            <button type="submit" class="btn btn-danger">刪除餐廳</button>
          </form>
        <?php endif; ?>
        <a href="add_restaurant.php" class="btn btn-gold ms-2">新增餐廳</a>
        </div>
      </div>

        <?php if (!$restaurantExists): ?>
        <?= $noRestaurantMessage; ?>
        <?php endif; ?>

        <?php if ($restaurantExists): ?>
          <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
              <a class="nav-link <?= $module=='profile'?'active':'' ?>" href="?module=profile&rid=<?= $rid ?>">基本資料</a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?= $module=='orders'?'active':'' ?>" href="?module=orders&rid=<?= $rid ?>">訂單管理</a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?= $module=='reviews'?'active':'' ?>" href="?module=reviews&rid=<?= $rid ?>">顧客評價</a>
            </li>
          </ul>

          <div class="restaurant-panel">
            <?php include __DIR__ . "/subpages/{$module}.php"; ?>
          </div>
        <?php endif; ?>

      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
