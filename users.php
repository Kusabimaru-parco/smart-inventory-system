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
        <h3>👥 Student & Faculty Directory</h3>
        
        <?php if (isset($_GET['msg'])) { ?>
            <div class="alert alert-success"><?php echo $_GET['msg']; ?></div>
        <?php } ?>

        <div class="card shadow-sm mt-3">
            <div class="card-body">
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
                        $sql = "SELECT * FROM users WHERE role != 'admin' ORDER BY penalty_points DESC";
                        $result = mysqli_query($conn, $sql);

                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                
                                // Logic: If points >= 60, status should visually alert
                                $points = $row['penalty_points'];
                                $row_class = "";
                                $status_badge = "success";
                                $status_text = "Active";

                                if ($points >= 60) {
                                    $row_class = "table-danger"; // Highlight red
                                    $status_badge = "danger";
                                    $status_text = "RESTRICTED";
                                }
                        ?>
                            <tr class="<?php echo $row_class; ?>">
                                <td><?php echo $row['id_number']; ?></td>
                                <td><?php echo $row['full_name']; ?></td>
                                <td><?php echo ucfirst($row['role']); ?></td>
                                <td class="fw-bold text-center"><?php echo $points; ?></td>
                                <td><span class="badge bg-<?php echo $status_badge; ?>"><?php echo $status_text; ?></span></td>
                                <td>
                                    <?php if ($points > 0) { ?>
                                        <a href="user_action.php?id=<?php echo $row['user_id']; ?>&action=reset" 
                                           class="btn btn-outline-primary btn-sm"
                                           onclick="return confirm('Reset points to 0? This will unban the user.');">
                                           🔄 Reset Points
                                        </a>
                                    <?php } else { ?>
                                        <span class="text-muted small">No actions</span>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php 
                            }
                        } else {
                            echo "<tr><td colspan='6' class='text-center'>No users found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>