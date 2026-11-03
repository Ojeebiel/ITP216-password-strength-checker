<?php
// view/verifyOTP.php - presentation only
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['temp_user_id'])) {
    header("Location: ../index.php");
    exit();
}
$error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP</title>
    <style>
        body { font-family: system-ui, sans-serif; background: #f4f6f9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); width: 100%; max-width: 400px; box-sizing: border-box; text-align: center; }
        input { width: 100%; padding: 12px; margin: 15px 0; border: 2px solid #e1e4e8; border-radius: 6px; font-size: 24px; text-align: center; letter-spacing: 8px; box-sizing: border-box; font-family: monospace; }
        button { width: 100%; padding: 12px; margin-top: 5px; background: #27ae60; color: white; border: none; border-radius: 6px; font-size: 16px; cursor: pointer; font-weight: bold; }
        button:hover { background: #219653; }
        .msg { color: #d9383a; font-size: 14px; margin-bottom: 10px; background: #fce8e6; padding: 10px; border-radius: 6px; border-left: 4px solid #d9383a; display: <?php echo $error ? 'block' : 'none'; ?>; text-align: left; }
        p.subtitle { color: #5c6c7c; font-size: 15px; line-height: 1.5; margin-bottom: 20px; }
        a { color: #27ae60; text-decoration: none; font-size: 14px; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="card">
    <h2>Two-Step Verification</h2>
    <p class="subtitle">Please enter the 6-digit code sent to your email to continue.</p>
    
    <div class="msg"><?php echo htmlspecialchars($error); ?></div>
    
    <form method="POST" action="../controllers/verify-otp.php">
        <input type="text" name="otp_code" required pattern="\d{6}" maxlength="6" placeholder="000000" autocomplete="one-time-code" autofocus>
        <button type="submit">Verify Code</button>
    </form>
    
    <p style="margin-top: 20px;"><a href="../controllers/login-user.php?action=logout">Cancel and return to Login</a></p>
</div>

</body>
</html>