<?php
require 'db_connect.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    $stmt = $conn->prepare("SELECT id FROM users WHERE verification_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $stmt = $conn->prepare("UPDATE users SET is_verified = 1, verification_token = NULL WHERE verification_token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        echo "Your email has been verified. You can now log in.";
    } else {
        echo "Invalid or expired token.";
    }
} else {
    echo "No token provided.";
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
    text-align: center;
    font-family: "Inter", sans-serif;
}
.verify-container h1 {
    color: #1e8449;
    margin-bottom: 20px;
}
.verify-container p {
    font-size: 1.1rem;
    color: <?php echo $success ? '#2e7d32' : '#c0392b'; ?>;
}
</style>

<div class="verify-container">
    <h1>Email Verification</h1>
    <p><?php echo $message; ?></p>
</div>

<?php include 'footer.php'; ?>
