<?php
session_start();
require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: ../login.php");
    exit();
}

$pageTitle = "إدارة التصنيفات";

$error = "";
$success = "";
$edit_category = null;

/* حذف تصنيف */
if (isset($_GET["delete_id"])) {
    $delete_id = (int) $_GET["delete_id"];

    if ($delete_id > 0) {
        $delete_sql = "DELETE FROM categories WHERE id = ?";
        $delete_stmt = mysqli_prepare($conn, $delete_sql);
        mysqli_stmt_bind_param($delete_stmt, "i", $delete_id);

        if (mysqli_stmt_execute($delete_stmt)) {
            header("Location: categories.php?msg=deleted");
            exit();
        } else {
            $error = "حدث خطأ أثناء حذف التصنيف";
        }
    }
}

/* إضافة أو تعديل */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST["name"]);
    $description = trim($_POST["description"]);

    if (empty($name)) {
        $error = "اسم التصنيف مطلوب";
    } else {

        if (isset($_POST["category_id"]) && !empty($_POST["category_id"])) {

            $category_id = (int) $_POST["category_id"];

            $update_sql = "UPDATE categories SET name = ?, description = ? WHERE id = ?";
            $update_stmt = mysqli_prepare($conn, $update_sql);
            mysqli_stmt_bind_param($update_stmt, "ssi", $name, $description, $category_id);

            if (mysqli_stmt_execute($update_stmt)) {
                header("Location: categories.php?msg=updated");
                exit();
            } else {
                $error = "حدث خطأ أثناء تعديل التصنيف";
            }

        } else {

            $insert_sql = "INSERT INTO categories (name, description) VALUES (?, ?)";
            $insert_stmt = mysqli_prepare($conn, $insert_sql);
            mysqli_stmt_bind_param($insert_stmt, "ss", $name, $description);

            if (mysqli_stmt_execute($insert_stmt)) {
                header("Location: categories.php?msg=added");
                exit();
            } else {
                $error = "حدث خطأ أثناء إضافة التصنيف";
            }
        }
    }
}

/* جلب بيانات التصنيف للتعديل */
if (isset($_GET["edit_id"])) {
    $edit_id = (int) $_GET["edit_id"];

    $edit_sql = "SELECT * FROM categories WHERE id = ?";
    $edit_stmt = mysqli_prepare($conn, $edit_sql);
    mysqli_stmt_bind_param($edit_stmt, "i", $edit_id);
    mysqli_stmt_execute($edit_stmt);

    $edit_result = mysqli_stmt_get_result($edit_stmt);

    if (mysqli_num_rows($edit_result) == 1) {
        $edit_category = mysqli_fetch_assoc($edit_result);
    }
}

/* رسائل النجاح */
if (isset($_GET["msg"])) {
    if ($_GET["msg"] == "added") {
        $success = "تمت إضافة التصنيف بنجاح";
    } elseif ($_GET["msg"] == "updated") {
        $success = "تم تعديل التصنيف بنجاح";
    } elseif ($_GET["msg"] == "deleted") {
        $success = "تم حذف التصنيف بنجاح";
    }
}

/* جلب كل التصنيفات */
$categories_sql = "SELECT * FROM categories ORDER BY id DESC";
$categories_result = mysqli_query($conn, $categories_sql);
?>

<?php include __DIR__ . "/../includes/header.php"; ?>

<h1>إدارة التصنيفات</h1>

<?php if (!empty($error)) { ?>
    <div class="error"><?php echo htmlspecialchars($error); ?></div>
<?php } ?>

<?php if (!empty($success)) { ?>
    <div class="success"><?php echo htmlspecialchars($success); ?></div>
<?php } ?>

<div class="form-box">
    <h2>
        <?php echo $edit_category ? "تعديل التصنيف" : "إضافة تصنيف جديد"; ?>
    </h2>

    <form method="POST" action="">
        <?php if ($edit_category) { ?>
            <input type="hidden" name="category_id" value="<?php echo $edit_category['id']; ?>">
        <?php } ?>

        <label>اسم التصنيف</label>
        <input 
            type="text" 
            name="name"
            value="<?php echo $edit_category ? htmlspecialchars($edit_category['name']) : ''; ?>"
        >

        <label>الوصف</label>
        <input 
            type="text" 
            name="description"
            value="<?php echo $edit_category ? htmlspecialchars($edit_category['description']) : ''; ?>"
        >

        <button type="submit">
            <?php echo $edit_category ? "حفظ التعديل" : "إضافة"; ?>
        </button>

        <?php if ($edit_category) { ?>
            <a class="btn secondary" href="categories.php">إلغاء التعديل</a>
        <?php } ?>
    </form>
</div>

<hr>

<h2>قائمة التصنيفات</h2>

<table class="data-table">
    <thead>
        <tr>
            <th>الرقم</th>
            <th>اسم التصنيف</th>
            <th>الوصف</th>
            <th>تاريخ الإضافة</th>
            <th>العمليات</th>
        </tr>
    </thead>

    <tbody>
        <?php if (mysqli_num_rows($categories_result) > 0) { ?>
            <?php while ($category = mysqli_fetch_assoc($categories_result)) { ?>
                <tr>
                    <td><?php echo $category["id"]; ?></td>
                    <td><?php echo htmlspecialchars($category["name"]); ?></td>
                    <td><?php echo htmlspecialchars($category["description"]); ?></td>
                    <td><?php echo $category["created_at"]; ?></td>
                    <td>
                        <a class="btn edit" href="categories.php?edit_id=<?php echo $category['id']; ?>">تعديل</a>

                        <a 
                            class="btn delete" 
                            href="categories.php?delete_id=<?php echo $category['id']; ?>"
                            onclick="return confirm('هل أنت متأكد من حذف هذا التصنيف؟');"
                        >
                            حذف
                        </a>
                    </td>
                </tr>
            <?php } ?>
        <?php } else { ?>
            <tr>
                <td colspan="5">لا توجد تصنيفات حاليًا</td>
            </tr>
        <?php } ?>
    </tbody>
</table>

<?php include __DIR__ . "/../includes/footer.php"; ?>