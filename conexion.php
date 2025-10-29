<?php
require_once 'database.php';

$db = new Database();

$conn = $db->getConnection();

if ($conn->connect_error) {
    die("❌ Error de conexión: " . $conn->connect_error);
}
?>