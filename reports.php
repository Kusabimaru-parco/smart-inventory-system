<?php 
session_start();
include "db_conn.php";

// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

// --- 1. GET FILTER INPUTS FIRST ---
$filter_type = isset($_GET['filter_type']) ? $_GET['filter_type'] : 'daily';
$filter_value = isset($_GET['filter_value']) ? $_GET['filter_value'] : date('Y-m-d');
$subtitle = "";

// --- 2. BUILD THE DATE CONDITION (Reusable for all queries) ---
$date_condition = "";

if ($filter_type == 'daily') {
    $date_condition = "DATE(tr.borrow_date) = '$filter_value'";
    $subtitle = "Daily Report: " . date('F d, Y', strtotime($filter_value));

} elseif ($filter_type == 'monthly') {
    $month = date('m', strtotime($filter_value));
    $year = date('Y', strtotime($filter_value));
    $date_condition = "MONTH(tr.borrow_date) = '$month' AND YEAR(tr.borrow_date) = '$year'";
    $subtitle = "Monthly Report: " . date('F Y', strtotime($filter_value));

} elseif ($filter_type == 'yearly') {
    $year = date('Y', strtotime($filter_value));
    if(strlen($filter_value) == 4) $year = $filter_value; 
    $date_condition = "YEAR(tr.borrow_date) = '$year'";
    $subtitle = "Annual Report: " . $year;
}

// --- 3. CHART 1: Transactions by Category (Filtered) ---
// Shows "Which category was borrowed most during this period?"
$cat_labels = []; 
$cat_data = [];
$sql_cat = "SELECT t.category, COUNT(tr.transaction_id) as count 
            FROM transactions tr 
            JOIN tools t ON tr.tool_id = t.tool_id
            WHERE $date_condition
            GROUP BY t.category";
$res_cat = mysqli_query($conn, $sql_cat);

if(mysqli_num_rows($res_cat) > 0) {
    while ($row = mysqli_fetch_assoc($res_cat)) {
        $cat_labels[] = $row['category'];
        $cat_data[] = $row['count'];
    }
} else {
    // Empty state for charts
    $cat_labels[] = "No Data"; $cat_data[] = 0;
}

// --- 4. CHART 2: Top Tools Borrowed (Filtered) ---
// Shows "Which specific tools were popular during this period?"
$top_labels = []; 
$top_data = [];
$sql_top = "SELECT t.tool_name, COUNT(tr.transaction_id) as usage_count 
            FROM transactions tr 
            JOIN tools t ON tr.tool_id = t.tool_id
            WHERE $date_condition
            GROUP BY tr.tool_id 
            ORDER BY usage_count DESC LIMIT 5";
$res_top = mysqli_query($conn, $sql_top);

if(mysqli_num_rows($res_top) > 0) {
    while ($row = mysqli_fetch_assoc($res_top)) {
        $top_labels[] = $row['tool_name'];
        $top_data[] = $row['usage_count'];
    }
} else {
    $top_labels[] = "No Data"; $top_data[] = 0;
}

// --- 5. REPORT TABLE DATA (Filtered) ---
$sql_report = "SELECT tr.status, tr.borrow_date, tr.return_date, tr.actual_return_date, 
                      u.full_name, t.tool_name 
               FROM transactions tr
               JOIN users u ON tr.user_id = u.user_id
               JOIN tools t ON tr.tool_id = t.tool_id
               WHERE $date_condition
               ORDER BY tr.borrow_date DESC";
$res_report = mysqli_query($conn, $sql_report);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Analytics & Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            .card { border: none !important; box-shadow: none !important; }
        }
    </style>
