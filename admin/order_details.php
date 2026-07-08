<?php
session_start();
require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: ../login.php");
    exit();
}

$pageTitle = "تفاصيل الطلب";

$order_id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

$order = mysqli_query($conn, "
    SELECT orders.*, users.name AS user_name, users.email
    FROM orders
    LEFT JOIN users ON orders.user_id = users.id
    WHERE orders.id = $order_id
");

if (mysqli_num_rows($order) == 0) {
    header("Location: orders.php");
    exit();
}

$order_data = mysqli_fetch_assoc($order);

$items = mysqli_query($conn, "
    SELECT order_items.*, products.name AS product_name, products.image
    FROM order_items
    LEFT JOIN products ON order_items.product_id = products.id
    WHERE order_items.order_id = $order_id
");
?>

<?php include __DIR__ . "/../includes/header.php"; ?>

<h1>تفاصيل الطلب رقم <?php echo $order_data["id"]; ?></h1>

<p>
    <strong>اسم المستخدم:</strong>
    <?php echo htmlspecialchars($order_data["user_name"]); ?>
</p>

<p>
    <strong>البريد الإلكتروني:</strong>
    <?php echo htmlspecialchars($order_data["email"]); ?>
</p>

<p>
    <strong>حالة الطلب:</strong>
    <?php echo htmlspecialchars($order_data["status"]); ?>
</p>

<p>
    <strong>السعر الكلي:</strong>
    <?php echo htmlspecialchars($order_data["total_price"]); ?>
</p>

<table class="data-table">
    <thead>
        <tr>
            <th>الصورة</th>
            <th>اسم المنتج</th>
            <th>الكمية</th>
            <th>السعر</th>
        </tr>
    </thead>

    <tbody>
        <?php while ($item = mysqli_fetch_assoc($items)) { ?>
            <tr>
                <td>
                    <?php if (!empty($item["image"])) { ?>
                        <img class="table-img" src="../uploads/<?php echo htmlspecialchars($item["image"]); ?>">
                    <?php } else { ?>
                        لا توجد صورة
                    <?php } ?>
                </td>

                <td><?php echo htmlspecialchars($item["product_name"]); ?></td>
                <td><?php echo htmlspecialchars($item["quantity"]); ?></td>
                <td><?php echo htmlspecialchars($item["price"]); ?></td>
            </tr>
        <?php } ?>
    </tbody>
</table>

<br>

<a class="btn secondary" href="orders.php">رجوع للطلبات</a>

<?php include __DIR__ . "/../includes/footer.php"; ?>