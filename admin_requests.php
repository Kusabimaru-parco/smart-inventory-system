<?php 
session_start();
include "db_conn.php";

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'student_assistant')) {
    header("Location: index.php");
    exit();
}

// 1. Fetch Students (Fixed: using 'id_number' based on your database image)
$students_sql = "SELECT user_id, full_name, id_number FROM users WHERE role='student' ORDER BY full_name ASC";
$students_res = mysqli_query($conn, $students_sql);

// 2. Fetch Available Tools
$tools_sql = "SELECT tool_name, COUNT(*) as qty FROM tools 
              WHERE status='Available' 
              AND tool_id NOT IN (SELECT tool_id FROM transactions WHERE status IN ('Pending','Approved','Borrowed'))
              GROUP BY tool_name ORDER BY tool_name ASC";
$tools_res = mysqli_query($conn, $tools_sql);
$tool_options = [];
while($t = mysqli_fetch_assoc($tools_res)) {
    $tool_options[] = $t;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Requests</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <style>
        .searchable-list {
            border: 1px solid #ced4da;
            border-top: none;
            max-height: 150px;
            overflow-y: auto;
            border-bottom-left-radius: 5px;
            border-bottom-right-radius: 5px;
        }
        .searchable-list option {
            padding: 8px 12px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
        }
        .searchable-list option:hover {
            background-color: #f8f9fa;
        }
        .searchable-list option:checked {
            background-color: #198754;
            color: white;
        }
        .search-input {
            border-bottom-left-radius: 0;
            border-bottom-right-radius: 0;
        }
    </style>
</head>
<body class="bg-light">

    <nav class="navbar navbar-dark bg-dark px-4">
        <span class="navbar-brand mb-0 h1">Request Approval</span>
        <div class="d-flex gap-2">
            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#walkinModal">
                <i class="bi bi-person-walking"></i> Walk-In Request
            </button>
            <a href="dashboard.php" class="btn btn-outline-light btn-sm">Dashboard</a>
        </div>
    </nav>

    <div class="container mt-5">
        
        <?php if(isset($_GET['msg'])) { ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo htmlspecialchars($_GET['msg']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php } ?>
        
        <?php if(isset($_GET['error'])) { ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php echo htmlspecialchars($_GET['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php } ?>

        <h3 class="mb-4">📩 Pending Group Requests</h3>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Control No.</th>
                                <th>Student</th>
                                <th>Items Requested</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $sql = "SELECT t.control_no, t.date_requested, u.full_name, u.course_section,
                                           GROUP_CONCAT(tl.tool_name SEPARATOR ', ') as tool_list
                                    FROM transactions t
                                    JOIN users u ON t.user_id = u.user_id
                                    JOIN tools tl ON t.tool_id = tl.tool_id
                                    WHERE t.status = 'Pending' 
                                    GROUP BY t.control_no
                                    ORDER BY t.date_requested DESC";
                            $result = mysqli_query($conn, $sql);

                            if (mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    $tools = explode(', ', $row['tool_list']);
                                    $counts = array_count_values($tools);
                                    $display_tools = [];
                                    foreach($counts as $name => $count) {
                                        $display_tools[] = "<b>{$count}x</b> $name";
                                    }
                            ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-primary"><?php echo $row['control_no']; ?></td>
                                    <td>
                                        <div class="fw-bold"><?php echo $row['full_name']; ?></div>
                                        <small class="text-muted"><?php echo $row['course_section']; ?></small>
                                    </td>
                                    <td><?php echo implode(', ', $display_tools); ?></td>
                                    <td><?php echo date('M d, h:i A', strtotime($row['date_requested'])); ?></td>
                                    <td>
                                        <a href="request_action.php?control_no=<?php echo $row['control_no']; ?>&action=approve_group" class="btn btn-success btn-sm me-1">
                                            <i class="bi bi-check-lg"></i> Approve
                                        </a>
                                        <a href="request_action.php?control_no=<?php echo $row['control_no']; ?>&action=decline_group" class="btn btn-outline-danger btn-sm" onclick="return confirm('Decline this entire request?');">
                                            <i class="bi bi-x-lg"></i> Decline
                                        </a>
                                    </td>
                                </tr>
                            <?php 
                                }
                            } else {
                                echo "<tr><td colspan='5' class='text-center py-5 text-muted'>No pending requests found.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="walkinModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="walkin_process.php" method="POST">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title"><i class="bi bi-person-walking"></i> Process Walk-In Request</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Select Student</label>
                            <input type="text" id="studentSearch" class="form-control search-input" placeholder="Type to search student name or ID..." autocomplete="off">
                            <select name="user_id" id="studentSelect" class="form-select searchable-list" size="3" required>
                                <option value="" disabled selected>-- Select from list below --</option>
                                <?php while($stu = mysqli_fetch_assoc($students_res)) { ?>
                                    <option value="<?php echo $stu['user_id']; ?>">
                                        <?php echo $stu['full_name']; ?> (<?php echo $stu['id_number']; ?>)
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <hr>

                        <label class="form-label fw-bold">Add Tools to Request</label>
                        <div class="row g-2 align-items-start mb-3">
                            <div class="col-md-7">
                                <input type="text" id="toolSearch" class="form-control search-input" placeholder="Type to search tool..." autocomplete="off">
                                <select id="toolSelect" class="form-select searchable-list" size="3">
                                    <option value="" disabled selected>-- Select Tool --</option>
                                    <?php foreach($tool_options as $t) { ?>
                                        <option value="<?php echo $t['tool_name']; ?>" data-max="<?php echo $t['qty']; ?>">
                                            <?php echo $t['tool_name']; ?> (<?php echo $t['qty']; ?> Available)
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="number" id="toolQty" class="form-control" placeholder="Qty" value="1" min="1" style="height: 38px;">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-primary w-100" onclick="addToolToList()" style="height: 38px;">Add</button>
                            </div>
                        </div>

                        <div class="card bg-light mb-3">
                            <div class="card-body p-2">
                                <h6 class="card-subtitle mb-2 text-muted small">Items to Borrow:</h6>
                                <ul id="selectedToolsList" class="list-group list-group-flush small">
                                    <li class="list-group-item text-center text-muted fst-italic" id="emptyMsg">No items added yet.</li>
                                </ul>
                            </div>
                        </div>

                        <div id="hiddenToolInputs"></div>

                        <hr>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small text-muted text-uppercase fw-bold">Subject / Class</label>
                                <input type="text" name="subject" class="form-control" placeholder="Ex. Electronics 1" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted text-uppercase fw-bold">Room No.</label>
                                <input type="text" name="room_no" class="form-control" placeholder="Ex. Lab 304" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small text-muted text-uppercase fw-bold">Return Date</label>
                                <input type="date" name="return_date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success fw-bold">Confirm & Approve</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        $(document).ready(function(){
            // Student Search
            $("#studentSearch").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("#studentSelect option").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });

            // Tool Search
            $("#toolSearch").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("#toolSelect option").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });
        });

        function addToolToList() {
            var toolName = $('#toolSelect').val();
            var toolQty = parseInt($('#toolQty').val());
            var selectedOption = $('#toolSelect option:selected');
            var maxQty = parseInt(selectedOption.data('max'));

            if (!toolName) { alert("Please select a tool first."); return; }
            if (isNaN(toolQty) || toolQty < 1) { alert("Please enter a valid quantity."); return; }
            if (toolQty > maxQty) { alert("Insufficient stock! Max available: " + maxQty); return; }

            $('#emptyMsg').hide();

            var listItem = `<li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>${toolName}</span>
                                <div>
                                    <span class="badge bg-primary rounded-pill me-2">${toolQty}</span>
                                    <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" onclick="removeTool(this, '${toolName}')">x</button>
                                </div>
                            </li>`;
            $('#selectedToolsList').append(listItem);

            var hiddenInput = `<input type="hidden" name="tools[${toolName}]" value="${toolQty}" id="input-${toolName.replace(/\s+/g, '')}">`;
            $('#hiddenToolInputs').append(hiddenInput);

            $('#toolSearch').val('');
            $('#toolSelect').val('');
            $("#toolSearch").trigger('keyup'); 
            $('#toolQty').val(1);
        }

        function removeTool(btn, toolName) {
            $(btn).closest('li').remove();
            $(`#input-${toolName.replace(/\s+/g, '')}`).remove();
            if ($('#selectedToolsList li').length <= 1) {
                $('#emptyMsg').show();
            }
        }
    </script>
</body>
</html>