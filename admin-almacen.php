<?php
// admin-almacen.php
include 'conexion.php';

require __DIR__ . '/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// === EXPORTAR A EXCEL ===
if (isset($_GET['export'])) {
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

    // Datos
    $sql = "
        SELECT a.*, CONCAT(t.nombres, ' ', t.apellidos) AS trabajador,
               GROUP_CONCAT(p.titulo SEPARATOR ', ') AS productos
        FROM almacenes a
        JOIN trabajadores t ON a.trabajador_id = t.id
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

    // Autoajustar columnas
    foreach (range('A', 'I') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // Descargar el archivo
    $filename = "almacenes_MundoGamer.xlsx";
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment; filename=\"$filename\"");
    $writer = new Xlsx($spreadsheet);
    $writer->save("php://output");
    exit;
}

// --------------------------------------
// Variables de alerta desde POST o GET
$alerta = $_GET['mensaje'] ?? '';
$tipo_alerta = $_GET['tipo'] ?? 'info';

// --------------------------------------
// Manejar eliminación vía AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'eliminar') {
    $id = (int)($_POST['eliminar_id'] ?? 0);
    if ($id) {
        // Eliminar productos asignados
        $stmt_del = $conn->prepare("DELETE FROM almacen_productos WHERE almacen_id=?");
        $stmt_del->bind_param("i", $id);
        $stmt_del->execute();
        $stmt_del->close();

        // Eliminar almacén
        $stmt = $conn->prepare("DELETE FROM almacenes WHERE id=?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo "ok";
        } else {
            http_response_code(500);
            echo "error";
        }
        $stmt->close();
        exit; // Evita enviar el resto del HTML
    } else {
        http_response_code(400);
        echo "error";
        exit;
    }
}

// --------------------------------------
// Procesar formulario de almacén
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') !== 'eliminar') {
    $almacen_id = $_POST['almacen_id'] ?? null;
    $trabajador_id = $_POST['trabajador_id'];
    $nombre = $_POST['nombre'];
    $ubicacion = $_POST['ubicacion'];
    $telefono = $_POST['telefono'];
    $stock = $_POST['stock'];
    $fecha_registro = $_POST['fecha_registro'];
    $productos = $_POST['productos'] ?? [];

    if ($almacen_id) {
        // Editar almacén
        $stmt = $conn->prepare("UPDATE almacenes SET trabajador_id=?, nombre=?, ubicacion=?, telefono=?, stock=?, fecha_registro=?, fecha_actualizacion=NOW() WHERE id=?");
        $stmt->bind_param("isssisi", $trabajador_id, $nombre, $ubicacion, $telefono, $stock, $fecha_registro, $almacen_id);
        $stmt->execute();
        $stmt->close();

        // Limpiar productos anteriores
        $stmt_del = $conn->prepare("DELETE FROM almacen_productos WHERE almacen_id=?");
        $stmt_del->bind_param("i", $almacen_id);
        $stmt_del->execute();
        $stmt_del->close();

        $alerta = "Almacén actualizado correctamente";
        $tipo_alerta = "ok";

    } else {
        // Nuevo almacén
        $stmt = $conn->prepare("INSERT INTO almacenes (trabajador_id, nombre, ubicacion, telefono, stock, fecha_registro, fecha_actualizacion) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("isssis", $trabajador_id, $nombre, $ubicacion, $telefono, $stock, $fecha_registro);
        $stmt->execute();
        $almacen_id = $conn->insert_id;
        $stmt->close();

        $alerta = "Almacén creado correctamente";
        $tipo_alerta = "ok";
    }

    // Guardar productos asignados
    if (!empty($productos)) {
        $stmt_prod = $conn->prepare("INSERT INTO almacen_productos (almacen_id, producto_id) VALUES (?, ?)");
        foreach ($productos as $prod_id) {
            $stmt_prod->bind_param("ii", $almacen_id, $prod_id);
            $stmt_prod->execute();
        }
        $stmt_prod->close();
    }
}

// --------------------------------------
// Cargar trabajadores y productos
$trabajadores = [];
$result = $conn->query("SELECT id, CONCAT(nombres,' ',apellidos) AS nombre FROM trabajadores WHERE puesto='Trabajador de Almacén' AND estado='activo'");
while ($row = $result->fetch_assoc()) {
    $trabajadores[] = $row;
}

$productos = [];
$result = $conn->query("SELECT id_producto, titulo FROM productos WHERE estado='activo'");
while ($row = $result->fetch_assoc()) {
    $productos[] = $row;
}

// --------------------------------------
// Cargar almacenes con productos asignados
$almacenes = [];
$sql = "
    SELECT a.*, t.nombres, t.apellidos,
        GROUP_CONCAT(p.titulo SEPARATOR ',') AS productos,
        GROUP_CONCAT(p.id_producto SEPARATOR ',') AS productos_ids
    FROM almacenes a
    JOIN trabajadores t ON a.trabajador_id = t.id
    LEFT JOIN almacen_productos ap ON a.id = ap.almacen_id
    LEFT JOIN productos p ON ap.producto_id = p.id_producto
    GROUP BY a.id
