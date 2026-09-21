<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_name'])) {
    header("Location: login.php");
    exit();
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}
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
    <h2>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h2>
    <p>Your authentication token and session variables have been established.</p>
    
    <a href="?logout=true" class="btn-logout">Log Out</a>
</div>

</body>
</html>