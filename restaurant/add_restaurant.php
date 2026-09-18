<?php
session_start();
require_once __DIR__ . '/../db_connection.php';

// 權限檢查
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'RestaurantOwner') {
    header('Location: ../index.php');
    exit;
}
$ownerId = $_SESSION['user_id'];

// 表單處理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['restaurantName'] ?? '');
    $addr = trim($_POST['restaurantAddress'] ?? '');
    $op   = trim($_POST['operationTime'] ?? '');
    $latitude = filter_input(INPUT_POST, 'latitude', FILTER_VALIDATE_FLOAT);
    $longitude = filter_input(INPUT_POST, 'longitude', FILTER_VALIDATE_FLOAT);

    $errors = [];
    if ($name === '') {
        $errors[] = '餐廳名稱不可為空';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("
            INSERT INTO restaurant
                (restaurantName, restaurantAddress, operationTime, latitude, longitude)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$name, $addr, $op, $latitude ?: null, $longitude ?: null]);

        $newRid = $pdo->lastInsertId();

        $stmt2 = $pdo->prepare("
            INSERT INTO owns (user_id, restaurant_id)
            VALUES (?, ?)
        ");
        $stmt2->execute([$ownerId, $newRid]);

        header("Location: restaurant_manage.php?rid={$newRid}&module=profile");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="UTF-8">
  <title>新增餐廳 | NOSH NOW</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/css/add_restaurant.css" rel="stylesheet">
  <link href="../assets/css/navbar_custom.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
  <?php include __DIR__ . '/../components/navbar.php'; ?>

  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-7">


        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger">
            <ul class="mb-0">
              <?php foreach($errors as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <div class="add-card">
          <form method="post">
            <div class="mb-3">
              <label class="form-label">餐廳名稱</label>
              <input type="text" name="restaurantName" class="form-control"
                     value="<?= htmlspecialchars($_POST['restaurantName'] ?? '') ?>">
            </div>
            <div class="mb-3">
              <label class="form-label">餐廳地址</label>
              <input type="text" name="restaurantAddress" class="form-control"
                     value="<?= htmlspecialchars($_POST['restaurantAddress'] ?? '') ?>">
            </div>
            <div class="mb-3">
              <label class="form-label">營業時間</label>
              <input type="text" name="operationTime" class="form-control"
                     placeholder="例如：11:00–21:00"
                     value="<?= htmlspecialchars($_POST['operationTime'] ?? '') ?>">
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">緯度（供外送導航使用）</label>
                <input type="number" step="any" name="latitude" class="form-control"
                       value="<?= htmlspecialchars($_POST['latitude'] ?? '') ?>">
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">經度（供外送導航使用）</label>
                <input type="number" step="any" name="longitude" class="form-control"
                       value="<?= htmlspecialchars($_POST['longitude'] ?? '') ?>">
              </div>
            </div>
            <button type="submit" class="btn btn-gold">新增</button>
            <a href="restaurant_manage.php" class="btn btn-dark-outline ms-2">取消</a>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
