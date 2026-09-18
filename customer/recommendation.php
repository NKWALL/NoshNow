<?php

require_once __DIR__ . '/../includes/auth.php';
require_login('Customer', '../login.php');
require_once __DIR__ . '/../db_connection.php';
$user_id = current_user_id();


// 取得 username
$user_stmt = $pdo->prepare("SELECT username FROM users WHERE user_id = ?");
$user_stmt->execute([$user_id]);
$username = $user_stmt->fetchColumn();


// 推薦菜單查詢
$sql = "SELECT
    m.item_id, m.foodName, m.price, m.calories,
    m.dessert, m.drink, m.main_dish,
    rest.restaurant_id, rest.restaurantName
    FROM (
        SELECT item_id, score
        FROM recommendation
        WHERE user_id = ?
        ORDER BY score DESC
        LIMIT 5
    ) r
    JOIN menuitem m ON r.item_id = m.item_id
    JOIN restaurant rest ON m.restaurant_id = rest.restaurant_id
    GROUP BY m.item_id
    ORDER BY r.score DESC";



$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 熱門品項查詢（備用）
$hot_results = [];
if (count($results) === 0) {
    $hot_sql = "
        SELECT
            m.item_id, m.foodName, m.price, m.calories,
            m.main_dish, m.dessert, m.drink,
            r.restaurant_id, r.restaurantName
        FROM orderitem oi
        JOIN contains c ON c.order_id = oi.order_id AND c.sqNo = oi.sqNo
        JOIN menuitem m ON m.item_id = c.item_id
        JOIN restaurant r ON m.restaurant_id = r.restaurant_id
        GROUP BY m.item_id, m.foodName, m.price, m.calories,
                 m.main_dish, m.dessert, m.drink, r.restaurant_id, r.restaurantName
        ORDER BY SUM(oi.quantity) DESC
        LIMIT 5
    ";

    $hot_stmt = $pdo->query($hot_sql);
    $hot_results = $hot_stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>Nosh Now 推薦</title>
    <link href="../assets/css/site.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: rgb(232, 228, 228);
            color:rgb(0, 0, 0);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 2rem;
        }
        .container {
            max-width: 800px;
            margin: auto;
        }
        h2, p {
            color:rgb(0, 0, 0);
        }
        ul {
            list-style: none;
            padding-left: 0;
        }
        li {
            background-color:rgb(255, 255, 255);
            border: 1px solid #fcd34d;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            box-shadow: 0 0 10px rgba(252, 211, 77, 0.3);
        }
        hr {
            border: none;
            border-top: 1px solid #fcd34d;
            margin-top: 1rem;
        }
        strong {
            color: #fcd34d;
        }
        .navbar-title {
            text-shadow: 0 0 5px #fcd34d;

        }

        .glow-gold-card {
            border: 2px solid #fcd34d;
            box-shadow:
                0 0 8px #fcd34d,
                0 0 15px #fcd34d,
                0 0 20px #fcd34d;
            border-radius: 8px;
            background-color: #fff;
        }


    </style>
