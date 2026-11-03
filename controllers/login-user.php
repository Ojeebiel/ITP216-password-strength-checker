<?php
// controllers/login-user.php
// Handles the login request (step 1: email + password, then emails an OTP)
// and the logout action.

require_once __DIR__ . '/../vendor/autoload.php';   // PHPMailer (Composer)
require_once __DIR__ . '/../model/Model.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ---- Logout (also used by "Cancel" on the OTP page) ---- */
if (($_GET['action'] ?? '') === 'logout') {
    $_SESSION = [];
    session_destroy();
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php");
    exit();
}

/**
 * Stores an error for the login view and redirects back to it.
 */
function backToLogin(string $error): void {
    $_SESSION['flash_error'] = $error;
    header("Location: ../index.php");
    exit();
}

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

$model = new Model();
$user  = $model->authenticate($email, $password);

if (!$user) {
    backToLogin("Invalid email or password.");
}

// 1. Generate a 6-digit OTP and its hash
$otpPlain = (string) random_int(100000, 999999);
$otpHash  = password_hash($otpPlain, PASSWORD_DEFAULT);

// 2. Expire in 10 minutes, replace any old OTP
$expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));

if (!$model->saveOtp($user['id'], $otpHash, $expiresAt)) {
    backToLogin("Something went wrong. Please try again.");
}

// 3. Send the OTP via PHPMailer
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username = $_ENV['SMTP_USER'];
    $mail->Password = $_ENV['SMTP_PASS'];
    // $mail->Username = 'wensamwar5@gmail.com';       // Update this
    // $mail->Password   = 'gtxo umew wogl hsft';    // Update this
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom('no-reply@yourdomain.com', 'System Security');
    $mail->addAddress($email, $user['name']);

    $mail->Subject = 'Your Login OTP';
    $mail->Body    = "Your verification code is: $otpPlain \nIt expires in 10 minutes.";

    $mail->send();
} catch (Exception $e) {
    error_log("Mailer Error: " . $mail->ErrorInfo);
    backToLogin("We couldn't send your verification code. Please try again.");
}

// Temporary session data, then on to OTP verification
session_regenerate_id(true);
$_SESSION['temp_user_id']   = $user['id'];
$_SESSION['temp_user_name'] = $user['name'];

header("Location: ../view/verifyOTP.php");
exit();
