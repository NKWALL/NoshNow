<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    $user_id = null;
} else {
    $user_id = (int)$_SESSION['user_id'];
}




?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>瀏覽餐廳 - Nosh Now</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color:rgb(232, 228, 228);
            color: #ffd700;
        }
        .navbar, .footer {
            background-color:rgb(33, 37, 41);
            color: #ffd700;
        }

        .card {
            background-color: #ffffff;
            color: #212121;
            border: 1px solid #ddd;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            height: 450px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .card-body {
            flex-grow: 1;
        }

        .card-title {
            color: #212121;
            font-size: 1.1rem;
            height: 50px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: normal;
            display: block;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }


        .card-img-top {
            height: 180px;
            object-fit: cover;
            width: 100%;
        }


        .btn-primary {
            background-color: #ffd700;
            border-color: #ffd700;
        }
        .btn-primary:hover {
            background-color: #e6c200;
            border-color: #e6c200;
        }
        .navbar-nav .nav-link {
            color: #ffd700;
        }
        .navbar-nav .nav-link:hover {
            color: #e6c200;
        }
        .dropdown-menu {
            background-color:rgb(0, 0, 0);
            border: 1px solid #ffd700;
            color:rgb(255, 255, 255);
        }
        .dropdown-item {
            color:rgb(255, 255, 255);
        }
        .dropdown-item:hover {
            background-color: #ffd700;
            color:rgb(0, 0, 0);
        }

        .dropdown-menu:hover {
            background-color:rgb(0, 0, 0);
        }

        .navbar {
        background-color:rgb(33, 37, 41) !important;
        backdrop-filter: blur(100000000px);
        border-bottom: 1px solid #ffd700;
        box-shadow: 0 4px 10px rgba(255, 215, 0, 0.2);
        }

        .navbar-nav .nav-link {
        color: #ffd700 !important;
        transition: color 0.3s ease, border-bottom 0.3s ease;
        }

        .navbar-nav .nav-link:hover {
            color: #ffffff !important;
            border-bottom: 2px solid #ffd700;
        }

        .nav-link.dropdown-toggle {
        transition: background-color 0.3s ease;
        border-radius: 0.5rem;
        }

        .nav-link.dropdown-toggle:hover {
        background-color: #333333;
        }

        .badge.bg-danger {
            font-weight: bold;
            background-color: #e74c3c;
            color: #ffffff;
            padding: 0.3rem 0.6rem;
            border-radius: 10px;
            font-size: 0.9rem;
        }

        .navbar {
        box-shadow: 0 2px 10px rgba(255, 215, 0, 0.3);
        border-bottom: 1px solid rgba(255, 215, 0, 0.2);
        }


        .navbar {
            background-color:rgb(33, 37, 41)!important;
            backdrop-filter: blur(10px);
            border-bottom: 1px solid #ffd700;
            box-shadow: 0 4px 10px rgba(255, 215, 0, 0.2);
        }

        @keyframes pulse-glow {
        0% {
            box-shadow: 0 0 0px rgba(255, 0, 0, 0.8);
        }
        50% {
            box-shadow: 0 0 10px rgba(255, 0, 0, 1);
        }
        100% {
            box-shadow: 0 0 0px rgba(255, 0, 0, 0.8);
        }
        }

        .badge.bg-danger {
            animation: pulse-glow 1.5s infinite;
        }

        .navbar {
        background-color:rgb(33, 37, 41);
        color:solid rgb(239, 191, 144);
        border-radius: 15px;
        padding: 10px 20px;
        margin: 10px 20px;


        border-left: 2.5px solid rgb(239, 191, 144);
        border-right: 2.5px solid rgb(239, 191, 144);
        border-top: 1px solid rgb(239, 191, 144);
        border-bottom: 4px solid rgb(239, 191, 144);


        box-shadow:
            0 0 12px rgba(255, 215, 0, 0.6),
            0 6px 12px rgba(255, 215, 0, 0.25),
            0 -4px 8px rgba(255, 255, 255, 0.05);
        }

        .card-img-top {
        border-top-left-radius: 0.5rem !important;
        border-top-right-radius: 0.5rem !important;
        }

        .card-title .badge.bg-danger {
            font-weight: bold;
            box-shadow: 0 0 6px #ff0000;
            padding: 0.25rem 0.5rem;
            font-size: 0.9rem;
            border-radius: 0.5rem;
            display: inline-block;
        }

        .divider {
            height: 2px;
            background: linear-gradient(90deg, rgba(255, 215, 0, 0.8) 0%, rgba(255, 215, 0, 0) 100%);
            margin-top: 40px;
            margin-bottom: 40px;
            box-shadow: 0 4px 8px rgba(255, 215, 0, 0.2);
            border-radius: 5px;
        }


        .divider-with-text {
            display: flex;
            align-items: center;
            text-align: center;
            color: #fff;
            font-weight: bold;
            font-size: 1.5rem;
            text-transform: uppercase;
            position: relative;
            margin: 2rem 0;
        }

        .divider-with-text::before,
        .divider-with-text::after {
            content: '';
            flex: 1;
            border-bottom: 4px solid transparent;
            background: linear-gradient(to right, #ff7e5f, #feb47b, #28a745, #6a5acd);
            margin: 0 1rem;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }

        .divider-with-text span {
            position: relative;
            background-color: #222;
            padding: 0 1rem;
            border-radius: 50px;
            z-index: 1;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }

        .divider-with-text span:hover {
            animation: bounce 0.5s ease-in-out infinite;
        }

        @keyframes bounce {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-10px);
            }
        }


        .btn-outline-primary {
            color: #000;
            border-color: #000;
        }


        .btn-outline-primary:hover {
            background-color: #000;
            color: #fff;
        }

    </style>
