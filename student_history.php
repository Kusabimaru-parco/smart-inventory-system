<?php 
session_start();
include "db_conn.php";

// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Transaction History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    <nav class="navbar navbar-dark bg-dark px-4">
        <span class="navbar-brand mb-0 h1">📜 My History & Feedback</span>
        <a href="dashboard.php" class="btn btn-outline-light btn-sm">Back to Dashboard</a>
    </nav>

    <div class="container mt-4">
        
        <?php if (isset($_GET['msg'])) { ?>
            <div class="alert alert-success text-center"><?php echo $_GET['msg']; ?></div>
        <?php } ?>

        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-chat-left-text-fill"></i> Completed Transactions</h5>
            </div>
            <div class="card-body">
                <?php
                // 1. Get Distinct Control Numbers for this student
                $sql_batches = "SELECT DISTINCT control_no, date_requested 
                                FROM transactions 
                                WHERE user_id = '$user_id' 
                                ORDER BY transaction_id DESC";
                $res_batches = mysqli_query($conn, $sql_batches);

                if (mysqli_num_rows($res_batches) > 0) {
                    while ($batch = mysqli_fetch_assoc($res_batches)) {
                        $c_no = $batch['control_no'];
                        $date = date('M d, Y', strtotime($batch['date_requested']));

                        // 2. Get Tools and Check Status for this specific batch
                        $sql_tools = "SELECT t.tool_name, tr.status, tr.feedback 
                                      FROM transactions tr 
                                      JOIN tools t ON tr.tool_id = t.tool_id 
                                      WHERE tr.control_no = '$c_no'";
                        $res_tools = mysqli_query($conn, $sql_tools);

                        $all_returned = true; // Assume true
                        $has_feedback = false;
                        $tool_list = [];
                        $saved_feedback = "";
                        $batch_is_cancelled = true; // Assume all cancelled

                        while ($row = mysqli_fetch_assoc($res_tools)) {
                            $status_color = 'secondary';
                            if($row['status'] == 'Returned') $status_color = 'success';
                            if($row['status'] == 'Cancelled') $status_color = 'dark';
                            if($row['status'] == 'Borrowed') $status_color = 'primary';

                            $tool_list[] = $row['tool_name'] . " <span class='badge bg-$status_color text-wrap' style='font-size:0.7em'>".$row['status']."</span>";
                            
                            // Check if ALL tools are returned (or cancelled/declined - which are also 'done')
                            if ($row['status'] == 'Borrowed' || $row['status'] == 'Pending' || $row['status'] == 'Approved') {
                                $all_returned = false;
                            }

                            // Check if at least one item was NOT cancelled
                            if ($row['status'] != 'Cancelled' && $row['status'] != 'Declined') {
                                $batch_is_cancelled = false;
                            }

                            // Check feedback
                            if (!empty($row['feedback'])) {
                                $has_feedback = true;
                                $saved_feedback = $row['feedback'];
                            }
                        }
                        
                        // Don't show feedback form if the whole batch was just cancelled
                        if ($batch_is_cancelled) {
                            $all_returned = false; 
                        }
                ?>
                    <div class="border rounded p-3 mb-3 bg-light">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold text-primary mb-0">
                                Control No: 
                                <a href="print_slip.php?control_no=<?php echo $c_no; ?>" target="_blank" class="text-decoration-none">
                                    <?php echo $c_no; ?> <i class="bi bi-box-arrow-up-right small"></i>
                                </a>
                            </h6>
                            <small class="text-muted"><?php echo $date; ?></small>
                        </div>
                        
                        <div class="mb-2 small">
                            <strong>Tools:</strong> <br>
                            <?php echo implode("<br>", $tool_list); ?>
                        </div>

                        <?php if ($all_returned && !$has_feedback) { ?>
                            <form action="submit_feedback.php" method="POST" class="mt-3 border-top pt-2">
                                <label class="form-label small fw-bold text-success">
                                    <i class="bi bi-check-circle-fill"></i> Transaction Complete. Please leave remarks:
                                </label>
                                <input type="hidden" name="control_no" value="<?php echo $c_no; ?>">
                                <div class="input-group">
                                    <input type="text" name="feedback" class="form-control form-control-sm" placeholder="Ex. Tools working great, returned in good condition." required>
                                    <button type="submit" class="btn btn-success btn-sm">Submit Feedback</button>
                                </div>
                            </form>

                        <?php } elseif ($has_feedback) { ?>
                            <div class="alert alert-secondary py-2 px-3 mt-2 mb-0 small">
                                <strong><i class="bi bi-chat-quote-fill"></i> Your Remarks:</strong> 
                                <?php echo $saved_feedback; ?>
                            </div>
                        <?php } ?>
                    </div>
                <?php 
                    }
                } else {
                    echo "<p class='text-muted text-center p-3'>No transactions yet.</p>";
                }
                ?>
            </div>
        </div>
    </div>

</body>
</html>