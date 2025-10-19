<?php
session_start();
include 'db_connect.php';
include 'header.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
?>

<main class="container" style="margin-bottom: 45vh">
    <h2>Your Order History</h2>

    <?php
    // Fetch all orders for the logged-in user
    $sql = "SELECT id, created_at, total_price, status FROM orders WHERE user_id = ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0):
        while ($order = $result->fetch_assoc()):
    ?>
    <div class="order-card">
        <div class="order-header">
            <h3>Order #<?php echo htmlspecialchars($order['id']); ?></h3>
            <p>Date: <?php echo date("F j, Y", strtotime($order['created_at'])); ?></p>
        </div>
        <div class="order-details">
            <p>Total: <span class="order-price">GHS <?php echo number_format($order['total_price'], 2); ?></span></p>
            <p>Status: <span class="order-status"><?php echo htmlspecialchars($order['status']); ?></span></p>
        </div>

        <h4>Items:</h4>
        <ul class="order-items-list">
            <?php
            // Fetch items for this specific order
            $sql_items = "SELECT p.name, oi.quantity, oi.price_at_purchase FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?";
            $stmt_items = $conn->prepare($sql_items);
            $stmt_items->bind_param("i", $order['id']);
            $stmt_items->execute();
            $result_items = $stmt_items->get_result();
            while ($item = $result_items->fetch_assoc()):
            ?>
            <li>
                <?php echo htmlspecialchars($item['name']); ?>
                (x<?php echo htmlspecialchars($item['quantity']); ?>)
                - GHS <?php echo number_format($item['price_at_purchase'], 2); ?> each
            </li>
            <?php endwhile; ?>
        </ul>
    </div>
    <?php endwhile;
    else:
    ?>
    <p>You have not placed any orders yet.</p>
    <?php endif; ?>
</main>

<?php include 'footer.php'; ?>