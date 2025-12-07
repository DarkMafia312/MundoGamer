<?php
// admin-almacen.php — versión integrada con security.php
// ----------------------------------------------------
// Requisitos: security.php (el que me pasaste) y conexion.php
// ----------------------------------------------------
require_once __DIR__ . '/sentry.php';
require_once __DIR__ . '/security.php';
secure_bootstrap(); // inicia headers y sesión segura

require_once __DIR__ . '/conexion.php';
require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// --- Helpers local ---
function int_val_or_zero($v){ return (int)$v; }
function esc_html($v){ return htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

// === EXPORTAR A EXCEL ===
if (isset($_GET['export'])) {
    // Permitimos export sólo si token CSRF coincide (mitigación contra CSRF via GET)
    $token = $_GET['csrf'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403); die("Acción no autorizada.");
    }

    try {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Almacenes');

        // Encabezados
        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'Trabajador');
        $sheet->setCellValue('C1', 'Nombre del Almacén');
        $sheet->setCellValue('D1', 'Ubicación');
        $sheet->setCellValue('E1', 'Teléfono');
        $sheet->setCellValue('F1', 'Stock');
        $sheet->setCellValue('G1', 'Productos');
        $sheet->setCellValue('H1', 'Fecha Registro');
        $sheet->setCellValue('I1', 'Fecha Actualización');

        $sql = "
            SELECT a.*, CONCAT(t.nombres, ' ', t.apellidos) AS trabajador,
                   GROUP_CONCAT(p.titulo SEPARATOR ', ') AS productos
            FROM almacenes a
            LEFT JOIN trabajadores t ON a.trabajador_id = t.id
            LEFT JOIN almacen_productos ap ON a.id = ap.almacen_id
            LEFT JOIN productos p ON ap.producto_id = p.id_producto
            GROUP BY a.id
            ORDER BY a.id ASC
        ";
        $query = $conn->query($sql);

        $fila = 2;
        while ($row = $query->fetch_assoc()) {
            $sheet->setCellValue("A{$fila}", $row['id']);
            $sheet->setCellValue("B{$fila}", $row['trabajador']);
            $sheet->setCellValue("C{$fila}", $row['nombre']);
            $sheet->setCellValue("D{$fila}", $row['ubicacion']);
            $sheet->setCellValue("E{$fila}", $row['telefono']);
            $sheet->setCellValue("F{$fila}", $row['stock']);
            $sheet->setCellValue("G{$fila}", $row['productos']);
            $sheet->setCellValue("H{$fila}", $row['fecha_registro']);
            $sheet->setCellValue("I{$fila}", $row['fecha_actualizacion']);
            $fila++;
        }

        foreach (range('A', 'I') as $col) $sheet->getColumnDimension($col)->setAutoSize(true);

        $filename = "almacenes_MundoGamer.xlsx";
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        $writer = new Xlsx($spreadsheet);
        $writer->save("php://output");
        exit;
    } catch (Throwable $e) {
        error_log("Export failed: " . $e->getMessage());
        http_response_code(500);
        echo "Error al generar el archivo. Intenta más tarde.";
        exit;
    }
}

// --------------------------------------
// Mensajes / alertas
$alerta = $_GET['mensaje'] ?? '';
$tipo_alerta = $_GET['tipo'] ?? 'info';

// --------------------------------------
// ELIMINAR (vía AJAX POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'eliminar') {
    csrf_verify_or_die(); // aborta si CSRF inválido

    $id = int_val_or_zero($_POST['eliminar_id'] ?? 0);
    if ($id <= 0) {
        http_response_code(400); echo "error"; exit;
    }

    // eliminar relaciones y almacén (prepared)
    $stmt_del = $conn->prepare("DELETE FROM almacen_productos WHERE almacen_id=?");
    $stmt_del->bind_param("i", $id);
    $stmt_del->execute();
    $stmt_del->close();

    $stmt = $conn->prepare("DELETE FROM almacenes WHERE id=?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo "ok";
    } else {
        error_log("Delete almacen failed: " . $stmt->error);
        http_response_code(500); echo "error";
    }
    $stmt->close();
    exit;
}

