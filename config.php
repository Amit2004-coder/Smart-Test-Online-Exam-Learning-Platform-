<?php
$host = "localhost";
$username = "root";
$password = "";
$dbname = "Smart_Test";

// Connection banana
$conn = mysqli_connect($host, $username, $password, $dbname);

// Check karna
if (!$conn) {
    die("Connection Failed: " . mysqli_connect_error());
}
?>