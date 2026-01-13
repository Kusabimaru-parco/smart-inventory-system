<?php
session_start();
include "db_conn.php";

// --- SECURITY CHECK (Admins & SAs Only) ---
if (!isset($_SESSION['user_id']) || 
   ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'student_assistant')) {
    
    // If accessed directly without login, or by a student
    header("Location: index.php");
    exit();
}
// ------------------------------------------

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// --- CONFIGURATION ---
// You MUST use a real email here (e.g., a dummy Gmail for your Capstone)
// If using Gmail, you need an "App Password" (Search: "Google App Password")
define('SMTP_USER', 'SmartInventoryTest@gmail.com'); 
define('SMTP_PASS', 'vrsl gljp ywhb jgiv'); 

function sendEmail($to, $name, $subject, $body) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom(SMTP_USER, 'Orbitz Admin');
        $mail->addAddress($to, $name);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// --- LOGIC START ---
$today = date('Y-m-d');
//$current_hour = date('H'); // uncomment for real cron job
$current_hour = 16; // this only for testing purposes

echo "<h3>Running Automated Reminder Checks...</h3>";

// QUERY: Get all ACTIVE borrowed items
$sql = "SELECT tr.transaction_id, tr.borrow_date, tr.return_date, u.full_name, u.email, t.tool_name 
        FROM transactions tr
        JOIN users u ON tr.user_id = u.user_id
        JOIN tools t ON tr.tool_id = t.tool_id
        WHERE tr.status = 'Borrowed'";

$result = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_assoc($result)) {
    
    $borrow_date = $row['borrow_date'];
    $return_date = $row['return_date'];
    $email = $row['email'];
    $name = $row['full_name'];
    $tool = $row['tool_name'];
    
    $email_sent = false;
    $subject = "";
    $message = "";

    // LOGIC 1: MULTI-DAY LOAN (Remind 1 day before)
    // If Due Date is Tomorrow
    $tomorrow = date('Y-m-d', strtotime('+1 day'));
    if ($return_date == $tomorrow) {
        $subject = "Reminder: Tool Due Tomorrow";
        $message = "Hi $name, <br> The tool <b>$tool</b> is due for return TOMORROW ($return_date). Please return it before 5:00 PM to avoid penalties.";
        $email_sent = true;
    }

    // LOGIC 2: DUE TODAY (1 Hour before 5 PM)
    // If Due Date is Today AND it is 4 PM (16:00) or later
    if ($return_date == $today && $current_hour >= 16 && $current_hour < 17) {
        $subject = "URGENT: Return Tool Now";
        $message = "Hi $name, <br> The tool <b>$tool</b> is due TODAY at 5:00 PM. You have less than 1 hour. <br> <b>Note:</b> Late returns incur higher penalties the longer you wait.";
        $email_sent = true;
    }

    // LOGIC 3: OVERDUE / LATE (1 Hour AFTER 5 PM)
    // If Due Date is Today AND it is 6 PM (18:00) or later
    if ($return_date == $today && $current_hour >= 18) {
        $subject = "OVERDUE ALERT: Penalty Applied";
        $message = "Hi $name, <br> You failed to return <b>$tool</b> by 5:00 PM today. <br> Penalties are now accumulating daily.";
        $email_sent = true;
    }

    // EXECUTE SEND
    if ($email_sent) {
        if (sendEmail($email, $name, $subject, $message)) {
            echo "Email sent to $name for $tool.<br>";
        } else {
            echo "Failed to send to $name.<br>";
        }
    }
}

echo "<hr>Check complete.";
?>