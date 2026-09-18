<?php

require_once __DIR__ . '/../includes/auth.php';
require_login('Customer', '../login.php');
require_once __DIR__ . '/../db_connection.php';

$user_id = current_user_id();
$order_id = filter_input(INPUT_GET, 'order_id', FILTER_VALIDATE_INT);

if (!$order_id) {
    http_response_code(400);
    exit('訂單編號不正確。');
}

// 檢查是否已有退款申請
$stmt = $pdo->prepare("SELECT * FROM refundapplication WHERE order_id = ? AND user_id = ?");
$stmt->execute([$order_id, $user_id]);
$existing_refund = $stmt->fetch();

$stmt_order = $pdo->prepare(
    "SELECT * FROM `order`
     WHERE order_id = ? AND user_id = ? AND order_status IN ('completed', 'refund_requested')"
);
$stmt_order->execute([$order_id, $user_id]);
$order = $stmt_order->fetch();
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>退款申請</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color:rgb(232, 228, 228);
            color:rgb(0, 0, 0);
            font-family: 'Noto Sans TC', sans-serif, sans-serif;
        }
        .container {
            padding-top: 60px;
            max-width: 700px;
        }
        h2 {
            text-align: center;
            font-weight: 700;
            margin-bottom: 40px;
            text-shadow: 0 0 8px #f0d36d;
        }
        .card {
            background-color:rgb(255, 255, 255);
            border: 1.5px solid #f0d36d;
            border-radius: 1rem;
            box-shadow: 0 0 10px #f0d36d44;
            margin-bottom: 30px;
        }
        .card-body {
            color:rgb(0, 0, 0);
        }
        .form-label {
            font-weight: 600;
            color:rgb(0, 0, 0);
        }
        .form-control, .form-control-plaintext, textarea {
            background-color:rgb(255, 255, 255);
            color:rgb(0, 0, 0);
            border: 1.5px solid #f0d36d;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }
        .form-control:focus, textarea:focus {
            background-color:rgb(255, 255, 255);
            border-color: #f0d36d;
            box-shadow: 0 0 8px #f0d36d;
            color:rgb(0, 0, 0);
        }
        .btn-warning {
            background-color: #f0d36d;
            border-color: #f0d36d;
            color: #121212;
            font-weight: 700;
            border-radius: 0.6rem;
            padding: 10px 25px;
            box-shadow: 0 0 12px #f0d36daa;
            transition: all 0.3s ease;
        }
        .btn-warning:hover {
            background-color: #d1b953;
            border-color: #d1b953;
            color: #121212;
            box-shadow: 0 0 15px #d1b953cc;
        }
        .alert-info {
            background-color:rgb(255, 255, 255);
            border-color: #f0d36d;
            color:rgb(0, 0, 0);
            border-radius: 0.75rem;
            box-shadow: 0 0 8px #f0d36d44;


        }
        .alert-danger {
            background-color: #330000;
            border-color: #f0d36d;
            color:rgb(0, 0, 0);
            border-radius: 0.75rem;
            box-shadow: 0 0 8px #f0d36d88;
            text-align: center;
            font-weight: 700;
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
    </style>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

</head>
<body>
<div class="container">
        <h2 class="mb-4 text-center">
            <div class="bg-dark p-3 shadow-sm rounded">
                <a class="navbar-brand fw-bold text-white" href="#" style="text-shadow: 0 0 5px #ffd700;">
                    <i class="bi bi-lightning-charge-fill me-1"></i> Nosh Now 退款申請
                </a>
            </div>
        </h2>

    <?php if ($order): ?>
        <div class="card mb-4">
            <div class="card-body">
                <h5>訂單資訊</h5>
                <p><strong>訂單編號：</strong><?= htmlspecialchars($order['order_id']) ?></p>
                <p><strong>金額：</strong><?= htmlspecialchars($order['total_price']) ?> 元</p>
                <p><strong>訂單時間：</strong><?= htmlspecialchars($order['order_time']) ?></p>
            </div>
        </div>

        <?php if ($existing_refund): ?>
            <div class="alert alert-info">
                您已提交過退款申請，請參考以下資訊：
            </div>
            <div class="card">
                <div class="card-body">
                    <p><strong>退款金額：</strong><?= htmlspecialchars($existing_refund['refund_price']) ?> 元</p>
                    <p><strong>申請原因：</strong><br><?= nl2br(htmlspecialchars($existing_refund['reason'])) ?></p>
                    <p><strong>退款狀態：</strong><?= htmlspecialchars($existing_refund['refundStatus']) ?></p>
                </div>
            </div>
        <?php else: ?>
            <form action="submit_refund.php" method="POST" novalidate>
                <input type="hidden" name="order_id" value="<?= htmlspecialchars($order_id) ?>">
                <input type="hidden" name="refund_price" value="<?= htmlspecialchars($order['total_price']) ?>">

                <div class="mb-3">
                    <label class="form-label">退款金額</label>
                    <p class="form-control-plaintext" style="padding-left: .7rem; padding-right: .5rem;">
                        <?= htmlspecialchars($order['total_price']) ?> 元
                    </p>
                </div>

                <div class="mb-3">
                    <label for="reason" class="form-label">申請原因</label>
                    <textarea name="reason" id="reason" class="form-control" rows="4" required></textarea>
                </div>

                <button type="submit" class="btn btn-gold">提交退款申請</button>
            </form>
        <?php endif; ?>

                    <!-- 兩個按鈕 -->
            <div class="mt-4 d-flex justify-content-between">
                <a href="customer_order_info.php?order_id=<?= htmlspecialchars($order_id) ?>" class="btn btn-gold">
                    ← 回訂單詳情
                </a>
                <a href="../index.php" class="btn btn-gold">
                    回主頁
                </a>
            </div>


    <?php else: ?>
        <div class="alert alert-danger">找不到該訂單。</div>
    <?php endif; ?>
</div>
</body>
</html>
