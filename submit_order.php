<?php
session_start();
include 'db_connect.php';

// Sanitize and validate input
// A more robust application would use more advanced validation, but this is a good start.
$name = isset($_POST['name']) ? htmlspecialchars(trim($_POST['name'])) : '';
$address = isset($_POST['address']) ? htmlspecialchars(trim($_POST['address'])) : '';
$city = isset($_POST['city']) ? htmlspecialchars(trim($_POST['city'])) : '';
$mobile_money_number = isset($_POST['mobile_money_number']) ? htmlspecialchars(trim($_POST['mobile_money_number'])) : '';

// Ensure the user is logged in and the cart is not empty before processing
if (!isset($_SESSION['user_id']) || empty($_SESSION['cart'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$cart = $_SESSION['cart'];

// Start a database transaction
// This ensures that either ALL database operations succeed, or none of them do.
// If any part of the order fails (e.g., inserting an item), the entire order is rolled back.
$conn->begin_transaction();

try {
    // 1. Calculate the total price based on the current cart data
    $total_price = 0;
    foreach ($cart as $product_id => $quantity) {
        // Fetch the current price from the database to avoid manipulation
        $sql_price = "SELECT price FROM products WHERE id = ?";
        $stmt_price = $conn->prepare($sql_price);
        $stmt_price->bind_param("i", $product_id);
        $stmt_price->execute();
        $result_price = $stmt_price->get_result();
        $product = $result_price->fetch_assoc();

        if ($product) {
            $total_price += $product['price'] * $quantity;
        } else {
            // Handle case where a product in the cart doesn't exist anymore
            throw new Exception("One or more products in the cart are no longer available.");
        }
    }

    // 2. Insert the main order into the `orders` table
    $sql_order = "INSERT INTO orders (user_id, total_price, shipping_address, mobile_money_number, status) VALUES (?, ?, ?, ?, 'Pending')";
    $stmt_order = $conn->prepare($sql_order);
    $stmt_order->bind_param("idss", $user_id, $total_price, $address, $mobile_money_number);
    $stmt_order->execute();
    $order_id = $stmt_order->insert_id;

    // 3. Insert each cart item into the `order_items` table
    $sql_item = "INSERT INTO order_items (order_id, product_id, quantity, price_at_purchase) VALUES (?, ?, ?, ?)";
    $stmt_item = $conn->prepare($sql_item);

    foreach ($cart as $product_id => $quantity) {
        // Fetch the price at the time of purchase to save in the order details
        $sql_price = "SELECT price FROM products WHERE id = ?";
        $stmt_price = $conn->prepare($sql_price);
        $stmt_price->bind_param("i", $product_id);
        $stmt_price->execute();
        $result_price = $stmt_price->get_result();
        $product = $result_price->fetch_assoc();

        if ($product) {
            $price_at_purchase = $product['price'];
            $stmt_item->bind_param("iidi", $order_id, $product_id, $quantity, $price_at_purchase);
            $stmt_item->execute();
        } else {
            // Rollback if a product is not found
            throw new Exception("Product ID " . $product_id . " not found.");
        }
    }

    // 4. Commit the transaction if all insertions were successful
    $conn->commit();
    
    // 5. Clear the cart and redirect to the success page
    unset($_SESSION['cart']);
    header("Location: order_success.php?id=" . $order_id);
    exit();

} catch (Exception $e) {
    // If any step failed, rollback the transaction and redirect with an error message
    $conn->rollback();
    // In a real application, you would log this error to a file
    error_log("Order Submission Error: " . $e->getMessage());

    $_SESSION['error_message'] = "There was an error processing your order. Please try again.";
    header("Location: checkout.php");
    exit();
}

$conn->close();
?>