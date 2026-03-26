<?php
require_once __DIR__ . '/../config/auth_hr.php';
require_once __DIR__ . '/../config/db.php';

$user_id = $_SESSION['user_id'];

$stmtUser = $pdo->prepare("SELECT full_name, position FROM users WHERE id=?");
$stmtUser->execute([$user_id]);
$user = $stmtUser->fetch();

$avatarInitials = implode('', array_map(fn($w)=>strtoupper($w[0]), explode(" ", $user['full_name'] ?? "Admin User")));

/* ==========================
HANDLE ACTIONS
========================== */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if(isset($_POST['approve_selected'])){
        $selectedUsers = $_POST['selected_users'] ?? [];

        if(!empty($selectedUsers)){
            $inQuery = implode(',', array_fill(0,count($selectedUsers),'?'));
            $stmt = $pdo->prepare("UPDATE users SET status='Approved' WHERE employee_number IN ($inQuery) AND role='Employee'");
            $stmt->execute($selectedUsers);
        }

        header("Location: manage_users.php");
        exit();
    }

    if(isset($_POST['approve_user'])){
        $uid=$_POST['approve_user'];
        $stmt=$pdo->prepare("UPDATE users SET status='Approved' WHERE employee_number=? AND role='Employee'");
        $stmt->execute([$uid]);
        header("Location: manage_users.php");
        exit();
    }

    if(isset($_POST['reject_user'])){
        $uid=$_POST['reject_user'];
        $stmt=$pdo->prepare("UPDATE users SET status='Rejected' WHERE employee_number=? AND role='Employee'");
        $stmt->execute([$uid]);
        header("Location: manage_users.php");
        exit();
    }

}

/* ==========================
LOAD EMPLOYEES WITH PAGINATION
========================== */

$statusFilter=$_GET['status'] ?? '';

// Pagination
$perPage = 5;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $perPage;

// Count total users for pagination
$countQuery = "SELECT COUNT(*) FROM users WHERE role='Employee'";
$countParams = [];
if(in_array($statusFilter,['Approved','Pending','Rejected','Inactive'])){
    $countQuery .= " AND status=?";
    $countParams[] = $statusFilter;
}
$stmtCount = $pdo->prepare($countQuery);
$stmtCount->execute($countParams);
$totalUsers = $stmtCount->fetchColumn();
$totalPages = ceil($totalUsers / $perPage);

// Fetch users for current page
$query="SELECT * FROM users WHERE role='Employee'";
$params=[];

if(in_array($statusFilter,['Approved','Pending','Rejected','Inactive'])){
    $query.=" AND status=?";
    $params[]=$statusFilter;
}

$query.=" ORDER BY employee_number ASC LIMIT ? OFFSET ?";
$params[] = $perPage;
$params[] = $offset;

