<?php
// 1. SETUP
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
require 'db.php';

// Security Checks
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); exit();
}

$faculty_id = $_GET['id'] ?? null;
if (!$faculty_id) die("Error: No Faculty ID provided.");

// 2. HANDLE SUBMISSION
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $course = $_POST['course_code'];
    // Use Null Coalescing (??) to prevent "undefined index" errors
    $clarity = $_POST['clarity'] ?? 0;
    $helpfulness = $_POST['helpfulness'] ?? 0;
    $difficulty = $_POST['difficulty'] ?? 0;
    $take_again = $_POST['take_again'] ?? 0;
    $comment = $_POST['comment'];
    $user_id = $_SESSION['user_id'];

    // Basic Validation
    if($clarity < 1 || $helpfulness < 1) {
        $error = "Please fill in all the star ratings.";
    } else {
        // Calculate Overall Quality
        $rating_score = ($clarity + $helpfulness) / 2;

        $stmt = $conn->prepare("INSERT INTO reviews 
            (user_id, faculty_id, course_code, rating_score, rating_clarity, rating_helpfulness, rating_difficulty, take_again, comment) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        if (!$stmt) die("SQL Error: " . $conn->error);

        $stmt->bind_param("iisddiiis", $user_id, $faculty_id, $course, $rating_score, $clarity, $helpfulness, $difficulty, $take_again, $comment);
        
        if ($stmt->execute()) {
            // --- THE FIX: IMMEDIATE REDIRECT ---
            // No text is printed. Browser jumps instantly.
            header("Location: faculty_detail.php?id=$faculty_id&status=success");
            exit();
        } else {
            $error = "Database Error: " . $stmt->error;
        }
    }
}

// Fetch Name for Display
$res = $conn->query("SELECT full_name FROM faculty_members WHERE faculty_id = $faculty_id");
$fac_name = ($res->num_rows > 0) ? $res->fetch_assoc()['full_name'] : "Unknown Faculty";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Rate <?php echo $fac_name; ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="logo">Ashesi Review</div>
        <div>
            <a href="faculty_detail.php?id=<?php echo $faculty_id; ?>" style="color:white; text-decoration:none;">&larr; Cancel</a>
        </div>
    </nav>

    <div class="auth-container" style="height:auto; min-height:90vh; padding: 40px 0;">
        <div class="auth-card" style="max-width:500px">
            <h2 style="color:#701326; text-align:center; margin-bottom:20px">Rate: <?php echo $fac_name; ?></h2>
            
            <?php if(isset($error)) echo "<p style='color:red;text-align:center;'>$error</p>"; ?>

            <form method="POST">
                <div style="margin-bottom:15px;">
                    <label style="font-weight:bold; display:block; margin-bottom:5px;">Course Code</label>
                    <input type="text" name="course_code" placeholder="e.g. CS221" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                </div>
                
                <div style="margin-bottom:15px;">
                    <label style="font-weight:bold; display:block;">Clarity (1-5)</label>
                    <div class="star-rating-input" style="justify-content:flex-end;">
                        <input type="radio" name="clarity" id="c5" value="5"><label for="c5">★</label>
                        <input type="radio" name="clarity" id="c4" value="4"><label for="c4">★</label>
                        <input type="radio" name="clarity" id="c3" value="3"><label for="c3">★</label>
                        <input type="radio" name="clarity" id="c2" value="2"><label for="c2">★</label>
                        <input type="radio" name="clarity" id="c1" value="1"><label for="c1">★</label>
                    </div>
                </div>

                <div style="margin-bottom:15px;">
                    <label style="font-weight:bold; display:block;">Helpfulness (1-5)</label>
                    <div class="star-rating-input" style="justify-content:flex-end;">
                        <input type="radio" name="helpfulness" id="h5" value="5"><label for="h5">★</label>
                        <input type="radio" name="helpfulness" id="h4" value="4"><label for="h4">★</label>
                        <input type="radio" name="helpfulness" id="h3" value="3"><label for="h3">★</label>
                        <input type="radio" name="helpfulness" id="h2" value="2"><label for="h2">★</label>
                        <input type="radio" name="helpfulness" id="h1" value="1"><label for="h1">★</label>
                    </div>
                </div>

                <div style="margin-bottom:15px;">
                    <label style="font-weight:bold; display:block;">Difficulty</label>
                    <input type="range" name="difficulty" min="1" max="5" value="3" style="width:100%;">
                    <div style="display:flex; justify-content:space-between; font-size:0.8rem; color:#666;">
                        <span>Easy</span><span>Hard</span>
                    </div>
                </div>

                <div style="margin-bottom:15px;">
                    <label style="font-weight:bold; display:block; margin-bottom:5px;">Would you take again?</label>
                    <div>
                        <label style="margin-right:15px;"><input type="radio" name="take_again" value="1" required> Yes</label>
                        <label><input type="radio" name="take_again" value="0"> No</label>
                    </div>
                </div>
                
                <div style="margin-bottom:20px;">
                    <label style="font-weight:bold; display:block; margin-bottom:5px;">Comment</label>
                    <textarea name="comment" rows="4" placeholder="Share your experience..." required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;"></textarea>
                </div>
                
                <button type="submit" class="view-btn" style="width:100%; border:none; background:var(--ashesi-maroon); color:white; cursor:pointer;">Submit Review</button>
            </form>
        </div>
    </div>
</body>
</html>