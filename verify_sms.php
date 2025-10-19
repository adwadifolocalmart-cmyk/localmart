<?php
session_start();
include 'header.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submitted = $_POST['code'] ?? '';
    $expected = $_SESSION['sms_code'] ?? null;

    if ($submitted === $expected) {
        $_SESSION['phone_verified'] = true;
        $success = "✅ Phone number verified successfully. You can now <a href='login.php'>log in</a>.";
        unset($_SESSION['sms_code']);
        unset($_SESSION['pending_phone']);
    } else {
        $error = "❌ Incorrect code. Please try again.";
    }
}
?>

<style>
.verify-container {
    max-width: 500px;
    margin: 120px auto;
    background: #fff;
    padding: 40px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    font-family: "Inter", sans-serif;
}
.verify-container h1 {
    text-align: center;
    color: #1e8449;
    margin-bottom: 20px;
}
.verify-container p {
    text-align: center;
    font-size: 1.1rem;
}
.message-box {
    margin-bottom: 15px;
    padding: 10px 15px;
    border-radius: 6px;
    text-align: center;
}
.success-message {
    background: #eafaf1;
    color: #2e7d32;
    border: 1px solid #c8e6c9;
}
.error-message {
    background: #fdecea;
    color: #c0392b;
    border: 1px solid #f5c6cb;
}
input[type="text"] {
    width: 100%;
    padding: 10px;
    margin-top: 10px;
    border-radius: 6px;
    border: 1px solid #ccc;
}
button {
    margin-top: 15px;
    background: #1e8449;
    color: white;
    border: none;
    padding: 10px;
    width: 100%;
    border-radius: 6px;
    font-size: 1rem;
    cursor: pointer;
}
button:hover {
    background: #145a32;
}
</style>

<div class="verify-container">
    <h1>Verify Your Phone</h1>

    <?php if ($error): ?>
        <div class="message-box error-message"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="message-box success-message"><?php echo $success; ?></div>
    <?php else: ?>
        <form method="post">
            <label for="code">Enter the 6-digit code sent to your phone</label>
            <input type="text" name="code" id="code" required>
            <button type="submit">Verify</button>
        </form>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
