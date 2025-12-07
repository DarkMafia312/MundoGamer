<?php
require_once __DIR__ . '/sentry.php';
session_start();
include 'security.php';
verificarUsuario("usuario"); // ⬅️ Seguridad correcta para usuarios
include 'conexion.php';

$usuarioSesion = $_SESSION['usuario'];
$idUsuario = $usuarioSesion['id'];
$mensaje = "";
$tipoMensaje = "";

// ✅ Cancelar membresía VIP con confirmación
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['accion']) && $_POST['accion'] === "confirmar_cancelacion_vip") {

    $sqlCancelar = "UPDATE usuarios_vip 
                    SET estado='Cancelada', fecha_cancelacion=NOW() 
                    WHERE id_usuario=? AND estado='Activa'";
    $stmt = $conn->prepare($sqlCancelar);
    $stmt->bind_param("i", $idUsuario);

    if ($stmt->execute()) {
        $_SESSION['mensaje'] = "❌ Tu membresía VIP ha sido cancelada correctamente.";
        $_SESSION['tipoMensaje'] = "info";
    } else {
        $_SESSION['mensaje'] = "⚠️ Error al cancelar la membresía.";
        $_SESSION['tipoMensaje'] = "danger";
    }
    header("Location: usuario-perfil.php");
    exit();
}

// ✅ Guardar cambios del perfil
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['accion']) && $_POST['accion'] === "guardar") {

    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $username = trim($_POST['username']);
    $correo = trim($_POST['correo']);
    $telefono = trim($_POST['telefono']);
    $fechaNacimiento = trim($_POST['fecha_nacimiento']);
    $direccion = trim($_POST['direccion']);
    $newPassword = trim($_POST['newPassword']);
    $confirmPassword = trim($_POST['confirmPassword']);

    if (!empty($newPassword) && $newPassword !== $confirmPassword) {
        $_SESSION['mensaje'] = "Las contraseñas nuevas no coinciden.";
        $_SESSION['tipoMensaje'] = "danger";
    } else {

        if (!empty($newPassword)) {
            $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
            $sql = "UPDATE usuarios SET nombre=?, apellido=?, username=?, correo=?, telefono=?, fechaNacimiento=?, direccion=?, password=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssssi", $nombre, $apellido, $username, $correo, $telefono, $fechaNacimiento, $direccion, $hashed, $idUsuario);
        } else {
            $sql = "UPDATE usuarios SET nombre=?, apellido=?, username=?, correo=?, telefono=?, fechaNacimiento=?, direccion=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssi", $nombre, $apellido, $username, $correo, $telefono, $fechaNacimiento, $direccion, $idUsuario);
        }

        if ($stmt->execute()) {
            $_SESSION['mensaje'] = "✅ Cambios guardados correctamente.";
            $_SESSION['tipoMensaje'] = "success";

            // 🔄 Actualizar datos en sesión
            $_SESSION['usuario'] = [
                'id' => $idUsuario,
                'nombre' => $nombre,
                'apellido' => $apellido,
                'username' => $username,
                'correo' => $correo,
                'telefono' => $telefono,
                'fechaNacimiento' => $fechaNacimiento,
                'direccion' => $direccion,
                'estado' => $usuarioSesion['estado']
            ];
        } else {
            $_SESSION['mensaje'] = "❌ Error al actualizar tus datos.";
            $_SESSION['tipoMensaje'] = "danger";
        }
    }

    header("Location: usuario-perfil.php?actualizado=1");
    exit();
}

// Obtener mensaje tras redirección
if (isset($_SESSION['mensaje'])) {
    $mensaje = $_SESSION['mensaje'];
    $tipoMensaje = $_SESSION['tipoMensaje'];
    unset($_SESSION['mensaje'], $_SESSION['tipoMensaje']);
}

// Obtener datos del usuario
$sql = "SELECT * FROM usuarios WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idUsuario);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();

// Consultar membresía VIP activa
$sqlVip = "SELECT * FROM usuarios_vip WHERE id_usuario=? AND estado='Activa' ORDER BY fecha_inicio DESC LIMIT 1";
$stmtVip = $conn->prepare($sqlVip);
$stmtVip->bind_param("i", $idUsuario);
$stmtVip->execute();
$membresiaActiva = $stmtVip->get_result()->fetch_assoc();

