<?php
session_start();
require_once __DIR__ . "/config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $user_id = $_SESSION["user_id"];
    $product_id = (int) $_POST["product_id"];

    $product_result = mysqli_query($conn, "SELECT * FROM products WHERE id = $product_id");

    if (mysqli_num_rows($product_result) == 1) {

        $product = mysqli_fetch_assoc($product_result);

        if ($product["quantity"] > 0) {

            $price = $product["price"];

            mysqli_query($conn, "INSERT INTO orders (user_id, total_price, status) VALUES ($user_id, $price, 'pending')");

            $order_id = mysqli_insert_id($conn);

            mysqli_query($conn, "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES ($order_id, $product_id, 1, $price)");

            mysqli_query($conn, "UPDATE products SET quantity = quantity - 1 WHERE id = $product_id");

            header("Location: index.php?msg=ordered");
            exit();

        } else {
            header("Location: index.php?msg=out");
            exit();
        }
    }
}

header("Location: index.php");
exit();
?>