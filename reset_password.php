<?php
include 'db_connect.php';
$token = $_GET['token'] ?? '';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['password'];
    $token = $_POST['token'];

    $stmt = $conn->prepare("SELECT email FROM password_resets WHERE token = ? AND expires_at > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $email = $result->fetch_assoc()['email'];
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE users SET hashed_password = ? WHERE email = ?");
        $stmt->bind_param("ss", $hashed, $email);
        $stmt->execute();

        $stmt = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();

        $success = "✅ Password updated successfully. You can now log in.";
    } else {
        $error = "Invalid or expired token.";
    }
}
?>

<style>
body {
  background-image: url('banner5.jpg');
  background-size: cover;
  background-repeat: no-repeat;
  background-position: center;
  background-attachment: fixed;
  font-family: "Inter", sans-serif;
}

.form-container {
  max-width: 420px;
  margin: 150px auto;
  background: #fff;
  padding: 40px;
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
  width: 100%;
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

.remember-me {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 15px;
}

@media (max-width: 600px) {
  .form-container {
    margin: 40px 15px;
    padding: 30px 20px;
  }
}
</style>

<?php include 'header.php'; ?>

<div class="form-container">
    <h1 class="page-title">Reset Your Password</h1>

    <?php if ($error): ?>
        <div class="message-box error-message"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="message-box success-message"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if (!$success): ?>
        <form method="post">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
            <div class="form-group">
                <label for="password">New Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn-primary">Reset Password</button>
        </form>
    <?php endif; ?>

    <p><a href="login.php">Back to Login</a></p>
</div>

<?php include 'footer.php'; ?>
