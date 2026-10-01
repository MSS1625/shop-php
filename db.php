<?php

$host = "localhost";
$username = "root"; 
$password = "";     
$dbname = "simple_shop";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("خطا در اتصال: " . $e->getMessage());
}
?>