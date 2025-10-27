<?php
session_start();
include 'conexion.php';

// Verificar sesión activa
if (!isset($_SESSION['usuario'])) {
  header("Location: usuario-login.php");
  exit();
}

$usuarioSesion = $_SESSION['usuario'];
$correoUsuario = is_array($usuarioSesion) ? $usuarioSesion['correo'] : $usuarioSesion;

// Obtener ID y teléfono del usuario
$sqlUsuario = "SELECT id, telefono FROM usuarios WHERE correo = ?";
$stmt = $conn->prepare($sqlUsuario);
$stmt->bind_param("s", $correoUsuario);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();
$id_usuario = $usuario['id'] ?? null;
$telefonoUsuario = $usuario['telefono'] ?? '';

if (!$id_usuario) {
  die("❌ Error: No se encontró el usuario en la base de datos.");
}

// CIP persistente temporal (mientras la sesión esté activa)
if (!isset($_SESSION['CIP'])) {
  $_SESSION['CIP'] = "CIP-" . strtoupper(substr(md5($id_usuario . time()), 0, 9));
}
$codigoCIP = $_SESSION['CIP'];

// Procesar pago
$redirigir = false;
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $tipo = $_POST['tipo'];
  $precio = floatval($_POST['precio']);
  $fecha_inicio = $_POST['fecha_inicio'];
  $fecha_fin = $_POST['fecha_fin'];

  // Verificar membresía activa
  $sqlCheck = "SELECT * FROM usuarios_vip WHERE id_usuario = ? AND estado = 'Activa'";
  $stmt = $conn->prepare($sqlCheck);
  $stmt->bind_param("i", $id_usuario);
  $stmt->execute();
  $res = $stmt->get_result();

  if ($res->num_rows > 0) {
    // Actualizar membresía existente
    $sqlUpdate = "UPDATE usuarios_vip 
                  SET tipo_membresia=?, precio=?, fecha_inicio=?, fecha_fin=?, estado='Activa', fecha_cancelacion=NULL, ultima_actualizacion=NOW()
                  WHERE id_usuario=?";
    $stmt = $conn->prepare($sqlUpdate);
    $stmt->bind_param("sdssi", $tipo, $precio, $fecha_inicio, $fecha_fin, $id_usuario);
    $stmt->execute();
    $mensaje = "✅ Tu membresía ha sido actualizada correctamente.";
  } else {
    // Insertar nueva membresía
    $sqlInsert = "INSERT INTO usuarios_vip (id_usuario, tipo_membresia, precio, fecha_inicio, fecha_fin, estado, ultima_actualizacion)
                  VALUES (?, ?, ?, ?, ?, 'Activa', NOW())";
    $stmt = $conn->prepare($sqlInsert);
    $stmt->bind_param("isdss", $id_usuario, $tipo, $precio, $fecha_inicio, $fecha_fin);
    $stmt->execute();
    $mensaje = "🎉 Membresía activada con éxito.";
  }

  $redirigir = true;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pago de Membresía VIP</title>
