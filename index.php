<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Login - Smart Inventory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { 
            background-color: #f4f6f9; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            min-height: 100vh; 
            padding: 15px; 
        }
        .login-box { 
            width: 100%; 
            max-width: 400px; 
            padding: 30px; 
            background: white; 
            border-radius: 12px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.08); 
        }
        .logo-icon img {
            
            width: 300px;      
            height: auto;    
            margin-bottom: none; 
            display: block;
            margin-top: -50px;     
            margin-left: auto;  
            margin-right: auto; 
            margin-bottom: -75px;
        }
    </style>
</head>
<body>

    <div class="login-box text-center">
        <div class="mb-3">
            <div class="logo-icon">
                <img src="LOGO-ORBITZ.png" alt="Logo">
            </div>
            
            <p class="text-muted small">Please sign in to continue</p>
        </div>
        
        <?php if (isset($_GET['error'])) { ?>
            <div class="alert alert-danger text-center py-2 text-small"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php } ?>

        <?php if (isset($_GET['success'])) { ?>
            <div class="alert alert-success text-center py-2 text-small"><?php echo htmlspecialchars($_GET['success']); ?></div>
        <?php } ?>

        <form action="auth.php" method="POST" class="text-start mt-4">
            <div class="mb-3">
                <label class="form-label fw-bold small text-secondary">ID Number</label>
                <input type="text" name="id_number" class="form-control form-control-lg" placeholder="e.g. 2025-0001" required>
            </div>
            
            <div class="mb-2">
                <div class="d-flex justify-content-between align-items-center">
                    <label class="form-label fw-bold small text-secondary">Password</label>
                    <a href="forgot_password.php" class="text-decoration-none small">Forgot Password?</a>
                </div>
                <input type="password" name="password" class="form-control form-control-lg" placeholder="******" required>
            </div>

            <div class="d-grid mt-4">
                <button type="submit" class="btn btn-primary btn-lg fw-bold" style="background-color: #800000; border: none;">Sign In</button>
            </div>
    
            <div class="text-center mt-4">
                <span class="text-muted small">Don't have an account? </span>
                <a href="register.php" class="text-decoration-none fw-bold small">Register Here</a>
            </div>
        </form>
    </div>

    <script>
        sessionStorage.removeItem('jane_welcome_played');
    </script>
</body>
</html>