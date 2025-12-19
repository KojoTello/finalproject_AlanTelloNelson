<?php
session_start();
require 'db.php';
$error = "";

// Check for success message from signup redirect
if (isset($_GET['signup']) && $_GET['signup'] == 'success') {
    $success_msg = "Account created successfully! Please log in.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    // Select is_admin in the query
    $stmt = $conn->prepare("SELECT user_id, full_name, password_hash, is_admin FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password_hash'])) {
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['full_name'] = $row['full_name'];
            $_SESSION['is_admin'] = $row['is_admin']; // Save role to session

            // Redirect based on role
            if ($row['is_admin'] == 1) {
                header("Location: admin.php");
            } else {
                header("Location: dashboard.php");
            }
            exit();
        } else { 
            $error = "Invalid password."; 
        }
    } else { 
        $error = "User not found. Please sign up."; 
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Login</title><link rel="stylesheet" href="style.css"></head>
<body>
<div class="auth-container">
    <div class="auth-card">
        <h2 style="text-align:center; color:#701326">Login</h2>
        
        <?php if(isset($success_msg)) echo "<p style='color:green;text-align:center'>$success_msg</p>"; ?>
        <?php if($error) echo "<p style='color:red;text-align:center'>$error</p>"; ?>
        
        <form method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Log In</button>
        </form>
        <p style="margin-top:15px; text-align:center">New here? <a href="signup.php">Sign Up</a></p>
    </div>
</div>
</body>
</html>