<?php
session_start();
include "db_conn.php";

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

if (isset($_POST['tool_name']) && isset($_POST['category'])) {
    
    $tool_name = mysqli_real_escape_string($conn, $_POST['tool_name']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);

    // 1. GENERATE UNIQUE BARCODE
    // Format: CategoryCode + RandomNumber (e.g., HT-8293)
    $prefix = strtoupper(substr($category, 0, 2)); // Get first 2 letters
    $rand_num = rand(1000, 9999);
    $barcode = $prefix . "-" . $rand_num;

    // 2. INSERT INTO DATABASE
    $sql = "INSERT INTO tools (barcode, tool_name, category, status) 
            VALUES ('$barcode', '$tool_name', '$category', 'Available')";

    if (mysqli_query($conn, $sql)) {
        header("Location: inventory.php?msg=New tool added successfully! Barcode: $barcode");
    } else {
        // Handle error (e.g. duplicate barcode)
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }

} else {
    header("Location: inventory.php");
}
?>