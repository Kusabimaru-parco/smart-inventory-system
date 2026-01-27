<?php
session_start();
include "db_conn.php";

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'student_assistant')) {
    echo "Unauthorized";
    exit();
}

if (isset($_POST['control_no']) && isset($_POST['remarks'])) {
    $control_no = mysqli_real_escape_string($conn, $_POST['control_no']);
    $remarks = mysqli_real_escape_string($conn, $_POST['remarks']);

    // Update all transactions with this Control No (Since remarks apply to the whole slip)
    $sql = "UPDATE transactions SET admin_remarks = '$remarks' WHERE control_no = '$control_no'";
    
    if (mysqli_query($conn, $sql)) {
        echo "Success";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>