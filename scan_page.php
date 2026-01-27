<?php 
ob_start(); // Start output buffering (Prevents "Headers already sent" errors)
session_start();
include "db_conn.php";

// --- SECURITY & LOGIC ---
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'student_assistant')) {
    header("Location: index.php");
    exit();
}

$processor = isset($_SESSION['name']) ? mysqli_real_escape_string($conn, $_SESSION['name']) : 'System';

// 1. Get Active Control Numbers
// FIX: Removed 'Lost' from the status check. 
$req_sql = "SELECT DISTINCT t.control_no, u.full_name, u.course_section 
            FROM transactions t
            JOIN users u ON t.user_id = u.user_id 
            WHERE t.status IN ('Approved', 'Borrowed') 
            ORDER BY t.transaction_id DESC";
$req_res = mysqli_query($conn, $req_sql);

// 2. Handle Selected Request & Messages
$selected_control_no = isset($_GET['control_no']) ? $_GET['control_no'] : '';
$msg = isset($_GET['msg']) ? $_GET['msg'] : "";
$error = isset($_GET['error']) ? $_GET['error'] : "";
// Determine message type based on what exists
$msg_type = !empty($error) ? "danger" : (!empty($msg) ? "success" : "");

// --- FETCH EXISTING ADMIN REMARKS ---
$existing_remarks = "";
if ($selected_control_no) {
    $rem_sql = "SELECT admin_remarks FROM transactions WHERE control_no = '$selected_control_no' LIMIT 1";
    $rem_res = mysqli_query($conn, $rem_sql);
    if ($rem_row = mysqli_fetch_assoc($rem_res)) {
        $existing_remarks = $rem_row['admin_remarks'];
    }
}

// --- HELPER FUNCTION: APPLY PENALTY ---
function apply_penalty($conn, $trans_id, $user_id) {
    $t_res = mysqli_query($conn, "SELECT return_date FROM transactions WHERE transaction_id = '$trans_id'");
    $trans = mysqli_fetch_assoc($t_res);
    
    $due_date = $trans['return_date'];
    $today = date('Y-m-d');

    if ($today > $due_date) {
        $diff = strtotime($today) - strtotime($due_date);
        $days_late = ceil($diff / (60 * 60 * 24));
        
        $points = 5 * pow(2, $days_late - 1);
        $reason = "Late Return ($days_late days) - Exponential Penalty";
        
        mysqli_query($conn, "INSERT INTO penalties (user_id, points, reason) VALUES ('$user_id', '$points', '$reason')");
        mysqli_query($conn, "UPDATE users SET penalty_points = penalty_points + $points WHERE user_id='$user_id'");
        
        $u_res = mysqli_query($conn, "SELECT penalty_points FROM users WHERE user_id='$user_id'");
        $u_row = mysqli_fetch_assoc($u_res);
        
        if ($u_row['penalty_points'] >= 60) {
            mysqli_query($conn, "UPDATE users SET account_status = 'restricted' WHERE user_id='$user_id'");
            return " (LATE: $days_late days. $points Pts added. ACCOUNT BANNED!)";
        }
        return " (LATE: $days_late days. $points Pts added)";
    }
    return "";
}

