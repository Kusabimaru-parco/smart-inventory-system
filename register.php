<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Smart Inventory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .login-box { width: 100%; max-width: 450px; padding: 30px; background: white; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    </style>
</head>
<body>

    <div class="login-box">
        <h3 class="text-center mb-3">Student Registration</h3>
        <p class="text-center text-muted mb-4">Create your laboratory account</p>
        
        <?php if (isset($_GET['error'])) { ?>
            <div class="alert alert-danger"><?php echo $_GET['error']; ?></div>
        <?php } ?>

        <form action="register_logic.php" method="POST">
            <div class="mb-3">
                <label>Full Name</label>
                <input type="text" name="full_name" class="form-control" placeholder="Juan Dela Cruz" required>
            </div>
            <div class="mb-3">
                <label>Student ID Number</label>
                <input type="text" name="id_number" class="form-control" placeholder="2025-0001-MN-0" required>
                <small class="text-muted">This will be your Login ID.</small>
            </div>
            <div class="mb-3">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="juan@student.pup.edu.ph" required>
                <small class="text-muted">We will contact you here</small>
            </div>
            <div class="mb-3">
                <label>Password</label>
                <input type="password" name="password" class="form-control" placeholder="******" required>
            </div>
            <div class="mb-3">
                <label>Confirm Password</label>
                <input type="password" name="re_password" class="form-control" placeholder="******" required>
            </div>
            
            <button type="submit" class="btn btn-success w-100">Create Account</button>
            
            <div class="text-center mt-3">
                <a href="index.php" class="text-decoration-none">Already have an account? Login</a>
            </div>
        </form>
    </div>

</body>
</html>