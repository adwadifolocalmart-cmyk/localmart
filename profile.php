<?php
// profile.php
session_start();
include 'db_connect.php';

// Get vendor ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit();
}
$vendor_id = intval($_GET['id']);

// Fetch vendor details, including the phone number
$stmt_vendor = $conn->prepare("SELECT farm_name, full_name, bio, location, profile_picture, phone_number FROM users WHERE id = ? AND user_type = 'vendor'");
$stmt_vendor->bind_param("i", $vendor_id);
$stmt_vendor->execute();
$result_vendor = $stmt_vendor->get_result();

if ($result_vendor->num_rows === 0) {
    $page_title = "Vendor Not Found";
    include 'header.php';
    echo "<div class='container'><p class='page-title'>This vendor could not be found.</p></div>";
    include 'footer.php';
    exit();
}

$vendor = $result_vendor->fetch_assoc();
$page_title = "Profile of " . htmlspecialchars(string: $vendor['farm_name']);
include 'header.php';
?>

<style>
    .profile-header {
        background-color: var(--card-bg-color);
        padding: 40px;
        border-radius: 12px;
        text-align: center;
        margin-bottom: 40px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    .profile-picture {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        object-fit: cover;
        border: 5px solid var(--primary-color);
        margin-bottom: 20px;
    }
    .profile-header h1 {
        font-size: 2.5rem;
        margin: 0;
        color: var(--primary-color);
    }
    .profile-header .location {
        font-size: 1.2rem;
        color: #777;
        margin-top: 5px;
    }
    .profile-header .bio {
        max-width: 700px;
        margin: 20px auto 0;
        font-size: 1.1rem;
    }
</style>
<main class="container">
    <div class="profile-header">
        <img src="<?php echo htmlspecialchars($vendor['profile_picture']); ?>" alt="Profile Picture" class="profile-picture" onerror="this.onerror=null;this.src='https://placehold.co/150x150/CCCCCC/FFFFFF?text=Photo';">
        <h1><?php echo htmlspecialchars($vendor['farm_name']); ?></h1>
        <p class="location"><?php echo htmlspecialchars($vendor['location']); ?></p>
        <p class="vendor-contact">
            Contact: <strong><?php echo htmlspecialchars($vendor['phone_number']); ?></strong>
        </p>
        <?php if (!empty($vendor['bio'])): ?>
            <p class="bio"><?php echo nl2br(htmlspecialchars($vendor['bio'])); ?></p>
        <?php endif; ?>
    </div>

    <h2 class="page-title">Products from <?php echo htmlspecialchars($vendor['farm_name']); ?></h2>
    
    <div class="product-grid">
        <?php
        // Fetch products from this vendor
        $sql_products = "SELECT id, name, price, image_url FROM products WHERE vendor_id = ?";
        $stmt_products = $conn->prepare($sql_products);
        $stmt_products->bind_param("i", $vendor_id);
        $stmt_products->execute();
        $result_products = $stmt_products->get_result();


        if ($result_products->num_rows > 0):
            while ($product = $result_products->fetch_assoc()):
        ?>
        <a href="product_detail.php?id=<?php echo htmlspecialchars($product['id']); ?>" class="product-card">
            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
            <center>
                <div class="product-info">
                <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                <p>GHS <?php echo number_format($product['price'], 2); ?></p>
            </div>
            </center>
        </a>
        <?php
            endwhile;
        else:
            echo "<p>This vendor has no active products right now.</p>";
        endif;
        ?>
    </div>
</main>

<?php
$stmt_vendor->close();
$conn->close();
include 'footer.php';
?>