<?php
session_start();

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// --- BULK ADD LOGIC ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'add_bulk') {
    
    $name = $_POST['tool_name'];
    $cat = $_POST['category'];
    $qty = (int)$_POST['qty'];

    // Update Session: [ 'Screwdriver' => ['qty'=>2, 'cat'=>'Hand Tool'] ]
    if (isset($_SESSION['cart'][$name])) {
        $_SESSION['cart'][$name]['qty'] += $qty;
    } else {
        $_SESSION['cart'][$name] = [
            'category' => $cat,
            'qty' => $qty
        ];
    }
    
    header("Location: student_catalog.php?msg=Added $qty $name(s) to cart");
    exit();
}

// --- REMOVE ITEM ---
if (isset($_GET['action']) && $_GET['action'] == 'remove' && isset($_GET['name'])) {
    $name = urldecode($_GET['name']); // Use Name as ID
    unset($_SESSION['cart'][$name]);
    header("Location: cart.php?msg=Item removed");
    exit();
}

header("Location: student_catalog.php");
?>