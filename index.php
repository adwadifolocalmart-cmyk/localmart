<?php
// index.php
$page_title = "Adwadifo - Fresh Produce from Local Farmers";
include 'header.php';
include 'db_connect.php';

// Handle search functionality
$search_query = '';
if (isset($_GET['search']) && '' !== trim($_GET['search'])) {
    $search_query = trim($_GET['search']);
}
?>

<div class="container">
    <div class="hero-wrapper" style="position: relative; height: 60vh; overflow: hidden; display: flex; align-items: center; justify-content: center;">
        <style>
        :root {
            --primary-color: #3a8a3a;
        }

        /* Reset for this block only (kept minimal to avoid interfering with site-wide styles) */
        .hero-wrapper * { box-sizing: border-box; }

        /* Background slideshow animation */
        @keyframes slideShow {
            0% { background-image: url('banner.jpg'); }
            25% { background-image: url('banner2.jpg'); }
            50% { background-image: url('banner3.jpg'); }
            75% { background-image: url('banner4.jpg'); }
            100% { background-image: url('banner5.jpg'); }
        }

        .hero-bg {
            position: absolute;
            inset: 0;
            background-size: cover;
            background-position: center;
            animation: slideShow 12s infinite ease-in-out;
            transition: background-image 1s ease-in-out;
            filter: saturate(1.05) contrast(0.98);
        }

        .hero-overlay {
            position: relative;
            background: rgba(218, 231, 219, 0.85);
            text-align: center;
            padding: 40px 20px;
            border-radius: 12px;
            max-width: 1000px;
            width: calc(100% - 40px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.12);
        }

        .hero-overlay h1 {
            font-size: 2.4rem;
            color: var(--primary-color);
            margin-bottom: 12px;
        }

        .hero-overlay p {
            font-size: 1rem;
            color: #333;
            line-height: 1.6;
            max-width: 800px;
            margin: 0 auto 18px;
        }

        .hero-overlay a.cta {
            display: inline-block;
            background-color: var(--primary-color);
            color: #fff;
            padding: 10px 22px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            transition: transform 0.2s ease, background 0.2s ease;
        }

        .hero-overlay a.cta:hover {
            background-color: #2f6e2f;
            transform: translateY(-3px);
        }

        /* Product Grid */
        .product-grid {
            display: grid;
            justify-content: center;
            align-items: center;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 2rem;
            margin: 2rem 50px;
        }

        .product-card {
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            top: 10%;
            left: 10%;
            box-shadow: 0 5px 15px rgba(0,0,0,0.07);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
            display: flex;
            flex-direction: column;
        }

        .product-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 20px rgba(0,0,0,0.12);
        }

        .product-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .product-card:hover img { transform: scale(1.03); }

        .product-card-content {
            padding: 1.25rem;
            display: flex;
            flex-direction: column;
            gap: 0.6rem;
            flex-grow: 1;
        }

        .product-card h3 {
            color: #2d5016;
            font-size: 1.15rem;
            margin: 0;
        }

        .category {
            font-size: 0.95rem;
            color: #6b6b6b;
            margin: 0;
        }

        .price {
            font-size: 1.2rem;
            font-weight: 700;
            color: #4a7c3a;
            margin: 0.4rem 0;
        }

        .page-title {
            display: block;
            margin: 40px auto 8px;
            font-size: 2.2rem;
            font-weight: 700;
            color: #ffffff;
            background-color: var(--primary-color);
            padding: 10px 18px;
            border-radius: 8px;
            text-align: center;
            width: fit-content;
            max-width: 95%;
        }

        .vendor-info { color: #666; font-size: 0.9rem; margin-top: auto; }
        .vendor-info::before { content: "🌱 "; }

        .btn {
            display: inline-block;
            background: linear-gradient(90deg,#4a7c3a,#5a8c4a);
            color: #fff;
            padding: 8px 14px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
        }

        .no-products { text-align:center; padding: 2rem; color:#666; font-size:1.05rem; grid-column:1/-1; }

        @media (max-width:768px){
            .hero-overlay h1 { font-size: 1.6rem; }
            .hero-overlay p { font-size: 0.95rem; }
            .hero-wrapper { height: auto; padding: 40px 0; }
            .hero-bg { position: absolute; height: 100%; }
        }
        </style>

        <div class="hero-bg" aria-hidden="true"></div>

        <div class="hero-overlay" role="banner">
            <h1>Welcome to Adwadifo</h1>
            <p>Your online market square for the freshest vegetables, fruits, and livestock directly from local Ghanaian farmers.</p>
            <a class="cta" href="products.php">Shop All Products</a>
        </div>
    </div>

    <?php if (!empty($search_query)): ?>
        <h2 class="page-title">Search Results for "<?php echo htmlspecialchars($search_query, ENT_QUOTES, 'UTF-8'); ?>"</h2>
    <?php else: ?>
        <h2 class="page-title">Featured Products</h2>
    <?php endif; ?>

    <div class="product-grid" id="product_grid">
        <?php
        // Prepare SQL safely and handle possible prepare() failures
        if (!empty($search_query)) {
            $sql = "
                SELECT p.id, p.name, p.price, p.image_url, p.description, p.category, u.farm_name
                FROM products p
                JOIN users u ON p.vendor_id = u.id
                WHERE p.stock_quantity > 0
                AND (p.name LIKE ? OR p.description LIKE ? OR p.category LIKE ? OR u.farm_name LIKE ?)
                ORDER BY p.created_at DESC
            ";
            $stmt = $conn->prepare($sql);
            if ($stmt === false) {
                echo '<div class="no-products">Error preparing search query. Please try again later.</div>';
            } else {
                $search_param = '%' . $search_query . '%';
                $stmt->bind_param('ssss', $search_param, $search_param, $search_param, $search_param);
            }
        } else {
            $sql = "
                SELECT p.id, p.name, p.price, p.image_url, p.description, p.category, u.farm_name
                FROM products p
                JOIN users u ON p.vendor_id = u.id
                WHERE p.stock_quantity > 0
                ORDER BY p.created_at DESC
                LIMIT 3
            ";
            $stmt = $conn->prepare($sql);
            if ($stmt === false) {
                echo '<div class="no-products">Error preparing products query. Please try again later.</div>';
            }
        }

        if ($stmt && $stmt !== false) {
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $id = (int)$row['id'];
                    $name = htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8');
                    $image = htmlspecialchars($row['image_url'], ENT_QUOTES, 'UTF-8');
                    $category = htmlspecialchars($row['category'], ENT_QUOTES, 'UTF-8');
                    $price = number_format((float)$row['price'], 2);
                    $farm = htmlspecialchars($row['farm_name'], ENT_QUOTES, 'UTF-8');

                    
                    echo '<div class="product-card" >';
                    echo '<img src="' . $image . '" alt="' . $name . '" onerror="this.onerror=null;this.src=\'https://placehold.co/600x400/CCCCCC/FFFFFF?text=Image+Not+Found\';">';
                    echo '<div class="product-card-content">';
                    echo '<h3>' . $name . '</h3>';
                    echo '<p class="category">' . ucfirst($category) . '</p>';
                    echo '<p class="price">GHS ' . $price . '</p>';
                    echo '<p class="vendor-info">Sold by: ' . $farm . '</p>';
                    echo '<a class="btn" href="product_detail.php?id=' . $id . '">View Details</a>';
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                if (!empty($search_query)) {
                    echo '<div class="no-products">No products found matching "' . htmlspecialchars($search_query, ENT_QUOTES, 'UTF-8') . '". Please try a different search term.</div>';
                } else {
                    echo '<div class="no-products">No featured products available at the moment. Please check back later!</div>';
                }
            }

            $stmt->close();
        }
        ?>
    </div>

    <?php if (empty($search_query)): ?>
        <div style="text-align: center; margin-top: 24px;">
            <a href="products.php" class="btn" style="padding: 12px 26px; border-radius: 6px;">View All Products</a>
        </div>
    <?php endif; ?>
</div>

<?php
$conn->close();
include 'footer.php';
?>
