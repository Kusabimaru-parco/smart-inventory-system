<?php 
session_start(); 
include "db_conn.php";

if (isset($_POST['id_number']) && isset($_POST['password'])) {

    function validate($data){
       $data = trim($data);
       $data = stripslashes($data);
       $data = htmlspecialchars($data);
       return $data;
    }

    $id_number = validate($_POST['id_number']);
    $pass = validate($_POST['password']);

    if (empty($id_number) || empty($pass)) {
        header("Location: index.php?error=ID and Password are required");
        exit();
    } else {
        // Query the DB
        $sql = "SELECT * FROM users WHERE id_number='$id_number' AND password='$pass'";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) === 1) {
            $row = mysqli_fetch_assoc($result);
            
            // CHECK PENALTY POINTS (The Capstone Feature)
            if ($row['penalty_points'] >= 60) {
                header("Location: index.php?error=ACCOUNT BANNED: You have 60+ penalty points.");
                exit();
            }

            // Success! Store user info in session
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['name'] = $row['full_name'];
            $_SESSION['role'] = $row['role'];
            
            header("Location: dashboard.php");
            exit();
        } else {
            header("Location: index.php?error=Incorrect ID or Password");
            exit();
        }
    }
} else {
    header("Location: index.php");
    exit();
}