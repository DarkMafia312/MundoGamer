<?php
include 'conexion.php';
session_start();
$id = intval($_POST['id_carrito'] ?? 0);
$cantidad = intval($_POST['cantidad'] ?? 1);
$stmt = $conn->prepare("UPDATE carrito SET cantidad = ? WHERE id_carrito = ?");
$stmt->bind_param("ii", $cantidad, $id);
echo $stmt->execute() ? "OK" : "Error";