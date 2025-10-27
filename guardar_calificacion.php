<?php
session_start();
include 'conexion.php';

// Verificar sesión activa
if (!isset($_SESSION['usuario'])) {
    http_response_code(401);
    echo "No autorizado";
    exit();
}

$usuario = $_SESSION['usuario'];
$idUsuario = $usuario['id_usuario'] ?? $usuario['id'] ?? null;

if (!$idUsuario) {
    http_response_code(400);
    echo "ID de usuario no válido.";
    exit();
}

// Recibir datos del POST
$id_producto = $_POST['id_producto'] ?? null;
$puntuacion = $_POST['puntuacion'] ?? null;
$comentario = $_POST['comentario'] ?? '';

if (!$id_producto || !$puntuacion) {
    http_response_code(400);
    echo "Datos incompletos.";
    exit();
}

// Insertar en la tabla calificaciones
$stmt = $conn->prepare("
    INSERT INTO calificaciones (id_usuario, id_producto, puntuacion, comentario, fecha_registro)
    VALUES (?, ?, ?, ?, NOW())
");
$stmt->bind_param("iiis", $idUsuario, $id_producto, $puntuacion, $comentario);

if ($stmt->execute()) {
    echo "ok";
} else {
    http_response_code(500);
    echo "Error al guardar la opinión.";
}
?>