// Formatear fecha
$fechaFormateada = "";
if (!empty($usuario['fechaNacimiento'])) {
    $fechaBruta = trim($usuario['fechaNacimiento']);
    if (preg_match("/^\d{4}-\d{2}-\d{2}$/", $fechaBruta)) {
        $fechaFormateada = $fechaBruta;
    } elseif (preg_match("/^\d{2}\/\d{2}\/\d{4}$/", $fechaBruta)) {
        $partes = explode("/", $fechaBruta);
        $fechaFormateada = $partes[2] . "-" . $partes[1] . "-" . $partes[0];
    } else {
        $fechaFormateada = date('Y-m-d', strtotime(str_replace('/', '-', $fechaBruta)));
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Perfil de Usuario</title>
<style>
  body {
    background: radial-gradient(circle at top, #0f0f0f, #000);
    color: white;
    font-family: 'Segoe UI', sans-serif;
    margin: 0;
    overflow-x: hidden;
  }

  .custom-navbar {
    background: rgba(28, 28, 28, 0.95);
    padding: 15px 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 4px 15px rgba(0,255,180,0.25);
    position: sticky;
    top: 0;
    z-index: 100;
  }
  .navbar-brand {
    font-size: 1.5rem;
    color: #00ffb3;
    font-weight: bold;
    text-decoration: none;
    letter-spacing: 1px;
  }
  .navbar-links a {
    color: #fff;
    margin-left: 18px;
    text-decoration: none;
    transition: 0.3s;
  }
  .navbar-links a:hover { color: #00ffb3; }

  .card {
    background: rgba(31,31,31,0.95);
    border: 2px solid #00ffb3;
    border-radius: 18px;
    padding: 30px;
    max-width: 700px;
    margin: 60px auto;
    box-shadow: 0 0 25px #00ffb3;
    text-align: center;
    position: relative;
    animation: fadeIn 1s ease;
  }

  @keyframes fadeIn {
    from {opacity:0; transform: translateY(20px);}
    to {opacity:1; transform: translateY(0);}
  }

  .info-title {
    color: #00ffb3;
    font-size: 1.3rem;
    margin-bottom: 15px;
    font-weight: 600;
  }

  .user-icon {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    border: 3px solid #00ffb3;
    margin-bottom: 10px;
    object-fit: cover;
    box-shadow: 0 0 15px #00ffb3;
  }

  .form-group {
    background: rgba(45,45,45,0.7);
    border-radius: 10px;
    padding: 12px;
    margin-bottom: 12px;
    text-align: left;
    transition: 0.3s;
  }
  .form-group:hover {
    box-shadow: 0 0 8px #00ffb3;
  }

  .form-group label {
    color: #ffaa00;
    font-weight: 500;
    display: block;
    margin-bottom: 5px;
  }
  .form-group input {
    width: 96%;
    padding: 10px;
    border-radius: 8px;
    border: none;
    background: #222;
    color: #fff;
  }

  .btn {
    width: 100%;
    padding: 12px;
    border: none;
    border-radius: 8px;
    font-weight: bold;
    cursor: pointer;
    transition: 0.3s;
    margin-top: 8px;
  }
  .btn:hover { transform: scale(1.05); }
  .btn-modificar { background: #00ffb3; color: black; }
  .btn-guardar { background: #ffaa00; color: black; display:none; }
  .btn-cancelar { background: #ff4d4d; color: white; display:none; }
  .btn-vip { background: #e6b800; color: black; font-weight: bold; margin-top: 15px; }

  .vip-info {
    margin-top: 18px;
    padding: 12px;
    background-color: #222;
    border-radius: 10px;
    border: 1px solid #00ffb3;
    color: #00ffb3;
  }

  .logout-btn {
    position: absolute;
    top: 14px;
    right: 14px;
    background-color: #ff4d4d;
    color: white;
    padding: 8px 12px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: 0.3s;
  }
  .logout-btn:hover { background-color: #ff2222; }

  /* ✅ Modales */
  .modal {
    display: none; position: fixed; top: 0; left: 0;
    width: 100%; height: 100%; background-color: rgba(0,0,0,0.7);
    justify-content: center; align-items: center;
    z-index: 200;
  }
  .modal-content {
    background: #1c1c1c; color: #fff; border: 2px solid #00ffb3;
    border-radius: 12px; padding: 25px; text-align: center;
    max-width: 400px;
    animation: scaleUp 0.3s ease;
  }
  @keyframes scaleUp {
    from {transform: scale(0.8); opacity:0;}
    to {transform: scale(1); opacity:1;}
  }
  .modal-content h3 { color: #00ffb3; }
  .close-modal, .confirm-modal {
    margin-top: 15px; padding: 10px 20px; border-radius: 8px;
    font-weight: bold; cursor: pointer; border: none;
  }
  .close-modal { background-color: #ffaa00; }
  .confirm-modal { background-color: #ff4d4d; color: #fff; }
</style>
</head>
<body>

<nav class="custom-navbar">
  <a class="navbar-brand" href="#">👤 Perfil</a>
  <div class="navbar-links">
    <a href="usuario-index.php" class="active">Inicio</a>
    <a href="usuario-galeria.php">Galería</a>
    <a href="usuario-carrito.php">Carrito</a>
    <a href="ayuda_cliente.php">Ayuda</a>
  </div>
</nav>

<div class="card">
  <form method="POST">
    <button type="button" class="logout-btn" onclick="window.location.href='logout_usuario.php'">Cerrar Sesión</button>
    <img src="https://i.pinimg.com/736x/99/d0/7f/99d07f72ea74f29fe21833964704cdc9.jpg" class="user-icon" alt="Usuario">
    <h3 class="info-title">Aquí puedes modificar tus datos personales</h3>

    <!-- Campos del perfil -->
    <div class="form-group"><label>Nombres</label><input type="text" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" disabled></div>
    <div class="form-group"><label>Apellidos</label><input type="text" name="apellido" value="<?php echo htmlspecialchars($usuario['apellido']); ?>" disabled></div>
    <div class="form-group"><label>Nombre de Usuario</label><input type="text" name="username" value="<?php echo htmlspecialchars($usuario['username']); ?>" disabled></div>
    <div class="form-group"><label>Correo</label><input type="email" name="correo" value="<?php echo htmlspecialchars($usuario['correo']); ?>" disabled></div>
    <div class="form-group"><label>Teléfono</label><input type="text" name="telefono" value="<?php echo htmlspecialchars($usuario['telefono'] ?? ''); ?>" disabled></div>
    <div class="form-group"><label>Fecha de Nacimiento</label><input type="date" name="fecha_nacimiento" value="<?php echo $fechaFormateada; ?>" disabled></div>
    <div class="form-group"><label>Dirección</label><input type="text" name="direccion" value="<?php echo htmlspecialchars($usuario['direccion'] ?? ''); ?>" disabled></div>

    <div class="form-group password-fields" style="display:none;">
      <label>Nueva Contraseña</label><input type="password" name="newPassword">
      <label>Confirmar Nueva Contraseña</label><input type="password" name="confirmPassword">
    </div>

    <!-- Botones -->
    <button type="button" class="btn btn-modificar" id="btnModificar">Modificar Información</button>
    <button type="submit" name="accion" value="guardar" class="btn btn-guardar" id="btnGuardar">Guardar Cambios</button>
    <button type="button" class="btn btn-cancelar" id="btnCancelar">Cancelar</button>

    <!-- Información VIP -->
    <?php if ($membresiaActiva): ?>
      <div class="vip-info">
        👑 <strong>Membresía <?php echo htmlspecialchars($membresiaActiva['tipo_membresia']); ?></strong><br>
        Válida hasta: <?php echo htmlspecialchars($membresiaActiva['fecha_fin']); ?><br>
        Estado: <span style="color:lime;">Activa</span>
      </div>
      <button type="button" class="btn btn-vip" onclick="abrirModal('modalConfirmarCancelacion')">❌ Cancelar Membresía VIP</button>
    <?php else: ?>
      <div class="vip-info" style="color:#ccc;">Aún no tienes una membresía VIP activa.</div>
      <button type="button" class="btn btn-vip" onclick="window.location.href='usuario-vip-seleccion.php'">💎 Suscribirme a VIP</button>
    <?php endif; ?>
  </form>
</div>

<!-- ✅ MODALES -->
<div id="modalGuardado" class="modal">
  <div class="modal-content">
    <h3>✅ Cambios guardados correctamente</h3>
    <button class="close-modal" onclick="cerrarModal('modalGuardado')">Aceptar</button>
  </div>
</div>

<div id="modalCancelado" class="modal">
  <div class="modal-content">
    <h3>❌ Cambios cancelados</h3>
    <p>Tu información no fue modificada.</p>
    <button class="close-modal" onclick="cerrarModal('modalCancelado')">Aceptar</button>
  </div>
</div>

<!-- ✅ Modal confirmación cancelar membresía -->
<div id="modalConfirmarCancelacion" class="modal">
  <div class="modal-content">
    <h3>⚠️ ¿Estás seguro de cancelar tu membresía VIP?</h3>
    <p>Perderás tus beneficios inmediatamente.</p>
    <form method="POST">
      <button type="submit" name="accion" value="confirmar_cancelacion_vip" class="confirm-modal">Sí, cancelar</button>
      <button type="button" class="close-modal" onclick="cerrarModal('modalConfirmarCancelacion')">No, mantener</button>
    </form>
  </div>
</div>

<script>
const btnModificar = document.getElementById('btnModificar');
const btnGuardar = document.getElementById('btnGuardar');
const btnCancelar = document.getElementById('btnCancelar');
const inputs = document.querySelectorAll('input[type="text"], input[type="email"], input[type="date"], input[type="password"]');
const passwordFields = document.querySelector('.password-fields');

btnModificar.addEventListener('click', () => {
  inputs.forEach(i => i.disabled = false);
  passwordFields.style.display = 'block';
  btnGuardar.style.display = 'block';
  btnCancelar.style.display = 'block';
  btnModificar.style.display = 'none';
});

btnCancelar.addEventListener('click', () => {
  abrirModal('modalCancelado');
  setTimeout(() => window.location.reload(), 1500);
});

function abrirModal(id) {
  document.getElementById(id).style.display = 'flex';
}
function cerrarModal(id) {
  document.getElementById(id).style.display = 'none';
}

// ✅ Mostrar modal si se acaba de guardar
<?php if (isset($_GET['actualizado']) && $tipoMensaje === "success"): ?>
abrirModal('modalGuardado');
<?php endif; ?>
</script>
</body>
</html>