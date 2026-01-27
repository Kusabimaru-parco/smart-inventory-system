<?php 
session_start();
include "db_conn.php";

// Security Check
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'student_assistant')) {
    header("Location: index.php");
    exit();
}

// --- 1. FILTER LOGIC ---
$filter_type = isset($_GET['filter_type']) ? $_GET['filter_type'] : 'monthly';
$filter_value = isset($_GET['filter_value']) ? $_GET['filter_value'] : date('Y-m');
$subtitle = "";
$date_condition = "";

// Build Date Condition
if ($filter_type == 'daily') {
    $date_condition = "DATE(tr.date_requested) = '$filter_value'";
    $subtitle = "Daily Report: " . date('F d, Y', strtotime($filter_value));
} elseif ($filter_type == 'monthly') {
    $month = date('m', strtotime($filter_value));
    $year = date('Y', strtotime($filter_value));
    $date_condition = "MONTH(tr.date_requested) = '$month' AND YEAR(tr.date_requested) = '$year'";
    $subtitle = "Monthly Report: " . date('F Y', strtotime($filter_value));
} elseif ($filter_type == 'yearly') {
    $year = $filter_value;
    $date_condition = "YEAR(tr.date_requested) = '$year'";
    $subtitle = "Annual Report: " . $year;
}

// --- 2. KEY METRICS (KPIs) ---
$sql_total = "SELECT COUNT(*) as count FROM transactions tr WHERE $date_condition";
$total_trans = mysqli_fetch_assoc(mysqli_query($conn, $sql_total))['count'];
if ($total_trans == 0) $total_trans = 1; 

$sql_active = "SELECT COUNT(*) as count FROM transactions tr WHERE $date_condition AND status = 'Borrowed'";
$active_trans = mysqli_fetch_assoc(mysqli_query($conn, $sql_active))['count'];

$sql_returned = "SELECT COUNT(*) as count FROM transactions tr WHERE $date_condition AND status = 'Returned'";
$returned_trans = mysqli_fetch_assoc(mysqli_query($conn, $sql_returned))['count'];

// --- 3. CHART DATA ---
$cat_labels = []; $cat_data = [];
$sql_cat = "SELECT t.category, COUNT(tr.transaction_id) as count 
            FROM transactions tr 
            JOIN tools t ON tr.tool_id = t.tool_id
            WHERE $date_condition
            GROUP BY t.category ORDER BY count DESC LIMIT 5";
$res_cat = mysqli_query($conn, $sql_cat);
while ($row = mysqli_fetch_assoc($res_cat)) {
    $cat_labels[] = $row['category'];
    $cat_data[] = $row['count'];
}

$top_labels = []; $top_data = [];
$sql_top = "SELECT t.tool_name, COUNT(tr.transaction_id) as count 
            FROM transactions tr 
            JOIN tools t ON tr.tool_id = t.tool_id
            WHERE $date_condition
            GROUP BY t.tool_name ORDER BY count DESC LIMIT 5";
$res_top = mysqli_query($conn, $sql_top);
while ($row = mysqli_fetch_assoc($res_top)) {
    $top_labels[] = $row['tool_name'];
    $top_data[] = $row['count'];
}

// --- 4. MOST ACTIVE ADMIN (FULL NAME RESOLUTION) ---
$active_admin = "None";
$sql_admin = "SELECT processed_by, COUNT(*) as count 
              FROM transactions tr 
              WHERE $date_condition AND processed_by IS NOT NULL AND processed_by != ''
              GROUP BY processed_by ORDER BY count DESC LIMIT 1";
$res_admin = mysqli_query($conn, $sql_admin);

if(mysqli_num_rows($res_admin) > 0) {
    $row_admin = mysqli_fetch_assoc($res_admin);
    $raw_processor = $row_admin['processed_by']; 
    $proc_count = $row_admin['count'];

    // Lookup Real Name
    $safe_proc = mysqli_real_escape_string($conn, $raw_processor);
    $name_sql = "SELECT full_name FROM users WHERE id_number = '$safe_proc' LIMIT 1";
    $name_res = mysqli_query($conn, $name_sql);

    if (mysqli_num_rows($name_res) > 0) {
        $final_name = mysqli_fetch_assoc($name_res)['full_name'];
    } else {
        $final_name = $raw_processor; 
    }
    $active_admin = $final_name . " (" . $proc_count . ")";
}

