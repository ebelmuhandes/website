<?php
// admin/logout.php - تسجيل الخروج
require_once '../config.php';

session_destroy();
redirect('login.php');
?>
