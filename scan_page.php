<?php 
session_start();
include "db_conn.php";

// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Scanner Interface</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <nav class="navbar navbar-dark bg-success px-4">
        <span class="navbar-brand mb-0 h1"><i class="bi bi-upc-scan"></i> Barcode Terminal</span>
        <a href="dashboard.php" class="btn btn-outline-light btn-sm">Exit Scanner</a>
    </nav>

    <div class="container mt-5 text-center">
        
        <?php if (isset($_GET['success'])) { ?>
            <div class="alert alert-success display-6"><?php echo $_GET['success']; ?></div>
        <?php } ?>
        <?php if (isset($_GET['error'])) { ?>
            <div class="alert alert-danger display-6"><?php echo $_GET['error']; ?></div>
        <?php } ?>

        <div class="card shadow p-5 mx-auto" style="max-width: 600px;">
            <h1 class="mb-4">Waiting for Scan...</h1>
            <p class="text-muted">Point the scanner at the tool's barcode label.</p>
            
            <form action="scan_logic.php" method="POST">
                <input type="text" name="barcode" class="form-control form-control-lg text-center fw-bold" 
                       placeholder="Click here and Scan" autofocus required autocomplete="off"
                       style="font-size: 2rem; letter-spacing: 3px;">
                
                <p class="mt-3 small text-muted">Press Enter if scanner doesn't submit automatically.</p>
            </form>
        </div>

        <div class="mt-5">
            <h5>Recent Scans</h5>
            </div>
    </div>

</body>
</html>