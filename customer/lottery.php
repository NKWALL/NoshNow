<?php
session_start();
require_once __DIR__ . '/../db_connection.php';

$stmt = $pdo->query("
    SELECT m.item_id, m.foodName, m.dessert, m.drink, m.main_dish,
           m.price, m.calories,
           r.restaurantName, r.restaurant_id
    FROM menuitem m
    JOIN has h ON m.item_id = h.item_id
    JOIN restaurant r ON h.restaurant_id = r.restaurant_id
    ORDER BY RAND()
    LIMIT 1
");
$item = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8" />
    <title>隨機餐點推薦 | NoshNow</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- Bootstrap + Google Fonts -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+TC:wght@400;600&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />

    <style>
body {
    background-color: #343a40;
    font-family: 'Noto Sans TC', sans-serif;
    color: #fcd34d;
    min-height: 100vh;
    display: flex;
    align-items: center;
    padding-top: 3rem;
    padding-bottom: 3rem;
}

h1 {
    text-shadow: 0 0 5px #ffd700;
}

.card {
    background-color: #1f1f1f;
    border: 5px solid #fcd34d;
    border-radius: 1rem;
    padding: 1.5rem;

    color:rgb(243, 177, 111);
    max-width: 500px;
    margin: 0 auto;
    animation: goldGlow 2.5s ease-in-out infinite;
    box-shadow: 0 0 20px 5px rgba(245, 182, 115, 0.6);
}

@keyframes goldGlow {
  0%, 100% {
    border-color: #fcd34d;
    box-shadow:
      0 0 15px 3px rgba(252, 211, 77, 0.7),
      0 0 30px 6px rgba(252, 211, 77, 0.5);
  }
  50% {
    border-color: #fff176;
    box-shadow:
      0 0 25px 6px rgba(255, 223, 0, 1),
      0 0 40px 12px rgba(255, 223, 0, 0.8);
  }
}


.card a {
    color: #fcd34d;
    text-decoration: none;
}

.card a:hover {
    text-decoration: underline;
}

p {
    color: #fcd34d;
}

.btn-outline-dark {
    color: #fcd34d;
    border-color: #fcd34d;
}

.btn-outline-dark:hover {
    background-color: #fcd34d;
    color: #1f1f1f;
    border-color: #fcd34d;
}

.btn-primary {
    background-color: rgb(241, 177, 75);
    border: 1px solid #fcd34d;
    color: #1f1f1f;
}

.btn-primary:hover {
    background-color: #fcd34d;
    color: #1f1f1f;
    border-color: #fcd34d;
}

input.form-control-sm {
    background-color: #2c2c2c;
    color: #fcd34d;
    border: 1px solid #fcd34d;
    border-radius: 0.5rem;
    width: 60px;
    transition: all 0.3s ease;
}

input.form-control-sm:focus {
    background-color: #2c2c2c;
    color: #fcd34d;
    border-color: #ffd700;
    outline: none;
    box-shadow: 0 0 8px 2px rgba(255, 215, 0, 0.6);
}

.alert-warning {
    background-color: #3a3a3a;
    color: #f87171;
    border: none;
    max-width: 500px;
    margin: 1rem auto;
}

.btn-outline-secondary {
    color: #fcd34d;
    border-color: #fcd34d;
}

.btn-outline-secondary:hover {
    background-color: #fcd34d;
    color: #1f1f1f;
    border-color: #fcd34d;
}

.text-center {
    text-align: center;
}

.ad-title {
    display: inline-block;
    width: auto;
    padding: 1rem 2rem;
    font-size: 2.2rem;
    font-weight: 800;
    line-height: 1.5;
    border: 4px double rgb(243, 177, 111);
    background: linear-gradient(135deg, #1f1f1f 0%, #343a40 100%);
    color:rgb(243, 177, 111);
    box-shadow: 0 0 20px 5px rgba(252, 211, 77, 0.6);
    animation: flashGlow 2s infinite;
    text-shadow: 2px 2px 5px #000, 0 0 10px #fcd34d;
    border-radius: 1rem;
}


@keyframes flashGlow {
  0% {
    text-shadow: 2px 2px 5px #000, 0 0 10px #fcd34d;
  }
  50% {
    text-shadow: 2px 2px 5px #000, 0 0 20px #fff176;
  }
  100% {
    text-shadow: 2px 2px 5px #000, 0 0 10px #fcd34d;
  }
}

    </style>
</head>
<body>
<div class="container">
<div class="container">
    <div class="text-center mb-4 ">
        <h1 class="ad-title text-center">
            <i class="bi bi-lightning-charge-fill me-1"></i> Nosh Now 驚喜餐點推薦
        </h1>
    </div>

    <?php if ($item): ?>
        <div class="card shadow rounded-4 mx-auto">
            <div class="card-body">
                <h4 class="card-title text-center"><?= htmlspecialchars($item['foodName']) ?></h4>
                <p style="color:rgb(243, 177, 111)" >
                    <?php if (!empty($item['main_dish'])): ?>
                        主餐：<?= htmlspecialchars($item['main_dish']) ?><br />
                    <?php endif; ?>
                    <?php if (!empty($item['dessert'])): ?>
                        甜點：<?= htmlspecialchars($item['dessert']) ?><br />
                    <?php endif; ?>
                    <?php if (!empty($item['drink'])): ?>
                        飲料：<?= htmlspecialchars($item['drink']) ?><br />
                    <?php endif; ?>
                </p>
                <p style="color:rgb(243, 177, 111)">
                    價格：<?= number_format($item['price'], 2) ?> 元<br />
                    卡路里：<?= htmlspecialchars($item['calories']) ?> 大卡
                </p>
                <p style="color:rgb(243, 177, 111)">
                    來自餐廳：
                    <a style="color:rgb(243, 177, 111)" href="restaurant_menu.php?restaurant_id=<?= $item['restaurant_id'] ?>">
                        <?= htmlspecialchars($item['restaurantName']) ?>
                    </a>
                </p>
                <div class="d-flex justify-content-between mt-3">
                    <a href="browse_restaurant.php" class="btn btn-outline-dark w-50 me-2">查看更多</a>
                    <form method="post" action="add_to_cart.php" class="d-flex w-50 gap-2">
                        <input type="hidden" name="item_id" value="<?= $item['item_id'] ?>" />
                        <input type="number" name="quantity" value="1" min="1" class="form-control form-control-sm" />
                        <button type="submit" class="btn btn-primary btn-sm">加入</button>
                    </form>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-warning text-center">目前沒有可供推薦的餐點。</div>
    <?php endif; ?>

    <div class="text-center mt-4">
        <a href="../index.php" class="btn btn-outline-secondary rounded-pill">返回首頁</a>
        <a href="lottery.php" class="btn btn-outline-secondary rounded-pill">再抽一次</a>
    </div>
</div>
</body>
</html>
