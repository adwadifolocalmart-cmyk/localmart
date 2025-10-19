<?php
// products.php
$page_title = "All Products";
include 'header.php';
include 'db_connect.php';
?>

<style>
    :root {
    --primary-color: #4a7c3a;
}

/* Reset and base styles */
body, html {
    margin: 0;
    padding: 0;
    width: 100%;
    overflow-x: hidden;
    background-color: #f9fbf8;
    color: #333;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    line-height: 1.6;
}

a {
    text-decoration: none;
    color: inherit;
}

/* Main container */
.main-content {
    max-width: 1200px;
    padding: 120px 1.5rem 2rem; /* Top padding accounts for fixed header */
}

/* Page title */
.page-title {
    text-align: center;
    font-size: 2rem;
    font-weight: bold;
    color: #fff;
    background-color: var(--primary-color);
    padding: 12px 24px;
    border-radius: 8px;
    margin-bottom: 2rem;
    width: fit-content;
    margin-left: auto;
    margin-right: auto;
}

/* Product grid */
.product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1.5rem;
    padding: 0 1rem;
    width: 100%;
    box-sizing: border-box;
}


/* Product card */
.product-card {
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    display: flex;
    flex-direction: column;
    animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.product-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 12px 20px rgba(0, 0, 0, 0.12);
}

.product-card img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    transition: transform 0.4s ease;
}

.product-card:hover img {
    transform: scale(1.05);
}

.product-card-content {
    padding: 1.2rem;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}

.product-card h3 {
    color: #2d5016;
    font-size: 1.2rem;
    margin-bottom: 0.4rem;
}

.price {
    font-size: 1.3rem;
    font-weight: bold;
    color: #4a7c3a;
    margin: 0.4rem 0;
}

.vendor-info {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 1rem;
    flex-grow: 1;
}

.vendor-info::before {
    content: "🌱 ";
}

.btn {
    display: inline-block;
    background: linear-gradient(to right, #4a7c3a, #5a8c4a);
    color: white;
    padding: 0.6rem 1.2rem;
    border-radius: 8px;
    text-align: center;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 6px rgba(74, 124, 58, 0.2);
    margin-top: auto;
}

.btn:hover {
    background: linear-gradient(to right, #3a6c2a, #4a7c3a);
    transform: translateY(-2px);
    box-shadow: 0 6px 8px rgba(74, 124, 58, 0.3);
}

.no-products {
    text-align: center;
    padding: 2rem;
    color: #666;
    font-size: 1.1rem;
    grid-column: 1 / -1;
}

/* Responsive tweaks */
@media (max-width: 768px) {
    .page-title {
        font-size: 1.6rem;
        padding: 10px 16px;
    }

    .product-card img {
        height: 180px;
    }

    .product-card h3 {
        font-size: 1.1rem;
    }

    .price {
        font-size: 1.2rem;
    }
}

@media (max-width: 480px) {
    .main-content {
        padding: 100px 1rem 2rem;
    }

    .product-grid {
        grid-template-columns: 1fr;
    }

    .page-title {
        font-size: 1.4rem;
    }
}

</style>

<div class="main-content">
    <h1 class="page-title">Our Products</h1>

    <div class="product-grid">
        <?php
        $stmt = $conn->prepare("
            SELECT p.id, p.name, p.price, p.image_url, u.farm_name
            FROM products p
            JOIN users u ON p.vendor_id = u.id
            WHERE p.stock_quantity > 0
            ORDER BY p.name ASC
        ");

        if ($stmt && $stmt->execute()) {
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $id = (int)$row['id'];
                    $name = htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8');
                    $image = htmlspecialchars($row['image_url'], ENT_QUOTES, 'UTF-8');
                    $price = number_format((float)$row['price'], 2);
                    $farm = htmlspecialchars($row['farm_name'], ENT_QUOTES, 'UTF-8');

                    echo '<div class="product-card">';
                    echo '<img src="' . $image . '" alt="' . $name . '" onerror="this.onerror=null;this.src=\'https://placehold.co/600x400/CCCCCC/FFFFFF?text=Image+Not+Found\';">';
                    echo '<div class="product-card-content">';
                    echo '<h3>' . $name . '</h3>';
                    echo '<p class="price">GHS ' . $price . '</p>';
                    echo '<p class="vendor-info">Sold by: ' . $farm . '</p>';
                    echo '<a href="product_detail.php?id=' . $id . '" class="btn">View Details</a>';
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo '<div class="no-products">No products have been listed yet. Please check back soon!</div>';
            }

            $stmt->close();
        } else {
            echo '<div class="no-products">Error loading products. Please try again later.</div>';
        }

        $conn->close();
        ?>
    </div>
</div>

<?php include 'footer.php'; ?>