$stmt=$pdo->prepare($query);
$stmt->execute($params);
$users=$stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Users</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
:root{
--primary:#4f46e5;
--secondary:#818cf8;
--bg:#f3f4f6;
--sidebar:#1f2937;
--danger:#ef4444;
--success:#10b981;
}
*{margin:0;padding:0;box-sizing:border-box;font-family:Segoe UI;}
body{display:flex;background:var(--bg);height:100vh;}
.sidebar{width:260px;background:var(--sidebar);color:white;padding:20px;display:flex;flex-direction:column;}
.logo{font-size:22px;font-weight:bold;margin-bottom:40px;text-align:center;color:var(--secondary);}
.nav-links{list-style:none;flex-grow:1;}
.nav-links li{margin-bottom:10px;}
.nav-links a{color:#9ca3af;text-decoration:none;display:flex;gap:10px;padding:12px;border-radius:8px;}
.nav-links a:hover,.nav-links a.active{background:var(--primary);color:white;}
.main-content{flex:1;display:flex;flex-direction:column;}
.content-area{padding:30px;}
.section-title{margin-bottom:20px;}
.table-container{background:white;padding:20px;border-radius:10px;box-shadow:0 3px 6px rgba(0,0,0,0.1);}
table{width:100%;border-collapse:collapse;}
th,td{padding:12px;border-bottom:1px solid #eee;text-align:left;}
th{font-size:13px;color:#6b7280;}
.status{padding:4px 10px;border-radius:20px;font-size:12px;font-weight:bold;}
.active{background:#d1fae5;color:#065f46;}
.pending{background:#fef3c7;color:#92400e;}
.rejected{background:#ef4444;color:white;}
.inactive{background:#e5e7eb;}
.btn{padding:6px 10px;border:none;border-radius:6px;font-size:13px;cursor:pointer;text-decoration:none;color:white;}
.btn-primary{background:var(--primary);}
.btn-success{background:var(--success);}
.btn-danger{background:var(--danger);}
.actions{display:flex;gap:6px;}
.pagination{margin-top:15px; display:flex; justify-content:center; gap:6px; flex-wrap:wrap;}
.pagination a{padding:6px 10px; border-radius:6px; text-decoration:none;}
.pagination a.active{background:var(--primary); color:white;}
.pagination a.inactive{background:#e5e7eb; color:#1f2937;}
</style>

<script>
function toggleAll(source){
    let checkboxes=document.getElementsByName('selected_users[]');
    for(let i=0;i<checkboxes.length;i++){
        checkboxes[i].checked=source.checked;
    }
}
function confirmApprove(){
    return confirm("Approve selected employees?");
}
</script>

</head>
<body>
<nav class="sidebar">
    <div class="logo"><i class="fa-solid fa-users-rectangle"></i> HR Portal</div>
    <ul class="nav-links">
        <li><a href="index.php"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
        <li><a href="manage_users.php" class="active"><i class="fa-solid fa-user-gear"></i> Manage Users</a></li>
        <li><a href="manage_exams.php"><i class="fa-solid fa-file-circle-question"></i> Manage Exams</a></li>
        <li><a href="essay_grading.php"><i class="fa-solid fa-file-lines"></i> Essay Grading</a></li>
        <li><a href="results.php"><i class="fa-solid fa-square-poll-vertical"></i> Results</a></li>
        <li><a href="profile.php"><i class="fa-solid fa-user"></i> My Profile</a></li>
        <a href="../config/logout.php" class="logout-btn" onclick="return confirmLogout();"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </ul>
    <div class="profile-footer" style="margin-top:auto; padding-top:20px; border-top:1px solid rgba(255,255,255,0.2); display:flex; align-items:center; gap:10px;">
        <div class="avatar" style="width:40px; height:40px; font-size:16px; flex-shrink:0; display:flex; align-items:center; justify-content:center; background-color:#818cf8; color:white; border-radius:50%;">
            <?= $avatarInitials ?>
        </div>
        <div style="color:white;">
            <div style="font-size:14px; font-weight:600;"><?= htmlspecialchars($user['full_name'] ?? 'Admin User') ?></div>
            <div style="font-size:12px; color:#d1d5db;"><?= htmlspecialchars($user['position'] ?? 'HR Manager') ?></div>
        </div>
    </div>
</nav>

<div class="main-content">
<div class="content-area">

<h2 class="section-title">Manage Users</h2>

<form method="POST" onsubmit="return confirmApprove();">

<button type="submit" name="approve_selected" class="btn btn-success">Approve Selected</button>

<div class="table-container">
<table>
<thead>
<tr>
<th><input type="checkbox" onclick="toggleAll(this)"></th>
<th>Employee #</th>
<th>Name</th>
<th>Email</th>
<th>Role</th>
<th>Status</th>
<th>Actions</th>
</tr>
</thead>
<tbody>
<?php foreach($users as $u): ?>
<tr>
<td><input type="checkbox" name="selected_users[]" value="<?= $u['employee_number']; ?>"></td>
<td><?= htmlspecialchars($u['employee_number']); ?></td>
<td><?= htmlspecialchars($u['full_name']); ?></td>
<td><?= htmlspecialchars($u['email']); ?></td>
<td><?= htmlspecialchars($u['role']); ?></td>
<td>
<?php
if($u['status']=='Approved') echo '<span class="status active">Approved</span>';
elseif($u['status']=='Pending') echo '<span class="status pending">Pending</span>';
elseif($u['status']=='Inactive') echo '<span class="status inactive">Inactive</span>';
else echo '<span class="status rejected">Rejected</span>';
?>
</td>
<td class="actions">
<?php if($u['status']=='Pending'): ?>
<button type="submit" name="approve_user" value="<?= $u['employee_number']; ?>" class="btn btn-success">Approve</button>
<button type="submit" name="reject_user" value="<?= $u['employee_number']; ?>" class="btn btn-danger">Reject</button>
<?php else: ?>
<a href="edit_user.php?id=<?= $u['id']; ?>" class="btn btn-primary">Edit</a>
<a href="delete_user.php?id=<?= $u['id']; ?>" class="btn btn-danger" onclick="return confirm('Set user inactive?')">Delete</a>
<?php endif; ?>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<!-- Pagination -->
<div class="pagination">
<?php for($i=1; $i<=$totalPages; $i++): ?>
<a href="?page=<?= $i ?><?= $statusFilter ? '&status='.$statusFilter : '' ?>" class="<?= $i==$page ? 'active' : 'inactive' ?>"><?= $i ?></a>
<?php endfor; ?>
</div>

</div>
</form>

</div>
</div>
</body>
</html>