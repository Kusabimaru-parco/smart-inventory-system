<?php 
session_start();
include "db_conn.php";

// Security
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'student_assistant')) {
    die("Access Denied");
}

// Filter Logic
$filter_date = isset($_GET['date']) ? $_GET['date'] : '';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Transaction History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <style>
        @media print {
            .no-print { display: none !important; }
            .card, .container { box-shadow: none !important; border: none !important; }
        }
    </style>
</head>
<body class="bg-light p-4">

    <div class="container bg-white p-4 shadow rounded">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3>📜 Full Transaction History</h3>
            <button onclick="window.print()" class="btn btn-outline-secondary no-print">
                <i class="bi bi-printer"></i> Print Report
            </button>
        </div>

        <form method="GET" class="row g-3 mb-4 border p-3 rounded bg-light no-print">
            <div class="col-auto">
                <label class="col-form-label fw-bold">Filter by Date:</label>
            </div>
            <div class="col-auto">
                <input type="date" name="date" class="form-control" value="<?php echo $filter_date; ?>">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">Search</button>
                <a href="admin_history.php" class="btn btn-secondary">Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Control No.</th> 
                        <th>Main Date</th>
                        <th>Student Name</th>
                        <th>Tool Name</th>
                        <th>Subject / Room</th>
                        <th>Date & Time Borrowed</th> <th>Time Returned</th> 
                        <th>Processed By</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $sql = "SELECT tr.transaction_id, tr.control_no, tr.subject, tr.room_no, tr.processed_by, 
                                   tr.status, tr.actual_return_date, tr.date_requested, tr.actual_borrow_date,
                                   t.tool_name, u.full_name 
                            FROM transactions tr
                            JOIN users u ON tr.user_id = u.user_id
                            JOIN tools t ON tr.tool_id = t.tool_id";

                    if (!empty($filter_date)) {
                        $sql .= " WHERE DATE(tr.date_requested) = '$filter_date' OR DATE(tr.actual_return_date) = '$filter_date'";
                    }

                    $sql .= " ORDER BY tr.transaction_id DESC";
                    $result = mysqli_query($conn, $sql);

                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            
                            // Date Display
                            $main_date = ($row['status'] == 'Returned') ? $row['actual_return_date'] : $row['date_requested'];
                            $display_date = date('M d, Y', strtotime($main_date));
                            
                            // 1. Borrow Date & Time (Updated Format)
                            $borrow_raw = !empty($row['actual_borrow_date']) ? $row['actual_borrow_date'] : $row['date_requested'];
                            // Format: Jan 08, 2026 03:00 PM
                            $time_borrowed = date('M d, Y h:i A', strtotime($borrow_raw));

                            // 2. Return Time
                            $time_returned = "-";
                            if ($row['status'] == 'Returned' && !empty($row['actual_return_date'])) {
                                $time_returned = date('h:i A', strtotime($row['actual_return_date']));
                            }

                            // Badge Logic
                            $badge = 'secondary';
                            if ($row['status'] == 'Returned') $badge = 'success';
                            elseif ($row['status'] == 'Borrowed') $badge = 'primary';
                            elseif ($row['status'] == 'Cancelled') $badge = 'dark';
                            elseif ($row['status'] == 'Pending') $badge = 'warning text-dark';
                            
                            $processed_by = !empty($row['processed_by']) ? $row['processed_by'] : '-';
                    ?>
                        <tr>
                            <td class="fw-bold text-primary">
                                <a href="print_slip.php?control_no=<?php echo $row['control_no']; ?>" target="_blank" class="text-decoration-none">
                                    <?php echo $row['control_no']; ?>
                                </a>
                            </td>
                            <td><?php echo $display_date; ?></td>
                            <td><?php echo $row['full_name']; ?></td>
                            <td><?php echo $row['tool_name']; ?></td>
                            <td>
                                <span class="fw-bold d-block"><?php echo $row['subject']; ?></span>
                                <small class="text-muted"><i class="bi bi-geo-alt"></i> <?php echo $row['room_no']; ?></small>
                            </td>
                            
                            <td class="text-primary fw-bold" style="font-size: 0.9rem;">
                                <?php echo $time_borrowed; ?>
                            </td>
                            
                            <td class="text-success fw-bold"><?php echo $time_returned; ?></td>

                            <td><small class="fst-italic text-muted"><?php echo $processed_by; ?></small></td>
                            <td><span class="badge bg-<?php echo $badge; ?>"><?php echo $row['status']; ?></span></td>
                        </tr>
                    <?php 
                        }
                    } else {
                        echo "<tr><td colspan='9' class='text-center py-4 text-muted'>No records found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>