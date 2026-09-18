<!DOCTYPE html>
<html lang="zh-TW">
<head>
 <meta charset="UTF-8" />
 <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
 <title>註冊 | NoshNow</title>


 <!-- Bootstrap & 字體 -->
 <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
 <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+TC:wght@400;700&display=swap" rel="stylesheet">
 <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">


 <style>
   body {


     background-color: #2c2f33;
     background-color: #343a40;




     color: #fcd34d;
     font-family: 'Noto Sans TC', sans-serif;
   }


   .register-container {
     max-width: 600px;
     margin: 60px auto;
     padding: 30px;
     background-color: #1e1e1e;
     border: 1px solid #fcd34d;
     border-radius: 15px;
     box-shadow: 0 0 15px #fcd34d;
   }


   .form-label {
     color: #fcd34d;
   }


   .btn-gold {
     background-color: rgb(241, 177, 75);
     color: white;
     font-weight: bold;
     border: none;
   }


   .btn-gold:hover {
     background-color: #fcd34d;
     color:black;
   }


   .alert {
     margin-top: 15px;
   }


   a {
     color: #ccc;
   }


   a:hover {
     color: #ffd700;
   }


   select.form-control, select.form-select {
     background-color: #2C2C2C;
     color: #fcd34d;
     border: 1px solid #fcd34d;
     box-shadow: 0 0 0 0.2rem rgba(255, 215, 0, 0.25);


   }


   input.form-control {
     background-color: #2C2C2C;
     color: black;
   }


   .form-control::placeholder
   {
     color: grey;


   }
   input.form-control {
   background-color: #2C2C2C;
   color: #fcd34d;
   border: 1px solid #fcd34d;
   }


   input.form-control:focus {
     border-color: #ffd700;
     box-shadow: 0 0 0 0.2rem rgba(255, 215, 0, 0.25);
     color: #fcd34d;
     background-color: #2C2C2C;
   }
   select.form-select:focus {
   border-color: #ffd700;
   box-shadow: 0 0 10px #ffd700;
   color: #ffd700;
   background-color: #2C2C2C;
   }


   select.form-select option {
   background-color: #2C2C2C;
   color: #ffd700;
   }




 </style>
</head>


<body>
 <div class="container">
   <div class="register-container">
     <form id="registerForm">
       <h2 class="mb-4 text-center">
         <a class="navbar-brand fw-bold text-white" href="#" style="text-shadow: 0 0 5px #ffd700;">
           <i class="bi bi-lightning-charge-fill me-1"></i>Nosh Now 註冊系統
         </a>
       </h2>

       <!-- Username -->
       <div class="mb-3">
         <label for="username" class="form-label">使用者名稱</label>
         <input class="form-control" id="username" name="username" placeholder="使用者名稱" required>
       </div>

       <!-- Email -->
       <div class="mb-3">
         <label for="email" class="form-label">Email 信箱</label>
         <input type="email" class="form-control" id="email" name="email" placeholder="Email 信箱" required>
       </div>

       <!-- Password -->
       <div class="mb-3">
         <label for="password" class="form-label">密碼</label>
         <input type="password" class="form-control" id="password" name="password" placeholder="密碼" required>
       </div>

       <!-- Confirm Password -->
       <div class="mb-3">
         <label for="confirm_password" class="form-label">再次輸入密碼</label>
         <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="再次輸入密碼" required>
       </div>

       <!-- Account Type -->
       <div class="mb-3">
         <label for="account_type" class="form-label">帳號身份</label>
         <select class="form-select" id="account_type" name="account_type" required>
           <option value="" disabled selected>選擇身份類型</option>
           <option value="Customer">Customer</option>
           <option value="DeliveryPerson">Delivery Person</option>
           <option value="RestaurantOwner">Restaurant Owner</option>
         </select>
       </div>

       <!-- Customer fields -->
       <div id="customer_fields" style="display:none;">
         <div class="mb-3">
           <label for="customer_address" class="form-label">地址</label>
           <input class="form-control" id="customer_address" name="customer_address">
         </div>
         <div class="mb-3">
           <label for="phone" class="form-label">手機</label>
           <input class="form-control" id="phone" name="phone">
         </div>
         <div class="mb-3">
           <label for="daily_calorie_goal" class="form-label">每日熱量目標 (可選)</label>
           <input type="number" class="form-control"
                  id="daily_calorie_goal" name="daily_calorie_goal"
                  min="1" step="1" pattern="[0-9]+" inputmode="numeric">
         </div>
       </div>


       <!-- DeliveryPerson fields -->
       <div id="deliveryperson_fields" style="display:none;">
         <div class="mb-3">
           <label for="vehicle_type" class="form-label">交通工具</label>
           <select class="form-select" id="vehicle_type" name="vehicle_type">
             <option value="" disabled selected>選擇交通工具</option>
             <option value="car">Car</option>
             <option value="motorcycle">Motorcycle</option>
           </select>
         </div>
       </div>


       <!-- Submit & link -->
       <div class="d-grid gap-2 mb-3">
         <button type="submit" class="btn btn-gold w-100">註冊</button>
         <a href="login.php" class="text-center" style=" color: #fcd34d; " >已經有帳號？前往登入</a>
       </div>


       <!-- 回首頁 -->
       <div class="text-center">
         <a href="index.php"  style=" color: #fcd34d; " >回首頁</a>
       </div>


       <!-- Alert -->
       <div id="msgBox" class="alert d-none" role="alert"></div>
     </form>
   </div>
 </div>


 <script>
 // 顯示額外欄位
 document.getElementById('account_type').addEventListener('change', e => {
   customer_fields.style.display = e.target.value === 'Customer' ? 'block' : 'none';
   deliveryperson_fields.style.display = e.target.value === 'DeliveryPerson' ? 'block' : 'none';
 });


 // 提交表單
 document.getElementById('registerForm').addEventListener('submit', async e => {
   e.preventDefault();
   const formData = new FormData(e.target);
   const msgBox = document.getElementById('msgBox');


   try {
     const res = await fetch('register_process.php', { method: 'POST', body: formData });
     const data = await res.json();
     msgBox.className = 'alert ' + (data.status === 'success' ? 'alert-success' : 'alert-danger') + ' fade show';
     msgBox.textContent = data.message;
     msgBox.classList.remove('d-none');
     if (data.status === 'success') {
       setTimeout(() => location.href = 'login.php', 3000);
     }
   } catch (err) {
     msgBox.className = 'alert alert-danger fade show';
     msgBox.textContent = 'Unexpected error: ' + err;
     msgBox.classList.remove('d-none');
   }
 });
 </script>
</body>
</html>
