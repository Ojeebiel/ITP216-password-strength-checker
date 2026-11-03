<?php
// view/dashboard.php - presentation only
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}
$userName = $_SESSION['user_name'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        body { font-family: system-ui, sans-serif; background: #f4f6f9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .card { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); width: 100%; max-width: 600px; text-align: center; }
        h1 { color: #2c3e50; }
        .success-banner { background: #e8f8f0; color: #27ae60; padding: 15px; border-radius: 6px; margin: 20px 0; border-left: 4px solid #27ae60; }
        a.btn-logout { display: inline-block; margin-top: 20px; padding: 10px 20px; background: #e74c3c; color: white; text-decoration: none; border-radius: 6px; }
        a.btn-logout:hover { background: #c0392b; }
    </style>
</head>
<body>

<div class="card">
    <h1>Dashboard</h1>
    <div class="success-banner">
        <strong>Success!</strong> You have securely logged in.
    </div>
    <h2>Welcome, <?php echo htmlspecialchars($userName); ?>!</h2>
    <p>Your authentication token and session variables have been established.</p>
    
    <a href="../controllers/login-user.php?action=logout" class="btn-logout">Log Out</a>
</div>

</body>
</html>