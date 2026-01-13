<?php
session_start();
$step = isset($_GET['step']) ? $_GET['step'] : 1;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Smart Inventory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 15px; }
        .reset-box { width: 100%; max-width: 450px; padding: 30px; background: white; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.08); }
    </style>
</head>
<body>

    <div class="reset-box">
        <h4 class="fw-bold mb-4 text-center">🔐 Password Recovery</h4>

        <?php if (isset($_GET['error'])) { ?>
            <div class="alert alert-danger text-center py-2"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php } ?>
        <?php if (isset($_GET['msg'])) { ?>
            <div class="alert alert-success text-center py-2"><?php echo htmlspecialchars($_GET['msg']); ?></div>
        <?php } ?>

        <?php if ($step == 1) { ?>
            <p class="text-muted text-center small mb-4">Enter your School ID Number to receive a verification code.</p>
            <form action="forgot_password_action.php" method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold">ID Number</label>
                    <input type="text" name="id_number" class="form-control form-control-lg" placeholder="Ex. 2025-0001" required>
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" name="request_reset" class="btn btn-primary btn-lg">Send Verification Code</button>
                    <a href="index.php" class="btn btn-outline-secondary">Back to Login</a>
                </div>
            </form>
        <?php } ?>

        <?php if ($step == 2) { ?>
            <p class="text-muted text-center small mb-4">
                We sent a code to your email.<br>Please check your <strong>Inbox</strong> or <strong>Spam</strong> folder.
            </p>
            <form action="forgot_password_action.php" method="POST">
                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($_GET['uid']); ?>">
                <div class="mb-3">
                    <label class="form-label fw-bold">Enter OTP Code</label>
                    <input type="text" name="otp_code" class="form-control form-control-lg text-center fs-4 letter-spacing-2" maxlength="6" placeholder="######" required>
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" name="verify_otp" class="btn btn-primary btn-lg">Verify Code</button>
                    <a href="forgot_password.php" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        <?php } ?>

        <?php if ($step == 3) { ?>
            <p class="text-muted text-center small mb-4">Identity verified! Set your new password below.</p>
            <form action="forgot_password_action.php" method="POST">
                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($_GET['uid']); ?>">
                <input type="hidden" name="otp_code" value="<?php echo htmlspecialchars($_GET['otp']); ?>"> 
                
                <div class="mb-3">
                    <label class="form-label fw-bold">New Password</label>
                    <input type="password" name="new_pass" class="form-control form-control-lg" placeholder="Min. 6 chars" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Confirm Password</label>
                    <input type="password" name="confirm_pass" class="form-control form-control-lg" placeholder="Retype password" required>
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" name="reset_password" class="btn btn-success btn-lg">Reset Password</button>
                </div>
            </form>
        <?php } ?>

    </div>

</body>
</html>