<?php
session_start();
include "db_conn.php";

// Security
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'student_assistant')) {
    exit("Access Denied");
}

$search = isset($_POST['search']) ? mysqli_real_escape_string($conn, $_POST['search']) : '';
$limit = 25;
$offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;

// REMOVED: if ($search == '') exit();  <-- THIS WAS THE CULPRIT

// SQL Construction
$sql = "SELECT tr.control_no, tr.date_requested, tr.status, u.full_name 
        FROM transactions tr
        JOIN users u ON tr.user_id = u.user_id";

// Only add WHERE clause if searching
if ($search != '') {
    $sql .= " WHERE tr.control_no LIKE '%$search%' OR u.full_name LIKE '%$search%'";
}

// Group & Order
$sql .= " GROUP BY tr.control_no 
          ORDER BY tr.transaction_id DESC
          LIMIT $limit OFFSET $offset";

$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $badge = 'secondary';
        if($row['status'] == 'Approved') $badge = 'warning text-dark';
        if($row['status'] == 'Borrowed') $badge = 'primary';
        if($row['status'] == 'Returned') $badge = 'success';
        if($row['status'] == 'Cancelled') $badge = 'dark';
?>
    <tr>
        <td class="fw-bold"><?php echo $row['control_no']; ?></td>
        <td><?php echo $row['full_name']; ?></td>
        <td><?php echo date('M d, Y h:i A', strtotime($row['date_requested'])); ?></td>
        <td><span class="badge bg-<?php echo $badge; ?>"><?php echo $row['status']; ?></span></td>
        <td class="text-center">
            <a href="print_slip.php?control_no=<?php echo $row['control_no']; ?>" 
               target="_blank" class="btn btn-outline-dark btn-sm">
                <i class="bi bi-printer-fill"></i> Print Slip
            </a>
        </td>
    </tr>
<?php 
    }
}
?>