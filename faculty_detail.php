<?php
// 1. SETUP
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
require 'db.php';

// Security Check
if (!isset($_SESSION['user_id'])) { 
    header("Location: login.php"); exit(); 
}

$faculty_id = $_GET['id'] ?? null;
if (!$faculty_id) die("No Faculty ID provided.");

// 2. FETCH FACULTY INFO
$stmt = $conn->prepare("SELECT * FROM faculty_members WHERE faculty_id = ?");
$stmt->bind_param("i", $faculty_id);
$stmt->execute();
$faculty = $stmt->get_result()->fetch_assoc();

if (!$faculty) die("Faculty member not found.");

// 3. FETCH AGGREGATE STATS
$agg_sql = "SELECT 
    AVG(rating_score) as avg_quality,
    AVG(rating_difficulty) as avg_diff,
    COUNT(CASE WHEN take_again = 1 THEN 1 END) as yes_count,
    COUNT(*) as total_reviews
    FROM reviews WHERE faculty_id = ?";
$agg_stmt = $conn->prepare($agg_sql);
$agg_stmt->bind_param("i", $faculty_id);
$agg_stmt->execute();
$stats = $agg_stmt->get_result()->fetch_assoc();

// Format Data
$overall_quality = $stats['avg_quality'] ? number_format($stats['avg_quality'], 1) : "N/A";
$avg_difficulty = $stats['avg_diff'] ? number_format($stats['avg_diff'], 1) : "N/A";
$take_again_pct = ($stats['total_reviews'] > 0) 
    ? round(($stats['yes_count'] / $stats['total_reviews']) * 100) . "%" 
    : "N/A";

// 4. FETCH REVIEWS LIST
$rev_sql = "SELECT * FROM reviews WHERE faculty_id = ? ORDER BY created_at DESC";
$rev_stmt = $conn->prepare($rev_sql);
$rev_stmt->bind_param("i", $faculty_id);
$rev_stmt->execute();
$reviews = $rev_stmt->get_result();

// Helper for Stars
function renderStars($rating) {
    $stars = '<span class="stars-display">';
    $rounded = round($rating);
    for ($i = 1; $i <= 5; $i++) {
        $stars .= ($i <= $rounded) 
            ? '<i class="fas fa-star filled"></i>' 
            : '<i class="fas fa-star" style="color:#ddd"></i>';
    }
    $stars .= '</span>';
    return $stars;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo $faculty['full_name']; ?> - Details</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="logo">
             <img src="images/ashesi-logo.png" alt="Ashesi Review" style="height:50px;">
        </div>
        <div>
            <a href="dashboard.php" style="color:white; margin-right:20px; text-decoration:none;">&larr; Back to Dashboard</a>
        </div>
    </nav>

    <main class="dashboard-container">
        
        <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
            <div class="alert-success">
                <i class="fas fa-check-circle"></i> Review submitted successfully!
            </div>
        <?php endif; ?>

        <div class="card profile-header-card">
            
            <div class="profile-avatar-container">
                <?php 
                    $initials = strtoupper(substr($faculty['full_name'], 0, 1));
                    $has_img_url = !empty($faculty['image_url']);
                ?>
                
                <?php if($has_img_url): ?>
                    <img src="<?php echo htmlspecialchars($faculty['image_url']); ?>" 
                         class="profile-avatar-img" 
                         alt="Profile"
                         onerror="this.style.display='none'; document.getElementById('fallback-avatar').style.display='flex';">
                <?php endif; ?>

                <div id="fallback-avatar" 
                     class="profile-avatar-circle" 
                     style="<?php echo $has_img_url ? 'display:none;' : 'display:flex;'; ?>">
                    <?php echo $initials; ?>
                </div>
            </div>

            <div class="profile-info">
                <h1><?php echo $faculty['full_name']; ?></h1>
                <p class="profile-dept">
                    <?php echo $faculty['department']; ?> | <?php echo $faculty['role']; ?>
                </p>

                <div class="scoreboard-card">
                    <div class="score-box">
                        <div class="score-value big-score"><?php echo $overall_quality; ?></div>
                        <div class="score-label">Quality</div>
                    </div>
                    <div class="score-box middle-box">
                        <div class="score-value"><?php echo $take_again_pct; ?></div>
                        <div class="score-label">Take Again</div>
                    </div>
                    <div class="score-box">
                        <div class="score-value"><?php echo $avg_difficulty; ?></div>
                        <div class="score-label">Difficulty</div>
                    </div>
                </div>
            </div>

            <a href="rate.php?id=<?php echo $faculty_id; ?>" class="rate-btn-large">Rate Professor</a>
        </div>

        <h3 style="margin-bottom:20px; color:#444; border-bottom:2px solid #eee; padding-bottom:10px;">
            Student Reviews (<?php echo $reviews->num_rows; ?>)
        </h3>

        <?php if ($reviews->num_rows > 0): ?>
            <?php while($row = $reviews->fetch_assoc()): ?>
                <div class="card review-card-item" style="display:block; margin-bottom:20px;">
                    <div class="review-header">
                        <span class="review-course"><?php echo htmlspecialchars($row['course_code']); ?></span>
                        <span class="review-date"><?php echo date("M d, Y", strtotime($row['created_at'])); ?></span>
                    </div>

                    <div style="margin-bottom:12px; font-size:0.95rem;">
                        <strong style="margin-right:5px;">Quality:</strong> 
                        <?php echo renderStars($row['rating_score']); ?> 
                        <span style="color:#ddd; margin:0 10px;">|</span>
                        <strong>Difficulty:</strong> <?php echo $row['rating_difficulty']; ?>/5
                    </div>

                    <p class="review-text">
                        <?php echo nl2br(htmlspecialchars($row['comment'])); ?>
                    </p>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="text-align:center; padding:40px; color:#777; background:white; border-radius:10px; border:1px solid #eee;">
                <i class="far fa-comment-dots" style="font-size:2rem; margin-bottom:10px;"></i>
                <p>No reviews yet. Be the first to rate this professor!</p>
            </div>
        <?php endif; ?>

    </main>
</body>
</html>