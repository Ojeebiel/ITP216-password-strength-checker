<?php
// controllers/verify-otp.php
// Handles the OTP submission (step 2 of login).

require_once __DIR__ . '/../model/Model.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Stores an error for the OTP view and redirects back to it.
 */
function backToOtp(string $error): void {
    $_SESSION['flash_error'] = $error;
    header("Location: ../view/verifyOTP.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../view/verifyOTP.php");
    exit();
}

// Must have passed step 1 first
if (empty($_SESSION['temp_user_id'])) {
    $_SESSION['flash_error'] = "Please log in first.";
    header("Location: ../index.php");
    exit();
}

$submittedOtp = trim($_POST['otp_code'] ?? '');
$userId       = (int) $_SESSION['temp_user_id'];

$model  = new Model();
$record = $model->getOtp($userId);

if (!$record) {
    backToOtp("No OTP requested.");
}

if (strtotime($record['expires_at']) < time()) {
    backToOtp("OTP has expired. Please log in again.");
}

if (!password_verify($submittedOtp, $record['otp_code'])) {
    backToOtp("Invalid OTP.");
}

// Success: clear temp data and establish the real session
session_regenerate_id(true);
$_SESSION['user_id']   = $userId;
$_SESSION['user_name'] = $_SESSION['temp_user_name'];
unset($_SESSION['temp_user_id'], $_SESSION['temp_user_name']);

$model->deleteOtp($userId);

header("Location: ../view/dashboard.php");
exit();