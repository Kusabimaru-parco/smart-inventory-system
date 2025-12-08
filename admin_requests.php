<?php 
session_start();
include "db_conn.php";

// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Requests</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    <nav class="navbar navbar-dark bg-dark px-4">
        <span class="navbar-brand mb-0 h1">Request Approval</span>
        <div>
            <a href="dashboard.php" class="btn btn-outline-light btn-sm me-2">Dashboard</a>
            <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
        </div>
    </nav>

    <div class="container mt-5">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3>📩 Pending Borrow Requests</h3>
            <div>
                <a href="request_action.php?action=approve_all" 
                   class="btn btn-success" 
                   onclick="return confirm('Are you sure you want to APPROVE ALL pending requests?');">
                   <i class="bi bi-check-all"></i> Approve All
                </a>
                <a href="request_action.php?action=decline_all" 
                   class="btn btn-outline-danger ms-2" 
                   onclick="return confirm('Are you sure you want to DECLINE ALL pending requests?');">
                   <i class="bi bi-x-circle"></i> Decline All
                </a>
            </div>
        </div>

        <?php if (isset($_GET['msg'])) { ?>
            <div class="alert alert-success text-center mb-4">
                <i class="bi bi-check-circle-fill"></i> <?php echo $_GET['msg']; ?>
            </div>
        <?php } ?>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Student Name</th>
                            <th>Tool Requested</th>
                            <th>Dates</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        // JOIN query to get Student Name and Tool Name
                        $sql = "SELECT t.transaction_id, t.borrow_date, t.return_date, 
                                       u.full_name, tl.tool_name 
                                FROM transactions t
                                JOIN users u ON t.user_id = u.user_id
                                JOIN tools tl ON t.tool_id = tl.tool_id
                                WHERE t.status = 'Pending'
                                ORDER BY t.transaction_id DESC";
                        
                        $result = mysqli_query($conn, $sql);

                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                        ?>
                            <tr>
                                <td class="ps-4 fw-bold"><?php echo $row['full_name']; ?></td>
                                <td><?php echo $row['tool_name']; ?></td>
                                <td>
                                    <small class="text-muted d-block">Borrow: <?php echo date('M d', strtotime($row['borrow_date'])); ?></small>
                                    <small class="text-danger fw-bold">Return: <?php echo date('M d', strtotime($row['return_date'])); ?></small>
                                </td>
                                <td>
                                    <a href="request_action.php?id=<?php echo $row['transaction_id']; ?>&action=approve" 
                                       class="btn btn-success btn-sm me-1">
                                       <i class="bi bi-check-lg"></i> Approve
                                    </a>
                                    
                                    <a href="request_action.php?id=<?php echo $row['transaction_id']; ?>&action=decline" 
                                       class="btn btn-outline-danger btn-sm"
                                       onclick="return confirm('Decline request for <?php echo $row['tool_name']; ?>?');">
                                       <i class="bi bi-x-lg"></i> Decline
                                    </a>
                                </td>
                            </tr>
                        <?php 
                            }
                        } else {
                            echo "<tr><td colspan='4' class='text-center py-5 text-muted'>No pending requests found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>