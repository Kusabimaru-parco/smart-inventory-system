<?php 
session_start();
include "db_conn.php";

// Security Check
if (!isset($_SESSION['user_id']) || 
   ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'student_assistant')) {
    header("Location: index.php");
    exit();
}

// --- FILTER LOGIC ---
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$category = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : '';

// 1. Get Distinct Categories
$cat_sql = "SELECT DISTINCT category FROM tools ORDER BY category ASC";
$cat_res = mysqli_query($conn, $cat_sql);
$categories = [];
while($c_row = mysqli_fetch_assoc($cat_res)) {
    $categories[] = $c_row['category'];
}

// 2. Build Tool Query (STRICTLY HIDE ARCHIVED)
$sql = "SELECT * FROM tools WHERE status NOT IN ('Archived', 'Deleted', 'Lost')"; 

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
            <div class="alert alert-success alert-dismissible fade show text-center">
                <?php echo htmlspecialchars($_GET['msg']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php } ?>
        <?php if (isset($_GET['error'])) { ?>
            <div class="alert alert-danger alert-dismissible fade show text-center">
                <?php echo htmlspecialchars($_GET['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php } ?>

        <div class="row g-4">
            
            <div class="col-md-4">
                <div class="card shadow-sm border-danger h-100">
                    <div class="card-header bg-danger text-white fw-bold">
                        <i class="bi bi-trash3-fill"></i> Scan to Remove Tool
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
                        <div class="d-flex justify-content-between mb-3 flex-wrap gap-2">
                            <h5 class="card-title text-success"><i class="bi bi-tools"></i> Inventory List</h5>
                            
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-danger btn-sm position-relative" data-bs-toggle="modal" data-bs-target="#archivedToolsModal">
                                    <i class="bi bi-trash3"></i> Bin / Deleted
                                </button>
                                
                                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#manageCatModal">
                                    <i class="bi bi-tags"></i> Categories
                                </button>
                                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addToolModal">
                                    + Add New Tool
                                </button>
                            </div>
                        </div>
                        
                        <form method="GET" class="row g-2">
                            <div class="col-md-5">
                                <input type="text" name="search" class="form-control" placeholder="Search name or ID..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-4">
                                <select name="category" class="form-select">
                                    <option value="All">All Types</option>
                                    <?php foreach ($categories as $cat) { ?>
                                        <option value="<?php echo $cat; ?>" <?php if($category == $cat) echo 'selected'; ?>>
                                            <?php echo $cat; ?>
                                        </option>
                                    <?php } ?>
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
                <div class="table-responsive">
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
                                           onclick="return confirm('Move this tool to the Bin?');">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php 
                                }
                            } else {
                                echo "<tr><td colspan='6' class='text-center py-4 text-muted'>No active tools found.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addToolModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="tool_add.php" method="POST">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">Add New Equipment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label>Tool Name</label>
                            <input type="text" name="tool_name" class="form-control" required placeholder="Ex. Fluke Multimeter">
                        </div>
                        
                        <div class="mb-3">
                            <label>Category</label>
                            <select id="catSelect" name="category_select" class="form-select" onchange="toggleNewCat()">
                                <?php foreach ($categories as $cat) { ?>
                                    <option value="<?php echo $cat; ?>"><?php echo $cat; ?></option>
                                <?php } ?>
                                <option value="NEW_CAT_OPTION" class="fw-bold text-primary">+ Create New Category</option>
                            </select>
                        </div>

                        <div class="mb-3 d-none" id="newCatDiv">
                            <label class="text-primary fw-bold">Enter New Category Name:</label>
                            <input type="text" name="new_category_name" class="form-control" placeholder="Ex. Cutting Tools">
                        </div>

                        <div class="mb-3">
                            <label>Quantity</label>
                            <input type="number" name="quantity" class="form-control" value="1" min="1" required>
                            <small class="text-muted">The system will generate unique barcodes for each item.</small>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Save Tools</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="manageCatModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-secondary text-white">
                    <h5 class="modal-title"><i class="bi bi-tags"></i> Manage Categories</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted">Deleting a category will move all its tools to <strong>"General"</strong>.</p>
                    <ul class="list-group">
                        <?php foreach ($categories as $cat) { ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?php echo $cat; ?>
                                
                                <?php if ($cat != 'General') { ?>
                                    <a href="category_delete.php?cat=<?php echo urlencode($cat); ?>" 
                                       class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('⚠️ DELETE CATEGORY: <?php echo $cat; ?>?\n\nTools will NOT be deleted. They will be moved to \'General\'.');">
                                        Delete
                                    </a>
                                <?php } else { ?>
                                    <span class="badge bg-light text-muted">Default</span>
                                <?php } ?>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="archivedToolsModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-trash3"></i> Deleted / Archived Tools</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Tool Name</th>
                                    <th>Category</th>
                                    <th>Barcode</th>
                                    <th class="text-end pe-3">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                // Query specifically for 'Archived'
                                $arch_sql = "SELECT * FROM tools WHERE status = 'Archived' ORDER BY tool_name ASC";
                                $arch_res = mysqli_query($conn, $arch_sql);

                                if (mysqli_num_rows($arch_res) > 0) {
                                    while ($arow = mysqli_fetch_assoc($arch_res)) {
                                ?>
                                    <tr>
                                        <td class="ps-3 fw-bold"><?php echo $arow['tool_name']; ?></td>
                                        <td><?php echo $arow['category']; ?></td>
                                        <td><code><?php echo $arow['barcode']; ?></code></td>
                                        <td class="text-end pe-3">
                                            <a href="tool_action.php?restore_id=<?php echo $arow['tool_id']; ?>" 
                                               class="btn btn-sm btn-success"
                                               onclick="return confirm('Restore this tool to the main inventory?');">
                                                <i class="bi bi-arrow-counterclockwise"></i> Restore
                                            </a>
                                        </td>
                                    </tr>
                                <?php 
                                    }
                                } else {
                                    echo "<tr><td colspan='4' class='text-center py-4 text-muted'>Bin is empty.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function toggleNewCat() {
            var select = document.getElementById("catSelect");
            var inputDiv = document.getElementById("newCatDiv");
            
            if (select.value === "NEW_CAT_OPTION") {
                inputDiv.classList.remove("d-none");
            } else {
                inputDiv.classList.add("d-none");
            }
        }
    </script>
</body>
</html>