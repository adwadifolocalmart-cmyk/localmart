<?php
session_start();
include 'db_connect.php';
include 'header.php';

// --- Cart Management Logic ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_quantity'])) {
        $product_id = intval($_POST['product_id']);
        $new_quantity = intval($_POST['quantity']);
        if ($new_quantity > 0) {
            $_SESSION['cart'][$product_id] = $new_quantity;
        } else {
            unset($_SESSION['cart'][$product_id]);
        }
    }

    if (isset($_POST['remove_item'])) {
        $product_id = intval($_POST['product_id']);
        unset($_SESSION['cart'][$product_id]);
    }

    header("Location: cart.php");
    exit();
}

$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$total_price = 0;

$products_in_cart = [];
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
    }
}
?>

<style>

.cart-layout {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 2rem;
    margin-top: 2rem;
}

.cart-item {
    display: flex;
    gap: 1rem;
    padding: 1.5rem;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    margin-bottom: 1rem;
    background: white;
}

.cart-item-image {
    width: 120px;
    height: 120px;
    object-fit: cover;
    border-radius: 8px;
}

.cart-item-details {
    flex: 1;
}

.cart-item-actions {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.quantity-input-group {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.quantity-input-group input {
    width: 60px;
    padding: 0.25rem;
}

.button {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    text-align: center;
}

.update-button {
    background: #1e8449;
    color: white;
}

.remove-button {
    background: #dc3545;
    color: white;
}

.checkout-button {
    background: #28a745;
    color: white;
    padding: 1rem;
    font-size: 1.1rem;
    width: 100%;
}

.cart-summary {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 8px;
    height: fit-content;
}

.summary-line {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
}

.total-line {
    font-weight: bold;
    font-size: 1.2rem;
}

.back-to-cart {
    background: #28a745;
    text-align: center;
    padding: 0.75rem;
    margin-top: 1.5rem;
    text-color: white;
}

@media (max-width: 768px) {
    .cart-layout {
        grid-template-columns: 1fr;
    }
    
    .cart-item {
        flex-direction: column;
    }
}
</style>

<main class="container">
    <h2>Your Shopping Cart</h2>

    <?php if (empty($cart)): ?>
        <div class="message-box">
            <p>Your cart is empty.</p>
            <a href="index.php" class="button">Start Shopping</a>
        </div>
    <?php else: ?>



        <?php
// Initialize total price
$total_price = 0.00;

// Ensure $cart and $products_in_cart exist
$cart = $cart ?? [];
$products_in_cart = $products_in_cart ?? [];
?>

<div class="cart-layout">
    <div class="cart-items" id="cart-items">
        <?php if (!empty($cart)): ?>
            <?php foreach ($cart as $product_id => $quantity): ?>
                <?php
                $product = $products_in_cart[$product_id] ?? null;
                if ($product):
                    $quantity = (int)$quantity;
                    $price = isset($product['price']) ? (float)$product['price'] : 0.00;
                    $item_price = $price * $quantity;
                    $total_price += $item_price;

                    // Fallback image if missing
                    $image = !empty($product['image_url']) ? htmlspecialchars($product['image_url']) : 'images/default-product.jpg';
                ?>
                <div class="cart-item">
                    <img src="<?php echo $image; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="cart-item-image">
                    
                    <div class="cart-item-details">
                        <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                        <p>Price: GHS <?php echo number_format($price, 2); ?></p>
                        <p>Total for item: GHS <?php echo number_format($item_price, 2); ?></p>
                    </div>

                    <div class="cart-item-actions">
                        <!-- Update Quantity Form -->
                        <form method="post" action="cart.php" style="margin-bottom: 10px;">
                            <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product_id); ?>">
                            <div class="quantity-input-group">
                                <label for="quantity-<?php echo $product_id; ?>">Quantity:</label>
                                <input type="number" id="quantity-<?php echo $product_id; ?>" name="quantity"
                                       value="<?php echo htmlspecialchars($quantity); ?>"
                                       min="1"
                                       max="<?php echo htmlspecialchars($product['stock_quantity'] ?? 99); ?>"
                                       required>
                                <button type="submit" name="update_quantity" class="button update-button">Update</button>
                            </div>
                        </form>

                        <!-- Remove Item Form -->
                        <form method="post" action="cart.php">
                            <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product_id); ?>">
                            <button type="submit" name="remove_item" class="button remove-button">Remove</button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Your cart is empty.</p>
        <?php endif; ?>
    </div>

    <div class="cart-summary">
        <h3>Cart Total</h3>
        <div class="summary-line">
            <span>Subtotal:</span>
            <span>GHS <?php echo number_format($total_price, 2); ?></span>
        </div>
        <hr>
        <div class="summary-line total-line">
            <span>Total:</span>
            <span>GHS <?php echo number_format($total_price, 2); ?></span>
        </div>

        <?php if ($total_price > 0): ?>
        <form action="checkout.php" method="post">
            <button type="submit" class="button checkout-button">Proceed to Checkout</button>
        </form>
        <?php endif; ?>
        <div class="back-to-cart">
            <a href="product_detail.php" class="button">Continue Shopping</a>
            </div>
    </div>
</div>
    <?php endif; ?>
</main>
<br><br><br><br><br><br><br><br><br><br>
<?php include 'footer.php'; ?>