// 3. SMART SCAN LOGIC
if (isset($_POST['scan_barcode']) && $selected_control_no) {
    $barcode = mysqli_real_escape_string($conn, $_POST['scan_barcode']);
    
    // Variables to hold redirect data
    $redirect_msg = "";
    $redirect_error = "";

    // A. Identify Tool
    $scanned_tool_q = mysqli_query($conn, "SELECT tool_id, tool_name, status FROM tools WHERE barcode='$barcode'");
    $scanned_tool = mysqli_fetch_assoc($scanned_tool_q);

    if (!$scanned_tool) {
        $redirect_error = "❌ Barcode $barcode not found.";
    } else {
        $scanned_tool_id = $scanned_tool['tool_id'];
        $scanned_status = $scanned_tool['status'];
        $scanned_tool_name = $scanned_tool['tool_name'];

        // --- RETURN LOGIC ---
        if ($scanned_status == 'Borrowed') {
            
            $check_trans = mysqli_query($conn, "SELECT transaction_id, user_id FROM transactions 
                                                WHERE control_no = '$selected_control_no' 
                                                AND tool_id = '$scanned_tool_id' 
                                                AND status = 'Borrowed' LIMIT 1");
            
            if (mysqli_num_rows($check_trans) > 0) {
                $trans_row = mysqli_fetch_assoc($check_trans);
                $tid = $trans_row['transaction_id'];
                $uid = $trans_row['user_id'];

                $penalty_msg = apply_penalty($conn, $tid, $uid);

                mysqli_query($conn, "UPDATE transactions SET status = 'Returned', actual_return_date = NOW(), processed_by = '$processor' WHERE transaction_id = '$tid'");
                mysqli_query($conn, "UPDATE tools SET status = 'Available' WHERE tool_id = '$scanned_tool_id'");
                
                $redirect_msg = "✅ RETURNED: $scanned_tool_name" . $penalty_msg;

            } else {
                // Swap & Return Logic
                $same_type_check = mysqli_query($conn, "SELECT tr.transaction_id, tr.tool_id, tr.user_id 
                                                        FROM transactions tr
                                                        JOIN tools t ON tr.tool_id = t.tool_id
                                                        WHERE tr.control_no = '$selected_control_no' 
                                                        AND tr.status = 'Borrowed' 
                                                        AND t.tool_name = '$scanned_tool_name' 
                                                        LIMIT 1");

                if (mysqli_num_rows($same_type_check) > 0) {
                    $my_trans = mysqli_fetch_assoc($same_type_check);
                    $my_trans_id = $my_trans['transaction_id'];
                    $my_uid = $my_trans['user_id'];
                    $my_original_tool_id = $my_trans['tool_id']; 

                    $other_trans_q = mysqli_query($conn, "SELECT transaction_id FROM transactions WHERE tool_id = '$scanned_tool_id' AND status = 'Borrowed' LIMIT 1");
                    
                    if (mysqli_num_rows($other_trans_q) > 0) {
                        $other_trans = mysqli_fetch_assoc($other_trans_q);
                        $other_trans_id = $other_trans['transaction_id'];

                        mysqli_query($conn, "UPDATE transactions SET tool_id = '$my_original_tool_id' WHERE transaction_id = '$other_trans_id'");
                        mysqli_query($conn, "UPDATE transactions SET tool_id = '$scanned_tool_id' WHERE transaction_id = '$my_trans_id'");

                        $penalty_msg = apply_penalty($conn, $my_trans_id, $my_uid);

                        mysqli_query($conn, "UPDATE transactions SET status = 'Returned', actual_return_date = NOW(), processed_by = '$processor' WHERE transaction_id = '$my_trans_id'");
                        mysqli_query($conn, "UPDATE tools SET status = 'Available' WHERE tool_id = '$scanned_tool_id'");

                        $redirect_msg = "✅ SWAP & RETURN: $scanned_tool_name" . $penalty_msg;
                    } else {
                        // Force Return
                        $penalty_msg = apply_penalty($conn, $my_trans_id, $my_uid);
                        mysqli_query($conn, "UPDATE transactions SET tool_id = '$scanned_tool_id', status = 'Returned', actual_return_date = NOW(), processed_by = '$processor' WHERE transaction_id = '$my_trans_id'");
                        mysqli_query($conn, "UPDATE tools SET status = 'Available' WHERE tool_id = '$scanned_tool_id'");
                        mysqli_query($conn, "UPDATE tools SET status = 'Available' WHERE tool_id = '$my_original_tool_id'");

                        $redirect_msg = "⚠️ FORCE RETURN: $scanned_tool_name" . $penalty_msg;
                    }
                } else {
                    $redirect_error = "⚠️ Wrong Tool: This tool belongs to someone else.";
                }
            }

        // --- ISSUE LOGIC ---
        } elseif ($scanned_status == 'Available') {
            
            $match_sql = "SELECT tr.transaction_id, tr.tool_id as reserved_tool_id 
                          FROM transactions tr
                          JOIN tools t ON tr.tool_id = t.tool_id
                          WHERE tr.control_no = '$selected_control_no' 
                          AND tr.status = 'Approved'
                          AND t.tool_name = '$scanned_tool_name'
                          LIMIT 1";
            
            $match_res = mysqli_query($conn, $match_sql);

            if (mysqli_num_rows($match_res) > 0) {
                $match = mysqli_fetch_assoc($match_res);
                $trans_id = $match['transaction_id'];
                $reserved_id = $match['reserved_tool_id']; 

                mysqli_query($conn, "UPDATE transactions SET tool_id = '$scanned_tool_id', status = 'Borrowed', actual_borrow_date = NOW(), processed_by = '$processor' WHERE transaction_id = '$trans_id'");
                mysqli_query($conn, "UPDATE tools SET status = 'Borrowed' WHERE tool_id = '$scanned_tool_id'");
                
                if ($reserved_id != $scanned_tool_id) {
                     mysqli_query($conn, "UPDATE tools SET status = 'Available' WHERE tool_id = '$reserved_id'");
                }

                $redirect_msg = "🚀 ISSUED: $scanned_tool_name";
            } else {
                $redirect_error = "⚠️ This tool ($scanned_tool_name) was not requested.";
            }
        
        } else {
            $redirect_error = "⛔ Tool status is '{$scanned_status}'. Cannot process.";
        }
    }

    // --- INSTANT REDIRECT TO REFRESH LIST ---
    // This forces the page to reload with the same control number, updating the list immediately.
    $params = "control_no=" . urlencode($selected_control_no);
    if($redirect_msg) $params .= "&msg=" . urlencode($redirect_msg);
    if($redirect_error) $params .= "&error=" . urlencode($redirect_error);
    
    header("Location: scan_page.php?" . $params);
    exit();
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
        .tool-row.lost { border-left-color: #dc3545; background-color: #f8d7da; }
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

                            <form method="POST" action="scan_page.php?control_no=<?php echo $selected_control_no; ?>">
                                <div class="mb-3">
                                    <?php 
                                    // PRE-CALCULATE IF ITEMS EXIST FOR FOCUS TRAP
                                    $list_sql = "SELECT status FROM transactions WHERE control_no = '$selected_control_no'";
                                    $list_res_check = mysqli_query($conn, $list_sql);
                                    $has_items = false;
                                    $all_resolved = true;
                                    
                                    if(mysqli_num_rows($list_res_check) > 0) {
                                        $has_items = true;
                                        while($row_c = mysqli_fetch_assoc($list_res_check)){
                                            if ($row_c['status'] == 'Borrowed' || $row_c['status'] == 'Approved') {
                                                $all_resolved = false;
                                            }
                                        }
                                    }

                                    $focus_trap = ""; 
                                    if ($has_items && !$all_resolved) {
                                        $focus_trap = 'onblur="this.focus()"';
                                    }
                                    ?>
                                    
                                    <input type="text" name="scan_barcode" 
                                           class="form-control form-control-lg text-center fw-bold scan-input py-3" 
                                           placeholder="Waiting for Barcode..." 
                                           autofocus autocomplete="off" 
                                           <?php echo $focus_trap; ?>>
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
                                // RE-FETCH LIST FOR DISPLAY
                                $list_sql = "SELECT tr.transaction_id, t.tool_name, tr.status, t.barcode, tr.processed_by, tr.tool_id
                                             FROM transactions tr
                                             JOIN tools t ON tr.tool_id = t.tool_id
                                             LEFT JOIN tools ON tools.tool_id = tr.tool_id 
                                             WHERE tr.control_no = '$selected_control_no'";
                                $list_res = mysqli_query($conn, $list_sql);
                                
                                if (mysqli_num_rows($list_res) > 0) {
                                    while($item = mysqli_fetch_assoc($list_res)) {
                                        $status = $item['status'];
                                        $trans_id = $item['transaction_id']; 
                                        $t_id = $item['tool_id'];
                                        
                                        $row_class = 'approved'; 
                                        $icon = '<i class="bi bi-hourglass text-primary"></i>';
                                        $badge = '<span class="status-badge bg-primary">Ready</span>';
                                        $meta = '';
                                        $action_btn = ''; 

                                        if ($status == 'Borrowed') {
                                            $row_class = 'borrowed';
                                            $icon = '<i class="bi bi-box-arrow-right text-warning"></i>';
                                            $badge = '<span class="status-badge bg-warning text-dark">Issued</span>';
                                            $meta = '<div class="small text-muted mt-1"><i class="bi bi-person"></i> by '.$item['processed_by'].'</div>';
                                            $action_btn = '<button type="button" class="btn btn-outline-danger btn-sm border-0" data-bs-toggle="modal" data-bs-target="#lostModal" data-tid="'.$trans_id.'" data-toolid="'.$t_id.'" data-name="'.$item['tool_name'].'"><i class="bi bi-exclamation-octagon"></i> Mark Lost</button>';
                                        } 
                                        elseif ($status == 'Returned') {
                                            $row_class = 'returned';
                                            $icon = '<i class="bi bi-check-circle-fill text-success"></i>';
                                            $badge = '<span class="status-badge bg-success">Returned</span>';
                                            $meta = '<div class="small text-muted mt-1"><i class="bi bi-arrow-return-left"></i> by '.$item['processed_by'].'</div>';
                                        }
                                        elseif ($status == 'Lost') {
                                            $row_class = 'lost';
                                            $icon = '<i class="bi bi-x-circle-fill text-danger"></i>';
                                            $badge = '<span class="status-badge bg-danger">LOST</span>';
                                            $meta = '<div class="small text-muted mt-1">Reported by '.$item['processed_by'].'</div>';
                                        }
                                        elseif ($status == 'Approved') {
                                            $all_resolved = false;
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
                                                <div class="mb-1"><?php echo $badge; ?></div>
                                                <?php echo $action_btn; ?>
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
                        
                        <?php if($has_items && $all_resolved): ?>
                            <div class="card-footer bg-light p-3">
                                <h5 class="fw-bold text-success mb-2"><i class="bi bi-check-circle-fill"></i> TRANSACTION COMPLETED</h5>
                                <label class="form-label small fw-bold text-muted">ADMIN REMARKS (Optional):</label>
                                <textarea id="adminRemarks" class="form-control mb-2" rows="2" placeholder="Enter remarks..."><?php echo htmlspecialchars($existing_remarks); ?></textarea>
                                <button type="button" id="saveRemarksBtn" class="btn btn-sm btn-primary w-100">Save Remarks</button>
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

    <div class="modal fade" id="lostModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-danger">
                <form action="mark_lost.php" method="POST">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title"><i class="bi bi-exclamation-triangle-fill"></i> Report Lost Tool</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center py-4">
                        <p class="lead mb-1">Are you sure you want to mark this item as <b>LOST</b>?</p>
                        <h4 id="lostToolName" class="fw-bold text-danger"></h4>
                        <p class="small text-muted mt-3">This will close the transaction and apply <b>30 Penalty Points</b>.</p>
                        <input type="hidden" name="transaction_id" id="lostTransID">
                        <input type="hidden" name="tool_id" id="lostToolID">
                        <input type="hidden" name="control_no" value="<?php echo $selected_control_no; ?>">
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="confirm_lost" class="btn btn-danger fw-bold">Confirm & Mark Lost</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        var lostModal = document.getElementById('lostModal');
        lostModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var toolName = button.getAttribute('data-name');
            var transId = button.getAttribute('data-tid');
            var toolId = button.getAttribute('data-toolid');
            document.getElementById('lostToolName').textContent = toolName;
            document.getElementById('lostTransID').value = transId;
            document.getElementById('lostToolID').value = toolId;
        });

        // AJAX Logic for Saving Remarks
        $('#saveRemarksBtn').click(function() {
            let remarks = $('#adminRemarks').val();
            let controlNo = '<?php echo $selected_control_no; ?>';

            $.ajax({
                url: 'save_scan_remarks.php', 
                type: 'POST',
                data: { control_no: controlNo, remarks: remarks },
                success: function(res) {
                    if(res === 'Success') {
                        alert('Remarks saved successfully!');
                        // Auto-refocus on scanner only if still scanning, but transaction is done here so maybe focus remarks?
                        // Actually, if transaction is done, we don't force focus on scanner.
                    } else {
                        alert('Error: ' + res);
                    }
                }
            });
        });
    </script>
</body>
</html>