<style>
  body {
    background-color: #121212;
    color: white;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    margin: 0;
  }
  .custom-navbar {
    background-color: #1c1c1c;
    padding: 12px 20px;
    box-shadow: 0 4px 12px rgba(0,255,180,0.3);
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .navbar-brand { font-size: 1.3rem; font-weight: bold; color: #fff; text-decoration: none; }
  .navbar-links { display: flex; gap: 12px; flex-wrap: wrap; }
  .navbar-links a { color: #fff; text-decoration: none; font-weight: 500; transition: all 0.3s ease; }
  .navbar-links a:hover { color: #00ffb3; transform: scale(1.05); }

  .container { max-width: 800px; margin: 50px auto; padding: 20px; text-align: center; animation: fadeIn 0.8s ease-in; }
  @keyframes fadeIn { from { opacity: 0; transform: translateY(10px);} to { opacity: 1; transform: translateY(0);} }

  h2 { color: #00ffb3; margin-bottom: 20px; }
  .resumen, .formulario-pago {
    background: #1f1f1f;
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 30px;
    box-shadow: 0 0 15px rgba(0,255,180,0.4);
  }
  .formulario-pago label { display: block; margin: 10px 0 5px; color: #ffaa00; }
  .formulario-pago input, .formulario-pago select {
    width: 100%; padding: 10px; border-radius: 8px; border: none;
    margin-bottom: 15px; background: #2a2a2a; color: white;
  }
  .btn-pagar {
    background: #00ffb3; border: none; border-radius: 10px; padding: 12px 20px;
    cursor: pointer; font-size: 1rem; font-weight: bold; transition: all 0.3s ease; width: 100%;
  }
  .btn-pagar:hover { transform: scale(1.05); background: #00d69b; }
  .btn-volver {
    margin-top: 20px; background: #ffaa00; color: black; font-weight: bold;
    padding: 10px 18px; border-radius: 8px; text-decoration: none; display: inline-block;
  }
  .btn-volver:hover { background: #e69500; color: white; }
  .metodo { display: none; opacity: 0; transform: translateY(-10px); transition: all 0.5s ease; }
  .metodo.visible { display: block; opacity: 1; transform: translateY(0); }
  .codigo-cip { font-size: 1.2rem; font-weight: bold; color: #00ffb3; text-align: center; margin: 10px 0; }
  .mensaje { margin-top: 20px; color: #00ffb3; font-weight: bold; }
</style>
</head>
<body>

<nav class="custom-navbar">
  <a class="navbar-brand" href="#">Pago VIP</a>
  <div class="navbar-links">
    <a href="usuario-index.php">Inicio</a>
    <a href="usuario-galeria.php">Galería</a>
    <a href="carrito.php">Carrito</a>
    <a href="contacto.php">Contacto</a>
  </div>
</nav>

<div class="container">
  <h2>💳 Finalizar Pago</h2>
  <p>Revisa tu selección y completa los datos de pago.</p>

  <div class="resumen">
    <h3>📌 Tu selección</h3>
    <p id="resumen-plan">Cargando...</p>
    <p id="resumen-precio"></p>
  </div>

  <div class="formulario-pago">
    <h3>🔐 Selecciona tu método de pago</h3>
    <form id="formPago" method="POST">
      <input type="hidden" name="tipo" id="tipoInput">
      <input type="hidden" name="precio" id="precioInput">
      <input type="hidden" name="fecha_inicio" id="inicioInput">
      <input type="hidden" name="fecha_fin" id="finInput">

      <label for="metodo">Método de pago</label>
      <select id="metodo" name="metodo" required>
        <option value="">-- Selecciona --</option>
        <option value="pagoefectivo">PagoEfectivo (CIP)</option>
        <option value="yape">Yape</option>
        <option value="plin">Plin</option>
        <option value="tarjeta">Tarjeta</option>
      </select>

      <div id="pago-pagoefectivo" class="metodo">
        <p>Se ha generado tu código CIP:</p>
        <div class="codigo-cip" id="codigoCIP"><?php echo $codigoCIP; ?></div>
        <label for="correo">Correo</label>
        <input type="email" id="correo" value="<?php echo $correoUsuario; ?>" readonly>
      </div>

      <div id="pago-yape" class="metodo">
        <label for="dniYape">DNI</label>
        <input type="text" id="dniYape" pattern="[0-9]{8}" maxlength="8">
        <label for="numYape">Número de celular Yape</label>
        <input type="text" id="numYape" pattern="[0-9]{9}" maxlength="9" value="<?php echo $telefonoUsuario; ?>">
      </div>

      <div id="pago-plin" class="metodo">
        <label for="dniPlin">DNI</label>
        <input type="text" id="dniPlin" pattern="[0-9]{8}" maxlength="8">
        <label for="numPlin">Número de celular Plin</label>
        <input type="text" id="numPlin" pattern="[0-9]{9}" maxlength="9" value="<?php echo $telefonoUsuario; ?>">
      </div>

      <div id="pago-tarjeta" class="metodo">
        <label for="tipoTarjeta">Tipo de Tarjeta</label>
        <select id="tipoTarjeta">
          <option value="">-- Selecciona --</option>
          <option value="Debito">Débito</option>
          <option value="Credito">Crédito</option>
        </select>
        <label for="nombre">Nombre completo</label>
        <input type="text" id="nombre">
        <label for="tarjeta">Número de tarjeta</label>
        <input type="text" id="tarjeta" pattern="[0-9]{16}" maxlength="16">
        <label for="fecha">Fecha de expiración</label>
        <input type="month" id="fecha">
        <label for="cvv">CVV</label>
        <input type="password" id="cvv" pattern="[0-9]{3,4}" maxlength="4">
      </div>

      <button type="submit" class="btn-pagar">💎 Confirmar y Activar Membresía</button>
    </form>
    <?php if (isset($mensaje)) echo "<p class='mensaje'>$mensaje</p>"; ?>
  </div>

  <a href="usuario-vip-seleccion.php" class="btn-volver">⬅️ Volver a Selección</a>
</div>

<script>
const plan = JSON.parse(localStorage.getItem("membresiaSeleccionada"));
if (plan) {
  document.getElementById("resumen-plan").textContent = `${plan.tipo}`;
  document.getElementById("resumen-precio").textContent = `💲 Monto: $${plan.precio}`;
  document.getElementById("tipoInput").value = plan.tipo;
  document.getElementById("precioInput").value = plan.precio;
  document.getElementById("inicioInput").value = plan.fecha_inicio;
  document.getElementById("finInput").value = plan.fecha_fin;
}

const metodo = document.getElementById("metodo");
const metodos = document.querySelectorAll(".metodo");

metodo.addEventListener("change", () => {
  metodos.forEach(m => m.classList.remove("visible"));
  if (metodo.value) {
    const activo = document.getElementById("pago-" + metodo.value);
    activo.classList.add("visible");
  }
});

document.getElementById("formPago").addEventListener("submit", e => {
  const metodoSel = metodo.value;
  let valido = false;

  if (metodoSel === "pagoefectivo") {
    valido = true; // CIP ya generado y correo mostrado
  } else if (metodoSel === "yape" || metodoSel === "plin") {
    const dni = document.getElementById(`dni${metodoSel.charAt(0).toUpperCase() + metodoSel.slice(1)}`).value.trim();
    const num = document.getElementById(`num${metodoSel.charAt(0).toUpperCase() + metodoSel.slice(1)}`).value.trim();
    valido = /^[0-9]{8}$/.test(dni) && /^[0-9]{9}$/.test(num);
  } else if (metodoSel === "tarjeta") {
    const tipoT = document.getElementById("tipoTarjeta").value;
    const tarjeta = document.getElementById("tarjeta").value.trim();
    const cvv = document.getElementById("cvv").value.trim();
    valido = tipoT !== "" && /^[0-9]{16}$/.test(tarjeta) && /^[0-9]{3,4}$/.test(cvv);
  }

  if (!valido) {
    e.preventDefault();
    alert("⚠️ Completa correctamente los datos del método de pago.");
  } else {
    alert("Procesando pago... ✅");
  }
});

<?php if ($redirigir): ?>
setTimeout(() => {
  window.location.href = "usuario-perfil.php";
}, 4000);
<?php endif; ?>
</script>
</body>
</html>