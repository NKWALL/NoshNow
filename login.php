<?php

declare(strict_types=1);

session_start();
require_once __DIR__ . '/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $userType = (string) ($_POST['user_type'] ?? '');
    $allowedRoles = ['Customer', 'DeliveryPerson', 'RestaurantOwner'];

    $statement = $conn->prepare(
        'SELECT user_id, username, password, user_type
         FROM users
         WHERE username = ? AND user_type = ?'
    );
    $statement->execute([$username, $userType]);
    $user = $statement->fetch();

    $passwordIsValid = $user && password_verify($password, $user['password']);

    if (in_array($userType, $allowedRoles, true) && $passwordIsValid) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_type'] = $user['user_type'];
        header('Location: index.php');
        exit;
    }

    $_SESSION['login_error'] = '帳號、密碼或身分類型不正確。';
    header('Location: login.php');
    exit;
}
?>


<!DOCTYPE html>
<html lang="zh-TW">
<head>
   <meta charset="UTF-8">
   <title>登入 | NoshNow</title>
   <meta name="viewport" content="width=device-width, initial-scale=1">


   <!-- Bootstrap + Google Fonts -->
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
   <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+TC:wght@400;600&display=swap" rel="stylesheet">
   <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">


   <style>
       body {

           background-color: #2c2f33;
           background-color: #343a40;


           font-family: 'Noto Sans TC', sans-serif;
           color: #fcd34d;
       }


       .login-box {
           width: 600px;
           height: 660px;
           margin: 60px auto;
           padding: 30px;
       }


       .card {
           background-color: #1f1f1f;
           border: 1px solid #fcd34d;
           border-radius: 1rem;
           padding: 2rem;
           color: #fcd34d;
           box-shadow: 0 0 0 0.2rem rgba(255, 215, 0, 0.25);


       }


       .form-control, .form-select {
       background-color: #2c2c2c;
       color: #fcd34d;
       border: 1px solid #fcd34d;
       border-radius: 0.5rem;
       transition: all 0.3s ease;
       }


       .form-control:focus, .form-select:focus {
       background-color: #2c2c2c;
       color: #fcd34d;
       border-color: #ffd700;
       outline: none;
       box-shadow: 0 0 8px 2px rgba(255, 215, 0, 0.6);
       }


       .form-control::placeholder {
       color: grey;
       }




       .form-control::placeholder {
           color: grey;
       }


       .form-select option {
           background-color: #2c2c2c;
           color: #fcd34d;
           box-shadow: 0 0 0 0.2rem rgba(255, 215, 0, 0.25);


       }


       .btn-gold {
           background-color: rgb(241, 177, 75);
           color: white;
           border: 1px solid #fcd34d;
       }


       .btn-gold:hover {
           background-color: #fcd34d;
           color: #1f1f1f;
       }


       .msg {
           color: #f87171;
           font-weight: 600;
           margin-bottom: 1rem;
       }


       a {
           color: #fcd34d;
           text-decoration: none;
       }


       .shadow {
       box-shadow: 0 .5rem 2rem rgba(255, 215, 0, 0.25) !important;
       }
   </style>
</head>


<body>
<div class="container d-flex justify-content-center align-items-center min-vh-100">
   <div class="login-box">
       <div class="card shadow shadow" >
           <form method="POST" action="login.php">
               <?php if (isset($_SESSION['login_error'])): ?>
                   <div class="msg text-center">
                       <?= htmlspecialchars($_SESSION['login_error']) ?>
                   </div>
                   <?php unset($_SESSION['login_error']); ?>
               <?php endif; ?>


               <h2 class="mb-4 text-center">
               <a class="navbar-brand fw-bold text-white" href="#" style="text-shadow: 0 0 5px #ffd700; font-size: 2rem;">
                   <i class="bi bi-lightning-charge-fill me-1"></i>Nosh Now 登入系統
               </a>
               </h2>




               <div class="mb-3">
                   <label for="username" class="form-label">使用者名稱</label>
                   <input type="text" class="form-control" id="username" name="username"
                          placeholder="請輸入帳號" required>
               </div>


               <div class="mb-3">
                   <label for="password" class="form-label">密碼</label>
                   <input type="password" class="form-control" id="password" name="password"
                          placeholder="請輸入密碼" required>
               </div>


               <div class="mb-4">
                   <label for="user_type" class="form-label">身份選擇</label>
                   <select id="user_type" name="user_type" class="form-select" required>
                       <option value="" disabled selected>請選擇身份</option>
                       <option value="Customer">Customer</option>
                       <option value="DeliveryPerson">Delivery Person</option>
                       <option value="RestaurantOwner">Restaurant</option>
                   </select>
               </div>


               <div class="d-grid mb-3">
                   <button type="submit" class="btn btn-gold btn-lg">登入</button>
               </div>


               <div class="text-center">
                   <a href="register.php">還沒有帳號？立即註冊</a>
               </div>
               <div class="text-center mt-2">
                   <a href="index.php">返回首頁</a>
               </div>
           </form>
       </div>
   </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', () => {
   const firstNode = document.body.childNodes[0];
   if (firstNode && firstNode.nodeType === Node.TEXT_NODE &&
       firstNode.textContent.trim().length > 0) {
       const msgDiv = document.createElement('div');
       msgDiv.className = 'msg';
       msgDiv.textContent = firstNode.textContent.trim();


       const card = document.querySelector('.card');
       card.prepend(msgDiv);
       firstNode.remove();
   }
});
</script>
</body>
</html>
