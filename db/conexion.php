<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "impulsora";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    die("Conexión fallida: " . $e->getMessage());
}
?>