// --- 5. TOOL UTILIZATION TABLE & ACTIVE STUDENT ---
$sql_util = "SELECT t.tool_name, t.category, 
                    COUNT(tr.transaction_id) as usage_count,
                    COUNT(DISTINCT tr.user_id) as unique_users,
                    SUM(CASE WHEN tr.status = 'Returned' THEN 1 ELSE 0 END) as returned_count,
                    SUM(CASE WHEN tr.status = 'Borrowed' THEN 1 ELSE 0 END) as active_count
             FROM transactions tr
             JOIN tools t ON tr.tool_id = t.tool_id
             WHERE $date_condition
             GROUP BY t.tool_name
             ORDER BY usage_count DESC";
$res_util = mysqli_query($conn, $sql_util);

$active_student = "None";
$sql_student = "SELECT u.full_name, COUNT(*) as count 
                FROM transactions tr 
                JOIN users u ON tr.user_id = u.user_id
                WHERE $date_condition
                GROUP BY tr.user_id ORDER BY count DESC LIMIT 1";
$res_student = mysqli_query($conn, $sql_student);
if(mysqli_num_rows($res_student) > 0) {
    $row_student = mysqli_fetch_assoc($res_student);
    $active_student = $row_student['full_name'] . " (" . $row_student['count'] . ")";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Analytics Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background-color: #f8f9fa; }
        .card { border: none; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); transition: transform 0.2s; }
        .card:hover { transform: translateY(-2px); box-shadow: 0 6px 12px rgba(0,0,0,0.1); }
        .metric-card { color: white; }
        .metric-title { font-size: 0.9rem; opacity: 0.8; text-transform: uppercase; letter-spacing: 1px; }
        .metric-value { font-size: 2.5rem; font-weight: bold; }
        .chart-container { position: relative; height: 300px; width: 100%; }
        .progress { height: 8px; border-radius: 4px; }
        
        /* --- OPTIMIZED PRINT STYLES (PORTRAIT FIX) --- */
        @media print {
            .no-print { display: none !important; }
            body { background-color: white !important; padding: 0; margin: 0; font-size: 12px; }
            .container-fluid { padding: 0 !important; width: 100% !important; }
            
            /* 1. Header Adjustment */
            .text-center.mb-4 { margin-bottom: 10px !important; }
            h2 { font-size: 24px !important; margin: 0 !important; }

            /* 2. Metrics Grid (2x2 Layout) */
            .row.g-3.mb-4 { display: flex; flex-wrap: wrap; margin-bottom: 15px !important; }
            .col-md-3 { 
                width: 50% !important; 
                flex: 0 0 50% !important; 
                margin-bottom: 10px !important;
                padding: 5px !important;
            }
            .metric-card {
                border: 1px solid #ccc !important;
                box-shadow: none !important;
                color: black !important;
                background: white !important;
                padding: 10px !important;
            }
            .metric-value { font-size: 1.8rem !important; color: black !important; }
            .metric-card i { display: none !important; } /* Hide icons to save space */

            /* 3. Charts: Stack Vertically & Reduce Height */
            .row.g-4.mb-4 { display: block !important; margin-bottom: 10px !important; }
            .col-md-6 { 
                width: 100% !important; 
                display: block !important; 
                margin-bottom: 15px !important;
                page-break-inside: avoid !important; /* Prevent cutting chart in half */
            }
            .card { 
                border: 1px solid #ddd !important; 
                box-shadow: none !important; 
                page-break-inside: avoid !important; 
            }
            .chart-container { 
                height: 250px !important; /* Smaller height specifically for print */
                width: 100% !important; 
            }
            canvas { width: 100% !important; height: 100% !important; }

            /* 4. Table */
            .table-responsive { overflow: visible !important; }
            .table { width: 100% !important; border-collapse: collapse; }
            th, td { padding: 4px 8px !important; font-size: 10px; border: 1px solid #ddd !important; }
            
            /* Hide Badges Backgrounds for simpler print */
            .badge { border: 1px solid #000; color: #000; background: none !important; }
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-dark bg-dark px-4 no-print sticky-top">
        <span class="navbar-brand mb-0 h1"><i class="bi bi-graph-up-arrow text-warning"></i> Analytics Dashboard</span>
        <div>
            <a href="dashboard.php" class="btn btn-outline-light btn-sm me-2">Back to Home</a>
            <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
        </div>
    </nav>

    <div class="container-fluid px-4 mt-4">
        
        <div class="card mb-4 no-print">
            <div class="card-body py-3">
                <form method="GET" class="row g-2 align-items-center">
                    <div class="col-auto"><label class="fw-bold">Report Type:</label></div>
                    <div class="col-auto">
                        <select name="filter_type" id="filterType" class="form-select form-select-sm" onchange="toggleInput()">
                            <option value="daily" <?php if($filter_type=='daily') echo 'selected'; ?>>Daily</option>
                            <option value="monthly" <?php if($filter_type=='monthly') echo 'selected'; ?>>Monthly</option>
                            <option value="yearly" <?php if($filter_type=='yearly') echo 'selected'; ?>>Yearly</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <input type="date" name="filter_value" id="dailyInput" class="form-control form-control-sm" value="<?php echo ($filter_type=='daily') ? $filter_value : date('Y-m-d'); ?>">
                        <input type="month" name="filter_value" id="monthlyInput" class="form-control form-control-sm" value="<?php echo ($filter_type=='monthly') ? $filter_value : date('Y-m'); ?>" style="display:none;">
                        <input type="number" name="filter_value" id="yearlyInput" class="form-control form-control-sm" placeholder="YYYY" value="<?php echo ($filter_type=='yearly') ? $filter_value : date('Y'); ?>" min="2020" max="2099" style="display:none;">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary btn-sm px-4">Generate Report</button>
                    </div>
                    <div class="col text-end">
                        <button type="button" onclick="window.print()" class="btn btn-dark btn-sm"><i class="bi bi-printer"></i> Print Report</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="text-center mb-4">
            <h2 class="fw-bold text-dark"><?php echo $subtitle; ?></h2>
            <p class="text-muted">Generated by Smart Inventory System • <?php echo date('F d, Y h:i A'); ?></p>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-3 col-6">
                <div class="card metric-card bg-primary p-3">
                    <div class="metric-title">Total Transactions</div>
                    <div class="metric-value"><?php echo ($total_trans == 1 && $active_trans == 0 && $returned_trans == 0) ? 0 : $total_trans; ?></div>
                    <i class="bi bi-receipt position-absolute top-0 end-0 m-3 fs-1 opacity-25"></i>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card metric-card bg-success p-3">
                    <div class="metric-title">Returned Tools</div>
                    <div class="metric-value"><?php echo $returned_trans; ?></div>
                    <i class="bi bi-check-circle position-absolute top-0 end-0 m-3 fs-1 opacity-25"></i>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card metric-card bg-warning text-dark p-3">
                    <div class="metric-title">Active / Borrowed</div>
                    <div class="metric-value"><?php echo $active_trans; ?></div>
                    <i class="bi bi-hourglass-split position-absolute top-0 end-0 m-3 fs-1 opacity-25"></i>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card metric-card bg-info text-dark p-3">
                    <div class="metric-title">Most Active Admin</div>
                    <div class="metric-value fs-4 text-truncate" title="<?php echo $active_admin; ?>">
                        <?php 
                        if ($active_admin == "None") {
                            echo "None";
                        } else {
                            echo trim(substr($active_admin, 0, strrpos($active_admin, '('))); 
                        }
                        ?>
                    </div>
                    <div class="small opacity-75">
                        <?php 
                        if ($active_admin != "None") {
                            echo "Processed: " . substr($active_admin, strpos($active_admin, '(')); 
                        }
                        ?>
                    </div>
                    <i class="bi bi-person-badge position-absolute top-0 end-0 m-3 fs-1 opacity-25"></i>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header bg-white fw-bold">📉 Borrowing Trends by Category</div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="categoryChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header bg-white fw-bold d-flex justify-content-between">
                        <span>🏆 Top 5 Most Used Tools</span>
                        <span class="badge bg-light text-dark border">By Frequency</span>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="topToolsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card bg-light border-0 mb-4">
            <div class="card-body d-flex justify-content-around text-center">
                <div>
                    <h6 class="text-muted text-uppercase small fw-bold">Top Borrower</h6>
                    <h5 class="text-primary fw-bold"><?php echo $active_student; ?></h5>
                </div>
                <div class="border-start"></div>
                <div>
                    <h6 class="text-muted text-uppercase small fw-bold">Popular Category</h6>
                    <h5 class="text-success fw-bold"><?php echo !empty($cat_labels) ? $cat_labels[0] : 'None'; ?></h5>
                </div>
                <div class="border-start"></div>
                <div>
                    <h6 class="text-muted text-uppercase small fw-bold">Utilization Rate</h6>
                    <h5 class="text-danger fw-bold">
                        <?php echo ($total_trans > 0) ? round(($returned_trans / $total_trans) * 100) . '%' : '0%'; ?>
                    </h5>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-5">
            <div class="card-header bg-secondary text-white fw-bold d-flex justify-content-between">
                <span><i class="bi bi-tools"></i> Tool Utilization Log</span>
                <span class="badge bg-light text-dark"><?php echo mysqli_num_rows($res_util); ?> Tools</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 50px;">#</th>
                                <th>Tool Name</th>
                                <th>Category</th>
                                <th class="text-center">Times Borrowed</th>
                                <th class="text-center">Unique Users</th>
                                <th style="width: 200px;">Utilization Rate</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if (mysqli_num_rows($res_util) > 0) {
                                $rank = 1;
                                while ($row = mysqli_fetch_assoc($res_util)) {
                                    $percentage = ($total_trans > 0) ? round(($row['usage_count'] / $total_trans) * 100, 1) : 0;
                                    $progress_color = 'bg-info';
                                    if ($percentage > 20) $progress_color = 'bg-success';
                                    if ($percentage > 50) $progress_color = 'bg-warning';
                                    if ($percentage > 80) $progress_color = 'bg-danger';
                            ?>
                                <tr>
                                    <td class="text-center fw-bold text-muted"><?php echo $rank++; ?></td>
                                    <td class="fw-bold text-primary"><?php echo $row['tool_name']; ?></td>
                                    <td><span class="badge bg-light text-dark border"><?php echo $row['category']; ?></span></td>
                                    <td class="text-center fw-bold"><?php echo $row['usage_count']; ?></td>
                                    <td class="text-center"><?php echo $row['unique_users']; ?></td>
                                    <td>
                                        <div class="d-flex justify-content-between small mb-1">
                                            <span>Share:</span>
                                            <span class="fw-bold"><?php echo $percentage; ?>%</span>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar <?php echo $progress_color; ?>" role="progressbar" 
                                                 style="width: <?php echo $percentage; ?>%"></div>
                                        </div>
                                    </td>
                                    <td class="text-center small">
                                        <?php if($row['active_count'] > 0): ?>
                                            <span class="text-danger fw-bold"><?php echo $row['active_count']; ?> Active</span>
                                        <?php else: ?>
                                            <span class="text-success">All Returned</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php 
                                }
                            } else {
                                echo "<tr><td colspan='7' class='text-center py-5 text-muted'>No usage data found for this period.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
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

            if (type === 'daily') { daily.style.display = 'block'; daily.disabled = false; }
            else if (type === 'monthly') { monthly.style.display = 'block'; monthly.disabled = false; }
            else if (type === 'yearly') { yearly.style.display = 'block'; yearly.disabled = false; }
        }
        window.onload = toggleInput;

        // CHART CONFIG
        const commonOptions = {
            responsive: true,
            maintainAspectRatio: false, // Critical for print resizing
            plugins: { legend: { position: 'bottom' } }
        };

        const ctxCat = document.getElementById('categoryChart').getContext('2d');
        new Chart(ctxCat, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($cat_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($cat_data); ?>,
                    backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796'],
                    borderWidth: 1
                }]
            },
            options: commonOptions
        });

        const ctxTop = document.getElementById('topToolsChart').getContext('2d');
        new Chart(ctxTop, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($top_labels); ?>,
                datasets: [{
                    label: 'Usage Count',
                    data: <?php echo json_encode($top_data); ?>,
                    backgroundColor: '#4e73df',
                    borderRadius: 5
                }]
            },
            options: commonOptions
        });
    </script>

</body>
</html>