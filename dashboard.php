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
?>

<!DOCTYPE html>
<html>
<head>
    <title>Smart Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    <nav class="navbar navbar-dark bg-dark px-4">
        <span class="navbar-brand mb-0 h1">🛠️ Smart Inventory System</span>
        <div>
            <span class="text-white me-3">Welcome, <?php echo $_SESSION['name']; ?></span>
            <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
        </div>
    </nav>

    <div class="container mt-4">

        <?php if ($role == 'admin') { ?>
            
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card text-center shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title text-primary"><i class="bi bi-upc-scan"></i> Scanner</h5>
                            <p class="card-text">Issue or Return tools via barcode.</p>
                            <a href="scan_page.php" class="btn btn-primary w-100">Open Scanner</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title text-warning"><i class="bi bi-envelope"></i> Requests</h5>
                            <p class="card-text">Approve student borrow requests.</p>
                            <a href="admin_requests.php" class="btn btn-warning w-100">Manage Requests</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title text-success"><i class="bi bi-tools"></i> Inventory</h5>
                            <p class="card-text">Add or edit tool details.</p>
                            <a href="inventory.php" class="btn btn-success w-100">View Catalog</a>
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
                            // Query for items that are OUT right now
                            $sql = "SELECT u.full_name, t.tool_name, t.barcode, tr.return_date, tr.status 
                                    FROM transactions tr
                                    JOIN users u ON tr.user_id = u.user_id
                                    JOIN tools t ON tr.tool_id = t.tool_id
                                    WHERE tr.status = 'Borrowed'";
                            $result = mysqli_query($conn, $sql);

                            if (mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    // Check if Overdue
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
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> Today's Successful Transactions</h5>
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
                            // Show ONLY things returned or issued TODAY
                            $today = date('Y-m-d');
                            $sql = "SELECT tr.status, t.tool_name, u.full_name, tr.actual_return_date 
                                    FROM transactions tr
                                    JOIN users u ON tr.user_id = u.user_id
                                    JOIN tools t ON tr.tool_id = t.tool_id
                                    WHERE (tr.status = 'Returned' OR tr.status = 'Borrowed') 
                                    ORDER BY tr.transaction_id DESC LIMIT 10"; // Showing last 10 for neatness
                            
                            $result = mysqli_query($conn, $sql);
                            if (mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    $type = ($row['status'] == 'Returned') ? 'IN (Returned)' : 'OUT (Issued)';
                                    $color = ($row['status'] == 'Returned') ? 'success' : 'primary';
                            ?>
                                <tr>
                                    <td><span class="badge bg-<?php echo $color; ?>"><?php echo $type; ?></span></td>
                                    <td><?php echo $row['tool_name']; ?></td>
                                    <td><?php echo $row['full_name']; ?></td>
                                    <td><?php echo date('h:i A'); // Or use actual timestamp col ?></td>
                                </tr>
                            <?php 
                                }
                            } else { echo "<tr><td colspan='4'>No activity yet today.</td></tr>"; }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php } else { ?>

            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card text-white bg-primary h-100">
                        <div class="card-body d-flex flex-column justify-content-center text-center">
                            <h3>Need a Tool?</h3>
                            <p>Browse the catalog and make a request.</p>
                            <a href="student_catalog.php" class="btn btn-light text-primary fw-bold">View Catalog</a>
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
                <div class="card-header">
                    <i class="bi bi-clock-history"></i> My Transaction History
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Tool Name</th>
                                <th>Date Borrowed</th>
                                <th>Date Returned</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $sql = "SELECT t.tool_name, tr.borrow_date, tr.actual_return_date, tr.status 
                                    FROM transactions tr
                                    JOIN tools t ON tr.tool_id = t.tool_id
                                    WHERE tr.user_id = '$user_id' AND tr.status IN ('Returned', 'Declined')
                                    ORDER BY tr.transaction_id DESC";
                            $result = mysqli_query($conn, $sql);

                            if (mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    $st_color = ($row['status'] == 'Returned') ? 'success' : 'danger';
                            ?>
                                <tr>
                                    <td><?php echo $row['tool_name']; ?></td>
                                    <td><?php echo $row['borrow_date']; ?></td>
                                    <td><?php echo $row['actual_return_date']; ?></td>
                                    <td><span class="badge bg-<?php echo $st_color; ?>"><?php echo $row['status']; ?></span></td>
                                </tr>
                            <?php 
                                }
                            } else { echo "<tr><td colspan='4' class='text-center'>No history yet.</td></tr>"; }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php } ?>

    </div>

</body>
</html>