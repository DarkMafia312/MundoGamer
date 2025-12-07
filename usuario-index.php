<?php
require_once __DIR__ . '/sentry.php';
session_start();
include 'conexion.php';
// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario'])) {
  header("Location: usuario-login.php");
  exit();
}

// Datos del usuario logueado
$usuario = $_SESSION['usuario'];
$nombreCompleto = trim($usuario['nombre'] . " " . $usuario['apellido']);
$username = $usuario['username'];
$mostrarNombre = !empty($nombreCompleto) ? $nombreCompleto : $username;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Mundo Gamer - Inicio</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

  <style>
    /* ===== PANTALLA DE CARGA ===== */
    #loader {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: radial-gradient(circle at center, #000000 40%, #0a0a0a 100%);
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 9999;
    }

    .spinner {
      border: 6px solid rgba(255, 255, 255, 0.1);
      border-top: 6px solid #00ffae;
      border-radius: 50%;
      width: 70px;
      height: 70px;
      animation: spin 1.2s linear infinite;
    }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    body {
      background: url('https://wallpapercave.com/wp/wp9112148.jpg') no-repeat center center fixed;
      background-size: cover;
      color: white;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      overflow-x: hidden;
      overflow-y: scroll;
      scroll-behavior: smooth;
    }

    html, body {
      height: 100%;
    }

    .custom-navbar {
      background-color: rgba(0, 0, 0, 0.9);
      box-shadow: 0 4px 12px rgba(0,0,0,0.6);
      padding: 12px 20px;
      transition: background-color 0.4s ease;
    }

    .custom-navbar.scrolled {
      background-color: rgba(0, 0, 0, 0.98);
      transition: 0.4s;
    }

    .custom-navbar .nav-link {
      color: #fff;
      font-weight: 500;
      margin: 0 8px;
      padding: 8px 16px;
      border-radius: 8px;
      transition: all 0.3s ease;
    }

    .custom-navbar .nav-link:hover,
    .custom-navbar .nav-link.active {
      background-color: #00ffae;
      color: black;
      font-weight: bold;
      transform: scale(1.1);
    }

    /* HERO */
    .hero {
      background: rgba(0, 0, 0, 0.8);
      padding: 100px 20px;
      border-radius: 20px;
      text-align: center;
      margin-bottom: 60px;
      animation: fadeIn 1.5s ease-in-out;
    }

    .hero h1 {
      font-size: 3.2rem;
      font-weight: bold;
      color: #00ffae;
      text-shadow: 2px 2px 10px black;
      animation: neonGlow 2s infinite alternate;
    }

    .hero p {
      font-size: 1.2rem;
      margin-top: 20px;
      opacity: 0.9;
    }

    @keyframes neonGlow {
      from { text-shadow: 0 0 5px #00ffae, 0 0 10px #00ffae; }
      to { text-shadow: 0 0 20px #00ffae, 0 0 40px #00ffae; }
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-30px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* TITULOS */
    .section-title {
      text-align: center;
      margin: 60px 0 30px;
      font-weight: bold;
      font-size: 2rem;
      color: #00ffea;
      text-shadow: 1px 1px 5px black;
      position: relative;
    }

    .section-title::after {
      content: "";
      display: block;
      width: 60px;
      height: 3px;
      background: #00ffea;
      margin: 10px auto 0;
      border-radius: 5px;
    }

    /* TARJETAS */
    .card {
      background: rgba(0,0,0,0.85);
      border: none;
      border-radius: 15px;
      transition: transform 0.4s ease, box-shadow 0.4s ease;
      color: white;
    }

    .card:hover {
      transform: translateY(-12px) scale(1.02);
      box-shadow: 0px 8px 25px rgba(0,255,200,0.6);
    }

    footer {
      background: rgba(0,0,0,0.95);
      text-align: center;
      padding: 25px 0;
      margin-top: 60px;
      border-top: 2px solid #00ffae;
      position: relative;
    }

    footer p {
      margin: 0;
    }

    .logout-btn {
      background-color: #ff4d4d;
      color: white;
      border: none;
      padding: 8px 15px;
      border-radius: 8px;
      font-weight: bold;
      transition: all 0.3s ease;
    }

    .logout-btn:hover {
      background-color: #ff1a1a;
      transform: scale(1.05);
    }

    [data-aos] {
      transition: all 0.8s ease;
    }
  </style>
</head>
<body>

<!-- ======= PANTALLA DE CARGA ======= -->
<div id="loader">
  <div class="spinner"></div>
</div>

<!-- ======= NAVBAR ======= -->
<nav class="navbar navbar-expand-lg navbar-dark custom-navbar fixed-top">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="usuario-perfil.php">
      <img src="https://i.pinimg.com/736x/99/d0/7f/99d07f72ea74f29fe21833964704cdc9.jpg" 
           alt="Usuario" width="35" class="me-2 rounded-circle border border-success">
      <span><?php echo htmlspecialchars($mostrarNombre); ?></span>
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto align-items-center">
        <li class="nav-item"><a class="nav-link active" href="usuario-index.php">Inicio</a></li>
        <li class="nav-item"><a class="nav-link" href="usuario-galeria.php">Galería</a></li>
        <li class="nav-item"><a class="nav-link" href="usuario-carrito.php">Carrito</a></li>
        <li class="nav-item"><a class="nav-link" href="ayuda_cliente.php">Ayuda</a></li>
        <li class="nav-item ms-3">
          <form method="POST" action="logout_usuario.php">
            <button type="submit" class="logout-btn">Cerrar Sesión</button>
          </form>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- ======= CONTENIDO ======= -->
<div class="container" style="margin-top:120px;">
  <div class="hero" data-aos="zoom-in">
    <h1>Bienvenido a <span style="color:#ff4757;">Mundo Gamer</span></h1>
    <p class="lead">Descubre, juega y comparte tu pasión por los videojuegos. 🎮🔥</p>
    <a href="usuario-galeria.php" class="btn btn-success btn-lg mt-3">Explorar Juegos</a>
  </div>
</div>

<div class="container">
  <h2 class="section-title" data-aos="fade-up">🎯 Categorías Populares</h2>
  <div class="row g-4 text-center">
    <div class="col-md-3" data-aos="fade-right">
      <div class="card p-3">
        <h5>Acción</h5>
        <p>Dispara, corre y sobrevive en mundos llenos de adrenalina.</p>
      </div>
    </div>
    <div class="col-md-3" data-aos="fade-up">
      <div class="card p-3">
        <h5>Aventura</h5>
        <p>Explora mundos abiertos y vive historias inolvidables.</p>
      </div>
    </div>
    <div class="col-md-3" data-aos="fade-up">
      <div class="card p-3">
        <h5>Estrategia</h5>
        <p>Demuestra tu inteligencia en batallas que requieren táctica.</p>
      </div>
    </div>
    <div class="col-md-3" data-aos="fade-left">
      <div class="card p-3">
        <h5>Multijugador</h5>
        <p>Conéctate con amigos y compite en línea con jugadores globales.</p>
      </div>
    </div>
  </div>
</div>

<div class="container">
  <h2 class="section-title" data-aos="fade-up">🔥 Juegos Destacados</h2>
  <div class="row g-4">
    <div class="col-md-4" data-aos="flip-left">
      <div class="card h-100">
        <img src="https://cdn.cloudflare.steamstatic.com/steam/apps/271590/header.jpg" class="card-img-top" alt="GTA V">
        <div class="card-body text-center">
          <h5 class="card-title">GTA V</h5>
          <p class="card-text">Explora Los Santos en un mundo abierto lleno de acción y aventuras.</p>
          <a href="usuario-galeria.php" class="btn btn-outline-success">Ver más</a>
        </div>
      </div>
    </div>
    <div class="col-md-4" data-aos="flip-up">
      <div class="card h-100">
        <img src="https://cdn.cloudflare.steamstatic.com/steam/apps/730/header.jpg" class="card-img-top" alt="CS:GO">
        <div class="card-body text-center">
          <h5 class="card-title">CS:GO</h5>
          <p class="card-text">Compite en el shooter táctico más jugado del mundo con partidas 5v5.</p>
          <a href="usuario-galeria.php" class="btn btn-outline-success">Ver más</a>
        </div>
      </div>
    </div>
    <div class="col-md-4" data-aos="flip-right">
      <div class="card h-100">
        <img src="https://cdn.cloudflare.steamstatic.com/steam/apps/570/header.jpg" class="card-img-top" alt="Dota 2">
        <div class="card-body text-center">
          <h5 class="card-title">Dota 2</h5>
          <p class="card-text">Estrategia, habilidad y trabajo en equipo en el MOBA más competitivo.</p>
          <a href="usuario-galeria.php" class="btn btn-outline-success">Ver más</a>
        </div>
      </div>
    </div>
  </div>
</div>

<footer data-aos="fade-up">
  <p>&copy; 2025 Mundo Gamer - Todos los derechos reservados</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
  AOS.init({ duration: 1200, once: true });

  window.addEventListener('scroll', () => {
    document.querySelector('.custom-navbar').classList.toggle('scrolled', window.scrollY > 50);
  });

  window.addEventListener('load', () => {
    const loader = document.getElementById('loader');
    setTimeout(() => loader.style.opacity = '0', 300);
    setTimeout(() => loader.style.display = 'none', 600);
  });
</script>
</body>
</html>
