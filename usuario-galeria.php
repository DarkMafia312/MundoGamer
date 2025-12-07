<?php
require_once __DIR__ . '/sentry.php';
session_start();
include 'security.php';
verificarUsuario("usuario");
include 'conexion.php';

$usuario = $_SESSION['usuario'];

// Detectar campo ID real desde la sesión o base de datos
$idUsuario = null;
if (isset($usuario['id_usuario'])) {
    $idUsuario = $usuario['id_usuario'];
} elseif (isset($usuario['id'])) {
    $idUsuario = $usuario['id'];
} elseif (isset($usuario['idUsuario'])) {
    $idUsuario = $usuario['idUsuario'];
}

if (!$idUsuario && isset($usuario['username'])) {
    $stmt = $conn->prepare("
        SELECT COALESCE(id_usuario, id, idUsuario) AS id_usuario_real 
        FROM usuarios 
        WHERE username = ? LIMIT 1
    ");
    $stmt->bind_param("s", $usuario['username']);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $idUsuario = $res['id_usuario_real'] ?? 0;
}

$nombreCompleto = trim(($usuario['nombre'] ?? '') . " " . ($usuario['apellido'] ?? ''));
$username = $usuario['username'] ?? 'Invitado';
$mostrarNombre = !empty($nombreCompleto) ? $nombreCompleto : $username;

// Verificar membresía VIP activa
$esVip = false;
if ($idUsuario) {
    $vipQuery = $conn->prepare("
        SELECT 1 FROM usuarios_vip 
        WHERE id_usuario = ? AND estado = 'activa' AND fecha_fin > NOW()
    ");
    $vipQuery->bind_param("i", $idUsuario);
    $vipQuery->execute();
    $esVip = $vipQuery->get_result()->num_rows > 0;
}

// Obtener productos
$stmt = $conn->prepare("SELECT * FROM productos ORDER BY id_producto DESC");
$stmt->execute();
$productos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// 🔹 Obtener calificaciones previas del usuario
$opiniones = [];
if ($idUsuario) {
    $opQuery = $conn->prepare("
        SELECT id_producto, puntuacion, comentario 
        FROM calificaciones WHERE id_usuario = ?
    ");
    $opQuery->bind_param("i", $idUsuario);
    $opQuery->execute();
    $res = $opQuery->get_result();
    while ($row = $res->fetch_assoc()) {
        $opiniones[$row['id_producto']] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Galería - MundoGamer</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

<style>
:root {
  --accent: #ffc857;
  --dark: #0c0c0e;
  --card-bg: rgba(18,18,20,0.88);
  --glow: 0 0 12px rgba(255,200,87,0.6);
}

/* PANTALLA DE CARGA */
#loader {
  position: fixed;
  inset: 0;
  background: #000;
  color: #ffc857;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-direction: column;
  z-index: 9999;
}
#loader i {
  font-size: 2.5rem;
  animation: spin 1.5s linear infinite;
}
@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

body {
  font-family: 'Poppins', sans-serif;
  color: #fff;
  background: url("https://img.freepik.com/foto-gratis/fondo-cosmico-luces-laser-colores-perfecto-fondo-pantalla-digital_181624-32741.jpg?w=2000") no-repeat center center fixed;
  background-size: cover;
  margin: 0;
}
body::before {
  content: "";
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.7);
  z-index: 0;
}
main { position: relative; z-index: 1; }

/* NAVBAR */
.navbar {
  background: rgba(10,10,12,0.88)!important;
  backdrop-filter: blur(6px);
  box-shadow: 0 4px 10px rgba(0,0,0,0.5);
}
.navbar-brand {
  font-weight: 700;
  color: var(--accent)!important;
}
.navbar-nav .nav-link {
  color: #ddd!important;
  font-weight: 500;
  margin: 0 6px;
  transition: all .2s ease;
}
.navbar-nav .nav-link:hover, .navbar-nav .nav-link.active {
  color: var(--accent)!important;
  text-shadow: var(--glow);
}
.navbar-nav .btn-danger {
  font-size: 0.9rem;
}

/* HERO */
.hero {
  text-align: center;
  margin-top: 40px;
  margin-bottom: 20px;
}
.hero h1 {
  color: var(--accent);
  font-weight: 700;
  text-shadow: 0 4px 10px rgba(0,0,0,0.6);
}
.hero p { color: #ddd; }

/* TARJETAS */
.card-game {
  background: var(--card-bg);
  border-radius: 14px;
  overflow: hidden;
  transition: all 0.3s ease;
  border: 1px solid rgba(255,255,255,0.1);
}
.card-game:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 25px rgba(0,0,0,0.7);
}
.card-game img {
  width: 100%;
  height: 200px;
  object-fit: cover;
}
.card-body { padding: 16px; }
.card-title { color: var(--accent); font-weight: 700; }
.card-desc { color: #ccc; font-size: 0.9rem; }
.vip-badge {
  background: linear-gradient(45deg,#ffd56b,#ff9a3c);
  color: #000;
  padding: 4px 8px;
  border-radius: 6px;
  font-weight: 700;
  font-size: 0.85rem;
}
.btn-vip {
  background: linear-gradient(45deg,#ffd56b,#ff9a3c);
  border: none;
  color: #000;
  font-weight: 700;
}
.btn-cart {
  background: #28a745;
  border: none;
  font-weight: 700;
}
.modal-content {
  background-color: #1a1a1a;
  color: #fff;
  border: 1px solid #333;
}

/* ⭐ CALIFICACIÓN */
.rating {
  display: flex;
  justify-content: center;
  margin-top: 10px;
}
.rating i {
  font-size: 1.6rem;
  color: #ccc;
  cursor: pointer;
  transition: color .2s;
}
.rating i.active {
  color: #ffc857;
  transform: scale(1.15);
  transition: transform .15s ease, color .15s ease;
}
.rating i:hover {
  color: #ffe48c;
  transform: scale(1.25);
}
.rating i:hover ~ i {
  color: #ccc;
}

textarea {
  width: 100%;
  resize: none;
  border-radius: 8px;
  border: 1px solid #444;
  background: #111;
  color: #fff;
  padding: 8px;
}
</style>
</head>
<body>

<!-- LOADER -->
<div id="loader">
  <i class="bi bi-controller"></i>
  <p class="mt-2">Cargando MundoGamer...</p>
</div>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top">
  <div class="container">
    <a class="navbar-brand" href="usuario-index.php">
      <i class="bi bi-controller"></i> MundoGamer
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav ms-auto align-items-center">
        <li class="nav-item"><a class="nav-link" href="usuario-index.php"><i class="bi bi-house-door"></i> Inicio</a></li>
        <li class="nav-item"><a class="nav-link active" href="usuario-galeria.php"><i class="bi bi-collection"></i> Galería</a></li>
        <li class="nav-item"><a class="nav-link" href="usuario-carrito.php"><i class="bi bi-cart4"></i> Carrito</a></li>
        <li class="nav-item"><a class="nav-link" href="ayuda_cliente.php"><i class="bi bi-question-circle"></i> Ayuda</a></li>
        <li class="nav-item ms-3">
          <form action="logout_usuario.php" method="POST" class="m-0">
            <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-box-arrow-right"></i> Salir</button>
          </form>
        </li>
      </ul>
    </div>
  </div>
</nav>

<main>
<div class="container mt-5 pt-4">
  <div class="hero">
    <h1>Catálogo de Juegos</h1>
    <p>Explora los mejores títulos disponibles. ¡Agrega tus favoritos al carrito y deja tu opinión!</p>
    <?php if (!$esVip): ?>
      <a href="usuario-vip-seleccion.php" class="btn btn-vip mt-3">👑 Hacerse VIP</a>
    <?php endif; ?>
  </div>

  <div class="row row-cols-1 row-cols-md-3 g-4 mt-3">
    <?php if(count($productos) == 0): ?>
      <p class="text-center text-secondary">⚠️ No hay productos disponibles aún.</p>
    <?php else: ?>
      <?php foreach($productos as $prod): 
        $op = $opiniones[$prod['id_producto']] ?? null;
        $puntuacion = $op['puntuacion'] ?? 0;
        $comentario = $op['comentario'] ?? "";
        $bloquear = $op ? 'disabled' : '';
      ?>
        <div class="col">
          <div class="card-game h-100 p-2">
            <img src="<?= htmlspecialchars($prod['imagen'] ?: 'https://via.placeholder.com/640x360?text=Sin+imagen'); ?>" alt="Juego">
            <div class="card-body">
              <h5 class="card-title"><?= htmlspecialchars($prod['titulo']); ?></h5>
              <p class="card-desc mb-2"><?= htmlspecialchars(substr($prod['descripcion'],0,120)); ?>...</p>
              <p><strong>$<?= number_format($prod['precio'],2); ?></strong> — <?= htmlspecialchars($prod['plataforma']); ?></p>
              
              <div class="rating" data-product="<?= $prod['id_producto']; ?>">
                <?php for($i=1; $i<=5; $i++): ?>
                  <i class="bi <?= ($puntuacion >= $i ? 'bi-star-fill active' : 'bi-star'); ?>" data-value="<?= $i; ?>"></i>
                <?php endfor; ?>
              </div>

              <textarea class="mt-2" rows="2" placeholder="Escribe tu opinión..." maxlength="200" <?= $bloquear; ?>><?= htmlspecialchars($comentario); ?></textarea>
              <?php if(!$bloquear): ?>
                <button class="btn btn-warning w-100 mt-2" onclick="guardarOpinion(this, <?= $prod['id_producto']; ?>)">💬 Guardar Opinión</button>
              <?php else: ?>
                <button class="btn btn-secondary w-100 mt-2" disabled>✅ Opinión guardada</button>
              <?php endif; ?>
              
              <hr class="text-secondary">
              <button class="btn btn-cart w-100" onclick="agregarCarrito('<?= $prod['id_producto']; ?>')">🛒 Agregar al carrito</button>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>
</main>

<!-- MODAL ALERTA -->
<div class="modal fade" id="modalCarrito" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center">
      <div class="modal-header border-0">
        <h5 class="modal-title text-warning">Producto añadido ✅</h5>
      </div>
      <div class="modal-body">
        <p>El producto ha sido agregado al carrito exitosamente.</p>
      </div>
      <div class="modal-footer border-0">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Seguir comprando</button>
        <a href="usuario-carrito.php" class="btn btn-success">Ir al carrito</a>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// 🔄 Pantalla de carga
window.addEventListener('load', () => {
  setTimeout(() => document.getElementById('loader').style.display = 'none', 800);
});

// ⭐ Calificación de estrellas
document.querySelectorAll('.rating').forEach(rating => {
  const stars = rating.querySelectorAll('i');
  if (rating.closest('.card-body').querySelector('textarea').disabled) return;
  stars.forEach(star => {
    star.addEventListener('mouseenter', () => {
      stars.forEach(s => s.classList.remove('active'));
      star.classList.add('active');
      let prev = star.previousElementSibling;
      while (prev) { prev.classList.add('active'); prev = prev.previousElementSibling; }
    });
    star.addEventListener('click', () => {
      rating.dataset.selected = star.dataset.value;
      stars.forEach(s => s.style.pointerEvents = 'none');
    });
  });
});

// 💬 Guardar opinión
function guardarOpinion(btn, idProd){
  const card = btn.closest('.card-body');
  const rating = card.querySelector('.rating');
  const stars = rating.dataset.selected || 0;
  const comentario = card.querySelector('textarea').value.trim();

  if (stars == 0 || comentario === "") {
    alert("Por favor selecciona una puntuación y escribe una opinión antes de enviar.");
    return;
  }

  fetch('guardar_calificacion.php', {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded'},
    body: `id_producto=${idProd}&puntuacion=${stars}&comentario=${encodeURIComponent(comentario)}`
  })
  .then(() => {
    btn.disabled = true;
    card.querySelector('textarea').disabled = true;
    card.querySelectorAll('.rating i').forEach(s => s.style.pointerEvents = 'none');
    btn.textContent = "✅ Opinión guardada";
  })
  .catch(() => alert('❌ Error al enviar opinión.'));
}

// 🛒 Agregar al carrito
function agregarCarrito(idProd){
  fetch('agregar_carrito.php', {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded'},
    body: 'id_producto=' + idProd
  })
  .then(() => new bootstrap.Modal(document.getElementById('modalCarrito')).show())
  .catch(() => alert('❌ Error al agregar al carrito.'));
}
</script>
</body>
</html>