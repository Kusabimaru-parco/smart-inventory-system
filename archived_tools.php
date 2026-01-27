<?php 
session_start();
include "db_conn.php";

// Security Check
if (!isset($_SESSION['user_id']) || 
   ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'student_assistant')) {
    header("Location: index.php");
    exit();
}

// Fetch Archived Tools
$sql = "SELECT * FROM tools WHERE status = 'Archived' ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Archived Tools</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    <nav class="navbar navbar-dark bg-secondary px-4">
        <span class="navbar-brand mb-0 h1"><i class="bi bi-trash"></i> Archived Tools (Bin)</span>
        <a href="inventory.php" class="btn btn-light btn-sm">
            <i class="bi bi-arrow-left"></i> Back to Inventory
        </a>
    </nav>

    <div class="container mt-4">
        
        <?php if (isset($_GET['msg'])) { ?>
            <div class="alert alert-success alert-dismissible fade show text-center">
                <?php echo $_GET['msg']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php } ?>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Barcode</th>
                                <th>Tool Name</th>
                                <th>Category</th>
                                <th>Archived Date</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if (mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                            ?>
                                <tr>
                                    <td class="ps-4 font-monospace"><?php echo $row['barcode']; ?></td>
                                    <td class="fw-bold text-secondary"><?php echo $row['tool_name']; ?></td>
                                    <td><?php echo $row['category']; ?></td>
                                    <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                    <td class="text-end pe-4">
                                        
                                        <a href="tool_action.php?restore_id=<?php echo $row['tool_id']; ?>" 
                                           class="btn btn-sm btn-success me-1"
                                           onclick="return confirm('Restore this tool to Active Inventory?');">
                                            <i class="bi bi-arrow-counterclockwise"></i> Restore
                                        </a>

                                        <a href="tool_action.php?permanent_delete_id=<?php echo $row['tool_id']; ?>" 
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('⚠️ PERMANENTLY DELETE?\n\nThis cannot be undone. History for this tool might be lost.');">
                                            <i class="bi bi-x-lg"></i> Delete
                                        </a>

                                    </td>
                                </tr>
                            <?php 
                                }
                            } else {
                                echo "<tr><td colspan='5' class='text-center py-5 text-muted'>
                                        <i class='bi bi-trash display-4 opacity-25'></i><br>
                                        Bin is empty.
                                      </td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</body>
</html>