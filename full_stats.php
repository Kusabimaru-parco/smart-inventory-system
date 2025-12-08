<?php 
session_start();
include "db_conn.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    die("Access Denied");
}

// --- GET FILTERS ---
$filter_type = isset($_GET['filter_type']) ? $_GET['filter_type'] : 'daily';
$filter_value = isset($_GET['filter_value']) ? $_GET['filter_value'] : date('Y-m-d');
$subtitle = "";

// --- REUSE DATE LOGIC ---
$date_condition = "";
if ($filter_type == 'daily') {
    $date_condition = "DATE(tr.borrow_date) = '$filter_value'";
    $subtitle = "Daily: " . date('F d, Y', strtotime($filter_value));
} elseif ($filter_type == 'monthly') {
    $month = date('m', strtotime($filter_value));
    $year = date('Y', strtotime($filter_value));
    $date_condition = "MONTH(tr.borrow_date) = '$month' AND YEAR(tr.borrow_date) = '$year'";
    $subtitle = "Monthly: " . date('F Y', strtotime($filter_value));
} elseif ($filter_type == 'yearly') {
    $year = date('Y', strtotime($filter_value));
    if(strlen($filter_value) == 4) $year = $filter_value; 
    $date_condition = "YEAR(tr.borrow_date) = '$year'";
    $subtitle = "Annual: " . $year;
}

// --- QUERY ---
$labels = [];
$data = [];
$tool_rows = [];

$sql = "SELECT t.tool_name, t.category, COUNT(tr.transaction_id) as usage_count 
        FROM tools t
        LEFT JOIN transactions tr ON t.tool_id = tr.tool_id AND $date_condition
        GROUP BY t.tool_id 
        ORDER BY usage_count DESC, t.tool_name ASC";

$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    $labels[] = $row['tool_name'];
    $data[] = $row['usage_count'];
    $tool_rows[] = $row; 
}

$chart_height = count($labels) * 40; 
if ($chart_height < 400) $chart_height = 400; 
?>

<!DOCTYPE html>
<html>
<head>
    <title>Full Statistics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* PRINT STYLING */
        @media print {
            .no-print { display: none !important; }
            .container { box-shadow: none !important; padding: 0 !important; max-width: 100% !important; }
            .card { border: none !important; }
            body { background-color: white !important; }
            
            /* FORCE COLORS TO PRINT */
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        }
    </style>
</head>
<body class="bg-light p-4">

    <div class="container bg-white shadow p-4 rounded">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="mb-0">📈 Tool Utilization Report</h3>
                <p class="text-muted mb-0">Period: <strong><?php echo $subtitle; ?></strong></p>
            </div>
            <button onclick="window.print()" class="btn btn-outline-dark no-print">🖨️ Print</button>
        </div>

        <div class="card mb-5">
            <div class="card-body">
                <div style="height: <?php echo $chart_height; ?>px; width: 100%;">
                    <canvas id="fullChart"></canvas>
                </div>
            </div>
        </div>

        <h5 class="mb-3">Detailed Data</h5>
        <table class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Rank</th>
                    <th>Tool Name</th>
                    <th>Category</th>
                    <th class="text-center">Times Borrowed</th>
                    <th>Utilization</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $rank = 1;
                $max = $data[0] > 0 ? $data[0] : 1; 
                foreach ($tool_rows as $row) {
                    $width = ($row['usage_count'] / $max) * 100;
                    $color = ($row['usage_count'] == 0) ? 'text-muted' : 'fw-bold';
                ?>
                    <tr>
                        <td><?php echo $rank++; ?></td>
                        <td class="<?php echo $color; ?>"><?php echo $row['tool_name']; ?></td>
                        <td><?php echo $row['category']; ?></td>
                        <td class="text-center <?php echo $color; ?>" style="font-size: 1.1rem;">
                            <?php echo $row['usage_count']; ?>
                        </td>
                        <td style="width: 200px; vertical-align: middle;">
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-primary" style="width: <?php echo $width; ?>%"></div>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

    </div>

    <script>
        const ctx = document.getElementById('fullChart');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($labels); ?>,
                datasets: [{
                    label: 'Times Borrowed',
                    data: <?php echo json_encode($data); ?>,
                    backgroundColor: '#36A2EB',
                    borderColor: '#2485C9',
                    borderWidth: 1,
                    barPercentage: 0.7
                }]
            },
            options: {
                indexAxis: 'y', 
                maintainAspectRatio: false, 
                scales: {
                    x: { beginAtZero: true, ticks: { stepSize: 1 } }
                },
                plugins: {
                    legend: { display: false },
                    title: { display: true, text: 'Usage Frequency (High to Low)' }
                }
            }
        });
    </script>
</body>
</html>