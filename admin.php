<?php
session_start();
require_once "db_connect.php";
include 'header.php';
require 'vendor/autoload.php'; // or path to PHPMailer if manual

        use PHPMailer\PHPMailer\PHPMailer;
        use PHPMailer\PHPMailer\Exception;

// Restrict access
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit;
}

if (isset($_GET['approve']) && is_numeric($_GET['approve'])) {
    $vendor_id = (int) $_GET['approve'];

    // Fetch vendor details
    $stmt = $conn->prepare("SELECT email, full_name FROM users WHERE id = ? AND user_type = 'vendor'");
    $stmt->bind_param("i", $vendor_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $vendor = $result->fetch_assoc();

        // Approve vendor
        $stmt = $conn->prepare("UPDATE users SET is_approved = 1 WHERE id = ?");
        $stmt->bind_param("i", $vendor_id);
        $stmt->execute();

        // Send email via PHPMailer
        

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'adwadifolocalmart@gmail.com'; // your Gmail
            $mail->Password = 'ctjvhppldujrmwpb';   // your Gmail App Password
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('adwadifolocalmart@gmail.com', 'Adwadifo Admin');
            $mail->addAddress($vendor['email'], $vendor['full_name']);
            $mail->Subject = 'Your Vendor Account Has Been Approved';
            $mail->Body = "Hello {$vendor['full_name']},\n\nYour vendor account on Adwadifo has been approved. You can now log in and start listing your products.\n\nLogin here: http://localhost/login.php\n\nThank you,\nAdwadifo Team";

            $mail->send();
        } catch (Exception $e) {
            error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        }
    }

    header("Location: admin_dashboard.php");
    exit();
}


// Fetch pending vendors
$result = $conn->query("SELECT id, full_name, email, farm_name, location FROM users WHERE user_type = 'vendor' AND is_approved = 0");

// Fetch dashboard stats
$totalUsers = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'];
$totalVendors = $conn->query("SELECT COUNT(*) AS total FROM users WHERE user_type='vendor'")->fetch_assoc()['total'];
$totalProducts = $conn->query("SELECT COUNT(*) AS total FROM products")->fetch_assoc()['total'];
$totalOrders = $conn->query("SELECT COUNT(*) AS total FROM orders")->fetch_assoc()['total'];