</head>
<body class="bg-light">

    <nav class="navbar navbar-dark bg-secondary px-4 no-print">
        <span class="navbar-brand mb-0 h1">📊 Analytics Dashboard</span>
        <div>
            <a href="dashboard.php" class="btn btn-outline-light btn-sm me-2">Back to Home</a>
            <a href="logout.php" class="btn btn-dark btn-sm">Logout</a>
        </div>
    </nav>

    <div class="container mt-5">
        
        <div class="row mb-5">
            <div class="col-md-6 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header fw-bold">Borrowing Distribution (By Category)</div>
                    <div class="card-body">
                        <canvas id="categoryChart" style="max-height: 250px;"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header fw-bold d-flex justify-content-between align-items-center">
                        <span>Most Borrowed Tools (Top 5)</span>
                        <a href="full_stats.php?filter_type=<?php echo $filter_type; ?>&filter_value=<?php echo $filter_value; ?>" 
                           target="_blank" 
                           class="btn btn-sm btn-outline-primary">
                           <i class="bi bi-arrows-fullscreen"></i> View Full List
                        </a>
                    </div>
                    <div class="card-body">
                        <canvas id="usageChart" style="max-height: 250px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-5 no-print">

        <div class="card shadow-sm mb-4 no-print">
            <div class="card-body">
                <h5 class="card-title mb-3"><i class="bi bi-funnel"></i> Generate Transaction History</h5>
                <form method="GET" class="row g-3 align-items-end">

           
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Report Type</label>
                        <select name="filter_type" id="filterType" class="form-select" onchange="toggleInput()">
                            <option value="daily" <?php if($filter_type=='daily') echo 'selected'; ?>>Daily (Specific Date)</option>
                            <option value="monthly" <?php if($filter_type=='monthly') echo 'selected'; ?>>Monthly (Month/Year)</option>
                            <option value="yearly" <?php if($filter_type=='yearly') echo 'selected'; ?>>Yearly (Annual)</option>
                        </select>
                    </div>
                    

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Select Period</label>
                        <input type="date" name="filter_value" id="dailyInput" class="form-control" 
                               value="<?php echo $filter_value; ?>">
                        
                        <input type="month" name="filter_value" id="monthlyInput" class="form-control" 
                               value="<?php echo (strlen($filter_value) > 4) ? substr($filter_value, 0, 7) : date('Y-m'); ?>" 
                               disabled style="display:none;">
                        
                        <input type="number" name="filter_value" id="yearlyInput" class="form-control" 
                               placeholder="Enter Year (e.g. 2025)" value="<?php echo substr($filter_value, 0, 4); ?>" 
                               min="2000" max="2099" disabled style="display:none;">
                    </div>

                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Generate</button>
                    </div>
                    
                    <div class="col-md-2">
                        <button type="button" onclick="window.print()" class="btn btn-outline-dark w-100">🖨️ Print Report</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white d-flex justify-content-between">
                <span>📄 <?php echo $subtitle; ?></span>
                <span class="badge bg-light text-dark"><?php echo mysqli_num_rows($res_report); ?> Records Found</span>
            </div>
            <div class="card-body">
                <table class="table table-striped table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Borrow Date</th>
                            <th>Student Name</th>
                            <th>Tool Name</th>
                            <th>Due Date</th>
                            <th>Returned On</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if (mysqli_num_rows($res_report) > 0) {
                            while ($row = mysqli_fetch_assoc($res_report)) {
                                $status_color = 'secondary';
                                if ($row['status'] == 'Borrowed') $status_color = 'warning';
                                if ($row['status'] == 'Returned') $status_color = 'success';
                                if ($row['status'] == 'Overdue') $status_color = 'danger';
                        ?>
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($row['borrow_date'])); ?></td>
                                <td><?php echo $row['full_name']; ?></td>
                                <td><?php echo $row['tool_name']; ?></td>
                                <td><?php echo date('M d', strtotime($row['return_date'])); ?></td>
                                <td>
                                    <?php echo ($row['actual_return_date']) ? date('M d, h:i A', strtotime($row['actual_return_date'])) : '--'; ?>
                                </td>
                                <td><span class="badge bg-<?php echo $status_color; ?>"><?php echo $row['status']; ?></span></td>
                            </tr>
                        <?php 
                            }
                        } else {
                            echo "<tr><td colspan='6' class='text-center py-4 text-muted'>No transactions found for this period.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script>
        function toggleInput() {
            var type = document.getElementById('filterType').value;
            var daily = document.getElementById('dailyInput');
            var monthly = document.getElementById('monthlyInput');
            var yearly = document.getElementById('yearlyInput');

            daily.style.display = 'none'; daily.disabled = true;
            monthly.style.display = 'none'; monthly.disabled = true;
            yearly.style.display = 'none'; yearly.disabled = true;

            if (type === 'daily') {
                daily.style.display = 'block'; daily.disabled = false;
            } else if (type === 'monthly') {
                monthly.style.display = 'block'; monthly.disabled = false;
            } else if (type === 'yearly') {
                yearly.style.display = 'block'; yearly.disabled = false;
            }
        }
        window.onload = function() { toggleInput(); };

        // --- DYNAMIC CHARTS (Now uses filtered data) ---
        const ctx1 = document.getElementById('categoryChart');
        new Chart(ctx1, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($cat_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($cat_data); ?>,
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF']
                }]
            }
        });

        const ctx2 = document.getElementById('usageChart');
        new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($top_labels); ?>,
                datasets: [{
                    label: 'Times Borrowed',
                    data: <?php echo json_encode($top_data); ?>,
                    backgroundColor: '#36A2EB'
                }]
            },
            options: {
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });
    </script>

</body>
</html>