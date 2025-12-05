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
</head>
<body>

    <nav class="navbar navbar-dark bg-dark px-4">
        <span class="navbar-brand mb-0 h1">Request Approval</span>
        <div>
            <a href="dashboard.php" class="btn btn-outline-light btn-sm me-2">Dashboard</a>
            <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
        </div>
    </nav>

    <div class="container mt-5">
        <h3>📩 Pending Borrow Requests</h3>

        <?php if (isset($_GET['msg'])) { ?>
            <div class="alert alert-success"><?php echo $_GET['msg']; ?></div>
        <?php } ?>

        <div class="card shadow-sm mt-3">
            <div class="card-body">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Student Name</th>
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
                                WHERE t.status = 'Pending'";
                        
                        $result = mysqli_query($conn, $sql);

                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                        ?>
                            <tr>
                                <td><?php echo $row['full_name']; ?></td>
                                <td class="fw-bold"><?php echo $row['tool_name']; ?></td>
                                <td>
                                    <small>Borrow: <?php echo $row['borrow_date']; ?></small><br>
                                    <small>Return: <?php echo $row['return_date']; ?></small>
                                </td>
                                <td>
                                    <a href="request_action.php?id=<?php echo $row['transaction_id']; ?>&action=approve" 
                                       class="btn btn-success btn-sm">✅ Approve</a>
                                    
                                    <a href="request_action.php?id=<?php echo $row['transaction_id']; ?>&action=decline" 
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('Are you sure you want to decline this?');">❌ Decline</a>
                                </td>
                            </tr>
                        <?php 
                            }
                        } else {
                            echo "<tr><td colspan='4' class='text-center'>No pending requests.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>