<?php
// ===============================
// 🔒 CONFIGURACIÓN DE SEGURIDAD
// ===============================
require_once __DIR__ . '/sentry.php';
session_start([
    'cookie_httponly' => true,
    'cookie_secure'   => isset($_SERVER['HTTPS']),
    'cookie_samesite' => 'Strict'
]);

include 'conexion.php';

// Sanitizador seguro
function limpiar($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// CSRF
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Inicialización
$mensaje = "";
$tipoMensaje = "success";

if (!isset($_SESSION['intentos'])) $_SESSION['intentos'] = 0;
if (!isset($_SESSION['bloqueado_hasta'])) $_SESSION['bloqueado_hasta'] = 0;
if (!isset($_SESSION['ciclos_bloqueo'])) $_SESSION['ciclos_bloqueo'] = 0;

$ahora = time();

// Bloqueo temporal
if ($ahora < $_SESSION['bloqueado_hasta']) {
    $restante = $_SESSION['bloqueado_hasta'] - $ahora;
    $tipoMensaje = "danger";

} elseif ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Verificar CSRF
    if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf'])) {
        die("⚠️ CSRF Token inválido.");
    }

    $username = strtolower(limpiar($_POST['username']));
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $mensaje = "Por favor, completa todos los campos.";
        $tipoMensaje = "danger";

    } else {
        // Buscar usuario
        $sql = "SELECT id, nombre, apellido, username, correo, password FROM usuarios WHERE LOWER(username) = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            $usuario = $resultado->fetch_assoc();

            if (password_verify($password, $usuario['password'])) {

                // Verificar estado en gestion_clientes
                $sql_estado = "SELECT estado, fecha_suspension_inicio, fecha_suspension_fin 
                               FROM gestion_clientes 
                               WHERE id_usuario = ? LIMIT 1";
                $stmt_estado = $conn->prepare($sql_estado);
                $stmt_estado->bind_param("i", $usuario['id']);
                $stmt_estado->execute();
                $res_estado = $stmt_estado->get_result();
                $estado_data = $res_estado->fetch_assoc();

                $estado = $estado_data['estado'] ?? 'Activo';
                $inicio_susp = $estado_data['fecha_suspension_inicio'] ?? null;
                $fin_susp = $estado_data['fecha_suspension_fin'] ?? null;

                // Revisar estado
                if ($estado === 'Baneado') {
                    $mensaje = "⛔ Tu cuenta ha sido baneada.";
                    $tipoMensaje = "danger";

                } elseif ($estado === 'Suspendido') {
                    $mensaje = "⚠️ Cuenta suspendida desde <b>" . date("d/m/Y", strtotime($inicio_susp)) .
                               "</b> hasta <b>" . date("d/m/Y", strtotime($fin_susp)) . "</b>.";
                    $tipoMensaje = "warning";

                } elseif ($estado === 'Inactivo') {
                    $mensaje = "❌ Tu cuenta está inactiva.";
                    $tipoMensaje = "danger";

                } else {
                    // Login correcto
                    $_SESSION['intentos'] = 0;
                    $_SESSION['bloqueado_hasta'] = 0;
                    $_SESSION['ciclos_bloqueo'] = 0;

                    $_SESSION['usuario'] = [
                        'id' => $usuario['id'],
                        'nombre' => $usuario['nombre'],
                        'apellido' => $usuario['apellido'],
                        'username' => $usuario['username'],
                        'correo' => $usuario['correo'],
                        'estado' => $estado
                    ];

                    // Redirección con animación
                    echo "<script>
                        sessionStorage.setItem('loginSuccess', 'true');
                        window.location.href = 'usuario-login.php';
                    </script>";
                    exit();
                }

            } else {
                $_SESSION['intentos']++;
                $mensaje = "Contraseña incorrecta ❌ Intento #" . $_SESSION['intentos'];
                $tipoMensaje = "danger";
            }

        } else {
            $_SESSION['intentos']++;
            $mensaje = "Usuario no encontrado ❌ Intento #" . $_SESSION['intentos'];
            $tipoMensaje = "danger";
        }

        // Bloqueo por intentos
        $limite = ($_SESSION['ciclos_bloqueo'] >= 1) ? 2 : 4;

        if ($_SESSION['intentos'] >= $limite) {
            $_SESSION['intentos'] = 0;
            $_SESSION['ciclos_bloqueo']++;
            $_SESSION['bloqueado_hasta'] = $ahora + 30;
            $restante = 30;
            $mensaje = "";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inicio de Sesión</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body {
      background-color: #1a1a1d;
      color: white;
      font-family: Arial, sans-serif;
      overflow: hidden;
    }
    .form-container {
      max-width: 450px;
      margin: 80px auto;
      padding: 30px;
      background-color: #222;
      border-radius: 12px;
      box-shadow: 0 0 15px rgba(0,0,0,0.5);
      position: relative;
      z-index: 2;
      animation: fadeIn 0.6s ease;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: scale(0.97);}
      to { opacity: 1; transform: scale(1);}
    }
    .form-container h2 {
      text-align: center;
      margin-bottom: 20px;
      color: #4aff47;
      text-shadow: 0 0 10px #4aff47;
    }
    .btn-custom {
      background-color: #4aff47;
      color: black;
      font-weight: bold;
      width: 100%;
      transition: all .25s ease;
    }
    .btn-custom:hover {
      background-color: #39cc39;
      color: white;
      transform: translateY(-2px);
    }
    .toggle-link {
      color: #4aff47;
      text-decoration: none;
    }
    .toggle-link:hover {
      text-decoration: underline;
    }
    #loadingScreen, #successScreen {
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: radial-gradient(circle at center, #0f0f0f, #1a1a1d, #000);
      display: flex;
      justify-content: center;
      align-items: center;
      flex-direction: column;
      z-index: 99999;
      color: #4aff47;
      font-family: monospace;
      font-size: 22px;
    }
    #loadingScreen .spinner, #successScreen .spinner {
      border: 8px solid #222;
      border-top: 8px solid #4aff47;
      border-radius: 50%;
      width: 70px;
      height: 70px;
      animation: spin 1s linear infinite;
      margin-bottom: 15px;
    }
    @keyframes spin { 0% { transform: rotate(0deg);} 100% { transform: rotate(360deg);} }
    .gamer-bg {
      position: fixed;
      width: 100%;
      height: 100%;
      background: linear-gradient(270deg, #1a1a1d, #0f0f0f, #222);
      background-size: 600% 600%;
      animation: bgAnimation 12s ease infinite;
      z-index: 1;
    }
    @keyframes bgAnimation {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }
    #contador {
      position: fixed;
      top: 15px;
      right: 25px;
      background: rgba(255, 0, 0, 0.85);
      color: white;
      padding: 10px 15px;
      border-radius: 10px;
      font-weight: bold;
      display: none;
      z-index: 9999;
      font-family: monospace;
      box-shadow: 0 0 10px red;
    }
  </style>
