<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="../index.php">
      <i class="bi bi-lightning-charge-fill me-1"></i> NOSH NOW
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
            data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
            aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="../index.php">Home</a></li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
             data-bs-toggle="dropdown" aria-expanded="false">Option</a>
          <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
            <?php if (isset($_SESSION['user_type'])): ?>
              <?php if ($_SESSION['user_type'] === 'Customer'): ?>
                <li><a class="dropdown-item" href="../customer/browse_restaurant.php">瀏覽餐廳</a></li>
                <li><a class="dropdown-item" href="../customer/customer_order_info.php">我的訂單</a></li>
              <?php elseif ($_SESSION['user_type'] === 'DeliveryPerson'): ?>
                <li><a class="dropdown-item" href="../delivery/delivery_available.php">可接外送</a></li>
                <li><a class="dropdown-item" href="../delivery/delivery_history.php">外送紀錄</a></li>
              <?php elseif ($_SESSION['user_type'] === 'RestaurantOwner'): ?>
                <li><a class="dropdown-item" href="../restaurant/restaurant_manage.php">餐廳管理</a></li>
                <li><a class="dropdown-item" href="../restaurant/menu_manage.php">菜單管理</a></li>
              <?php endif; ?>
            <?php endif; ?>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="#">其他選項</a></li>
          </ul>
        </li>
      </ul>
      <ul class="navbar-nav ms-auto">
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle text-white" href="#" id="userMenu" role="button"
             data-bs-toggle="dropdown" aria-expanded="false">
            <?= isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Login / Register' ?>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu">
            <?php if (isset($_SESSION['username'])): ?>
              <li><a class="dropdown-item" href="../logout.php">Logout</a></li>
            <?php else: ?>
              <li><a class="dropdown-item" href="../login.php">Login</a></li>
              <li><a class="dropdown-item" href="../register.php">Register</a></li>
            <?php endif; ?>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>
