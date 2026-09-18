<?php

require_once __DIR__ . '/includes/auth.php';
require_login();
require_once __DIR__ . '/db_connection.php';

$user_id = current_user_id();
$user_type = (string) $_SESSION['user_type'];
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_valid_csrf();
    $action = (string) ($_POST['action'] ?? '');

    try {
        if ($action === 'change_password') {
            $currentPassword = (string) ($_POST['current_password'] ?? '');
            $newPassword = (string) ($_POST['password'] ?? '');
            $statement = $conn->prepare('SELECT password FROM users WHERE user_id = ?');
            $statement->execute([$user_id]);
            $storedPassword = (string) $statement->fetchColumn();
            $validCurrentPassword = password_verify($currentPassword, $storedPassword);

            if (!$validCurrentPassword || strlen($newPassword) < 8) {
                throw new InvalidArgumentException('目前密碼不正確，或新密碼少於 8 個字元。');
            }

            $statement = $conn->prepare('UPDATE users SET password = ? WHERE user_id = ?');
            $statement->execute([password_hash($newPassword, PASSWORD_DEFAULT), $user_id]);
        } elseif ($action === 'update_customer' && $user_type === 'Customer') {
            $calorieGoal = filter_input(INPUT_POST, 'daily_calorie_goal', FILTER_VALIDATE_INT);
            $statement = $conn->prepare(
                'UPDATE customer
                 SET daily_calorie_goal = ?, customer_address = ?, phone = ?
                 WHERE user_id = ?'
            );
            $statement->execute([
                $calorieGoal ?: null,
                trim((string) ($_POST['customer_address'] ?? '')),
                trim((string) ($_POST['phone'] ?? '')),
                $user_id,
            ]);
        } elseif ($action === 'update_vehicle' && $user_type === 'DeliveryPerson') {
            $vehicleType = trim((string) ($_POST['vehicle_type'] ?? ''));
            if ($vehicleType === '') {
                throw new InvalidArgumentException('請選擇交通工具。');
            }
            $statement = $conn->prepare(
                'UPDATE deliveryperson SET vehicle_type = ? WHERE user_id = ?'
            );
            $statement->execute([$vehicleType, $user_id]);
        } elseif (in_array($action, ['add_email', 'edit_email'], true)) {
            $newEmail = filter_var(
                trim((string) ($_POST[$action === 'add_email' ? 'email' : 'new_email'] ?? '')),
                FILTER_VALIDATE_EMAIL
            );
            if (!$newEmail) {
                throw new InvalidArgumentException('電子郵件格式不正確。');
            }
            if ($action === 'add_email') {
                $statement = $conn->prepare('INSERT INTO email (user_id, email) VALUES (?, ?)');
                $statement->execute([$user_id, $newEmail]);
            } else {
                $statement = $conn->prepare(
                    'UPDATE email SET email = ? WHERE email = ? AND user_id = ?'
                );
                $statement->execute([
                    $newEmail,
                    (string) ($_POST['original_email'] ?? ''),
                    $user_id,
                ]);
            }
        } elseif ($action === 'delete_email') {
            $statement = $conn->prepare(
                'DELETE FROM email WHERE email = ? AND user_id = ?'
            );
            $statement->execute([(string) ($_POST['email'] ?? ''), $user_id]);
        } else {
            throw new InvalidArgumentException('無法處理此項帳號設定。');
        }

        header('Location: account_manage.php?updated=1');
        exit;
    } catch (InvalidArgumentException $exception) {
        $error = $exception->getMessage();
    } catch (PDOException $exception) {
        error_log('NoshNow account update failed: ' . $exception->getMessage());
        $error = '更新失敗，請確認資料是否重複後再試。';
    }
}

$statement = $conn->prepare('SELECT user_id, username FROM users WHERE user_id = ?');
$statement->execute([$user_id]);
$user = $statement->fetch();

