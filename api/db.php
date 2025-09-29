<?php
$servername = "localhost";   
$username   = "root";        
$password   = "MalldoOr0524gm";  // MySQL password
$dbname     = "COP4331";     // database name

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
