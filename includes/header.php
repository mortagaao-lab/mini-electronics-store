<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title><?php echo isset($pageTitle) ? $pageTitle : "متجري الإلكتروني"; ?></title>
    <link rel="stylesheet" href="/mini_store/assets/css/style.css">
</head>
<body>

<nav class="navbar">
    <h2>متجري الإلكتروني</h2>

    <div>
        <a href="/mini_store/index.php">الرئيسية</a>

        <?php if (isset($_SESSION["user_id"])) { ?>

            <?php if ($_SESSION["role"] == "admin") { ?>
                <a href="/mini_store/admin/dashboard.php">لوحة التحكم</a>
            <?php } ?>

            <?php if ($_SESSION["role"] == "user") { ?>
                <a href="/mini_store/my_orders.php">طلباتي</a>
            <?php } ?>

            <a href="/mini_store/logout.php">تسجيل خروج</a>

        <?php } else { ?>

            <a href="/mini_store/register.php">تسجيل جديد</a>
            <a href="/mini_store/login.php">تسجيل دخول</a>

        <?php } ?>
    </div>
</nav>

<div class="container">