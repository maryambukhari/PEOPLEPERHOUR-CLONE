<?php
$host = "localhost";
$user = "uasxxqbztmxwm";
$pass = "wss863wqyhal";
$dbname = "dbp0jgpy2lbtso";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}
?>
