<?php
// product_detail.php
include 'db_connect.php';

// Validate product ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: products.php");
    exit();
}

$product_id = (int)$_GET['id'];

// Fetch product and vendor details
$stmt = $conn->prepare("
    SELECT 
        p.id, p.name, p.description, p.price, p.image_url, p.stock_quantity, p.category,
        u.id AS vendor_id, u.farm_name, u.location
    FROM products p
    JOIN users u ON p.vendor_id = u.id
    WHERE p.id = ? AND p.stock_quantity > 0
");

$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $page_title = "Product Not Found";
    include 'header.php';
    echo "<div class='main-content'><h2 class='page-title'>Sorry, this product could not be found or is currently out of stock.</h2></div>";
    include 'footer.php';
    exit();
}

$product = $result->fetch_assoc();
$page_title = htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8');
include 'header.php';
?>

<style>
    :root {
        --primary-color: #4a7c3a;
        --secondary-color: #2e7d32;
    }

    body, html {
        margin: 0;
        padding: 0;
        width: 100%;
        overflow-x: hidden;
        background-color: #f9fbf8;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .main-content {
        max-width: 1200px;
        margin: 0 auto;
        padding: 120px 1.5rem 2rem;
    }

    .product-detail-container {
        display: flex;
        flex-wrap: wrap;
        gap: 2rem;
        margin-top: 2rem;
    }

    .product-image-gallery {
        flex: 1;
        min-width: 280px;
        text-align: center;
    }

    .product-image-gallery img {
        width: 100%;
        max-width: 400px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    .product-info {
        flex: 1;
        min-width: 280px;
    }

    .product-info h1 {
        font-size: 2.2rem;
        color: var(--primary-color);
        margin-bottom: 1rem;
    }

    .product-info .price {
        font-size: 1.8rem;
        font-weight: bold;
        color: var(--secondary-color);
        margin-bottom: 1rem;
    }

    .product-info .stock {
        font-size: 1rem;
        color: #555;
        margin-bottom: 1rem;
    }

    .product-info .stock.low {
        color: #c0392b;
    }

    .product-info .description {
        margin-bottom: 1.5rem;
        line-height: 1.6;
        color: #444;
    }

    .vendor-card {
        background-color: #f1f8e9;
        border-left: 5px solid var(--primary-color);
        padding: 1rem;
        border-radius: 8px;
        margin-top: 2rem;
    }

    .vendor-card h3 {
        margin-top: 0;
        margin-bottom: 0.5rem;
        color: var(--primary-color);
    }

    .button {
        background-color: var(--primary-color);
        color: white;
        padding: 0.7rem 1.5rem;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.3s ease;
    }

    .button:hover {
        background-color: #2e7d32;
    }

    .page-title {
        text-align: center;
        font-size: 1.6rem;
        color: #c0392b;
        margin-top: 2rem;
    }

    @media (max-width: 768px) {
        .product-detail-container {
            flex-direction: column;
            align-items: center;
        }

        .product-info h1 {
            font-size: 1.8rem;
        }

        .product-info .price {
            font-size: 1.5rem;
        }
    }
</style>

<div class="main-content">
    <div class="product-detail-container">
        <div class="product-image-gallery">
            <img src="<?php echo htmlspecialchars($product['image_url'], ENT_QUOTES, 'UTF-8'); ?>" 
                 alt="<?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>" 
                 onerror="this.onerror=null;this.src='https://placehold.co/600x600/CCCCCC/FFFFFF?text=Image+Not+Found';">
        </div>

        <div class="product-info">
            <h1><?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?></h1>
            <p class="price">GHS <?php echo number_format((float)$product['price'], 2); ?></p>

            <p class="stock <?php echo ($product['stock_quantity'] < 10) ? 'low' : ''; ?>">
                Available Stock: <?php echo (int)$product['stock_quantity']; ?> units
            </p>

            <div class="description">
                <?php echo nl2br(htmlspecialchars($product['description'], ENT_QUOTES, 'UTF-8')); ?>
            </div>

            <form action="add_to_cart.php" method="POST">
                <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                <button type="submit" class="button">Add to Cart</button>
            </form>

            <div class="vendor-card">
                <h3>Sold By</h3>
                <p><strong>Farm:</strong> 
                    <a href="profile.php?id=<?php echo (int)$product['vendor_id']; ?>">
                        <?php echo htmlspecialchars($product['farm_name'], ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                </p>
                <p><strong>Location:</strong> <?php echo htmlspecialchars($product['location'], ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
        </div>
    </div>
</div>

<?php
$stmt->close();
$conn->close();
include 'footer.php';
?>
