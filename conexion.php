<?php
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
} catch (Exception $e) {
    error_log("Error en conexion.php: " . $e->getMessage());
    http_response_code(500);
    echo "Error al conectar con la base de datos.";
    exit;
}

return $conn;
?>
