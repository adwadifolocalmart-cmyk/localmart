<?php
session_start();
include 'db_connect.php';
include 'header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Redirect if cart is empty
if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit();
}

// Initialize variables
$error = '';
$success = '';
$cart = $_SESSION['cart'];
$total_price = 0;
$products_in_cart = [];

// Get cart products from database
if (!empty($cart)) {
    $product_ids = array_keys($cart);
    $placeholders = str_repeat('?,', count($product_ids) - 1) . '?';
    $sql = "SELECT id, name, price, image_url, stock_quantity FROM products WHERE id IN ($placeholders)";
    $stmt = $conn->prepare($sql);
    $types = str_repeat('i', count($product_ids));
    $stmt->bind_param($types, ...$product_ids);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($product = $result->fetch_assoc()) {
        $products_in_cart[$product['id']] = $product;
        $quantity = $cart[$product['id']];
        $total_price += $product['price'] * $quantity;
    }
}

// Process checkout form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $payment_method = $_POST['payment_method'] ?? '';
    
    // Basic validation
    if (empty($full_name) || empty($email) || empty($phone) || empty($address) || empty($city)) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Get user ID if logged in, otherwise use 0 for guest
            $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
            
            // Insert order - FIXED: Match the correct number of parameters
            $order_sql = "INSERT INTO orders (customer_id, total_amount, payment_method, full_name, address, city) 
                         VALUES (?, ?, ?, ?, ?, ?)";
            $order_stmt = $conn->prepare($order_sql);
            $order_stmt->bind_param("idssss", $user_id, $total_price, $payment_method, $full_name, $address, $city);
            $order_stmt->execute();
            $order_id = $conn->insert_id;
            
            // Insert order items and update product stock
            foreach ($cart as $product_id => $quantity) {
                $product = $products_in_cart[$product_id];
                
                // Check stock availability
                if ($product['stock_quantity'] < $quantity) {
                    throw new Exception("Sorry, '{$product['name']}' only has {$product['stock_quantity']} items in stock.");
                }
                
                // Insert order item
                $item_sql = "INSERT INTO order_items (order_id, product_id, quantity, price) 
                            VALUES (?, ?, ?, ?)";
                $item_stmt = $conn->prepare($item_sql);
                $item_stmt->bind_param("iiid", $order_id, $product_id, $quantity, $product['price']);
                $item_stmt->execute();
                
                // Update product stock
                $update_sql = "UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("ii", $quantity, $product_id);
                $update_stmt->execute();
            }
            
            // Commit transaction
            $conn->commit();
            
            // Clear cart and show success
            unset($_SESSION['cart']);
            $success = "Order placed successfully! Your order ID is #{$order_id}";
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $error = $e->getMessage();
        }
    }
}
?>

<main class="container">
    <h2>Checkout</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-error">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($success); ?>
            <p><a href="index.php" class="button">Continue Shopping</a></p>
        </div>
    <?php else: ?>
    
    <div class="checkout-layout">
        <div class="checkout-form">
            <h3>Shipping Information</h3>
            <form method="post" action="checkout.php">
                <div class="form-group">
                    <label for="full_name">Full Name *</label>
                    <input type="text" id="full_name" name="full_name" 
                           value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>" 
                           required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                           required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number *</label>
                    <input type="tel" id="phone" name="phone" 
                           value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" 
                           required>
                </div>
                
                <div class="form-group">
                    <label for="address">Delivery Address *</label>
                    <textarea id="address" name="address" required><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="city">City *</label>
                    <input type="text" id="city" name="city" 
                           value="<?php echo isset($_POST['city']) ? htmlspecialchars($_POST['city']) : ''; ?>" 
                           required>
                </div>
                
                <div class="form-group">
                    <label for="payment_method">Payment Method *</label>
                    <select id="payment_method" name="payment_method" required>
                        <option value="">Select Payment Method</option>
                        <option value="mobile_money" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] == 'mobile_money') ? 'selected' : ''; ?>>Mobile Money</option>
                        <option value="cash_on_delivery" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] == 'cash_on_delivery') ? 'selected' : ''; ?>>Cash on Delivery</option>
                        <option value="bank_transfer" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] == 'bank_transfer') ? 'selected' : ''; ?>>Bank Transfer</option>
                    </select>
                </div>
                
                <button type="submit" class="button checkout-button">Place Order</button>
            </form>
        </div>
        
        <div class="order-summary">
            <h3>Order Summary</h3>
            <div class="summary-items">
                <?php foreach ($cart as $product_id => $quantity): ?>
                    <?php if (isset($products_in_cart[$product_id])): ?>
                        <?php $product = $products_in_cart[$product_id]; ?>
                        <div class="summary-item">
                            <span class="item-name"><?php echo htmlspecialchars($product['name']); ?> × <?php echo $quantity; ?></span>
                            <span class="item-price">GHS <?php echo number_format($product['price'] * $quantity, 2); ?></span>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            
            <div class="summary-total">
                <hr>
                <div class="total-line">
                    <strong>Total: GHS <?php echo number_format($total_price, 2); ?></strong>
                </div>
            </div>
            
            <div class="back-to-cart">
                <a href="cart.php" class="button">← Back to Cart</a>
            </div>
        </div>
    </div>
    
    <?php endif; ?>
</main>

<style>
.checkout-layout {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 2rem;
    margin-top: 2rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: bold;
}

.form-group input,
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 1rem;
}

.form-group textarea {
    height: 80px;
    resize: vertical;
}

.order-summary {
    background: #f9f9f9;
    padding: 1.5rem;
    border-radius: 8px;
    height: fit-content;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
}

.summary-total {
    margin-top: 1rem;
}

.total-line {
    display: flex;
    justify-content: space-between;
    font-size: 1.2rem;
}

.back-to-cart {
    background: #28a745;
    text-align: center;
    padding: 0.75rem;
    margin-top: 1.5rem;
    text-color: white;
}

.alert {
    padding: 1rem;
    border-radius: 4px;
    margin-bottom: 1rem;
}

.alert-error {
    background: #fee;
    border: 1px solid #fcc;
    color: #c33;
}

.alert-success {
    background: #efe;
    border: 1px solid #cfc;
    color: #363;
}

.checkout-button {
    width: 100%;
    padding: 1rem;
    font-size: 1.1rem;
    background: #28a745;
    color: white;
}

.checkout-button:hover {
    background: #218838;
}

@media (max-width: 768px) {
    .checkout-layout {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include 'footer.php'; ?>


