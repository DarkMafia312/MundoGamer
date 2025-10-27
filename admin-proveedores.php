<?php
session_start();
include('conexion.php');

// Carga de dependencias de Composer (una sola vez)
require __DIR__ . '/vendor/autoload.php';

// Importaciones de clases (DEBEN ir fuera de cualquier función o if)
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// === EXPORTAR A EXCEL ===
if (isset($_GET['export'])) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Proveedores');

    // Encabezados
    $sheet->setCellValue('A1', 'ID');
    $sheet->setCellValue('B1', 'Empresa');
    $sheet->setCellValue('C1', 'RUC');
    $sheet->setCellValue('D1', 'Teléfono');
    $sheet->setCellValue('E1', 'Correo');
    $sheet->setCellValue('F1', 'Dirección');
    $sheet->setCellValue('G1', 'Paga');
    $sheet->setCellValue('H1', 'Fecha Registro');

    // Datos
    $query = $conn->query("SELECT * FROM proveedores ORDER BY id ASC");
    $fila = 2;
    while ($row = $query->fetch_assoc()) {
        $sheet->setCellValue("A{$fila}", $row['id']);
        $sheet->setCellValue("B{$fila}", $row['empresa']);
        $sheet->setCellValue("C{$fila}", $row['ruc']);
        $sheet->setCellValue("D{$fila}", $row['telefono']);
        $sheet->setCellValue("E{$fila}", $row['correo']);
        $sheet->setCellValue("F{$fila}", $row['direccion']);
        $sheet->setCellValue("G{$fila}", $row['paga']);
        $sheet->setCellValue("H{$fila}", $row['fechaRegistro']);
        $fila++;
    }

    // Estilos simples
    foreach (range('A', 'H') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // Descargar el archivo
    $filename = "proveedores_MundoGamer.xlsx";
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment; filename=\"$filename\"");
    $writer = new Xlsx($spreadsheet);
    $writer->save("php://output");
    exit;
}

function clean($data, $conn){
    return htmlspecialchars($conn->real_escape_string($data));
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $id = isset($_POST['proveedorId']) && $_POST['proveedorId'] !== "" ? (int)$_POST['proveedorId'] : null;
    $empresa = clean($_POST['nombreEmpresa'], $conn);
    $ruc = clean($_POST['rucProveedor'], $conn);
    $telefono = clean($_POST['telefonoProveedor'], $conn);
    $correo = clean($_POST['correoProveedor'], $conn);
    $direccion = clean($_POST['direccionProveedor'], $conn);
    $paga = clean($_POST['pagaProveedor'], $conn);

    if($id){ 
        $stmt = $conn->prepare("UPDATE proveedores SET empresa=?, ruc=?, telefono=?, correo=?, direccion=?, paga=? WHERE id=?");
        $stmt->bind_param("ssssssi", $empresa, $ruc, $telefono, $correo, $direccion, $paga, $id);
        $stmt->execute();
        $stmt->close();
        $_SESSION['mensaje'] = "Proveedor actualizado correctamente ✅";
    } else { 
        $stmt = $conn->prepare("INSERT INTO proveedores (empresa,ruc,telefono,correo,direccion,paga) VALUES (?,?,?,?,?,?)");
        $stmt->bind_param("ssssss", $empresa, $ruc, $telefono, $correo, $direccion, $paga);
        $stmt->execute();
        $stmt->close();
        $_SESSION['mensaje'] = "Proveedor agregado correctamente ✅";
    }

    header("Location: admin-proveedores.php");
    exit();
}

if(isset($_GET['delete'])){
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM proveedores WHERE id=$id");
    $_SESSION['mensaje'] = "Proveedor eliminado correctamente 🗑️";
    header("Location: admin-proveedores.php");
    exit();
}

