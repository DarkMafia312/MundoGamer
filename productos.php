<?php
// productos.php - integrado con security.php (CSRF, headers, sesiones seguras)

// ---------------------------
// Seguridad (session, headers, CSRF, helpers)
// ---------------------------
require_once __DIR__ . '/sentry.php';
require_once __DIR__ . '/security.php';
secure_bootstrap(); // inicia cabeceras y sesión segura

// ---------------------------
// Conexión y dependencias
// ---------------------------
include_once __DIR__ . '/conexion.php';
require __DIR__ . '/vendor/autoload.php'; // PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// ---------------------------
// EXPORTAR A EXCEL (GET) - requiere token CSRF en query para mayor seguridad
// ---------------------------
if (isset($_GET['export'])) {
    $token = $_GET['csrf'] ?? '';
    if (!$token || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        die("Acción no autorizada (CSRF).");
    }

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Productos');

    // ENCABEZADOS
    $sheet->setCellValue('A1', 'ID');
    $sheet->setCellValue('B1', 'Título');
    $sheet->setCellValue('C1', 'Género');
    $sheet->setCellValue('D1', 'Proveedor');
    $sheet->setCellValue('E1', 'Descripción');
    $sheet->setCellValue('F1', 'Precio');
    $sheet->setCellValue('G1', 'Plataforma');
    $sheet->setCellValue('H1', 'Fecha Lanzamiento');
    $sheet->setCellValue('I1', 'Rating');
    $sheet->setCellValue('J1', 'Estado');
    $sheet->setCellValue('K1', 'VIP');
    $sheet->setCellValue('L1', 'Fecha Creación');

    $query = $conn->query("
        SELECT p.*, pr.empresa AS proveedor
        FROM productos p
        LEFT JOIN proveedores pr ON p.id_proveedor = pr.id
        ORDER BY p.id_producto ASC
    ");

    $fila = 2;
    while ($row = $query->fetch_assoc()) {
        $sheet->setCellValue("A{$fila}", $row['id_producto']);
        $sheet->setCellValue("B{$fila}", $row['titulo']);
        $sheet->setCellValue("C{$fila}", $row['genero']);
        $sheet->setCellValue("D{$fila}", $row['proveedor']);
        $sheet->setCellValue("E{$fila}", $row['descripcion']);
        $sheet->setCellValue("F{$fila}", $row['precio']);
        $sheet->setCellValue("G{$fila}", $row['plataforma']);
        $sheet->setCellValue("H{$fila}", $row['fecha_lanzamiento']);
        $sheet->setCellValue("I{$fila}", $row['rating_promedio']);
        $sheet->setCellValue("J{$fila}", $row['estado']);
        $sheet->setCellValue("K{$fila}", $row['vip'] == 1 ? 'Sí' : 'No');
        $sheet->setCellValue("L{$fila}", $row['fecha_creacion']);
        $fila++;
    }

    foreach (range('A', 'L') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    $filename = "productos_MundoGamer.xlsx";
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment; filename=\"$filename\"");
    $writer = new Xlsx($spreadsheet);
    $writer->save("php://output");
    exit;
}
// ---------------------------
// AJAX HANDLER (POST)
// ---------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json; charset=utf-8');
    $action = $_POST['action'];

    try {
        switch ($action) {

            // LISTAR PROVEEDORES (no requiere CSRF - sólo lectura)
            case 'listProveedores':
                $res = $conn->query("SELECT id, empresa FROM proveedores ORDER BY id ASC");
                $proveedores = [];
                while ($row = $res->fetch_assoc()) {
                    $proveedores[] = $row;
                }
                echo json_encode($proveedores);
                break;

            // LISTAR PRODUCTOS (no requiere CSRF - sólo lectura)
            case 'listProductos':
                $res = $conn->query("
                    SELECT p.*, pr.empresa AS nombre_proveedor
                    FROM productos p
                    LEFT JOIN proveedores pr ON p.id_proveedor = pr.id
                    ORDER BY p.id_producto ASC
                ");
                $productos = [];
                while ($row = $res->fetch_assoc()) {
                    $productos[] = $row;
                }
                echo json_encode($productos);
                break;

            // AGREGAR PRODUCTO (mutación -> exigir CSRF)
            case 'add':
                csrf_verify_or_die();

                // sanitizar entradas
                $titulo = clean_input($_POST['titulo'] ?? '');
                $genero = clean_input($_POST['genero'] ?? '');
                $id_proveedor = (int)($_POST['id_proveedor'] ?? 0);
                $descripcion = clean_input($_POST['descripcion'] ?? '');
                $precio = is_numeric($_POST['precio'] ?? null) ? (float)$_POST['precio'] : 0.0;
                $plataforma = clean_input($_POST['plataforma'] ?? '');
                $fecha_lanzamiento = clean_input($_POST['fecha_lanzamiento'] ?? null);
                $rating_promedio = is_numeric($_POST['rating_promedio'] ?? null) ? (float)$_POST['rating_promedio'] : 0.0;
                $imagen = clean_input($_POST['imagen'] ?? '');
                $estado = clean_input($_POST['estado'] ?? 'activo');
                $vip = isset($_POST['vip']) && (int)$_POST['vip'] === 1 ? 1 : 0;

                $stmt = $conn->prepare("
                    INSERT INTO productos
                    (titulo, genero, id_proveedor, descripcion, precio, plataforma, fecha_lanzamiento, rating_promedio, imagen, estado, vip, fecha_creacion)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                if (!$stmt) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }

                // types: s s i s d s s d s s i  => 'ssisdssdssi'
                $types = 'ssisdssdssi';
                $stmt->bind_param(
                    $types,
                    $titulo,
                    $genero,
                    $id_proveedor,
                    $descripcion,
                    $precio,
                    $plataforma,
                    $fecha_lanzamiento,
                    $rating_promedio,
                    $imagen,
                    $estado,
                    $vip
                );

                if (!$stmt->execute()) {
                    throw new Exception("Execute failed: " . $stmt->error);
                }
                $stmt->close();

                echo json_encode(['status' => 'success']);
                break;

            // EDITAR PRODUCTO (mutación -> exigir CSRF)
            case 'edit':
                csrf_verify_or_die();

                // sanitizar
                $id_producto = (int)($_POST['id_producto'] ?? 0);
                if ($id_producto <= 0) {
                    throw new Exception("ID inválido");
                }
                $titulo = clean_input($_POST['titulo'] ?? '');
                $genero = clean_input($_POST['genero'] ?? '');
                $id_proveedor = (int)($_POST['id_proveedor'] ?? 0);
                $descripcion = clean_input($_POST['descripcion'] ?? '');
                $precio = is_numeric($_POST['precio'] ?? null) ? (float)$_POST['precio'] : 0.0;
                $plataforma = clean_input($_POST['plataforma'] ?? '');
                $fecha_lanzamiento = clean_input($_POST['fecha_lanzamiento'] ?? null);
                $rating_promedio = is_numeric($_POST['rating_promedio'] ?? null) ? (float)$_POST['rating_promedio'] : 0.0;
                $imagen = clean_input($_POST['imagen'] ?? '');
                $estado = clean_input($_POST['estado'] ?? 'activo');
                $vip = isset($_POST['vip']) && (int)$_POST['vip'] === 1 ? 1 : 0;

                $stmt = $conn->prepare("
                    UPDATE productos SET
                        titulo=?, genero=?, id_proveedor=?,
                        descripcion=?, precio=?, plataforma=?,
                        fecha_lanzamiento=?, rating_promedio=?,
                        imagen=?, estado=?, vip=?
                    WHERE id_producto=?
                ");
                if (!$stmt) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }

                // types: s s i s d s s d s s i i  => 'ssisdssdssii'
                $types = 'ssisdssdssii';
                $stmt->bind_param(
                    $types,
                    $titulo,
                    $genero,
                    $id_proveedor,
                    $descripcion,
                    $precio,
                    $plataforma,
                    $fecha_lanzamiento,
                    $rating_promedio,
                    $imagen,
                    $estado,
                    $vip,
                    $id_producto
                );

                if (!$stmt->execute()) {
                    throw new Exception("Execute failed: " . $stmt->error);
                }
                $stmt->close();

                echo json_encode(['status' => 'success']);
                break;

            // ELIMINAR PRODUCTO (mutación -> exigir CSRF)
            case 'delete':
                csrf_verify_or_die();

                $id_producto = (int)($_POST['id_producto'] ?? 0);
                if ($id_producto <= 0) {
                    throw new Exception("ID inválido");
                }

                $stmt = $conn->prepare("DELETE FROM productos WHERE id_producto=?");
                if (!$stmt) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }
                $stmt->bind_param("i", $id_producto);
                if (!$stmt->execute()) {
                    throw new Exception("Execute failed: " . $stmt->error);
                }
                $stmt->close();

                echo json_encode(['status' => 'success']);
                break;

            default:
                echo json_encode(['status' => 'error', 'message' => 'Acción no válida']);
                break;
        }
    } catch (Exception $e) {
        // log en servidor, enviar mensaje general al cliente
        error_log("productos.php AJAX error: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Ocurrió un error.']);
    }
    exit;
}

// ---------------------------
// Si llegamos aquí: render HTML
// ---------------------------
$csrf_token_for_js = htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Gestión de Productos</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
/* Mantén tus estilos originales — los dejé intactos para que no cambie el diseño */
body {
  background: linear-gradient(135deg, #2c003e, #0d1b2a);
  color: #fff;
  font-family: 'Segoe UI', sans-serif;
  min-height: 100vh;
  margin: 0;
  padding: 0;
}
h2 {
  text-align: center;
  margin: 20px 0;
  font-weight: bold;
  text-transform: uppercase;
  color: #ff4d6d;
  letter-spacing: 1.5px;
}
.card {
  background: rgba(76,0,123,0.7);
  border: 1px solid #e43f5a;
  border-radius: 15px;
  box-shadow: 0 0 20px rgba(228,63,90,0.5);
}
.table th { background-color: rgba(228,63,90,0.4); }
.btn-custom { background-color: #e43f5a; color: #fff; border: none; }
.btn-custom:hover { background-color: #ff4d6d; }
.btn-secondary { background-color: #0d1b2a; border: none; }
label.form-label, .form-check-label { color: #fff; }
input::placeholder, textarea::placeholder { color: #fff; }
select option { color: #000; }
input, textarea, select { color: #000; background-color: rgba(255,255,255,0.9); border: 1px solid #e43f5a; border-radius: 5px; padding: 4px 8px; }
#loader { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: #0d1b2a; display: flex; align-items: center; justify-content: center; z-index: 9999; }
.loader-spinner { border: 8px solid #1a273d; border-top: 8px solid #e43f5a; border-radius: 50%; width: 80px; height: 80px; animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
.fade-in { animation: fadeIn 0.8s ease-in-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>
</head>
<body>
<div id="loader"><div class="loader-spinner"></div></div>

<div class="container py-4 fade-in" id="mainContent" style="display:none;">
  <h2><i class="fa-solid fa-box-open"></i> Gestión de Productos</h2>

  <div class="text-start mb-3">
    <button type="button" class="btn btn-secondary" onclick="window.location.href='admin-dashboard.php'">
      <i class="fa-solid fa-arrow-left"></i> Volver
    </button>
  </div>

  <div class="card p-4 mb-4">
    <!-- Formulario (el CSRF se maneja en JS para AJAX) -->
    <form id="productForm" autocomplete="off">
      <div class="row g-3">
        <div class="col-md-6"><label class="form-label">Título</label><input type="text" id="titulo" class="form-control" required></div>
        <div class="col-md-3"><label class="form-label">Género</label><input type="text" id="genero" class="form-control" required></div>
        <div class="col-md-3"><label class="form-label">Proveedor</label>
          <select id="id_proveedor" class="form-select" required><option value="">Cargando...</option></select>
        </div>
        <div class="col-md-12"><label class="form-label">Descripción</label><textarea id="descripcion" class="form-control" rows="2" required></textarea></div>
        <div class="col-md-3"><label class="form-label">Precio ($)</label><input type="number" id="precio" class="form-control" step="0.01" min="0" required></div>
        <div class="col-md-3"><label class="form-label">Plataforma</label><input type="text" id="plataforma" class="form-control" required></div>
        <div class="col-md-3"><label class="form-label">Fecha de Lanzamiento</label><input type="date" id="fecha_lanzamiento" class="form-control" required></div>
        <div class="col-md-3"><label class="form-label">Rating</label><input type="number" id="rating_promedio" class="form-control" step="0.1" min="0" max="5" required></div>
        <div class="col-md-6"><label class="form-label">URL Imagen</label><input type="url" id="imagen" class="form-control"></div>
        <div class="col-md-1 d-flex align-items-center"><div class="form-check mt-2"><input class="form-check-input" type="checkbox" id="vip"><label class="form-check-label" for="vip">VIP</label></div></div>
        <div class="col-md-2"><label class="form-label">Estado</label><select id="estado" class="form-select" required><option value="activo">Activo</option><option value="descontinuado">Descontinuado</option></select></div>
      </div>
      <div class="mt-3 text-center">
        <button type="submit" class="btn btn-custom px-4"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
        <button type="reset" class="btn btn-secondary px-4"><i class="fa-solid fa-eraser"></i> Limpiar</button>
        <button type="button" id="cancelBtn" class="btn btn-warning px-4" style="display:none;"><i class="fa-solid fa-xmark"></i> Cancelar</button>
      </div>
    </form>
  </div>

<div class="text-end mb-3">
  <a href="productos.php?export=1&csrf=<?= urlencode($csrf_token_for_js) ?>" class="btn btn-success">
    <i class="fa-solid fa-file-excel"></i> Exportar a Excel
  </a>
</div>
  
  <div class="table-responsive fade-in">
    <table class="table table-dark table-hover align-middle">
      <thead>
        <tr>
          <th>ID</th><th>Imagen</th><th>Título</th><th>Género</th><th>Proveedor</th><th>Precio</th>
          <th>Plataforma</th><th>Fecha</th><th>Rating</th><th>Estado</th><th>VIP</th><th>Acciones</th>
        </tr>
      </thead>
      <tbody id="productList"></tbody>
    </table>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// CSRF token proporcionado por PHP
const CSRF_TOKEN = '<?= $csrf_token_for_js ?>';

const loader=document.getElementById('loader'), mainContent=document.getElementById('mainContent');
const form=document.getElementById('productForm'), cancelBtn=document.getElementById('cancelBtn'), productList=document.getElementById('productList');
let editIndex=null;

function ajaxPromise(data){
  return new Promise((resolve,reject)=>{
    // adjuntar CSRF siempre
    data.csrf_token = CSRF_TOKEN;
    const xhr=new XMLHttpRequest();
    xhr.open('POST','productos.php',true);
    xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
    xhr.onload=()=>{
      try{
        resolve(JSON.parse(xhr.responseText));
      }catch(e){
        reject(new Error('Respuesta inválida: '+xhr.responseText));
      }
    };
    xhr.onerror=()=>reject(new Error('Error de red'));
    xhr.send(Object.keys(data).map(k=>encodeURIComponent(k)+'='+encodeURIComponent(data[k])).join('&'));
  });
}

async function loadProveedores(){
  const sel=document.getElementById('id_proveedor');
  try{
    const res = await ajaxPromise({action:'listProveedores'});
    sel.innerHTML='<option value="">Seleccione</option>';
    res.forEach(p=>{ sel.innerHTML += `<option value="${p.id}">${p.empresa}</option>`; });
  }catch(e){
    console.error(e);
    sel.innerHTML='<option value="">Error cargando</option>';
  }
}

function escapeHtml(unsafe) {
  if (unsafe === null || unsafe === undefined) return '';
  return String(unsafe).replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#039;");
}

function loadProductos(){
  ajaxPromise({action:'listProductos'}).then(res=>{
    const tbody=document.getElementById('productList');tbody.innerHTML='';
    res.forEach(p=>{
      tbody.innerHTML+=`
      <tr>
        <td>${p.id_producto}</td>
        <td><img src="${escapeHtml(p.imagen||'https://via.placeholder.com/60x40')}" width="60" class="rounded"></td>
        <td>${escapeHtml(p.titulo)} ${p.vip==1?'<i class="fa-solid fa-crown text-warning"></i>':''}</td>
        <td>${escapeHtml(p.genero)}</td>
        <td>${escapeHtml(p.nombre_proveedor||'-')}</td>
        <td>$${parseFloat(p.precio).toFixed(2)}</td>
        <td>${escapeHtml(p.plataforma)}</td>
        <td>${escapeHtml(p.fecha_lanzamiento)}</td>
        <td>${escapeHtml(p.rating_promedio)}</td>
        <td class="${p.estado==='activo'?'text-success':'text-danger'}">${escapeHtml(p.estado)}</td>
        <td>${p.vip==1?'Sí':'No'}</td>
        <td>
          <button class="btn btn-warning btn-sm" onclick="editProd(${p.id_producto})"><i class="fa-solid fa-pen"></i></button>
          <button class="btn btn-danger btn-sm" onclick="deleteProd(${p.id_producto})"><i class="fa-solid fa-trash"></i></button>
        </td>
      </tr>`;
    });
  }).catch(err=>{ console.error(err); alert('Error cargando productos'); });
}

form.addEventListener('submit',e=>{
  e.preventDefault();
  const d = {
    titulo: document.getElementById('titulo').value,
    genero: document.getElementById('genero').value,
    id_proveedor: document.getElementById('id_proveedor').value,
    descripcion: document.getElementById('descripcion').value,
    precio: document.getElementById('precio').value,
    plataforma: document.getElementById('plataforma').value,
    fecha_lanzamiento: document.getElementById('fecha_lanzamiento').value,
    rating_promedio: document.getElementById('rating_promedio').value,
    imagen: document.getElementById('imagen').value,
    estado: document.getElementById('estado').value,
    vip: document.getElementById('vip').checked?1:0
  };
  if(editIndex){ d.action='edit'; d.id_producto = editIndex; } else { d.action='add'; }

  ajaxPromise(d).then(r=>{
    if(r && r.status === 'success'){
      loadProductos();
      form.reset();
      editIndex=null;
      cancelBtn.style.display='none';
    } else {
      console.error(r);
      alert('Error: ' + (r.message || 'Operación fallida'));
    }
  }).catch(err=>{
    console.error(err);
    alert('Error de red o respuesta inválida');
  });
});

function editProd(id){
  // cargamos lista y buscamos el producto (podríamos implementar endpoint específico si lo prefieres)
  ajaxPromise({action:'listProductos'}).then(res=>{
    const p = res.find(x=>x.id_producto == id);
    if(p){
      document.getElementById('titulo').value = p.titulo;
      document.getElementById('genero').value = p.genero;
      document.getElementById('id_proveedor').value = p.id_proveedor;
      document.getElementById('descripcion').value = p.descripcion;
      document.getElementById('precio').value = p.precio;
      document.getElementById('plataforma').value = p.plataforma;
      document.getElementById('fecha_lanzamiento').value = p.fecha_lanzamiento;
      document.getElementById('rating_promedio').value = p.rating_promedio;
      document.getElementById('imagen').value = p.imagen;
      document.getElementById('estado').value = p.estado;
      document.getElementById('vip').checked = p.vip == 1;
      editIndex = id;
      cancelBtn.style.display = 'inline-block';
      window.scrollTo({ top: 0, behavior: 'smooth' });
    } else {
      alert('Producto no encontrado');
    }
  });
}

function deleteProd(id){
  if(confirm('¿Eliminar producto?')){
    ajaxPromise({action:'delete', id_producto: id}).then(r=>{
      if(r && r.status === 'success') loadProductos();
      else alert('No se pudo eliminar');
    }).catch(err=>{ console.error(err); alert('Error al eliminar'); });
  }
}

cancelBtn.onclick = ()=>{ form.reset(); editIndex = null; cancelBtn.style.display = 'none'; };

window.onload = async ()=>{ loader.style.display = 'none'; mainContent.style.display = 'block'; await loadProveedores(); loadProductos(); };
</script>
</body>
</html>
