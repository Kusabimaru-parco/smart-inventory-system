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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <div class="container mt-5" style="max-width: 800px;">
        <div class="card shadow">
            <div class="card-header bg-primary text-white d-flex justify-content-between">
                <h4 class="mb-0">🛒 Borrowing Cart</h4>
                <a href="student_catalog.php" class="btn btn-sm btn-light text-primary">Continue browsing</a>
            </div>
            <div class="card-body">
                
                <?php if (empty($cart_items)): ?>
                    <div class="text-center py-5">
                        <h3 class="text-muted">Your cart is empty.</h3>
                    </div>
                <?php else: ?>
                    
                    <form action="checkout_process.php" method="POST">
                        <table class="table align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Tool Name</th>
                                    <th>Category</th>
                                    <th>Barcode</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Fetch details for all items in cart
                                $sql = "SELECT * FROM tools WHERE tool_id IN ($ids)";
                                $result = mysqli_query($conn, $sql);
                                while ($row = mysqli_fetch_assoc($result)) {
                                ?>
                                    <tr>
                                        <td class="fw-bold"><?php echo $row['tool_name']; ?></td>
                                        <td><?php echo $row['category']; ?></td>
                                        <td><code><?php echo $row['barcode']; ?></code></td>
                                        <td>
                                            <a href="cart_action.php?action=remove&id=<?php echo $row['tool_id']; ?>" 
                                               class="btn btn-sm btn-outline-danger">Remove</a>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>

                        <hr>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Subject / Class Name:</label>
                            <input type="text" name="subject" class="form-control" placeholder="Ex. Networking 1" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Room No. / Laboratory:</label>
                            <input type="text" name="room_no" class="form-control" placeholder="Ex. Lab 1 (301)" required>
                        </div>
                        <div class="row g-3 align-items-end">
                            <div class="col-md-8">
                                <label class="form-label fw-bold">When will you return these items?</label>
                                <input type="date" name="return_date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                                <div class="form-text text-danger">Warning: Late returns will incur cumulative penalty points per item.</div>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-success w-100 py-2">
                                    Confirm Request (<?php echo count($cart_items); ?> Items)
                                </button>
                            </div>
                        </div>
                    </form>

                <?php endif; ?>
            </div>
        </div>
    </div>

</body>
</html>