<?php
session_start();
include "db_conn.php";

// Security
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'student_assistant')) {
    header("Location: index.php");
    exit();
}

$search = isset($_GET['search']) ? $_GET['search'] : '';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Search Borrower Slip</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-light">

    <nav class="navbar navbar-dark bg-dark px-3 mb-4">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1"><i class="bi bi-printer"></i> Print Slip Search</span>
            <a href="dashboard.php" class="btn btn-outline-light btn-sm">Back to Dashboard</a>
        </div>
    </nav>

    <div class="container">
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="GET" class="row g-2 mb-4">
                    <div class="col-md-9">
                        <input type="text" name="search" class="form-control form-control-lg" 
                               placeholder="Enter Control Number (e.g. 20260114-001) or Student Name..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary btn-lg w-100"><i class="bi bi-search"></i> Search</button>
                    </div>
                </form>

                <h5 class="mb-3 text-secondary">
                    <?php echo ($search != '') ? 'Search Results for: "' . htmlspecialchars($search) . '"' : 'Recent Transactions'; ?>
                </h5>

                <div class="table-responsive">
                    <table class="table table-hover align-middle border">
                        <thead class="table-light">
                            <tr>
                                <th>Control No.</th>
                                <th>Student Name</th>
                                <th>Date Requested</th>
                                <th>Status</th>
                                <th>Admin Remarks</th> <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="resultsBody">
                        </tbody>
                    </table>
                </div>

                <div class="text-center mt-3">
                    <div id="loadingSpinner" class="spinner-border text-primary" role="status" style="display:none;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    
                    <button id="loadMoreBtn" class="btn btn-outline-primary" style="display:none;">
                        Load More Records <i class="bi bi-chevron-down"></i>
                    </button>
                    
                    <p id="endMessage" class="text-muted small mt-2" style="display:none;">End of results.</p>
                </div>

            </div>
        </div>
    </div>

    <div class="modal fade" id="remarksModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Admin Remarks</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label fw-bold">Control No: <span id="modalControlNo" class="text-primary"></span></label>
                    <textarea id="modalRemarksText" class="form-control" rows="4" placeholder="Enter remarks about tool condition, late return reasons, etc..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success" id="saveRemarksBtn">Save Remarks</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function(){
            let offset = 0;
            const limit = 25; 
            let searchTerm = "<?php echo $search; ?>"; 
            let isLoading = false;

            loadData();

            function loadData() {
                if(isLoading) return;
                isLoading = true;
                $('#loadingSpinner').show();
                $('#loadMoreBtn').hide();

                $.ajax({
                    url: 'fetch_slip_search.php',
                    type: 'POST',
                    data: { 
                        offset: offset,
                        search: searchTerm
                    },
                    success: function(response) {
                        $('#loadingSpinner').hide();
                        
                        if($.trim(response) === "") {
                            if(offset === 0) {
                                $('#resultsBody').html("<tr><td colspan='6' class='text-center py-4 text-muted'>No records found.</td></tr>");
                            } else {
                                $('#endMessage').show();
                            }
                        } else {
                            $('#resultsBody').append(response);
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

            $('#loadMoreBtn').click(function(){
                loadData();
            });

            // --- MODAL LOGIC ---
            // Open Modal and populate data
            $(document).on('click', '.btn-edit-remarks', function() {
                let controlNo = $(this).data('control');
                let currentRemarks = $(this).data('remarks'); // Assuming you add data-remarks attribute in fetch script

                $('#modalControlNo').text(controlNo);
                $('#modalRemarksText').val(currentRemarks);
                $('#remarksModal').modal('show');
            });

            // Save Remarks via AJAX
            $('#saveRemarksBtn').click(function() {
                let controlNo = $('#modalControlNo').text();
                let remarks = $('#modalRemarksText').val();

                $.ajax({
                    url: 'update_remarks.php',
                    type: 'POST',
                    data: { control_no: controlNo, remarks: remarks },
                    success: function(response) {
                        if(response === 'Success') {
                            alert('Remarks Saved!');
                            $('#remarksModal').modal('hide');
                            // Optional: Reload specific row or page to show update
                            location.reload(); 
                        } else {
                            alert('Error saving: ' + response);
                        }
                    }
                });
            });
        });
    </script>

</body>
</html>