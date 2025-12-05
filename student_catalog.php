<?php 
session_start();
include "db_conn.php";

// Security: Only Students can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tool Catalog</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

    <nav class="navbar navbar-dark bg-primary px-4">
        <span class="navbar-brand mb-0 h1">Student Portal</span>
        <div>
            <a href="dashboard.php" class="btn btn-outline-light btn-sm me-2">Dashboard</a>
            <a href="logout.php" class="btn btn-dark btn-sm">Logout</a>
        </div>
    </nav>

    <div class="container mt-5">
        <h3>📚 Available Equipment</h3>
        
        <?php if (isset($_GET['msg'])) { ?>
            <div class="alert alert-success text-center"><?php echo $_GET['msg']; ?></div>
        <?php } ?>

        <div class="row mt-4">
            <?php 
            // Fetch ONLY Available tools
            $sql = "SELECT * FROM tools WHERE status = 'Available'";
            $result = mysqli_query($conn, $sql);

            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
            ?>
                <div class="col-md-3 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <h1 class="display-4">🛠️</h1>
                            <h5 class="card-title"><?php echo $row['tool_name']; ?></h5>
                            <p class="text-muted small"><?php echo $row['category']; ?></p>
                            <button type="button" class="btn btn-primary w-100 borrow-btn" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#borrowModal"
                                    data-id="<?php echo $row['tool_id']; ?>"
                                    data-name="<?php echo $row['tool_name']; ?>">
                                Borrow This
                            </button>
                        </div>
                    </div>
                </div>
            <?php 
                }
            } else {
                echo "<p class='text-muted'>No tools available right now.</p>";
            }
            ?>
        </div>
    </div>

    <div class="modal fade" id="borrowModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="borrow_process.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Borrow Request</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>You are requesting: <b id="toolNameDisplay"></b></p>
                        <input type="hidden" name="tool_id" id="toolIdInput">
                        
                        <div class="mb-3">
                            <label>When will you return it?</label>
                            <input type="date" name="return_date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Confirm Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        var borrowModal = document.getElementById('borrowModal');
        borrowModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var toolId = button.getAttribute('data-id');
            var toolName = button.getAttribute('data-name');
            
            document.getElementById('toolIdInput').value = toolId;
            document.getElementById('toolNameDisplay').textContent = toolName;
        });
    </script>
</body>
</html>