<?php
session_start();
require 'db.php';
$error = ""; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $allowed_domains = ['ashesi.edu.gh', 'admin.ashesi.edu.gh'];
    $domain = substr(strrchr($email, "@"), 1);

    if (!in_array($domain, $allowed_domains)) {
        $error = "Access Denied: You must use a valid Ashesi email.";
    } else {
        $check_stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        
        if ($check_stmt->get_result()->num_rows > 0) {
            $error = "Email already registered. <a href='login.php'>Login here</a>";
        } else {
            // Determine Admin Status
            $is_admin = ($domain === 'admin.ashesi.edu.gh') ? 1 : 0;
            
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (full_name, email, password_hash, is_admin) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sssi", $full_name, $email, $hashed, $is_admin);
            
            if ($stmt->execute()) {
                // SUCCESS: Redirect immediately to Login
                header("Location: login.php?signup=success");
                exit();
            } else { 
                $error = "Database Error: " . $conn->error; 
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Sign Up</title><link rel="stylesheet" href="style.css"></head>
<body>
<div class="auth-container">
    <div class="auth-card">
        <h2 style="text-align:center; color:#701326">Sign Up</h2>
        <?php if($error) echo "<p style='color:red;text-align:center'>$error</p>"; ?>
        
        <form method="POST" onsubmit="validateForm(event)">
            <input type="text" name="full_name" placeholder="Full Name" required>
            <input type="email" id="email" name="email" placeholder="Email (@ashesi.edu.gh)" required>
            <input type="password" id="password" name="password" placeholder="Password" required>
            <button type="submit">Create Account</button>
        </form>
        <p style="margin-top:15px; text-align:center">Already have an account? <a href="login.php">Login</a></p>
    </div>
</div>
<script>
function validateForm(event) {
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const emailPattern = /^[a-zA-Z0-9._%+-]+@(admin\.)?ashesi\.edu\.gh$/i;
    const passPattern = /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/;

    if (!emailPattern.test(email)) {
        alert("Invalid Email! Must be @ashesi.edu.gh or @admin.ashesi.edu.gh");
        event.preventDefault(); return false;
    }
    if (!passPattern.test(password)) {
        alert("Password must be at least 8 characters long and contain both letters and numbers.");
        event.preventDefault(); return false;
    }
    return true;
}
</script>
</body>
</html>