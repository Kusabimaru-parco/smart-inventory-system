<?php 
session_start();
include "db_conn.php";

// Security Check
if (!isset($_SESSION['user_id']) || 
   ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'student_assistant')) {
    header("Location: index.php");
    exit();
}

// --- DATA FETCHING ---
// 1. Get Active Students
$students_sql = "SELECT user_id, full_name, id_number, course_section FROM users WHERE role='student' AND account_status='active' ORDER BY full_name ASC";
$students_res = mysqli_query($conn, $students_sql);

// 2. Get Available Tools
$tools_sql = "SELECT tool_id, tool_name, barcode, category FROM tools WHERE status='Available' ORDER BY tool_name ASC";
$tools_res = mysqli_query($conn, $tools_sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Requests</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        /* Custom Scrollbar for lists */
        .scrollable-list {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            background: white;
        }
        
        /* Tool Item Styling */
        .tool-item {
            cursor: pointer;
            transition: all 0.2s;
            border-left: 4px solid transparent;
        }
        .tool-item:hover {
            background-color: #f1f3f5;
            border-left: 4px solid #0d6efd;
        }
        
        /* Disabled state when added to cart */
        .tool-added {
            background-color: #e9ecef !important;
            color: #6c757d;
            pointer-events: none;
            border-left: 4px solid #198754;
        }

        /* Student Item Styling */
        .student-item {
            cursor: pointer;
        }
        .student-item:hover {
            background-color: #e9ecef;
        }
    </style>
</head>
<body class="bg-light">

    <nav class="navbar navbar-dark bg-dark px-4">
        <span class="navbar-brand mb-0 h1">Request Approval</span>
        <div>
            <button class="btn btn-warning btn-sm me-2" onclick="openMainModal()">
                <i class="bi bi-pencil-square"></i> Walk-in / Manual Borrow
            </button>
            <a href="dashboard.php" class="btn btn-outline-light btn-sm me-2">Dashboard</a>
            <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
        </div>
    </nav>

    <div class="container mt-5">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3>📩 Pending Borrow Requests</h3>
            <div>
                <a href="request_action.php?action=approve_all" class="btn btn-success" onclick="return confirm('Approve ALL?');"><i class="bi bi-check-all"></i> Approve All</a>
                <a href="request_action.php?action=decline_all" class="btn btn-outline-danger ms-2" onclick="return confirm('Decline ALL?');"><i class="bi bi-x-circle"></i> Decline All</a>
            </div>
        </div>

        <?php if (isset($_GET['msg'])) { ?>
            <div class="alert alert-success text-center mb-4"><i class="bi bi-check-circle-fill"></i> <?php echo htmlspecialchars($_GET['msg']); ?></div>
        <?php } ?>
        <?php if (isset($_GET['error'])) { ?>
            <div class="alert alert-danger text-center mb-4"><i class="bi bi-exclamation-circle-fill"></i> <?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php } ?>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Control No.</th> <th>Student Name</th>
                                <th>Tool Requested</th>
                                <th>Subject / Room</th> <th>Dates</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $sql = "SELECT t.transaction_id, t.borrow_date, t.return_date, t.control_no, t.subject, t.room_no, u.full_name, tl.tool_name 
                                    FROM transactions t
                                    JOIN users u ON t.user_id = u.user_id
                                    JOIN tools tl ON t.tool_id = tl.tool_id
                                    WHERE t.status = 'Pending' ORDER BY t.transaction_id DESC";
                            $result = mysqli_query($conn, $sql);
                            if (mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                            ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-primary"><?php echo $row['control_no']; ?></td>
                                    <td class="fw-bold"><?php echo $row['full_name']; ?></td>
                                    <td><?php echo $row['tool_name']; ?></td>
                                    <td>
                                        <div class="fw-bold text-dark"><?php echo $row['subject']; ?></div>
                                        <small class="text-muted"><i class="bi bi-geo-alt"></i> <?php echo $row['room_no']; ?></small>
                                    </td>
                                    <td>
                                        <small class="text-muted d-block">Borrow: <?php echo date('M d', strtotime($row['borrow_date'])); ?></small>
                                        <small class="text-danger fw-bold">Return: <?php echo date('M d', strtotime($row['return_date'])); ?></small>
                                    </td>
                                    <td>
                                        <a href="request_action.php?id=<?php echo $row['transaction_id']; ?>&action=approve" class="btn btn-success btn-sm me-1"><i class="bi bi-check-lg"></i></a>
                                        <a href="request_action.php?id=<?php echo $row['transaction_id']; ?>&action=decline" class="btn btn-outline-danger btn-sm" onclick="return confirm('Decline?');"><i class="bi bi-x-lg"></i></a>
                                    </td>
                                </tr>
                            <?php 
                                }
                            } else { echo "<tr><td colspan='6' class='text-center py-5 text-muted'>No pending requests found.</td></tr>"; }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="manualBorrowModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-xl"> 
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-basket-fill"></i> Manual / Walk-in Borrowing</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                
                <form action="manual_borrow_save.php" method="POST" id="borrowForm">
                    <div class="modal-body bg-light">
                        
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Student</label>
                                <div class="input-group">
                                    <input type="text" id="displayStudentName" class="form-control bg-white" placeholder="No student selected" readonly required onclick="openStudentModal()">
                                    <input type="hidden" name="student_id" id="hiddenStudentId">
                                    <button class="btn btn-primary" type="button" onclick="openStudentModal()"><i class="bi bi-search"></i> Select</button>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Subject / Class</label>
                                <input type="text" name="subject" class="form-control" placeholder="Ex. IT 101" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Room No.</label>
                                <input type="text" name="room_no" class="form-control" placeholder="Ex. Lab 1" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Return Date</label>
                                <input type="date" name="return_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="card-header bg-white">
                                        <h6 class="mb-0 fw-bold"><i class="bi bi-tools"></i> Available Tools</h6>
                                    </div>
                                    <div class="p-2 bg-light border-bottom">
                                        <input type="text" id="toolSearch" class="form-control" placeholder="🔍 Search Tool Name, Barcode, or Category..." onkeyup="filterTools()">
                                    </div>
                                    <div class="list-group scrollable-list list-group-flush" id="toolInventoryList">
                                        <?php while($tool = mysqli_fetch_assoc($tools_res)) { ?>
                                            <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center tool-item" 
                                                 id="tool-opt-<?php echo $tool['tool_id']; ?>"
                                                 data-search="<?php echo strtolower($tool['tool_name'] . ' ' . $tool['barcode'] . ' ' . $tool['category']); ?>"
                                                 onclick="addItemToCart('<?php echo $tool['tool_id']; ?>', '<?php echo htmlspecialchars($tool['tool_name']); ?>', '<?php echo $tool['barcode']; ?>')">
                                                
                                                <div>
                                                    <div class="fw-bold"><?php echo $tool['tool_name']; ?></div>
                                                    <small class="text-muted">
                                                        <i class="bi bi-upc-scan"></i> <?php echo $tool['barcode']; ?> 
                                                        <span class="badge bg-secondary ms-1"><?php echo $tool['category']; ?></span>
                                                    </small>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-outline-success btn-add-icon">
                                                    <i class="bi bi-plus-lg"></i>
                                                </button>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card h-100 border-success">
                                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0 fw-bold"><i class="bi bi-cart-check"></i> Tools to Borrow</h6>
                                        <span class="badge bg-white text-success" id="cartCount">0 Items</span>
                                    </div>
                                    <div class="list-group scrollable-list list-group-flush" id="cartList" style="min-height: 250px;">
                                        <div id="emptyCart" class="text-center p-5 text-muted">
                                            <i class="bi bi-basket display-1 opacity-25"></i>
                                            <p class="mt-3">Select tools from the left list.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary px-4 fw-bold" onclick="return validateForm()">
                            <i class="bi bi-check-circle-fill"></i> Submit & Approve Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="studentSearchModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="bi bi-person-lines-fill"></i> Search Student</h5>
                    <button type="button" class="btn-close" onclick="closeStudentModal()"></button>
                </div>
                <div class="modal-body">
                    <input type="text" id="studentSearchInput" class="form-control form-control-lg mb-3" placeholder="Type Name or ID Number to search..." onkeyup="filterStudents()">
                    
                    <div class="list-group scrollable-list" id="studentListContainer">
                        <?php 
                        // Reset pointer to reuse student result set if needed, or query again logic handled above
                        mysqli_data_seek($students_res, 0); 
                        while($stu = mysqli_fetch_assoc($students_res)) { 
                            $display = $stu['full_name'] . " (" . $stu['id_number'] . ")";
                            if(!empty($stu['course_section'])) { $display .= " - " . $stu['course_section']; }
                        ?>
                            <button type="button" class="list-group-item list-group-item-action student-item" 
                                    data-search="<?php echo strtolower($display); ?>"
                                    onclick="selectStudent('<?php echo $stu['user_id']; ?>', '<?php echo htmlspecialchars($display, ENT_QUOTES); ?>')">
                                <div class="fw-bold"><?php echo $stu['full_name']; ?></div>
                                <div class="small text-muted">ID: <?php echo $stu['id_number']; ?> | <?php echo $stu['course_section']; ?></div>
                            </button>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // --- MODAL HANDLING ---
        var mainModal, studentModal;

        document.addEventListener("DOMContentLoaded", function() {
            mainModal = new bootstrap.Modal(document.getElementById('manualBorrowModal'));
            studentModal = new bootstrap.Modal(document.getElementById('studentSearchModal'));
        });

        function openMainModal() {
            mainModal.show();
        }

        function openStudentModal() {
            document.getElementById('manualBorrowModal').style.opacity = 0; // Hide main modal visually
            studentModal.show();
        }

        function closeStudentModal() {
            studentModal.hide();
            document.getElementById('manualBorrowModal').style.opacity = 1; // Show main modal
        }

        // --- 1. STUDENT SELECTION LOGIC ---
        function filterStudents() {
            var input = document.getElementById("studentSearchInput").value.toLowerCase();
            var items = document.getElementsByClassName("student-item");
            
            for (var i = 0; i < items.length; i++) {
                var searchVal = items[i].getAttribute("data-search");
                if (searchVal.indexOf(input) > -1) {
                    items[i].style.display = "";
                } else {
                    items[i].style.display = "none";
                }
            }
        }

        function selectStudent(id, name) {
            document.getElementById("hiddenStudentId").value = id;
            document.getElementById("displayStudentName").value = name;
            closeStudentModal();
        }

        // --- 2. TOOL SEARCH LOGIC ---
        function filterTools() {
            var input = document.getElementById("toolSearch").value.toLowerCase();
            var items = document.getElementsByClassName("tool-item");

            for (var i = 0; i < items.length; i++) {
                var searchVal = items[i].getAttribute("data-search");
                if (searchVal.indexOf(input) > -1) {
                    items[i].style.display = ""; // Show
                    items[i].classList.add("d-flex"); // Maintain flex layout
                } else {
                    items[i].style.display = "none"; // Hide
                    items[i].classList.remove("d-flex");
                }
            }
        }

        // --- 3. CART SYSTEM ---
        function addItemToCart(id, name, barcode) {
            var cartList = document.getElementById("cartList");
            var emptyMsg = document.getElementById("emptyCart");
            var sourceBtn = document.getElementById("tool-opt-" + id);

            // Hide empty message
            emptyMsg.style.display = "none";

            // Visual disable in inventory
            sourceBtn.classList.add("tool-added");
            sourceBtn.querySelector(".btn-add-icon").innerHTML = '<i class="bi bi-check-lg"></i> Added';
            sourceBtn.querySelector(".btn-add-icon").className = "btn btn-sm btn-success";

            // Create Cart Item
            var item = document.createElement("div");
            item.className = "list-group-item d-flex justify-content-between align-items-center bg-white border-bottom";
            item.id = "cart-item-" + id;
            item.innerHTML = `
                <div>
                    <div class="fw-bold text-success">${name}</div>
                    <small class="text-muted">${barcode}</small>
                    <input type="hidden" name="tool_ids[]" value="${id}">
                </div>
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeFromCart('${id}')">
                    <i class="bi bi-trash"></i>
                </button>
            `;

            cartList.appendChild(item);
            updateCartCount();
        }

        function removeFromCart(id) {
            // Remove from cart
            document.getElementById("cart-item-" + id).remove();

            // Re-enable in inventory
            var sourceBtn = document.getElementById("tool-opt-" + id);
            sourceBtn.classList.remove("tool-added");
            sourceBtn.querySelector(".btn").innerHTML = '<i class="bi bi-plus-lg"></i>';
            sourceBtn.querySelector(".btn").className = "btn btn-sm btn-outline-success btn-add-icon";

            updateCartCount();

            // Show empty msg if needed
            var cartList = document.getElementById("cartList");
            if (cartList.children.length <= 1) { // 1 is emptyMsg
                document.getElementById("emptyCart").style.display = "block";
            }
        }

        function updateCartCount() {
            var count = document.getElementsByName("tool_ids[]").length;
            document.getElementById("cartCount").innerText = count + " Items";
        }

        // --- 4. VALIDATION ---
        function validateForm() {
            var studentId = document.getElementById("hiddenStudentId").value;
            var cartItems = document.getElementsByName("tool_ids[]");

            if (!studentId) {
                alert("Please select a student first!");
                return false;
            }
            if (cartItems.length === 0) {
                alert("Please add at least one tool to the cart!");
                return false;
            }
            return true;
        }
    </script>
</body>
</html>