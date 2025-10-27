<?php
include 'conexion.php';
session_start();
$id = intval($_POST['id_carrito'] ?? 0);
$stmt = $conn->prepare("DELETE FROM carrito WHERE id_carrito = ?");
$stmt->bind_param("i", $id);
echo $stmt->execute() ? "OK" : "Error";