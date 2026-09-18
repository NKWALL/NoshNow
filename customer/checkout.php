<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_login('Customer', '../login.php');
require_once __DIR__ . '/../db_connection.php';

$username = (string) $_SESSION['username'];
$userId = current_user_id();
$cart_id = filter_var($_SESSION['cart_order_id'] ?? null, FILTER_VALIDATE_INT);

if (!$cart_id) {
    header('Location: cart.php');
    exit;
}

$cartCheck = $conn->prepare(
    'SELECT c.cart_id
     FROM cart c
     LEFT JOIN `order` o ON o.cart_id = c.cart_id
     WHERE c.cart_id = ? AND c.user_id = ? AND o.order_id IS NULL'
);
$cartCheck->execute([$cart_id, $userId]);
if (!$cartCheck->fetchColumn()) {
    header('Location: cart.php');
    exit;
}

// 取得用戶基本資料
$sql_user = "
    SELECT u.username, c.phone, c.customer_address
    FROM users u
    LEFT JOIN customer c ON u.user_id = c.user_id
    WHERE u.username = ?
";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->execute([$username]);
$user = $stmt_user->fetch(PDO::FETCH_ASSOC);

$user['username'] = $user['username'] ?? '';
$user['phone'] = $user['phone'] ?? '';
$user['customer_address'] = $user['customer_address'] ?? '';


// 取得購物車商品資料
$sql_items = "
    SELECT mi.foodName, mi.main_dish, mi.drink, mi.dessert
    FROM records r
    JOIN menuitem mi ON r.item_id = mi.item_id
    WHERE r.cart_id = ?
";
$stmt_items = $conn->prepare($sql_items);
$stmt_items->execute([$cart_id]);
$order_items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

// 取得付款方式
$sql_payment = "SELECT cmnPayMethod FROM cmnpaymethod WHERE user_id = (SELECT user_id FROM users WHERE username = ?)";
$stmt_pay = $conn->prepare($sql_payment);
$stmt_pay->execute([$username]);
$payment_methods_raw = $stmt_pay->fetchAll(PDO::FETCH_COLUMN);

// 排序付款方式
$preferred_order = ['Credit Card', 'PayPal', 'Cash', 'Line Pay', 'Debit Card', 'Bank Transfer'];
$payment_methods = array_values(array_filter($preferred_order, fn($m) => in_array($m, $payment_methods_raw)));
if (empty($payment_methods)) {
    $payment_methods = $preferred_order;
}
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>結帳資訊 - NoshNow</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color:rgb(232, 228, 228);
            color: #f0d066;
            font-family: 'Segoe UI', sans-serif;
        }
        .card {
            background-color:rgb(255, 255, 255);
            border: 3px solid #f0d066;
        }
        .card-header {
            background-color:rgb(0, 0, 0);
            color:rgb(241, 177, 75);
            border-bottom: 1px solid rgb(241, 177, 75);

        }
        .form-control, .form-select, textarea {
            background-color:rgb(255, 255, 255);
            color:rgb(26, 13, 13);
            border: 2px solid rgb(241, 177, 75);
        }
        .form-control:focus, .form-select:focus, textarea:focus {
            background-color:rgb(255, 255, 255);
            color:rgb(0, 0, 0);
            border-color: #ffe066;
            box-shadow: none;
        }
        .btn-gold {
            background-color:rgb(241, 177, 75);
            color: #1e1e1e;
            border: black 2px solid;
        }
        .btn-gold:hover {
            background-color: #ffe066;
            color: #000;
        }
        .list-group-item {
            background-color:rgb(255, 255, 255);
            color:rgb(241, 177, 75);
            border: 2px solid  #2a2a2a;
        }
        .alert-danger {
            background-color: #661212;
            color: #ffdede;
            border: 1px solid #ff4e4e;
        }

        .btn-outline-gold {
                color: rgb(0, 0, 0);
                border: 1px solid rgb(181, 180, 178);
                background-color: rgb(181, 180, 178);
            }
            .btn-outline-gold:hover {
                background-color: rgb(208, 206, 204);
                color: black;
            }

    </style>
