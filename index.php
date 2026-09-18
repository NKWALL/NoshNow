<?php
session_start();
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Nosh Now</title>
    <link href="assets/css/site.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">


    <style>
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
        0% {
            transform: translateY(0);
        }
        50% {
            transform: translateY(-5px);
        }
        100% {
            transform: translateY(0);
        }
        }


        .navbar {
        background-color: #1a1a1a;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
        }


        .navbar-brand {
        font-size: 1.8rem;
        font-weight: bold;
        color: #ffd700;
        text-transform: uppercase;
        letter-spacing: 2px;
        transition: all 0.3s ease;
        }


        .navbar-nav .nav-link {
        color: #d4af37;
        padding: 12px 20px;
        font-size: 1.1rem;
        font-weight: 500;
        transition: color 0.3s ease, transform 0.3s ease;
        }

        .navbar-nav .nav-link:hover {
        color: #ffd700;
        transform: scale(1.05);
        }


        .navbar-nav .nav-item.dropdown:hover .dropdown-menu {
        display: block;
        background-color: #1a1a1a;
        animation: fadeIn 0.5s ease-in-out;
        border: 1px solid #ffd700;
        }

        .dropdown-menu {
        border-radius: 8px;
        box-shadow: 0px 8px 15px rgba(0, 0, 0, 0.3);
        }

        .dropdown-item {
        padding: 10px 20px;
        font-size: 1.1rem;
        }

        .dropdown-item:hover {
        background-color: #ffd700;
        color: #1a1a1a;
        }


        .form-control {
        border-radius: 25px;
        padding: 10px 15px;
        border: 1px solid #ffd700;
        background-color: #333333;
        color: #fff;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }


        .btn-outline-success {
        border-radius: 25px;
        padding: 10px 20px;
        color: #ffd700;
        border-color: #ffd700;
        transition: background-color 0.3s ease;
        }

        .btn-outline-success:hover {
        background-color: #ffd700;
        color: #1a1a1a;
        }


        #loginbtn .dropdown-menu {
        border-radius: 10px;
        box-shadow: 0px 8px 15px rgba(0, 0, 0, 0.3);
        }

        #loginbtn .dropdown-item:hover {
        background-color: #ffd700;
        color: #1a1a1a;
        }


        .nav-link {
        position: relative;
        z-index: 1;
        }

        .nav-link:before {
        content: '';
        position: absolute;
        width: 100%;
        height: 2px;
        background: #ffd700;
        bottom: 0;
        left: 0;
        transform: scaleX(0);
        transform-origin: bottom right;
        transition: transform 0.25s ease-out;
        }

        .nav-link:hover:before {
        transform: scaleX(1);
        transform-origin: bottom left;
        }



        .dropdown-menu {
            background-color: #222;
            color: #fff;
            border-radius: 8px;
            box-shadow: 0px 8px 15px rgba(0, 0, 0, 0.3);
        }


        .dropdown-item {
            color: #fff;
            padding: 10px 20px;
        }


        .dropdown-item:hover {
            background-color: #ffd700;
            color: #1a1a1a;
        }


        .form-control {
            border-radius: 25px;
            padding: 10px 15px;
            border: 1px solid #ffd700;
            background-color: #333333;
            color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }


        .form-control:focus {
            background-color: #333333;
            border-color: #ffd700;
            color: #fff;
        }


        .btn-outline-success:focus,
        .btn-outline-success:hover {
            box-shadow: #ffd700;
            border-color: #ffd700;
            color: #ffffff;
        }


        .form-control:focus {
        box-shadow: 0 0 10px rgba(255, 215, 0, 0.8);
        border-color: #ffd700;
        background-color: #333333;
        }

        .badge.bg-danger {
        font-weight: bold;
        box-shadow: 0 0 6px #ff0000;
        }


        .card-custom {
        background-color: #fff;
        color: #222;
        border: 2px solid #ffd700;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        transition: transform 0.2s ease;
        }

        .card-custom:hover {
        transform: translateY(-5px);
        }

        .card-custom .card-title {
        font-weight: 700;
        font-family: 'Noto Sans TC', sans-serif;
        }

        .card-custom .card-text {
        font-size: 0.9rem;
        }


    </style>

