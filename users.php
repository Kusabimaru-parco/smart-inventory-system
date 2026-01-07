<?php 
session_start();
include "db_conn.php";

// STRICT SECURITY: Only 'admin' can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

// --- 1. CREATE SA LOGIC ---
if (isset($_POST['create_sa'])) {
    $name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $pass = mysqli_real_escape_string($conn, $_POST['password']);
    
    // Auto-generate SA ID
    $count_sql = "SELECT COUNT(*) as total FROM users WHERE role='student_assistant'";
    $res = mysqli_query($conn, $count_sql);
    $data = mysqli_fetch_assoc($res);
    $next_id = $data['total'] + 1;
    $sa_id = "SA-" . str_pad($next_id, 3, '0', STR_PAD_LEFT); 

    $check = mysqli_query($conn, "SELECT * FROM users WHERE id_number='$sa_id'");
    if (mysqli_num_rows($check) > 0) { $sa_id = "SA-" . rand(100, 999); }

    $sql = "INSERT INTO users (id_number, full_name, password, role, account_status) 
            VALUES ('$sa_id', '$name', '$pass', 'student_assistant', 'active')";
    
    if (mysqli_query($conn, $sql)) {
        $msg = "✅ New SA Created! Login ID: <strong>$sa_id</strong>";
    } else {
        $error = "❌ Failed to create SA: " . mysqli_error($conn);
    }
}

// --- 2. USER ACTIONS ---
if (isset($_GET['action']) && isset($_GET['id'])) {
    $target_id = $_GET['id'];
    $action = $_GET['action'];
    
    // Prevent Admin from acting on themselves
    if ($target_id == $_SESSION['user_id']) {
        $error = "You cannot perform actions on your own account.";
    } else {
        if ($action == 'ban') {
            mysqli_query($conn, "UPDATE users SET account_status='restricted' WHERE user_id='$target_id'");
            $msg = "User has been banned.";
        } elseif ($action == 'unban') {
            mysqli_query($conn, "UPDATE users SET account_status='active', penalty_points=0 WHERE user_id='$target_id'");
            $msg = "User activated and points reset.";
        } elseif ($action == 'reset_pass') {
            $default_pass = '12345'; 
            mysqli_query($conn, "UPDATE users SET password='$default_pass' WHERE user_id='$target_id'");
            $msg = "Password reset to '12345'.";
        } elseif ($action == 'delete') {
            // --- SOFT DELETE LOGIC ---
            // 1. Check if they have UNRETURNED tools (Safety Check)
            $check_active = mysqli_query($conn, "SELECT * FROM transactions WHERE user_id='$target_id' AND status='Borrowed'");
            
            if (mysqli_num_rows($check_active) > 0) {
                $error = "❌ Cannot delete user: They still have unreturned tools.";
            } else {
                // 2. Perform Soft Delete (Archive)
                // We change ID so the original ID can be reused, remove password, set status to 'deleted'
                // This keeps the row for history reports, but hides it from management.
                $archived_id = "DEL_" . time() . "_" . rand(10,99); 
                
                $sql_del = "UPDATE users SET 
                            account_status = 'deleted', 
                            id_number = '$archived_id', 
                            password = '' 
                            WHERE user_id='$target_id'";
                
                if (mysqli_query($conn, $sql_del)) {
                    $msg = "User deleted successfully (Account archived to preserve history).";
                } else {
                    $error = "Database Error: " . mysqli_error($conn);
                }
            }
        }
    }
}