$result = $conn->query("SELECT * FROM proveedores ORDER BY id ASC");
$proveedores = [];
while($row = $result->fetch_assoc()){
    $proveedores[] = $row;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gestión de Proveedores</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
#loader { position: fixed; top:0; left:0; width:100%; height:100%; background:#0a0a0a; display:flex; justify-content:center; align-items:center; z-index:9999; }
.spinner { border:6px solid #1a1a1a; border-top:6px solid #00c8ff; border-radius:50%; width:60px;height:60px; animation:spin 1s linear infinite; }
@keyframes spin { to{transform:rotate(360deg);} }
body { background: linear-gradient(135deg, #0a0f1f, #1a1f2f); color: #e0e0e0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; min-height:100vh; opacity:0; animation:fadeIn 0.8s forwards; }
.container-custom { background-color:#121826; padding:2rem; border-radius:15px; box-shadow:0px 0px 25px rgba(0,200,255,0.25); animation:slideDown 0.5s ease-in-out; }
h2 { text-align:center; color:#00c8ff; margin-bottom:20px; }
.btn-gamer { background: linear-gradient(90deg,#00c8ff,#0077ff); color:#fff; font-weight:bold; border:none; border-radius:8px; transition:0.2s; }
.btn-gamer:hover { transform:scale(1.05); box-shadow:0 0 10px #00c8ff; }
.btn-cancel { background:#ff4d4d; border:none; color:#fff; font-weight:bold; border-radius:8px; transition:0.2s; display:none; }
.btn-cancel:hover { transform:scale(1.05); box-shadow:0 0 10px #ff4d4d; }
table { color:#fff; }
table thead { background:#1e273b; }
table tbody tr:hover { background: rgba(0,200,255,0.1); transform: scale(1.01); transition:0.3s; }
.action-btn { cursor:pointer; font-size:1.2rem; margin:0 5px; border:none; background:none; }
.action-btn.edit { color:#ffc107; }
.action-btn.delete { color:#ff4d4d; }
@keyframes slideDown { from{transform:translateY(-15px);opacity:0;} to{transform:translateY(0);opacity:1;} }
@keyframes fadeIn { to{opacity:1;} }
#alerta { display:none; margin-bottom:15px; }
</style>
</head>
<body>

<div id="loader"><div class="spinner"></div></div>

<div class="container container-custom mt-5">
  <h2><i class="fas fa-truck"></i> Gestión de Proveedores</h2>

  <div id="alerta" class="alert alert-success text-center">
      <?php 
      if(isset($_SESSION['mensaje'])) { 
          echo $_SESSION['mensaje']; 
          unset($_SESSION['mensaje']); 
      } 
      ?>
  </div>

  <form id="proveedorForm" class="mb-4" method="POST">
    <input type="hidden" name="proveedorId" id="proveedorId">
    <div class="row g-3">
      <div class="col-md-6"><input type="text" name="nombreEmpresa" id="nombreEmpresa" class="form-control" placeholder="Nombre de la Empresa" required></div>
      <div class="col-md-6"><input type="text" name="rucProveedor" id="rucProveedor" class="form-control" placeholder="RUC" required></div>
      <div class="col-md-6"><input type="tel" name="telefonoProveedor" id="telefonoProveedor" class="form-control" placeholder="Teléfono" required></div>
      <div class="col-md-6"><input type="email" name="correoProveedor" id="correoProveedor" class="form-control" placeholder="Correo" required></div>
      <div class="col-md-6"><input type="text" name="direccionProveedor" id="direccionProveedor" class="form-control" placeholder="Dirección" required></div>
      <div class="col-md-6"><input type="text" name="pagaProveedor" id="pagaProveedor" class="form-control" placeholder="Paga" required></div>
    </div>
    <div class="d-flex gap-2 mt-3">
      <button type="submit" id="btnAgregar" class="btn btn-gamer w-100"><i class="fas fa-plus-circle"></i> Agregar</button>
      <button type="submit" id="btnGuardar" class="btn btn-gamer w-100" style="display:none;"><i class="fas fa-save"></i> Guardar</button>
      <button type="button" id="btnCancelar" class="btn btn-cancel w-100"><i class="fas fa-ban"></i> Cancelar</button>
    </div>
  </form>

  <table class="table table-dark table-hover align-middle">
    <thead>
      <tr>
        <th>ID</th><th>Empresa</th><th>RUC</th><th>Teléfono</th><th>Correo</th><th>Dirección</th><th>Paga</th><th>Fecha Registro</th><th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($proveedores as $p): ?>
      <tr>
        <td><?= $p['id'] ?></td>
        <td><?= $p['empresa'] ?></td>
        <td><?= $p['ruc'] ?></td>
        <td><?= $p['telefono'] ?></td>
        <td><?= $p['correo'] ?></td>
        <td><?= $p['direccion'] ?></td>
        <td><?= $p['paga'] ?></td>
        <td><?= $p['fechaRegistro'] ?></td>
        <td>
          <button type="button" class="action-btn edit" 
            title="Editar proveedor"
            onclick="editarProveedor(<?= $p['id'] ?>,'<?= addslashes($p['empresa']) ?>','<?= addslashes($p['ruc']) ?>','<?= addslashes($p['telefono']) ?>','<?= addslashes($p['correo']) ?>','<?= addslashes($p['direccion']) ?>','<?= addslashes($p['paga']) ?>')">
            <i class="fas fa-pencil-alt"></i>
          </button>
          <a href="?delete=<?= $p['id'] ?>" class="action-btn delete" title="Eliminar proveedor" onclick="return confirm('Eliminar este proveedor?')">
            <i class="fas fa-trash-alt"></i>
          </a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <div class="d-flex justify-content-between">
    <a href="admin-dashboard.php" class="btn btn-secondary mt-3"><i class="fas fa-arrow-left"></i> Volver</a>
    <a href="?export=1" class="btn btn-success mt-3"><i class="fas fa-file-excel"></i> Exportar a Excel</a>
  </div>
</div>

<script>
window.addEventListener("load",()=>{
    document.getElementById("loader").style.display="none";
    const alerta = document.getElementById('alerta');
    if(alerta.textContent.trim()!==''){ alerta.style.display='block'; setTimeout(()=>alerta.style.display='none',3000);}
});

function editarProveedor(id, empresa, ruc, telefono, correo, direccion, paga){
    document.getElementById('proveedorId').value = id;
    document.getElementById('nombreEmpresa').value = empresa;
    document.getElementById('rucProveedor').value = ruc;
    document.getElementById('telefonoProveedor').value = telefono;
    document.getElementById('correoProveedor').value = correo;
    document.getElementById('direccionProveedor').value = direccion;
    document.getElementById('pagaProveedor').value = paga;

    document.getElementById('btnAgregar').style.display = 'none';
    document.getElementById('btnGuardar').style.display = 'block';
    document.getElementById('btnCancelar').style.display = 'block';
}

document.getElementById('btnCancelar').addEventListener('click', ()=>{
    document.getElementById('proveedorForm').reset();
    document.getElementById('btnAgregar').style.display = 'block';
    document.getElementById('btnGuardar').style.display = 'none';
    document.getElementById('btnCancelar').style.display = 'none';

    const alerta = document.getElementById('alerta');
    alerta.textContent = "Edición cancelada ❌";
    alerta.className = "alert alert-warning text-center";
    alerta.style.display='block';
    setTimeout(()=>alerta.style.display='none',3000);
});
</script>
</body>
</html>