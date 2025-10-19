<?php
session_start();
require_once 'db_connect.php';
require 'vendor/autoload.php'; // PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$errors = [];
$success_message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Sanitize inputs
    $full_name = htmlspecialchars(trim($_POST['full_name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $password = $_POST['password'];
    $confirm = $_POST['password_confirm'];
    $user_type = htmlspecialchars($_POST['user_type']);
    $phone = htmlspecialchars(trim($_POST['phone_number'] ?? ''));
    $location = htmlspecialchars(trim($_POST['location'] ?? ''));
    $farm_name = ($user_type === 'vendor') ? htmlspecialchars(trim($_POST['farm_name'] ?? '')) : null;

    // Validate
    if (empty($full_name)) $errors[] = "Full name is required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format.";
    if (strlen($password) < 8) $errors[] = "Password must be at least 8 characters.";
    if ($password !== $confirm) $errors[] = "Passwords do not match.";
    if (empty($user_type)) $errors[] = "Select account type.";
    if ($user_type === 'vendor' && empty($farm_name)) $errors[] = "Farm name required for vendors.";
    if ($user_type === 'vendor' && empty($location)) $errors[] = "Location required for vendors.";

    // Check email uniqueness
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) $errors[] = "Email already registered.";
    $stmt->close();

    // Profile picture
    $profile_picture = '';
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $dir = 'uploads/profiles/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $ext = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $size = $_FILES['profile_picture']['size'];

        if (!in_array($ext, $allowed)) $errors[] = "Invalid image type.";
        elseif ($size > 500000) $errors[] = "Image too large (max 500KB).";
        else {
            $filename = uniqid('profile_') . '.' . $ext;
            $path = $dir . $filename;
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $path)) {
                $profile_picture = $path;
            } else {
                $errors[] = "Failed to upload image.";
            }
        }
    }

    // Insert user
    if (empty($errors)) {
        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $token = bin2hex(random_bytes(32));
        $is_approved = ($user_type === 'vendor') ? 0 : 1;

        $stmt = $conn->prepare("INSERT INTO users (full_name, email, hashed_password, user_type, farm_name, location, phone_number, profile_picture, verification_token, is_verified, is_approved) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?)");
        $stmt->bind_param("sssssssssi", $full_name, $email, $hashed, $user_type, $farm_name, $location, $phone, $profile_picture, $token, $is_approved);

        if ($stmt->execute()) {
            // Send verification email
            $link = "http://localhost/verify_email.php?token=$token";
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'adwadifolocalmart@gmail.com';         // Replace
                $mail->Password = 'ctjvhppldujrmwpb';           // Replace
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                $mail->setFrom('adwadifolocalmart@gmail.com', 'Adwadifo');
                $mail->addAddress($email, $full_name);
                $mail->Subject = 'Verify Your Email';
                $mail->Body = "Hello $full_name,\n\nPlease verify your email:\n$link\n\nThank you,\nAdwadifo Team";
                $mail->send();
            } catch (Exception $e) {
                error_log("Email error: {$mail->ErrorInfo}");
            }

            // Simulate SMS
            $_SESSION['sms_code'] = rand(100000, 999999);
            $_SESSION['pending_phone'] = $phone;
            echo "<script>
                alert('Your SMS code is: {$_SESSION['sms_code']}');
                window.location.href = 'verify_sms.php';
            </script>";
            exit;
        } else {
            $errors[] = "Registration failed. Try again.";
        }
        $stmt->close();
    }
}
$conn->close();
?>


<style>
/* === LOGIN PAGE STYLING === */
body {
  background-image: url('banner5.jpg');
  background-size: cover;        /* Makes it fill the entire page */
  background-repeat: no-repeat;  /* Prevents tiling */
  background-position: center;   /* Centers the image */
  background-attachment: fixed; 
  font-family: "Inter", sans-serif;
}

