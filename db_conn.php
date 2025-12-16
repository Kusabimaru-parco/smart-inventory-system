<?php
// 1. SET TIMEZONE TO PHILIPPINES
date_default_timezone_set('Asia/Manila');

$sname = "localhost";
$uname = "root";
$password = "";
$db_name = "smart_inventory_db";

$conn = mysqli_connect($sname, $uname, $password, $db_name);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>