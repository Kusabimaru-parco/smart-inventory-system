<?php
session_start();
include "db_conn.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch current user details
$sql = "SELECT full_name, id_number, course_section, email FROM users WHERE user_id = '$user_id'";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);

// Check if OTP Step is Active
$otp_step = isset($_GET['otp_step']) ? true : false;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Account Settings</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    <nav class="navbar navbar-dark bg-dark px-3 mb-4">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">⚙️ Account Settings</span>
            <a href="dashboard.php" class="btn btn-outline-light btn-sm">Back to Dashboard</a>
        </div>
    </nav>

    <div class="container">
        
        <?php if(isset($_GET['msg'])) { ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo htmlspecialchars($_GET['msg']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php } ?>
        
        <?php if(isset($_GET['error'])) { ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php echo htmlspecialchars($_GET['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php } ?>

        <div class="row justify-content-center">
            
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white fw-bold">
                        <i class="bi bi-person-badge-fill text-primary"></i> Profile Information
                    </div>
                    <div class="card-body">
                        <form action="account_action.php" method="POST">
                            <div class="mb-3">
                                <label class="form-label text-muted small">Full Name</label>
                                <input type="text" class="form-control" value="<?php echo $user['full_name']; ?>" disabled>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted small">ID Number</label>
                                <input type="text" class="form-control" value="<?php echo $user['id_number']; ?>" disabled>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted small">Registered Email</label>
                                <input type="text" class="form-control" value="<?php echo $user['email']; ?>" disabled>
                                <div class="form-text">Contact Admin to change email.</div>
                            </div>
                            
                            <hr>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Year & Section / Course</label>
                                <input type="text" name="course_section" class="form-control" 
                                       value="<?php echo $user['course_section']; ?>" 
                                       placeholder="Ex. BSIT 4-1">
                            </div>
                            
                            <button type="submit" name="update_profile" class="btn btn-primary w-100">
                                Save Changes
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100 border-warning">
                    <div class="card-header bg-warning text-dark fw-bold">
                        <i class="bi bi-shield-lock-fill"></i> Security & Password
                    </div>
                    <div class="card-body">
                        
                        <?php if (!$otp_step) { ?>
                            <p class="small text-muted">To change your password, we need to verify it's really you. We will send a One-Time Password (OTP) to: <strong><?php echo $user['email']; ?></strong></p>
                            
                            <form action="account_action.php" method="POST">
                                <div class="d-grid gap-2">
                                    <button type="submit" name="request_otp" class="btn btn-outline-dark">
                                        <i class="bi bi-envelope-paper"></i> Send Verification OTP
                                    </button>
                                </div>
                            </form>

                        <?php } else { ?>
                            <div class="alert alert-info small">
                                <i class="bi bi-info-circle"></i> OTP sent to your email. Check Spam folder.
                            </div>

                            <form action="account_action.php" method="POST">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Enter OTP Code</label>
                                    <input type="text" name="otp_code" class="form-control form-control-lg text-center letter-spacing-2" placeholder="6-Digit Code" maxlength="6" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">New Password</label>
                                    <input type="password" name="new_pass" class="form-control" placeholder="Min. 6 characters" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Confirm New Password</label>
                                    <input type="password" name="confirm_pass" class="form-control" placeholder="Retype password" required>
                                </div>
                                
                                <div class="d-grid gap-2 mb-3">
                                    <button type="submit" name="change_password" class="btn btn-warning fw-bold">
                                        Verify & Change Password
                                    </button>
                                </div>
                            </form>

                            <div class="d-flex justify-content-between align-items-center border-top pt-3">
                                <a href="account_settings.php" class="text-decoration-none text-secondary small">
                                    <i class="bi bi-arrow-left"></i> Cancel
                                </a>
                                <a href="account_action.php?resend_otp=true" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-arrow-clockwise"></i> Resend OTP
                                </a>
                            </div>

                        <?php } ?>

                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>