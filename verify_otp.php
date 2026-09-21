<?php
session_start();
if (!isset($_SESSION['temp_user_id'])) { header("Location: login.php"); exit(); }

$conn = new mysqli('localhost', 'root', '', 'itp216_pt');
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submitted_otp = $_POST['otp_code'];
    $user_id = $_SESSION['temp_user_id'];
    
    $stmt = $conn->prepare("SELECT otp_code, expires_at FROM login_otp WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        if (strtotime($row['expires_at']) < time()) {
            $error = "OTP has expired. Please log in again.";
        } elseif (password_verify($submitted_otp, $row['otp_code'])) {
            // Success! Clear temp data and establish real session
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_name'] = $_SESSION['temp_user_name'];
            
            unset($_SESSION['temp_user_id'], $_SESSION['temp_user_name']);
            $conn->query("DELETE FROM login_otp WHERE user_id = $user_id");
            
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid OTP.";
        }
    } else {
        $error = "No OTP requested.";
    }
}
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
    
    <form method="POST" action="">
        <input type="text" name="otp_code" required pattern="\d{6}" maxlength="6" placeholder="000000" autocomplete="one-time-code" autofocus>
        <button type="submit">Verify Code</button>
    </form>
    
    <p style="margin-top: 20px;"><a href="login.php">Cancel and return to Login</a></p>
</div>

</body>
</html>