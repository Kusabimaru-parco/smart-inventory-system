<?php
session_start();
include "db_conn.php";

// Security Check
if (!isset($_SESSION['user_id']) || 
   ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'student_assistant')) {
    header("Location: index.php");
    exit();
}

if (isset($_GET['cat'])) {
    
    $category_to_delete = mysqli_real_escape_string($conn, $_GET['cat']);
    
    // Prevent deleting the 'General' category (Safety net)
    if ($category_to_delete == 'General') {
        header("Location: inventory.php?error=Cannot delete the default 'General' category.");
        exit();
    }

    // 1. Count tools in this category (Just for the message)
    $check_sql = "SELECT COUNT(*) as count FROM tools WHERE category = '$category_to_delete'";
    $check_res = mysqli_query($conn, $check_sql);
    $data = mysqli_fetch_assoc($check_res);
    $count = $data['count'];

    // 2. REASSIGN tools to 'General'
    // This effectively "deletes" the category because no tools will use it anymore.
    $update_sql = "UPDATE tools SET category = 'General' WHERE category = '$category_to_delete'";
    
    if (mysqli_query($conn, $update_sql)) {
        $msg = "Category '$category_to_delete' deleted. $count tools moved to 'General'.";
        header("Location: inventory.php?msg=$msg");
    } else {
        header("Location: inventory.php?error=Database error: " . mysqli_error($conn));
    }

} else {
    header("Location: inventory.php");
}
?>