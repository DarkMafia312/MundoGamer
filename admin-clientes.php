<?php
require_once __DIR__ . '/sentry.php';
session_start();

// ===== SECURITY =====
require_once "security.php";
require_admin();
// =======================================

include 'conexion.php';

// === Cargar PhpSpreadsheet ===
require __DIR__ . '/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// ==================== EXPORTAR CLIENTES A EXCEL ====================
if (isset($_GET['export'])) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Clientes');

    // Encabezados
    $sheet->setCellValue('A1', 'ID');
    $sheet->setCellValue('B1', 'Nombre');
    $sheet->setCellValue('C1', 'Apellido');
    $sheet->setCellValue('D1', 'Usuario');
    $sheet->setCellValue('E1', 'Correo');
    $sheet->setCellValue('F1', 'Teléfono');
    $sheet->setCellValue('G1', 'Dirección');
    $sheet->setCellValue('H1', 'Fecha Registro');
    $sheet->setCellValue('I1', 'Estado');
    $sheet->setCellValue('J1', 'Inicio Suspensión');
    $sheet->setCellValue('K1', 'Fin Suspensión');

    $query = "
      SELECT 
        u.id, u.nombre, u.apellido, u.username, u.correo, u.telefono, 
        u.direccion, u.fechaRegistro,
        COALESCE(g.estado, 'Activo') AS estado,
        g.fecha_suspension_inicio,
        g.fecha_suspension_fin
      FROM usuarios u
      LEFT JOIN gestion_clientes g ON u.id = g.id_usuario
      ORDER BY u.fechaRegistro DESC
    ";
    $res = $conn->query($query);

    $fila = 2;
    while ($row = $res->fetch_assoc()) {
        $sheet->setCellValue("A{$fila}", $row['id']);
        $sheet->setCellValue("B{$fila}", $row['nombre']);
        $sheet->setCellValue("C{$fila}", $row['apellido']);
        $sheet->setCellValue("D{$fila}", $row['username']);
        $sheet->setCellValue("E{$fila}", $row['correo']);
        $sheet->setCellValue("F{$fila}", $row['telefono']);
        $sheet->setCellValue("G{$fila}", $row['direccion']);
        $sheet->setCellValue("H{$fila}", $row['fechaRegistro']);
        $sheet->setCellValue("I{$fila}", $row['estado']);
        $sheet->setCellValue("J{$fila}", $row['fecha_suspension_inicio']);
        $sheet->setCellValue("K{$fila}", $row['fecha_suspension_fin']);
        $fila++;
    }

    foreach (range('A', 'K') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    $filename = "clientes_MundoGamer.xlsx";
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment; filename=\"$filename\"");
    $writer = new Xlsx($spreadsheet);
    $writer->save("php://output");
    exit;
}

// VALIDAR QUE EL ADMIN ESTÁ LOGEADO (SESION CORRECTA)
if (!isset($_SESSION['usuario_admin'])) {
  header("Location: admin-login.php");
  exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['accion'])) {
  header('Content-Type: application/json; charset=utf-8');
  $accion = $_POST['accion'];
  $id_usuario = intval($_POST['id_usuario']);

  if ($accion === 'actualizar_estado') {
    $estado = $_POST['estado'] ?? 'Activo';
    $inicio = $_POST['fecha_suspension_inicio'] ?: null;
    $fin = $_POST['fecha_suspension_fin'] ?: null;

    $check = $conn->prepare("SELECT id_gestion FROM gestion_clientes WHERE id_usuario=?");
    $check->bind_param("i", $id_usuario);
    $check->execute();
    $res = $check->get_result();

    if ($res->num_rows > 0) {
      $stmt = $conn->prepare("UPDATE gestion_clientes SET estado=?, fecha_suspension_inicio=?, fecha_suspension_fin=? WHERE id_usuario=?");
      $stmt->bind_param("sssi", $estado, $inicio, $fin, $id_usuario);
    } else {
      $stmt = $conn->prepare("INSERT INTO gestion_clientes (id_usuario, estado, fecha_suspension_inicio, fecha_suspension_fin) VALUES (?, ?, ?, ?)");
      $stmt->bind_param("isss", $id_usuario, $estado, $inicio, $fin);
    }

    $stmt->execute();
    echo json_encode(["status" => "ok", "mensaje" => "✅ Estado actualizado correctamente."]);
    exit;
  }

  if ($accion === 'eliminar_usuario') {
    $stmt = $conn->prepare("DELETE FROM usuarios WHERE id=?");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $conn->query("DELETE FROM gestion_clientes WHERE id_usuario=$id_usuario");
    echo json_encode(["status" => "ok", "mensaje" => "🗑️ Usuario eliminado correctamente."]);
    exit;
  }
}

$query = "
  SELECT 
    u.id, u.nombre, u.apellido, u.username, u.correo, u.telefono, 
    u.direccion, u.fechaRegistro,
    COALESCE(g.estado, 'Activo') AS estado,
    g.fecha_suspension_inicio,
    g.fecha_suspension_fin
  FROM usuarios u
  LEFT JOIN gestion_clientes g ON u.id = g.id_usuario
  ORDER BY u.fechaRegistro DESC
