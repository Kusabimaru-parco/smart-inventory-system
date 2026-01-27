<?php
session_start();
include "db_conn.php";

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'student_assistant')) {
    die("Access Denied");
}

// --- ARCHIVE BY ID ---
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    archiveTool($conn, "tool_id", $id);
}

// --- ARCHIVE BY BARCODE ---
if (isset($_POST['remove_barcode'])) {
    $barcode = mysqli_real_escape_string($conn, $_POST['remove_barcode']);
    archiveTool($conn, "barcode", $barcode);
}

// --- RESTORE ---
if (isset($_GET['restore_id'])) {
    $id = $_GET['restore_id'];
    $sql = "UPDATE tools SET status = 'Available' WHERE tool_id = '$id'";
    if (mysqli_query($conn, $sql)) {
        header("Location: inventory.php?msg=Tool restored.");
    } else {
        header("Location: inventory.php?error=Restore failed.");
    }
    exit();
}

// --- FUNCTION ---
function archiveTool($conn, $col, $val) {
    // 1. Check existence
    $check = mysqli_query($conn, "SELECT status, tool_name FROM tools WHERE $col = '$val'");
    if (mysqli_num_rows($check) == 0) {
        header("Location: inventory.php?error=Tool not found.");
        exit();
    }

    $tool = mysqli_fetch_assoc($check);

    // 2. Safety Check
    if ($tool['status'] == 'Borrowed' || $tool['status'] == 'Approved') {
        header("Location: inventory.php?error=Cannot delete '{$tool['tool_name']}'. It is currently borrowed.");
        exit();
    }

    // 3. Update Status
    $sql = "UPDATE tools SET status = 'Archived' WHERE $col = '$val'";
    
    if (mysqli_query($conn, $sql)) {
        header("Location: inventory.php?msg=Tool moved to Bin.");
    } else {
        // If this fails, it's usually because 'Archived' isn't in the ENUM list
        header("Location: inventory.php?error=Database Error: " . mysqli_error($conn));
    }
    exit();
}
?>