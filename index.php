<?php
session_start();
require_once __DIR__ . "/config/database.php";

$pageTitle = "الصفحة الرئيسية";

$success = "";
$error = "";

if (isset($_GET["msg"])) {
    if ($_GET["msg"] == "ordered") {
        $success = "تم إرسال الطلب بنجاح";
    } elseif ($_GET["msg"] == "out") {
        $error = "المنتج غير متوفر حاليًا";
    }
}

$products = mysqli_query($conn, "
    SELECT products.*, categories.name AS category_name
    FROM products
    LEFT JOIN categories ON products.category_id = categories.id
    ORDER BY products.id DESC
");
?>

<?php include __DIR__ . "/includes/header.php"; ?>

<h1>المنتجات المتوفرة</h1>

<p>يمكنك تصفح المنتجات الموجودة في المتجر.</p>

<?php if (!empty($success)) { ?>
    <div class="success"><?php echo htmlspecialchars($success); ?></div>
<?php } ?>

<?php if (!empty($error)) { ?>
    <div class="error"><?php echo htmlspecialchars($error); ?></div>
<?php } ?>

<div class="products-grid">

    <?php if (mysqli_num_rows($products) > 0) { ?>

        <?php while ($product = mysqli_fetch_assoc($products)) { ?>

            <div class="product-card">

                <?php if (!empty($product["image"])) { ?>
                    <img src="uploads/<?php echo htmlspecialchars($product["image"]); ?>">
                <?php } ?>

                <h3><?php echo htmlspecialchars($product["name"]); ?></h3>

                <p><?php echo htmlspecialchars($product["description"]); ?></p>

                <p>
                    <strong>التصنيف:</strong>
                    <?php echo htmlspecialchars($product["category_name"]); ?>
                </p>

                <p>
                    <strong>السعر:</strong>
                    <?php echo htmlspecialchars($product["price"]); ?>
                </p>

                <p>
                    <strong>الكمية المتوفرة:</strong>
                    <?php echo htmlspecialchars($product["quantity"]); ?>
                </p>

                <?php if (isset($_SESSION["user_id"]) && $_SESSION["role"] == "user") { ?>

                    <?php if ($product["quantity"] > 0) { ?>
                        <form method="POST" action="order.php">
                            <input type="hidden" name="product_id" value="<?php echo $product["id"]; ?>">
                            <button class="order-btn" type="submit">طلب المنتج</button>
                        </form>
                    <?php } else { ?>
                        <p class="out-text">غير متوفر</p>
                    <?php } ?>

                <?php } elseif (!isset($_SESSION["user_id"])) { ?>

                    <a class="login-link" href="login.php">سجل الدخول للطلب</a>

                <?php } ?>

            </div>

        <?php } ?>

    <?php } else { ?>

        <p>لا توجد منتجات حاليًا.</p>

    <?php } ?>

</div>

<?php include __DIR__ . "/includes/footer.php"; ?>