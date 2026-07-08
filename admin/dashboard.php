<?php
session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: ../login.php");
    exit();
}

$pageTitle = "لوحة التحكم";
?>

<?php include __DIR__ . "/../includes/header.php"; ?>

<h1>لوحة تحكم المسؤول</h1>

<p>أهلًا <?php echo htmlspecialchars($_SESSION["name"]); ?>، أنت داخل كمسؤول.</p>

<div class="dashboard-cards">
    <a class="card" href="products.php">إدارة المنتجات</a>
    <a class="card" href="categories.php">إدارة التصنيفات</a>
    <a class="card" href="orders.php">إدارة الطلبات</a>
    <a class="card" href="users.php">إدارة المستخدمين</a>
</div>

<?php include __DIR__ . "/../includes/footer.php"; ?>