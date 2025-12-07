<?php 
session_start();
include "db_conn.php";

// Security
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    die("Access Denied");
}

// Filter Logic
$filter_date = isset($_GET['date']) ? $_GET['date'] : '';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Transaction History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">

    <div class="container bg-white p-4 shadow rounded">
        <div class="d-flex justify-content-between mb-4">
            <h3>📜 Full Transaction History</h3>
            <button onclick="window.print()" class="btn btn-outline-secondary">Print Report</button>
        </div>

        <form method="GET" class="row g-3 mb-4 border p-3 rounded bg-light">
            <div class="col-auto">
                <label class="col-form-label fw-bold">Filter by Date:</label>
            </div>
            <div class="col-auto">
                <input type="date" name="date" class="form-control" value="<?php echo $filter_date; ?>">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">Search</button>
                <a href="admin_history.php" class="btn btn-secondary">Reset</a>
            </div>
        </form>

        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Action</th>
                    <th>Tool Name</th>
                    <th>Student Name</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                // Build Query
                $sql = "SELECT tr.status, t.tool_name, u.full_name, tr.actual_return_date, tr.date_requested 
                        FROM transactions tr
                        JOIN users u ON tr.user_id = u.user_id
                        JOIN tools t ON tr.tool_id = t.tool_id";

                // Apply Filter if date is selected
                if (!empty($filter_date)) {
                    $sql .= " WHERE DATE(tr.date_requested) = '$filter_date' OR DATE(tr.actual_return_date) = '$filter_date'";
                }

                $sql .= " ORDER BY tr.transaction_id DESC";
                $result = mysqli_query($conn, $sql);

                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        
                        // Determine which date to use
                        $raw_date = ($row['status'] == 'Returned') ? $row['actual_return_date'] : $row['date_requested'];
                        $display_date = date('M d, Y', strtotime($raw_date));
                        $display_time = date('h:i A', strtotime($raw_date));
                        
                        $badge = ($row['status'] == 'Returned') ? 'success' : 'primary';
                ?>
                    <tr>
                        <td><?php echo $display_date; ?></td>
                        <td><?php echo $display_time; ?></td>
                        <td><span class="badge bg-<?php echo $badge; ?>"><?php echo $row['status']; ?></span></td>
                        <td><?php echo $row['tool_name']; ?></td>
                        <td><?php echo $row['full_name']; ?></td>
                        <td><?php echo $row['status']; ?></td>
                    </tr>
                <?php 
                    }
                } else {
                    echo "<tr><td colspan='6' class='text-center'>No records found for this date.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

</body>
</html>