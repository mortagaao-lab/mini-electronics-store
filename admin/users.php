<?php
session_start();
require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: ../login.php");
    exit();
}

$pageTitle = "إدارة المستخدمين";

$error = "";
$success = "";

if (isset($_GET["delete_id"])) {
    $id = (int) $_GET["delete_id"];

    if ($id == $_SESSION["user_id"]) {
        $error = "لا يمكنك حذف حسابك الحالي";
    } else {
        mysqli_query($conn, "DELETE FROM users WHERE id = $id");
        header("Location: users.php?msg=deleted");
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = (int) $_POST["user_id"];
    $role = $_POST["role"];

    if ($role == "admin" || $role == "user") {
        $sql = "UPDATE users SET role = '$role' WHERE id = $user_id";
        mysqli_query($conn, $sql);

        header("Location: users.php?msg=updated");
        exit();
    } else {
        $error = "نوع الصلاحية غير صحيح";
    }
}

if (isset($_GET["msg"])) {
    if ($_GET["msg"] == "updated") {
        $success = "تم تعديل صلاحية المستخدم";
    } elseif ($_GET["msg"] == "deleted") {
        $success = "تم حذف المستخدم";
    }
}

$users = mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC");
?>

<?php include __DIR__ . "/../includes/header.php"; ?>

<h1>إدارة المستخدمين</h1>

<?php if (!empty($error)) { ?>
    <div class="error"><?php echo htmlspecialchars($error); ?></div>
<?php } ?>

<?php if (!empty($success)) { ?>
    <div class="success"><?php echo htmlspecialchars($success); ?></div>
<?php } ?>

<table class="data-table">
    <thead>
        <tr>
            <th>الرقم</th>
            <th>الاسم</th>
            <th>البريد الإلكتروني</th>
            <th>الصلاحية</th>
            <th>تاريخ التسجيل</th>
            <th>العمليات</th>
        </tr>
    </thead>

    <tbody>
        <?php if (mysqli_num_rows($users) > 0) { ?>

            <?php while ($user = mysqli_fetch_assoc($users)) { ?>
                <tr>
                    <td><?php echo $user["id"]; ?></td>
                    <td><?php echo htmlspecialchars($user["name"]); ?></td>
                    <td><?php echo htmlspecialchars($user["email"]); ?></td>

                    <td>
                        <form method="POST">
                            <input type="hidden" name="user_id" value="<?php echo $user["id"]; ?>">

                            <select name="role">
                                <option value="user" <?php if ($user["role"] == "user") echo "selected"; ?>>
                                    مستخدم
                                </option>

                                <option value="admin" <?php if ($user["role"] == "admin") echo "selected"; ?>>
                                    مسؤول
                                </option>
                            </select>

                            <button class="small-btn" type="submit">حفظ</button>
                        </form>
                    </td>

                    <td><?php echo htmlspecialchars($user["created_at"]); ?></td>

                    <td>
                        <?php if ($user["id"] != $_SESSION["user_id"]) { ?>
                            <a class="btn delete"
                               href="users.php?delete_id=<?php echo $user["id"]; ?>"
                               onclick="return confirm('هل أنت متأكد من حذف المستخدم؟');">
                                حذف
                            </a>
                        <?php } else { ?>
                            الحساب الحالي
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>

        <?php } else { ?>
            <tr>
                <td colspan="6">لا يوجد مستخدمين حاليًا</td>
            </tr>
        <?php } ?>
    </tbody>
</table>

<?php include __DIR__ . "/../includes/footer.php"; ?>