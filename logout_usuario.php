<?php
// ===============================
// 🔒 LOGOUT DE USUARIO SEGURO
// ===============================

// Incluir configuración de seguridad global
require_once 'security.php';

// Asegurar que la sesión esté inicializada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Eliminar solo la sesión del usuario si existe
if (isset($_SESSION['usuario'])) {
    unset($_SESSION['usuario']);
}

// Limpiar todas las variables de sesión
session_unset();

// Destruir completamente la sesión
session_destroy();

// Eliminar cookie de sesión del navegador (si existe)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Regenerar ID por seguridad (evita reutilización de sesión)
session_start();
session_regenerate_id(true);

// Redirigir al login de usuario
header("Location: usuario-login.php");
exit;
?>
