<?php
require_once "db_connect.php";
$id = intval($_GET['id']);
$conn->query("DELETE FROM users WHERE id = $id");
header("Location: admin.php?view=users");
exit;
?>
