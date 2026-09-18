<?php

require_once __DIR__ . '/../includes/auth.php';
require_login('Customer', '../login.php');
require_once __DIR__ . '/../db_connection.php';

$userId = current_user_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cartId = filter_input(INPUT_POST, 'cart_id', FILTER_VALIDATE_INT);
    $itemId = filter_input(INPUT_POST, 'item_id', FILTER_VALIDATE_INT);

    if ($cartId && $itemId) {
        if (isset($_POST['update_quantity'])) {
            $quantity = max(1, (int) ($_POST['quantity'] ?? 1));
            $statement = $pdo->prepare(
                'UPDATE records r
                 JOIN cart c ON c.cart_id = r.cart_id
                 LEFT JOIN `order` o ON o.cart_id = c.cart_id
                 SET r.quantity = ?
                 WHERE r.cart_id = ? AND r.item_id = ? AND c.user_id = ? AND o.order_id IS NULL'
            );
            $statement->execute([$quantity, $cartId, $itemId, $userId]);
        } elseif (isset($_POST['delete_item'])) {
            $statement = $pdo->prepare(
                'DELETE r FROM records r
                 JOIN cart c ON c.cart_id = r.cart_id
                 LEFT JOIN `order` o ON o.cart_id = c.cart_id
                 WHERE r.cart_id = ? AND r.item_id = ? AND c.user_id = ? AND o.order_id IS NULL'
            );
            $statement->execute([$cartId, $itemId, $userId]);
        }
    }

    header('Location: cart.php');
    exit;
}

$statement = $pdo->prepare(
    'SELECT mi.foodName, mi.price, r.quantity, r.cart_id, r.item_id
     FROM cart c
     JOIN records r ON r.cart_id = c.cart_id
     JOIN menuitem mi ON mi.item_id = r.item_id
     LEFT JOIN `order` o ON o.cart_id = c.cart_id
     WHERE c.user_id = ? AND o.order_id IS NULL
     ORDER BY r.item_id'
);
$statement->execute([$userId]);
$items = $statement->fetchAll();
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
   <meta charset="UTF-8">
   <title>我的購物車</title>
   <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
   <style>
       body {
           background-color: rgb(232, 228, 228);
           color: #f0d066;
           font-family: 'Segoe UI', sans-serif;
       }
       .card {
           background-color: rgb(255, 255, 255);
           border: 1px solid #f0d066;
       }
       .card-header {
           background-color: rgb(255, 255, 255);
           color: rgb(241, 177, 75);
           border-bottom: 1px solid rgb(241, 177, 75);
       }
       .form-control, .form-select, textarea {
           background-color: rgb(255, 255, 255);
           color: rgb(26, 13, 13);
           border: 1px solid rgb(241, 177, 75);
       }
       .form-control:focus, .form-select:focus, textarea:focus {
           background-color: rgb(255, 255, 255);
           color: rgb(0, 0, 0);
           border-color: #ffe066;
           box-shadow: none;
       }
       .btn-gold {
           background-color: rgb(241, 177, 75);
           color: #1e1e1e;
           border: none;
       }
       .btn-gold:hover {
           background-color: #ffe066;
           color: #000;
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
       .table-dark {
           background-color: rgb(255, 255, 255);
           color: rgb(241, 177, 75);
       }
       .table th, .table td {
           text-align: center;
           background-color:rgb(255, 255, 255);
           color: black;
           border:2px solid rgb(241, 177, 75);
       }
       .table-bordered {
           border: 2px solid rgb(241, 177, 75);
       }
       .table-hover tbody tr:hover {
           background-color: rgb(241, 177, 75);
           color:rgb(255, 255, 255);
       }
   </style>
</head>
<body>
   <div class="container py-5">

   <h2 class="mb-4 text-center">
        <div class="bg-dark p-3 shadow-sm rounded">
            <a class="navbar-brand fw-bold text-white" href="#" style="text-shadow: 0 0 5px #ffd700;">
                <i class="bi bi-lightning-charge-fill me-1"></i> Nosh Now 我的購物車
            </a>
        </div>
    </h2>


       <?php if (count($items) > 0): ?>
           <div class="table-responsive">
               <table class="table table-bordered table-hover align-middle bg-white shadow-sm">
                   <thead class="table-dark">
                       <tr>
                           <th>餐點名稱</th>
                           <th>單價</th>
                           <th>數量</th>
                           <th>小計</th>
                           <th>操作</th>
                       </tr>
                   </thead>
                   <tbody>
                       <?php
                       $total = 0;
                       foreach ($items as $item):
                           $subtotal = $item['price'] * $item['quantity'];
                           $total += $subtotal;
                       ?>
                       <tr>
                           <td><?= htmlspecialchars($item['foodName']) ?></td>
                           <td>$<?= number_format($item['price'], 2) ?></td>
                           <td>
                               <form method="post" class="d-inline-block">
                                   <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1" class="form-control form-control-sm" style="width: 60px;">
                                   <input type="hidden" name="cart_id" value="<?= $item['cart_id'] ?>">
                                   <input type="hidden" name="item_id" value="<?= $item['item_id'] ?>">
                                   <button type="submit" name="update_quantity" class="btn btn-sm btn-warning mt-2">更新數量</button>
                               </form>
                           </td>
                           <td>$<?= number_format($subtotal, 2) ?></td>
                           <td>
                               <form method="post" class="d-inline-block">
                                   <input type="hidden" name="cart_id" value="<?= $item['cart_id'] ?>">
                                   <input type="hidden" name="item_id" value="<?= $item['item_id'] ?>">
                                   <button type="submit" name="delete_item" class="btn btn-sm btn-danger">刪除</button>
                               </form>
                           </td>
                       </tr>
                       <?php endforeach; ?>
                   </tbody>
                   <tfoot>
                       <tr class="table-secondary">
                           <td colspan="3" class="text-end"><strong>總金額：</strong></td>
                           <td><strong>$<?= number_format($total, 2) ?></strong></td>
                       </tr>
                   </tfoot>
               </table>
           </div>

           <?php
           if (count($items) > 0) {
               $_SESSION['cart_order_id'] = $items[0]['cart_id'];
           }
           ?>

           <div class="d-flex flex-column mt-4">
               <!-- 返回上一頁按鈕 -->
               <button onclick="window.history.back()" class="btn btn-outline-gold mb-2">返回上一頁</button>
               <!-- 返回瀏覽餐廳按鈕 -->
               <a href="browse_restaurant.php" class="btn btn-outline-gold mb-2">返回瀏覽餐廳</a>
               <a href="checkout.php" class="btn btn-gold">前往結帳</a>
           </div>

       <?php else: ?>
           <div class="alert alert-warning text-center">
               購物車目前是空的。
           </div>
           <div class="text-center">
               <a href="browse_restaurant.php" class="btn btn-outline-gold">繼續瀏覽餐點</a>
           </div>
       <?php endif; ?>
   </div>
</body>
</html>
