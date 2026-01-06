<?php
// send_single_reminder.php
session_start();
include "db_conn.php";

// 1. SECURITY: Only Admins allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

// OPTION A: Manual PHPMailer (Use this if you used the folder method)
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// OPTION B: Composer (Uncomment if using Composer)
// require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (isset($_GET['id'])) {
    $trans_id = $_GET['id'];

    // 2. GET DETAILS (User email, Name, Tool Name, Due Date)
    $sql = "SELECT u.email, u.full_name, t.tool_name, tr.return_date 
            FROM transactions tr
            JOIN users u ON tr.user_id = u.user_id
            JOIN tools t ON tr.tool_id = t.tool_id
            WHERE tr.transaction_id = '$trans_id'";
            
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);

    if ($row) {
        $email = $row['email'];
        $name = $row['full_name'];
        $tool = $row['tool_name'];
        $due = date('M d, Y', strtotime($row['return_date']));

        // 3. SEND EMAIL
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'smartinventorytest@gmail.com'; // REPLACE THIS
            $mail->Password   = 'wqqi xvka eazx zndc';    // REPLACE THIS
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Recipients
            $mail->setFrom('no-reply@orbitzinventory.com', 'Orbitz Admin');
            $mail->addAddress($email);

            // Content
            $mail->isHTML(true);
            $mail->Subject = "Reminder: Please Return $tool";
            
            $body = "
            <h3>Inventory Reminder</h3>
            <p>Hi <b>$name</b>,</p>
            <p>This is a reminder to return the following tool:</p>
            <ul>
                <li><b>Tool:</b> $tool</li>
                <li><b>Due Date:</b> $due</li>
            </ul>
            <p>If you have already returned this item, please ignore this message.</p>
            <p>Regards,<br><b>Orbitz Inventory System</b></p>
            ";

            $mail->Body = $body;
            $mail->send();

            $msg = "Reminder sent to $name!";

        } catch (Exception $e) {
            $msg = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        $msg = "Transaction not found.";
    }
} else {
    $msg = "No ID provided.";
}

// Redirect back to Dashboard
header("Location: dashboard.php?msg=$msg");
exit();
?>