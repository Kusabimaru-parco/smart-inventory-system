<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Smart Inventory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .login-box { width: 100%; max-width: 400px; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    </style>
</head>
<body>

    <div class="login-box">
        <h3 class="text-center mb-4">Inventory System</h3>
        
        <?php if (isset($_GET['error'])) { ?>
            <div class="alert alert-danger text-center"><?php echo $_GET['error']; ?></div>
        <?php } ?>

        <?php if (isset($_GET['success'])) { ?>
            <div class="alert alert-success text-center"><?php echo $_GET['success']; ?></div>
        <?php } ?>

        <form action="auth.php" method="POST">
            <div class="mb-3">
                <label>ID Number</label>
                <input type="text" name="id_number" class="form-control" placeholder="e.g. 2025-0001" required>
            </div>
            <div class="mb-3">
                <label>Password</label>
                <input type="password" name="password" class="form-control" placeholder="******" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
    
            <div class="text-center mt-3">
                <span class="text-muted">Don't have an account? </span>
                <a href="register.php" class="text-decoration-none fw-bold">Register Here</a>
            </div>
        </form>
    </div>
    <script>
        sessionStorage.removeItem('jane_welcome_played');
    </script>
</body>
</html>