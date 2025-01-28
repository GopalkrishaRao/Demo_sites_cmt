<?php
// Database connection variables

$host = "127.0.0.1";
$dbname = "res_aurabindo_group_cmt";
$username = "root";
$password = "";


// $host = "190.92.174.104";
// $username = "codemyth_staywell";
// $password = "codemyth_staywell";
// $dbname = "codemyth_staywell";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Connected successfully";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
