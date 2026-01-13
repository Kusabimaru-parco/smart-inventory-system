<?php 
session_start();
// Security
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'student_assistant')) {
    die("Access Denied");
}

$filter_date = isset($_GET['date']) ? $_GET['date'] : '';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Transaction History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <style>
        @media print {
            .no-print { display: none !important; }
            .card, .container { box-shadow: none !important; border: none !important; }
        }
    </style>
</head>
<body class="bg-light p-4">

    <div class="container bg-white p-4 shadow rounded">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3>📜 Full Transaction History</h3>
            <button onclick="window.print()" class="btn btn-outline-secondary no-print">
                <i class="bi bi-printer"></i> Print Report
            </button>
        </div>

        <form method="GET" class="row g-3 mb-4 border p-3 rounded bg-light no-print">
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

        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Control No.</th> 
                        <th>Main Date</th>
                        <th>Student Name</th>
                        <th>Tool Name</th>
                        <th>Subject / Room</th>
                        <th>Date & Time Borrowed</th> <th>Date & Time Returned</th> 
                        <th>Processed By</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="historyTableBody">
                    </tbody>
            </table>
        </div>

        <div class="text-center mt-3 no-print">
            <div id="loadingSpinner" class="spinner-border text-primary" role="status" style="display:none;">
                <span class="visually-hidden">Loading...</span>
            </div>
            
            <button id="loadMoreBtn" class="btn btn-primary px-5" style="display:none;">
                Load More Records <i class="bi bi-chevron-down"></i>
            </button>
            
            <p id="endMessage" class="text-muted fst-italic mt-2" style="display:none;">
                End of records.
            </p>
        </div>

    </div>

    <script>
        $(document).ready(function(){
            let offset = 0;
            const limit = 50; 
            let filterDate = "<?php echo $filter_date; ?>";
            let isLoading = false;

            function loadHistory() {
                if(isLoading) return;
                isLoading = true;
                $('#loadingSpinner').show();
                $('#loadMoreBtn').hide();

                $.ajax({
                    url: 'fetch_admin_history.php',
                    type: 'POST',
                    data: { 
                        offset: offset,
                        date: filterDate
                    },
                    success: function(response) {
                        $('#loadingSpinner').hide();
                        
                        // Check if empty
                        if($.trim(response) === "") {
                            if(offset === 0) {
                                $('#historyTableBody').html("<tr><td colspan='9' class='text-center py-4 text-muted'>No records found.</td></tr>");
                            } else {
                                $('#endMessage').show();
                            }
                        } else {
                            $('#historyTableBody').append(response);
                            offset += limit;
                            isLoading = false;
                            $('#loadMoreBtn').show();
                        }
                    },
                    error: function() {
                        $('#loadingSpinner').hide();
                        alert("Error loading data.");
                    }
                });
            }

            // Initial Load
            loadHistory();

            // Click Handler
            $('#loadMoreBtn').click(function(){
                loadHistory();
            });
        });
    </script>

</body>
</html>