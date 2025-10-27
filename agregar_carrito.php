<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['usuario'])) {
    http_response_code(401);
    exit("No autorizado");
}

$usuario = $_SESSION['usuario'];
$idUsuario = $usuario['id_usuario'] ?? $usuario['id'] ?? $usuario['idUsuario'] ?? 0;
$idProducto = intval($_POST['id_producto'] ?? 0);

if ($idUsuario <= 0 || $idProducto <= 0) {
    http_response_code(400);
    exit("Datos inválidos");
}

// Obtener precio actual del producto
$stmt = $conn->prepare("SELECT precio FROM productos WHERE id_producto = ?");
$stmt->bind_param("i", $idProducto);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows == 0) {
    http_response_code(404);
    exit("Producto no encontrado");
}
$producto = $res->fetch_assoc();

// Verificar si ya está en el carrito
$check = $conn->prepare("SELECT id_carrito, cantidad FROM carrito WHERE id_usuario = ? AND id_producto = ?");
$check->bind_param("ii", $idUsuario, $idProducto);
$check->execute();
$resCheck = $check->get_result();

if ($resCheck->num_rows > 0) {
    // Si ya existe, solo incrementar cantidad
    $item = $resCheck->fetch_assoc();
    $nuevaCantidad = $item['cantidad'] + 1;
    $update = $conn->prepare("UPDATE carrito SET cantidad = ? WHERE id_carrito = ?");
    $update->bind_param("ii", $nuevaCantidad, $item['id_carrito']);
    $update->execute();
} else {
    // Si no existe, insertar nuevo registro
    $insert = $conn->prepare("
        INSERT INTO carrito (id_usuario, id_producto, cantidad, precio_unitario, fecha_agregado)
        VALUES (?, ?, 1, ?, NOW())
    ");
    $insert->bind_param("iid", $idUsuario, $idProducto, $producto['precio']);
    $insert->execute();
}

echo "OK";
?>