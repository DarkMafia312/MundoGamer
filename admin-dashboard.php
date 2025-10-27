<?php
session_start();
include('conexion.php');

if (!isset($_SESSION['usuario_admin'])) {
    header("Location: admin-login.php");
    exit();
}

$usuario = $_SESSION['usuario_admin'];
$query = "SELECT nombre, apellido FROM administradores WHERE usuario_admin = '$usuario'";
$result = $conn->query($query);
$admin = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Administrador</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    #loader {
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
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

    @keyframes spin {
      100% { transform: rotate(360deg); }
    }

    @keyframes glow {
      from { box-shadow: 0 0 10px #00f5ff; }
      to { box-shadow: 0 0 30px #00ffcc; }
    }

    body {
      background: linear-gradient(135deg, #0d0d0d, #1c1c1c, #111);
      color: #fff;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      min-height: 100vh;
      padding: 30px;
      opacity: 0;
      transition: opacity 0.5s ease-in-out;
    }

    body.loaded {
      opacity: 1;
    }

    h1 {
      text-align: center;
      color: #00f5ff;
      text-shadow: 0 0 10px rgba(0, 245, 255, 0.6);
      margin-bottom: 40px;
      animation: glowText 2s infinite alternate;
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

    .card-custom {
      background: #1a1a1a;
      border: 1px solid rgba(0, 255, 255, 0.2);
      border-radius: 15px;
      padding: 25px;
      text-align: center;
      box-shadow: 0px 0px 15px rgba(0, 245, 255, 0.15);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .card-custom:hover {
      transform: translateY(-8px) scale(1.03);
      box-shadow: 0px 0px 25px rgba(0, 245, 255, 0.5);
      border-color: rgba(0, 245, 255, 0.6);
    }

    .card-custom h3 {
      margin-top: 10px;
      margin-bottom: 15px;
      color: #00ffcc;
    }

    .card-custom i {
      font-size: 2.5rem;
      margin-bottom: 10px;
      color: #00f5ff;
      text-shadow: 0 0 10px rgba(0,245,255,0.6);
    }

    .card-custom a {
      text-decoration: none;
      color: #fff;
      font-weight: bold;
      padding: 10px 15px;
      background: linear-gradient(90deg, #00f5ff, #00ffcc);
      border-radius: 8px;
      transition: 0.3s;
      display: inline-block;
    }

    .card-custom a:hover {
      background: linear-gradient(90deg, #00ffcc, #00f5ff);
      color: #000;
      box-shadow: 0 0 10px rgba(0, 245, 255, 0.8);
    }

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
    }

    .logout-btn:hover {
      background: linear-gradient(90deg, #ff1a75, #ff4d4d);
      transform: scale(1.05);
      box-shadow: 0 0 15px rgba(255, 77, 77, 0.7);
    }

    @keyframes glowText {
      from { text-shadow: 0 0 10px #00f5ff, 0 0 20px #00f5ff; }
      to { text-shadow: 0 0 20px #00ffcc, 0 0 40px #00ffcc; }
    }
  </style>
</head>
<body>

  <div id="loader">
    <div class="spinner"></div>
  </div>

  <button class="logout-btn" onclick="cerrarSesion()">Cerrar Sesión</button>

  <h1>Panel de Administración</h1>
  <p class="bienvenida">👋 Bienvenido, <strong><?= $admin['nombre'] . ' ' . $admin['apellido'] ?></strong></p>

  <div class="container">
    <div class="row g-4">
      <div class="col-md-4">
        <div class="card-custom">
          <i class="bi bi-people-fill"></i>
          <h3>Trabajadores</h3>
          <p>Administra la información de los empleados.</p>
          <a href="admin-trabajadores.php">Gestionar</a>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card-custom">
          <i class="bi bi-truck"></i>
          <h3>Proveedores</h3>
          <p>Gestiona los proveedores y sus contratos.</p>
          <a href="admin-proveedores.php">Gestionar</a>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card-custom">
          <i class="bi bi-box-seam"></i>
          <h3>Almacén</h3>
          <p>Controla los inventarios y productos.</p>
          <a href="admin-almacen.php">Gestionar</a>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card-custom">
          <i class="bi bi-controller"></i>
          <h3>Productos</h3>
          <p>Agrega, edita o elimina productos del sistema.</p>
          <a href="productos.php">Gestionar</a>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card-custom">
          <i class="bi bi-person-circle"></i>
          <h3>Usuarios</h3>
          <p>Controla la información y estado de los clientes.</p>
          <a href="admin-clientes.php">Gestionar</a>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card-custom">
          <i class="bi bi-chat-dots"></i>
          <h3>Mensajes y Calificaciones</h3>
          <p>Revisa y gestiona los mensajes y calificaciones de los usuarios.</p>
          <a href="admin-mensajes-calificacion.php">Gestionar</a>
        </div>
      </div>
    </div>
  </div>

  <script>
    window.addEventListener("load", () => {
      document.getElementById("loader").style.display = "none";
      document.body.classList.add("loaded");
    });

    function cerrarSesion() {
      fetch("logout.php")
        .then(() => {
          window.location.href = "admin-login.php";
        });
    }
  </script>

</body>
</html>