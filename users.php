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

// --- 2. MANUAL BAN LOGIC (POST) ---
if (isset($_POST['manual_ban'])) {
    $target_id = mysqli_real_escape_string($conn, $_POST['ban_user_id']);
    $duration = $_POST['ban_duration']; // '1_day', '1_week', '1_month', 'permanent'
    $reason = mysqli_real_escape_string($conn, $_POST['ban_reason']);
    
    // Calculate End Date
    $ban_end_date = "NULL"; // Default for permanent
    if ($duration == '1_day') {
        $ban_end_date = "'" . date('Y-m-d H:i:s', strtotime('+1 day')) . "'";
    } elseif ($duration == '1_week') {
        $ban_end_date = "'" . date('Y-m-d H:i:s', strtotime('+1 week')) . "'";
    } elseif ($duration == '1_month') {
        $ban_end_date = "'" . date('Y-m-d H:i:s', strtotime('+1 month')) . "'";
    }

    $sql = "UPDATE users SET 
            account_status = 'restricted', 
            ban_end_date = $ban_end_date, 
            ban_reason = '$reason' 
            WHERE user_id = '$target_id'";

    if (mysqli_query($conn, $sql)) {
        $msg = "User successfully banned for " . str_replace('_', ' ', $duration) . ".";
    } else {
        $error = "Failed to ban user: " . mysqli_error($conn);
    }
}

