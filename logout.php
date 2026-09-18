<?php
session_start(); // 開啟 session
session_unset();  // 清除 session 資料
session_destroy();  // 銷毀 session
header("Location: index.php");  // 登出後重定向回首頁
exit();
?>
