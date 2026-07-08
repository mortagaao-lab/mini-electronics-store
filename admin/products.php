<?php
session_start();
require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: ../login.php");
    exit();
}

$pageTitle = "إدارة المنتجات";

$error = "";
$success = "";
$edit_product = null;

if (isset($_GET["delete_id"])) {
    $id = (int) $_GET["delete_id"];

    mysqli_query($conn, "DELETE FROM products WHERE id = $id");

    header("Location: products.php?msg=deleted");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $product_id = isset($_POST["product_id"]) ? (int) $_POST["product_id"] : 0;

    $name = trim($_POST["name"]);
    $description = trim($_POST["description"]);
    $category_id = (int) $_POST["category_id"];
    $price = $_POST["price"];
    $quantity = (int) $_POST["quantity"];

    $old_image = isset($_POST["old_image"]) ? $_POST["old_image"] : "";
    $image_name = $old_image;

    if (empty($name) || empty($description) || empty($price)) {
        $error = "يرجى تعبئة جميع الحقول";
    } elseif ($category_id <= 0) {
        $error = "يرجى اختيار التصنيف";
    } elseif (!is_numeric($price) || $price <= 0) {
        $error = "السعر يجب أن يكون رقم صحيح";
    } elseif ($quantity < 0) {
        $error = "الكمية لا يمكن أن تكون أقل من صفر";
    } else {

        if (!empty($_FILES["image"]["name"])) {

            $file_name = $_FILES["image"]["name"];
            $file_tmp = $_FILES["image"]["tmp_name"];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            $allowed = ["jpg", "jpeg", "png", "gif"];

            if (!in_array($file_ext, $allowed)) {
                $error = "نوع الصورة غير مسموح";
            } else {
                $image_name = time() . "_" . $file_name;
                move_uploaded_file($file_tmp, __DIR__ . "/../uploads/" . $image_name);
            }

        } elseif ($product_id == 0) {
            $error = "يرجى اختيار صورة للمنتج";
        }

        if (empty($error)) {

            $name = mysqli_real_escape_string($conn, $name);
            $description = mysqli_real_escape_string($conn, $description);
            $image_name = mysqli_real_escape_string($conn, $image_name);
            $price = (float) $price;

            if ($product_id > 0) {

                $sql = "UPDATE products SET
                        category_id = $category_id,
                        name = '$name',
                        description = '$description',
                        price = $price,
                        quantity = $quantity,
                        image = '$image_name'
                        WHERE id = $product_id";

                mysqli_query($conn, $sql);

                header("Location: products.php?msg=updated");
                exit();

            } else {

                $sql = "INSERT INTO products 
                        (category_id, name, description, price, quantity, image)
                        VALUES 
                        ($category_id, '$name', '$description', $price, $quantity, '$image_name')";

                mysqli_query($conn, $sql);

                header("Location: products.php?msg=added");
                exit();
            }
        }
    }
}

if (isset($_GET["edit_id"])) {
    $edit_id = (int) $_GET["edit_id"];

    $result = mysqli_query($conn, "SELECT * FROM products WHERE id = $edit_id");

    if (mysqli_num_rows($result) == 1) {
        $edit_product = mysqli_fetch_assoc($result);
    }
}

if (isset($_GET["msg"])) {
    if ($_GET["msg"] == "added") {
        $success = "تمت إضافة المنتج بنجاح";
    } elseif ($_GET["msg"] == "updated") {
        $success = "تم تعديل المنتج بنجاح";
    } elseif ($_GET["msg"] == "deleted") {
        $success = "تم حذف المنتج بنجاح";
    }
}

$categories = mysqli_query($conn, "SELECT * FROM categories");

