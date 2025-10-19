<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_id = intval($_POST['product_id']);

    // Fetch product details from the database
    $sql = "SELECT name, price, image_url, stock_quantity FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    if ($product) {
        // If the product is already in the cart, update the quantity
        if (isset($_SESSION['cart'][$product_id])) {
        } else {
            // Add the product and its details to the cart
            $_SESSION['cart'][$product_id] = [
                'name' => $product['name'],
                'price' => $product['price'],
                'image' => $product['image_url'],
                'quantity' => $quantity
            ];
        }
    }
}

header('Location: cart.php');
exit();
?>