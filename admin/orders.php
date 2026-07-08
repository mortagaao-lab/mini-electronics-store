<?php
session_start();
require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: ../login.php");
    exit();
}

$pageTitle = "إدارة الطلبات";

$success = "";

if (isset($_GET["complete_id"])) {
    $id = (int) $_GET["complete_id"];

    mysqli_query($conn, "UPDATE orders SET status = 'completed' WHERE id = $id");

    header("Location: orders.php?msg=completed");
    exit();
}

if (isset($_GET["cancel_id"])) {
    $id = (int) $_GET["cancel_id"];

    mysqli_query($conn, "UPDATE orders SET status = 'cancelled' WHERE id = $id");

    header("Location: orders.php?msg=cancelled");
    exit();
}

if (isset($_GET["delete_id"])) {
    $id = (int) $_GET["delete_id"];

    mysqli_query($conn, "DELETE FROM orders WHERE id = $id");

    header("Location: orders.php?msg=deleted");
    exit();
}

if (isset($_GET["msg"])) {
    if ($_GET["msg"] == "completed") {
        $success = "تم تغيير حالة الطلب إلى مكتمل";
    } elseif ($_GET["msg"] == "cancelled") {
        $success = "تم إلغاء الطلب";
    } elseif ($_GET["msg"] == "deleted") {
        $success = "تم حذف الطلب";
    }
}

$orders = mysqli_query($conn, "
    SELECT orders.*, users.name AS user_name, users.email
    FROM orders
    LEFT JOIN users ON orders.user_id = users.id
    ORDER BY orders.id DESC
");
?>

<?php include __DIR__ . "/../includes/header.php"; ?>

<h1>إدارة الطلبات</h1>

<?php if (!empty($success)) { ?>
    <div class="success"><?php echo htmlspecialchars($success); ?></div>
<?php } ?>

<table class="data-table">
    <thead>
        <tr>
            <th>رقم الطلب</th>
            <th>اسم المستخدم</th>
            <th>البريد الإلكتروني</th>
            <th>السعر الكلي</th>
            <th>الحالة</th>
            <th>تاريخ الطلب</th>
            <th>العمليات</th>
        </tr>
    </thead>

    <tbody>
        <?php if (mysqli_num_rows($orders) > 0) { ?>

            <?php while ($order = mysqli_fetch_assoc($orders)) { ?>
                <tr>
                    <td><?php echo $order["id"]; ?></td>
                    <td><?php echo htmlspecialchars($order["user_name"]); ?></td>
                    <td><?php echo htmlspecialchars($order["email"]); ?></td>
                    <td><?php echo htmlspecialchars($order["total_price"]); ?></td>
                    <td><?php echo htmlspecialchars($order["status"]); ?></td>
                    <td><?php echo htmlspecialchars($order["created_at"]); ?></td>

                    <td>
    <a class="btn edit" href="order_details.php?id=<?php echo $order["id"]; ?>">تفاصيل</a>

    <a class="btn edit" href="orders.php?complete_id=<?php echo $order["id"]; ?>">مكتمل</a>

    <a class="btn secondary" href="orders.php?cancel_id=<?php echo $order["id"]; ?>">إلغاء</a>

    <a class="btn delete"
       href="orders.php?delete_id=<?php echo $order["id"]; ?>"
       onclick="return confirm('هل أنت متأكد من حذف الطلب؟');">
        حذف
    </a>
</td>
                </tr>
            <?php } ?>

        <?php } else { ?>
            <tr>
                <td colspan="7">لا توجد طلبات حاليًا</td>
            </tr>
        <?php } ?>
    </tbody>
</table>

<?php include __DIR__ . "/../includes/footer.php"; ?>