$products = mysqli_query($conn, "
    SELECT products.*, categories.name AS category_name
    FROM products
    LEFT JOIN categories ON products.category_id = categories.id
    ORDER BY products.id DESC
");
?>

<?php include __DIR__ . "/../includes/header.php"; ?>

<h1>إدارة المنتجات</h1>

<?php if (!empty($error)) { ?>
    <div class="error"><?php echo htmlspecialchars($error); ?></div>
<?php } ?>

<?php if (!empty($success)) { ?>
    <div class="success"><?php echo htmlspecialchars($success); ?></div>
<?php } ?>

<div class="form-box wide-form">

    <h2><?php echo $edit_product ? "تعديل المنتج" : "إضافة منتج جديد"; ?></h2>

    <form method="POST" enctype="multipart/form-data">

        <?php if ($edit_product) { ?>
            <input type="hidden" name="product_id" value="<?php echo $edit_product["id"]; ?>">
            <input type="hidden" name="old_image" value="<?php echo htmlspecialchars($edit_product["image"]); ?>">
        <?php } ?>

        <label>اسم المنتج</label>
        <input type="text" name="name"
               value="<?php echo $edit_product ? htmlspecialchars($edit_product["name"]) : ""; ?>">

        <label>التصنيف</label>
        <select name="category_id">
            <option value="">اختاري التصنيف</option>

            <?php while ($cat = mysqli_fetch_assoc($categories)) { ?>
                <option value="<?php echo $cat["id"]; ?>"
                    <?php
                    if ($edit_product && $edit_product["category_id"] == $cat["id"]) {
                        echo "selected";
                    }
                    ?>
                >
                    <?php echo htmlspecialchars($cat["name"]); ?>
                </option>
            <?php } ?>
        </select>

        <label>الوصف</label>
        <textarea name="description" rows="4"><?php echo $edit_product ? htmlspecialchars($edit_product["description"]) : ""; ?></textarea>

        <label>السعر</label>
        <input type="number" step="0.01" name="price"
               value="<?php echo $edit_product ? htmlspecialchars($edit_product["price"]) : ""; ?>">

        <label>الكمية</label>
        <input type="number" name="quantity"
               value="<?php echo $edit_product ? htmlspecialchars($edit_product["quantity"]) : ""; ?>">

        <label>صورة المنتج</label>
        <input type="file" name="image">

        <?php if ($edit_product && !empty($edit_product["image"])) { ?>
            <p>الصورة الحالية:</p>
            <img class="product-preview"
                 src="../uploads/<?php echo htmlspecialchars($edit_product["image"]); ?>">
        <?php } ?>

        <button type="submit">
            <?php echo $edit_product ? "حفظ التعديل" : "إضافة المنتج"; ?>
        </button>

        <?php if ($edit_product) { ?>
            <a class="btn secondary" href="products.php">إلغاء التعديل</a>
        <?php } ?>

    </form>
</div>

<hr>

<h2>قائمة المنتجات</h2>

<table class="data-table">
    <thead>
        <tr>
            <th>الصورة</th>
            <th>اسم المنتج</th>
            <th>التصنيف</th>
            <th>السعر</th>
            <th>الكمية</th>
            <th>العمليات</th>
        </tr>
    </thead>

    <tbody>
        <?php if (mysqli_num_rows($products) > 0) { ?>

            <?php while ($product = mysqli_fetch_assoc($products)) { ?>
                <tr>
                    <td>
                        <?php if (!empty($product["image"])) { ?>
                            <img class="table-img"
                                 src="../uploads/<?php echo htmlspecialchars($product["image"]); ?>">
                        <?php } else { ?>
                            لا توجد صورة
                        <?php } ?>
                    </td>

                    <td><?php echo htmlspecialchars($product["name"]); ?></td>
                    <td><?php echo htmlspecialchars($product["category_name"]); ?></td>
                    <td><?php echo htmlspecialchars($product["price"]); ?></td>
                    <td><?php echo htmlspecialchars($product["quantity"]); ?></td>

                    <td>
                        <a class="btn edit" href="products.php?edit_id=<?php echo $product["id"]; ?>">تعديل</a>

                        <a class="btn delete"
                           href="products.php?delete_id=<?php echo $product["id"]; ?>"
                           onclick="return confirm('هل أنت متأكد من الحذف؟');">
                            حذف
                        </a>
                    </td>
                </tr>
            <?php } ?>

        <?php } else { ?>
            <tr>
                <td colspan="6">لا توجد منتجات حاليًا</td>
            </tr>
        <?php } ?>
    </tbody>
</table>

<?php include __DIR__ . "/../includes/footer.php"; ?>