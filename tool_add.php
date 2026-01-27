<?php
session_start();
include "db_conn.php";

// Security Check
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tool_name = mysqli_real_escape_string($conn, $_POST['tool_name']);
    $cat_select = $_POST['category_select'];
    $final_category = "";
    
    // Get Quantity (Default to 1 if empty)
    $qty = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

    // --- 1. HANDLE CATEGORY SELECTION ---
    if ($cat_select == "NEW_CAT_OPTION") {
        $final_category = mysqli_real_escape_string($conn, $_POST['new_category_name']);
        if (empty($final_category)) {
            $final_category = "General"; 
        }
    } else {
        $final_category = mysqli_real_escape_string($conn, $cat_select);
    }

    // --- 2. PREPARE BARCODE PREFIX (Smart Logic) ---
    // Example: "Cutting Tools" -> "CU"
    $prefix = strtoupper(substr($final_category, 0, 2));
    $prefix = preg_replace("/[^A-Z0-9]/", "", $prefix); // Remove symbols
    if(strlen($prefix) < 2) $prefix = "XX"; // Fallback

    // Safety limit for loop (Prevent server crash)
    if ($qty > 50) $qty = 50;
    if ($qty < 1) $qty = 1;

    $success_count = 0;

    // --- 3. LOOP TO ADD ITEMS ---
    for ($i = 0; $i < $qty; $i++) {
        
        // Generate Unique Barcode for THIS specific item
        $barcode = "";
        do {
            $rand_num = rand(1000, 9999); // 4-digit random number
            $barcode = $prefix . "-" . $rand_num; // Result: CU-4821
            
            // Check database to ensure this specific number doesn't exist
            $check = mysqli_query($conn, "SELECT tool_id FROM tools WHERE barcode='$barcode'");
        } while (mysqli_num_rows($check) > 0);

        // Insert into Database
        $sql = "INSERT INTO tools (tool_name, category, barcode, status, created_at) 
                VALUES ('$tool_name', '$final_category', '$barcode', 'Available', NOW())";
        
        if (mysqli_query($conn, $sql)) {
            $success_count++;
        }
    }

    // --- 4. REDIRECT ---
    if ($success_count > 0) {
        header("Location: inventory.php?msg=$success_count tool(s) added successfully.");
    } else {
        header("Location: inventory.php?error=Failed to add tools.");
    }
}
?>