<?php
session_start();
include 'db_connect.php';

$error_message = '';
$remembered_email = $_COOKIE['user_email'] ?? '';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Handle login submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);

    if (empty($email) || empty($password)) {
        $error_message = "Both email and password are required.";
    } else {
        $stmt = $conn->prepare("SELECT id, hashed_password, user_type, full_name, is_verified, is_approved FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['hashed_password'])) {
                if ($user['user_type'] === 'vendor' && !$user['is_approved']) {
                    $error_message = "Your vendor account is pending approval.";
                } elseif (!$user['is_verified']) {
                    $error_message = "Please verify your email before logging in.";
                } else {
                    // Login success
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_type'] = $user['user_type'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['email'] = $email;

                    if ($remember) {
                        setcookie("user_email", $email, time() + (86400 * 30), "/"); // 30 days
                    }

                    header("Location: index.php");
                    exit();
                }
            } else {
                $error_message = "Invalid email or password.";
            }
        } else {
            $error_message = "Invalid email or password.";
        }
        $stmt->close();
    }
}

$conn->close();
?>


<?php include 'header.php'; ?>

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

<div class="form-container">
    <h1 class="page-title">Login to Your Account</h1>

    <?php if ($error_message): ?>
        <div class="message-box error-message">
            <p><?php echo htmlspecialchars($error_message); ?></p>
        </div>
    <?php endif; ?>

    <form action="login.php" method="post">
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($remembered_email); ?>" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        <div class="remember-me">
            <input type="checkbox" id="remember" name="remember">
            <label for="remember">Remember Me</label>
        </div>
        <button type="submit" class="btn btn-primary">Login</button>
    </form>

    <p><a href="forgot_password.php">Forgot your password?</a></p>
    <p>Don't have an account? <a href="register.php">Register here</a></p>
    <p><a href="resend_verification.php">Resend verification link</a></p>

</div>

<?php include 'footer.php'; ?>