// --- SEARCH LOGIC ---
$search = isset($_GET['search']) ? $_GET['search'] : '';
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body>

    <nav class="navbar navbar-dark bg-dark px-4">
        <span class="navbar-brand mb-0 h1">User Management</span>
        <div>
            <button class="btn btn-primary btn-sm me-2" data-bs-toggle="modal" data-bs-target="#createSAModal">
                <i class="bi bi-person-plus-fill"></i> Add Student Assistant
            </button>
            <a href="dashboard.php" class="btn btn-outline-light btn-sm me-2">Dashboard</a>
            <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
        </div>
    </nav>

    <div class="container mt-5">
        
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>👥 Student & Faculty Directory</h3>
        </div>
        
        <?php if (isset($msg)) { ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo $msg; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php } ?>

        <?php if (isset($error)) { ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php } ?>

        <div class="card shadow-sm">
            <div class="card-body">
                
                <form method="GET" class="row g-2 mb-4">
                    <div class="col-md-5">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Search by Name or ID Number..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Search</button>
                    </div>
                    <div class="col-md-2">
                        <?php if($search != ''): ?>
                            <a href="users.php" class="btn btn-secondary w-100">Clear</a>
                        <?php endif; ?>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ID Number</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Points</th>
                                <th>Status</th>
                                <th style="width: 250px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            // HIDE Deleted Users and Admins from this list
                            $sql = "SELECT * FROM users WHERE role != 'admin' AND account_status != 'deleted'";

                            if ($search != '') {
                                $safe_search = mysqli_real_escape_string($conn, $search);
                                $sql .= " AND (full_name LIKE '%$safe_search%' OR id_number LIKE '%$safe_search%')";
                            }

                            $sql .= " ORDER BY role ASC, penalty_points DESC"; 
                            $result = mysqli_query($conn, $sql);

                            if (mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    
                                    // Status Logic
                                    $points = $row['penalty_points'];
                                    $ban_end = $row['ban_end_date'];
                                    $current_date = date('Y-m-d H:i:s');
                                    $row_class = "";
                                    $status_badge = "success";
                                    $status_text = "Active";

                                    if ($points >= 60) {
                                        $row_class = "table-danger"; 
                                        $status_badge = "danger";
                                        $status_text = "RESTRICTED";
                                    } elseif (!empty($ban_end) && $ban_end > $current_date) {
                                        $row_class = "table-warning"; 
                                        $status_badge = "warning text-dark";
                                        $status_text = "SUSPENDED";
                                    }
                                    
                                    $role_badge = ($row['role'] == 'student_assistant') ? 'primary' : 'secondary';
                            ?>
                                <tr class="<?php echo $row_class; ?>">
                                    <td><?php echo $row['id_number']; ?></td>
                                    <td class="fw-bold"><?php echo $row['full_name']; ?></td>
                                    <td><?php echo !empty($row['email']) ? $row['email'] : '<span class="text-muted">-</span>'; ?></td>
                                    <td><span class="badge bg-<?php echo $role_badge; ?>"><?php echo strtoupper($row['role']); ?></span></td>
                                    <td class="fw-bold text-center"><?php echo $points; ?></td>
                                    <td><span class="badge bg-<?php echo $status_badge; ?>"><?php echo $status_text; ?></span></td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="users.php?action=reset_pass&id=<?php echo $row['user_id']; ?>" 
                                               class="btn btn-outline-secondary btn-sm"
                                               onclick="return confirm('Reset password to 12345?');" title="Reset Password">
                                               <i class="bi bi-key"></i>
                                            </a>

                                            <button class="btn btn-outline-warning btn-sm" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#banModal"
                                                    data-userid="<?php echo $row['user_id']; ?>"
                                                    data-name="<?php echo $row['full_name']; ?>" title="Ban User">
                                                <i class="bi bi-slash-circle"></i>
                                            </button>

                                            <a href="users.php?action=delete&id=<?php echo $row['user_id']; ?>" 
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirm('⚠️ DELETE ACCOUNT?\n\nThe user will be removed from this list, but their past transaction history will be KEPT for reports.\n\nContinue?');"
                                               title="Delete User">
                                               <i class="bi bi-trash-fill"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php 
                                }
                            } else {
                                echo "<tr><td colspan='7' class='text-center py-4 text-muted'>No users found.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="banModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="users.php" method="GET">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title">Suspend User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Suspending: <strong id="banNameDisplay"></strong></p>
                        <input type="hidden" name="id" id="banIdInput">
                        <input type="hidden" name="action" value="ban">
                        
                        <p class="text-muted small">This will set the user status to 'Restricted'.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Confirm Suspension</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="createSAModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-person-plus"></i> Add Student Assistant</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="full_name" class="form-control" placeholder="Ex. Juan Dela Cruz" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="text" name="password" class="form-control" placeholder="Ex. sa_pass123" required>
                        </div>
                        <div class="alert alert-info small mb-0">
                            <i class="bi bi-info-circle"></i> Login ID (e.g., SA-001) will be auto-generated.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="create_sa" class="btn btn-primary">Create Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        var banModal = document.getElementById('banModal');
        banModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var userId = button.getAttribute('data-userid');
            var userName = button.getAttribute('data-name');
            
            document.getElementById('banIdInput').value = userId;
            document.getElementById('banNameDisplay').textContent = userName;
        });
    </script>
</body>
</html>