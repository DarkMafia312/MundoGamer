<?php
session_start();
include 'conexion.php';

// Verificar sesión activa
if (!isset($_SESSION['usuario'])) {
  header("Location: usuario-login.php");
  exit();
}

$usuarioSesion = $_SESSION['usuario'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Seleccionar Membresía VIP</title>
<style>
  body {
    background-color: #121212;
    color: white;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    margin: 0;
    overflow-x: hidden;
  }

  /* ANIMACIONES DE ENTRADA */
  @keyframes fadeInUp {
    from {
      opacity: 0;
      transform: translateY(40px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  @keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
  }

  /* NAVBAR */
  .custom-navbar {
    background-color: #1c1c1c;
    padding: 12px 20px;
    box-shadow: 0 4px 12px rgba(0,255,180,0.3);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    animation: fadeIn 1s ease forwards;
  }

  .navbar-brand { font-size: 1.3rem; font-weight: bold; color: #fff; text-decoration: none; }
  .navbar-links { display: flex; gap: 12px; flex-wrap: wrap; }
  .navbar-links a { color: #fff; text-decoration: none; font-weight: 500; transition: all 0.3s ease; }
  .navbar-links a:hover { color: #00ffb3; transform: scale(1.05); }
  .navbar-links a.active { color: #00ffb3; font-weight: bold; }

  /* CONTENEDOR PRINCIPAL */
  .container {
    max-width: 900px;
    margin: 50px auto;
    padding: 20px;
    text-align: center;
    animation: fadeInUp 1s ease forwards;
  }

  h2 { 
    color: #00ffb3; 
    margin-bottom: 20px; 
    animation: fadeInUp 1s ease forwards;
    animation-delay: 0.2s;
  }

  p {
    animation: fadeInUp 1s ease forwards;
    animation-delay: 0.4s;
  }

  /* PLANES */
  .planes {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 20px;
    margin-top: 30px;
  }

  .plan-card {
    background: #1f1f1f;
    border: 2px solid #00ffb3;
    border-radius: 15px;
    padding: 25px;
    text-align: center;
    transition: transform 0.3s ease, box-shadow 0.3s ease, background 0.3s ease;
    opacity: 0;
    animation: fadeInUp 0.8s ease forwards;
    animation-fill-mode: forwards;
    will-change: transform, box-shadow;
  }

  /* Animación escalonada */
  .plan-card:nth-child(1) { animation-delay: 0.6s; }
  .plan-card:nth-child(2) { animation-delay: 0.9s; }

  /* EFECTO HOVER MEJORADO */
  .plan-card:hover {
    transform: scale(1.05);
    box-shadow: 0 0 25px #00ffb3;
    background: linear-gradient(145deg, #1f1f1f, #002a1e);
    transition: all 0.3s ease-in-out;
  }

  .plan-card:hover .plan-title {
    color: #ffd700;
    transition: color 0.3s ease;
  }

  .plan-title { 
    font-size: 1.2rem; 
    margin-bottom: 12px; 
    color: #ffaa00; 
  }

  .plan-options { margin: 15px 0; }
  .plan-options button {
    background: #00ffb3;
    border: none;
    border-radius: 8px;
    padding: 10px 14px;
    margin: 5px;
    cursor: pointer;
    font-weight: bold;
    transition: transform 0.2s ease, background 0.2s ease;
  }

  .plan-options button:hover {
    transform: scale(1.08);
    background: #00d69b;
    color: white;
  }

  .price {
    font-size: 1rem;
    color: #00ffb3;
    margin-top: 5px;
    font-weight: bold;
  }

  /* BOTÓN VOLVER */
  .btn-volver {
    margin-top: 30px;
    background: #ffaa00;
    color: black;
    font-weight: bold;
    padding: 12px 20px;
    border-radius: 8px;
    text-decoration: none;
    display: inline-block;
    transition: transform 0.3s ease, background 0.3s ease;
    animation: fadeInUp 1s ease forwards;
    animation-delay: 1.1s;
    opacity: 0;
    animation-fill-mode: forwards;
  }

  .btn-volver:hover {
    transform: scale(1.05);
    background: #e69500;
    color: white;
  }

  /* EFECTO DE FONDO */
  .background-glow {
    position: fixed;
    width: 300px;
    height: 300px;
    background: radial-gradient(circle, rgba(0,255,179,0.3) 0%, transparent 70%);
    filter: blur(60px);
    z-index: -1;
    animation: moveGlow 12s ease-in-out infinite alternate;
  }

  @keyframes moveGlow {
    0% { top: 10%; left: 10%; }
    50% { top: 40%; left: 60%; }
    100% { top: 20%; left: 80%; }
  }
</style>
</head>
<body>

<div class="background-glow"></div>

<nav class="custom-navbar">
  <a class="navbar-brand" href="#">Membresía VIP</a>
  <div class="navbar-links">
    <a href="usuario-index.php">Inicio</a>
    <a href="usuario-galeria.php">Galería</a>
    <a href="usuario-carrito.php">Carrito</a>
    <a href="ayuda-cliente.php">Ayuda</a>
  </div>
</nav>

<div class="container">
  <h2>👑 Selecciona tu Membresía VIP</h2>
  <p>Elige el plan que más te convenga para disfrutar de beneficios exclusivos.</p>

  <div class="planes">
    <div class="plan-card">
      <h3 class="plan-title">📅 Membresía Mensual</h3>
      <p>Acceso completo durante varios meses</p>
      <div class="plan-options">
        <button onclick="seleccionarPlan('Mensual',2,9.99)">2 Meses</button>
        <div class="price">$ 9.99</div>
        <button onclick="seleccionarPlan('Mensual',4,17.99)">4 Meses</button>
        <div class="price">$ 17.99</div>
        <button onclick="seleccionarPlan('Mensual',6,24.99)">6 Meses</button>
        <div class="price">$ 24.99</div>
      </div>
    </div>

    <div class="plan-card">
      <h3 class="plan-title">📅 Membresía Anual</h3>
      <p>Beneficios exclusivos a largo plazo</p>
      <div class="plan-options">
        <button onclick="seleccionarPlan('Anual',1,39.99)">1 Año</button>
        <div class="price">$ 39.99</div>
        <button onclick="seleccionarPlan('Anual',2,69.99)">2 Años</button>
        <div class="price">$ 69.99</div>
        <button onclick="seleccionarPlan('Anual',3,99.99)">3 Años</button>
        <div class="price">$ 99.99</div>
      </div>
    </div>
  </div>

  <a href="usuario-perfil.php" class="btn-volver">⬅️ Volver al Perfil</a>
</div>

<script>
function seleccionarPlan(tipo, duracion, precio) {
  const hoy = new Date();
  let fechaFin;

  if (tipo === "Mensual") {
    fechaFin = new Date(hoy);
    fechaFin.setMonth(hoy.getMonth() + duracion);
  } else if (tipo === "Anual") {
    fechaFin = new Date(hoy);
    fechaFin.setFullYear(hoy.getFullYear() + duracion);
  }

  const plan = {
    tipo,
    duracion,
    precio,
    fecha_inicio: hoy.toISOString().split("T")[0],
    fecha_fin: fechaFin.toISOString().split("T")[0]
  };

  localStorage.setItem("membresiaSeleccionada", JSON.stringify(plan));
  window.location.href = "usuario-vip-pago.php";
}
</script>

</body>
</html>