<?php
session_start();
include "db_conn.php";

if (isset($_POST['id_number']) && isset($_POST['password']) && isset($_POST['full_name'])) {

    function validate($data){
       $data = trim($data);
       $data = stripslashes($data);
       $data = htmlspecialchars($data);
       return $data;
    }

    $id_number = validate($_POST['id_number']);
    $full_name = validate($_POST['full_name']);
    $email = validate($_POST['email']);
    $pass = validate($_POST['password']);
    $re_pass = validate($_POST['re_password']);
    
    // 1. Validation
    if (empty($id_number)) {
        header("Location: register.php?error=ID Number is required");
        exit();
    } else if (empty($full_name)) {
        header("Location: register.php?error=Name is required");
        exit();
    } else if (empty($email)) {
        header("Location: register.php?error=Email is required");
        exit();
    } else if (empty($pass)) {
        header("Location: register.php?error=Password is required");
        exit();
    } else if ($pass !== $re_pass) {
        header("Location: register.php?error=Passwords do not match");
        exit();
    } else {

        // 2. Check if ID already exists
        $sql = "SELECT * FROM users WHERE id_number='$id_number'";
        $sql = "SELECT * FROM users WHERE id_number='$id_number' OR email='$email'";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {
            header("Location: register.php?error=The ID Number is already registered.");
            exit();
        } else {
            // 3. Insert New User
            // Note: For a prototype, plain text is fine. For production, use password_hash($pass, PASSWORD_DEFAULT)
            $sql2 = "INSERT INTO users(id_number, full_name, email, password, role, penalty_points) 
                     VALUES('$id_number', '$full_name', '$email', '$pass', 'student', 0)";
            
            $result2 = mysqli_query($conn, $sql2);

            if ($result2) {
                header("Location: index.php?success=Account created successfully! Please login.");
                exit();
            } else {
                header("Location: register.php?error=unknown error occurred");
                exit();
            }
        }
    }
} else {
    header("Location: register.php");
    exit();
}