<?php
session_start();
include 'conexion.php';
require __DIR__ . '/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// ✅ EXPORTAR MENSAJES Y CALIFICACIONES A EXCEL
if (isset($_GET['export'])) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Mensajes y Calificaciones');

    // Encabezados
    $sheet->setCellValue('A1', 'ID Soporte');
    $sheet->setCellValue('B1', 'Usuario');
    $sheet->setCellValue('C1', 'Correo');
    $sheet->setCellValue('D1', 'Mensaje');
    $sheet->setCellValue('E1', 'Respuesta Admin');
    $sheet->setCellValue('F1', 'Estado');
    $sheet->setCellValue('G1', 'Fecha Envío');
    $sheet->setCellValue('H1', 'Fecha Respuesta');
    $sheet->setCellValue('I1', 'Producto');
    $sheet->setCellValue('J1', 'Calificación');
    $sheet->setCellValue('K1', 'Comentario');
    $sheet->setCellValue('L1', 'Fecha Calificación');

    // Traer datos
    $query = $conn->query("
      SELECT 
        s.id_soporte, u.nombre, u.apellido, u.correo,
        s.mensaje, s.respuesta_admin, s.estado, s.fecha_envio, s.fecha_respuesta,
        p.titulo AS producto, c.puntuacion, c.comentario, c.fecha_registro
      FROM soporte_cliente s
      LEFT JOIN usuarios u ON s.id_usuario = u.id
      LEFT JOIN calificaciones c ON u.id = c.id_usuario
      LEFT JOIN productos p ON c.id_producto = p.id_producto
      ORDER BY s.fecha_envio DESC
    ");

    $fila = 2;
    while ($row = $query->fetch_assoc()) {
        $sheet->setCellValue("A{$fila}", $row['id_soporte']);
        $sheet->setCellValue("B{$fila}", $row['nombre'] . ' ' . $row['apellido']);
        $sheet->setCellValue("C{$fila}", $row['correo']);
        $sheet->setCellValue("D{$fila}", $row['mensaje']);
        $sheet->setCellValue("E{$fila}", $row['respuesta_admin']);
        $sheet->setCellValue("F{$fila}", $row['estado']);
        $sheet->setCellValue("G{$fila}", $row['fecha_envio']);
        $sheet->setCellValue("H{$fila}", $row['fecha_respuesta']);
        $sheet->setCellValue("I{$fila}", $row['producto']);
        $sheet->setCellValue("J{$fila}", $row['puntuacion']);
        $sheet->setCellValue("K{$fila}", $row['comentario']);
        $sheet->setCellValue("L{$fila}", $row['fecha_registro']);
        $fila++;
    }

    // Ajuste de ancho
    foreach (range('A', 'L') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // Descargar
    $filename = "mensajes_calificaciones_MundoGamer.xlsx";
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment; filename=\"$filename\"");
    $writer = new Xlsx($spreadsheet);
    $writer->save("php://output");
    exit;
}


if (!isset($_SESSION['usuario_admin'])) {
  header("Location: admin-login.php");
  exit();
}