</head>
<body>


    <!-- 導航欄 -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="../index.php" style="text-shadow: 0 0 5px #ffd700;">
                <i class="bi bi-lightning-charge-fill me-1"></i> Nosh Now
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="../index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../account_manage.php">帳號管理</a>
                    </li>
                    <!-- 客戶可見的購物車 -->
                    <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'Customer'): ?>
                    <li class="nav-item dropdown me-4">
                        <a class="nav-link dropdown-toggle position-relative" href="#" id="cartDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-cart-fill"></i> Cart
                            <span id="cart-count-badge"
                                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                    style="min-width: 1.5em; font-size: 0.75rem;">
                                0
                                </span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end"
                            aria-labelledby="cartDropdown"
                            style="min-width: 250px; max-height: 300px; overflow-y: auto;"
                            id="cart-items-list">

                            <!-- 動態插入的購物車商品放這裡 -->
                            <li id="cart-dynamic-items">
                                <div class="px-3 py-2 text-center">Loading...</div>
                            </li>

                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-center" href="cart.php">View Full Cart</a></li>
                        </ul>

                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

<?php
require_once __DIR__ . '/../db_connection.php';

$mode = $_GET['mode'] ?? 'restaurant';
$q = trim($_GET['q'] ?? '');
?>

<br>
<br>


<!-- 搜尋模式 + 關鍵字表單 -->
<div class="container mt-5 pt-4">
    <form method="get">
        <div class="row g-3 align-items-end">
            <!-- 搜尋模式 -->
            <div class="col-md-3">
                <label for="filterMode" class="form-label" style="color: #000; font-weight: 1000">搜尋模式</label>
                <select class="form-select rounded-pill" name="mode" id="filterMode">
                    <option value="restaurant" <?= $mode === 'restaurant' ? 'selected' : '' ?>>依餐廳</option>
                    <option value="menu" <?= $mode === 'menu' ? 'selected' : '' ?>>依餐點</option>
                </select>
            </div>

            <!-- 關鍵字 -->
            <div class="col-md-5">
                <label for="keyword" class="form-label" style="color: #000; font-weight: 1000">關鍵字</label>
                <input type="text" class="form-control rounded-pill" name="q" id="keyword"
                    placeholder="<?= $mode === 'restaurant' ? '輸入餐廳或餐點名稱' : '輸入餐點名稱或類別' ?>"
                    value="<?= htmlspecialchars($q) ?>">
            </div>

            <!-- 搜尋按鈕 -->
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary rounded-pill w-100" style="font-weight: 1000">
                    <i class="bi bi-search"></i> 搜尋
                </button>
            </div>
        </div>
    </form>
</div>

<!-- 分隔線，包含文字 -->
<div class="divider-with-text my-5">
    <span>查詢結果</span>
</div>