$statement = $conn->prepare('SELECT email FROM email WHERE user_id = ? ORDER BY email');
$statement->execute([$user_id]);
$emails = $statement->fetchAll();

if ($user_type === 'Customer') {
    $statement = $conn->prepare('SELECT * FROM customer WHERE user_id = ?');
    $statement->execute([$user_id]);
    $customer = $statement->fetch();
} elseif ($user_type === 'DeliveryPerson') {
    $statement = $conn->prepare('SELECT * FROM deliveryperson WHERE user_id = ?');
    $statement->execute([$user_id]);
    $delivery = $statement->fetch();
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>帳號管理</title>
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
   <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">




   <style>
       body {
           background-color: rgb(232, 228, 228);


           font-family: 'Noto Sans TC', sans-serif;
       }
       .card {
           background-color: white;
           border: 2px solid #ffc107;

       }
       .card-title {
           color: black;
           font-weight: bold;
       }
       .form-control, .form-select {
           background-color: white;
           border: 2px solid #ffc107;
           color: black;
       }
       .form-control:focus, .form-select:focus {
           border-color: #ffca2c;
           box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
       }
       .btn-primary {
           background-color: #ffc107;
           border: none;
           color: #000;
           font-weight: bold;
       }
       .btn-primary:hover {
           background-color: #ffca2c;
           color: black;
       }
       .btn-success {
           background-color: #28a745;
           border: none;
       }
       .btn-danger {
           background-color: #dc3545;
           border: none;
       }
       .btn-secondary {
           background-color: #6c757d;
           border: none;
       }
       .list-group-item {
           background-color: white;
           color: #fff;
           border: 1px solid #ffc107;
       }
       .list-group-item form input {
           background-color: white;
           border: 1px solid #ffc107;
           color: black;
       }
   </style>


</head>
<body>

   <div class="container mt-5">
       <?php if ($error): ?>
           <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
       <?php elseif (isset($_GET['updated'])): ?>
           <div class="alert alert-success">資料已更新。</div>
       <?php endif; ?>




       <!-- 顯示 user_id -->
       <div class="card">
           <h2 class="text-center">
               <div class="bg-dark p-3 shadow-sm rounded">
                   <a class="navbar-brand fw-bold text-white" href="#" style="text-shadow: 0 0 5px #ffd700; font-size: 2rem;">
                       <i class="bi bi-lightning-charge-fill me-1"></i> Nosh Now 帳號管理
                   </a>
               </div>
           </h2>
           <div class="card-body">
               <h5 class="card-title">帳號資訊</h5>
               <p><strong>使用者ID：</strong> <?= htmlspecialchars($user['user_id']) ?></p>
               <div class="d-flex justify-content-start mt-3">
                   <a href="index.php" class="btn btn-primary">回主頁</a>
               </div>
           </div>
       </div>


       <!-- 基本帳號資料 -->
       <div class="card mt-4">
           <div class="card-body">
               <h5 class="card-title">基本資料</h5>
               <form action="account_manage.php" method="POST">
                   <input type="hidden" name="action" value="change_password">
                   <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                   <div class="mb-3">
                       <label for="username" class="form-label">使用者名稱</label>
                       <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" disabled>
                   </div>
                   <div class="mb-3">
                       <label for="current_password" class="form-label">目前密碼</label>
                       <input type="password" class="form-control" id="current_password" name="current_password" required>
                   </div>
                   <div class="mb-3">
                       <label for="password" class="form-label">密碼</label>
                       <input type="password" class="form-control" id="password" name="password" minlength="8" placeholder="輸入至少 8 個字元的新密碼" required>
                   </div>
                   <button type="submit" class="btn btn-primary">更新基本資料</button>
               </form>
           </div>
       </div>


       <!-- 電子郵件管理 -->
       <div class="card mt-4">
           <div class="card-body">
               <h5 class="card-title">電子郵件管理</h5>
               <form action="account_manage.php" method="POST">
                   <input type="hidden" name="action" value="add_email">
                   <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                   <div class="mb-3">
                       <label for="email" class="form-label">新增電子郵件</label>
                       <input type="email" class="form-control" id="email" name="email" placeholder="輸入新電子郵件">
                   </div>
                   <button type="submit" class="btn btn-primary">新增電子郵件</button>
               </form>
               <ul class="list-group mt-3">
                   <?php foreach ($emails as $email): ?>
                       <li class="list-group-item">
                           <form action="account_manage.php" method="POST" class="d-flex align-items-center">
                               <input type="hidden" name="action" value="edit_email">
                               <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                               <input type="hidden" name="original_email" value="<?= htmlspecialchars($email['email']) ?>">
                               <input type="email" name="new_email" value="<?= htmlspecialchars($email['email']) ?>" class="form-control me-2" required>
                               <button type="submit" class="btn btn-success btn-sm me-2">修改</button>
                           </form>
                           <form action="account_manage.php" method="POST" class="mt-2">
                               <input type="hidden" name="action" value="delete_email">
                               <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                               <input type="hidden" name="email" value="<?= htmlspecialchars($email['email']) ?>">
                               <button type="submit" class="btn btn-danger btn-sm">刪除</button>
                           </form>
                       </li>
                   <?php endforeach; ?>
               </ul>
           </div>
       </div>


       <!-- 根據使用者類型顯示不同的資料 -->
       <?php if ($user_type == 'Customer'): ?>
           <div class="card mt-4">
               <div class="card-body">
                   <h5 class="card-title">顧客資料</h5>
                   <form action="account_manage.php" method="POST">
                       <input type="hidden" name="action" value="update_customer">
                       <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                       <div class="mb-3">
                           <label for="daily_calorie_goal" class="form-label">每日熱量目標</label>
                           <input type="number" class="form-control" id="daily_calorie_goal" name="daily_calorie_goal" value="<?= $customer['daily_calorie_goal'] ?>">
                       </div>
                       <div class="mb-3">
                           <label for="customer_address" class="form-label">顧客地址</label>
                           <input type="text" class="form-control" id="customer_address" name="customer_address" value="<?= htmlspecialchars($customer['customer_address'] ?? '') ?>">
                       </div>
                       <div class="mb-3">
                           <label for="phone" class="form-label">電話號碼</label>
                           <input type="text" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($customer['phone'] ?? '') ?>">
                       </div>
                       <button type="submit" class="btn btn-primary">更新顧客資料</button>
                   </form>
               </div>
           </div>


           <?php elseif ($user_type == 'DeliveryPerson'): ?>
               <div class="card mt-4">
                   <div class="card-body">
                       <h5 class="card-title">外送員資料</h5>
                       <form action="account_manage.php" method="POST">
                           <input type="hidden" name="action" value="update_vehicle">
                           <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                           <div class="mb-3">
                               <label for="rating" class="form-label">評價</label>
                               <input type="number" class="form-control" id="rating" name="rating" value="<?= $delivery['rating'] ?>" min="1" max="5" readonly>
                           </div>
                           <div class="mb-3">
                               <label for="vehicle_type" class="form-label">交通工具類型</label>
                               <select class="form-select" id="vehicle_type" name="vehicle_type" required>
                                   <option value="Car" <?= strcasecmp((string) $delivery['vehicle_type'], 'Car') === 0 ? 'selected' : '' ?>>Car</option>
                                   <option value="Motorcycle" <?= strcasecmp((string) $delivery['vehicle_type'], 'Motorcycle') === 0 ? 'selected' : '' ?>>Motorcycle</option>
                                   <option value="Bicycle" <?= strcasecmp((string) $delivery['vehicle_type'], 'Bicycle') === 0 ? 'selected' : '' ?>>Bicycle</option>
                               </select>
                           </div>
                           <button type="submit" class="btn btn-primary">更新外送員資料</button>
                       </form>
                   </div>
               </div>
           <?php endif; ?>

           <br>
           <br>



   </div>
</body>
</html>
