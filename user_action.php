<?php
session_start();
include "db_conn.php";

// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    die("Access Denied");
}

if (isset($_GET['id']) && isset($_GET['action'])) {
    
    $user_id = $_GET['id'];
    $action = $_GET['action'];

    if ($action == 'reset') {
        // Reset points to 0 and remove restriction
        $sql = "UPDATE users SET penalty_points = 0, account_status = 'active' WHERE user_id = '$user_id'";
        
        if (mysqli_query($conn, $sql)) {
            // Optional: Log this action in a history table if you want strict auditing
            header("Location: users.php?msg=User penalty points have been reset.");
        } else {
            echo "Error updating record: " . mysqli_error($conn);
        }
    }

} else {
    header("Location: users.php");
}
?>