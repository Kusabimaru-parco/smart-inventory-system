<?php
session_start();
include "db_conn.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id'])) {
    
    $control_no = mysqli_real_escape_string($conn, $_POST['control_no']);
    $feedback = mysqli_real_escape_string($conn, $_POST['feedback']);

    if (!empty($feedback)) {
        // Update all transaction rows with this Control No
        $sql = "UPDATE transactions SET feedback = '$feedback' WHERE control_no = '$control_no'";
        
        if (mysqli_query($conn, $sql)) {
            // REDIRECT TO MAIN DASHBOARD
            header("Location: student_history.php?msg=Feedback submitted successfully!");
        } else {
            header("Location: student_history.php?error=Error saving feedback.");
        }
    } else {
        header("Location: student_history.php?error=Feedback cannot be empty.");
    }
} else {
    header("Location: index.php");
}
?>