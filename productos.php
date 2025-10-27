<?php
include('conexion.php');
require __DIR__ . '/vendor/autoload.php'; // Carga Composer una sola vez al inicio

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// ==================== EXPORTAR A EXCEL ====================
if (isset($_GET['export'])) {
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

// ==================== AJAX HANDLER ====================
if(isset($_POST['action'])){
    $action = $_POST['action'];
    header('Content-Type: application/json');

    try{
        switch($action){

            // LISTAR PROVEEDORES
            case 'listProveedores':
                $res = $conn->query("SELECT id, empresa FROM proveedores ORDER BY id ASC");
                $proveedores = [];
                while($row = $res->fetch_assoc()){
                    $proveedores[] = $row;
                }
                echo json_encode($proveedores);
                break;

            // LISTAR PRODUCTOS
            case 'listProductos':
                $res = $conn->query("
                    SELECT p.*, pr.empresa AS nombre_proveedor
                    FROM productos p
                    LEFT JOIN proveedores pr ON p.id_proveedor = pr.id
                    ORDER BY p.id_producto ASC
                ");
                $productos = [];
                while($row = $res->fetch_assoc()){
                    $productos[] = $row;
                }
                echo json_encode($productos);
                break;

            // AGREGAR PRODUCTO
            case 'add':
                $stmt = $conn->prepare("
                    INSERT INTO productos
                    (titulo, genero, id_proveedor, descripcion, precio, plataforma, fecha_lanzamiento, rating_promedio, imagen, estado, vip, fecha_creacion)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                $vip = isset($_POST['vip']) && $_POST['vip'] == '1' ? 1 : 0;
                $stmt->bind_param(
                    "ssisdssdssi",
                    $_POST['titulo'],
                    $_POST['genero'],
                    $_POST['id_proveedor'],
                    $_POST['descripcion'],
                    $_POST['precio'],
                    $_POST['plataforma'],
                    $_POST['fecha_lanzamiento'],
                    $_POST['rating_promedio'],
                    $_POST['imagen'],
                    $_POST['estado'],
                    $vip
                );
                $stmt->execute();
                echo json_encode(['status'=>'success']);
                break;

            // EDITAR PRODUCTO
            case 'edit':
                $vip = isset($_POST['vip']) && $_POST['vip'] == '1' ? 1 : 0;

                $stmt = $conn->prepare("
                    UPDATE productos SET
                        titulo=?, genero=?, id_proveedor=?,
                        descripcion=?, precio=?, plataforma=?,
                        fecha_lanzamiento=?, rating_promedio=?,
                        imagen=?, estado=?, vip=?
                    WHERE id_producto=?
                ");
                $stmt->bind_param(
                    "ssisdssdssii",
                    $_POST['titulo'],
                    $_POST['genero'],
                    $_POST['id_proveedor'],
                    $_POST['descripcion'],
                    $_POST['precio'],
                    $_POST['plataforma'],
                    $_POST['fecha_lanzamiento'],
                    $_POST['rating_promedio'],
                    $_POST['imagen'],
                    $_POST['estado'],
                    $vip,
                    $_POST['id_producto']
                );
                $stmt->execute();
                echo json_encode(['status'=>'success']);
                break;

            // ELIMINAR PRODUCTO
            case 'delete':
                $stmt = $conn->prepare("DELETE FROM productos WHERE id_producto=?");
                $stmt->bind_param("i", $_POST['id_producto']);
                $stmt->execute();
                echo json_encode(['status'=>'success']);
                break;

            default:
                echo json_encode(['status'=>'error','message'=>'Acción no válida']);
        }
    }catch(Exception $e){
        echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gestión de Productos</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<style>
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
.table th {
  background-color: rgba(228,63,90,0.4);
}
.btn-custom {
  background-color: #e43f5a;
  color: #fff;
  border: none;
}
.btn-custom:hover {
  background-color: #ff4d6d;
}
.btn-secondary {
  background-color: #0d1b2a;
  border: none;
}
.estado-activo { color: #4caf50; font-weight: bold; }
.estado-descontinuado { color: #f44336; font-weight: bold; }
.vip-icon { color: gold; margin-left: 6px; font-size: 1rem; vertical-align: middle; }

/* Labels y checkbox label */
label.form-label,
.form-check-label {
  color: #fff; /* blanco */
}

/* Placeholders de inputs y textarea */
input::placeholder,
textarea::placeholder {
  color: #fff; /* blanco */
}

/* Select opciones */
select option {
  color: #000; /* negro para opciones */
}

/* Texto que escribe el usuario */
input,
textarea,
select {
  color: #000; /* negro para lo que se escribe */
  background-color: rgba(255,255,255,0.9); /* opcional: fondo claro para inputs */
  border: 1px solid #e43f5a;
  border-radius: 5px;
  padding: 4px 8px;
}

/* Pantalla de carga */
#loader {
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: #0d1b2a;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}
.loader-spinner {
  border: 8px solid #1a273d;
  border-top: 8px solid #e43f5a;
  border-radius: 50%;
  width: 80px; height: 80px;
  animation: spin 1s linear infinite;
}
@keyframes spin {
  to { transform: rotate(360deg); }
}
.fade-in {
  animation: fadeIn 0.8s ease-in-out;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
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
  <a href="productos.php?export=1" class="btn btn-success">
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
const loader=document.getElementById('loader'),mainContent=document.getElementById('mainContent');
const form=document.getElementById('productForm'),cancelBtn=document.getElementById('cancelBtn'),productList=document.getElementById('productList');
let editIndex=null;

function ajaxPromise(data){
  return new Promise((resolve,reject)=>{
    const xhr=new XMLHttpRequest();
    xhr.open('POST','productos.php',true);
    xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
    xhr.onload=()=>{try{resolve(JSON.parse(xhr.responseText));}catch(e){reject(e);} };
    xhr.onerror=()=>reject(new Error('Error de red'));
    xhr.send(Object.keys(data).map(k=>encodeURIComponent(k)+'='+encodeURIComponent(data[k])).join('&'));
  });
}

async function loadProveedores(){
  const sel=document.getElementById('id_proveedor');
  const res=await ajaxPromise({action:'listProveedores'});
  sel.innerHTML='<option value="">Seleccione</option>';
  res.forEach(p=>{sel.innerHTML+=`<option value="${p.id}">${p.empresa}</option>`});
}

function loadProductos(){
  ajaxPromise({action:'listProductos'}).then(res=>{
    const tbody=document.getElementById('productList');tbody.innerHTML='';
    res.forEach(p=>{
      tbody.innerHTML+=`
      <tr>
        <td>${p.id_producto}</td>
        <td><img src="${p.imagen||'https://via.placeholder.com/60x40'}" width="60" class="rounded"></td>
        <td>${p.titulo} ${p.vip==1?'<i class="fa-solid fa-crown text-warning"></i>':''}</td>
        <td>${p.genero}</td>
        <td>${p.nombre_proveedor||'-'}</td>
        <td>$${parseFloat(p.precio).toFixed(2)}</td>
        <td>${p.plataforma}</td>
        <td>${p.fecha_lanzamiento}</td>
        <td>${p.rating_promedio}</td>
        <td class="${p.estado==='activo'?'text-success':'text-danger'}">${p.estado}</td>
        <td>${p.vip==1?'Sí':'No'}</td>
        <td>
          <button class="btn btn-warning btn-sm" onclick="editProd(${p.id_producto})"><i class="fa-solid fa-pen"></i></button>
          <button class="btn btn-danger btn-sm" onclick="deleteProd(${p.id_producto})"><i class="fa-solid fa-trash"></i></button>
        </td>
      </tr>`;
    });
  });
}

form.addEventListener('submit',e=>{
  e.preventDefault();
  const d={
    titulo:titulo.value,genero:genero.value,id_proveedor:id_proveedor.value,descripcion:descripcion.value,
    precio:precio.value,plataforma:plataforma.value,fecha_lanzamiento:fecha_lanzamiento.value,
    rating_promedio:rating_promedio.value,imagen:imagen.value,estado:estado.value,vip:vip.checked?1:0
  };
  if(editIndex){d.action='edit';d.id_producto=editIndex;}else{d.action='add';}
  ajaxPromise(d).then(r=>{
    loadProductos();form.reset();editIndex=null;cancelBtn.style.display='none';
  });
});

function editProd(id){
  ajaxPromise({action:'listProductos'}).then(res=>{
    const p=res.find(x=>x.id_producto==id);
    if(p){
      titulo.value=p.titulo;genero.value=p.genero;id_proveedor.value=p.id_proveedor;
      descripcion.value=p.descripcion;precio.value=p.precio;plataforma.value=p.plataforma;
      fecha_lanzamiento.value=p.fecha_lanzamiento;rating_promedio.value=p.rating_promedio;
      imagen.value=p.imagen;estado.value=p.estado;vip.checked=p.vip==1;
      editIndex=id;cancelBtn.style.display='inline-block';
    }
  });
}

function deleteProd(id){
  if(confirm('¿Eliminar producto?')){
    ajaxPromise({action:'delete',id_producto:id}).then(()=>loadProductos());
  }
}

cancelBtn.onclick=()=>{form.reset();editIndex=null;cancelBtn.style.display='none';};

window.onload=async()=>{loader.style.display='none';mainContent.style.display='block';await loadProveedores();loadProductos();};
</script>
</body>
</html>