</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="index.php" style="text-shadow: 0 0 5px #ffd700;">
                <i class="bi bi-lightning-charge-fill me-1"></i> Nosh Now
            </a>

              <!-- 漢堡 -->
              <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
              </button>

              <!-- 漢堡展開 -->
              <div class="collapse navbar-collapse" id="navbarSupportedContent">
                  <!-- 一組導覽選單 -->
                  <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <!-- Home -->
                        <li class="nav-item">
                          <a class="nav-link active" aria-current="page" href="#">Home</a>
                        </li>
                        <!-- Dropdown -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Option</a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <?php if (isset($_SESSION['user_type'])): ?>
                                    <?php if ($_SESSION['user_type'] === 'Customer'): ?>
                                        <li><a class="dropdown-item" href="customer/browse_restaurant.php">瀏覽餐廳</a></li>
                                        <li><a class="dropdown-item" href="customer/customer_order_info.php">我的訂單</a></li>
                                        <li><a class="dropdown-item" href="customer/recommendation.php">我的專屬美食週報</a></li>
                                        <li><a class="dropdown-item" href="customer/lottery.php">想不到吃什麼嗎?</a></li>
                                    <?php elseif ($_SESSION['user_type'] === 'DeliveryPerson'): ?>
                                        <li><a class="dropdown-item" href="delivery/delivery_available.php">可接外送</a></li>
                                        <li><a class="dropdown-item" href="delivery/delivery_history.php">我的外送紀錄</a></li>
                                        <li><a class="dropdown-item" href="delivery/delivery_navigation.php">最佳路線導航</a></li>
                                    <?php elseif ($_SESSION['user_type'] === 'RestaurantOwner'): ?>
                                        <li><a class="dropdown-item" href="restaurant/restaurant_manage.php">餐廳管理</a></li>
                                        <li><a class="dropdown-item" href="restaurant/menu_manage.php">菜單管理</a></li>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <!-- 其他公共選項 -->
                                <li><hr class="dropdown-divider"></li>

                                <!-- 帳號管理選項 -->
                                <?php if (isset($_SESSION['username'])): ?>
                                    <!-- 已登入，顯示帳號管理 -->
                                    <li><a class="dropdown-item" href="account_manage.php">帳號管理</a></li>
                                <?php else: ?>
                                    <!-- 未登入，導向登入頁面 -->
                                    <li><a class="dropdown-item" href="login.php">帳號管理</a></li>
                                <?php endif; ?>
                            </ul>
                        </li>

                        <!-- cart -->
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
                                <li><a class="dropdown-item text-center" href="customer/cart.php">View Full Cart</a></li>
                            </ul>
                        </li>
                        <?php endif; ?>
                  </ul>


                        <!-- navbar搜尋欄 -->
                        <form class="d-flex me-3 mb-sm-0 w-100">
                            <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search" style="flex-grow: 1;">
                            <button class="btn btn-outline-success" type="submit">Search</button>
                        </form>

                        <!-- 登入登出 -->
                        <div id="loginbtn" class="d-flex flex-column flex-sm-row ms-auto w-100 mt-3 mt-sm-0 ">
                            <ul class="navbar-nav">
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle text-white" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <?php if (isset($_SESSION['username'])): ?>
                                            <?php echo htmlspecialchars($_SESSION['username']); ?>
                                        <?php else: ?>
                                            Login / Register
                                        <?php endif; ?>
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                        <?php if (isset($_SESSION['username'])): ?>
                                            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                                        <?php else: ?>
                                            <li><a class="dropdown-item" href="login.php">Login</a></li>
                                            <li><a class="dropdown-item" href="register.php">Register</a></li>
                                        <?php endif; ?>
                                    </ul>
                                </li>
                            </ul>
                        </div>

              </div>
        </div>
    </nav>


<!-- Rotating Banner (Enhanced Carousel) -->
<div id="mainCarousel" class="carousel slide" data-bs-ride="carousel">
  <div class="carousel-inner">

    <div class="carousel-item active">
      <img src="assets/images/food-placeholder.svg" class="d-block w-100" alt="快速外送示意圖">
      <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-50 rounded p-3">
        <h5>火鍋任你配！</h5>
        <p>暖心又暖胃，立即享受在家火鍋。</p>
        <a href="#" class="btn btn-warning">立即點餐</a>
      </div>
    </div>

    <div class="carousel-item">
      <img src="assets/images/food-placeholder.svg" class="d-block w-100" alt="熱門優惠示意圖">
      <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-50 rounded p-3">
        <h5>限時牛排優惠</h5>
        <p>多汁香嫩，滿足你的味蕾。</p>
        <a href="#" class="btn btn-danger">立即下單</a>
      </div>
    </div>

    <div class="carousel-item">
      <img src="assets/images/food-placeholder.svg" class="d-block w-100" alt="人氣餐廳示意圖">
      <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-50 rounded p-3">
        <h5>經典義大利麵</h5>
        <p>人氣推薦，經典不敗口味。</p>
        <a href="#" class="btn btn-success">馬上試試</a>
      </div>
    </div>

  </div>

  <button class="carousel-control-prev" type="button" data-bs-target="#mainCarousel" data-bs-slide="prev">
    <span class="carousel-control-prev-icon"></span>
  </button>
  <button class="carousel-control-next" type="button" data-bs-target="#mainCarousel" data-bs-slide="next">
    <span class="carousel-control-next-icon"></span>
  </button>
</div>


    <!-- 分隔線 -->
    <div class="divider-with-text my-5">
        <span>推薦餐點</span>
    </div>



