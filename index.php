<?php
// index.php (login view) - presentation only
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body { font-family: system-ui, sans-serif; background: #f4f6f9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); width: 100%; max-width: 400px; box-sizing: border-box; }
        input { width: 100%; padding: 12px; margin: 10px 0; border: 2px solid #e1e4e8; border-radius: 6px; font-size: 16px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; margin-top: 15px; background: #27ae60; color: white; border: none; border-radius: 6px; font-size: 16px; cursor: pointer; }
        button:hover { background: #219653; }
        .msg { color: #d9383a; font-size: 14px; margin-bottom: 10px; background: #fce8e6; padding: 10px; border-radius: 6px; border-left: 4px solid #d9383a; display: <?php echo $error ? 'block' : 'none'; ?>; }
    </style>
</head>
<body>

<div class="card">
    <h2>Welcome Back</h2>
    <div class="msg"><?php echo htmlspecialchars($error); ?></div>
    
    <form method="POST" action="controllers/login-user.php">
        <label for="email">Email Address:</label>
        <input type="email" id="email" name="email" required placeholder="name@example.com">

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">Log In</button>
    </form>
    <p style="text-align: center; margin-top: 15px; font-size: 14px;">Don't have an account? <a href="view/register.php">Register</a></p>
</div>

</body>
</html>