";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Gestión de Clientes</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
/* ==== ESTILO GENERAL ==== */
body {
  background: radial-gradient(circle at top left, #0d1b2a, #1b263b, #0a1128);
  color: #eaeaea;
  font-family: 'Poppins', sans-serif;
  overflow-x: hidden;
  animation: fadeInBody 1.5s ease;
}
@keyframes fadeInBody {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

h1 {
  color: #00ffc6;
  text-shadow: 0 0 15px #00ffc6;
  margin-bottom: 25px;
  animation: fadeIn 1s ease-in-out;
}
@keyframes fadeIn {
  from { opacity: 0; transform: scale(0.95); }
  to { opacity: 1; transform: scale(1); }
}

/* ==== TARJETAS ==== */
.card {
  background: linear-gradient(145deg, #1e2338, #2a3152);
  border: 1px solid rgba(0, 255, 198, 0.3);
  border-radius: 15px;
  padding: 22px;
  color: #cce7e1;
  box-shadow: 0 4px 25px rgba(0,255,198,0.1);
  transition: transform 0.4s ease, box-shadow 0.4s ease;
  animation: floatCard 0.6s ease-in-out;
}
.card:hover {
  transform: translateY(-5px);
  box-shadow: 0 6px 25px rgba(0,255,198,0.3);
}
@keyframes floatCard {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

/* ==== BOTONES ==== */
.btn-accion {
  border-radius: 10px;
  font-weight: 600;
  padding: 8px 15px;
  transition: all .25s ease-in-out;
  border: none;
  cursor: pointer;
}
.btn-editar { background:#0077ff; color:#fff; box-shadow: 0 0 8px rgba(0,119,255,0.4); }
.btn-editar:hover { background:#339aff; transform: scale(1.05); }

.btn-eliminar { background:#ff3b3b; color:#fff; box-shadow: 0 0 8px rgba(255,59,59,0.4); }
.btn-eliminar:hover { background:#ff5e5e; transform: scale(1.05); }

.btn-guardar { background:#00ffc6; color:#000; box-shadow: 0 0 8px rgba(0,255,198,0.6); }
.btn-guardar:hover { background:#55ffd9; transform: scale(1.05); }

.btn-cancelar { background:#999; color:#000; }
.btn-cancelar:hover { background:#bbb; transform: scale(1.05); }

/* ==== INPUTS ==== */
.form-control, .form-select {
  background-color:#23233c;
  color:#fff;
  border:1px solid #00ffc6;
  border-radius: 6px;
  transition: all 0.3s ease;
}
.form-control:focus, .form-select:focus {
  border-color: #00ffe0;
  box-shadow: 0 0 8px rgba(0,255,198,0.4);
}

/* ==== FECHAS ==== */
input[type="date"] {
  background-color:#23233c;
  border:1px solid #00ffc6;
  color:#fff;
  border-radius:6px;
  padding:5px;
  width:100%;
}

/* ==== ANIMACIONES ==== */
.fechaSuspension { display:none; }
#listaUsuarios { animation: fadeInList 1.2s ease-in-out; }
@keyframes fadeInList {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

.btn-outline-info {
  border-color:#00ffc6;
  color:#00ffc6;
  transition: all 0.3s;
}
.btn-outline-info:hover {
  background:#00ffc6;
  color:#000;
  transform: scale(1.05);
}
</style>
</head>

<body>

<div class="container mt-4">
  <h1 class="text-center"><i class="fas fa-users"></i> Gestión de Clientes</h1>
  <div class="text-end mb-3">
  <a href="admin-clientes.php?export=1" class="btn btn-success">
    <i class="fa-solid fa-file-excel"></i> Exportar a Excel
  </a>
</div>
  <input type="text" id="busqueda" class="form-control mb-4 shadow" placeholder="🔍 Buscar usuario...">

  <div class="row" id="listaUsuarios">
    <?php if ($result->num_rows > 0): ?>
      <?php while ($u = $result->fetch_assoc()): ?>
        <div class="col-md-6 usuario-card mb-4" data-nombre="<?= strtolower($u['nombre'].' '.$u['apellido'].' '.$u['username']) ?>">
          <div class="card">
            <h5><i class="fas fa-user"></i> <?= htmlspecialchars($u['nombre'].' '.$u['apellido']) ?></h5>
            <p><strong>Username:</strong> <?= htmlspecialchars($u['username']) ?></p>
            <p><strong>Correo:</strong> <?= htmlspecialchars($u['correo']) ?></p>
            <p><strong>Teléfono:</strong> <?= htmlspecialchars($u['telefono']) ?></p>
            <p><strong>Dirección:</strong> <?= htmlspecialchars($u['direccion']) ?></p>
            <p><strong>Registro:</strong> <?= $u['fechaRegistro'] ?></p>

            <label><strong>Estado:</strong></label>
            <select class="form-select form-select-sm estadoSelect" data-id="<?= $u['id'] ?>" disabled>
              <option value="Activo" <?= ($u['estado']=='Activo')?'selected':'' ?>>Activo</option>
              <option value="Inactivo" <?= ($u['estado']=='Inactivo')?'selected':'' ?>>Inactivo</option>
              <option value="Suspendido" <?= ($u['estado']=='Suspendido')?'selected':'' ?>>Suspendido</option>
              <option value="Baneado" <?= ($u['estado']=='Baneado')?'selected':'' ?>>Baneado</option>
            </select>

            <div class="fechaSuspension mt-2" <?= ($u['estado']=='Suspendido')?'style="display:block"':'' ?>>
              <label><strong>Desde:</strong></label>
              <input type="date" class="form-control form-control-sm inicioSusp" value="<?= $u['fecha_suspension_inicio'] ?>">
              <label class="mt-2"><strong>Hasta:</strong></label>
              <input type="date" class="form-control form-control-sm finSusp" value="<?= $u['fecha_suspension_fin'] ?>">
            </div>

            <div class="mt-4 botones-defecto">
              <button class="btn btn-sm btn-accion btn-editar" data-id="<?= $u['id'] ?>">Editar</button>
              <button class="btn btn-sm btn-accion btn-eliminar" data-id="<?= $u['id'] ?>">Eliminar</button>
            </div>

            <div class="acciones-guardado mt-3" style="display:none;">
              <button class="btn btn-sm btn-accion btn-guardar" data-id="<?= $u['id'] ?>">Guardar</button>
              <button class="btn btn-sm btn-accion btn-cancelar">Cancelar</button>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p class="text-center text-muted">No hay usuarios registrados.</p>
    <?php endif; ?>
  </div>

  <div class="text-center mt-4 mb-5">
    <a href="admin-dashboard.php" class="btn btn-outline-info"><i class="fas fa-arrow-left"></i> Volver al Dashboard</a>
  </div>
 
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
  document.getElementById('busqueda').addEventListener('input', e => {
    const filtro = e.target.value.toLowerCase();
    document.querySelectorAll('.usuario-card').forEach(card => {
      card.style.display = card.dataset.nombre.includes(filtro) ? '' : 'none';
    });
  });

  document.querySelectorAll('.estadoSelect').forEach(sel => {
    sel.addEventListener('change', () => {
      const cont = sel.closest('.card').querySelector('.fechaSuspension');
      cont.style.display = (sel.value === 'Suspendido') ? 'block' : 'none';
    });
  });

  document.querySelectorAll('.btn-editar').forEach(btn => {
    btn.addEventListener('click', () => {
      const card = btn.closest('.card');
      btn.style.display = 'none';
      card.querySelector('.btn-eliminar').style.display = 'none';
      card.querySelector('.estadoSelect').disabled = false;
      card.querySelector('.acciones-guardado').style.display = 'flex';
      card.style.boxShadow = '0 0 20px rgba(0,255,198,0.5)';
      card.style.transition = 'box-shadow 0.3s ease';
    });
  });

  document.querySelectorAll('.btn-cancelar').forEach(btn => 
    btn.addEventListener('click', () => location.reload())
  );

  document.querySelectorAll('.btn-guardar').forEach(btn => {
    btn.addEventListener('click', async () => {
      const card = btn.closest('.card');
      const id = btn.dataset.id;
      const estado = card.querySelector('.estadoSelect').value;
      const inicio = card.querySelector('.inicioSusp')?.value || '';
      const fin = card.querySelector('.finSusp')?.value || '';

      const fd = new FormData();
      fd.append('accion', 'actualizar_estado');
      fd.append('id_usuario', id);
      fd.append('estado', estado);
      fd.append('fecha_suspension_inicio', inicio);
      fd.append('fecha_suspension_fin', fin);

      const r = await fetch('admin-clientes.php', { method: 'POST', body: fd });
      const d = await r.json();

      if (d.status === 'ok') {
        Swal.fire({ icon:'success', title:'¡Guardado!', text:d.mensaje, timer:2000, showConfirmButton:false });
        card.querySelector('.estadoSelect').disabled = true;
        card.querySelector('.acciones-guardado').style.display = 'none';
        card.querySelector('.botones-defecto').style.display = 'block';
      } else Swal.fire('Error', '❌ No se pudo actualizar el estado.', 'error');
    });
  });

  document.querySelectorAll('.btn-eliminar').forEach(btn => {
    btn.addEventListener('click', async () => {
      const confirm = await Swal.fire({
        title: '¿Eliminar usuario?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
      });
      if (!confirm.isConfirmed) return;

      const id = btn.dataset.id;
      const fd = new FormData();
      fd.append('accion', 'eliminar_usuario');
      fd.append('id_usuario', id);

      const r = await fetch('admin-clientes.php', { method: 'POST', body: fd });
      const d = await r.json();

      if (d.status === 'ok') {
        Swal.fire({ icon:'success', title:'Eliminado', text:d.mensaje, timer:2000, showConfirmButton:false })
          .then(() => location.reload());
      } else Swal.fire('Error', '❌ No se pudo eliminar el usuario.', 'error');
    });
  });
});
</script>
</body>
</html>

<?php $conn->close(); ?>