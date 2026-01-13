<?php 
session_start();
include "db_conn.php";

// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Transaction History</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <style>
        @media (max-width: 576px) {
            .navbar-brand { font-size: 1.1rem; }
            .btn-sm { font-size: 0.8rem; }
            .badge { font-size: 0.65em; }
            .input-group { flex-direction: column; } 
            .input-group .form-control { width: 100% !important; border-radius: 5px !important; margin-bottom: 8px; }
            .input-group .btn { width: 100% !important; border-radius: 5px !important; }
        }
    </style>
</head>
<body class="bg-light">

    <nav class="navbar navbar-dark bg-dark px-3 sticky-top">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1 text-truncate"><i class="bi bi-clock-history"></i> History & Feedback</span>
            <a href="dashboard.php" class="btn btn-outline-light btn-sm text-nowrap">
                <i class="bi bi-arrow-left"></i> Dashboard
            </a>
        </div>
    </nav>

    <div class="container mt-4 mb-5" style="max-width: 800px;">
        
        <?php if (isset($_GET['msg'])) { ?>
            <div class="alert alert-success text-center py-2 shadow-sm mb-3"><?php echo htmlspecialchars($_GET['msg']); ?></div>
        <?php } ?>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-success text-white py-3">
                <h5 class="mb-0 fs-6"><i class="bi bi-check2-circle"></i> Completed Transactions</h5>
            </div>
            
            <div class="card-body p-2 p-md-3">
                <div id="historyContainer">
                    </div>

                <div id="loadingSpinner" class="text-center py-3" style="display:none;">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>

                <div class="text-center mt-3">
                    <button id="loadMoreBtn" class="btn btn-outline-success btn-sm px-4 fw-bold" style="display:none;">
                        Load More Records <i class="bi bi-chevron-down"></i>
                    </button>
                    <p id="endMessage" class="text-muted small mt-2" style="display:none;">No more records to show.</p>
                </div>
            </div>
        </div>
        
        <div style="height: 50px;"></div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        $(document).ready(function(){
            let offset = 0;
            const limit = 3; // Must match the limit in PHP
            let isLoading = false;

            // Function to fetch data
            function loadHistory() {
                if (isLoading) return;
                isLoading = true;
                $('#loadingSpinner').show();
                $('#loadMoreBtn').hide();

                $.ajax({
                    url: 'fetch_history.php',
                    type: 'POST',
                    data: { offset: offset },
                    success: function(response) {
                        $('#loadingSpinner').hide();
                        
                        // Check if response is empty (End of records)
                        if ($.trim(response) === "") {
                            if(offset > 0) {
                                $('#endMessage').show(); // End of list
                            } else {
                                $('#historyContainer').html("<p class='text-center text-muted py-4'>No transactions found.</p>");
                            }
                        } else {
                            // Append new cards
                            $('#historyContainer').append(response);
                            offset += limit;
                            isLoading = false;
                            
                            // Determine if we should show the Load More button again
                            // Ideally, we check if the returned count < limit, but simple way is just show it
                            // and if the next click returns empty, we hide it then.
                            $('#loadMoreBtn').show();
                        }
                    },
                    error: function() {
                        $('#loadingSpinner').hide();
                        alert("Failed to load history. Please try again.");
                    }
                });
            }

            // Load initial batch
            loadHistory();

            // Button Click
            $('#loadMoreBtn').click(function(){
                loadHistory();
            });
        });
    </script>
</body>
</html>