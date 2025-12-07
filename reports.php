<?php 
session_start();
include "db_conn.php";

// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

// --- DATA PREPARATION FOR CHARTS ---

// 1. PIE CHART: Tools per Category
$cat_labels = [];
$cat_data = [];
$sql_cat = "SELECT category, COUNT(*) as count FROM tools GROUP BY category";
$res_cat = mysqli_query($conn, $sql_cat);
while ($row = mysqli_fetch_assoc($res_cat)) {
    $cat_labels[] = $row['category'];
    $cat_data[] = $row['count'];
}

// 2. BAR CHART: Top 5 Most Borrowed Tools
$top_labels = [];
$top_data = [];
$sql_top = "SELECT t.tool_name, COUNT(tr.transaction_id) as usage_count 
            FROM transactions tr
            JOIN tools t ON tr.tool_id = t.tool_id
            GROUP BY tr.tool_id
            ORDER BY usage_count DESC LIMIT 5";
$res_top = mysqli_query($conn, $sql_top);
while ($row = mysqli_fetch_assoc($res_top)) {
    $top_labels[] = $row['tool_name'];
    $top_data[] = $row['usage_count'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Analytics & Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">

    <nav class="navbar navbar-dark bg-secondary px-4">
        <span class="navbar-brand mb-0 h1">📊 Analytics Dashboard</span>
        <div>
            <a href="dashboard.php" class="btn btn-outline-light btn-sm me-2">Back to Home</a>
            <a href="logout.php" class="btn btn-dark btn-sm">Logout</a>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card shadow">
                    <div class="card-header bg-white fw-bold">Inventory Distribution (By Category)</div>
                    <div class="card-body">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card shadow">
                    <div class="card-header bg-white fw-bold">Top 5 Most Borrowed Tools</div>
                    <div class="card-body">
                        <canvas id="usageChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center my-4">
            <button onclick="window.print()" class="btn btn-primary btn-lg">🖨️ Print / Save as PDF</button>
        </div>
    </div>

    <script>
        // 1. CONFIG FOR PIE CHART
        const ctx1 = document.getElementById('categoryChart');
        new Chart(ctx1, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($cat_labels); ?>,
                datasets: [{
                    label: 'Number of Tools',
                    data: <?php echo json_encode($cat_data); ?>,
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF'
                    ],
                    hoverOffset: 4
                }]
            }
        });

        // 2. CONFIG FOR BAR CHART
        const ctx2 = document.getElementById('usageChart');
        new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($top_labels); ?>,
                datasets: [{
                    label: 'Times Borrowed',
                    data: <?php echo json_encode($top_data); ?>,
                    backgroundColor: '#36A2EB',
                    borderColor: '#2485C9',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } }
                }
            }
        });
    </script>

</body>
</html>