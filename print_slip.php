<?php 
session_start();
include "db_conn.php";

// Security Check
if (!isset($_SESSION['user_id']) || 
   ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'student_assistant' && $_SESSION['role'] != 'student')) {
    header("Location: index.php");
    exit();
}

if (!isset($_GET['control_no'])) {
    die("Error: No Control Number provided.");
}

$control_no = mysqli_real_escape_string($conn, $_GET['control_no']);

// 1. Get Transaction Details
// We also fetch 'feedback' here (tr.feedback)
$sql_header = "SELECT tr.*, u.full_name, u.id_number, u.course_section 
               FROM transactions tr
               JOIN users u ON tr.user_id = u.user_id
               WHERE tr.control_no = '$control_no' 
               LIMIT 1";

$res_header = mysqli_query($conn, $sql_header);

if (mysqli_num_rows($res_header) == 0) {
    die("Error: Record not found.");
}

$head = mysqli_fetch_assoc($res_header);

// Format Data
$date_borrowed = date('m/d/y', strtotime($head['date_requested'])); 
$time_borrowed = date('h:i A', strtotime($head['date_requested']));
$student_name = strtoupper($head['full_name']); 
$subject = strtoupper($head['subject']);
$room = $head['room_no'];

// Feedback Check
$feedback_text = !empty($head['feedback']) ? strtoupper($head['feedback']) : "NO REMARKS";

// SAFETY CHECK: If database is NULL, show placeholder
if (!empty($head['course_section'])) {
    $course_sec = strtoupper($head['course_section']);
} else {
    $course_sec = "NOT SET"; 
}

$processor = strtoupper($head['processed_by']); 

// 2. Get All Tools
$sql_tools = "SELECT t.tool_name, t.barcode 
              FROM transactions tr
              JOIN tools t ON tr.tool_id = t.tool_id
              WHERE tr.control_no = '$control_no'";
$res_tools = mysqli_query($conn, $sql_tools);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Borrower Slip - <?php echo $control_no; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Times New Roman', serif; background: #555; }
        .paper {
            width: 800px;
            background: white;
            margin: 30px auto;
            padding: 40px;
            box-shadow: 0 0 10px rgba(0,0,0,0.5);
            min-height: 1000px; 
        }
        .header-text { text-align: center; line-height: 1.2; }
        .univ-name { font-weight: bold; font-size: 14pt; }
        .sub-name { font-size: 10pt; }
        
        .control-no-box {
            text-align: right;
            margin-top: 20px;
            font-size: 10pt;
            border-bottom: 1px solid black;
            padding-bottom: 5px;
            margin-bottom: 20px;
        }

        .slip-title {
            text-align: center;
            font-weight: bold;
            font-size: 14pt;
            margin-bottom: 20px;
            text-transform: uppercase;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 20px;
            font-size: 11pt;
        }
        .info-line { border-bottom: 1px solid black; display: inline-block; width: 60%; padding-left: 5px;}
        .label { font-weight: bold; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { border: 1px solid black; padding: 8px; text-align: center; }
        th { background: #f0f0f0; }

        .feedback-box {
            border: 1px solid black;
            padding: 10px;
            margin-bottom: 30px;
            font-size: 10pt;
            text-align: left;
        }

        .disclaimer { font-size: 9pt; font-style: italic; margin-bottom: 30px; }

        @media print {
            body { background: white; margin: 0; }
            .paper { box-shadow: none; width: 100%; margin: 0; padding: 20px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="text-center no-print mt-3">
        <button onclick="window.print()" class="btn btn-primary">🖨️ Print Slip</button>
        <button onclick="window.close()" class="btn btn-secondary">Close</button>
    </div>

    <div class="paper">
        <div class="clearfix">
            <div class="header-text">
                <div class="univ-name">POLYTECHNIC UNIVERSITY OF THE PHILIPPINES</div>
                <div class="sub-name">OFFICE OF THE VICE PRESIDENT FOR ACADEMIC AFFAIRS</div>
                <div class="sub-name fw-bold">INSTITUTE OF TECHNOLOGY</div>
                <div class="sub-name">LABORATORY OFFICE</div>
            </div>
        </div>

        <div class="control-no-box">
            ITECH-LAB Control No.: <b><?php echo $control_no; ?></b>
        </div>

        <div class="slip-title">BORROWER'S SLIP</div>

        <div class="info-grid">
            <div><span class="label">Name:</span> <span class="info-line" style="width:70%"><?php echo $student_name; ?></span></div>
            <div><span class="label">Date:</span> <span class="info-line"><?php echo $date_borrowed; ?></span></div>
            
            <div><span class="label">Time:</span> <span class="info-line"><?php echo $time_borrowed; ?></span></div>
            
            <div><span class="label">Course/Sec:</span> <span class="info-line"><?php echo $course_sec; ?></span></div>
            
            <div style="grid-column: span 2;">
                <span class="label">Subject:</span> <span class="info-line" style="width: 85%"><?php echo $subject; ?></span>
            </div>
            <div><span class="label">Room No:</span> <span class="info-line"><?php echo $room; ?></span></div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 60%">Description / Tool Name</th>
                    <th style="width: 40%">Barcode</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $count = 0;
                while($tool = mysqli_fetch_assoc($res_tools)) {
                    $count++;
                ?>
                <tr>
                    <td style="text-align: left; padding-left: 15px;"><?php echo strtoupper($tool['tool_name']); ?></td>
                    <td><?php echo $tool['barcode']; ?></td>
                </tr>
                <?php } ?>
                
                <?php for($i=0; $i < (5-$count); $i++) { ?>
                <tr>
                    <td>&nbsp;</td>
                    <td></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>

        <div style="font-weight: bold; margin-bottom: 5px;">REMARKS / FEEDBACK:</div>
        <div class="feedback-box">
            <?php echo $feedback_text; ?>
        </div>

        <div class="disclaimer">
            I shall take full responsibility/accountability for any tools and equipment I borrowed. Likewise, I undertake that I will be liable for any loss or damage of the items above.
        </div>

        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-top: 50px;">
            
            <div style="text-align: center; width: 40%;">
                <br><br> 
                <div style="padding-top: 5px; font-weight: bold;"><?php echo $student_name; ?></div>
                <div style="font-size: 9pt;">Signature over printed name of borrower</div>
            </div>

            <div style="text-align: center; width: 40%;">
                <div style="text-align: left; margin-bottom: 30px; font-size: 9pt;">Released By:</div>
                <div style="padding-top: 5px; font-weight: bold;"><?php echo $processor; ?></div>
                <div style="font-size: 9pt;">Laboratory Personnel / Student Assistant</div>
            </div>

        </div>

    </div>

</body>
</html>