// ✅ Procesar acciones AJAX
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['accion'])) {
  header('Content-Type: application/json; charset=utf-8');
  
  $accion = $_POST['accion'];
  $id = intval($_POST['id']);

  if ($accion === 'responder') {
    $respuesta = trim($_POST['respuesta']);
    $stmt = $conn->prepare("UPDATE soporte_cliente SET respuesta_admin=?, estado='Resuelto', fecha_respuesta=NOW() WHERE id_soporte=?");
    $stmt->bind_param("si", $respuesta, $id);
    $stmt->execute();
    echo json_encode(["status" => "ok", "mensaje" => "Respuesta enviada correctamente."]);
    exit;
  }

  if ($accion === 'ignorar') {
    $stmt = $conn->prepare("UPDATE soporte_cliente SET estado='En Revisión' WHERE id_soporte=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    echo json_encode(["status" => "ok", "mensaje" => "Mensaje marcado como 'En Revisión'."]);
    exit;
  }

  if ($accion === 'eliminar_soporte') {
    $stmt = $conn->prepare("DELETE FROM soporte_cliente WHERE id_soporte=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    echo json_encode(["status" => "ok", "mensaje" => "Mensaje eliminado correctamente."]);
    exit;
  }

  if ($accion === 'eliminar_calificacion') {
    $stmt = $conn->prepare("DELETE FROM calificaciones WHERE id_calificacion=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    echo json_encode(["status" => "ok", "mensaje" => "Calificación eliminada correctamente."]);
    exit;
  }
}

// ✅ Consultas
$usuarios = $conn->query("SELECT id, nombre, apellido, correo FROM usuarios");
$soporte = $conn->query("
  SELECT s.*, u.nombre, u.apellido
  FROM soporte_cliente s
  INNER JOIN usuarios u ON s.id_usuario = u.id
  ORDER BY s.fecha_envio DESC
");
$calificaciones = $conn->query("
  SELECT c.*, p.titulo AS producto, u.nombre, u.apellido
  FROM calificaciones c
  INNER JOIN productos p ON c.id_producto = p.id_producto
  INNER JOIN usuarios u ON c.id_usuario = u.id
  ORDER BY c.fecha_registro DESC
");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Gestión de Mensajes y Calificaciones</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>
  body {
    background:#0d1117;
    color:#e6edf3;
    padding:20px;
    font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    opacity:0;
    animation:fadeIn 0.8s ease-out forwards;
  }
  @keyframes fadeIn { to { opacity:1; } }

  .user-card {
    background:#1c1f26;
    border-radius:12px;
    padding:20px;
    margin-bottom:20px;
    box-shadow:0 4px 15px rgba(0,0,0,0.3);
    transition:transform 0.3s ease, box-shadow 0.3s ease;
  }
  .user-card:hover {
    transform:translateY(-5px);
    box-shadow:0 6px 18px rgba(0,0,0,0.4);
  }
  .item {
    background:#161b22;
    border-radius:8px;
    padding:12px;
    margin-bottom:10px;
    position:relative;
    transition:background 0.3s ease;
  }
  .item:hover { background:#202632; }
  .btn-sm { font-size:0.8rem; margin-right:4px; }
  .badge-estado { font-size:0.75rem; padding:4px 6px; border-radius:5px; text-transform:capitalize; }
  .pendiente { background:#f39c12; }
  .resuelto { background:#2ecc71; }
  .revision { background:#9b59b6; }

  .fade-in {
    animation:slideUp 0.5s ease-out forwards;
  }
  @keyframes slideUp {
    from { transform:translateY(15px); opacity:0; }
    to { transform:translateY(0); opacity:1; }
  }

  .volver {
    background:#1f6feb;
    color:white;
    border:none;
    padding:10px 18px;
    border-radius:8px;
    text-decoration:none;
    transition:background 0.3s ease;
  }
  .volver:hover { background:#388bfd; }

</style>
</head>
<body>

<h2 class="text-center mb-4 fade-in"><i class="fas fa-tools"></i> Gestión de Mensajes y Calificaciones</h2>

  <div class="text-end mb-3">
  <a href="admin-mensajes-calificacion.php?export=1" class="btn btn-success">
    <i class="fa-solid fa-file-excel"></i> Exportar a Excel
  </a>
</div>

<div class="container fade-in">
<?php
$usuariosData = [];

while ($fila = $soporte->fetch_assoc()) {
  $id = $fila['id_usuario'];
  $usuariosData[$id]['nombre'] = $fila['nombre'];
  $usuariosData[$id]['apellido'] = $fila['apellido'];
  $usuariosData[$id]['soporte'][] = $fila;
}

while ($fila = $calificaciones->fetch_assoc()) {
  $id = $fila['id_usuario'];
  $usuariosData[$id]['nombre'] = $fila['nombre'];
  $usuariosData[$id]['apellido'] = $fila['apellido'];
  $usuariosData[$id]['calificaciones'][] = $fila;
}

if (empty($usuariosData)) {
  echo "<p class='text-center text-muted'>No hay registros disponibles.</p>";
} else {
  foreach ($usuariosData as $id => $user) {
    echo "<div class='user-card fade-in'>";
    echo "<h5><i class='fas fa-user'></i> " . htmlspecialchars($user['nombre'].' '.$user['apellido']) . "</h5>";

    // 🔹 MENSAJES DE AYUDA
    echo "<h6 class='mt-3 text-info'><i class='fas fa-headset'></i> Mensajes de Ayuda</h6>";
    if (empty($user['soporte'])) {
      echo "<p class='text-muted'>Sin mensajes.</p>";
    } else {
      foreach ($user['soporte'] as $m) {
        $estadoClass = strtolower($m['estado']);
        echo "<div class='item'>
                <p><strong>Asunto:</strong> ".htmlspecialchars($m['asunto'])."</p>
                <p><strong>Mensaje:</strong> ".nl2br(htmlspecialchars($m['mensaje']))."</p>
                <p><strong>Fecha:</strong> {$m['fecha_envio']}</p>
                <p><strong>Estado:</strong> <span class='badge-estado {$estadoClass}'>".htmlspecialchars($m['estado'])."</span></p>";
        if (!empty($m['respuesta_admin'])) {
          echo "<p><strong>Respuesta Admin:</strong> ".nl2br(htmlspecialchars($m['respuesta_admin']))."</p>";
        }

        echo "<div class='mt-2'>
                <button class='btn btn-success btn-sm' onclick='abrirModalResponder({$m['id_soporte']})'><i class=\"fas fa-reply\"></i> Responder</button>
                <button class='btn btn-warning btn-sm' onclick='ignorarMensaje({$m['id_soporte']})'><i class=\"fas fa-eye-slash\"></i> Ignorar</button>
                <button class='btn btn-danger btn-sm' onclick='eliminarSoporte({$m['id_soporte']})'><i class=\"fas fa-trash\"></i> Eliminar</button>
              </div></div>";
      }
    }

    // 🔹 CALIFICACIONES
    echo "<h6 class='mt-4 text-warning'><i class='fas fa-star'></i> Calificaciones</h6>";
    if (empty($user['calificaciones'])) {
      echo "<p class='text-muted'>Sin calificaciones.</p>";
    } else {
      foreach ($user['calificaciones'] as $c) {
        $stars = str_repeat('★', $c['puntuacion']).str_repeat('☆', 5 - $c['puntuacion']);
        echo "<div class='item'>
                <p><strong>Producto:</strong> ".htmlspecialchars($c['producto'])."</p>
                <p><strong>Puntuación:</strong> <span style='color:gold;'>{$stars}</span></p>
                <p><strong>Comentario:</strong> ".nl2br(htmlspecialchars($c['comentario']))."</p>
                <p><strong>Fecha:</strong> {$c['fecha_registro']}</p>
                <button class='btn btn-danger btn-sm' onclick='eliminarCalificacion({$c['id_calificacion']})'><i class=\"fas fa-trash\"></i> Eliminar</button>
              </div>";
      }
    }

    echo "</div>";
  }
}
?>
</div>

<!-- 🔹 Botón de Volver -->
<div class="text-center mt-4 fade-in">
  <a href="admin-dashboard.php" class="volver"><i class="fas fa-arrow-left"></i> Volver al Dashboard</a>
</div>

<!-- 🔹 Modal de Respuesta -->
<div class="modal fade" id="modalResponder" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-dark text-light">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-reply"></i> Responder Mensaje</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formRespuesta">
          <input type="hidden" id="idSoporte" name="id">
          <div class="mb-3">
            <label>Respuesta del Administrador</label>
            <textarea class="form-control" name="respuesta" id="respuesta" rows="4" required></textarea>
          </div>
          <button type="submit" class="btn btn-success w-100">Enviar Respuesta</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
let idSeleccionado = null;

function abrirModalResponder(id) {
  idSeleccionado = id;
  document.getElementById('idSoporte').value = id;
  new bootstrap.Modal(document.getElementById('modalResponder')).show();
}

document.getElementById('formRespuesta').addEventListener('submit', async (e) => {
  e.preventDefault();
  const formData = new FormData();
  formData.append('accion', 'responder');
  formData.append('id', idSeleccionado);
  formData.append('respuesta', document.getElementById('respuesta').value);

  const res = await fetch('admin-mensajes-calificacion.php', { method: 'POST', body: formData });
  const data = await res.json();
  alert(data.mensaje);
  location.reload();
});

async function ignorarMensaje(id) {
  if (!confirm("¿Marcar mensaje como 'En Revisión'?")) return;
  const formData = new FormData();
  formData.append('accion', 'ignorar');
  formData.append('id', id);
  const res = await fetch('admin-mensajes-calificacion.php', { method: 'POST', body: formData });
  const data = await res.json();
  alert(data.mensaje);
  location.reload();
}

async function eliminarSoporte(id) {
  if (!confirm("¿Eliminar este mensaje definitivamente?")) return;
  const formData = new FormData();
  formData.append('accion', 'eliminar_soporte');
  formData.append('id', id);
  const res = await fetch('admin-mensajes-calificacion.php', { method: 'POST', body: formData });
  const data = await res.json();
  alert(data.mensaje);
  location.reload();
}

async function eliminarCalificacion(id) {
  if (!confirm("¿Eliminar esta calificación?")) return;
  const formData = new FormData();
  formData.append('accion', 'eliminar_calificacion');
  formData.append('id', id);
  const res = await fetch('admin-mensajes-calificacion.php', { method: 'POST', body: formData });
  const data = await res.json();
  alert(data.mensaje);
  location.reload();
}
</script>

</body>
</html>

<?php $conn->close(); ?>