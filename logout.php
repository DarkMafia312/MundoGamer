<?php
session_start();

// Eliminar todas las variables de sesión
$_SESSION = [];

// Invalidar la cookie de sesión (muy importante)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destruir la sesión
session_destroy();

// Evitar caché del navegador para que no vuelva a mostrar páginas protegidas
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");

// Redirigir
header("Location: admin-login.php");
exit();
