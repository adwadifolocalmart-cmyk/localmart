<?php
require_once "db_connect.php";
$id = intval($_GET['id']);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['full_name'];
    $email = $_POST['email'];
    $location = $_POST['location'];
    $conn->query("UPDATE users SET full_name='$name', email='$email', location='$location' WHERE id=$id");
    header("Location: admin.php?view=users");
    exit;
}
$user = $conn->query("SELECT * FROM users WHERE id=$id")->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head><title>Edit User</title></head>
<body>
<h3>Edit User</h3>
<form method="POST">
  Name: <input type="text" name="full_name" value="<?= $user['full_name'] ?>"><br>
  Email: <input type="email" name="email" value="<?= $user['email'] ?>"><br>
  Location: <input type="text" name="location" value="<?= $user['location'] ?>"><br>
  <button type="submit">Save</button>
</form>
</body>
</html>