// --------------------------------------
// GUARDAR / EDITAR (POST normal)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') !== 'eliminar') {
    csrf_verify_or_die(); // verifica token

    // limpieza y validación básica
    $almacen_id     = clean_input($_POST['almacen_id'] ?? '');
    $trabajador_id  = clean_input($_POST['trabajador_id'] ?? '');
    $nombre         = clean_input($_POST['nombre'] ?? '');
    $ubicacion      = clean_input($_POST['ubicacion'] ?? '');
    $telefono       = clean_input($_POST['telefono'] ?? '');
    $stock          = clean_input($_POST['stock'] ?? '');
    $fecha_registro = clean_input($_POST['fecha_registro'] ?? '');
    $productos      = $_POST['productos'] ?? [];

    // validaciones mínimas
    if ($trabajador_id === '' || $nombre === '' || $ubicacion === '' || $telefono === '' || $stock === '' || $fecha_registro === '') {
        $alerta = "Por favor completa todos los campos obligatorios.";
        $tipo_alerta = "error";
    } else {
        if ($almacen_id !== '') {
            // actualizar
            $stmt = $conn->prepare("UPDATE almacenes SET trabajador_id=?, nombre=?, ubicacion=?, telefono=?, stock=?, fecha_registro=?, fecha_actualizacion=NOW() WHERE id=?");
            $stmt->bind_param("isssisi", $trabajador_id, $nombre, $ubicacion, $telefono, $stock, $fecha_registro, $almacen_id);
            if (!$stmt->execute()) {
                error_log("Update almacen failed: " . $stmt->error);
                $alerta = "Error al actualizar el almacén.";
                $tipo_alerta = "error";
            } else {
                $alerta = "Almacén actualizado correctamente";
                $tipo_alerta = "ok";
            }
            $stmt->close();

            // limpiar productos previos
            $stmt_del = $conn->prepare("DELETE FROM almacen_productos WHERE almacen_id=?");
            $stmt_del->bind_param("i", $almacen_id);
            $stmt_del->execute();
            $stmt_del->close();

            $insert_almacen_id = (int)$almacen_id;
        } else {
            // insertar
            $stmt = $conn->prepare("INSERT INTO almacenes (trabajador_id, nombre, ubicacion, telefono, stock, fecha_registro, fecha_actualizacion) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("isssis", $trabajador_id, $nombre, $ubicacion, $telefono, $stock, $fecha_registro);
            if (!$stmt->execute()) {
                error_log("Insert almacen failed: " . $stmt->error);
                $alerta = "Error al crear el almacén.";
                $tipo_alerta = "error";
            } else {
                $insert_almacen_id = $conn->insert_id;
                $alerta = "Almacén creado correctamente";
                $tipo_alerta = "ok";
            }
            $stmt->close();
        }

        // guardar productos asignados
        if (!empty($productos) && !empty($insert_almacen_id)) {
            $stmt_prod = $conn->prepare("INSERT INTO almacen_productos (almacen_id, producto_id) VALUES (?, ?)");
            foreach ($productos as $prod_id) {
                $prod_id = (int) clean_input($prod_id);
                if ($prod_id > 0) {
                    $stmt_prod->bind_param("ii", $insert_almacen_id, $prod_id);
                    $stmt_prod->execute();
                }
            }
            $stmt_prod->close();
        }
    }

    // redirigir para evitar reenvío de formulario
    $qs = '?';
    if ($alerta !== '') {
        $qs .= 'mensaje=' . urlencode($alerta) . '&tipo=' . urlencode($tipo_alerta);
    } else {
        $qs = '';
    }
    header("Location: admin-almacen.php" . $qs);
    exit;
}

// --------------------------------------
// CARGAR LISTADOS (trabajadores, productos, almacenes)
$trabajadores = [];
$res = $conn->query("SELECT id, CONCAT(nombres,' ',apellidos) AS nombre FROM trabajadores WHERE puesto='Trabajador de Almacén' AND estado='activo'");
while ($r = $res->fetch_assoc()) $trabajadores[] = $r;

$productos = [];
$res = $conn->query("SELECT id_producto, titulo FROM productos WHERE estado='activo'");
while ($r = $res->fetch_assoc()) $productos[] = $r;

