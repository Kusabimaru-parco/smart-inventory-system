<?php
// cancel_request.php
session_start();
include "db_conn.php";

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$trans_id = $_GET['id'];
$actor = $_SESSION['role']; // 'admin' or 'student'
$user_id = $_SESSION['user_id'];

// 1. GET TRANSACTION DETAILS
// FIX: Changed 'u.firstname' to 'u.full_name' to match your database
$query = "SELECT t.transaction_id, t.tool_id, t.user_id, t.status, tl.tool_name, u.email, u.full_name 
          FROM transactions t 
          JOIN tools tl ON t.tool_id = tl.tool_id 
          JOIN users u ON t.user_id = u.user_id 
          WHERE t.transaction_id = '$trans_id'";

$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);

if (!$row) {
    die("Transaction not found.");
}

// 2. SECURITY CHECK
// If student, ensure they own this transaction
if ($actor == 'student' && $row['user_id'] != $user_id) {
    die("Access Denied: You cannot cancel someone else's request.");
}

// Only allow cancelling Pending or Approved requests
if ($row['status'] == 'Borrowed' || $row['status'] == 'Returned') {
    die("Cannot cancel a transaction that is already active or completed.");
}

// 3. PROCESS CANCELLATION
$tool_id = $row['tool_id'];

// Update Transaction Status
$cancel_sql = "UPDATE transactions SET status = 'Cancelled', actual_return_date = NOW() WHERE transaction_id = '$trans_id'";
mysqli_query($conn, $cancel_sql);

// Free up the Tool (Make it Available again)
$tool_sql = "UPDATE tools SET status = 'Available' WHERE tool_id = '$tool_id'";
mysqli_query($conn, $tool_sql);

// 4. SEND EMAIL NOTIFICATION
$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com'; 
    $mail->SMTPAuth   = true;
    $mail->Username   = 'smartinventorytest@gmail.com';
    $mail->Password   = 'wqqi xvka eazx zndc';    
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // Recipients
    $mail->setFrom('no-reply@orbitzinventory.com', 'Orbitz Admin');
    $mail->addAddress($row['email']); 

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Orbitz: Request Cancelled - ' . $row['tool_name'];
    
    // FIX: Using $row['full_name']
    if ($actor == 'admin') {
        $body = "Hi " . $row['full_name'] . ",<br><br>
                 Your request for <b>" . $row['tool_name'] . "</b> has been cancelled by the Admin.<br>
                 Reason: Item was not picked up on time.<br><br>
                 Regards,<br>Orbitz System";
    } else {
        $body = "Hi " . $row['full_name'] . ",<br><br>
                 You have successfully cancelled your request for <b>" . $row['tool_name'] . "</b>.<br><br>
                 Regards,<br>Orbitz System";
    }

    $mail->Body = $body;
    $mail->send();

} catch (Exception $e) {
    // Email failed, but cancellation worked.
}

// 5. REDIRECT BACK
header("Location: dashboard.php?msg=Request Cancelled Successfully");
exit();
?>