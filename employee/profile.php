<?php
session_start();
require_once "../config/db.php";

// =====================
// 🔐 SECURITY CHECK
// =====================
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$employee_id = $_SESSION['user_id'];

// Fetch user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$employee_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Branches and positions for dropdown
$branches = [
    "Ma Chicken Trinoma","Kopi Tiam Benitez","Next Door Texas","North Park P. Guevarra",
    "North Park Caltext Macapagal","North Park Caltex Slex","North Park Westgate","North Park Fairview Terraces",
    "North Park Santana Grove","North Park Parqal","North Park SM Fairview","North Park Banawe",
    "North Park SM Bicutan","North Park SM Muntinlupa","North Park Tomas Morato","North Park SM Molino",
    "North Park Convergys","North Park Hypermarket Antipolo","North Park Hypermarket Cainta","North Park Hypermarket EDSA",
    "North Park Hypermarket FTI","North Park Hypermarket Makati","North Park Market Market","North Park Greenfield",
    "North Park Ortigas Home Depot","North Park Paseo De Roces","North Park SM Dasmarinas","North Park Eton Centris",
    "North Park Arnolds","North Park Marquee Mall","North Park Valenzuela","North Park Shell Slex",
    "North Park SM North Edsa","North Park Alabang Town Center","North Park G. Araneta Ave","North Park SM Mall of Asia (Annex)",
    "North Park SM Bacoor","North Park Jetti Macapagal","North Park Park Square","North Park Hypermarket Imus",
    "North Park Hypermarket Antipolo (G/F)"
];

