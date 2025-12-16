<?php 
session_start();
include "db_conn.php";

// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

// --- SEARCH LOGIC ---
$search = isset($_GET['search']) ? $_GET['search'] : '';
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

    <nav class="navbar navbar-dark bg-dark px-4">
        <span class="navbar-brand mb-0 h1">User Management</span>
        <div>
            <a href="dashboard.php" class="btn btn-outline-light btn-sm me-2">Dashboard</a>
            <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
        </div>
    </nav>

    <div class="container mt-5">
        
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>👥 Student & Faculty Directory</h3>
        </div>
        
        <?php if (isset($_GET['msg'])) { ?>
            <div class="alert alert-success"><?php echo $_GET['msg']; ?></div>
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

                <table class="table table-hover table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>ID Number</th>
                            <th>Full Name</th>
                            <th>Role</th>
                            <th>Penalty Points</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        // Base Query
                        $sql = "SELECT * FROM users WHERE role != 'admin'";

                        // Add Filter if Searching
                        if ($search != '') {
                            // We use real_escape_string to prevent SQL errors with quotes
                            $safe_search = mysqli_real_escape_string($conn, $search);
                            $sql .= " AND (full_name LIKE '%$safe_search%' OR id_number LIKE '%$safe_search%')";
                        }

                        $sql .= " ORDER BY penalty_points DESC";
                        $result = mysqli_query($conn, $sql);

                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                
                                // --- STATUS LOGIC START ---
                                $points = $row['penalty_points'];
                                $ban_end = $row['ban_end_date'];
                                $current_date = date('Y-m-d H:i:s');

                                $row_class = "";
                                $status_badge = "success";
                                $status_text = "Active";

                                // 1. Check for 60-Point Restriction
                                if ($points >= 60) {
                                    $row_class = "table-danger"; 
                                    $status_badge = "danger";
                                    $status_text = "RESTRICTED (Points)";
                                } 
                                // 2. Check for Manual Ban (Time-based)
                                elseif (!empty($ban_end) && $ban_end > $current_date) {
                                    $row_class = "table-warning"; 
                                    $status_badge = "warning text-dark";
                                    $status_text = "SUSPENDED (Until " . date('M d', strtotime($ban_end)) . ")";
                                }
                                // --- STATUS LOGIC END ---
                        ?>
                            <tr class="<?php echo $row_class; ?>">
                                <td><?php echo $row['id_number']; ?></td>
                                <td><?php echo $row['full_name']; ?></td>
                                <td><?php echo ucfirst($row['role']); ?></td>
                                <td class="fw-bold text-center"><?php echo $points; ?></td>
                                <td><span class="badge bg-<?php echo $status_badge; ?>"><?php echo $status_text; ?></span></td>
                                <td>
                                    <a href="user_action.php?id=<?php echo $row['user_id']; ?>&action=reset" 
                                       class="btn btn-outline-primary btn-sm"
                                       onclick="return confirm('Are you sure you want to reset this account\'s points? This will remove ALL bans (Points & Time).');">
                                       🔄 Reset
                                    </a>

                                    <button class="btn btn-outline-danger btn-sm" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#banModal"
                                            data-userid="<?php echo $row['user_id']; ?>"
                                            data-name="<?php echo $row['full_name']; ?>">
                                       ⛔ Ban
                                    </button>
                                </td>
                            </tr>
                        <?php 
                            }
                        } else {
                            echo "<tr><td colspan='6' class='text-center py-4 text-muted'>No users found matching '$search'.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="banModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="user_action.php" method="POST">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">Temporary Ban User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Banning Student: <strong id="banNameDisplay"></strong></p>
                        <input type="hidden" name="user_id" id="banIdInput">
                        
                        <div class="mb-3">
                            <label class="form-label">Duration</label>
                            <select name="ban_days" class="form-select">
                                <option value="1">1 Day</option>
                                <option value="3">3 Days</option>
                                <option value="7">1 Week</option>
                                <option value="30">1 Month</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Reason for Ban</label>
                            <textarea name="ban_reason" class="form-control" rows="3" placeholder="e.g. Misconduct in laboratory..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="ban_user_btn" class="btn btn-danger">Confirm Ban</button>
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