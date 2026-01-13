<?php
// 1. FILE PATHS: Point to where the files are physically located
// If your folder is named "PHPMailer" and inside it is "src", these are correct:
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// 2. NAMESPACES: These must match what is written INSIDE the library files
// (Do not change these to 'src', keep them as PHPMailer\PHPMailer)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function sendOTP($toEmail, $otp) {
    $mail = new PHPMailer(true);

    try {
        // --- SERVER SETTINGS ---
        $mail->isSMTP();                                            
        $mail->Host       = 'smtp.gmail.com';                     
        $mail->SMTPAuth   = true;                                   
        
        // YOUR GMAIL CREDENTIALS
        $mail->Username   = 'SmartInventoryTest@gmail.com';       
        $mail->Password   = 'tphi qjmu vnhm gdya';                
        
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;            
        $mail->Port       = 587;                                    

        // --- RECIPIENTS ---
        $mail->setFrom('SmartInventoryTest@gmail.com', 'Smart Inventory Admin'); 
        $mail->addAddress($toEmail);     

        // --- CONTENT ---
        $mail->isHTML(true);                                  
        $mail->Subject = 'Password Reset OTP - Smart Inventory';
        
        // HTML Message Body
        $mail->Body    = "
            <h3>Password Reset Request</h3>
            <p>You requested to change your password.</p>
            <p>Your OTP Code is: <b style='font-size: 20px; color: blue;'>$otp</b></p>
            <p>This code expires in 10 minutes.</p>
            <br>
            <small>If you did not request this, please ignore this email.</small>
        ";
        
        // Plain text version
        $mail->AltBody = "Your OTP Code is: $otp";

        $mail->send();
        return true;
    } catch (Exception $e) {
        // ERROR DEBUGGING
        // REMOVED THE '//' BELOW TO SEE THE ERROR:
        //echo "Mailer Error: " . $mail->ErrorInfo; 
        //exit(); // Stop the script so we can read the error
        return false;
    }
}
?>