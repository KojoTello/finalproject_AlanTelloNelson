<?php
session_start();
require 'db.php';

// 1. SECURITY: Only allow Admins
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    die("Access Denied. Admins Only. <a href='login.php'>Login</a>");
}

// 2. HANDLE ACTIONS
// DELETE REVIEW
if (isset($_GET['delete_review'])) {
    $id = intval($_GET['delete_review']);
    $conn->query("DELETE FROM reviews WHERE review_id = $id");
    header("Location: admin.php"); exit();
}

// DELETE FACULTY
if (isset($_GET['delete_faculty'])) {
    $id = intval($_GET['delete_faculty']);
    // First delete their reviews to satisfy Foreign Key constraints
    $conn->query("DELETE FROM reviews WHERE faculty_id = $id");
    // Then delete the person
    $conn->query("DELETE FROM faculty_members WHERE faculty_id = $id");
    header("Location: admin.php"); exit();
}

// ADD OR UPDATE FACULTY
$edit_mode = false;
$edit_data = ['full_name'=>'', 'department'=>'Computer Science & Information Systems', 'role'=>'Professor', 'employment_status'=>'Full-time', 'image_url'=>'', 'faculty_id'=>''];

// Check if we are in "Edit Mode"
if (isset($_GET['edit_faculty'])) {
    $edit_mode = true;
    $id = intval($_GET['edit_faculty']);
    $res = $conn->query("SELECT * FROM faculty_members WHERE faculty_id = $id");
    $edit_data = $res->fetch_assoc();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_faculty'])) {
    $name = $_POST['name'];
    $dept = $_POST['dept'];
    $role = $_POST['role'];
    $status = $_POST['status'];
    $img = $_POST['image_url'];
    
    if (!empty($_POST['faculty_id'])) {
        // UPDATE EXISTING
        $fid = $_POST['faculty_id'];
        $stmt = $conn->prepare("UPDATE faculty_members SET full_name=?, department=?, role=?, employment_status=?, image_url=? WHERE faculty_id=?");
        $stmt->bind_param("sssssi", $name, $dept, $role, $status, $img, $fid);
    } else {
        // INSERT NEW
        $stmt = $conn->prepare("INSERT INTO faculty_members (full_name, department, role, employment_status, image_url) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $dept, $role, $status, $img);
    }
    $stmt->execute();
    header("Location: admin.php"); exit();
}

// 3. SEARCH LOGIC
$search_query = "";
$fac_sql = "SELECT * FROM faculty_members ORDER BY full_name ASC";

if (isset($_GET['search'])) {
    $search_query = $_GET['search'];
    // Secure search
    $safe_search = $conn->real_escape_string($search_query);
    $fac_sql = "SELECT * FROM faculty_members WHERE full_name LIKE '%$safe_search%' OR department LIKE '%$safe_search%'";
}
$faculty_list = $conn->query($fac_sql);

// FETCH REVIEWS
$reviews = $conn->query("SELECT r.*, u.full_name as student, f.full_name as faculty 
                         FROM reviews r 
                         JOIN users u ON r.user_id = u.user_id 
                         JOIN faculty_members f ON r.faculty_id = f.faculty_id 
                         ORDER BY r.created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Portal</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .admin-container { max-width: 1200px; margin: 30px auto; padding: 20px; }
        .grid-split { display: grid; grid-template-columns: 1fr 2fr; gap: 30px; }
        
        table { width: 100%; border-collapse: collapse; background: white; margin-top: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        th, td { padding: 10px; border: 1px solid #ddd; font-size: 0.9rem; text-align: left; }
        th { background: #701326; color: white; }
        
        .action-btn { padding: 5px 10px; border-radius: 4px; text-decoration: none; font-size: 0.8rem; margin-right: 5px; color: white; }
        .btn-edit { background: #f0ad4e; }
        .btn-delete { background: #d9534f; }
        
        .search-bar { width: 100%; padding: 10px; margin-bottom: 20px; border: 1px solid #ccc; border-radius: 5px; }
        .form-box { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        input, select { width: 100%; padding: 8px; margin: 5px 0 15px 0; border: 1px solid #ccc; border-radius: 4px; }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo">Admin Portal</div>
        <div>
            <a href="dashboard.php" style="color:white; margin-right:20px;">Back to Dashboard</a>
            <a href="logout.php" class="logout-btn">Log Out</a>
        </div>
    </nav>

    <div class="admin-container">
        <h1>Faculty Management</h1>
        
        <div class="grid-split">
            <div class="form-box">
                <h3 style="color:#701326;"><?php echo $edit_mode ? "Edit Faculty" : "Add Faculty"; ?></h3>
                <form method="POST">
                    <input type="hidden" name="faculty_id" value="<?php echo $edit_data['faculty_id']; ?>">
                    
                    <label>Full Name</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($edit_data['full_name']); ?>" required>
                    
                    <label>Department</label>
                    <select name="dept">
                        <option <?php if($edit_data['department']=='Computer Science & Information Systems') echo 'selected'; ?>>Computer Science & Information Systems</option>
                        <option <?php if($edit_data['department']=='Business Administration') echo 'selected'; ?>>Business Administration</option>
                        <option <?php if($edit_data['department']=='Economics') echo 'selected'; ?>>Economics</option>
                        <option <?php if($edit_data['department']=='Engineering') echo 'selected'; ?>>Engineering</option>
                        <option <?php if($edit_data['department']=='Law') echo 'selected'; ?>>Law</option>
                        <option <?php if($edit_data['department']=='Humanities & Social Sciences') echo 'selected'; ?>>Humanities & Social Sciences</option>
                    </select>
                    
                    <label>Role</label>
                    <select name="role">
                        <option value="Professor" <?php if($edit_data['role']=='Professor') echo 'selected'; ?>>Professor</option>
                        <option value="Intern" <?php if($edit_data['role']=='Intern') echo 'selected'; ?>>Intern</option>
                    </select>
                    
                    <label>Status</label>
                    <select name="status">
                        <option <?php if($edit_data['employment_status']=='Full-time') echo 'selected'; ?>>Full-time</option>
                        <option <?php if($edit_data['employment_status']=='Adjunct') echo 'selected'; ?>>Adjunct</option>
                        <option <?php if($edit_data['employment_status']=='Head of Dept') echo 'selected'; ?>>Head of Dept</option>
                        <option <?php if($edit_data['employment_status']=='Intern') echo 'selected'; ?>>Intern</option>
                        <option <?php if($edit_data['employment_status']=='Dean') echo 'selected'; ?>>Dean</option>
                    </select>

                    <label>Image URL (Optional)</label>
                    <input type="text" name="image_url" value="<?php echo htmlspecialchars($edit_data['image_url']); ?>" placeholder="https://...">

                    <button type="submit" name="save_faculty" style="width:100%; padding:10px; background:#701326; color:white; border:none; border-radius:4px; cursor:pointer;">
                        <?php echo $edit_mode ? "Update Faculty" : "Add Faculty"; ?>
                    </button>
                    <?php if($edit_mode): ?>
                        <a href="admin.php" style="display:block; text-align:center; margin-top:10px; text-decoration:none; color:#777;">Cancel Edit</a>
                    <?php endif; ?>
                </form>
            </div>

            <div>
                <form method="GET">
                    <input type="text" name="search" class="search-bar" placeholder="Search faculty name or department..." value="<?php echo htmlspecialchars($search_query); ?>">
                </form>

                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Dept</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($faculty_list->num_rows > 0): ?>
                            <?php while($row = $faculty_list->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['role']); ?></td>
                                <td style="font-size:0.8rem"><?php echo htmlspecialchars($row['department']); ?></td>
                                <td>
                                    <a href="admin.php?edit_faculty=<?php echo $row['faculty_id']; ?>" class="action-btn btn-edit"><i class="fas fa-edit"></i></a>
                                    <a href="admin.php?delete_faculty=<?php echo $row['faculty_id']; ?>" class="action-btn btn-delete" onclick="return confirm('Delete this faculty member?');"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4">No faculty found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <h2 style="margin-top:40px; border-top:1px solid #ccc; padding-top:20px;">Review Moderation</h2>
        <table>
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Faculty</th>
                    <th>Rating</th>
                    <th>Comment</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($r = $reviews->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($r['student']); ?></td>
                    <td><?php echo htmlspecialchars($r['faculty']); ?></td>
                    <td><?php echo $r['rating_score']; ?>/5</td>
                    <td><?php echo htmlspecialchars($r['comment']); ?></td>
                    <td>
                        <a href="admin.php?delete_review=<?php echo $r['review_id']; ?>" class="action-btn btn-delete" onclick="return confirm('Delete review?');">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>