</head>
<body>
    <div class="container py-5">
        <div class="bg-dark p-3 shadow-sm rounded text-center">
            <h2 class="mb-0">
                <a class="navbar-brand fw-bold text-white fs-3" href="#" style="text-shadow: 0 0 5px #ffd700;">
                    <i class="bi bi-lightning-charge-fill me-1"></i> Nosh Now 美食週報
                </a>
            </h2>
        </div>
        <br>

        <div class="card mb-4 glow-gold-card">
        <div class="card-body ">
            <a href="../index.php" class="btn btn-warning fw-bold mb-3 glow-gold">
            <i class="bi bi-house-door-fill me-1"></i> 返回首頁
            </a>
            <h2 class="card-title mb-0 glow-gold">您好，會員 <?php echo htmlspecialchars($username); ?>，</h2>
        </div>
        </div>

        <?php if (count($results) > 0): ?>
            <p>以下是根據您的評分推薦的菜單：</p>
            <ul>
                <?php foreach ($results as $row): ?>

                    <li>
                        <strong>餐點名稱：</strong><?php echo $row['foodName']; ?><br>
                        <strong>價格：</strong>NT$ <?php echo $row['price']; ?><br>
                        <strong>熱量：</strong><?php echo $row['calories']; ?> 卡路里<br>
                        <?php if (!empty($row['main_dish'])): ?>
                            <strong>主菜：</strong><?php echo $row['main_dish']; ?><br>
                        <?php endif; ?>
                        <?php if (!empty($row['dessert'])): ?>
                            <strong>甜點：</strong><?php echo $row['dessert']; ?><br>
                        <?php endif; ?>
                        <?php if (!empty($row['drink'])): ?>
                            <strong>飲料：</strong><?php echo $row['drink']; ?><br>
                        <?php endif; ?>

                        <!-- 加入購物車 -->
                        <form action="add_to_cart.php" method="post" class="mt-2">
                            <input type="hidden" name="item_id" value="<?php echo $row['item_id']; ?>">
                            <input type="number" name="quantity" value="1" min="1" class="form-control d-inline-block w-auto">
                            <button type="submit" class="btn btn-sm ms-2" style="background-color: #FFC107; color: black; border-color: #FFC107;">
                            加入購物車
                            </button>
                        </form>

                        <!-- 查看餐廳 -->
                        <form action="restaurant_menu.php" method="get" class="mt-2">
                            <input type="hidden" name="restaurant_id" value="<?php echo $row['restaurant_id']; ?>">
                            <button type="submit" class="btn btn-sm mb-2" style="background-color: rgb(239, 191, 144); color: black; border-color: rgb(239, 191, 144);">
                                查看 <?php echo htmlspecialchars($row['restaurantName']); ?> 餐廳
                            </button>
                        </form>
                         <hr style="border: none; height: 1px; background-color: black;">
                    </li>

                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>您尚未評分過任何餐點，因此目前無法提供個人化推薦。</p>
            <p>以下是熱門推薦品項，歡迎參考：</p>
            <ul>
                <?php foreach ($hot_results as $hot): ?>
                    <li>
                        <strong>餐點名稱：</strong><?php echo $hot['foodName']; ?><br>
                        <strong>價格：</strong>NT$ <?php echo $hot['price']; ?><br>
                        <strong>熱量：</strong><?php echo $hot['calories']; ?> 卡路里<br>
                        <?php if (!empty($hot['main_dish'])): ?>
                            <strong>主菜：</strong><?php echo $hot['main_dish']; ?><br>
                        <?php endif; ?>
                        <?php if (!empty($hot['dessert'])): ?>
                            <strong>甜點：</strong><?php echo $hot['dessert']; ?><br>
                        <?php endif; ?>
                        <?php if (!empty($hot['drink'])): ?>
                            <strong>飲料：</strong><?php echo $hot['drink']; ?><br>
                        <?php endif; ?>

                        <!-- 加入購物車 -->
                        <form action="add_to_cart.php" method="post" class="mt-2">
                            <input type="hidden" name="item_id" value="<?php echo $hot['item_id']; ?>">
                            <input type="number" name="quantity" value="1" min="1" class="form-control d-inline-block w-auto">
                            <button type="submit" class="btn btn-sm ms-2" style="background-color: #FFC107; color: black; border-color: #FFC107;">
                            加入購物車
                            </button>
                        </form>

                        <!-- 查看餐廳 -->
                        <form action="restaurant_menu.php" method="get" class="mt-2">
                            <input type="hidden" name="restaurant_id" value="<?php echo $hot['restaurant_id']; ?>">
                            <button type="submit" class="btn btn-sm mb-2" style="background-color: rgb(239, 191, 144); color: black; border-color: rgb(239, 191, 144);">
                                查看 <?php echo htmlspecialchars($hot['restaurantName']); ?> 餐廳
                            </button>
                            <br>
                        </form>
                        <hr style="border: none; height: 1px; background-color: black;">
                    </li>

                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</body>
</html>
