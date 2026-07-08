<?php
session_start();
require_once __DIR__ . "/config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$pageTitle = "طلباتي";

$user_id = $_SESSION["user_id"];

$orders = mysqli_query($conn, "
    SELECT orders.*, products.name AS product_name, products.image, order_items.quantity
    FROM orders
    LEFT JOIN order_items ON orders.id = order_items.order_id
    LEFT JOIN products ON order_items.product_id = products.id
    WHERE orders.user_id = $user_id
    ORDER BY orders.id DESC
");
?>

<?php include __DIR__ . "/includes/header.php"; ?>

<h1>طلباتي</h1>

<table class="data-table">
    <thead>
        <tr>
            <th>رقم الطلب</th>
            <th>الصورة</th>
            <th>اسم المنتج</th>
            <th>الكمية</th>
            <th>السعر</th>
            <th>الحالة</th>
            <th>تاريخ الطلب</th>
        </tr>
    </thead>

    <tbody>
        <?php if (mysqli_num_rows($orders) > 0) { ?>

            <?php while ($order = mysqli_fetch_assoc($orders)) { ?>
                <tr>
                    <td><?php echo $order["id"]; ?></td>

                    <td>
                        <?php if (!empty($order["image"])) { ?>
                            <img class="table-img" src="uploads/<?php echo htmlspecialchars($order["image"]); ?>">
                        <?php } else { ?>
                            لا توجد صورة
                        <?php } ?>
                    </td>

                    <td><?php echo htmlspecialchars($order["product_name"]); ?></td>
                    <td><?php echo htmlspecialchars($order["quantity"]); ?></td>
                    <td><?php echo htmlspecialchars($order["total_price"]); ?></td>
                    <td><?php echo htmlspecialchars($order["status"]); ?></td>
                    <td><?php echo htmlspecialchars($order["created_at"]); ?></td>
                </tr>
            <?php } ?>

        <?php } else { ?>

            <tr>
                <td colspan="7">لا توجد طلبات حاليًا</td>
            </tr>

        <?php } ?>
    </tbody>
</table>

<?php include __DIR__ . "/includes/footer.php"; ?>