// --- 3. GET ACTIONS (Unban, Reset, Delete) ---
if (isset($_GET['action']) && isset($_GET['id'])) {
    $target_id = mysqli_real_escape_string($conn, $_GET['id']);
    $action = $_GET['action'];
    
    if ($target_id == $_SESSION['user_id']) {
        $error = "You cannot perform actions on your own account.";
    } else {
        if ($action == 'unban') {
            // Unban: Set active, clear ban date/reason. 
            // Note: We do NOT reset points here automatically unless you want to.
            mysqli_query($conn, "UPDATE users SET account_status='active', ban_end_date=NULL, ban_reason=NULL WHERE user_id='$target_id'");
            $msg = "User has been unbanned and reactivated.";
        } elseif ($action == 'reset_points') {
            mysqli_query($conn, "UPDATE users SET penalty_points=0 WHERE user_id='$target_id'");
            $msg = "User's penalty points have been reset to 0.";
        } elseif ($action == 'delete') {
            $timestamp = time();
            $archived_id = "DEL_" . rand(100,999) . "_" . $timestamp; 
            $archived_id = substr($archived_id, 0, 20); 

            $u_query = mysqli_query($conn, "SELECT email FROM users WHERE user_id='$target_id'");
            $u_data = mysqli_fetch_assoc($u_query);
            $old_email = $u_data['email'];
            $archived_email = "DEL_" . $timestamp . "_" . $old_email;

            $sql_del = "UPDATE users SET 
                        account_status = 'deleted', 
                        id_number = '$archived_id', 
                        email = '$archived_email',
                        password = '' 
                        WHERE user_id='$target_id'";
            
            if (mysqli_query($conn, $sql_del)) {
                $msg = "User deleted successfully.";
            } else {
                $error = "Database Error: " . mysqli_error($conn);
            }
        }
    }
}

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
                        <input type="text" name="search" class="form-control" placeholder="Search by Name or ID Number..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-2"><button type="submit" class="btn btn-primary w-100">Search</button></div>
                    <div class="col-md-2">
                        <?php if($search != ''): ?><a href="users.php" class="btn btn-secondary w-100">Clear</a><?php endif; ?>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ID Number</th> <th>Full Name</th> <th>Email</th> <th>Role</th> <th>Points</th> <th>Status</th>
                                <th style="width: 250px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $sql = "SELECT * FROM users WHERE role != 'admin' AND account_status != 'deleted'";
                            if ($search != '') {
                                $safe_search = mysqli_real_escape_string($conn, $search);
                                $sql .= " AND (full_name LIKE '%$safe_search%' OR id_number LIKE '%$safe_search%')";
                            }
                            $sql .= " ORDER BY role ASC, penalty_points DESC"; 
                            $result = mysqli_query($conn, $sql);

                            if (mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    $points = $row['penalty_points'];
                                    $status = $row['account_status'];
                                    $row_class = "";
                                    $status_badge = "success";
                                    $status_text = "Active";

                                    // Visual Logic
                                    if ($status == 'restricted') {
                                        $row_class = "table-warning";
                                        $status_badge = "warning text-dark";
                                        $status_text = "BANNED";
                                    } elseif ($points >= 60) {
                                        $row_class = "table-danger"; 
                                        $status_badge = "danger";
                                        $status_text = "AUTO-RESTRICTED";
                                    }
                                    
                                    $role_badge = ($row['role'] == 'student_assistant') ? 'primary' : 'secondary';
                            ?>
                                <tr class="<?php echo $row_class; ?>">
                                    <td><?php echo $row['id_number']; ?></td>
                                    <td class="fw-bold"><?php echo $row['full_name']; ?></td>
                                    <td><?php echo $row['email']; ?></td>
                                    <td><span class="badge bg-<?php echo $role_badge; ?>"><?php echo strtoupper($row['role']); ?></span></td>
                                    <td class="fw-bold text-center"><?php echo $points; ?></td>
                                    <td><span class="badge bg-<?php echo $status_badge; ?>"><?php echo $status_text; ?></span></td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            
                                            <?php if ($status == 'restricted') { ?>
                                                <a href="users.php?action=unban&id=<?php echo $row['user_id']; ?>" 
                                                   class="btn btn-success btn-sm"
                                                   onclick="return confirm('Lift the ban for this user?');"
                                                   title="Unban User">
                                                    <i class="bi bi-check-circle-fill"></i> Unban
                                                </a>
                                            <?php } else { ?>
                                                <button class="btn btn-outline-warning btn-sm" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#banModal"
                                                        data-userid="<?php echo $row['user_id']; ?>"
                                                        data-name="<?php echo $row['full_name']; ?>" title="Ban User">
                                                    <i class="bi bi-slash-circle"></i> Ban
                                                </button>
                                            <?php } ?>

                                            <a href="users.php?action=reset_points&id=<?php echo $row['user_id']; ?>" 
                                               class="btn btn-outline-info btn-sm"
                                               onclick="return confirm('Reset points to 0?');" title="Reset Points">
                                               <i class="bi bi-arrow-counterclockwise"></i>
                                            </a>

                                            <a href="users.php?action=delete&id=<?php echo $row['user_id']; ?>" 
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirm('Delete this user?');" title="Delete">
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
                <form action="users.php" method="POST">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title"><i class="bi bi-slash-circle"></i> Manual Ban User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Banning: <strong id="banNameDisplay" class="fs-5"></strong></p>
                        <input type="hidden" name="ban_user_id" id="banIdInput">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Ban Duration:</label>
                            <select name="ban_duration" class="form-select" required>
                                <option value="1_day">1 Day</option>
                                <option value="1_week">1 Week</option>
                                <option value="1_month">1 Month</option>
                                <option value="permanent">Permanent / Indefinite</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Reason / Message to Student:</label>
                            <textarea name="ban_reason" class="form-control" rows="3" placeholder="Ex. Disrespectful behavior in the lab." required></textarea>
                        </div>

                        <div class="alert alert-warning small">
                            <i class="bi bi-info-circle"></i> The student will see this message on their dashboard and cannot borrow tools.
                        </div>
                        
                        <input type="hidden" name="manual_ban" value="true">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Confirm Ban</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="createSAModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Add Student Assistant</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3"><label>Name</label><input type="text" name="full_name" class="form-control" required></div>
                        <div class="mb-3"><label>Password</label><input type="text" name="password" class="form-control" required></div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="create_sa" class="btn btn-primary">Create</button>
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