.form-container {
    max-width: 500px;
  margin: 100px auto;
  background: #fff;
  padding: 45px;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.page-title {
  text-align: center;
  color: #1e8449;
  margin-bottom: 25px;
  font-size: 1.8rem;
  font-weight: 700;
}

.form-group {
  margin-bottom: 20px;
}

label {
  display: block;
  font-weight: 600;
  color: #333;
  margin-bottom: 8px;
}

input[type="email"],
input[type="password"] {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #ccc;
  border-radius: 6px;
  font-size: 1rem;
  transition: border-color 0.3s ease;
}

input:focus {
  border-color: #1e8449;
  outline: none;
}

.btn-primary {
  background: #1e8449;
  color: white;
  border: none;
  padding: 10px 0;
  width: 108%;
  font-size: 1rem;
  font-weight: 600;
  border-radius: 6px;
  cursor: pointer;
  transition: background 0.3s ease, transform 0.2s ease;
}

.btn-primary:hover {
  background: #145a32;
  transform: scale(1.02);
}

.message-box {
  margin-bottom: 15px;
  padding: 10px 15px;
  border-radius: 6px;
  text-align: center;
}

.error-message {
  background: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
}

.form-container p {
  text-align: center;
  margin-top: 20px;
  color: #333;
}

.form-container a {
  color: #1e8449;
  text-decoration: none;
  font-weight: 600;
}

.form-container a:hover {
  text-decoration: underline;
}

/* === Responsive === */
@media (max-width: 600px) {
  .form-container {
    margin: 40px 15px;
    padding: 30px 20px;
  }
}
</style>

<div class="form-container">
    <h1 class="page-title">Create an Account</h1>
    <?php if (!empty($errors)): ?>
        <div class="message-box error-message">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($success_message): ?>
        <div class="message-box success-message"><?php echo $success_message; ?></div>
    <?php else: ?>
        <form action="register.php" method="post" id="registrationForm" enctype="multipart/form-data">
            <div class="form-group">
                <label for="user_type">I am a:</label>
                <select id="user_type" name="user_type" required onchange="toggleVendorFields()">
                    <option value="">--Select Account Type--</option>
                    <option value="customer" <?php echo (isset($_POST['user_type']) && $_POST['user_type'] == 'customer') ? 'selected' : ''; ?>>Customer (Buying)</option>
                    <option value="vendor" <?php echo (isset($_POST['user_type']) && $_POST['user_type'] == 'vendor') ? 'selected' : ''; ?>>Vendor (Selling)</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group" id="vendorFields" style="display: <?php echo (isset($_POST['user_type']) && $_POST['user_type'] == 'vendor') ? 'block' : 'none'; ?>;">
                <div class="form-group">
                    <label for="farm_name">Farm/Business Name</label>
                    <input type="text" id="farm_name" name="farm_name" value="<?php echo htmlspecialchars($_POST['farm_name'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="location">Location (e.g., City, Region)</label>
                <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($_POST['location'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="phone_number">Phone Number</label>
                <input type="tel" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($_POST['phone_number'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="profile_picture">Profile Picture (Optional)</label>
                <input type="file" id="profile_picture" name="profile_picture" accept="image/*">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label for="password_confirm">Confirm Password</label>
                <input type="password" id="password_confirm" name="password_confirm" required>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">Register</button>
        </form>
    <?php endif; ?>
</div>

<script>
function toggleVendorFields() {
    const userType = document.getElementById('user_type').value;
    const vendorFields = document.getElementById('vendorFields');
    const farmNameInput = document.getElementById('farm_name');
    const locationInput = document.getElementById('location');
    const isVendor = userType === 'vendor';

    vendorFields.style.display = isVendor ? 'block' : 'none';
    farmNameInput.required = isVendor;
    locationInput.required = isVendor;
}
document.addEventListener('DOMContentLoaded', toggleVendorFields);
</script>

<?php include 'footer.php'; ?>
