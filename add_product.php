<?php
// add_product.php
$page_title = "Add a New Product";
include 'header.php';
include 'db_connect.php';

// Security check
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'vendor') {
    header("Location: login.php");
    exit();
}

$errors = [];
$success_message = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $vendor_id = $_SESSION['user_id'];
    $name = trim($_POST['name']);
    $description = trim($_POST['description_html'] ?? '');
    $price = filter_var($_POST['price'], FILTER_VALIDATE_FLOAT);
    $category = trim($_POST['category']);
    $stock_quantity = filter_var($_POST['stock_quantity'], FILTER_VALIDATE_INT);

    // Image upload
    $image_url = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        $image_name = uniqid() . '_' . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $image_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if ($check === false) $errors[] = "File is not a valid image.";
        if ($_FILES["image"]["size"] > 2 * 1024 * 1024) $errors[] = "Image must be less than 2MB.";
        if (!in_array($imageFileType, $allowed_types)) $errors[] = "Only JPG, JPEG, PNG, GIF, and WEBP files are allowed.";

        if (empty($errors)) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image_url = $target_file;
            } else {
                $errors[] = "Failed to upload image.";
            }
        }
    } else {
        $errors[] = "Product image is required.";
    }

    // Validate other fields
    if (empty($name)) $errors[] = "Product name is required.";
    if (empty($description)) $errors[] = "Description is required.";
    if ($price === false || $price <= 0) $errors[] = "Invalid price.";
    if (empty($category)) $errors[] = "Category is required.";
    if ($stock_quantity === false || $stock_quantity < 0) $errors[] = "Invalid stock quantity.";

    // Insert into DB
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO products (vendor_id, name, description, price, category, stock_quantity, image_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issdsis", $vendor_id, $name, $description, $price, $category, $stock_quantity, $image_url);

        if ($stmt->execute()) {
            header("Location: products.php?added=1");
            exit();
        } else {
            $errors[] = "Database error: Could not add product.";
        }
        $stmt->close();
    }
}
?>

<style>
    .form-container {
        max-width: 700px;
        margin: 120px auto 40px;
        padding: 2rem;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .page-title {
        text-align: center;
        color: #1e8449;
        margin-bottom: 1.5rem;
    }

    .form-group {
        margin-bottom: 1.2rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: #333;
    }

    .form-group input,
    .form-group select {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 1rem;
    }

    .form-group textarea {
        width: 100%;
        height: 100px;
        padding: 0.75rem;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 1rem;
        resize: vertical;
    }

    .btn-primary {
        background-color: #1e8449;
        color: white;
        border: none;
        padding: 0.9rem;
        font-size: 1rem;
        border-radius: 6px;
        cursor: pointer;
        transition: background 0.3s ease;
        width: 100%;
    }

    .btn-primary:hover {
        background-color: #145a32;
    }

    .alert {
        padding: 1rem;
        border-radius: 6px;
        margin-bottom: 1rem;
    }

    .alert-error {
        background-color: #fdecea;
        color: #c0392b;
        border: 1px solid #f5c6cb;
    }

    .image-preview {
        margin-top: 10px;
        max-width: 100%;
        height: auto;
        display: none;
        border: 1px solid #ccc;
        border-radius: 6px;
    }
</style>

<!-- Quill.js for rich text editor -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>

<div class="form-container">
    <h1 class="page-title">Add New Product</h1>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="add_product.php" method="post" enctype="multipart/form-data" onsubmit="return syncEditorContent();">
        <div class="form-group">
            <label for="name">Product Name *</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="description">Product Description *</label>
            <div id="editor"><?php echo htmlspecialchars($_POST['description_html'] ?? ''); ?></div>
            <textarea name="description_html" id="description_html" style="display:none;"></textarea>
        </div>

        <div class="form-group">
            <label for="price">Price (GHS) *</label>
            <input type="number" id="price" name="price" step="0.01" value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="category">Category *</label>
            <select id="category" name="category" required>
                <option value="">--Select Category--</option>
                <option value="vegetable" <?php echo (($_POST['category'] ?? '') === 'vegetable') ? 'selected' : ''; ?>>Vegetable</option>
                <option value="fruit" <?php echo (($_POST['category'] ?? '') === 'fruit') ? 'selected' : ''; ?>>Fruit</option>
                <option value="livestock" <?php echo (($_POST['category'] ?? '') === 'livestock') ? 'selected' : ''; ?>>Livestock</option>
                <option value="other" <?php echo (($_POST['category'] ?? '') === 'other') ? 'selected' : ''; ?>>Other</option>
            </select>
        </div>

        <div class="form-group">
            <label for="stock_quantity">Stock Quantity (units) *</label>
            <input type="number" id="stock_quantity" name="stock_quantity" value="<?php echo htmlspecialchars($_POST['stock_quantity'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="image">Product Image *</label>
            <input type="file" id="image" name="image" accept="image/*" onchange="previewImage(event)" required>
            <img id="preview" class="image-preview" alt="Image Preview">
        </div>

        <button type="submit" class="btn btn-primary">Add Product</button>
    </form>
</div>

<script>
    const quill = new Quill('#editor', {
        theme: 'snow',
        placeholder: 'Write a detailed product description...'
    });

    function syncEditorContent() {
        document.getElementById('description_html').value = quill.root.innerHTML;
        return true;
    }

    function previewImage(event) {
        const preview = document.getElementById('preview');
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    }
</script>

<?php
$conn->close();
include 'footer.php';
?>
