<?php
session_start();
include 'conexion.php';

// Evitar salida accidental
header('Content-Type: application/json; charset=utf-8');

// Obtener usuario
$usuario = $_SESSION['usuario'] ?? null;
$idUsuario = $usuario['id_usuario'] ?? $usuario['id'] ?? null;

// Verificar método POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["status" => "error", "mensaje" => "Método no permitido."]);
    exit;
}

// Recibir datos
$nombre    = trim($_POST['nombre'] ?? '');
$apellido  = trim($_POST['apellido'] ?? '');
$correo    = trim($_POST['correo'] ?? '');
$telefono  = trim($_POST['telefono'] ?? '');
$asunto    = trim($_POST['asunto'] ?? '');
$mensaje   = trim($_POST['mensaje'] ?? '');

// Validaciones básicas
if ($nombre === '' || $apellido === '' || $correo === '' || $asunto === '' || $mensaje === '') {
    echo json_encode(["status" => "error", "mensaje" => "Por favor completa todos los campos obligatorios."]);
    exit;
}

// Insertar en la tabla soporte_cliente
$stmt = $conn->prepare("
    INSERT INTO soporte_cliente 
    (id_usuario, nombre, apellido, correo, telefono, asunto, mensaje, fecha_envio, estado)
    VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 'Pendiente')
");

$stmt->bind_param("issssss", $idUsuario, $nombre, $apellido, $correo, $telefono, $asunto, $mensaje);

if ($stmt->execute()) {
    echo json_encode(["status" => "ok", "mensaje" => "Tu solicitud fue enviada correctamente."]);
} else {
    echo json_encode(["status" => "error", "mensaje" => "Error al registrar la solicitud: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>