</head>
<body>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
            <h2 class="mb-4 text-center">
                <div class="bg-dark p-3 shadow-sm rounded">
                    <a class="navbar-brand fw-bold text-white" href="#" style="text-shadow: 0 0 5px #ffd700;">
                        <i class="bi bi-lightning-charge-fill me-1"></i> Nosh Now 結帳資訊
                    </a>
                </div>
            </h2>

                <div class="card-body">
                    <?php if ($user): ?>
                        <div class="mb-3">
                            <p><strong>使用者：</strong> <?= htmlspecialchars($user['username']) ?></p>
                            <p><strong>聯絡電話：</strong> <?= isset($user['phone']) ? htmlspecialchars($user['phone']) : '' ?></p>
                            <div class="mb-3">
                                <label class="form-label">請點選地圖設定送餐地址</label>
                                <?php if (google_maps_api_key() === ''): ?>
                                    <p class="alert alert-warning">請先設定 Google Maps API 金鑰，才能在地圖上選擇送餐位置。</p>
                                <?php else: ?>
                                    <div id="map" style="height: 400px; border: 2px solid #f0d066;"></div>
                                <?php endif; ?>
                                <p class="form-text text-dark mt-2">請點擊地圖以選擇送餐位置</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-danger">
                            無法取得使用者資料，請確認帳戶資料是否完整。
                        </div>
                    <?php endif; ?>

                    <h5 class="mt-4 mb-2">訂單商品</h5>
                    <ul class="list-group mb-3">
                        <?php foreach ($order_items as $item): ?>
                            <li class="list-group-item">
                                <?= htmlspecialchars($item['foodName']) ?>
                                <div class="small text-muted">
                                    <?= isset($item['main_dish']) ? htmlspecialchars($item['main_dish']) : '' ?>
                                    <?= isset($item['drink']) ? htmlspecialchars($item['drink']) : '' ?>
                                    <?= isset($item['dessert']) ? htmlspecialchars($item['dessert']) : '' ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <form method="POST" action="process_checkout.php">
                        <!-- 隱藏欄位移進來 -->
                        <input type="hidden" name="selected_lat" id="selected_lat">
                        <input type="hidden" name="selected_lng" id="selected_lng">

                        <div class="mb-3">
                            <label for="payment_method" class="form-label">付款方式</label>
                            <select name="payment_method" id="payment_method" class="form-select" required>
                                <?php foreach ($payment_methods as $method): ?>
                                    <option value="<?= htmlspecialchars($method) ?>"><?= htmlspecialchars($method) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="order_ps" class="form-label">訂單備註</label>
                            <textarea name="order_ps" id="order_ps" rows="4" class="form-control" placeholder="可填寫額外備註..."></textarea>
                        </div>

                        <div class="d-flex gap-3 mt-4">
                            <a href="cart.php" class="btn btn-outline-gold w-50 text-center">← 返回購物車</a>
                            <button type="submit" class="btn btn-gold w-50" <?= google_maps_api_key() === '' ? 'disabled' : '' ?>>送出訂單</button>
                        </div>
                    </form>

                </div>

            </div>

        </div>
    </div>
</div>


<?php if (google_maps_api_key() !== ''): ?>
<script
  src="https://maps.googleapis.com/maps/api/js?key=<?= urlencode(google_maps_api_key()) ?>&callback=initMap"
  async defer></script>
<?php endif; ?>

<script>
    let map;
    let marker;
function initMap() {
    // 預設位置（台北101）
    const defaultLoc = { lat: 25.033964, lng: 121.564468 };

    // 先建立地圖，但中心還用預設位置，之後會移動
    map = new google.maps.Map(document.getElementById("map"), {
        center: defaultLoc,
        zoom: 15
    });

    // 嘗試取得瀏覽器位置
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                const userPos = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude,
                };
                // 把地圖中心移到使用者位置
                map.setCenter(userPos);

                // 加上標記
                if (!marker) {
                    marker = new google.maps.Marker({
                        position: userPos,
                        map: map,
                        title: "你的目前位置"
                    });
                } else {
                    marker.setPosition(userPos);
                }

                // 將座標寫入隱藏欄位，方便送出表單
                document.getElementById("selected_lat").value = userPos.lat;
                document.getElementById("selected_lng").value = userPos.lng;
            },
            (error) => {
                console.warn(`無法取得位置，使用預設位置: ${error.message}`);
                // 失敗就維持預設位置
            }
        );
    } else {
        console.warn("瀏覽器不支援 Geolocation");
        // 不支援就用預設位置
    }

    // 地圖點擊事件：使用者可點擊改變位置
    map.addListener("click", function(event) {
        const clickedLocation = event.latLng;

        if (!marker) {
            marker = new google.maps.Marker({
                position: clickedLocation,
                map: map
            });
        } else {
            marker.setPosition(clickedLocation);
        }

        document.getElementById("selected_lat").value = clickedLocation.lat();
        document.getElementById("selected_lng").value = clickedLocation.lng();
    });
}
</script>



</body>
</html>