</head>
<body>

<!-- Pantalla de carga inicial -->
<div id="loadingScreen">
  <div class="spinner"></div>
  <p>Cargando...</p>
</div>

<!-- Pantalla de éxito -->
<div id="successScreen" style="display:none;">
  <div class="spinner"></div>
  <p>Iniciando sesión...</p>
</div>

<div class="gamer-bg"></div>

<div class="form-container">
  <form method="POST" action="">
    <h2>Iniciar Sesión</h2>

    <input type="hidden" name="csrf" value="<?php echo $csrf_token; ?>">

    <?php if (!empty($mensaje)): ?>
      <div class="alert alert-<?php echo $tipoMensaje; ?> text-center" id="alerta">
        <?php echo $mensaje; ?>
      </div>
    <?php endif; ?>

    <div class="mb-3">
      <label for="loginUsername" class="form-label">Nombre de Usuario</label>
      <input type="text" class="form-control" id="loginUsername" name="username" required <?php echo isset($restante) ? 'disabled' : ''; ?>>
    </div>
    <div class="mb-3">
      <label for="loginPassword" class="form-label">Contraseña</label>
      <input type="password" class="form-control" id="loginPassword" name="password" required <?php echo isset($restante) ? 'disabled' : ''; ?>>
    </div>
    <button type="submit" class="btn btn-custom" id="loginButton" <?php echo isset($restante) ? 'disabled' : ''; ?>>Ingresar</button>

    <p class="text-center mt-3">
      ¿No tienes cuenta? 
      <a href="usuario-register.php" class="toggle-link">Regístrate aquí</a>
    </p>
  </form>
</div>

<div id="contador"></div>

<script>
window.addEventListener("load", () => {
  setTimeout(() => document.getElementById("loadingScreen").style.display = "none", 800);
});

// Éxito
if (sessionStorage.getItem("loginSuccess") === "true") {
  document.getElementById("successScreen").style.display = "flex";
  sessionStorage.removeItem("loginSuccess");
  setTimeout(() => window.location.href = "usuario-index.php", 2500);
}

// Contador de bloqueo
const tiempoRestante = <?php echo isset($restante) ? $restante : 0; ?>;

if (tiempoRestante > 0) {
  const contador = document.getElementById("contador");
  const inputs = document.querySelectorAll("input");
  const boton = document.getElementById("loginButton");
  const alerta = document.getElementById("alerta");

  let segundos = tiempoRestante;
  contador.style.display = "block";
  contador.textContent = "Bloqueado por " + segundos + "s";
  inputs.forEach(i => i.disabled = true);
  boton.disabled = true;
  if (alerta) alerta.remove();

  const interval = setInterval(() => {
    segundos--;
    contador.textContent = "Bloqueado por " + segundos + "s";

    if (segundos <= 0) {
      clearInterval(interval);
      contador.style.display = "none";
      inputs.forEach(i => i.disabled = false);
      boton.disabled = false;
    }
  }, 1000);
}
</script>
</body>
</html>