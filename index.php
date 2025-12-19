<?php
session_start();
require 'db.php';

// 1. IF LOGGED IN, GO TO DASHBOARD
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

// 2. OPTIONAL: FETCH STATS FOR SOCIAL PROOF
// We use a try-catch block so the page still loads even if the DB has issues
$total_reviews = 0;
$total_faculty = 0;
try {
    $rev_res = $conn->query("SELECT COUNT(*) as c FROM reviews");
    if($rev_res) $total_reviews = $rev_res->fetch_assoc()['c'];

    $fac_res = $conn->query("SELECT COUNT(*) as c FROM faculty_members");
    if($fac_res) $total_faculty = $fac_res->fetch_assoc()['c'];
} catch (Exception $e) {
    // Fail silently if DB isn't ready
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Welcome to Ashesi Review</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Specific Styles for Landing Page */
        .hero-section {
            text-align: center;
            padding: 80px 20px;
            max-width: 800px;
            margin: 0 auto;
        }
        .hero-title {
            font-size: 3.5rem;
            color: var(--ashesi-maroon);
            margin-bottom: 20px;
            line-height: 1.1;
        }
        .hero-subtitle {
            font-size: 1.2rem;
            color: #555;
            margin-bottom: 40px;
            line-height: 1.6;
        }
        .cta-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-bottom: 50px;
        }
        .btn-primary {
            background: var(--ashesi-maroon);
            color: white;
            padding: 15px 35px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.1rem;
            transition: transform 0.2s;
            box-shadow: 0 4px 10px rgba(112, 19, 38, 0.3);
        }
        .btn-secondary {
            background: white;
            color: var(--ashesi-maroon);
            padding: 15px 35px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.1rem;
            border: 2px solid var(--ashesi-maroon);
            transition: transform 0.2s;
        }
        .btn-primary:hover, .btn-secondary:hover {
            transform: translateY(-3px);
        }
        .stats-row {
            display: flex;
            justify-content: center;
            gap: 50px;
            margin-top: 50px;
            border-top: 1px solid #ddd;
            padding-top: 40px;
        }
        .stat-item {
            text-align: center;
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            color: #333;
        }
        .stat-label {
            text-transform: uppercase;
            font-size: 0.9rem;
            color: #777;
            letter-spacing: 1px;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo">
            <img src="images/ashesi-logo.png" alt="Ashesi Review" style="height:50px;">
        </div>
        <div>
            <a href="login.php" style="color:white; text-decoration:none; font-weight:bold;">Log In</a>
        </div>
    </nav>

    <main class="dashboard-container">
        <div class="hero-section">
            <h1 class="hero-title">Your Voice Matters.</h1>
            <p class="hero-subtitle">
                The official student-led platform for faculty feedback at Ashesi University. 
                Share your experiences, help your peers, and shape the academic future.
            </p>

            <div class="cta-buttons">
                <a href="signup.php" class="btn-primary">Get Started</a>
                <a href="login.php" class="btn-secondary">Log In</a>
            </div>

            <div class="stats-row">
                <div class="stat-item">
                    <div class="stat-number"><?php echo $total_reviews; ?></div>
                    <div class="stat-label">Reviews Submitted</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo $total_faculty; ?></div>
                    <div class="stat-label">Faculty Members</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">100%</div>
                    <div class="stat-label">Anonymous</div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
