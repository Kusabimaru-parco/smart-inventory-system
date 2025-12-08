<?php 
session_start();
include "db_conn.php";

// Security
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

// --- FILTER LOGIC ---
$search = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';

$sql = "SELECT * FROM tools WHERE 1=1"; // 1=1 makes appending AND easier

if ($search != '') {
    $sql .= " AND (tool_name LIKE '%$search%' OR barcode LIKE '%$search%')";
}
if ($category != '' && $category != 'All') {
    $sql .= " AND category = '$category'";
}

$sql .= " ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Inventory Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    <nav class="navbar navbar-dark bg-dark px-4">
        <span class="navbar-brand mb-0 h1">Smart Inventory - Admin</span>
        <div>
            <a href="dashboard.php" class="btn btn-outline-light btn-sm me-2">Dashboard</a>
            <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
        </div>
    </nav>

    <div class="container mt-4">
        
        <?php if (isset($_GET['msg'])) { ?>
            <div class="alert alert-success text-center"><?php echo $_GET['msg']; ?></div>
        <?php } ?>
        <?php if (isset($_GET['error'])) { ?>
            <div class="alert alert-danger text-center"><?php echo $_GET['error']; ?></div>
        <?php } ?>

        <div class="row g-4">
            
            <div class="col-md-4">
                <div class="card shadow-sm border-danger h-100">
                    <div class="card-header bg-danger text-white fw-bold">
                        <i class="bi bi-trash3-fill"></i> Scan to Remove
                    </div>
                    <div class="card-body">
                        <form action="tool_action.php" method="POST">
                            <label class="form-label small text-muted">Click below and scan barcode</label>
                            <input type="text" name="remove_barcode" class="form-control form-control-lg text-center fw-bold text-danger" 
                                   placeholder="Scan Barcode Here" autofocus autocomplete="off">
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <h5 class="card-title text-success"><i class="bi bi-tools"></i> Inventory List</h5>
                            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addToolModal">
                                + Add New Tool
                            </button>
                        </div>
                        
                        <form method="GET" class="row g-2">
                            <div class="col-md-5">
                                <input type="text" name="search" class="form-control" placeholder="Search name or ID..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-4">
                                <select name="category" class="form-select">
                                    <option value="All">All Types</option>
                                    <option value="Hand Tool" <?php if($category == 'Hand Tool') echo 'selected'; ?>>Hand Tool</option>
                                    <option value="Power Tool" <?php if($category == 'Power Tool') echo 'selected'; ?>>Power Tool</option>
                                    <option value="Network Equipment" <?php if($category == 'Network Equipment') echo 'selected'; ?>>Network Equipment</option>
                                    <option value="Measuring" <?php if($category == 'Measuring') echo 'selected'; ?>>Measuring Instrument</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary w-100">Filter</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>

        <div class="card shadow-sm mt-4">
            <div class="card-body p-0">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Barcode ID</th>
                            <th>Tool Name</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Date Added</th>
                            <th class="text-end pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                $status_color = 'success';
                                if($row['status'] == 'Borrowed') $status_color = 'warning';
                                if($row['status'] == 'Maintenance') $status_color = 'secondary';
                        ?>
                            <tr>
                                <td class="ps-4 fw-bold font-monospace"><?php echo $row['barcode']; ?></td>
                                <td><?php echo $row['tool_name']; ?></td>
                                <td><?php echo $row['category']; ?></td>
                                <td><span class="badge bg-<?php echo $status_color; ?>"><?php echo $row['status']; ?></span></td>
                                <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                <td class="text-end pe-4">
                                    <a href="tool_action.php?delete_id=<?php echo $row['tool_id']; ?>" 
                                       class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('Are you sure you want to permanently remove this tool?');">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php 
                            }
                        } else {
                            echo "<tr><td colspan='6' class='text-center py-4 text-muted'>No tools found matching your filters.</td></tr>";
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