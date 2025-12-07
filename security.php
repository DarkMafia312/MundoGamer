<?php
// ---------------------------
// CONFIGURACIÓN
// ---------------------------
define('SEC_DEBUG', false); // Cambia a true en desarrollo local para ver errores completos (NO en producción)
define('SEC_LOG_DIR', __DIR__ . '/logs');

define('SEC_CSP', "
    default-src 'self';
    script-src 'self' 'unsafe-inline';
    style-src 'self' 'unsafe-inline';
    img-src 'self' data:;
    object-src 'none';
    frame-ancestors 'none';
");
define('SEC_FORCE_HTTPS', false);
define('SEC_HSTS_MAX_AGE', 31536000);
define('SEC_SESSION_NAME', 'mundogamer_sess');
// ---------------------------
// LOGS
// ---------------------------
if (!is_dir(SEC_LOG_DIR)) {
    @mkdir(SEC_LOG_DIR, 0750, true);
}
$logfile = SEC_LOG_DIR . '/app_errors.log';

if (!file_exists($logfile)) {
    @touch($logfile);
    @chmod($logfile, 0640);
}
// Intentar asegurar que el logfile sea escribible
if (!is_writable(SEC_LOG_DIR) || (file_exists($logfile) && !is_writable($logfile))) {
    // Si no es escribible, escribir en el log de PHP como fallback
    error_log("security.php: El directorio o archivo de logs no es escribible: " . SEC_LOG_DIR);
}
ini_set('display_errors', SEC_DEBUG ? 1 : 0);
ini_set('log_errors', 1);
ini_set('error_log', $logfile);
// ======================================================
// BOOTSTRAP PRINCIPAL
// ======================================================
function secure_bootstrap() {
    if (defined('SEC_BOOTSTRAPPED')) return;
    if (!headers_sent()) {
        secure_set_security_headers();
    }
    secure_session_start();

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = secure_random_token(32);
    }
    secure_session_expiration_control();
    define('SEC_BOOTSTRAPPED', true);
}
// ======================================================
// HEADERS DE SEGURIDAD
// ======================================================
function secure_set_security_headers() {
    if (headers_sent()) return;

    header("X-Content-Type-Options: nosniff");
    header("X-Frame-Options: DENY");
    header("X-XSS-Protection: 1; mode=block");
    header("Referrer-Policy: no-referrer-when-downgrade");
    header("Permissions-Policy: interest-cohort=()");
    header("Content-Security-Policy: " . SEC_CSP);
    // Si forzamos HTTPS y estamos en HTTPS o en modo forzar, establecemos HSTS
    if (SEC_FORCE_HTTPS && (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')) {
        header("Strict-Transport-Security: max-age=" . SEC_HSTS_MAX_AGE . "; includeSubDomains; preload");
    }
}
// ======================================================
// SESIONES SEGURAS
// ======================================================
function secure_session_start() {
    if (session_status() === PHP_SESSION_ACTIVE) return;
    $secure_cookie = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || SEC_FORCE_HTTPS;
    session_name(SEC_SESSION_NAME);
    session_start([
        'cookie_lifetime' => 0,
        'cookie_secure'   => $secure_cookie,
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'use_strict_mode' => 1,
        'use_only_cookies' => 1
    ]);
}

// 🔁 Regeneración periódica del ID de sesión
function secure_session_expiration_control() {
    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
    } elseif (time() - $_SESSION['created'] > 1800) { // 30 min
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
}

// ======================================================
// TOKEN SEGURO
// ======================================================
function secure_random_token($length = 32) {
    try {
        return bin2hex(random_bytes($length));
    } catch (Exception $e) {
        error_log("secure_random_token falló: ".$e->getMessage());
        return bin2hex(openssl_random_pseudo_bytes($length) ?: uniqid('', true));
    }
}
// ======================================================
// LIMPIEZA Y VALIDACIÓN
// ======================================================
function clean_input($data) {
    if (is_array($data)) {
        return array_map('clean_input', $data);
    }
    return htmlspecialchars(trim((string)$data), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Validaciones adicionales:
function validate_int($value) {
    return filter_var($value, FILTER_VALIDATE_INT);
}
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}
function sanitize_array($array) {
    $clean = [];
    foreach ($array as $k => $v) {
        $clean[$k] = clean_input($v);
    }
    return $clean;
}

// ======================================================
// CSRF
// ======================================================
function csrf_token() {
    return $_SESSION['csrf_token'];
}
function csrf_input_field() {
    return '<input type="hidden" name="csrf_token" value="' . 
        htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES) . '">';
}
function csrf_verify_or_die() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

    $sent = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;

    if (!$sent || !hash_equals($_SESSION['csrf_token'], $sent)) {
        error_log("CSRF FAILED - IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
        http_response_code(403);
        die("Solicitud inválida (CSRF).");
    }
}

// ======================================================
// ANTI FUERZA BRUTA (LOGIN)
// ======================================================
function check_attempts() {
    if (!isset($_SESSION['attempts'])) {
        $_SESSION['attempts'] = 0;
    }
    if ($_SESSION['attempts'] >= 5) {
        die("Has excedido el número de intentos. Vuelve más tarde.");
    }
}
function add_attempt() {
    if (!isset($_SESSION['attempts'])) {
        $_SESSION['attempts'] = 0;
    }
    $_SESSION['attempts']++;
}
// ======================================================
// AUTH
// ======================================================
function require_admin() {
    if (!isset($_SESSION['usuario_admin'])) {
      header("Location: admin-login.php");
      exit();
    }
}
function verificarUsuario() {
    if (!isset($_SESSION['usuario'])) {
        header("Location: usuario-login.php");
        exit();
    }
}
function logout_and_destroy() {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 3600,
            $p['path'], $p['domain'], $p['secure'], $p['httponly']
        );
    }
    session_destroy();
}
// ======================================================
// MANEJO GLOBAL DE EXCEPCIONES
// ======================================================
set_exception_handler(function($e) use ($logfile) {
    $msg = "Uncaught: {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}";
    // Log completo (stack trace)
    error_log($msg . "\nStack trace:\n" . $e->getTraceAsString());

    // Adicional: escribir explícitamente al logfile si es posible
    if (is_writable($logfile)) {
        @file_put_contents($logfile, "[".date('Y-m-d H:i:s')."] EXCEPTION: ".$msg.PHP_EOL.$e->getTraceAsString().PHP_EOL, FILE_APPEND | LOCK_EX);
    }
    http_response_code(500);
    if (defined('SEC_DEBUG') && SEC_DEBUG) {
        // Modo desarrollo: mostrar detalles
        echo "<h2>Excepción no capturada</h2>";
        echo "<pre>" . htmlspecialchars($msg) . "\n\n" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    } else {
        // Producción: mensaje seguro
        echo "Ha ocurrido un error. Intente más tarde.";
    }
    exit;
});
// Inicialización
secure_bootstrap();

?>