<?php
session_start();
include "db_conn.php";

// 1. Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

// 2. Check if Action exists
if (isset($_GET['action'])) {
    
    $action = $_GET['action'];

    // --- BULK ACTIONS (No ID required) ---
    
    if ($action == 'approve_all') {
        // Approve ALL transactions that are currently Pending
        $sql = "UPDATE transactions SET status='Approved' WHERE status='Pending'";
        if (mysqli_query($conn, $sql)) {
            header("Location: admin_requests.php?msg=All pending requests have been APPROVED.");
            exit();
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }

    elseif ($action == 'decline_all') {
        // Decline ALL transactions that are currently Pending
        $sql = "UPDATE transactions SET status='Declined' WHERE status='Pending'";
        if (mysqli_query($conn, $sql)) {
            header("Location: admin_requests.php?msg=All pending requests have been DECLINED.");
            exit();
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }

    // --- SINGLE ACTIONS (ID required) ---
    
    elseif (isset($_GET['id'])) {
        
        $trans_id = $_GET['id'];
        $new_status = "";

        if ($action == 'approve') {
            $new_status = "Approved";
        } elseif ($action == 'decline') {
            $new_status = "Declined";
        } else {
            header("Location: admin_requests.php?error=Invalid action");
            exit();
        }

        // Update specific record
        $sql = "UPDATE transactions SET status='$new_status' WHERE transaction_id='$trans_id'";
        
        if (mysqli_query($conn, $sql)) {
            header("Location: admin_requests.php?msg=Request $new_status successfully.");
            exit();
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }

} else {
    // If no action set, go back
    header("Location: admin_requests.php");
    exit();
}
?>