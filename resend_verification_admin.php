<?php
session_start();
require_once 'db_connect.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);


if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit;
}

$email = $_GET['email'] ?? '';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Invalid email.");
}

$stmt = $conn->prepare("SELECT full_name FROM users WHERE email = ? AND is_verified = 0");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows === 1) {
    $user = $result->fetch_assoc();
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
        $mail->Username = 'adwadifolocalmart@gmail.com';
        $mail->Password = 'ctjvhppldujrmwpb';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('adwadifolocalmart@gmail.com', 'Adwadifo Admin');
        $mail->addAddress($email, $user['full_name']);
        $mail->Subject = 'Resend: Verify Your Email';
        $mail->Body = "Hello {$user['full_name']},\n\nHere is your new verification link:\n$link\n\nThank you,\nAdwadifo Team";

        $mail->send();
    } catch (Exception $e) {
        error_log("Email error: {$mail->ErrorInfo}");
    }
}

header("Location: admin.php?view=unverified");
exit;
