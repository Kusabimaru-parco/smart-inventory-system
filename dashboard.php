<?php 
session_start();
include "db_conn.php";

// Security Check
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// --- STUDENT LOGIC: Get Points & Ban Status ---
$my_points = 0;
$bar_color = "success";
$bar_width = "0%";
$account_status = "active";
$ban_end = "";
$ban_reason = "";

if ($role == 'student') {
    $p_sql = "SELECT penalty_points, account_status, ban_end_date, ban_reason FROM users WHERE user_id = '$user_id'";
    $p_res = mysqli_query($conn, $p_sql);
    
    if ($p_res && mysqli_num_rows($p_res) > 0) {
        $p_data = mysqli_fetch_assoc($p_res);
        $my_points = $p_data['penalty_points'];
        $account_status = $p_data['account_status'];
        $ban_end = $p_data['ban_end_date'];
        $ban_reason = $p_data['ban_reason'];
    }

    // Bar Logic
    $max_points = 60;
    if($my_points > 60) $my_points = 60;
    $percentage = ($my_points / $max_points) * 100;
    $bar_width = $percentage . "%";

    if ($my_points >= 50) { $bar_color = "danger"; } 
    elseif ($my_points >= 30) { $bar_color = "warning"; } 
    else { $bar_color = "success"; }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Smart Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <link rel="stylesheet" href="assets/css/message_request.css">
    
</head>
<body class="bg-light">

    <nav class="navbar navbar-dark bg-dark px-4">
        <span class="navbar-brand mb-0 h1">🛠️ Smart Inventory</span>
        <div class="d-flex align-items-center">
            
            <?php if ($role == 'admin') { ?>
                <button id="janeToggleBtn" class="btn btn-outline-primary btn-sm me-3" onclick="Jane.toggle()">
                    <i id="janeIcon" class="bi bi-mic-fill"></i> Jane: <span id="janeStatus">ON</span>
                </button>
            <?php } ?>

            <span class="text-white me-3">Welcome, <?php echo $_SESSION['name']; ?></span>
            <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
        </div>
    </nav>

    <div class="container mt-4">

        <?php if ($role == 'admin') { ?> 
            
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="card text-center shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title text-primary"><i class="bi bi-upc-scan"></i> Scanner</h5>
                            <p class="card-text">Issue or Return tools via barcode.</p>
                            <a href="scan_page.php" class="btn btn-primary w-100">Open Scanner</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center shadow-sm h-100 position-relative" id="requestCard">
                        <div class="card-body">
                            <h5 class="card-title text-warning">
                                <i class="bi bi-envelope"></i> Requests
                                <span id="reqBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display:none;">
                                    0
                                    <span class="visually-hidden">unread messages</span>
                                </span>
                            </h5>
                            <p class="card-text">Approve student borrow requests.</p>
                            <a href="admin_requests.php" class="btn btn-warning w-100">Manage Requests</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title text-success"><i class="bi bi-tools"></i> Inventory</h5>
                            <p class="card-text">Add or edit tool details.</p>
                            <a href="inventory.php" class="btn btn-success w-100">View Catalog</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title text-danger"><i class="bi bi-graph-up"></i> Reports</h5>
                            <p class="card-text">View utilization analytics.</p>
                            <a href="reports.php" class="btn btn-danger w-100">View Analytics</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title text-dark"><i class="bi bi-people-fill"></i> Users</h5>
                            <p class="card-text">Manage bans and penalties.</p>
                            <a href="users.php" class="btn btn-outline-dark w-100">Manage Users</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4"> 
                    <div class="card text-center shadow-sm h-100 border-warning">
                        <div class="card-body">
                            <h5 class="card-title text-warning"><i class="bi bi-bell"></i> Automation</h5>
                            <p class="card-text">Trigger email reminders manually.</p>
                            <a href="cron_email.php" target="_blank" class="btn btn-warning w-100">Run Reminders</a>
                        </div>
                    </div>
                </div>
            </div> 

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-person-badge"></i> Currently Borrowed Tools (Active)</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Borrower Name</th>
                                <th>Tool Name</th>
                                <th>Barcode</th>
                                <th>Due Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $sql = "SELECT u.full_name, t.tool_name, t.barcode, tr.return_date, tr.status 
                                    FROM transactions tr
                                    JOIN users u ON tr.user_id = u.user_id
                                    JOIN tools t ON tr.tool_id = t.tool_id
                                    WHERE tr.status = 'Borrowed'";
                            $result = mysqli_query($conn, $sql);

                            if ($result && mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    $is_overdue = (date('Y-m-d') > $row['return_date']);
                                    $badge = $is_overdue ? 'danger' : 'warning';
                                    $status_text = $is_overdue ? 'Overdue!' : 'Borrowed';
                            ?>
                                <tr>
                                    <td><?php echo $row['full_name']; ?></td>
                                    <td><?php echo $row['tool_name']; ?></td>
                                    <td><code><?php echo $row['barcode']; ?></code></td>
                                    <td><?php echo $row['return_date']; ?></td>
                                    <td><span class="badge bg-<?php echo $badge; ?>"><?php echo $status_text; ?></span></td>
                                </tr>
                            <?php 
                                }
                            } else {
                                echo "<tr><td colspan='5' class='text-center text-muted'>No tools are currently being used.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> Today's Successful Transactions</h5>
                    <a href="admin_history.php" target="_blank" class="btn btn-sm btn-light text-secondary fw-bold">View Full History</a>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Tool</th>
                                <th>Student</th>
                                <th>Time Processed</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $today = date('Y-m-d');
                            $sql = "SELECT tr.status, t.tool_name, u.full_name, tr.actual_return_date, tr.date_requested 
                                    FROM transactions tr
                                    JOIN users u ON tr.user_id = u.user_id
                                    JOIN tools t ON tr.tool_id = t.tool_id
                                    WHERE 
                                    (
                                        (tr.status = 'Borrowed' AND DATE(tr.date_requested) = '$today') 
                                        OR 
                                        (tr.status = 'Returned' AND DATE(tr.actual_return_date) = '$today')
                                    )
                                    ORDER BY tr.transaction_id DESC"; 
                            
                            $result = mysqli_query($conn, $sql);

                            if ($result && mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    $type = ($row['status'] == 'Returned') ? 'IN (Returned)' : 'OUT (Issued)';
                                    $color = ($row['status'] == 'Returned') ? 'success' : 'primary';
                                    
                                    if ($row['status'] == 'Returned' && !empty($row['actual_return_date'])) {
                                        $display_time = date('h:i A', strtotime($row['actual_return_date']));
                                    } elseif (!empty($row['date_requested'])) {
                                        $display_time = date('h:i A', strtotime($row['date_requested']));
                                    } else {
                                        $display_time = "Today";
                                    }
                            ?>
                                <tr>
                                    <td><span class="badge bg-<?php echo $color; ?>"><?php echo $type; ?></span></td>
                                    <td><?php echo $row['tool_name']; ?></td>
                                    <td><?php echo $row['full_name']; ?></td>
                                    <td><?php echo $display_time; ?></td>
                                </tr>
                            <?php 
                                }
                            } else { 
                                echo "<tr><td colspan='4' class='text-center py-3 text-muted fst-italic'>No successful transactions yet today.</td></tr>"; 
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php } else { ?> 

            <?php if ($account_status == 'restricted' && !empty($ban_end)) { ?>
                <div class="card shadow-sm mb-4 border-danger">
                    <div class="card-body text-center text-danger">
                        <h2 class="display-1"><i class="bi bi-slash-circle"></i></h2>
                        <h4 class="fw-bold">Account Temporarily Suspended</h4>
                        <p class="lead mt-3 text-dark">
                            Access Restricted Until:<br>
                            <strong><?php echo date('F d, Y h:i A', strtotime($ban_end)); ?></strong>
                        </p>
                        <button class="btn btn-outline-danger btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#banReasonModal">
                            Why?
                        </button>
                    </div>
                </div>
            <?php } else { ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-shield-check"></i> Account Standing
                                <button type="button" class="btn btn-link text-decoration-none p-0 ms-2" 
                                        data-bs-toggle="modal" data-bs-target="#penaltyRulesModal">
                                    <i class="bi bi-question-circle-fill text-muted" style="font-size: 1rem;"></i>
                                </button>
                            </h5>
                            <span class="badge bg-<?php echo $bar_color; ?> fs-6">
                                <?php echo $my_points; ?> / 60 Penalty Points
                            </span>
                        </div>
                        <div class="progress" style="height: 25px; background-color: #e9ecef;">
                            <div class="progress-bar bg-<?php echo $bar_color; ?> progress-bar-striped progress-bar-animated" 
                                 role="progressbar" 
                                 style="width: <?php echo $bar_width; ?>; font-weight: bold;">
                                 <?php echo ($my_points > 5) ? $my_points . ' Pts' : ''; ?>
                            </div>
                        </div>
                        <div class="mt-2 text-muted small d-flex justify-content-between">
                            <span>🟢 0-29 (Safe)</span>
                            <span>🟡 30-49 (Warning)</span>
                            <span>🔴 60 (Restricted)</span>
                        </div>
                    </div>
                </div>
            <?php } ?>

            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card text-white bg-primary h-100">
                        <div class="card-body d-flex flex-column justify-content-center text-center">
                            <h3>Need a Tool?</h3>
                            <p>Browse the catalog and make a request.</p>
                            <?php 
                            // Disable button if banned
                            $btn_state = ($account_status == 'restricted') ? 'disabled btn-secondary' : 'btn-light text-primary';
                            ?>
                            <a href="student_catalog.php" class="btn <?php echo $btn_state; ?> fw-bold">View Catalog</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-8 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-warning text-dark fw-bold">
                            <i class="bi bi-hourglass-split"></i> My Currently Borrowed Tools
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <?php 
                                $sql = "SELECT t.tool_name, tr.return_date, tr.status 
                                        FROM transactions tr
                                        JOIN tools t ON tr.tool_id = t.tool_id
                                        WHERE tr.user_id = '$user_id' AND tr.status IN ('Borrowed', 'Approved')";
                                $result = mysqli_query($conn, $sql);
                                if (mysqli_num_rows($result) > 0) {
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        $msg = ($row['status'] == 'Approved') ? "Ready to pick up" : "Due: " . $row['return_date'];
                                ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <?php echo $row['tool_name']; ?>
                                        <span class="badge bg-primary rounded-pill"><?php echo $msg; ?></span>
                                    </li>
                                <?php 
                                    }
                                } else { echo "<p class='text-muted p-2'>You have no active tools.</p>"; }
                                ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-calendar-check"></i> History - <?php echo date('M d, Y'); ?></h5>
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#fullHistoryModal">
                        View All Records
                    </button>
                </div>
                <div class="card-body p-0"> 
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Tool Name</th>
                                    <th>Action</th>
                                    <th>Time</th>
                                    <th class="text-end pe-4">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $today = date('Y-m-d');
                                $sql = "SELECT t.tool_name, t.category, tr.status, tr.borrow_date, tr.actual_return_date, tr.date_requested
                                        FROM transactions tr
                                        JOIN tools t ON tr.tool_id = t.tool_id
                                        WHERE tr.user_id = '$user_id' 
                                        AND ((tr.status = 'Borrowed' AND DATE(tr.date_requested) = '$today') 
                                        OR (tr.status IN ('Returned', 'Declined') AND DATE(tr.actual_return_date) = '$today'))
                                        ORDER BY tr.transaction_id DESC";
                                $result = mysqli_query($conn, $sql);

                                if ($result && mysqli_num_rows($result) > 0) {
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        if ($row['status'] == 'Borrowed') {
                                            $action = "Borrowed";
                                            $time = date('h:i A', strtotime($row['date_requested']));
                                            $badge = "warning";
                                        } else {
                                            $action = "Returned";
                                            $time = date('h:i A', strtotime($row['actual_return_date']));
                                            $badge = ($row['status'] == 'Returned') ? "success" : "danger";
                                        }
                                ?>
                                    <tr>
                                        <td class="ps-4 fw-bold"><?php echo $row['tool_name']; ?></td>
                                        <td><?php echo $action; ?></td>
                                        <td><?php echo $time; ?></td>
                                        <td class="text-end pe-4"><span class="badge bg-<?php echo $badge; ?> rounded-pill"><?php echo $row['status']; ?></span></td>
                                    </tr>
                                <?php 
                                    }
                                } else { 
                                    echo "<tr><td colspan='4' class='text-center py-4 text-muted fst-italic'>
                                            <i class='bi bi-folder2-open display-6 d-block mb-2'></i>You have no record on this day.
                                          </td></tr>"; 
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="fullHistoryModal" tabindex="-1">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header"><h5 class="modal-title">📜 All Past Transactions</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                        <div class="modal-body p-0">
                            <table class="table table-striped mb-0">
                                <thead class="table-dark"><tr><th>Date</th><th>Tool</th><th>Status</th></tr></thead>
                                <tbody>
                                    <?php 
                                    $sql_all = "SELECT t.tool_name, tr.status, tr.date_requested FROM transactions tr JOIN tools t ON tr.tool_id = t.tool_id WHERE tr.user_id = '$user_id' ORDER BY tr.transaction_id DESC";
                                    $res_all = mysqli_query($conn, $sql_all);
                                    while ($row_all = mysqli_fetch_assoc($res_all)) {
                                    ?>
                                        <tr>
                                            <td><?php echo date('M d, Y', strtotime($row_all['date_requested'])); ?></td>
                                            <td><?php echo $row_all['tool_name']; ?></td>
                                            <td><span class="badge bg-secondary"><?php echo $row_all['status']; ?></span></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="penaltyRulesModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-light">
                            <h5 class="modal-title"><i class="bi bi-info-circle-fill text-primary"></i> How Penalties Work</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p>To ensure fair usage of laboratory tools, the system applies specific penalties.</p>
                            <ul class="list-group list-group-flush mb-3">
                                <li class="list-group-item d-flex justify-content-between align-items-center">Late Return Fee <span class="badge bg-warning text-dark">5 Points / Day</span></li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">Damaged Item <span class="badge bg-danger">20 - 50 Points</span></li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">Lost Item <span class="badge bg-danger">60 Points (Ban)</span></li>
                            </ul>
                            <div class="alert alert-danger d-flex align-items-center">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <div><strong>Restriction Warning:</strong><br>If you reach <strong>60 Points</strong>, your account is automatically restricted.</div>
                            </div>
                        </div>
                        <div class="modal-footer"><button type="button" class="btn btn-primary w-100" data-bs-dismiss="modal">Understood</button></div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="banReasonModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title">Suspension Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-dark">
                            <h6>Admin Message:</h6>
                            <p class="p-3 bg-light border rounded fst-italic">"<?php echo $ban_reason; ?>"</p>
                        </div>
                    </div>
                </div>
            </div>

        <?php } ?>

    </div>
    

    <?php if ($role == 'admin') { ?>
        <script src="assets/js/jane_voice.js"></script>
        <script src="assets/js/notif_sounds.js"></script>
    <?php } ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>