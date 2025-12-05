<?php 
session_start();
include "db_conn.php";

// Security: Only Admins can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Inventory Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

    <nav class="navbar navbar-dark bg-dark px-4">
        <span class="navbar-brand mb-0 h1">Smart Inventory - Admin</span>
        <div>
            <a href="dashboard.php" class="btn btn-outline-light btn-sm me-2">Dashboard</a>
            <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>🛠️ Tool Inventory</h3>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addToolModal">
                + Add New Tool
            </button>
        </div>

        <?php if (isset($_GET['msg'])) { ?>
            <div class="alert alert-success text-center"><?php echo $_GET['msg']; ?></div>
        <?php } ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <table class="table table-hover table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Barcode ID</th>
                            <th>Tool Name</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Date Added</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $sql = "SELECT * FROM tools ORDER BY created_at DESC";
                        $result = mysqli_query($conn, $sql);

                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                
                                // Color code the status
                                $status_color = 'success';
                                if($row['status'] == 'Borrowed') $status_color = 'warning';
                                if($row['status'] == 'Maintenance') $status_color = 'danger';
                        ?>
                            <tr>
                                <td class="fw-bold"><?php echo $row['barcode']; ?></td>
                                <td><?php echo $row['tool_name']; ?></td>
                                <td><?php echo $row['category']; ?></td>
                                <td><span class="badge bg-<?php echo $status_color; ?>"><?php echo $row['status']; ?></span></td>
                                <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                            </tr>
                        <?php 
                            }
                        } else {
                            echo "<tr><td colspan='5' class='text-center'>No tools found. Add one!</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addToolModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="tool_add.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Equipment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label>Tool Name</label>
                            <input type="text" name="tool_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Category</label>
                            <select name="category" class="form-select">
                                <option value="Hand Tool">Hand Tool</option>
                                <option value="Power Tool">Power Tool</option>
                                <option value="Network Equipment">Network Equipment</option>
                                <option value="Measuring">Measuring Instrument</option>
                            </select>
                        </div>
                        <div class="alert alert-info py-2">
                            <small>ℹ️ A unique <b>Barcode ID</b> will be generated automatically.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Tool</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>