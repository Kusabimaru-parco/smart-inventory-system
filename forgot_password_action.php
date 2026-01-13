<?php
session_start();
include "db_conn.php";
include "changepass_email.php"; // REUSING YOUR EMAIL FUNCTION

// --- 1. REQUEST RESET (SEND OTP) ---
if (isset($_POST['request_reset'])) {
    $id_number = mysqli_real_escape_string($conn, $_POST['id_number']);

    // Find user by ID
    $sql = "SELECT user_id, email FROM users WHERE id_number='$id_number'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $user_id = $user['user_id'];
        $email = $user['email'];

        if (empty($email)) {
            header("Location: forgot_password.php?error=No email associated with this ID. Contact Admin.");
            exit();
        }

        // Generate OTP
        $otp = rand(100000, 999999);
        $expiry = date("Y-m-d H:i:s", strtotime("+10 minutes"));

        // Save to DB
        $update = "UPDATE users SET otp_code='$otp', otp_expiry='$expiry' WHERE user_id='$user_id'";
        
        if (mysqli_query($conn, $update)) {
            // Send Email using your existing function
            if (sendOTP($email, $otp)) {
                header("Location: forgot_password.php?step=2&uid=$user_id&msg=OTP sent to your email.");
            } else {
                header("Location: forgot_password.php?error=Failed to send email. Server error.");
            }
        } else {
            header("Location: forgot_password.php?error=Database error.");
        }

    } else {
        header("Location: forgot_password.php?error=ID Number not found.");
    }
    exit();
}

// --- 2. VERIFY OTP ---
if (isset($_POST['verify_otp'])) {
    $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
    $otp_input = mysqli_real_escape_string($conn, $_POST['otp_code']);
    $now = date("Y-m-d H:i:s");

    $sql = "SELECT * FROM users WHERE user_id='$user_id' AND otp_code='$otp_input' AND otp_expiry > '$now'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        // OTP Valid! Move to Step 3
        header("Location: forgot_password.php?step=3&uid=$user_id&otp=$otp_input"); 
    } else {
        header("Location: forgot_password.php?step=2&uid=$user_id&error=Invalid or Expired OTP.");
    }
    exit();
}

// --- 3. RESET PASSWORD ---
if (isset($_POST['reset_password'])) {
    $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
    $otp_check = mysqli_real_escape_string($conn, $_POST['otp_code']); // Security double check
    $new_pass = mysqli_real_escape_string($conn, $_POST['new_pass']);
    $confirm_pass = mysqli_real_escape_string($conn, $_POST['confirm_pass']);

    if ($new_pass !== $confirm_pass) {
        header("Location: forgot_password.php?step=3&uid=$user_id&otp=$otp_check&error=Passwords do not match.");
        exit();
    }

    // Update Password & Clear OTP
    $sql = "UPDATE users SET password='$new_pass', otp_code=NULL, otp_expiry=NULL WHERE user_id='$user_id'";
    
    if (mysqli_query($conn, $sql)) {
        header("Location: index.php?success=Password reset successful! Please login.");
    } else {
        header("Location: forgot_password.php?error=Failed to update password.");
    }
    exit();
}
?>