$positions = [
    "Store Manager","Assistant Manager","Management Trainee","Admin Assistant","Dining Supervisor",
    "Kitchen Supervisor","Cashier","Dining Staff","Kitchen Staff"
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $branch = trim($_POST['branch']);
    $position = trim($_POST['position']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    $errors = [];

    if (!empty($password) && $password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    $photoPath = $user['profile_photo'];
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === 0) {
        $allowed = ['jpg','jpeg','png','gif'];
        $ext = strtolower(pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            $errors[] = "Invalid image format. Allowed: jpg, jpeg, png, gif.";
        } else {
            $newName = 'uploads/profile_' . $employee_id . '.' . $ext;
            if (!is_dir('uploads')) mkdir('uploads', 0777, true);
            if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $newName)) {
                $photoPath = $newName;
            } else {
                $errors[] = "Failed to upload image.";
            }
        }
    }

    if (empty($errors)) {
        if (!empty($password)) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET full_name=?, email=?, branch=?, position=?, username=?, password=?, profile_photo=? WHERE id=?");
            $stmt->execute([$full_name, $email, $branch, $position, $username, $hashed, $photoPath, $employee_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET full_name=?, email=?, branch=?, position=?, username=?, profile_photo=? WHERE id=?");
            $stmt->execute([$full_name, $email, $branch, $position, $username, $photoPath, $employee_id]);
        }
        $success = "Profile updated successfully!";
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$employee_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profile</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<style>
body {
    font-family:'Poppins',sans-serif;
    background:#ffffff;
    min-height:100vh;
    margin:0;
    color:#333;
}

/* SIDEBAR */
.sidebar {
    width:280px;
    height:100vh;
    position:fixed;
    top:0;
    left:0;
    background:linear-gradient(135deg,#2c3e50 0%,#34495e 100%);
    color:white;
    padding-top:30px;
    box-shadow:2px 0 10px rgba(0,0,0,0.1);
    transition:transform 0.3s ease;
    z-index:1050;
}
.sidebar a {
    color:white;
    text-decoration:none;
    padding:15px 20px;
    display:block;
    border-radius:8px;
    margin:5px 10px;
    transition: background-color 0.3s ease, padding-left 0.3s ease;
}
.sidebar a i { margin-right:10px; }
.sidebar a:hover:not(.active) { background-color: rgba(255,255,255,0.1); padding-left:25px; }
.sidebar a.active { background: linear-gradient(45deg, #4dabf7, #1e88e5); padding-left:25px; }

/* MAIN CONTENT */
.main-content {
    margin-left:280px;
    padding:30px;
    background-color:#ffffff;
    min-height:100vh;
    border-radius:20px 0 0 0;
    box-shadow:-5px 0 15px rgba(0,0,0,0.05);
    transition:margin-left 0.3s ease;
}

/* CARDS & BUTTONS */
.card{border:2px solid #e9ecef;border-radius:15px;box-shadow:0 8px 25px rgba(0,0,0,0.05);margin-bottom:20px;padding:15px;}
.btn{border-radius:25px;padding:10px 20px;font-weight:600;transition:all 0.3s ease;}
.btn-primary{background:linear-gradient(45deg,#667eea,#764ba2);border:none;}
.btn-primary:hover{transform:scale(1.05);}
.btn-secondary{border:none;background:#aaa;color:white;}
h2{color:#2c3e50;font-weight:600;margin-bottom:20px;}
.form-buttons{text-align:right;margin-top:20px;}

/* MOBILE */
@media(max-width:768px){
    .sidebar{transform:translateX(-100%);}
    .sidebar.show{transform:translateX(0);box-shadow:2px 0 15px rgba(0,0,0,0.3);}
    .main-content{margin-left:0;padding:20px; padding-top:80px;} /* added top padding for menu button */
    .toggle-btn{
        display:block;
        position:fixed;
        top:20px;
        left:20px;
        z-index:1100;
    }
}
</style>
</head>
<body>

<!-- Overlay -->
<div id="overlay" onclick="closeSidebar()" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.4);z-index:1040;"></div>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <h4 class="text-center mb-4" style="font-weight:600;">Employee Portal</h4>
    <a href="index.php"><i class="bi bi-house-door-fill"></i> Home</a>
    <a href="seminars.php"><i class="bi bi-calendar-event-fill"></i> Seminars</a>
    <a href="examinations.php"><i class="bi bi-pencil-square"></i> Examinations</a>
    <a href="profile.php" class="active"><i class="bi bi-person-fill"></i> Profile</a>
    <a href="../config/logout.php" id="logoutLink"><i class="bi bi-box-arrow-right"></i> Logout</a>
</div>

<!-- Mobile toggle button -->
<button class="btn btn-primary toggle-btn d-md-none" id="menuBtn"><i class="bi bi-list"></i></button>

<div class="main-content">
    <h2>Your Profile</h2>
    <div class="card">
        <div class="card-body">
            <?php if(!empty($errors)) foreach($errors as $err) echo "<div class='alert alert-danger'>$err</div>"; ?>
            <?php if(!empty($success)) echo "<div class='alert alert-success'>$success</div>"; ?>

            <div class="text-center mb-3">
                <img id="photoPreview" src="<?=!empty($user['profile_photo']) ? htmlspecialchars($user['profile_photo']) : 'https://via.placeholder.com/100'?>" 
                alt="Profile Picture" class="rounded-circle" style="width:100px;height:100px;">
            </div>

            <form id="profileForm" method="POST" enctype="multipart/form-data">
                <div class="mb-3 text-center">
                    <input type="file" name="profile_photo" id="profile_photo" accept="image/*" style="display:none;">
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('profile_photo').click()">Change Photo</button>
                </div>

                <div class="mb-3"><label class="form-label">Full Name</label><input type="text" class="form-control" name="full_name" value="<?=htmlspecialchars($user['full_name'])?>" required></div>
                <div class="mb-3"><label class="form-label">Email</label><input type="email" class="form-control" name="email" value="<?=htmlspecialchars($user['email'])?>" required></div>
                <div class="mb-3"><label class="form-label">Branch</label><select name="branch" class="form-control" required>
                    <option value="" disabled>Select branch</option>
                    <?php foreach($branches as $b): ?>
                        <option value="<?=htmlspecialchars($b)?>" <?=($user['branch']==$b?'selected':'')?>><?=$b?></option>
                    <?php endforeach; ?>
                </select></div>
                <div class="mb-3"><label class="form-label">Position</label><select name="position" class="form-control" required>
                    <option value="" disabled>Select position</option>
                    <?php foreach($positions as $p): ?>
                        <option value="<?=htmlspecialchars($p)?>" <?=($user['position']==$p?'selected':'')?>><?=$p?></option>
                    <?php endforeach; ?>
                </select></div>
                <div class="mb-3"><label class="form-label">Username</label><input type="text" class="form-control" name="username" value="<?=htmlspecialchars($user['username'])?>" required></div>
                <div class="mb-3"><label class="form-label">New Password</label><input type="password" class="form-control" name="password" placeholder="Leave blank to keep current"></div>
                <div class="mb-3"><label class="form-label">Confirm Password</label><input type="password" class="form-control" name="confirm_password" placeholder="Leave blank to keep current"></div>

                <div class="form-buttons">
                    <button type="button" class="btn btn-secondary" onclick="discardChanges()">Discard</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('overlay');
const menuBtn = document.getElementById('menuBtn');

menuBtn.addEventListener('click', toggleSidebar);
function toggleSidebar(){
    sidebar.classList.toggle('show');
    overlay.style.display = sidebar.classList.contains('show') ? 'block' : 'none';
    menuBtn.style.display = sidebar.classList.contains('show') ? 'none' : 'block';
}
function closeSidebar(){
    sidebar.classList.remove('show');
    overlay.style.display='none';
    menuBtn.style.display='block';
}

// Preview profile photo
const profilePhotoInput = document.getElementById('profile_photo');
const photoPreview = document.getElementById('photoPreview');
profilePhotoInput.addEventListener('change', function(){
    const file = this.files[0];
    if(file){
        const reader = new FileReader();
        reader.onload = function(e){ photoPreview.src = e.target.result; }
        reader.readAsDataURL(file);
    }
});

// Discard changes
function discardChanges(){
    document.getElementById('profileForm').reset();
    photoPreview.src = "<?=!empty($user['profile_photo']) ? htmlspecialchars($user['profile_photo']) : 'https://via.placeholder.com/100'?>";
}
</script>

</body>
</html>
