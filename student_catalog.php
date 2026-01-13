<?php 
session_start();
include "db_conn.php";

// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: index.php");
    exit();
}

// 1. CART COUNT LOGIC
$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;

// 2. SEARCH & FILTER LOGIC
$search = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';

// Build the SQL Query dynamically
$sql = "SELECT * FROM tools 
        WHERE status = 'Available' 
        AND tool_id NOT IN (
            SELECT tool_id FROM transactions 
            WHERE status IN ('Pending', 'Approved')
        )";

if ($search != '') {
    $search_safe = mysqli_real_escape_string($conn, $search);
    $sql .= " AND tool_name LIKE '%$search_safe%'";
}
if ($category != '' && $category != 'All') {
    $category_safe = mysqli_real_escape_string($conn, $category);
    $sql .= " AND category = '$category_safe'";
}

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tool Catalog</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        /* Mobile Optimizations */
        @media (max-width: 576px) {
            .navbar-brand { font-size: 1.1rem; }
            .btn-sm { font-size: 0.8rem; }
            .tool-card-title { font-size: 0.9rem; font-weight: bold; }
            .tool-icon { font-size: 2rem; }
            .card-body { padding: 10px; }
            /* Make navbar buttons full width when inside hamburger */
            .navbar-collapse .btn { width: 100%; margin-bottom: 5px; }
        }
        .tool-card { transition: transform 0.2s; border: none; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .tool-card:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        
        /* Cart Badge Pulse Animation */
        @keyframes pulse { 0% { transform: scale(1); } 50% { transform: scale(1.2); } 100% { transform: scale(1); } }
        .badge-pulse { animation: pulse 1s infinite; }
    </style>
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary px-3 sticky-top">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1"><i class="bi bi-box-seam"></i> Student Portal</span>
            
            <a href="cart.php" class="btn btn-warning btn-sm position-relative ms-auto me-2">
                <i class="bi bi-cart-fill"></i> 
                <span class="d-none d-sm-inline">My Cart</span>
                
                <?php if($cart_count > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger badge-pulse">
                        <?php echo $cart_count; ?>
                    </span>
                <?php endif; ?>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navContent">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse flex-grow-0" id="navContent">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center mt-3 mt-lg-0 gap-2">
                    <a href="dashboard.php" class="btn btn-outline-light btn-sm">Dashboard</a>
                    <a href="logout.php" class="btn btn-dark btn-sm">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" class="row g-2 align-items-center">
                    <div class="col-12 col-md-6">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search tool..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                    </div>
                    
                    <div class="col-6 col-md-4">
                        <select name="category" class="form-select">
                            <option value="All">All Categories</option>
                            <option value="Hand Tool" <?php if($category == 'Hand Tool') echo 'selected'; ?>>Hand Tool</option>
                            <option value="Power Tool" <?php if($category == 'Power Tool') echo 'selected'; ?>>Power Tool</option>
                            <option value="Network Equipment" <?php if($category == 'Network Equipment') echo 'selected'; ?>>Network Equipment</option>
                            <option value="Measuring" <?php if($category == 'Measuring') echo 'selected'; ?>>Measuring Instrument</option>
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
                    $in_cart = (isset($_SESSION['cart']) && in_array($row['tool_id'], $_SESSION['cart']));
                    $btn_class = $in_cart ? "btn-secondary disabled border-0" : "btn-outline-primary";
                    $btn_text = $in_cart ? "In Cart" : "Add";
                    $link = $in_cart ? "#" : "cart_action.php?action=add&id=" . $row['tool_id'];
            ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="card h-100 tool-card">
                        <div class="card-body text-center d-flex flex-column">
                            <div class="mb-2 text-secondary tool-icon">
                                <i class="bi bi-tools" style="font-size: 2rem;"></i>
                            </div>
                            
                            <h6 class="card-title text-dark tool-card-title text-truncate mb-1" title="<?php echo $row['tool_name']; ?>">
                                <?php echo $row['tool_name']; ?>
                            </h6>
                            
                            <div class="mb-2">
                                <span class="badge bg-light text-secondary border small"><?php echo $row['category']; ?></span>
                            </div>
                            
                            <small class="text-muted d-block mb-3" style="font-size: 0.75rem;">
                                ID: <?php echo $row['barcode']; ?>
                            </small>
                            
                            <div class="mt-auto">
                                <a href="<?php echo $link; ?>" class="btn <?php echo $btn_class; ?> btn-sm w-100 fw-bold">
                                    <?php echo $btn_text; ?> <i class="bi bi-plus-lg"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php 
                }
            } else {
                echo "
                <div class='col-12 text-center py-5'>
                    <div class='text-muted display-1'><i class='bi bi-search'></i></div>
                    <h5 class='text-muted mt-3'>No tools found.</h5>
                    <a href='student_catalog.php' class='btn btn-link text-decoration-none'>Clear Search</a>
                </div>";
            }
            ?>
        </div>
        
        <div style="height: 50px;"></div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>