<?php
session_start();

// Database configuration
$host = 'localhost';$db_user = 'root';
$db_pass = '';$db_name = 'itp216_pt'; // Ensure this matches your actual database name

$conn = new mysqli($host,$db_user, $db_pass,$db_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password =$_POST['password'];

    // Basic validation
    if (!empty($name) && !empty($email) && !empty($password)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt =$conn->prepare("INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email,$password_hash);

        if ($stmt->execute()) {
            $_SESSION['user_name'] =$name;
            header("Location: dashboard.php");
            exit();
        } else {
            $message = "Error: Email might already be registered.";
        }
        $stmt->close();
    } else {
        $message = "Please fill in all fields.";
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Account</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/zxcvbn/4.4.2/zxcvbn.js"></script>
    <style>
        body { font-family: system-ui, sans-serif; background: #f4f6f9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); width: 100%; max-width: 450px; box-sizing: border-box; }
        input { width: 100%; padding: 12px; margin: 10px 0; border: 2px solid #e1e4e8; border-radius: 6px; font-size: 16px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; margin-top: 15px; background: #27ae60; color: white; border: none; border-radius: 6px; font-size: 16px; cursor: pointer; }
        button:hover { background: #219653; }
        .status { margin-top: 15px; padding: 12px; border-radius: 6px; font-size: 14px; font-weight: 500; display: none; }
        .danger { background: #fce8e6; color: #d9383a; border-left: 4px solid #d9383a; }
        .safe { background: #e8f8f0; color: #27ae60; border-left: 4px solid #27ae60; }
        .msg { color: #d9383a; font-size: 14px; margin-bottom: 10px; }
    </style>
</head>
<body>

<div class="card">
    <h2>Create an Account</h2>
    <?php if($message): ?><div class="msg"><?php echo $message; ?></div><?php endif; ?>
    
    <form method="POST" action="">
        <label for="name">Full Name:</label>
        <input type="text" id="name" name="name" required placeholder="John Doe">

        <label for="email">Email Address:</label>
        <input type="email" id="email" name="email" required placeholder="john@example.com">

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required placeholder="Type a secure password...">
        
        <div id="zxcvbn-status" class="status"></div>
        <div id="breach-status" class="status"></div>

        <button type="submit" id="submit-btn">Register</button>
    </form>
    <p style="text-align: center; margin-top: 15px; font-size: 14px;">Already have an account? <a href="login.php">Log in</a></p>
</div>

<script>
    const passwordInput = document.getElementById('password');
    const zxcvbnStatus = document.getElementById('zxcvbn-status');
    const breachStatus = document.getElementById('breach-status');
    const submitBtn = document.getElementById('submit-btn');

    let debounceTimer;

    passwordInput.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        const password = passwordInput.value;

        if (!password) {
            zxcvbnStatus.style.display = 'none';
            breachStatus.style.display = 'none';
            submitBtn.disabled = false;
            return;
        }

        const analysis = zxcvbn(password);
        displayScore(analysis);

        debounceTimer = setTimeout(() => {
            checkBreachAPI(password);
        }, 500); 
    });

    function displayScore(analysis) {
        zxcvbnStatus.style.display = 'block';
        const rating = ["Very Weak", "Weak", "Reasonable", "Strong", "Excellent"];
        
        if (analysis.score < 3) {
            zxcvbnStatus.className = "status danger";
            zxcvbnStatus.innerHTML = `⚠️ <strong>Guessability:</strong> ${rating[analysis.score]}<br><small>${analysis.feedback.warning || 'Add more words or variety.'}</small>`;
            submitBtn.disabled = true; // Prevent weak password registration
        } else {
            zxcvbnStatus.className = "status safe";
            zxcvbnStatus.innerHTML = `✅ <strong>Guessability:</strong> ${rating[analysis.score]}`;
            submitBtn.disabled = false;
        }
    }

    async function checkBreachAPI(password) {
        breachStatus.style.display = 'block';
        breachStatus.className = "status";
        breachStatus.textContent = "Checking breach databases safely...";

        try {
            const msgUint8 = new TextEncoder().encode(password);
            const hashBuffer = await crypto.subtle.digest('SHA-1', msgUint8);
            const hashArray = Array.from(new Uint8Array(hashBuffer));
            const hashHex = hashArray.map(b => b.toString(16).padStart(2, '0')).join('').toUpperCase();

            const prefix = hashHex.slice(0, 5);
            const suffix = hashHex.slice(5);

            const response = await fetch(`https://api.pwnedpasswords.com/range/${prefix}`);
            if (!response.ok) throw new Error('API error');
            
            const data = await response.text();
            const lines = data.split('\n');
            let breachCount = 0;

            for (let line of lines) {
                const [returnedSuffix, count] = line.trim().split(':');
                if (returnedSuffix === suffix) {
                    breachCount = parseInt(count, 10);
                    break;
                }
            }

            if (breachCount > 0) {
                breachStatus.className = "status danger";
                breachStatus.innerHTML = `❌ <strong>Leaked!</strong> This password appeared in <strong>${breachCount.toLocaleString()}</strong> data breaches. Do not use it.`;
                submitBtn.disabled = true; 
            } else {
                breachStatus.className = "status safe";
                breachStatus.innerHTML = `✅ <strong>No Breaches Found:</strong> This password has not been seen in known leaks.`;
            }

        } catch (error) {
            breachStatus.className = "status danger";
            breachStatus.textContent = "Could not verify breach status at this time.";
            console.error(error);
        }
    }
</script>
</body>
</html>