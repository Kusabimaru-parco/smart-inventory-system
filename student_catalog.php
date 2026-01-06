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

$result = mysqli_query($conn, $sql);

if ($search != '') {
    $sql .= " AND tool_name LIKE '%$search%'";
}
if ($category != '' && $category != 'All') {
    $sql .= " AND category = '$category'";
}

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tool Catalog</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    <nav class="navbar navbar-dark bg-primary px-4">
        <span class="navbar-brand mb-0 h1"><i class="bi bi-box-seam"></i> Student Portal</span>
        <div>
            <a href="cart.php" class="btn btn-warning btn-sm me-2 position-relative">
                <i class="bi bi-cart-fill"></i> My Cart
                <?php if($cart_count > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        <?php echo $cart_count; ?>
                    </span>
                <?php endif; ?>
            </a>
            
            <a href="dashboard.php" class="btn btn-outline-light btn-sm me-2">Dashboard</a>
            <a href="logout.php" class="btn btn-dark btn-sm">Logout</a>
        </div>
    </nav>

    <div class="container mt-4">
        
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" class="row g-2 align-items-center">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Search tool name (e.g. Screwdriver)..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <select name="category" class="form-select">
                            <option value="All">All Categories</option>
                            <option value="Hand Tool" <?php if($category == 'Hand Tool') echo 'selected'; ?>>Hand Tool</option>
                            <option value="Power Tool" <?php if($category == 'Power Tool') echo 'selected'; ?>>Power Tool</option>
                            <option value="Network Equipment" <?php if($category == 'Network Equipment') echo 'selected'; ?>>Network Equipment</option>
                            <option value="Measuring" <?php if($category == 'Measuring') echo 'selected'; ?>>Measuring Instrument</option>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </form>
            </div>
        </div>

        <?php if (isset($_GET['msg'])) { ?>
            <div class="alert alert-success text-center py-2"><?php echo $_GET['msg']; ?></div>
        <?php } ?>

        <div class="row">
            <?php 
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    // Check if item is ALREADY in cart to disable button
                    $in_cart = (isset($_SESSION['cart']) && in_array($row['tool_id'], $_SESSION['cart']));
                    $btn_class = $in_cart ? "btn-secondary disabled" : "btn-outline-primary";
                    $btn_text = $in_cart ? "In Cart" : "Add to Cart";
                    $link = $in_cart ? "#" : "cart_action.php?action=add&id=" . $row['tool_id'];
            ?>
                <div class="col-md-3 mb-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body text-center">
                            <div class="display-4 text-secondary mb-2"><i class="bi bi-tools"></i></div>
                            <h5 class="card-title text-dark"><?php echo $row['tool_name']; ?></h5>
                            <span class="badge bg-secondary mb-3"><?php echo $row['category']; ?></span>
                            <br>
                            <small class="text-muted">ID: <?php echo $row['barcode']; ?></small>
                            
                            <a href="<?php echo $link; ?>" class="btn <?php echo $btn_class; ?> w-100 mt-3">
                                <?php echo $btn_text; ?> <i class="bi bi-plus"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php 
                }
            } else {
                echo "<div class='col-12 text-center text-muted py-5'><h4>No tools found matching your search.</h4></div>";
            }
            ?>
        </div>
    </div>

</body>
</html>