<!-- 顯示搜尋結果 -->
<div class="container mb-5">
    <div class="row">
        <?php if ($mode === 'restaurant'): ?>
            <?php
            $stmt = $pdo->prepare("
                SELECT restaurant_id, restaurantName, restaurantAddress, operationTime
                FROM restaurant
                WHERE restaurantName LIKE ? OR restaurantAddress LIKE ?");
            $stmt->execute(["%$q%", "%$q%"]);
            $restaurants = $stmt->fetchAll();

            foreach ($restaurants as $r):
            ?>
                <div class="col-12 col-sm-6 col-lg-4 mb-4">
                <div class="card shadow-sm rounded-4 border-0">
                    <img src="../assets/images/food-placeholder.svg" class="card-img-top rounded-top" alt="餐廳示意圖">
                    <div class="card-body">
                        <h5 class="card-title d-flex justify-content-between">
                            <?= htmlspecialchars($r['restaurantName']) ?>
                            <span class="badge bg-danger py-1 px-2 rounded-pill" style="font-size: 0.9rem;">🔥 熱門</span>
                        </h5>
                        <p class="card-text text-muted mb-1">
                            地址：<?= htmlspecialchars($r['restaurantAddress']) ?>
                        </p>
                        <p class="card-text mb-2">
                            營業時間：<?= htmlspecialchars($r['operationTime']) ?>
                        </p>
                        <a href="restaurant_menu.php?restaurant_id=<?= $r['restaurant_id'] ?>" class="btn btn-sm btn-dark w-100">查看所有餐點</a>
                    </div>
                </div>
                </div>
            <?php endforeach; ?>

<?php elseif ($mode === 'menu'): ?>
    <?php


    // 1. 先撈前五推薦的 item_id 與 max_score
    $stmt = $pdo->prepare("
        SELECT item_id, MAX(score) AS max_score
        FROM weighted_recommendation
        WHERE user_id = ?
        GROUP BY item_id
        ORDER BY max_score DESC
        LIMIT 5
    ");
    $stmt->execute([$user_id]);
    $topItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $recommendedMenus = [];
    $excludeIds = [];

    if (count($topItems) > 0) {
        $excludeIds = array_column($topItems, 'item_id');
        $placeholders = implode(',', array_fill(0, count($excludeIds), '?'));

        // 2. 用 IN 取菜單與餐廳資訊，保持順序
        $stmt2 = $pdo->prepare("
            SELECT m.item_id, m.foodName, m.dessert, m.drink, m.main_dish,
                   r.restaurantName, r.restaurant_id
            FROM menuitem m
            JOIN restaurant r ON m.restaurant_id = r.restaurant_id
            WHERE m.item_id IN ($placeholders)
            ORDER BY FIELD(m.item_id, $placeholders)
        ");
        // 把 item_id 放兩次：IN 和 ORDER BY FIELD
        $params = array_merge($excludeIds, $excludeIds);
        $stmt2->execute($params);
        $recommendedMenus = $stmt2->fetchAll();
    }

    // 3. 搜尋字串
    $searchTerm = "%$q%";

    if (count($excludeIds) > 0) {
        $placeholders2 = implode(',', array_fill(0, count($excludeIds), '?'));
        $sql = "
            SELECT m.item_id, m.foodName, m.dessert, m.drink, m.main_dish,
                   r.restaurantName, r.restaurant_id
            FROM menuitem m
            JOIN restaurant r ON m.restaurant_id = r.restaurant_id
            WHERE (m.foodName LIKE ? OR m.dessert LIKE ? OR m.drink LIKE ? OR m.main_dish LIKE ?)
              AND m.item_id NOT IN ($placeholders2)
        ";
        $params = array_merge([$searchTerm, $searchTerm, $searchTerm, $searchTerm], $excludeIds);
    } else {
        $sql = "
            SELECT m.item_id, m.foodName, m.dessert, m.drink, m.main_dish,
                   r.restaurantName, r.restaurant_id
            FROM menuitem m
            JOIN restaurant r ON m.restaurant_id = r.restaurant_id
            WHERE m.foodName LIKE ? OR m.dessert LIKE ? OR m.drink LIKE ? OR m.main_dish LIKE ?
        ";
        $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm];
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $menuItems = $stmt->fetchAll();


    // 先輸出推薦結果，帶一個「推薦」標籤
    foreach ($recommendedMenus as $item):
    ?>
        <div class="col-12 col-sm-6 col-lg-4 mb-4">
            <div class="card shadow-sm rounded-4 border-0">
                <img src="../assets/images/food-placeholder.svg" class="card-img-top rounded-top" alt="餐點示意圖">
                <div class="card-body">
                    <h5 class="card-title">
                        <?= htmlspecialchars($item['foodName']) ?>
                        <span class="badge bg-danger" style="font-size: 0.8rem; padding-left: 0.3rem; padding-right: 0.3rem; margin-right: 0.3rem; margin-left: 0.3rem;">推薦</span>
                    </h5>
                    <p class="card-text text-muted mb-1">
                        甜點：<?= htmlspecialchars($item['dessert']) ?: '無' ?>
                    </p>
                    <p class="card-text text-muted mb-1">
                        飲料：<?= htmlspecialchars($item['drink']) ?: '無' ?>
                    </p>
                    <p class="card-text text-muted mb-1">
                        主餐：<?= htmlspecialchars($item['main_dish']) ?: '無' ?>
                    </p>
                    <p class="card-text text-muted mb-2">
                        來自餐廳：<a href="restaurant_menu.php?restaurant_id=<?= $item['restaurant_id'] ?>">
                            <?= htmlspecialchars($item['restaurantName']) ?>
                        </a>
                    </p>
                    <div class="d-flex justify-content-between mt-3">
                        <a href="menu_item_details.php?menu_id=<?= $item['item_id'] ?>" class="btn btn-sm btn-dark me-2">
                            查看更多
                        </a>
                        <form method="post" action="add_to_cart.php" class="d-flex gap-2">
                            <input type="hidden" name="item_id" value="<?= $item['item_id'] ?>">
                            <input type="number" name="quantity" value="1" min="1" class="form-control form-control-sm" style="width: 60px;">
                            <button type="submit" class="btn btn-sm btn-primary">加入</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <!-- 輸出正常搜尋結果 -->
    <?php foreach ($menuItems as $item): ?>
        <div class="col-12 col-sm-6 col-lg-4 mb-4">
            <div class="card shadow-sm rounded-4 border-0">
                <img src="../assets/images/food-placeholder.svg" class="card-img-top rounded-top" alt="餐點示意圖">
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($item['foodName']) ?></h5>
                    <p class="card-text text-muted mb-1">
                        甜點：<?= htmlspecialchars($item['dessert']) ?: '無' ?>
                    </p>
                    <p class="card-text text-muted mb-1">
                        飲料：<?= htmlspecialchars($item['drink']) ?: '無' ?>
                    </p>
                    <p class="card-text text-muted mb-1">
                        主餐：<?= htmlspecialchars($item['main_dish']) ?: '無' ?>
                    </p>
                    <p class="card-text text-muted mb-2">
                        來自餐廳：<a href="restaurant_menu.php?restaurant_id=<?= $item['restaurant_id'] ?>">
                            <?= htmlspecialchars($item['restaurantName']) ?>
                        </a>
                    </p>
                    <div class="d-flex justify-content-between mt-3">
                        <a href="menu_item_details.php?menu_id=<?= $item['item_id'] ?>" class="btn btn-sm btn-dark me-2">
                            查看更多
                        </a>
                        <form method="post" action="add_to_cart.php" class="d-flex gap-2">
                            <input type="hidden" name="item_id" value="<?= $item['item_id'] ?>">
                            <input type="number" name="quantity" value="1" min="1" class="form-control form-control-sm" style="width: 60px;">
                            <button type="submit" class="btn btn-sm btn-primary">加入</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>


    </div>
</div>


    <!-- 頁尾 -->
    <footer class="footer py-4 mt-5">
        <div class="container text-center">
            <p>© 2025 Nosh Now | All rights reserved</p>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

    <script>
        window.onload = function() {
            fetch('get_cart_items.php')
                .then(response => response.text())
                .then(data => {
                    // 更新商品清單的部分，不影響「查看完整內容」
                    document.getElementById('cart-dynamic-items').innerHTML = data;
                })
                .catch(error => {
                    console.error('Error fetching cart items:', error);
                    document.getElementById('cart-dynamic-items').innerHTML =
                        '<div class="px-3 py-2 text-center">Error loading items</div>';
                });
        };
    </script>


    <script>
        function updateCartCount() {
            fetch('get_cart_count.php')
            .then(response => response.json())
            .then(data => {
                const badge = document.getElementById('cart-count-badge');
                if (data.count > 0) {
                badge.textContent = data.count;
                badge.style.display = 'inline-block';
                } else {
                badge.style.display = 'none'; // 沒東西就隱藏
                }
            })
            .catch(error => {
                console.error('Error loading cart count:', error);
            });
        }

        // 載入頁面時執行
        window.addEventListener('load', updateCartCount);
    </script>

</body>
</html>
