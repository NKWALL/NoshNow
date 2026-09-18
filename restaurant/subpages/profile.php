<?php
$rid = $restaurantId;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $latitude = filter_input(INPUT_POST, 'latitude', FILTER_VALIDATE_FLOAT);
    $longitude = filter_input(INPUT_POST, 'longitude', FILTER_VALIDATE_FLOAT);
    $sql = "UPDATE restaurant
            SET restaurantName    = ?,
                restaurantAddress = ?,
                operationTime     = ?,
                latitude          = ?,
                longitude         = ?
            WHERE restaurant_id   = ?";
    $pdo->prepare($sql)->execute([
        $_POST['restaurantName'],
        $_POST['restaurantAddress'],
        $_POST['operationTime'],
        $latitude === false ? null : $latitude,
        $longitude === false ? null : $longitude,
        $rid
    ]);
    echo "<div class='alert alert-success'>餐廳 #{$rid} 已更新！</div>";
}

$stmt = $pdo->prepare("SELECT * FROM restaurant WHERE restaurant_id = ?");
$stmt->execute([$rid]);
$rest = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$rest) {
    echo "<div class='alert alert-warning'>找不到餐廳 #{$rid} 的資料。</div>";
    return;
}
?>

<div class="row">
  <div class="col-md-3">
    <div class="list-group mb-3">
      <?php foreach ($myRestaurants as $r): ?>
        <a href="?module=profile&rid=<?= $r ?>"
           class="list-group-item list-group-item-action <?= $r == $rid ? 'active' : '' ?>">
          餐廳 #<?= $r ?>
        </a>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="col-md-9">
    <h5>編輯餐廳 #<?= $rid ?></h5>
    <form method="post">
      <div class="mb-3">
        <label class="form-label">店名</label>
        <input name="restaurantName" class="form-control"
               value="<?= htmlspecialchars($rest['restaurantName']) ?>">
      </div>
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">緯度（供外送導航使用）</label>
          <input type="number" step="any" name="latitude" class="form-control"
                 value="<?= htmlspecialchars($rest['latitude'] ?? '') ?>">
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">經度（供外送導航使用）</label>
          <input type="number" step="any" name="longitude" class="form-control"
                 value="<?= htmlspecialchars($rest['longitude'] ?? '') ?>">
        </div>
      </div>
      <div class="mb-3">
        <label class="form-label">地址</label>
        <input name="restaurantAddress" class="form-control"
               value="<?= htmlspecialchars($rest['restaurantAddress']) ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">營業時間</label>
        <input name="operationTime" class="form-control"
               placeholder="例：10:00-22:00"
               value="<?= htmlspecialchars($rest['operationTime']) ?>">
      </div>
      <button class="btn btn-save">儲存更改</button>
    </form>
  </div>
</div>
