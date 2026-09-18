<?php

declare(strict_types=1);

session_start();
require_once __DIR__ . '/../db_connection.php';

$itemId = filter_input(INPUT_GET, 'menu_id', FILTER_VALIDATE_INT);
if (!$itemId) {
    http_response_code(400);
    exit('餐點編號不正確。');
}

$statement = $conn->prepare(
    'SELECT m.*, r.restaurantName, r.restaurant_id
     FROM menuitem m
     JOIN restaurant r ON r.restaurant_id = m.restaurant_id
     WHERE m.item_id = ?'
);
$statement->execute([$itemId]);
$item = $statement->fetch();

if (!$item) {
    http_response_code(404);
    exit('找不到此餐點。');
}

$statement = $conn->prepare(
    'SELECT ingredient FROM ingredients WHERE item_id = ? ORDER BY ingredient'
);
$statement->execute([$itemId]);
$ingredients = $statement->fetchAll(PDO::FETCH_COLUMN);

$statement = $conn->prepare(
    'SELECT c.category
     FROM menuitem_category mc
     JOIN category c ON c.category_id = mc.category_id
     WHERE mc.item_id = ?
     ORDER BY c.category'
);
$statement->execute([$itemId]);
$categories = $statement->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($item['foodName']) ?>｜NoshNow</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<main class="container py-5" style="max-width: 900px;">
    <a href="browse_restaurant.php" class="btn btn-outline-secondary mb-4">← 返回搜尋</a>
    <div class="card border-warning shadow-sm overflow-hidden">
        <img
            src="<?= !empty($item['food_image']) ? htmlspecialchars(str_starts_with($item['food_image'], 'assets/') ? '../' . $item['food_image'] : $item['food_image']) : '../assets/images/food-placeholder.svg' ?>"
            alt="<?= htmlspecialchars($item['foodName']) ?>"
            style="width: 100%; max-height: 360px; object-fit: cover;"
            onerror="this.onerror=null;this.src='../assets/images/food-placeholder.svg';"
        >
        <div class="card-body p-4">
            <h1 class="h3"><?= htmlspecialchars($item['foodName']) ?></h1>
            <p>
                <a href="restaurant_menu.php?restaurant_id=<?= (int) $item['restaurant_id'] ?>">
                    <?= htmlspecialchars($item['restaurantName']) ?>
                </a>
            </p>
            <dl class="row mb-4">
                <dt class="col-sm-3">價格</dt>
                <dd class="col-sm-9">$<?= number_format((float) $item['price'], 2) ?></dd>
                <dt class="col-sm-3">熱量</dt>
                <dd class="col-sm-9"><?= (int) $item['calories'] ?> kcal</dd>
                <dt class="col-sm-3">分類</dt>
                <dd class="col-sm-9"><?= htmlspecialchars(implode('、', $categories) ?: '未分類') ?></dd>
                <dt class="col-sm-3">食材</dt>
                <dd class="col-sm-9"><?= htmlspecialchars(implode('、', $ingredients) ?: '未提供') ?></dd>
            </dl>

            <?php if (($_SESSION['user_type'] ?? null) === 'Customer'): ?>
                <form method="post" action="add_to_cart.php" class="d-flex gap-2">
                    <input type="hidden" name="item_id" value="<?= (int) $item['item_id'] ?>">
                    <input type="number" name="quantity" value="1" min="1" class="form-control" style="max-width: 90px;">
                    <button type="submit" class="btn btn-warning">加入購物車</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</main>
</body>
</html>
