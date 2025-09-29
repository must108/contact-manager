<?php
$servername = "localhost";   // Because PHP + MySQL are on the same server
$username   = "root";        // Or better: the new MySQL user you create
$password   = "MalldoOr0524gm";  // Your MySQL password
$dbname     = "COP4331";     // Your database name

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
