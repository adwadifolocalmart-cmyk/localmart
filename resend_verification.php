<?php
session_start();
require_once 'db_connect.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);


$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } else {
        $stmt = $conn->prepare("SELECT full_name, is_verified FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if ($user['is_verified']) {
                $error = "Your account is already verified.";
            } else {
                $token = bin2hex(random_bytes(32));
                $stmt = $conn->prepare("UPDATE users SET verification_token = ? WHERE email = ?");
                $stmt->bind_param("ss", $token, $email);
                $stmt->execute();

                $link = "http://localhost/localmart/verify_email.php?token=$token";
                

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
                    $mail->addAddress($email, $user['full_name']);
                    $mail->Subject = 'Resend: Verify Your Email';
                    $mail->Body = "Hello {$user['full_name']},\n\nHere is your new verification link:\n$link\n\nThank you,\nAdwadifo Team";

                    $mail->send();
                    $success = "Verification link has been resent to your email.";
                } catch (Exception $e) {
                    $error = "Failed to send email. Try again later.";
                }
            }
        } else {
            $error = "No account found with that email.";
        }
    }
}
?>

<?php include 'header.php'; ?>
<div class="form-container">
    <h1 class="page-title">Resend Verification Link</h1>

    <?php if ($error): ?>
        <div class="message-box error-message"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="message-box success-message"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="form-group">
            <label for="email">Enter your registered email</label>
            <input type="email" name="email" id="email" required>
        </div>
        <button type="submit" class="btn-primary">Resend Link</button>
    </form>
    <p><a href="login.php">Back to Login</a></p>
</div>
<?php include 'footer.php'; ?>
