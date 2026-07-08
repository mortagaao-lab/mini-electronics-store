<?php
session_start();
require_once __DIR__ . "/config/database.php";

$pageTitle = "تسجيل مستخدم جديد";

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "يرجى تعبئة جميع الحقول";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "يرجى إدخال بريد إلكتروني صحيح";
    } elseif (strlen($password) < 6) {
        $error = "كلمة المرور يجب أن تكون 6 أحرف على الأقل";
    } elseif ($password !== $confirm_password) {
        $error = "كلمتا المرور غير متطابقتين";
    } else {

        $check_sql = "SELECT id FROM users WHERE email = ?";
        $check_stmt = mysqli_prepare($conn, $check_sql);
        mysqli_stmt_bind_param($check_stmt, "s", $email);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);

        if (mysqli_stmt_num_rows($check_stmt) > 0) {
            $error = "هذا البريد الإلكتروني مستخدم مسبقًا";
        } else {

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = "user";

            $insert_sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
            $insert_stmt = mysqli_prepare($conn, $insert_sql);
            mysqli_stmt_bind_param($insert_stmt, "ssss", $name, $email, $hashed_password, $role);

            if (mysqli_stmt_execute($insert_stmt)) {
                $success = "تم إنشاء الحساب بنجاح، يمكنك الآن تسجيل الدخول";
            } else {
                $error = "حدث خطأ أثناء إنشاء الحساب";
            }
        }
    }
}
?>

<?php include __DIR__ . "/includes/header.php"; ?>

<div class="form-box">
    <h1>تسجيل مستخدم جديد</h1>

    <?php if (!empty($error)) { ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php } ?>

    <?php if (!empty($success)) { ?>
        <div class="success"><?php echo htmlspecialchars($success); ?></div>
    <?php } ?>

    <form method="POST" action="">
        <label>الاسم</label>
        <input type="text" name="name" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>">

        <label>البريد الإلكتروني</label>
        <input type="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">

        <label>كلمة المرور</label>
        <input type="password" name="password">

        <label>تأكيد كلمة المرور</label>
        <input type="password" name="confirm_password">

        <button type="submit">إنشاء حساب</button>
    </form>
</div>

<?php include __DIR__ . "/includes/footer.php"; ?>