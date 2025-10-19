<?php
require_once "db_connect.php";
$id = intval($_GET['id']);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $stock = $_POST['stock_quantity'];
    $conn->query("UPDATE products SET name='$name', price='$price', stock_quantity='$stock' WHERE id=$id");
    header("Location: admin.php?view=products");
    exit;
}
$product = $conn->query("SELECT * FROM products WHERE id=$id")->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head><title>Edit Product</title></head>
<body>
<h3>Edit Product</h3>
<form method="POST">
  Name: <input type="text" name="name" value="<?= $product['name'] ?>"><br>
  Price: <input type="number" step="0.01" name="price" value="<?= $product['price'] ?>"><br>
  Stock: <input type="number" name="stock_quantity" value="<?= $product['stock_quantity'] ?>"><br>
  <button type="submit">Save</button>
</form>
</body>
</html>
