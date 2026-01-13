<?php
session_start();
include "db_conn.php";

if (!isset($_SESSION['user_id'])) exit("Error: Unauthorized");

$user_id = $_SESSION['user_id'];
$limit = 3; // Number of records to load per click
$offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;

// 1. Get Distinct Control Numbers (Batches) with LIMIT
$sql_batches = "SELECT DISTINCT control_no, date_requested 
                FROM transactions 
                WHERE user_id = '$user_id' 
                ORDER BY transaction_id DESC 
                LIMIT $limit OFFSET $offset";

$res_batches = mysqli_query($conn, $sql_batches);

// If no more records found
if (mysqli_num_rows($res_batches) == 0) {
    if ($offset == 0) {
        // User has absolutely no history
        echo "
        <div class='text-center py-5'>
            <div class='text-muted display-1 opacity-25'><i class='bi bi-inbox'></i></div>
            <p class='text-muted mt-2'>No transaction history found.</p>
            <a href='student_catalog.php' class='btn btn-primary btn-sm mt-2'>Start Borrowing</a>
        </div>";
    }
    exit(); // Stop here
}

// Loop through the batches
while ($batch = mysqli_fetch_assoc($res_batches)) {
    $c_no = $batch['control_no'];
    $date = date('M d, Y', strtotime($batch['date_requested']));

    // 2. Get Tools for this specific batch
    $sql_tools = "SELECT t.tool_name, tr.status, tr.feedback 
                  FROM transactions tr 
                  JOIN tools t ON tr.tool_id = t.tool_id 
                  WHERE tr.control_no = '$c_no'";
    $res_tools = mysqli_query($conn, $sql_tools);

    $all_returned = true; 
    $has_feedback = false;
    $tool_list = [];
    $saved_feedback = "";
    $batch_is_cancelled = true; 

    while ($row = mysqli_fetch_assoc($res_tools)) {
        $status_color = 'secondary';
        if($row['status'] == 'Returned') $status_color = 'success';
        if($row['status'] == 'Cancelled') $status_color = 'dark';
        if($row['status'] == 'Borrowed') $status_color = 'primary';

        $tool_list[] = "<div class='d-flex justify-content-between align-items-center mb-1'>" . 
                       "<span class='text-truncate me-2'>" . $row['tool_name'] . "</span>" . 
                       "<span class='badge bg-$status_color'>" . $row['status'] . "</span>" . 
                       "</div>";
        
        if ($row['status'] == 'Borrowed' || $row['status'] == 'Pending' || $row['status'] == 'Approved') {
            $all_returned = false;
        }
        if ($row['status'] != 'Cancelled' && $row['status'] != 'Declined') {
            $batch_is_cancelled = false;
        }
        if (!empty($row['feedback'])) {
            $has_feedback = true;
            $saved_feedback = $row['feedback'];
        }
    }
    
    if ($batch_is_cancelled) $all_returned = false; 

    // --- HTML OUTPUT FOR ONE CARD ---
    ?>
    <div class="card mb-3 border shadow-sm history-card-item">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                <div>
                    <small class="text-muted d-block text-uppercase" style="font-size: 0.7rem; letter-spacing: 1px;">Control No</small>
                    <a href="print_slip.php?control_no=<?php echo $c_no; ?>" target="_blank" class="fw-bold text-decoration-none text-primary fs-5">
                        <?php echo $c_no; ?> <i class="bi bi-box-arrow-up-right ms-1" style="font-size: 0.8rem;"></i>
                    </a>
                </div>
                <div class="text-end">
                    <small class="text-muted d-block text-uppercase" style="font-size: 0.7rem; letter-spacing: 1px;">Date</small>
                    <span class="fw-bold text-dark"><?php echo $date; ?></span>
                </div>
            </div>
            
            <div class="mb-3">
                <small class="text-muted text-uppercase fw-bold mb-2 d-block" style="font-size: 0.75rem;">Tools in this Request:</small>
                <?php echo implode("", $tool_list); ?>
            </div>

            <?php if ($all_returned && !$has_feedback) { ?>
                <div class="bg-light p-3 rounded mt-3">
                    <form action="submit_feedback.php" method="POST">
                        <label class="form-label small fw-bold text-success mb-2">
                            <i class="bi bi-chat-dots-fill me-1"></i> Transaction Complete. Remarks?
                        </label>
                        <input type="hidden" name="control_no" value="<?php echo $c_no; ?>">
                        <div class="input-group">
                            <input type="text" name="feedback" class="form-control form-control-sm" placeholder="Ex. Good condition." required>
                            <button type="submit" class="btn btn-success btn-sm fw-bold">Submit</button>
                        </div>
                    </form>
                </div>
            <?php } elseif ($has_feedback) { ?>
                <div class="alert alert-secondary py-2 px-3 mt-3 mb-0 small border-0 bg-light">
                    <strong class="text-dark"><i class="bi bi-chat-quote-fill me-1"></i> Your Remarks:</strong> 
                    <span class="fst-italic text-muted"><?php echo $saved_feedback; ?></span>
                </div>
            <?php } ?>
        </div>
    </div>
    <?php
}
?>