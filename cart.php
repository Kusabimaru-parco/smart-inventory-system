<?php 
session_start();
include "db_conn.php";

if (!isset($_SESSION['user_id'])) header("Location: index.php");

$cart_items = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$ids = implode(',', $cart_items); // Convert array [1,2,3] to string "1,2,3" for SQL
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Cart</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        /* Mobile Tweaks */
        @media (max-width: 576px) {
            .container { padding-left: 10px; padding-right: 10px; }
            .card-header h4 { font-size: 1.2rem; }
            .table { font-size: 0.9rem; }
            .btn-sm { padding: 0.25rem 0.5rem; font-size: 0.8rem; }
        }
    </style>
</head>
<body class="bg-light">

    <div class="container mt-4 mb-5" style="max-width: 800px;">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-3">
                <h4 class="mb-0"><i class="bi bi-cart3"></i> Borrowing Cart</h4>
                <a href="student_catalog.php" class="btn btn-sm btn-light text-primary fw-bold">
                    <i class="bi bi-arrow-left"></i> Keep Browsing
                </a>
            </div>
            
            <div class="card-body p-3 p-md-4"> <?php if (empty($cart_items)): ?>
                    <div class="text-center py-5">
                        <div class="mb-3 text-muted display-1"><i class="bi bi-cart-x"></i></div>
                        <h4 class="text-muted">Your cart is empty.</h4>
                        <a href="student_catalog.php" class="btn btn-primary mt-3">Browse Tools</a>
                    </div>
                <?php else: ?>
                    
                    <form action="checkout_process.php" method="POST">
                        
                        <div class="table-responsive mb-3">
                            <table class="table align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tool Name</th>
                                        <th class="d-none d-sm-table-cell">Category</th> <th class="d-none d-md-table-cell">Barcode</th>  <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql = "SELECT * FROM tools WHERE tool_id IN ($ids)";
                                    $result = mysqli_query($conn, $sql);
                                    while ($row = mysqli_fetch_assoc($result)) {
                                    ?>
                                        <tr>
                                            <td>
                                                <div class="fw-bold text-primary"><?php echo $row['tool_name']; ?></div>
                                                <div class="d-sm-none small text-muted"><?php echo $row['category']; ?></div>
                                                <div class="d-md-none small text-secondary fst-italic"><?php echo $row['barcode']; ?></div>
                                            </td>
                                            <td class="d-none d-sm-table-cell"><?php echo $row['category']; ?></td>
                                            <td class="d-none d-md-table-cell"><code><?php echo $row['barcode']; ?></code></td>
                                            <td class="text-end">
                                                <a href="cart_action.php?action=remove&id=<?php echo $row['tool_id']; ?>" 
                                                   class="btn btn-sm btn-outline-danger" title="Remove Item">
                                                   <i class="bi bi-trash"></i> <span class="d-none d-sm-inline">Remove</span>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>

                        <hr class="my-4">
                        
                        <h5 class="fw-bold mb-3"><i class="bi bi-info-circle"></i> Borrowing Details</h5>

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold small text-secondary">Subject / Class Name</label>
                                <input type="text" name="subject" class="form-control" placeholder="Ex. Networking 1" required>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold small text-secondary">Room No. / Laboratory</label>
                                <input type="text" name="room_no" class="form-control" placeholder="Ex. Lab 1 (301)" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold small text-secondary">Return Date</label>
                                <input type="date" name="return_date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                                <div class="form-text text-danger small mt-1">
                                    <i class="bi bi-exclamation-triangle"></i> Late returns will incur penalty points.
                                </div>
                            </div>
                        </div>

                        <div class="d-grid mt-4 pt-2">
                            <button type="submit" class="btn btn-success btn-lg fw-bold shadow-sm">
                                Confirm Request (<?php echo count($cart_items); ?> Items)
                            </button>
                        </div>

                    </form>

                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>