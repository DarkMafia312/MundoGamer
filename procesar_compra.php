<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['usuario'])) {
    exit("Sesión expirada");
}

$usuario = $_SESSION['usuario'];
$idUsuario = $usuario['id'] ?? 0;
$metodo = $_POST['metodo'] ?? 'Desconocido';

// 🔹 Obtener productos del carrito
$sql = "SELECT * FROM carrito WHERE id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idUsuario);
$stmt->execute();
$carrito = $stmt->get_result();

if ($carrito->num_rows == 0) {
    exit("Carrito vacío");
}

// 🔹 Calcular totales
$total = 0;
$cantidadTotal = 0;
while ($item = $carrito->fetch_assoc()) {
    $total += $item['precio_unitario'] * $item['cantidad'];
    $cantidadTotal += $item['cantidad'];
}

// 🔹 Verificar si el usuario es VIP
$sqlVip = "SELECT * FROM usuarios_vip WHERE id_usuario = ? AND estado = 'Activa'";
$stmtVip = $conn->prepare($sqlVip);
$stmtVip->bind_param("i", $idUsuario);
$stmtVip->execute();
$resVip = $stmtVip->get_result();
$esVip = $resVip->num_rows > 0;

// 🔹 Aplicar descuentos automáticos
if ($esVip) {
    $descuento = $total * 0.15; // VIP 15%
} elseif ($cantidadTotal >= 10) {
    $descuento = $total * 0.10; // 10% si compra 10 o más
} elseif ($cantidadTotal >= 5) {
    $descuento = $total * 0.05; // 5% si compra 5 o más
} else {
    $descuento = 0;
}

$totalFinal = $total - $descuento;

// 🔹 Registrar venta
$stmtVenta = $conn->prepare("
    INSERT INTO ventas (id_usuario, total, descuento, total_final, metodo_pago, fecha_venta, estado)
    VALUES (?, ?, ?, ?, ?, NOW(), 'completada')
");
$stmtVenta->bind_param("iddss", $idUsuario, $total, $descuento, $totalFinal, $metodo);
$stmtVenta->execute();
$idVenta = $stmtVenta->insert_id;

// 🔹 Insertar detalle_venta
$stmtCarrito = $conn->prepare("SELECT * FROM carrito WHERE id_usuario = ?");
$stmtCarrito->bind_param("i", $idUsuario);
$stmtCarrito->execute();
$productos = $stmtCarrito->get_result();

while ($row = $productos->fetch_assoc()) {
    $subtotal = $row['precio_unitario'] * $row['cantidad'];
    $sqlDetalle = "
        INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario, subtotal)
        VALUES (?, ?, ?, ?, ?)
    ";
    $stmtDet = $conn->prepare($sqlDetalle);
    $stmtDet->bind_param("iiidd", $idVenta, $row['id_producto'], $row['cantidad'], $row['precio_unitario'], $subtotal);
    $stmtDet->execute();
}

// 🔹 Registrar pago
$stmtPago = $conn->prepare("
    INSERT INTO pagos (id_venta, metodo, codigo_transaccion, monto_pagado, fecha_pago)
    VALUES (?, ?, ?, ?, NOW())
");
$codigo = uniqid("TRX");
$stmtPago->bind_param("issd", $idVenta, $metodo, $codigo, $totalFinal);
$stmtPago->execute();

// 🔹 Vaciar carrito
$conn->query("DELETE FROM carrito WHERE id_usuario = $idUsuario");

// 🔹 Respuesta final JSON (NO se modifica, sigue igual)
echo json_encode([
    "status" => "OK",
    "mensaje" => "Compra realizada correctamente",
    "id_venta" => $idVenta,
    "codigo" => $codigo,
    "total_final" => number_format($totalFinal, 2),
    "fecha" => date("Y-m-d H:i:s"),
    // 🔹 Se agrega la URL del voucher (opcional para frontend)
    "voucher_url" => "voucher.php?id_venta=" . $idVenta
]);
?>