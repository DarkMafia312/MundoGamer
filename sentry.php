<?php
require_once __DIR__ . '/vendor/autoload.php';

\Sentry\init([
    'dsn' => 'https://1a4066fc78ea75c2fdde3d53a6ef60a6@o4510438250971136.ingest.us.sentry.io/4510438269452288',
    'traces_sample_rate' => 1.0,
    'profiles_sample_rate' => 1.0,
]);

// ========== DEBUG TEMPORAL ==========
\Sentry\configureScope(function (\Sentry\State\Scope $scope) {
    $scope->setTag('archivo_en_ejecucion', isset($_SERVER['SCRIPT_FILENAME']) ? $_SERVER['SCRIPT_FILENAME'] : 'no-detectado');
});

// ========== GLOBAL ERROR HANDLERS ==========

// 1. MANEJADOR DE ERRORES FATALES (Excepciones No Capturadas)
// El código DEBE detenerse aquí.
set_exception_handler(function (Throwable $e) {
    \Sentry\captureException($e);
    
    // Solo mostrar el mensaje de error si no estamos en un entorno de desarrollo
    if (getenv('APP_ENV') !== 'development') { 
        http_response_code(500);
        echo "<h1>Ha ocurrido un error interno. Ya estamos trabajando en ello.</h1>";
    }
    
    exit(1); // Detiene la ejecución.
});

// 2. MANEJADOR DE ERRORES (Advertencias, Avisos, etc.)
set_error_handler(function($severity, $message, $file, $line) {
    // Si el error está suprimido por @ o no está en el nivel de reporte actual, ignorar.
    if (!(error_reporting() & $severity)) {
        return false;
    }

    // Convertir Errores Recuperables/Fatales en Excepción (para que set_exception_handler lo capture y DETENGA el script)
    if ($severity & (E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_RECOVERABLE_ERROR | E_USER_ERROR)) {
        throw new ErrorException($message, 0, $severity, $file, $line);
    }
    
    // Para errores menores (Warning, Notice, Deprecated), SÓLO enviamos a Sentry y CONTINUAMOS la ejecución.
    \Sentry\captureMessage($message, \Sentry\Severity::warning());
    
    // Devolver true indica que el error fue manejado y PHP NO DEBE ejecutar su manejador de errores estándar.
    return true; 
});