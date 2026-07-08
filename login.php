<?php
session_start();
require_once __DIR__ . "/config/database.php";

$pageTitle = "تسجيل الدخول";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if (empty($email) || empty($password)) {
        $error = "يرجى إدخال البريد الإلكتروني وكلمة المرور";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "يرجى إدخال بريد إلكتروني صحيح";
    } else {

        $sql = "SELECT id, name, email, password, role FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) == 1) {

            $user = mysqli_fetch_assoc($result);

            if (password_verify($password, $user["password"])) {

                $_SESSION["user_id"] = $user["id"];
                $_SESSION["name"] = $user["name"];
                $_SESSION["email"] = $user["email"];
                $_SESSION["role"] = $user["role"];

                setcookie("user_email", $user["email"], time() + (86400 * 7), "/");

                if ($user["role"] == "admin") {
                    header("Location: admin/dashboard.php");
                    exit();
                } else {
                    header("Location: index.php");
                    exit();
                }

            } else {
                $error = "كلمة المرور غير صحيحة";
            }

        } else {
            $error = "البريد الإلكتروني غير موجود";
        }
    }
}
?>

<?php include __DIR__ . "/includes/header.php"; ?>

<div class="form-box">
    <h1>تسجيل الدخول</h1>

    <?php if (!empty($error)) { ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php } ?>

    <form method="POST" action="">
        <label>البريد الإلكتروني</label>
        <input 
            type="email" 
            name="email"
            value="<?php echo isset($_COOKIE['user_email']) ? htmlspecialchars($_COOKIE['user_email']) : ''; ?>"
        >

        <label>كلمة المرور</label>
        <input type="password" name="password">

        <button type="submit">دخول</button>
    </form>
</div>

<?php include __DIR__ . "/includes/footer.php"; ?>