<!--小卡片區塊-->
<div class="container py-5">
  <div class="row text-center">
    <div class="col-md-4 mb-4">
      <div class="card shadow-lg border-0 rounded-4 h-100 transition-all" style="overflow: hidden;">
        <img src="assets/images/food-placeholder.svg" class="card-img-top" alt="餐點示意圖" style="object-fit: cover; height: 200px;">
        <div class="card-body">
          <h5 class="card-title fw-bold text-primary">義大利麵</h5>
          <p class="card-text text-muted">經典番茄肉醬，熱賣中！</p>
          <a href="#" class="btn btn-outline-primary rounded-pill">立即訂購</a>
        </div>
      </div>
    </div>

    <div class="col-md-4 mb-4">
      <div class="card shadow-lg border-0 rounded-4 h-100 transition-all" style="overflow: hidden;">
        <img src="assets/images/food-placeholder.svg" class="card-img-top" alt="餐點示意圖" style="object-fit: cover; height: 200px;">
        <div class="card-body">
          <h5 class="card-title fw-bold text-danger">炸雞便當</h5>
          <p class="card-text text-muted">酥脆多汁、現點現炸！</p>
          <a href="#" class="btn btn-outline-danger rounded-pill">立即訂購</a>
        </div>
      </div>
    </div>

    <div class="col-md-4 mb-4">
      <div class="card shadow-lg border-0 rounded-4 h-100 transition-all" style="overflow: hidden;">
        <img src="assets/images/food-placeholder.svg" class="card-img-top" alt="餐點示意圖" style="object-fit: cover; height: 200px;">
        <div class="card-body">
          <h5 class="card-title fw-bold text-success">壽司套餐</h5>
          <p class="card-text text-muted">每日新鮮現做日式壽司！</p>
          <a href="#" class="btn btn-outline-success rounded-pill">立即訂購</a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
include('db_connection.php');

$sql = "
    SELECT
        m.item_id,
        m.foodName,
        m.price,
        COALESCE(m.food_image, 'assets/images/food-placeholder.svg') AS food_image,
        m.calories,
        m.dessert,
        m.drink,
        m.main_dish,
        SUM(oi.quantity) AS sales_count
    FROM OrderItem oi
    JOIN contains c ON oi.order_id = c.order_id AND oi.sqNo = c.sqNo
    JOIN menuitem m ON c.item_id = m.item_id
    GROUP BY
        m.item_id,
        m.foodName,
        m.price,
        m.food_image,
        m.calories,
        m.dessert,
        m.drink,
        m.main_dish
    ORDER BY sales_count DESC
    LIMIT 10
";

$stmt = $pdo->query($sql);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-5">
    <div class="divider-with-text">
        <span>Top 10 銷售排行榜</span>
    </div>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5 g-4 justify-content-center">
        <?php foreach ($results as $row): ?>
            <div class="col d-flex justify-content-center"> <!-- 中央對齊卡片 -->
                <div class="card h-100 shadow-sm rounded-4 border-0" style="width: 100%; max-width: 220px;">
                    <img src="<?= !empty($row['food_image']) ? htmlspecialchars($row['food_image']) : 'assets/images/food-placeholder.svg' ?>"
                         class="card-img-top rounded-top"
                         style="height: 180px; object-fit: cover;"
                         onerror="this.onerror=null;this.src='assets/images/food-placeholder.svg';"
                         alt="<?= htmlspecialchars($row['foodName']) ?>">

                    <div class="card-body d-flex flex-column justify-content-between">
                        <div>
                            <h5 class="card-title"><?= htmlspecialchars($row['foodName']) ?></h5>
                                <p class="card-text">
                                    銷售數量：<?= $row['sales_count'] ?> 筆<br>
                                    價錢：NT$<?= number_format($row['price']) ?><br>
                                    熱量：<?= htmlspecialchars($row['calories']) ?> kcal<br>
                                    <?php if (!empty($row['dessert'])): ?>
                                        甜點：<?= htmlspecialchars($row['dessert']) ?><br>
                                    <?php endif; ?>
                                    <?php if (!empty($row['drink'])): ?>
                                        飲料：<?= htmlspecialchars($row['drink']) ?><br>
                                    <?php endif; ?>
                                    <?php if (!empty($row['main_dish'])): ?>
                                        主餐：<?= htmlspecialchars($row['main_dish']) ?>
                                    <?php endif; ?>
                                </p>
                        </div>

                        <!-- 加入購物車表單 -->
                        <form method="post" action="customer/add_to_cart.php" class="d-flex gap-2 mt-2">
                            <input type="hidden" name="item_id" value="<?= htmlspecialchars($row['item_id']) ?>">
                            <div class="input-group input-group-sm" style="width: 100px;">
                            <input type="number" name="quantity" value="1" min="1" class="form-control" style="background-color: #fff; color: black;" autocomplete="off">
                            </div>
                            <button type="submit" class="btn btn-sm btn-warning">加入</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>


    <!-- 頁尾 -->
    <footer class="footer py-4 mt-5">
        <div class="container text-center">
            <p>© 2025 Nosh Now | All rights reserved</p>
        </div>
    </footer>


    <script>
        window.onload = function() {
            fetch('customer/get_cart_items.php')
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
            fetch('customer/get_cart_count.php')
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
