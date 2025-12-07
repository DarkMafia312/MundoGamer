<?php
require_once __DIR__ . '/sentry.php';
session_start();
require_once 'conexion.php'; // Usa require_once para evitar inclusiones repetidas

// Verificar sesión activa
if (!isset($_SESSION['usuario_admin'])) {
    header("Location: admin-login.php");
    exit();
}

// Sanitizar usuario
$usuario = clean_input($_SESSION['usuario_admin']);

// Consulta preparada
$stmt = $conn->prepare("SELECT nombre, apellido FROM administradores WHERE usuario_admin = ?");
$stmt->bind_param("s", $usuario);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

if (!$admin) {
    // Si no existe el admin, cerrar sesión por seguridad
    session_destroy();
    header("Location: admin-login.php?error=notfound");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Administrador</title>

  <!-- Bootstrap & Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

  <style>
    /* Loader */
    #loader {
      position: fixed;
      inset: 0;
      background: #0d0d0d;
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 9999;
    }

    .spinner {
      border: 6px solid rgba(0,255,255,0.1);
      border-left-color: #00f5ff;
      border-radius: 50%;
      width: 70px;
      height: 70px;
      animation: spin 1s linear infinite, glow 1.5s infinite alternate;
    }

    @keyframes spin { to { transform: rotate(360deg); } }
    @keyframes glow {
      from { box-shadow: 0 0 10px #00f5ff; }
      to { box-shadow: 0 0 30px #00ffcc; }
    }

    /* Body */
    body {
      background: linear-gradient(135deg, #0d0d0d, #1c1c1c, #111);
      color: #fff;
      font-family: 'Segoe UI', sans-serif;
      min-height: 100vh;
      padding: 30px;
      opacity: 0;
      transition: opacity 0.5s ease-in-out;
    }
    body.loaded { opacity: 1; }

    h1 {
      text-align: center;
      color: #00f5ff;
      text-shadow: 0 0 10px rgba(0, 245, 255, 0.6);
      margin-bottom: 40px;
      animation: glowText 2s infinite alternate;
    }

    @keyframes glowText {
      from { text-shadow: 0 0 10px #00f5ff; }
      to { text-shadow: 0 0 25px #00ffcc; }
    }

    .bienvenida {
      text-align: center;
      color: #00ffcc;
      margin-bottom: 40px;
      font-size: 1.2rem;
      animation: fadeInUp 1s ease-in-out;
    }

    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* Cards */
    .card-custom {
      background: #1a1a1a;
      border: 1px solid rgba(0, 255, 255, 0.2);
      border-radius: 15px;
      padding: 25px;
      text-align: center;
      box-shadow: 0px 0px 15px rgba(0, 245, 255, 0.15);
      transition: transform 0.3s, box-shadow 0.3s;
    }

    .card-custom:hover {
      transform: translateY(-8px) scale(1.03);
      box-shadow: 0px 0px 25px rgba(0, 245, 255, 0.5);
      border-color: rgba(0, 245, 255, 0.6);
    }

    .card-custom h3 { color: #00ffcc; }
    .card-custom i {
      font-size: 2.5rem;
      color: #00f5ff;
      text-shadow: 0 0 10px rgba(0, 245, 255, 0.6);
    }

    .card-custom a {
      text-decoration: none;
      color: #000;
      font-weight: bold;
      padding: 10px 15px;
      background: linear-gradient(90deg, #00f5ff, #00ffcc);
      border-radius: 8px;
      transition: 0.3s;
      display: inline-block;
    }

    .card-custom a:hover {
      background: linear-gradient(90deg, #00ffcc, #00f5ff);
      box-shadow: 0 0 10px rgba(0, 245, 255, 0.8);
    }

    /* Botón Cerrar Sesión */
    .logout-btn {
      position: absolute;
      top: 20px;
      right: 30px;
      background: linear-gradient(90deg, #ff4d4d, #ff1a75);
      color: #fff;
      border: none;
      padding: 10px 18px;
      border-radius: 8px;
      font-weight: bold;
      transition: 0.3s;
      cursor: pointer;
    }

    .logout-btn:hover {
      background: linear-gradient(90deg, #ff1a75, #ff4d4d);
      transform: scale(1.05);
      box-shadow: 0 0 15px rgba(255, 77, 77, 0.7);
    }
  </style>
</head>

<body>

  <!-- Loader -->
  <div id="loader">
    <div class="spinner"></div>
  </div>

  <!-- Botón cerrar sesión -->
  <button class="logout-btn" onclick="cerrarSesion()">Cerrar Sesión</button>

  <h1>Panel de Administración</h1>

  <p class="bienvenida">
    👋 Bienvenido, <strong><?= htmlspecialchars($admin['nombre'] . ' ' . $admin['apellido']) ?></strong>
  </p>

  <!-- Tarjetas del panel -->
  <div class="container">
    <div class="row g-4">

      <?php
      $cards = [
        ["bi-people-fill", "Trabajadores", "Administra la información de los empleados.", "admin-trabajadores.php"],
        ["bi-truck", "Proveedores", "Gestiona los proveedores y sus contratos.", "admin-proveedores.php"],
        ["bi-box-seam", "Almacén", "Controla los inventarios y productos.", "admin-almacen.php"],
        ["bi-controller", "Productos", "Agrega, edita o elimina productos.", "productos.php"],
        ["bi-person-circle", "Usuarios", "Controla la información de los clientes.", "admin-clientes.php"],
        ["bi-chat-dots", "Mensajes y Calificaciones", "Gestiona mensajes y calificaciones.", "admin-mensajes-calificacion.php"],
      ];

      foreach ($cards as $card): ?>
        <div class="col-md-4">
          <div class="card-custom">
            <i class="bi <?= $card[0] ?>"></i>
            <h3><?= $card[1] ?></h3>
            <p><?= $card[2] ?></p>
            <a href="<?= $card[3] ?>">Gestionar</a>
          </div>
        </div>
      <?php endforeach; ?>

    </div>
  </div>

  <script>
    // Transición de carga
    window.addEventListener("load", () => {
      document.getElementById("loader").style.display = "none";
      document.body.classList.add("loaded");
    });

    // Cerrar sesión
    function cerrarSesion() {
      fetch("logout.php")
        .then(() => { window.location.href = "admin-login.php"; });
    }
  </script>

</body>
</html>
