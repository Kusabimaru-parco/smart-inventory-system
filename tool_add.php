<?php
session_start();
include "db_conn.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $tool_name = mysqli_real_escape_string($conn, $_POST['tool_name']);
    $cat_select = $_POST['category_select'];
    $final_category = "";

    // 1. Determine Category
    if ($cat_select == "NEW_CAT_OPTION") {
        // Use the typed input
        $final_category = mysqli_real_escape_string($conn, $_POST['new_category_name']);
        if (empty($final_category)) {
            $final_category = "General"; // Fallback
        }
    } else {
        // Use existing selection
        $final_category = mysqli_real_escape_string($conn, $cat_select);
    }

    // 2. Generate Barcode Prefix (First 2 letters, Uppercase)
    // Example: "Cutting Tools" -> "CU"
    $prefix = strtoupper(substr($final_category, 0, 2));
    
    // Ensure prefix is alphanumeric (remove spaces/symbols if any)
    $prefix = preg_replace("/[^A-Z0-9]/", "", $prefix);
    if(strlen($prefix) < 2) $prefix = "XX"; // Fallback

    // 3. Generate Unique Random 4-digit Number
    // Example: CU-4821
    do {
        $rand_num = rand(1000, 9999);
        $barcode = $prefix . "-" . $rand_num;
        
        // Check uniqueness in DB
        $check = mysqli_query($conn, "SELECT tool_id FROM tools WHERE barcode='$barcode'");
    } while (mysqli_num_rows($check) > 0);

    // 4. Insert into Database
    $sql = "INSERT INTO tools (tool_name, category, barcode, status, created_at) 
            VALUES ('$tool_name', '$final_category', '$barcode', 'Available', NOW())";

    if (mysqli_query($conn, $sql)) {
        header("Location: inventory.php?msg=New Tool Added! Barcode: $barcode");
    } else {
        header("Location: inventory.php?error=Failed to add tool");
    }
}
?>