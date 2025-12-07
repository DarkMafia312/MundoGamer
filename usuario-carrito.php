<?php
require_once __DIR__ . '/sentry.php';
session_start();
include 'conexion.php';

// ✅ Verificar sesión activa
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

$usuario = $_SESSION['usuario'];
$idUsuario = $usuario['id'] ?? 0;

// ✅ Verificar si el usuario tiene una membresía VIP activa
$vipQuery = $conn->prepare("
    SELECT 1 
    FROM usuarios_vip 
    WHERE id_usuario = ? 
      AND estado = 'Activa' 
      AND (fecha_fin IS NULL OR fecha_fin >= CURDATE())
    LIMIT 1
");
$vipQuery->bind_param("i", $idUsuario);
$vipQuery->execute();
$resVip = $vipQuery->get_result();
$esVip = ($resVip->num_rows > 0);

// ✅ Obtener productos del carrito
$query = $conn->prepare("
    SELECT 
        c.id_carrito,
        p.id_producto,
        p.titulo AS nombre,
        p.imagen,
        c.cantidad,
        c.precio_unitario
    FROM carrito c
    INNER JOIN productos p ON c.id_producto = p.id_producto
    WHERE c.id_usuario = ?
");
$query->bind_param("i", $idUsuario);
$query->execute();
$result = $query->get_result();
$carrito = $result->fetch_all(MYSQLI_ASSOC);

// ✅ Calcular totales
$total = 0;
$cantidadTotal = 0;
foreach ($carrito as $item) {
    $total += $item['precio_unitario'] * $item['cantidad'];
    $cantidadTotal += $item['cantidad'];
}

// ✅ Calcular descuento dinámico
$descuento = 0;
$mensajeDescuento = "Sin descuento aplicado";
if ($esVip) {
    $descuento = $total * 0.15;
    $mensajeDescuento = "🎖️ Usuario VIP - Descuento del 15% aplicado";
} elseif ($cantidadTotal >= 10) {
    $descuento = $total * 0.10;
    $mensajeDescuento = "🕹️ Descuento del 10% por comprar 10 o más juegos";
} elseif ($cantidadTotal >= 5) {
    $descuento = $total * 0.05;
    $mensajeDescuento = "🎮 Descuento del 5% por comprar 5 o más juegos";
}

$totalFinal = $total - $descuento;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>🛒 Mi Carrito | MundoGamer</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<style>
body {
    background: linear-gradient(135deg, #0c0c0c, #1a1a1a);
    color: #fff;
    font-family: 'Segoe UI', sans-serif;
}
.navbar {
    background: #111;
    border-bottom: 2px solid #00ffc8;
}
.navbar-brand {
    color: #00ffc8 !important;
    font-weight: bold;
    transition: transform 0.3s ease;
}
.navbar-brand:hover { transform: scale(1.1); }
.nav-link { color: #fff !important; margin: 0 10px; transition: color 0.3s ease; }
.nav-link:hover { color: #00ffc8 !important; }

.carrito-container {
    max-width: 1000px;
    margin: 50px auto;
    background: #1e1e1e;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 0 20px #00ffc833;
    animation: fadeIn 0.8s ease;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}
.carrito-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid #333;
    padding: 15px 0;
    transition: background 0.3s ease;
}
.carrito-item:hover { background: #2a2a2a; }

.carrito-item img {
    width: 110px;
    height: 110px;
    object-fit: cover;
    border-radius: 10px;
    border: 2px solid #00ffc8;
    margin-right: 20px;
}
.precio { color: #f1c40f; font-weight: bold; }
.subtotal { color: #00bfff; font-weight: bold; }
.total-container { text-align: right; margin-top: 30px; }
.btn-success {
    background: linear-gradient(90deg, #00ffc8, #00bfff);
    color: #000;
    font-weight: bold;
    transition: 0.3s;
}
.btn-success:hover { transform: scale(1.05); }

input[type=number] {
    width: 70px;
    text-align: center;
    border: none;
    border-radius: 5px;
    padding: 3px;
}

.btn-danger {
    background-color: #dc3545;
    border: none;
    transition: 0.3s;
}
.btn-danger:hover {
    transform: scale(1.05);
    background-color: #ff4757;
}
.form-control::placeholder { color: #ccc; }
</style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container">
    <a class="navbar-brand" href="usuario-index.php">🎮 Mundo Gamer</a>
    <div id="menu" class="collapse navbar-collapse show">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a href="usuario-index.php" class="nav-link">🏠 Inicio</a></li>
        <li class="nav-item"><a href="usuario-galeria.php" class="nav-link">🎮 Galería</a></li>
        <li class="nav-item"><a href="usuario-carrito.php" class="nav-link active">🛒 Carrito</a></li>
        <li class="nav-item"><a href="ayuda_cliente.php" class="nav-link">❓ Ayuda</a></li>
        <li class="nav-item"><a href="logout_usuario.php" class="nav-link">🚪 Cerrar sesión</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="carrito-container">
    <h2 class="text-center text-info mb-2">🛒 Tu Carrito</h2>
    <p class="text-center text-warning"><?= $mensajeDescuento ?></p>

<?php if (count($carrito) > 0): ?>
    <?php foreach ($carrito as $item): ?>
        <?php
            // ✅ Buscar imagen primero en img/productos/
            if (!empty($item['imagen']) && filter_var($item['imagen'], FILTER_VALIDATE_URL)) {
              $rutaImagen = $item['imagen'];
            } else {
    $rutaImagen = 'img/no-image.png';
}
        ?>
        <div class="carrito-item" id="item-<?= $item['id_carrito'] ?>">
            <img src="<?= htmlspecialchars($rutaImagen) ?>" alt="Producto">
            <div style="flex:1;">
                <h5><?= htmlspecialchars($item['nombre']) ?></h5>
                <p class="precio">S/<?= number_format($item['precio_unitario'], 2) ?></p>
                <label>Cantidad:</label>
                <input type="number" min="0" value="<?= $item['cantidad'] ?>" onchange="actualizarCantidad(<?= $item['id_carrito'] ?>, this.value)">
                <p class="subtotal">Subtotal: S/<?= number_format($item['precio_unitario'] * $item['cantidad'], 2) ?></p>
            </div>
            <button class="btn btn-danger" onclick="confirmarEliminar(<?= $item['id_carrito'] ?>)">🗑️</button>
        </div>
    <?php endforeach; ?>

    <div class="total-container">
        <h4>Total final: <span class="text-warning">S/<?= number_format($totalFinal,2) ?></span></h4>

        <div class="mt-3">
            <label for="metodo" class="form-label">💳 Método de pago:</label>
            <select id="metodo" class="form-select bg-dark text-light" onchange="mostrarCamposPago()">
                <option value="">-- Seleccione --</option>
                <option value="Tarjeta">Tarjeta de crédito/débito</option>
                <option value="Yape">Yape</option>
                <option value="Plin">Plin</option>
                <option value="PagoEfectivo">PagoEfectivo</option>
            </select>
        </div>

        <div id="camposPago" class="mt-3"></div>

        <button class="btn btn-success w-100 mt-4" onclick="procederCompra()">Proceder a la compra</button>
    </div>
<?php else: ?>
    <div class="text-center mt-4">
        <p>Tu carrito está vacío 😢</p>
        <a href="usuario-galeria.php" class="btn btn-warning">Volver a la tienda 🎮</a>
    </div>
<?php endif; ?>
</div>

<!-- MODAL ELIMINAR -->
<div class="modal fade" id="modalEliminar" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-dark text-light">
      <div class="modal-header">
        <h5 class="modal-title">🗑️ Eliminar producto</h5>
      </div>
      <div class="modal-body">
        <p>¿Seguro que deseas eliminar este producto del carrito?</p>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button class="btn btn-danger" id="btnConfirmarEliminar">Eliminar</button>
      </div>
    </div>
  </div>
</div>

<script>
let idEliminar = null;

function mostrarCamposPago() {
    const metodo = document.getElementById('metodo').value;
    const contenedor = document.getElementById('camposPago');
    let html = '';

    if (metodo === 'Tarjeta') {
        html = `
            <label>Número de tarjeta</label>
            <input class="form-control bg-dark text-light mt-2" placeholder="Número de tarjeta" required>
            <label>Nombre del titular</label>
            <input class="form-control bg-dark text-light mt-2" placeholder="Nombre del titular" required>
            <label>Fecha de expiración (MM/AA)</label>
            <input class="form-control bg-dark text-light mt-2" placeholder="MM/AA" required>
            <label>CVV</label>
            <input class="form-control bg-dark text-light mt-2" placeholder="CVV" required>
        `;
    } else if (metodo === 'Yape' || metodo === 'Plin') {
        html = `
            <label>Número de celular</label>
            <input class="form-control bg-dark text-light mt-2" placeholder="Número de celular" required>
            <label>DNI de confirmación</label>
            <input class="form-control bg-dark text-light mt-2" placeholder="DNI" required>
        `;
    } else if (metodo === 'PagoEfectivo') {
        html = `<div class="alert alert-warning mt-2 text-dark"><strong>💵 Código CIP generado automáticamente al pagar.</strong></div>`;
    }

    contenedor.innerHTML = html;
}

function actualizarCantidad(idCarrito, cantidad) {
    fetch('actualizar_carrito.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'id_carrito=' + idCarrito + '&cantidad=' + cantidad
    })
    .then(r => r.text())
    .then(res => {
        if (res.includes('ELIMINADO')) {
            document.getElementById('item-' + idCarrito).remove();
        } else if (!res.includes('OK')) {
            alert('Error al actualizar: ' + res);
        } else {
            location.reload();
        }
    });
}

function confirmarEliminar(idCarrito) {
    idEliminar = idCarrito;
    const modal = new bootstrap.Modal(document.getElementById('modalEliminar'));
    modal.show();
}

document.getElementById('btnConfirmarEliminar').addEventListener('click', () => {
    if (!idEliminar) return;
    fetch('eliminar_carrito.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'id_carrito=' + idEliminar
    })
    .then(r => r.text())
    .then(res => {
        if (res.includes('OK')) {
            document.getElementById('item-' + idEliminar).remove();
            bootstrap.Modal.getInstance(document.getElementById('modalEliminar')).hide();
        } else {
            alert('Error: ' + res);
        }
    });
});

function procederCompra() {
    const metodo = document.getElementById('metodo').value;
    if (!metodo) {
        alert('Seleccione un método de pago');
        return;
    }

    fetch('procesar_compra.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'metodo=' + encodeURIComponent(metodo)
    })
    .then(r => r.json())
    .then(res => {
        if (res.status === "OK") {
            alert('✅ Compra realizada con éxito');
            // 🔹 Redirigir automáticamente al voucher
            window.location.href = 'voucher.php?id_venta=' + res.id_venta;
        } else {
            alert('❌ Error: ' + res.mensaje);
        }
    })
    .catch(err => alert('⚠️ Error de conexión: ' + err));
}
</script>
</body>
</html>