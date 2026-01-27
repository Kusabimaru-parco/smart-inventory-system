<?php
session_start();
include "db_conn.php";

if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }

$action = isset($_GET['action']) ? $_GET['action'] : '';
$control_no = isset($_GET['control_no']) ? mysqli_real_escape_string($conn, $_GET['control_no']) : '';

if ($control_no) {
    if ($action == 'approve_group') {
        $sql = "UPDATE transactions SET status = 'Approved' WHERE control_no = '$control_no' AND status = 'Pending'";
        mysqli_query($conn, $sql);
        header("Location: admin_requests.php?msg=Request $control_no Approved");
    } 
    elseif ($action == 'decline_group') {
        $sql = "UPDATE transactions SET status = 'Declined' WHERE control_no = '$control_no' AND status = 'Pending'";
        mysqli_query($conn, $sql);
        header("Location: admin_requests.php?msg=Request $control_no Declined");
    }
} else {
    header("Location: admin_requests.php");
}
?>