";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $almacenes[] = $row;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gestión de Almacén</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<style>
#loader{position:fixed;top:0;left:0;width:100%;height:100%;background:#0d0d0d;display:flex;justify-content:center;align-items:center;z-index:9999}
.spinner{border:6px solid rgba(0,255,255,0.1);border-left-color:#00f5ff;border-radius:50%;width:70px;height:70px;animation:spin 1s linear infinite,glow 1.5s infinite alternate}
@keyframes spin{100%{transform:rotate(360deg)}}
@keyframes glow{from{box-shadow:0 0 10px #00f5ff}to{box-shadow:0 0 30px #00ffcc}}
body{background:linear-gradient(135deg,#0d0d0d,#1c1c1c,#111);color:#fff;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;min-height:100vh;padding:30px;opacity:0;transition:opacity .5s ease-in-out}
body.loaded{opacity:1}
h1{text-align:center;color:#00f5ff;text-shadow:0 0 10px rgba(0,245,255,.6);margin-bottom:30px;animation:glowText 2s infinite alternate}
@keyframes glowText{from{text-shadow:0 0 10px #00f5ff,0 0 20px #00f5ff}to{text-shadow:0 0 20px #00ffcc,0 0 40px #00ffcc}}
.form-control,.form-select{background:#1a1a1a;border:1px solid rgba(0,245,255,.4);color:#fff}
.form-control:focus,.form-select:focus{border-color:#00ffcc;box-shadow:0 0 10px rgba(0,245,255,.5)}
.btn-custom{background:linear-gradient(90deg,#00f5ff,#00ffcc);border:none;color:#000;font-weight:bold;transition:.3s}
.btn-custom:hover{background:linear-gradient(90deg,#00ffcc,#00f5ff);box-shadow:0 0 15px rgba(0,245,255,.7)}
.alerta{position:fixed;top:20px;right:20px;padding:15px 20px;border-radius:8px;font-weight:bold;display:none;z-index:2000}
.alerta.ok{background:#00ffcc;color:#000}
.alerta.error{background:#ff4d4d;color:#fff}
.alerta.info{background:#00f5ff;color:#000}
table{margin-top:25px;width:100%}
th,td{text-align:center;padding:12px;border-bottom:1px solid rgba(0,245,255,.3)}
th{background:rgba(0,245,255,.15)}
tr:hover{background:rgba(0,245,255,.1)}
.volver-btn{margin-bottom:15px}
#listaProductos li{background:rgba(0,245,255,.1);margin:3px 0;padding:4px 8px;border-radius:6px;display:flex;justify-content:space-between;align-items:center}
#listaProductos li button{background:none;border:none;color:#ff4d4d;font-size:16px;cursor:pointer}
</style>
</head>
<body>
<div id="loader"><div class="spinner"></div></div>
<div id="alerta" class="alerta <?= $tipo_alerta ?>"><?= $alerta ?></div>

<div class="container">
<button class="btn btn-danger volver-btn" onclick="volverDashboard()"><i class="bi bi-arrow-left-circle"></i> Volver</button>
<h1><i class="bi bi-box-seam"></i> Gestión de Almacén</h1>

<form id="almacenForm" class="row g-3" method="POST">
<input type="hidden" name="almacen_id" id="almacen_id">
<div id="productosHidden"></div>

<div class="col-md-6">
<label class="form-label">Trabajador Asignado</label>
<select class="form-select" name="trabajador_id" id="trabajador" required>
<option value="">Seleccione trabajador</option>
<?php foreach($trabajadores as $t): ?>
<option value="<?= $t['id'] ?>"><?= $t['nombre'] ?></option>
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
<option value="<?= $p['id_producto'] ?>"><?= $p['titulo'] ?></option>
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
    <a href="?export=1" class="btn btn-success">
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
<tr data-productos="<?= $a['productos_ids'] ?>" data-trabajador="<?= $a['trabajador_id'] ?>">
<td><?= $a['id'] ?></td>
<td><?= $a['nombres'].' '.$a['apellidos'] ?></td>
<td><?= $a['nombre'] ?></td>
<td><?= $a['ubicacion'] ?></td>
<td><?= $a['telefono'] ?></td>
<td><?= $a['stock'] ?></td>
<td><?= $a['productos'] ?></td>
<td><?= $a['fecha_registro'] ?></td>
<td><?= $a['fecha_actualizacion'] ?></td>
<td>
<button type="button" class="btn btn-warning btn-sm" onclick="editarFila(this)">Editar</button>
<button type="button" class="btn btn-danger btn-sm" onclick="confirmarEliminar(<?= $a['id'] ?>,'<?= addslashes($a['nombre']) ?>')">Eliminar</button>
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
    const text = select.options[select.selectedIndex].text;
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
    document.getElementById('almacen_id').value = tr.cells[0].textContent;
    document.getElementById('nombreAlmacen').value = tr.cells[2].textContent;
    document.getElementById('ubicacion').value = tr.cells[3].textContent;
    document.getElementById('telefono').value = tr.cells[4].textContent;
    document.getElementById('stock').value = tr.cells[5].textContent;
    document.getElementById('fechaRegistro').value = tr.cells[7].textContent;
    document.getElementById('trabajador').value = tr.dataset.trabajador;

    const ids = tr.dataset.productos.split(',').filter(p=>p);
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

document.getElementById('almacenForm').addEventListener('submit', function(){
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

// --------- Modal eliminación con AJAX ------------
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
        body: `accion=eliminar&eliminar_id=${encodeURIComponent(eliminarId)}`
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
</body>admin-almacen
</html>