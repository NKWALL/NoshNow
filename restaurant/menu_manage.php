<?php
session_start();
require_once __DIR__ . '/../db_connection.php';


if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'RestaurantOwner') {
    header('Location: ../index.php');
    exit;
}
$ownerId = $_SESSION['user_id'];


$stmt = $pdo->prepare("
  SELECT r.restaurant_id, r.restaurantName
    FROM owns      o
    JOIN restaurant r ON r.restaurant_id = o.restaurant_id
   WHERE o.user_id = ?
   ORDER BY r.restaurant_id
");
$stmt->execute([$ownerId]);
$restaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (!$restaurants) {
    die('<div class="alert alert-warning">您尚未擁有任何餐廳，請先新增。</div>');
}


$ownedRestaurantIds = array_map('intval', array_column($restaurants, 'restaurant_id'));
$requestedRid = filter_input(INPUT_GET, 'rid', FILTER_VALIDATE_INT);
$rid = $requestedRid && in_array($requestedRid, $ownedRestaurantIds, true)
    ? $requestedRid
    : $ownedRestaurantIds[0];


if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
    ($_POST['action'] ?? '') === 'add') {

    $sql = "INSERT INTO MenuItem
              (restaurant_id, price, foodName, calories, dessert, drink, main_dish)
            VALUES (?,?,?,?,?,?,?)";
    $pdo->beginTransaction();
    $pdo->prepare($sql)->execute([
        $rid,
        $_POST['price'],
        trim((string) $_POST['foodName']),
        $_POST['calories'],
        trim((string) $_POST['dessert']) ?: null,
        trim((string) $_POST['drink']) ?: null,
        trim((string) $_POST['main_dish']) ?: null,
    ]);
    $itemId = (int) $pdo->lastInsertId();
    $pdo->prepare('INSERT INTO has (restaurant_id, item_id) VALUES (?, ?)')
        ->execute([$rid, $itemId]);
    $pdo->commit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $del_id = (int)$_POST['item_id'];
    $pdo->prepare("DELETE FROM MenuItem WHERE item_id = ? AND restaurant_id = ?")->execute([$del_id, $rid]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'edit') {
    $sql = "UPDATE MenuItem SET foodName=?, price=?, calories=?, main_dish=?, drink=?, dessert=?
            WHERE item_id=? AND restaurant_id=?";
    $pdo->prepare($sql)->execute([
        $_POST['foodName'],
        $_POST['price'],
        $_POST['calories'],
        $_POST['main_dish'],
        $_POST['drink'],
        $_POST['dessert'],
        $_POST['item_id'],
        $rid
    ]);
}


$stmtItems = $pdo->prepare("SELECT * FROM MenuItem WHERE restaurant_id = ?");
$stmtItems->execute([$rid]);
$items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="UTF-8">
  <title>菜單管理 | Nosh Now</title>
  <link href="../assets/css/navbar_custom.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link href="../assets/css/restaurant_manage.css" rel="stylesheet">

</head>
<body>
<?php include __DIR__ . '/../components/navbar.php'; ?>

<div class="container py-4">
<h2 class="menu-title mb-4">菜單管理</h2>
  <div class="row">

    <!-- 左：餐廳清單 -->
    <div class="col-md-3">
      <div class="list-group mb-3">
        <?php foreach ($restaurants as $r): ?>
          <a href="?rid=<?= $r['restaurant_id'] ?>"
             class="list-group-item list-group-item-action <?= $r['restaurant_id']==$rid?'active':'' ?>"
             title="餐廳 #<?= $r['restaurant_id'] ?>">
            <?= htmlspecialchars($r['restaurantName']) ?>
          </a>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- 右：菜單表格 + 新增 -->
    <div class="col-md-9">
      <h5><?= htmlspecialchars(
              array_column($restaurants, 'restaurantName', 'restaurant_id')[$rid]
            ) ?> — 菜單</h5>

      <!-- ★ 顯示六個欄位 -->
      <table class="table table-striped align-middle">
        <thead>
          <tr>
            <th>名稱</th>
            <th class="text-end">價格</th>
            <th>熱量</th>
            <th>主餐</th>
            <th>飲料</th>
            <th>甜點</th>
            <th>操作</th>
          </tr>
        </thead>
      <tbody>
<?php
$editing_id = $_POST['edit_item_id'] ?? null;
foreach ($items as $it):
    if ($editing_id == $it['item_id'] && ($_POST['action'] ?? '') === 'start_edit'):
?>
    <!-- 編輯模式行 -->
    <tr id="edit-row-<?= $it['item_id'] ?>">
        <form method="post">
            <td><input name="foodName" class="form-control" value="<?= htmlspecialchars($it['foodName']) ?>" required></td>
            <td><input name="price" class="form-control text-end" value="<?= $it['price'] ?>" required></td>
            <td><input name="calories" class="form-control" value="<?= $it['calories'] ?>"></td>
            <td><input name="main_dish" class="form-control" value="<?= htmlspecialchars($it['main_dish']) ?>"></td>
            <td><input name="drink" class="form-control" value="<?= htmlspecialchars($it['drink']) ?>"></td>
            <td><input name="dessert" class="form-control" value="<?= htmlspecialchars($it['dessert']) ?>"></td>
            <td>
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="item_id" value="<?= $it['item_id'] ?>">
                <button class="btn btn-sm btn-success me-1" type="submit">儲存</button>
                <a href="?rid=<?= $rid ?>" class="btn btn-sm btn-secondary">取消</a>
            </td>
        </form>
    </tr>




<?php
    else:
?>


    <!-- 一般顯示行 -->
    <tr>
        <td><?= htmlspecialchars($it['foodName']) ?></td>
        <td class="text-end">$<?= $it['price'] ?></td>
        <td><?= $it['calories'] ?></td>
        <td><?= htmlspecialchars($it['main_dish']) ?></td>
        <td><?= htmlspecialchars($it['drink']) ?></td>
        <td><?= htmlspecialchars($it['dessert']) ?></td>
        <td>
            <!-- 編輯按鈕 -->
            <form method="post" class="d-inline">
                <input type="hidden" name="action" value="start_edit">
                <input type="hidden" name="edit_item_id" value="<?= $it['item_id'] ?>">
                <button class="btn btn-sm btn-outline-primary me-1" type="submit">編輯</button>
            </form>
            <!-- 刪除按鈕 -->
            <form method="post" class="d-inline" onsubmit="return confirm('確定要刪除這個菜色嗎？')">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="item_id" value="<?= $it['item_id'] ?>">
                <button class="btn btn-sm btn-danger" type="submit">刪除</button>
            </form>
        </td>
    </tr>
<?php
    endif;
endforeach;
?>
</tbody>


      </table>

      <!-- 新增菜品 -->
      <form method="post" class="menu-form">
        <input type="hidden" name="action" value="add">
        <div class="row g-2 mb-2">
          <div class="col"><input name="foodName"  class="form-control" placeholder="名稱" required></div>
          <div class="col-2"><input name="price"     class="form-control" placeholder="價格" required></div>
          <div class="col-2"><input name="calories"  class="form-control" placeholder="熱量"></div>
        </div>
        <div class="row g-2 mb-2">
          <div class="col"><input name="main_dish" class="form-control" placeholder="主餐"></div>
          <div class="col"><input name="drink"     class="form-control" placeholder="飲料"></div>
          <div class="col"><input name="dessert"   class="form-control" placeholder="甜點"></div>
        </div>
        <button class="btn btn-sm btn-gold">新增菜品</button>
      </form>
    </div>
  </div>
</div>

<?php if ($editing_id): ?>
<script>
    // 讓頁面自動滾到正在編輯的那一列
    window.onload = function() {
        var row = document.getElementById('edit-row-<?= $editing_id ?>');
        if (row) {
            row.scrollIntoView({ behavior: "smooth", block: "center" });
        }
    }
</script>
<?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
