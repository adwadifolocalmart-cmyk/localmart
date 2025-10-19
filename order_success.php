<?php
session_start();
include 'db_connect.php';
include 'header.php';

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
?>

<main class="container text-center">
    <h2>Thank You for Your Order!</h2>
    <?php if ($order_id): ?>
        <p>Your order has been successfully placed. Your order number is <strong>#<?php echo $order_id; ?></strong>.</p>
        <p>We will contact you via Mobile Money to confirm payment before processing and shipping your order. Thank you for your patience!</p>
    <?php else: ?>
        <p>Your order has been placed successfully.</p>
    <?php endif; ?>
    <a href="index.php" class="button">Continue Shopping</a>
</main>

<?php include 'footer.php'; ?>