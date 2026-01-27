<?php 
session_start();
include "db_conn.php";

// --- SECURITY & LOGIC ---
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'student_assistant')) {
    header("Location: index.php");
    exit();
}

$processor = isset($_SESSION['name']) ? mysqli_real_escape_string($conn, $_SESSION['name']) : 'System';

// 1. Get Active Control Numbers
$req_sql = "SELECT DISTINCT t.control_no, u.full_name, u.course_section 
            FROM transactions t
            JOIN users u ON t.user_id = u.user_id 
            WHERE t.status IN ('Approved', 'Borrowed') 
            ORDER BY t.transaction_id DESC";
$req_res = mysqli_query($conn, $req_sql);

// 2. Handle Selected Request
$selected_control_no = isset($_GET['control_no']) ? $_GET['control_no'] : '';
$msg = "";
$error = "";
$msg_type = ""; // success or danger

// 3. SMART SCAN LOGIC
if (isset($_POST['scan_barcode']) && $selected_control_no) {
    $barcode = mysqli_real_escape_string($conn, $_POST['scan_barcode']);

    // A. Identify Tool
    $scanned_tool_q = mysqli_query($conn, "SELECT tool_id, tool_name, status FROM tools WHERE barcode='$barcode'");
    $scanned_tool = mysqli_fetch_assoc($scanned_tool_q);

    if (!$scanned_tool) {
        $error = "❌ Barcode <b>$barcode</b> not found in system.";
        $msg_type = "danger";
    } else {
        
        // --- RETURN LOGIC ---
        if ($scanned_tool['status'] == 'Borrowed') {
            $check_trans = mysqli_query($conn, "SELECT transaction_id FROM transactions 
                                                WHERE control_no = '$selected_control_no' 
                                                AND tool_id = '{$scanned_tool['tool_id']}' 
                                                AND status = 'Borrowed' LIMIT 1");
            
            if (mysqli_num_rows($check_trans) > 0) {
                $trans_row = mysqli_fetch_assoc($check_trans);
                mysqli_query($conn, "UPDATE transactions SET status = 'Returned', actual_return_date = NOW(), processed_by = '$processor' WHERE transaction_id = '{$trans_row['transaction_id']}'");
                mysqli_query($conn, "UPDATE tools SET status = 'Available' WHERE tool_id = '{$scanned_tool['tool_id']}'");
                
                $msg = "✅ <b>RETURNED:</b> {$scanned_tool['tool_name']}";
                $msg_type = "success";
            } else {
                $error = "⚠️ Tool is borrowed, but <b>NOT</b> under this Transaction ID.";
                $msg_type = "warning";
            }

        // --- ISSUE LOGIC ---
        } elseif ($scanned_tool['status'] == 'Available') {
            $match_sql = "SELECT tr.transaction_id, tr.tool_id as reserved_tool_id 
                          FROM transactions tr
                          JOIN tools t ON tr.tool_id = t.tool_id
                          WHERE tr.control_no = '$selected_control_no' 
                          AND tr.status = 'Approved'
                          AND t.tool_name = '{$scanned_tool['tool_name']}'
                          LIMIT 1";
            $match_res = mysqli_query($conn, $match_sql);

            if (mysqli_num_rows($match_res) > 0) {
                $match = mysqli_fetch_assoc($match_res);
                $trans_id = $match['transaction_id'];
                $reserved_id = $match['reserved_tool_id'];
                $new_id = $scanned_tool['tool_id'];

                mysqli_query($conn, "UPDATE transactions SET tool_id = '$new_id', status = 'Borrowed', actual_borrow_date = NOW(), processed_by = '$processor' WHERE transaction_id = '$trans_id'");
                mysqli_query($conn, "UPDATE tools SET status = 'Borrowed' WHERE tool_id = '$new_id'");
                
                if ($reserved_id != $new_id) {
                     mysqli_query($conn, "UPDATE tools SET status = 'Available' WHERE tool_id = '$reserved_id'");
                }

                $msg = "🚀 <b>ISSUED:</b> {$scanned_tool['tool_name']}";
                $msg_type = "primary";
            } else {
                $error = "⚠️ This tool ({$scanned_tool['tool_name']}) is not required for this request.";
                $msg_type = "warning";
            }
        } else {
            $error = "⛔ Tool status is '{$scanned_tool['status']}'. Cannot process.";
            $msg_type = "danger";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Scanner Terminal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f4f6f9; }
        .scanner-card { border: none; border-radius: 15px; overflow: hidden; }
        .scanner-header { background: linear-gradient(135deg, #198754, #20c997); color: white; padding: 20px; }
        .scan-input { font-size: 1.5rem; letter-spacing: 2px; border: 3px solid #198754; border-radius: 10px; }
        .scan-input:focus { box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25); border-color: #198754; }
        .status-badge { font-size: 0.8rem; padding: 5px 10px; border-radius: 20px; }
        .tool-row { transition: all 0.3s ease; border-left: 4px solid transparent; }
        .tool-row.borrowed { border-left-color: #ffc107; background-color: #fffbf0; }
        .tool-row.returned { border-left-color: #198754; background-color: #f0fff4; opacity: 0.7; }
        .tool-row.approved { border-left-color: #0d6efd; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm py-3">
        <div class="container-fluid px-4">
            <span class="navbar-brand fw-bold">
                <i class="bi bi-upc-scan text-success"></i> Fulfillment Terminal
            </span>
            <div class="d-flex align-items-center text-white">
                <div class="me-4 text-end d-none d-md-block">
                    <div class="small text-white-50">OPERATOR</div>
                    <div class="fw-bold"><?php echo $processor; ?></div>
                </div>
                <a href="dashboard.php" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-arrow-return-left"></i> Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-4 d-flex flex-column flex-md-row align-items-md-center gap-3">
                <div class="flex-grow-1">
                    <label class="form-label text-muted small fw-bold text-uppercase">Active Transaction ID</label>
                    <form method="GET">
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                            <select name="control_no" class="form-select fw-bold text-primary" onchange="this.form.submit()">
                                <option value="">-- Select Transaction --</option>
                                <?php while($r = mysqli_fetch_assoc($req_res)) { ?>
                                    <option value="<?php echo $r['control_no']; ?>" <?php if($selected_control_no == $r['control_no']) echo 'selected'; ?>>
                                        <?php echo $r['control_no']; ?> &mdash; <?php echo $r['full_name']; ?> (<?php echo $r['course_section']; ?>)
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    </form>
                </div>
                <?php if ($selected_control_no): ?>
                    <div class="text-end">
                        <a href="print_slip.php?control_no=<?php echo $selected_control_no; ?>" target="_blank" class="btn btn-secondary btn-lg">
                            <i class="bi bi-printer-fill"></i> Print Slip
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($selected_control_no): ?>
            <div class="row g-4">
                
                <div class="col-lg-5">
                    <div class="card scanner-card shadow-lg h-100">
                        <div class="scanner-header text-center">
                            
                            <h3 class="fw-bold">Ready to Scan</h3>
                            <p class="mb-0 opacity-75">Focus input below & scan barcode</p>
                        </div>
                        <div class="card-body p-4 text-center d-flex flex-column justify-content-center">
                            
                            <?php if($msg): ?>
                                <div class="alert alert-<?php echo $msg_type; ?> shadow-sm mb-4 border-0 d-flex align-items-center justify-content-center gap-2 py-3">
                                    <i class="bi bi-check-circle-fill fs-4"></i> 
                                    <span class="fs-5"><?php echo $msg; ?></span>
                                </div>
                            <?php endif; ?>

                            <?php if($error): ?>
                                <div class="alert alert-<?php echo $msg_type; ?> shadow-sm mb-4 border-0 d-flex align-items-center justify-content-center gap-2 py-3">
                                    <i class="bi bi-exclamation-triangle-fill fs-4"></i> 
                                    <span class="fs-5"><?php echo $error; ?></span>
                                </div>
                            <?php endif; ?>

                            <form method="POST">
                                <div class="mb-3">
                                    <input type="text" name="scan_barcode" 
                                           class="form-control form-control-lg text-center fw-bold scan-input py-3" 
                                           placeholder="Waiting for Barcode..." 
                                           autofocus autocomplete="off" onblur="this.focus()">
                                </div>
                                <p class="text-muted small">
                                    <span class="badge bg-primary">SCAN</span> Available tool to <b>ISSUE</b><br>
                                    <span class="badge bg-warning text-dark">SCAN</span> Borrowed tool to <b>RETURN</b>
                                </p>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold"><i class="bi bi-list-check text-primary"></i> Request Items</h5>
                            <span class="badge bg-light text-dark border"><?php echo $selected_control_no; ?></span>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                <?php 
                                $list_sql = "SELECT t.tool_name, tr.status, tools.barcode, tr.processed_by
                                             FROM transactions tr
                                             JOIN tools t ON tr.tool_id = t.tool_id
                                             LEFT JOIN tools ON tools.tool_id = tr.tool_id 
                                             WHERE tr.control_no = '$selected_control_no'";
                                $list_res = mysqli_query($conn, $list_sql);
                                
                                $all_returned = true;
                                $has_items = false;

                                if (mysqli_num_rows($list_res) > 0) {
                                    $has_items = true;
                                    while($item = mysqli_fetch_assoc($list_res)) {
                                        $status = $item['status'];
                                        
                                        // Visual Logic Variables
                                        $row_class = 'approved'; 
                                        $icon = '<i class="bi bi-hourglass text-primary"></i>';
                                        $badge = '<span class="status-badge bg-primary">Ready</span>';
                                        $meta = '';

                                        if ($status == 'Borrowed') {
                                            $row_class = 'borrowed';
                                            $all_returned = false;
                                            $icon = '<i class="bi bi-box-arrow-right text-warning"></i>';
                                            $badge = '<span class="status-badge bg-warning text-dark">Issued</span>';
                                            $meta = '<div class="small text-muted mt-1"><i class="bi bi-person"></i> by '.$item['processed_by'].'</div>';
                                        } 
                                        elseif ($status == 'Returned') {
                                            $row_class = 'returned';
                                            $icon = '<i class="bi bi-check-circle-fill text-success"></i>';
                                            $badge = '<span class="status-badge bg-success">Returned</span>';
                                            $meta = '<div class="small text-muted mt-1"><i class="bi bi-arrow-return-left"></i> by '.$item['processed_by'].'</div>';
                                        }
                                        elseif ($status == 'Approved') {
                                            $all_returned = false; // Still needs action
                                        }
                                ?>
                                    <div class="list-group-item p-3 tool-row <?php echo $row_class; ?>">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="fs-4"><?php echo $icon; ?></div>
                                                <div>
                                                    <div class="fw-bold fs-5"><?php echo $item['tool_name']; ?></div>
                                                    <div class="font-monospace text-muted small">
                                                        <i class="bi bi-upc"></i> <?php echo $item['barcode'] ? $item['barcode'] : 'Pending Scan'; ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <?php echo $badge; ?>
                                                <?php echo $meta; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php 
                                    }
                                } else {
                                    echo "<div class='text-center py-5 text-muted'>No items found in this transaction.</div>";
                                }
                                ?>
                            </div>
                        </div>
                        
                        <?php if($has_items && $all_returned): ?>
                            <div class="card-footer bg-success text-white text-center py-3">
                                <h5 class="mb-0"><i class="bi bi-check-all"></i> TRANSACTION COMPLETED</h5>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            
            <div class="text-center py-5 text-muted">
                <i class="bi bi-receipt display-1 opacity-25"></i>
                <h3 class="mt-3">No Transaction Selected</h3>
                <p>Please select a Control Number from the dropdown above to begin scanning.</p>
            </div>

        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>