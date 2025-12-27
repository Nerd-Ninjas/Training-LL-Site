<?php
error_reporting(E_ERROR | E_PARSE);
ob_start(); // Turns on output buffering
session_start();

date_default_timezone_set("Asia/Kolkata");

$db_host="localhost"; //localhost server 
$db_user="root";	//database username
$db_password="";	//database password   
$db_name="learnlikes_gurukulam";	//database name

try {
    $con = new PDO("mysql:host={$db_host};dbname={$db_name}",$db_user,$db_password);
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
}
catch (PDOException $e) {
    exit("Connection failed: " . $e->getMessage());
}
?>