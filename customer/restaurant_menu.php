<?php
require_once __DIR__ . '/../db_connection.php';
session_start();

$restaurant_id = filter_input(INPUT_GET, 'restaurant_id', FILTER_VALIDATE_INT);

if (!$restaurant_id) {
    header("Location: ../index.php");
    exit;
}

// 抓餐廳資訊
$stmt_restaurant = $conn->prepare("SELECT * FROM restaurant WHERE restaurant_id = ?");
$stmt_restaurant->execute([$restaurant_id]);
$restaurant = $stmt_restaurant->fetch(PDO::FETCH_ASSOC);

if (!$restaurant) {
    http_response_code(404);
    exit('找不到此餐廳。');
}

// 抓 menuitem
$stmt_items = $conn->prepare("
    SELECT item_id, restaurant_id, price, foodName, calories, dessert, drink, main_dish, food_image
    FROM menuitem
    WHERE restaurant_id = ?
");
$stmt_items->execute([$restaurant_id]);
$items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

?>



<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($restaurant['restaurantName']) ?> 的所有餐點</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <!-- 返回按鈕 -->
    <a href="browse_restaurant.php" class="btn btn-outline-secondary mb-4">← 返回瀏覽餐廳</a>
</div>

<div class="container mt-5">
    <h2 class="mb-4"><?= htmlspecialchars($restaurant['restaurantName']) ?> 的所有餐點</h2>

    <div class="row">
        <?php foreach ($items as $row): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm rounded-4 border-0">
                    <!-- 使用動態圖片或者預設圖片 -->
                    <img src="<?= !empty($row['food_image']) ? htmlspecialchars(str_starts_with($row['food_image'], 'assets/') ? '../' . $row['food_image'] : $row['food_image']) : '../assets/images/food-placeholder.svg' ?>"
                         class="card-img-top rounded-top" alt="餐點圖片">

                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($row['foodName']) ?></h5>
                        <p class="card-text">
                            價錢：$<?= htmlspecialchars($row['price']) ?><br>
                            熱量：<?= htmlspecialchars($row['calories']) ?> kcal<br>
                            甜點：<?= htmlspecialchars($row['dessert']) ?><br>
                            飲料：<?= htmlspecialchars($row['drink']) ?><br>
                            主餐：<?= htmlspecialchars($row['main_dish']) ?>
                        </p>

                        <!-- 加入購物車表單 -->
                        <?php if (($_SESSION['user_type'] ?? null) === 'Customer'): ?>
                            <form method="post" action="add_to_cart.php" class="d-flex gap-2">
                                <input type="hidden" name="item_id" value="<?= htmlspecialchars($row['item_id']) ?>">
                                <input type="number" name="quantity" value="1" min="1" class="form-control form-control-sm" style="width: 60px;">
                                <button type="submit" class="btn btn-sm btn-warning">加入</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>



</body>
</html>