// Fetch data for Chart.js
$salesData = [];
$result = $conn->query("SELECT DATE(created_at) AS date, SUM(total_amount) AS total FROM orders GROUP BY DATE(created_at) ORDER BY date DESC LIMIT 7");
while ($row = $result->fetch_assoc()) {
    $salesData[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard | Adwadifo</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body { background: #f8f9fa; }
    .sidebar { width: 250px; background: #055902c5; color: white; height: 100vh; }
    .sidebar a { display: block; color: white; padding: 12px 20px; text-decoration: none; }
    .sidebar a:hover { background: #9ca39fff; }
    .active-link { background: #03471e79 !important; }
  </style>
</head>
<body>
<div class="d-flex">
  <div class="sidebar">
    <h4 class="text-center mt-3 mb-4">Admin Panel</h4>
    <a href="?view=dashboard" class="<?= ($_GET['view']??'dashboard')=='dashboard'?'active-link':'' ?>">Dashboard</a>
    <a href="?view=users" class="<?= ($_GET['view']??'')=='users'?'active-link':'' ?>">Users</a>
    <a href="?view=unverified" class="<?= ($_GET['view']??'')=='unverified'?'active-link':'' ?>">Unverified Users</a>
    <a href="?view=products" class="<?= ($_GET['view']??'')=='products'?'active-link':'' ?>">Products</a>
    <a href="?view=orders" class="<?= ($_GET['view']??'')=='orders'?'active-link':'' ?>">Orders</a>
    <a href="change_password.php">Change Password</a>
    <a href="logout.php">Logout</a>
  </div>

  <div class="flex-grow-1 p-4">
    <?php
    $view = $_GET['view'] ?? 'dashboard';

    if ($view === 'dashboard') {
    $labels = json_encode(array_column($salesData, 'date'));
    $totals = json_encode(array_column($salesData, 'total'));

    echo "
    <h2>Welcome, " . htmlspecialchars($_SESSION['admin_name']) . "</h2>
    <div class='row text-center mt-4'>
      <div class='col-md-3'><div class='card'><div class='card-body'><h5>Users</h5><h3>" . htmlspecialchars($totalUsers) . "</h3></div></div></div>
      <div class='col-md-3'><div class='card'><div class='card-body'><h5>Vendors</h5><h3>" . htmlspecialchars($totalVendors) . "</h3></div></div></div>
      <div class='col-md-3'><div class='card'><div class='card-body'><h5>Products</h5><h3>" . htmlspecialchars($totalProducts) . "</h3></div></div></div>
      <div class='col-md-3'><div class='card'><div class='card-body'><h5>Orders</h5><h3>" . htmlspecialchars($totalOrders) . "</h3></div></div></div>
    </div>

    <canvas id='salesChart' height='100' class='mt-5'></canvas>
    <script>
      const ctx = document.getElementById('salesChart').getContext('2d');
      new Chart(ctx, {
        type: 'bar',
        data: {
          labels: $labels,
          datasets: [{
            label: 'Total Sales (₵)',
            data: $totals,
            backgroundColor: '#1abc9c'
          }]
        },
        options: {
          responsive: true,
          scales: {
            y: {
              beginAtZero: true
            }
          }
        }
      });
    </script>";


    }

    elseif ($view === 'users') {
        echo "<h2>Manage Users</h2>";
        $res = $conn->query("SELECT * FROM users ORDER BY id DESC");
        echo "<table class='table table-bordered mt-3'>
                <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Type</th><th>Location</th><th>Action</th></tr></thead><tbody>";
        while ($r = $res->fetch_assoc()) {
            echo "<tr>
                    <td>{$r['id']}</td><td>{$r['full_name']}</td><td>{$r['email']}</td>
                    <td>{$r['user_type']}</td><td>{$r['location']}</td>
                    <td>
                      <a href='edit_user.php?id={$r['id']}' class='btn btn-sm btn-warning'>Edit</a>
                      <a href='delete_user.php?id={$r['id']}' class='btn btn-sm btn-danger' onclick='return confirm(\"Delete user?\")'>Delete</a>
                    </td>
                  </tr>";
        }
        echo "</tbody></table>";
    }

    elseif ($view === 'products') {
        echo "<h2>Manage Products</h2>";
        $res = $conn->query("SELECT p.*, u.full_name AS vendor FROM products p JOIN users u ON u.id = p.vendor_id ORDER BY p.id DESC");
        echo "<table class='table table-bordered mt-3'>
                <thead><tr><th>ID</th><th>Name</th><th>Vendor</th><th>Category</th><th>Price</th><th>Stock</th><th>Action</th></tr></thead><tbody>";
        while ($r = $res->fetch_assoc()) {
            echo "<tr>
                    <td>{$r['id']}</td><td>{$r['name']}</td><td>{$r['vendor']}</td>
                    <td>{$r['category']}</td><td>₵{$r['price']}</td><td>{$r['stock_quantity']}</td>
                    <td>
                      <a href='edit_product.php?id={$r['id']}' class='btn btn-sm btn-warning'>Edit</a>
                      <a href='delete_product.php?id={$r['id']}' class='btn btn-sm btn-danger' onclick='return confirm(\"Delete product?\")'>Delete</a>
                    </td>
                  </tr>";
        }
        echo "</tbody></table>";
    }

    elseif ($view === 'unverified') {
    echo "<h2>Unverified Users</h2>";
    $res = $conn->query("SELECT id, full_name, email, user_type, created_at FROM users WHERE is_verified = 0 ORDER BY created_at DESC");

    echo "<table class='table table-bordered mt-3'>
            <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Type</th><th>Registered</th><th>Action</th></tr></thead><tbody>";

    while ($r = $res->fetch_assoc()) {
        echo "<tr>
                <td>{$r['id']}</td><td>{$r['full_name']}</td><td>{$r['email']}</td>
                <td>{$r['user_type']}</td><td>{$r['created_at']}</td>
                <td><a href='resend_verification_admin.php?email={$r['email']}' class='btn btn-sm btn-primary'>Resend Link</a></td>
              </tr>";
    }

    echo "</tbody></table>";
}


    elseif ($view === 'orders') {
        echo "<h2>Manage Orders</h2>";
        $res = $conn->query("SELECT * FROM orders ORDER BY id DESC");
        echo "<table class='table table-bordered mt-3'>
                <thead><tr><th>ID</th><th>Customer</th><th>Total</th><th>Status</th><th>Payment</th><th>Date</th><th>Action</th></tr></thead><tbody>";
        while ($r = $res->fetch_assoc()) {
            echo "<tr>
                    <td>{$r['id']}</td><td>{$r['customer_id']}</td><td>₵{$r['total_amount']}</td>
                    <td>{$r['status']}</td><td>{$r['payment_status']}</td><td>{$r['created_at']}</td>
                    <td><a href='delete_order.php?id={$r['id']}' class='btn btn-sm btn-danger' onclick='return confirm(\"Delete order?\")'>Delete</a></td>
                  </tr>";
        }
        echo "</tbody></table>";
    }
    ?>
  </div>
</div>
</body>
</html>
