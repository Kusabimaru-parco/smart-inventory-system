<?php
session_start();
include "db_conn.php";

if (isset($_POST['register'])) {
    
    // 1. Capture Inputs
    $id_number = mysqli_real_escape_string($conn, $_POST['id_number']);
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email     = mysqli_real_escape_string($conn, $_POST['email']);
    $password  = mysqli_real_escape_string($conn, $_POST['password']);
    
    // NEW FIELDS
    $course       = mysqli_real_escape_string($conn, $_POST['course']);       // e.g. BSIT
    $year_section = mysqli_real_escape_string($conn, $_POST['year_section']); // e.g. 4-1
    
    // Combine them for the database (e.g. "BSIT 4-1")
    $course_section = $course . " " . $year_section;

    // 2. Check if User Already Exists
    $check_user = mysqli_query($conn, "SELECT * FROM users WHERE id_number='$id_number' OR email='$email'");
    
    if (mysqli_num_rows($check_user) > 0) {
        $error = "ID Number or Email is already registered!";
    } else {
        // 3. Insert User (Added course_section)
        // We set role='student' and points=0 by default
        $sql = "INSERT INTO users (id_number, full_name, email, password, role, course_section, account_status, penalty_points) 
                VALUES ('$id_number', '$full_name', '$email', '$password', 'student', '$course_section', 'active', 0)";
        
        if (mysqli_query($conn, $sql)) {
            // Redirect to Login with success message
            header("Location: index.php?msg=Registration successful! Please login.");
            exit();
        } else {
            $error = "Registration failed: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* 2. FULL SCREEN CENTERING */
        body { 
            background-color: #f8f9fa; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            min-height: 100vh; /* Ensures it takes up full height */
            padding: 15px;     /* Prevents content from touching edges on small phones */
        }
        
        /* 3. CARD RESPONSIVENESS */
        .register-card { 
            width: 100%; 
            max-width: 500px; 
            border-radius: 15px; 
            overflow: hidden; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.1); 
            background: white;
        }
        
        .card-header { 
            background: #800000; 
            color: white; 
            text-align: center; 
            padding: 25px 15px; 
        }

        /* 4. MOBILE TWEAKS */
        @media (max-width: 576px) {
            .card-body { padding: 1.5rem !important; }
            .btn-lg { width: 100%; }
        }
    </style>
</head>
<body>

    <div class="register-card">
        <div class="card-header">
            <h3>📝 Student Registration</h3>
            <p class="mb-0 opacity-75 small">PUP ITECH Laboratory System</p>
        </div>
        
        <div class="card-body p-4">
            
            <?php if (isset($error)) { ?>
                <div class="alert alert-danger text-center small"><?php echo $error; ?></div>
            <?php } ?>

            <form method="POST">
                
                <div class="mb-3">
                    <label class="form-label fw-bold small text-secondary">Student ID Number</label>
                    <input type="text" name="id_number" class="form-control" placeholder="Ex. 2023-00123-MN-0" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small text-secondary">Full Name</label>
                    <input type="text" name="full_name" class="form-control" placeholder="Ex. Juan Dela Cruz" required>
                </div>

                <div class="row mb-3 g-2"> <div class="col-6">
                        <label class="form-label fw-bold small text-secondary">Course</label>
                        <input type="text" name="course" class="form-control" placeholder="Ex. BSIT" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-bold small text-secondary">Year & Section</label>
                        <input type="text" name="year_section" class="form-control" placeholder="Ex. 4-1" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small text-secondary">Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="Ex. juandelacruz@gmail.com" required>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold small text-secondary">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="******" required>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" name="register" class="btn btn-primary btn-lg fw-bold" style="background-color: #800000; border: none;">
                        Register Account
                    </button>
                    <a href="index.php" class="btn btn-outline-secondary">Already have an account? Login</a>
                </div>

            </form>
        </div>
    </div>

</body>
</html>