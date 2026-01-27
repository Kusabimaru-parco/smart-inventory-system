<?php 
session_start();
include "db_conn.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: index.php");
    exit();
}

$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';

// --- 1. GROUPED QUERY (Show 1 card per Tool Name) ---
// We count how many are available AND not currently booked in pending/approved transactions
$sql = "SELECT tool_name, category, COUNT(*) as qty_available 
        FROM tools 
        WHERE status = 'Available' 
        AND tool_id NOT IN (
            SELECT tool_id FROM transactions WHERE status IN ('Pending', 'Approved')
        )";

if ($search != '') {
    $safe_search = mysqli_real_escape_string($conn, $search);
    $sql .= " AND tool_name LIKE '%$safe_search%'";
}
if ($category != '' && $category != 'All') {
    $safe_cat = mysqli_real_escape_string($conn, $category);
    $sql .= " AND category = '$safe_cat'";
}

$sql .= " GROUP BY tool_name, category ORDER BY tool_name ASC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tool Catalog</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary px-3 sticky-top">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1"><i class="bi bi-box-seam"></i> Student Portal</span>
            <a href="cart.php" class="btn btn-warning btn-sm position-relative ms-auto me-2">
                <i class="bi bi-cart-fill"></i> <span class="d-none d-sm-inline">My Cart</span>
                <?php if($cart_count > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        <?php echo $cart_count; ?>
                    </span>
                <?php endif; ?>
            </a>
            <div class="d-flex gap-2">
                <a href="dashboard.php" class="btn btn-outline-light btn-sm">Dashboard</a>
                <a href="logout.php" class="btn btn-dark btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" class="row g-2 align-items-center">
                    <div class="col-12 col-md-6">
                        <input type="text" name="search" class="form-control" placeholder="Search tool..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-6 col-md-4">
                        <select name="category" class="form-select">
                            <option value="All">All Categories</option>
                            <?php 
                            $cats = ["Hand Tool", "Power Tool", "Network Equipment", "Measuring"];
                            foreach($cats as $cat) {
                                $sel = ($category == $cat) ? 'selected' : '';
                                echo "<option value='$cat' $sel>$cat</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </form>
            </div>
        </div>

        <?php if (isset($_GET['msg'])) { ?>
            <div class="alert alert-success text-center py-2 shadow-sm rounded-pill mb-4 small">
                <i class="bi bi-check-circle-fill me-1"></i> <?php echo htmlspecialchars($_GET['msg']); ?>
            </div>
        <?php } ?>

        <div class="row g-3">
            <?php 
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
            ?>
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body d-flex align-items-center">
                            <div class="me-3 text-secondary">
                                <i class="bi bi-tools" style="font-size: 2.5rem;"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="card-title mb-1"><?php echo $row['tool_name']; ?></h5>
                                <div class="mb-2">
                                    <span class="badge bg-light text-secondary border"><?php echo $row['category']; ?></span>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success">
                                        <?php echo $row['qty_available']; ?> Available
                                    </span>
                                </div>
                                
                                <form action="cart_action.php" method="POST" class="d-flex gap-2">
                                    <input type="hidden" name="action" value="add_bulk">
                                    <input type="hidden" name="tool_name" value="<?php echo htmlspecialchars($row['tool_name']); ?>">
                                    <input type="hidden" name="category" value="<?php echo htmlspecialchars($row['category']); ?>">
                                    
                                    <input type="number" name="qty" class="form-control form-control-sm" value="1" min="1" max="<?php echo $row['qty_available']; ?>" style="width: 70px;">
                                    
                                    <button type="submit" class="btn btn-primary btn-sm flex-grow-1">Add</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php 
                }
            } else {
                echo "<div class='col-12 text-center text-muted py-5'>No tools found.</div>";
            }
            ?>
        </div>
        
        <div style="height: 50px;"></div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>