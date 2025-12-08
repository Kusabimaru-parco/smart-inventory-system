<?php
// Connect to Database (Adjust path if needed)
// If db_conn.php is in the main folder, use ../db_conn.php
include "../db_conn.php"; 

$response = array('count' => 0);

if ($conn) {
    $sql = "SELECT COUNT(*) as count FROM transactions WHERE status = 'Pending'";
    $result = mysqli_query($conn, $sql);
    
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $response['count'] = $row['count'];
    }
}

// Return JSON
header('Content-Type: application/json');
echo json_encode($response);
?>