$almacenes = [];
$sql = "
    SELECT a.*, t.nombres, t.apellidos,
        GROUP_CONCAT(p.titulo SEPARATOR ',') AS productos,
        GROUP_CONCAT(p.id_producto SEPARATOR ',') AS productos_ids
    FROM almacenes a
    LEFT JOIN trabajadores t ON a.trabajador_id = t.id
    LEFT JOIN almacen_productos ap ON a.id = ap.almacen_id
    LEFT JOIN productos p ON ap.producto_id = p.id_producto
    GROUP BY a.id
    ORDER BY a.id ASC
";
$res = $conn->query($sql);
while ($r = $res->fetch_assoc()) $almacenes[] = $r;

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Gestión de Almacén — MundoGamer</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<style>
/* --- mantengo tus estilos originales --- */
#loader{position:fixed;top:0;left:0;width:100%;height:100%;background:#0d0d0d;display:flex;justify-content:center;align-items:center;z-index:9999}
.spinner{border:6px solid rgba(0,255,255,0.1);border-left-color:#00f5ff;border-radius:50%;width:70px;height:70px;animation:spin 1s linear infinite,glow 1.5s infinite alternate}
@keyframes spin{100%{transform:rotate(360deg)}}@keyframes glow{from{box-shadow:0 0 10px #00f5ff}to{box-shadow:0 0 30px #00ffcc}}
body{background:linear-gradient(135deg,#0d0d0d,#1c1c1c,#111);color:#fff;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;min-height:100vh;padding:30px;opacity:0;transition:opacity .5s ease-in-out}
body.loaded{opacity:1}
h1{text-align:center;color:#00f5ff;text-shadow:0 0 10px rgba(0,245,255,.6);margin-bottom:30px;animation:glowText 2s infinite alternate}
@keyframes glowText{from{text-shadow:0 0 10px #00f5ff,0 0 20px #00f5ff}to{text-shadow:0 0 20px #00ffcc,0 0 40px #00ffcc}}
.form-control,.form-select{background:#1a1a1a;border:1px solid rgba(0,245,255,.4);color:#fff}
.form-control:focus,.form-select:focus{border-color:#00ffcc;box-shadow:0 0 10px rgba(0,245,255,.5)}
.btn-custom{background:linear-gradient(90deg,#00f5ff,#00ffcc);border:none;color:#000;font-weight:bold;transition:.3s}
.btn-custom:hover{background:linear-gradient(90deg,#00ffcc,#00f5ff);box-shadow:0 0 15px rgba(0,245,255,.7)}
.alerta{position:fixed;top:20px;right:20px;padding:15px 20px;border-radius:8px;font-weight:bold;display:none;z-index:2000}
.alerta.ok{background:#00ffcc;color:#000}.alerta.error{background:#ff4d4d;color:#fff}.alerta.info{background:#00f5ff;color:#000}
table{margin-top:25px;width:100%}
th,td{text-align:center;padding:12px;border-bottom:1px solid rgba(0,245,255,.3)}
th{background:rgba(0,245,255,.15)}tr:hover{background:rgba(0,245,255,.1)}
.volver-btn{margin-bottom:15px}
#listaProductos li{background:rgba(0,245,255,.1);margin:3px 0;padding:4px 8px;border-radius:6px;display:flex;justify-content:space-between;align-items:center}
#listaProductos li button{background:none;border:none;color:#ff4d4d;font-size:16px;cursor:pointer}
</style>
</head>
<body>
<div id="loader"><div class="spinner"></div></div>

<!-- alerta -->
<div id="alerta" class="alerta <?= esc_html($tipo_alerta) ?>"><?= esc_html($alerta) ?></div>

<div class="container">
<button class="btn btn-danger volver-btn" onclick="volverDashboard()"><i class="bi bi-arrow-left-circle"></i> Volver</button>
<h1><i class="bi bi-box-seam"></i> Gestión de Almacén</h1>

<form id="almacenForm" class="row g-3" method="POST" action="admin-almacen.php">
    <?php echo csrf_input_field(); /* campo CSRF oculto */ ?>
    <input type="hidden" name="almacen_id" id="almacen_id" value="">

    <div id="productosHidden"></div>

    <div class="col-md-6">
        <label class="form-label">Trabajador Asignado</label>
        <select class="form-select" name="trabajador_id" id="trabajador" required>
            <option value="">Seleccione trabajador</option>
            <?php foreach($trabajadores as $t): ?>
                <option value="<?= esc_html($t['id']) ?>"><?= esc_html($t['nombre']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">Nombre del Almacén</label>
        <input type="text" class="form-control" name="nombre" id="nombreAlmacen" required>
    </div>

    <div class="col-md-6">
        <label class="form-label">Ubicación</label>
        <input type="text" class="form-control" name="ubicacion" id="ubicacion" required>
    </div>

    <div class="col-md-6">
        <label class="form-label">Teléfono</label>
        <input type="tel" class="form-control" name="telefono" id="telefono" required>
    </div>

    <div class="col-md-6">
        <label class="form-label">Stock</label>
        <input type="number" class="form-control" name="stock" id="stock" required>
    </div>

    <div class="col-md-6">
        <label class="form-label">Productos</label>
        <div class="d-flex">
            <select class="form-select me-2" id="productoSelect">
                <option value="">Seleccione producto</option>
                <?php foreach($productos as $p): ?>
                    <option value="<?= esc_html($p['id_producto']) ?>"><?= esc_html($p['titulo']) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="button" class="btn btn-custom" onclick="agregarProducto()">➕</button>
        </div>
        <ul id="listaProductos" class="mt-2"></ul>
    </div>

    <div class="col-md-6">
        <label class="form-label">Fecha de Registro</label>
        <input type="date" class="form-control" name="fecha_registro" id="fechaRegistro" required>
    </div>

    <div class="col-12 text-center">
        <button type="submit" class="btn btn-custom">Guardar</button>
        <button type="button" id="btnCancelar" class="btn btn-secondary" onclick="cancelarAccion()">Cancelar</button>
    </div>
</form>

<div class="text-end mb-3">
    <a href="?export=1&csrf=<?= urlencode($_SESSION['csrf_token']) ?>" class="btn btn-success">
        <i class="bi bi-file-earmark-excel"></i> Exportar a Excel
    </a>
</div>

<table id="almacenTable" class="table table-dark table-striped mt-4">
<thead>
<tr>
<th>ID</th><th>Trabajador</th><th>Nombre</th><th>Ubicación</th><th>Teléfono</th><th>Stock</th><th>Productos</th><th>Registro</th><th>Actualización</th><th>Acciones</th>
</tr>
</thead>
<tbody>
<?php foreach($almacenes as $a): ?>
<tr data-productos="<?= esc_html($a['productos_ids'] ?? '') ?>" data-trabajador="<?= esc_html($a['trabajador_id'] ?? '') ?>">
<td><?= esc_html($a['id']) ?></td>
<td><?= esc_html(($a['nombres'] ?? '') . ' ' . ($a['apellidos'] ?? '')) ?></td>
<td><?= esc_html($a['nombre']) ?></td>
<td><?= esc_html($a['ubicacion']) ?></td>
<td><?= esc_html($a['telefono']) ?></td>
<td><?= esc_html($a['stock']) ?></td>
<td><?= esc_html($a['productos']) ?></td>
<td><?= esc_html($a['fecha_registro']) ?></td>
<td><?= esc_html($a['fecha_actualizacion']) ?></td>
<td>
    <button type="button" class="btn btn-warning btn-sm" onclick="editarFila(this)">Editar</button>
    <button type="button" class="btn btn-danger btn-sm" onclick="confirmarEliminar(<?= (int)$a['id'] ?>,'<?= addslashes(esc_html($a['nombre'])) ?>')">Eliminar</button>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>

<!-- Modal Confirmar Eliminación -->
<div class="modal fade" id="modalEliminar" tabindex="-1" aria-labelledby="modalEliminarLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content bg-dark text-white">
      <div class="modal-header">
        <h5 class="modal-title" id="modalEliminarLabel">Confirmar Eliminación</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        ¿Está seguro de que desea eliminar el almacén <span id="nombreAlmacenEliminar"></span>?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" id="btnConfirmarEliminar" class="btn btn-danger" onclick="eliminarAlmacen()">Eliminar</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const CSRF_TOKEN = <?= json_encode($_SESSION['csrf_token']) ?>;
let productosAsignados = [];
let eliminarId = null;
let modalEliminar = null;

window.addEventListener('load', ()=>{
    document.getElementById('loader').style.display='none';
    document.body.classList.add('loaded');
    modalEliminar = new bootstrap.Modal(document.getElementById('modalEliminar'));
    const alerta = document.getElementById('alerta');
    if(alerta.textContent.trim()!==''){
        alerta.style.display='block';
        setTimeout(()=>{ alerta.style.display='none'; }, 3000);
    }
});

function agregarProducto() {
    const select = document.getElementById('productoSelect');
    const val = select.value;
    const text = select.options[select.selectedIndex]?.text || '';
    if(val && !productosAsignados.some(p=>p.id==val)) {
        productosAsignados.push({id: val, nombre: text});
        renderListaProductos();
    }
}

function renderListaProductos() {
    const ul = document.getElementById('listaProductos');
    ul.innerHTML = '';
    productosAsignados.forEach((p,i)=>{
        const li = document.createElement('li');
        li.innerHTML = `${p.nombre} <button type="button" onclick="eliminarProducto(${i})">❌</button>`;
        ul.appendChild(li);
    });
}

function eliminarProducto(i){
    productosAsignados.splice(i,1);
    renderListaProductos();
}

function editarFila(btn){
    const tr = btn.closest('tr');
    document.getElementById('almacen_id').value = tr.cells[0].textContent.trim();
    document.getElementById('nombreAlmacen').value = tr.cells[2].textContent.trim();
    document.getElementById('ubicacion').value = tr.cells[3].textContent.trim();
    document.getElementById('telefono').value = tr.cells[4].textContent.trim();
    document.getElementById('stock').value = tr.cells[5].textContent.trim();
    document.getElementById('fechaRegistro').value = tr.cells[7].textContent.trim();
    document.getElementById('trabajador').value = tr.dataset.trabajador || '';

    const raw = tr.dataset.productos || '';
    const ids = raw.split(',').filter(p=>p);
    productosAsignados = [];
    ids.forEach(id=>{
        const option = document.querySelector(`#productoSelect option[value='${id}']`);
        if(option) productosAsignados.push({id:id, nombre:option.text});
    });
    renderListaProductos();
    window.scrollTo({top:0, behavior:'smooth'});
}

function cancelarAccion(){
    document.getElementById('almacenForm').reset();
    productosAsignados = [];
    renderListaProductos();
    mostrarAlerta("Acción cancelada", "info");
}

function volverDashboard(){
    window.location.href = 'admin-dashboard.php';
}

// antes de submit añadimos los inputs hidden de productos y el token ya está en el form
document.getElementById('almacenForm').addEventListener('submit', function(e){
    const container = document.getElementById('productosHidden');
    container.innerHTML = '';
    productosAsignados.forEach(p=>{
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'productos[]';
        input.value = p.id;
        container.appendChild(input);
    });
    mostrarAlerta("Guardado correctamente", "ok");
});

function mostrarAlerta(mensaje, tipo){
    const alerta = document.getElementById('alerta');
    alerta.textContent = mensaje;
    alerta.className = 'alerta ' + tipo;
    alerta.style.display = 'block';
    setTimeout(()=>{ alerta.style.display='none'; }, 3000);
}

// --- eliminación via AJAX con CSRF incluido ---
function confirmarEliminar(id, nombre){
    eliminarId = id;
    document.getElementById('nombreAlmacenEliminar').textContent = nombre;
    modalEliminar.show();
}

function eliminarAlmacen(){
    if(!eliminarId) return;
    fetch('admin-almacen.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `accion=eliminar&eliminar_id=${encodeURIComponent(eliminarId)}&csrf_token=${encodeURIComponent(CSRF_TOKEN)}`
    })
    .then(res => res.text())
    .then(respuesta => {
        if(respuesta.trim() === "ok"){
            document.querySelectorAll('#almacenTable tbody tr').forEach(tr=>{
                if(tr.cells[0].textContent == eliminarId){
                    tr.remove();
                }
            });
            mostrarAlerta("Almacén eliminado correctamente", "ok");
            eliminarId = null;
            modalEliminar.hide();
        } else {
            mostrarAlerta("Error al eliminar el almacén", "error");
        }
    })
    .catch(err => {
        console.error(err);
        mostrarAlerta("Error al eliminar el almacén", "error");
    });
}
</script>
</body>
</html>
