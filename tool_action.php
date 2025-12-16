<?php
session_start();
include "db_conn.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    die("Access Denied");
}

// --- DELETE BY ID (From Table Button) ---
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    deleteTool($conn, "tool_id", $id);
}

// --- DELETE BY BARCODE (From Scanner Input) ---
if (isset($_POST['remove_barcode'])) {
    $barcode = mysqli_real_escape_string($conn, $_POST['remove_barcode']);
    deleteTool($conn, "barcode", $barcode);
}

// --- REUSABLE DELETE FUNCTION ---
function deleteTool($conn, $col, $val) {
    // 1. Check if tool exists
    $check = mysqli_query($conn, "SELECT status, tool_name FROM tools WHERE $col = '$val'");
    if (mysqli_num_rows($check) == 0) {
        header("Location: inventory.php?error=Tool not found.");
        exit();
    }

    $tool = mysqli_fetch_assoc($check);

    // 2. SAFETY: Prevent deleting if currently borrowed
    if ($tool['status'] == 'Borrowed' || $tool['status'] == 'Approved') {
        header("Location: inventory.php?error=Cannot delete '{$tool['tool_name']}'. It is currently borrowed/active.");
        exit();
    }

    // 3. DELETE (Or Soft Delete if preferred)
    // Note: This will fail if you have Foreign Key Constraints without CASCADE. 
    // Usually better to set status='Archived', but for this request we DELETE.
    $sql = "DELETE FROM tools WHERE $col = '$val'";
    
    if (mysqli_query($conn, $sql)) {
        header("Location: inventory.php?msg=Tool '{$tool['tool_name']}' removed successfully.");
    } else {
        // If it fails (likely due to FK constraint with history), show error
        header("Location: inventory.php?error=Cannot delete. Tool has transaction history.");
    }
    exit();
}
?>