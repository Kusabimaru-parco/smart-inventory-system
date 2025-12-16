<?php
session_start();
include "db_conn.php";

// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    die("Access Denied");
}

// 1. RESET POINTS Logic
if (isset($_GET['id']) && isset($_GET['action']) && $_GET['action'] == 'reset') {
    $user_id = $_GET['id'];
    // Reset points, active status, and clear ban details
    $sql = "UPDATE users SET 
            penalty_points = 0, 
            account_status = 'active', 
            ban_end_date = NULL, 
            ban_reason = NULL 
            WHERE user_id = '$user_id'";
    
    if (mysqli_query($conn, $sql)) {
        header("Location: users.php?msg=User reset successfully.");
    }
}

// 2. MANUAL BAN Logic
if (isset($_POST['ban_user_btn'])) {
    $user_id = $_POST['user_id'];
    $days = $_POST['ban_days']; // e.g., 3 days
    $reason = mysqli_real_escape_string($conn, $_POST['ban_reason']);

    // Calculate expiration date
    $ban_end = date('Y-m-d H:i:s', strtotime("+$days days"));

    $sql = "UPDATE users SET 
            account_status = 'restricted', 
            ban_end_date = '$ban_end', 
            ban_reason = '$reason' 
            WHERE user_id = '$user_id'";

    if (mysqli_query($conn, $sql)) {
        header("Location: users.php?msg=User has been banned for $days days.");
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>