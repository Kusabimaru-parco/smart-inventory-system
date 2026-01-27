<?php
include "db_conn.php";

$offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;
$search = isset($_POST['search']) ? mysqli_real_escape_string($conn, $_POST['search']) : '';
$limit = 25;

// Fetch unique transactions by Control Number
// Added 'admin_remarks' to the SELECT list
$sql = "SELECT t.control_no, u.full_name, t.date_requested, t.status, t.admin_remarks
        FROM transactions t
        JOIN users u ON t.user_id = u.user_id
        WHERE 1=1";

if ($search != '') {
    $sql .= " AND (t.control_no LIKE '%$search%' OR u.full_name LIKE '%$search%')";
}

// Group by Control No to show 1 row per slip
$sql .= " GROUP BY t.control_no ORDER BY t.transaction_id DESC LIMIT $offset, $limit";

$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $status_color = 'success';
        if ($row['status'] == 'Borrowed') $status_color = 'warning';
        if ($row['status'] == 'Pending') $status_color = 'secondary';
        if ($row['status'] == 'Lost') $status_color = 'danger';

        $date = date('M d, Y', strtotime($row['date_requested']));
        
        // Truncate remarks for display
        $remarks_display = !empty($row['admin_remarks']) ? substr($row['admin_remarks'], 0, 30) . '...' : '<span class="text-muted small">No remarks</span>';
        $full_remarks = htmlspecialchars($row['admin_remarks']); // For the modal input

        echo "<tr>
                <td class='fw-bold text-primary'>{$row['control_no']}</td>
                <td>{$row['full_name']}</td>
                <td>{$date}</td>
                <td><span class='badge bg-{$status_color}'>{$row['status']}</span></td>
                
                <td>
                    {$remarks_display} 
                    <a href='#' class='text-decoration-none small ms-1 btn-edit-remarks' 
                       data-control='{$row['control_no']}' 
                       data-remarks='{$full_remarks}'>
                       <i class='bi bi-pencil-square text-secondary'></i>
                    </a>
                </td>

                <td class='text-center'>
                    <a href='print_slip.php?control_no={$row['control_no']}' target='_blank' class='btn btn-sm btn-outline-dark'>
                        <i class='bi bi-printer'></i> Print
                    </a>
                </td>
              </tr>";
    }
}
?>