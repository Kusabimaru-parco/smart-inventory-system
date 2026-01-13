<?php
session_start();
include "db_conn.php";
include "changepass_email.php"; // <--- INCLUDE THE MAILER HELPER

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// --- 1. UPDATE PROFILE ---
if (isset($_POST['update_profile'])) {
    $course_section = mysqli_real_escape_string($conn, $_POST['course_section']);
    $sql = "UPDATE users SET course_section = '$course_section' WHERE user_id = '$user_id'";
    if (mysqli_query($conn, $sql)) {
        header("Location: account_settings.php?msg=Profile updated successfully!");
    } else {
        header("Location: account_settings.php?error=Update failed.");
    }
    exit();
}

// --- 2. REQUEST OTP ---
if (isset($_POST['request_otp']) || isset($_GET['resend_otp'])) {
    
    $otp = rand(100000, 999999);
    $expiry = date("Y-m-d H:i:s", strtotime("+10 minutes"));

    $res = mysqli_query($conn, "SELECT email FROM users WHERE user_id='$user_id'");
    $row = mysqli_fetch_assoc($res);
    $email = $row['email'];

    if (empty($email)) {
        header("Location: account_settings.php?error=No email address linked to account.");
        exit();
    }

    // Save OTP to DB
    $sql = "UPDATE users SET otp_code = '$otp', otp_expiry = '$expiry' WHERE user_id = '$user_id'";
    
    if (mysqli_query($conn, $sql)) {
        
        // --- USE PHPMAILER FUNCTION ---
        if (sendOTP($email, $otp)) {
            header("Location: account_settings.php?otp_step=true&msg=OTP sent to $email successfully!");
        } else {
            header("Location: account_settings.php?error=Failed to send email. Please check your internet connection.");
        }

    } else {
        header("Location: account_settings.php?error=Database error.");
    }
    exit();
}

// --- 3. VERIFY & CHANGE PASSWORD ---
if (isset($_POST['change_password'])) {
    $input_otp = mysqli_real_escape_string($conn, $_POST['otp_code']);
    $new_pass = mysqli_real_escape_string($conn, $_POST['new_pass']);
    $confirm_pass = mysqli_real_escape_string($conn, $_POST['confirm_pass']);
    $now = date("Y-m-d H:i:s");

    if ($new_pass !== $confirm_pass) {
        header("Location: account_settings.php?otp_step=true&error=Passwords do not match.");
        exit();
    }

    $sql = "SELECT * FROM users WHERE user_id='$user_id' AND otp_code='$input_otp' AND otp_expiry > '$now'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $update = "UPDATE users SET password='$new_pass', otp_code=NULL, otp_expiry=NULL WHERE user_id='$user_id'";
        mysqli_query($conn, $update);
        header("Location: account_settings.php?msg=Password changed successfully!");
    } else {
        header("Location: account_settings.php?otp_step=true&error=Invalid or Expired OTP.");
    }
    exit();
}
?>