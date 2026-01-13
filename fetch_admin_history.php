<?php
session_start();
include "db_conn.php";

// Security Check
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'student_assistant')) {
    exit("Access Denied");
}

$limit = 50;
$offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;
$filter_date = isset($_POST['date']) ? $_POST['date'] : '';

// SQL Construction
$sql = "SELECT tr.transaction_id, tr.control_no, tr.subject, tr.room_no, tr.processed_by, 
               tr.status, tr.actual_return_date, tr.date_requested, tr.actual_borrow_date,
               t.tool_name, u.full_name 
        FROM transactions tr
        JOIN users u ON tr.user_id = u.user_id
        JOIN tools t ON tr.tool_id = t.tool_id";

if (!empty($filter_date)) {
    $date_safe = mysqli_real_escape_string($conn, $filter_date);
    $sql .= " WHERE DATE(tr.date_requested) = '$date_safe' OR DATE(tr.actual_return_date) = '$date_safe'";
}

$sql .= " ORDER BY tr.transaction_id DESC LIMIT $limit OFFSET $offset";

$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        
        // Main Date Logic
        $main_date = ($row['status'] == 'Returned') ? $row['actual_return_date'] : $row['date_requested'];
        $display_date = date('M d, Y', strtotime($main_date));
        
        // 1. Borrow Date & Time
        $borrow_raw = !empty($row['actual_borrow_date']) ? $row['actual_borrow_date'] : $row['date_requested'];
        $time_borrowed = date('M d, Y h:i A', strtotime($borrow_raw));

        // 2. Return Date & Time (UPDATED)
        $time_returned = "-";
        if ($row['status'] == 'Returned' && !empty($row['actual_return_date'])) {
            // Changed from 'h:i A' to 'M d, Y h:i A'
            $time_returned = date('M d, Y h:i A', strtotime($row['actual_return_date']));
        } elseif ($row['status'] == 'Cancelled' && !empty($row['actual_return_date'])) {
             // Optional: Show cancellation time too
             $time_returned = date('M d, Y h:i A', strtotime($row['actual_return_date']));
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
        
        <td class="text-primary fw-bold" style="font-size: 0.85rem;"><?php echo $time_borrowed; ?></td>
        
        <td class="text-success fw-bold" style="font-size: 0.85rem;"><?php echo $time_returned; ?></td>
        
        <td><small class="fst-italic text-muted"><?php echo $processed_by; ?></small></td>
        <td><span class="badge bg-<?php echo $badge; ?>"><?php echo $row['status']; ?></span></td>
    </tr>
<?php 
    }
}
?>