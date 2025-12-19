<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

// Helper function to draw stars
function renderStars($rating) {
    if(!$rating) $rating = 0;
    $stars = '<div class="stars-display">';
    $rounded = round($rating);
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $rounded) {
            $stars .= '<i class="fas fa-star filled"></i>';
        } else {
            $stars .= '<i class="fas fa-star"></i>';
        }
    }
    $stars .= '</div>';
    return $stars;
}

// PROFESSORS QUERY
$prof_sql = "SELECT f.*, 
             (SELECT AVG(rating_score) FROM reviews WHERE faculty_id = f.faculty_id) as avg_score,
             (SELECT COUNT(*) FROM reviews WHERE faculty_id = f.faculty_id) as count
             FROM faculty_members f WHERE role = 'Professor' ORDER BY full_name ASC";
$prof_result = $conn->query($prof_sql);

// INTERNS QUERY
$intern_sql = "SELECT f.*, 
               (SELECT AVG(rating_score) FROM reviews WHERE faculty_id = f.faculty_id) as avg_score,
               (SELECT COUNT(*) FROM reviews WHERE faculty_id = f.faculty_id) as count
               FROM faculty_members f WHERE role = 'Intern' ORDER BY full_name ASC";
$intern_result = $conn->query($intern_sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<nav class="navbar">
        <div class="logo">Ashesi Review</div>
        <div style="display:flex; align-items:center; gap:15px;">
            <span>Hi, <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
            
            <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                <a href="admin.php" style="color:white; text-decoration:none; font-weight:bold; border:1px solid white; padding:5px 10px; border-radius:4px;">
                    <i class="fas fa-user-shield"></i> Admin Portal
                </a>
            <?php endif; ?>

            <a href="logout.php" class="logout-btn">Log Out</a>
        </div>
    </nav>

    <div class="search-container">
        <input type="text" id="searchInput" onkeyup="liveSearch()" class="student-search-bar" placeholder="🔍 Search for a name, department, or role...">
    </div>

    <div class="view-toggle-container">
        <button class="toggle-btn active" onclick="switchView('faculty')" id="btn-faculty">
            <i class="fas fa-chalkboard-teacher"></i> Faculty
        </button>
        <button class="toggle-btn" onclick="switchView('interns')" id="btn-interns">
            <i class="fas fa-user-graduate"></i> Interns
        </button>
    </div>

    <section class="filter-section">
        <button class="filter-btn active" onclick="filterSelection('all')">All</button>
        <button class="filter-btn" onclick="filterSelection('Computer Science & Information Systems')">CS & IS</button>
        <button class="filter-btn" onclick="filterSelection('Business Administration')">Business</button>
        <button class="filter-btn" onclick="filterSelection('Economics')">Economics</button>
        <button class="filter-btn" onclick="filterSelection('Engineering')">Engineering</button>
        <button class="filter-btn" onclick="filterSelection('Law')">Law</button>
        <button class="filter-btn" onclick="filterSelection('Humanities & Social Sciences')">Humanities</button>
    </section>

    <main class="dashboard-container">
        
        <div id="view-faculty">
            <div class="grid-list">
                <?php while($row = $prof_result->fetch_assoc()): 
                    $score = $row['avg_score'] ? number_format($row['avg_score'], 1) : "0.0"; 
                    $initials = strtoupper(substr($row['full_name'], 0, 1)); 
                    $status_class = strtolower(str_replace(' ', '-', $row['employment_status']));
                ?>
                    <!-- Make the whole card clickable or just the button. Let's make the card cleaner. -->
                    <div class="card" data-dept="<?php echo $row['department']; ?>" onclick="window.location.href='faculty_detail.php?id=<?php echo $row['faculty_id']; ?>'" style="cursor:pointer">
                        <div class="card-left">
                            <?php if(!empty($row['image_url'])): ?>
                                <img src="<?php echo $row['image_url']; ?>" class="avatar-img">
                            <?php else: ?>
                                <div class="avatar-circle"><?php echo $initials; ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="card-right">
                            <h3><?php echo $row['full_name']; ?></h3>
                            <span class="status-badge <?php echo $status_class; ?>"><?php echo $row['employment_status']; ?></span>
                            <p class="dept"><?php echo $row['department']; ?></p>
                            
                            <div class="rating-box">
                                <span style="font-weight:bold; color:black; font-size:1.1rem; margin-right:5px;"><?php echo $score; ?></span>
                                <?php echo renderStars($row['avg_score']); ?>
                                <span>(<?php echo $row['count']; ?>)</span>
                            </div>
                            
                            <span class="view-btn">View Profile</span>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <div id="view-interns" style="display:none;">
            <div class="grid-list">
                <?php while($row = $intern_result->fetch_assoc()): 
                    $score = $row['avg_score'] ? number_format($row['avg_score'], 1) : "0.0"; 
                    $initials = strtoupper(substr($row['full_name'], 0, 1)); 
                ?>
                    <div class="card" data-dept="<?php echo $row['department']; ?>" onclick="window.location.href='faculty_detail.php?id=<?php echo $row['faculty_id']; ?>'" style="cursor:pointer">
                        <div class="card-left">
                            <div class="avatar-circle fi-color"><?php echo $initials; ?></div>
                        </div>
                        <div class="card-right">
                            <h3><?php echo $row['full_name']; ?></h3>
                            <span class="status-badge intern">Intern</span>
                            <p class="dept"><?php echo $row['department']; ?></p>
                            
                            <div class="rating-box">
                                <span style="font-weight:bold; color:black; font-size:1.1rem; margin-right:5px;"><?php echo $score; ?></span>
                                <?php echo renderStars($row['avg_score']); ?>
                                <span>(<?php echo $row['count']; ?>)</span>
                            </div>

                            <span class="view-btn fi-btn">View Profile</span>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </main>

    <script>
        // 1. LIVE SEARCH FUNCTION
        function liveSearch() {
            let input = document.getElementById('searchInput').value.toUpperCase();
            let cards = document.querySelectorAll('.card');
            
            cards.forEach(card => {
                let name = card.querySelector('h3').innerText.toUpperCase();
                let dept = card.querySelector('.dept').innerText.toUpperCase();
                
                if (name.indexOf(input) > -1 || dept.indexOf(input) > -1) {
                    card.style.display = "flex";
                } else {
                    card.style.display = "none";
                }
            });

            if(input === "") {
                filterSelection('all');
            }
        }

        // 2. SWITCH VIEW FUNCTION
        function switchView(viewName) {
            document.getElementById('view-faculty').style.display = 'none';
            document.getElementById('view-interns').style.display = 'none';
            document.getElementById('btn-faculty').classList.remove('active');
            document.getElementById('btn-interns').classList.remove('active');

            if(viewName === 'faculty') {
                document.getElementById('view-faculty').style.display = 'block';
                document.getElementById('btn-faculty').classList.add('active');
            } else {
                document.getElementById('view-interns').style.display = 'block';
                document.getElementById('btn-interns').classList.add('active');
            }
        }

        // 3. FILTER FUNCTION
        function filterSelection(cat) {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            
            // Highlight the correct button - simplified for demo
            let buttons = document.querySelectorAll('.filter-btn');
            for(let btn of buttons) {
                if(btn.innerText.includes(cat) || (cat === 'all' && btn.innerText === 'All')) {
                    btn.classList.add('active');
                }
            }

            document.getElementById('searchInput').value = ""; 

            const isFaculty = document.getElementById('view-faculty').style.display !== 'none';
            const context = isFaculty ? document.getElementById('view-faculty') : document.getElementById('view-interns');
            
            context.querySelectorAll('.card').forEach(c => {
                c.style.display = (cat === 'all' || c.dataset.dept === cat) ? 'flex' : 'none';
            });
        }
    </script>
</body>
</html>
