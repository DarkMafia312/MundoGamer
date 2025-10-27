<?php
require __DIR__ . '/vendor/autoload.php';
include('conexion.php'); // tu conexión normal

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if (isset($_GET['exportar']) && $_GET['exportar'] === 'excel') {
    ob_clean(); // limpia cualquier salida previa
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="trabajadores_MundoGamer.xlsx"');
    header('Cache-Control: max-age=0');

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    $sheet->setCellValue('A1', 'ID')
          ->setCellValue('B1', 'Nombres')
          ->setCellValue('C1', 'Apellidos')
          ->setCellValue('D1', 'DNI')
          ->setCellValue('E1', 'Correo')
          ->setCellValue('F1', 'Puesto')
          ->setCellValue('G1', 'Fecha Contratación')
          ->setCellValue('H1', 'Sueldo')
          ->setCellValue('I1', 'Estado')
          ->setCellValue('J1', 'Fecha Despido')
          ->setCellValue('K1', 'Inicio Vacaciones')
          ->setCellValue('L1', 'Fin Vacaciones')
          ->setCellValue('M1', 'Liquidación');

    $query = "SELECT * FROM trabajadores ORDER BY id ASC";
    $result = $conn->query($query);
    $rowNum = 2;
    while ($row = $result->fetch_assoc()) {
        $sheet->setCellValue("A{$rowNum}", $row['id'])
              ->setCellValue("B{$rowNum}", $row['nombres'])
              ->setCellValue("C{$rowNum}", $row['apellidos'])
              ->setCellValue("D{$rowNum}", $row['dni'])
              ->setCellValue("E{$rowNum}", $row['correo'])
              ->setCellValue("F{$rowNum}", $row['puesto'])
              ->setCellValue("G{$rowNum}", $row['fechaContratacion'])
              ->setCellValue("H{$rowNum}", $row['sueldo'])
              ->setCellValue("I{$rowNum}", $row['estado'])
              ->setCellValue("J{$rowNum}", $row['fechaDespido'])
              ->setCellValue("K{$rowNum}", $row['fechaInicioVacaciones'])
              ->setCellValue("L{$rowNum}", $row['fechaFinVacaciones'])
              ->setCellValue("M{$rowNum}", $row['liquidacion']);
        $rowNum++;
    }

    foreach (range('A', 'M') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    $sheet->getStyle('A1:M1')->getFont()->setBold(true)->setSize(12);

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit();
}

// === FUNCIÓN LIMPIAR ===
function clean($data, $conn) {
    return htmlspecialchars(trim($conn->real_escape_string($data)));
}

// === PETICIÓN AJAX (GUARDAR / ACTUALIZAR / ELIMINAR / DESPEDIR / LISTAR) ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['listar'])) {
    header('Content-Type: application/json; charset=utf-8');

    try {
        // === GUARDAR O ACTUALIZAR ===
        if (isset($_POST['accion']) && $_POST['accion'] === 'guardar') {
            $nombres = clean($_POST['nombres'] ?? '', $conn);
            $apellidos = clean($_POST['apellidos'] ?? '', $conn);
            $dni = clean($_POST['dni'] ?? '', $conn);
            $correo = clean($_POST['correo'] ?? '', $conn);
            $fechaNacimiento = $_POST['fechaNacimiento'] ?? null;
            $puesto = clean($_POST['puesto'] ?? '', $conn);
            $fechaContratacion = $_POST['fechaContratacion'] ?? null;
            $sueldo = isset($_POST['sueldo']) && $_POST['sueldo'] !== '' ? (float)$_POST['sueldo'] : 0;
            $estado = clean($_POST['estado'] ?? 'activo', $conn);
            $fechaInicioSuspension = $_POST['fechaInicioSuspension'] ?? null;
            $fechaFinSuspension = $_POST['fechaFinSuspension'] ?? null;
            $fechaDespido = $_POST['fechaDespido'] ?? null;
            $fechaInicioVacaciones = $_POST['fechaInicioVacaciones'] ?? null;
            $fechaFinVacaciones = $_POST['fechaFinVacaciones'] ?? null;
            $liquidacion = isset($_POST['liquidacion']) && $_POST['liquidacion'] !== '' ? (float)$_POST['liquidacion'] : 0;
            $editId = isset($_POST['editIndex']) && $_POST['editIndex'] !== '' ? (int)$_POST['editIndex'] : null;

            // Validar campos obligatorios
            if ($nombres === '' || $apellidos === '' || $dni === '' || $correo === '' || $puesto === '') {
                echo json_encode(['success' => false, 'error' => 'Faltan campos obligatorios']);
                exit();
            }

            if ($editId) {
                // === ACTUALIZAR ===
                $stmt = $conn->prepare("UPDATE trabajadores SET 
                    nombres=?, apellidos=?, dni=?, correo=?, fechaNacimiento=?, puesto=?, 
                    fechaContratacion=?, sueldo=?, estado=?, fechaInicioSuspension=?, fechaFinSuspension=?, 
                    fechaInicioVacaciones=?, fechaFinVacaciones=?, fechaDespido=?, liquidacion=? 
                    WHERE id=?");

                $stmt->bind_param(
                    "sssssssdsdssssdi",
                    $nombres,
                    $apellidos,
                    $dni,
                    $correo,
                    $fechaNacimiento,
                    $puesto,
                    $fechaContratacion,
                    $sueldo,
                    $estado,
                    $fechaInicioSuspension,
                    $fechaFinSuspension,
                    $fechaInicioVacaciones,
                    $fechaFinVacaciones,
                    $fechaDespido,
                    $liquidacion,
                    $editId
                );
                $accion = "update";
            } else {
                // === NUEVO REGISTRO ===
                $stmt = $conn->prepare("INSERT INTO trabajadores 
                    (nombres, apellidos, dni, correo, fechaNacimiento, puesto, 
                     fechaContratacion, sueldo, estado, fechaInicioSuspension, fechaFinSuspension, 
                     fechaInicioVacaciones, fechaFinVacaciones, fechaDespido, liquidacion)
                     VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

                $stmt->bind_param(
                    "sssssssdsdssssd",
                    $nombres,
                    $apellidos,
                    $dni,
                    $correo,
                    $fechaNacimiento,
                    $puesto,
                    $fechaContratacion,
                    $sueldo,
                    $estado,
                    $fechaInicioSuspension,
                    $fechaFinSuspension,
                    $fechaInicioVacaciones,
                    $fechaFinVacaciones,
                    $fechaDespido,
                    $liquidacion
                );
                $accion = "insert";
            }

            $ok = ($stmt && $stmt->execute());
            $errorMsg = $stmt ? $stmt->error : $conn->error;
            $stmt->close();

            echo json_encode([
                'success' => $ok,
                'action' => $accion,
                'error' => $ok ? null : $errorMsg
            ]);
            exit();
        }

        // === DESPEDIR ===
        if (isset($_POST['accion']) && $_POST['accion'] === 'despedir') {
            $id = (int)($_POST['id'] ?? 0);
            $hoy = date("Y-m-d");

            $stmt = $conn->prepare("SELECT sueldo FROM trabajadores WHERE id=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $res = $stmt->get_result();

            if ($row = $res->fetch_assoc()) {
                $liquidacion = $row['sueldo'] * 2;
                $stmt2 = $conn->prepare("UPDATE trabajadores 
                    SET estado='despedido', fechaDespido=?, liquidacion=? WHERE id=?");
                $stmt2->bind_param("sdi", $hoy, $liquidacion, $id);
                $stmt2->execute();
                $stmt2->close();
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'No se encontró el trabajador']);
            }

            $stmt->close();
            exit();
        }

        // === ELIMINAR ===
        if (isset($_POST['accion']) && $_POST['accion'] === 'eliminar') {
            $id = (int)($_POST['id'] ?? 0);
            $stmt = $conn->prepare("DELETE FROM trabajadores WHERE id=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            echo json_encode(['success' => true]);
            exit();
        }

        // === LISTAR ===
        if (isset($_GET['listar'])) {
            $result = $conn->query("SELECT * FROM trabajadores ORDER BY id ASC");
            $trabajadores = [];

            while ($row = $result->fetch_assoc()) {
                $trabajadores[] = [
                    'id' => $row['id'],
                    'nombres' => $row['nombres'],
                    'apellidos' => $row['apellidos'],
                    'dni' => $row['dni'],
                    'correo' => $row['correo'],
                    'fechaNacimiento' => $row['fechaNacimiento'] ?? null,
                    'puesto' => $row['puesto'],
                    'fechaContratacion' => $row['fechaContratacion'] ?? null,
                    'sueldo' => $row['sueldo'] ?? 0,
                    'estado' => $row['estado'] ?? 'activo',
                    'fechaInicioSuspension' => $row['fechaInicioSuspension'] ?? null,
                    'fechaFinSuspension' => $row['fechaFinSuspension'] ?? null,
                    'fechaInicioVacaciones' => $row['fechaInicioVacaciones'] ?? null,
                    'fechaFinVacaciones' => $row['fechaFinVacaciones'] ?? null,
                    'fechaDespido' => $row['fechaDespido'] ?? null,
                    'liquidacion' => $row['liquidacion'] ?? 0
                ];
            }
            echo json_encode($trabajadores);
            exit();
        }

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit();
    }
    exit();
}

// === LISTAR PARA LA VISTA (HTML) ===
$result = $conn->query("SELECT * FROM trabajadores ORDER BY id ASC");
$trabajadores = [];

while ($row = $result->fetch_assoc()) {
    $trabajadores[] = [
        'id' => $row['id'],
        'nombres' => $row['nombres'],
        'apellidos' => $row['apellidos'],
        'dni' => $row['dni'],
        'correo' => $row['correo'],
        'fechaNacimiento' => $row['fechaNacimiento'] ?? null,
        'puesto' => $row['puesto'],
        'fechaContratacion' => $row['fechaContratacion'] ?? null,
        'sueldo' => $row['sueldo'] ?? 0,
        'estado' => $row['estado'] ?? 'activo',
        'fechaInicioSuspension' => $row['fechaInicioSuspension'] ?? null,
        'fechaFinSuspension' => $row['fechaFinSuspension'] ?? null,
        'fechaInicioVacaciones' => $row['fechaInicioVacaciones'] ?? null,
        'fechaFinVacaciones' => $row['fechaFinVacaciones'] ?? null,
        'fechaDespido' => $row['fechaDespido'] ?? null,
        'liquidacion' => $row['liquidacion'] ?? 0
    ];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Gestión de Trabajadores</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<style>
html, body { height:100%; margin:0; background:#121212; color:#eaeaea; font-family:"Segoe UI", sans-serif; }
#loader{position:fixed;top:0;left:0;width:100%;height:100%;background:radial-gradient(circle at center,#0a0a1a,#121212 80%);display:flex;flex-direction:column;justify-content:center;align-items:center;z-index:2000;overflow:hidden;}
.particle{position:absolute;width:6px;height:6px;background:linear-gradient(45deg,#00e6e6,#ff00ff);border-radius:50%;opacity:0.7;animation:moveParticle linear infinite;}
@keyframes moveParticle{0%{transform:translateY(0) scale(1);opacity:0.7;}50%{transform:translateY(-50px) scale(1.3);opacity:1;}100%{transform:translateY(0) scale(1);opacity:0.7;}}
.loader-animation{display:flex;gap:6px;z-index:10;position:relative;}
.loader-animation div{width:14px;height:14px;background:linear-gradient(45deg,#00e6e6,#ff00ff);border-radius:50%;animation:bounce 1s infinite alternate;}
.loader-animation div:nth-child(2){animation-delay:0.1s;}
.loader-animation div:nth-child(3){animation-delay:0.2s;}
.loader-animation div:nth-child(4){animation-delay:0.3s;}
.loader-animation div:nth-child(5){animation-delay:0.4s;}
@keyframes bounce{0%{transform:translateY(0);opacity:0.6;}50%{transform:translateY(-15px);opacity:1;}100%{transform:translateY(0);opacity:0.6;}}
.loader-text{margin-top:20px;font-size:1.3rem;color:#00e6e6;letter-spacing:1px;text-shadow:0 0 6px #ff00ff,0 0 12px #00e6e6;animation:glowText 1.5s infinite alternate;}
@keyframes glowText{from{text-shadow:0 0 6px #ff00ff,0 0 12px #00e6e6;}to{text-shadow:0 0 12px #00ffdd,0 0 24px #ff00ff;}}
body.show-content{opacity:1;transition:opacity 0.7s ease-in;} body.hidden{opacity:0;}
h1{text-shadow:0 0 8px #00ff88;}
.trabajador-card{background:#1e1e1e;border:1px solid #333;border-radius:12px;padding:14px;color:#fff;transition:0.3s;margin-bottom:15px;}
.trabajador-card:hover{transform:translateY(-5px);box-shadow:0 6px 18px rgba(0,255,150,0.4);}
.estado-activo{color:#00ff6a;font-weight:bold;}
.estado-suspendido{color:#ffcc00;font-weight:bold;}
.estado-vacaciones{color:#0099ff;font-weight:bold;}
.estado-despedido{color:#ff3b3b;font-weight:bold;}
.btn-accion{border-radius:8px;font-size:0.85rem;}
#mensaje{position:fixed;top:20px;right:20px;z-index:3000;display:none;padding:12px 18px;border-radius:8px;font-weight:bold;}
</style>
</head>
<body class="hidden">
<div id="loader">
  <div class="loader-animation"><div></div><div></div><div></div><div></div><div></div></div>
  <div class="loader-text">Cargando Trabajadores...</div>
</div>
<div id="mensaje"></div>
<div class="container mt-4">
  <header class="mb-4 text-center">
    <h1><i class="fas fa-user-gear"></i> Gestión de Trabajadores</h1>
  </header>
    
  <div class="d-flex justify-content-between mb-3">
    <button class="btn btn-warning" onclick="limpiarFormulario();"><i class="fas fa-broom"></i> Limpiar Formulario</button>
    <a href="admin-dashboard.php" class="btn btn-outline-light"><i class="fas fa-arrow-left"></i> Volver al Dashboard</a>
  </div>
    
<form id="formTrabajador" class="row g-3">
  <input type="hidden" name="editIndex" id="editIndex">
  <input type="hidden" name="accion" value="guardar">

  <div class="col-md-6">
    <label class="form-label">Nombres</label>
    <input type="text" class="form-control" name="nombres" id="nombres" required>
  </div>
  <div class="col-md-6">
    <label class="form-label">Apellidos</label>
    <input type="text" class="form-control" name="apellidos" id="apellidos" required>
  </div>
  <div class="col-md-4">
    <label class="form-label">DNI</label>
    <input type="text" class="form-control" name="dni" id="dni" pattern="^[0-9]{8}$" required>
  </div>
  <div class="col-md-4">
    <label class="form-label">Correo</label>
    <input type="email" class="form-control" name="correo" id="correo" required>
  </div>
  <div class="col-md-4">
    <label class="form-label">Fecha Nacimiento</label>
    <input type="date" class="form-control" name="fechaNacimiento" id="fechaNacimiento" required>
  </div>
  <div class="col-md-4">
    <label class="form-label">Puesto</label>
    <select class="form-select" name="puesto" id="puesto" required>
      <option value="">Seleccione...</option>
      <option>Gerente</option>
      <option>Administrador de Sistemas</option>
      <option>Administrador de la Empresa</option>
      <option>Marketing - Gestor de Comunidad</option>
      <option>Trabajador de Almacén</option>
      <option>Marketing - Especialista en Marketing Digital</option>
      <option>Gerente de Contenido/Productos</option>
      <option>Recursos Humanos</option>
      <option>Economista/Financiero</option>
    </select>
  </div>
  <div class="col-md-4">
    <label class="form-label">Fecha Contratación</label>
    <input type="date" class="form-control" name="fechaContratacion" id="fechaContratacion" required>
  </div>
  <div class="col-md-4">
    <label class="form-label">Sueldo ($)</label>
    <input type="number" class="form-control" name="sueldo" id="sueldo" min="300" required>
  </div>

  <!-- Estado -->
  <div class="col-md-4">
    <label class="form-label">Estado</label>
    <select class="form-select" name="estado" id="estado" required>
      <option value="activo">Activo</option>
      <option value="suspendido">Suspendido</option>
      <option value="vacaciones">Vacaciones</option>
      <option value="despedido">Despedido</option>
    </select>
  </div>

  <!-- Campos dinámicos -->
  <div class="col-md-6" id="diasSuspendidoContainer" style="display:none;">
    <label class="form-label">Inicio Suspensión</label>
    <input type="date" class="form-control" name="fechaInicioSuspension" id="fechaInicioSuspension">
  </div>
  <div class="col-md-6" id="diasSuspendidoFinContainer" style="display:none;">
    <label class="form-label">Fin Suspensión</label>
    <input type="date" class="form-control" name="fechaFinSuspension" id="fechaFinSuspension">
  </div>

  <div class="col-md-6" id="vacacionesContainer" style="display:none;">
    <label class="form-label">Inicio Vacaciones</label>
    <input type="date" class="form-control" name="fechaInicioVacaciones" id="fechaInicioVacaciones">
  </div>
  <div class="col-md-6" id="vacacionesFinContainer" style="display:none;">
    <label class="form-label">Fin Vacaciones (máx 2 meses)</label>
    <input type="date" class="form-control" name="fechaFinVacaciones" id="fechaFinVacaciones">
  </div>

  <div class="col-12 d-flex justify-content-end">
    <button type="submit" id="guardarBtn" class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button>
    <button type="button" id="cancelarBtn" class="btn btn-secondary" style="display:none;">Cancelar</button>
  </div>
</form>

<a href="admin-trabajadores.php?exportar=excel" 
   style="background:#00ffcc; color:#000; padding:10px 20px; border-radius:8px; text-decoration:none; font-weight:bold;">
   📥 Exportar a Excel
</a>
    
<hr>
<input type="text" id="buscadorTrabajador" class="form-control mb-3" placeholder="🔎 Buscar trabajador...">
<div id="listaTrabajadores" class="row g-3"></div>
</div>

<script>
let trabajadores = <?= json_encode($trabajadores) ?>;

// 🌀 Loader animado
window.addEventListener("load", () => {
  const loader = document.getElementById("loader");
  const body = document.body;
  for (let i = 0; i < 50; i++) {
    const p = document.createElement("div");
    p.classList.add("particle");
    p.style.top = Math.random() * 100 + "%";
    p.style.left = Math.random() * 100 + "%";
    p.style.animationDuration = 2 + Math.random() * 3 + "s";
    p.style.width = 4 + Math.random() * 6 + "px";
    p.style.height = p.style.width;
    loader.appendChild(p);
  }
  setTimeout(() => {
    loader.style.transition = "opacity 0.5s ease";
    loader.style.opacity = 0;
    setTimeout(() => {
      loader.style.display = "none";
      body.classList.remove("hidden");
      body.classList.add("show-content");
      renderTrabajadores();
    }, 500);
  }, 1000);
});

// 📩 Mostrar mensajes dinámicos
function showMessage(msg, type = "info") {
  const mensaje = document.getElementById("mensaje");
  mensaje.innerText = msg;
  mensaje.style.background =
    type === "success"
      ? "#28a745"
      : type === "danger"
      ? "#dc3545"
      : type === "warning"
      ? "#ffc107"
      : "#17a2b8";
  mensaje.style.display = "block";
  setTimeout(() => (mensaje.style.display = "none"), 3000);
}

// 🧮 Calcular edad
function calcularEdad(fecha) {
  if (!fecha) return "—";
  const nacimiento = new Date(fecha);
  const hoy = new Date();
  let edad = hoy.getFullYear() - nacimiento.getFullYear();
  const m = hoy.getMonth() - nacimiento.getMonth();
  if (m < 0 || (m === 0 && hoy.getDate() < nacimiento.getDate())) edad--;
  return edad;
}

// 🎨 Renderizar tarjetas de trabajadores
function renderTrabajadores() {
  const contenedor = document.getElementById("listaTrabajadores");
  const filtro = document.getElementById("buscadorTrabajador").value.toLowerCase();
  contenedor.innerHTML = "";
  trabajadores.forEach((t, index) => {
    if (
      t.nombres.toLowerCase().includes(filtro) ||
      t.apellidos.toLowerCase().includes(filtro)
    ) {
      const card = document.createElement("div");
      card.classList.add("col-md-4", "trabajador-card");
      card.innerHTML = `
        <div>
          <h5><i class="fas fa-id-card"></i> ID: ${t.id}</h5>
          <p><i class="fas fa-user"></i> ${t.nombres} ${t.apellidos}</p>
          <p><i class="fas fa-cake-candles"></i> Edad: ${calcularEdad(t.fechaNacimiento)}</p>
          <p><i class="fas fa-briefcase"></i> Puesto: ${t.puesto}</p>
          <p><i class="fas fa-envelope"></i> ${t.correo}</p>
          <p><i class="fas fa-calendar-plus"></i> Contratación: ${t.fechaContratacion}</p>
          <p><i class="fas fa-dollar-sign"></i> Sueldo: $${t.sueldo}</p>
          ${
            t.estado === "despedido"
              ? `<p><i class="fas fa-calendar-xmark"></i> Despedido el: ${t.fechaDespido}</p><p><i class="fas fa-sack-dollar"></i> Liquidación: $${t.liquidacion || 0}</p>`
              : ""
          }
          ${
            t.estado === "suspendido"
              ? `<p><i class="fas fa-calendar-minus"></i> Suspensión: ${t.fechaInicioSuspension || "—"} a ${t.fechaFinSuspension || "—"}</p>`
              : ""
          }
          ${
            t.estado === "vacaciones"
              ? `<p><i class="fas fa-umbrella-beach"></i> Vacaciones: ${t.fechaInicioVacaciones || "—"} a ${t.fechaFinVacaciones || "—"}</p>`
              : ""
          }
          <p><i class="fas fa-circle"></i> Estado: <span class="estado-${t.estado}">${t.estado}</span></p>
          <div class="d-flex justify-content-between mt-2">
            ${
              t.estado !== "despedido"
                ? `
              <button class="btn btn-warning btn-sm btn-accion" onclick="editarTrabajador(${index})"><i class='fas fa-pen'></i> Editar</button>
              <button class="btn btn-danger btn-sm btn-accion" onclick="despedirTrabajador(${t.id})"><i class='fas fa-user-xmark'></i> Despedir</button>
              `
                : `<button class="btn btn-outline-danger btn-sm btn-accion" onclick="eliminarTrabajador(${t.id})"><i class='fas fa-trash'></i> Eliminar</button>`
            }
          </div>
        </div>`;
      contenedor.appendChild(card);
    }
  });
}

// 🧼 Limpiar formulario
function limpiarFormulario() {
  document.getElementById("formTrabajador").reset();
  document.getElementById("editIndex").value = "";
  document.getElementById("diasSuspendidoContainer").style.display = "none";
  document.getElementById("diasSuspendidoFinContainer").style.display = "none";
  document.getElementById("vacacionesContainer").style.display = "none";
  document.getElementById("vacacionesFinContainer").style.display = "none";
  document.getElementById("cancelarBtn").style.display = "none";
}

// 🔄 Mostrar campos dinámicos según estado
const estadoSelect = document.getElementById("estado");

estadoSelect.addEventListener("change", () => {
  const estado = estadoSelect.value;
  document.getElementById("diasSuspendidoContainer").style.display = estado === "suspendido" ? "block" : "none";
  document.getElementById("diasSuspendidoFinContainer").style.display = estado === "suspendido" ? "block" : "none";
  document.getElementById("vacacionesContainer").style.display = estado === "vacaciones" ? "block" : "none";
  document.getElementById("vacacionesFinContainer").style.display = estado === "vacaciones" ? "block" : "none";

  const hoy = new Date().toISOString().split("T")[0];
  if (estado === "suspendido") {
    document.getElementById("fechaInicioSuspension").min = hoy;
    document.getElementById("fechaFinSuspension").min = hoy;
  }
  if (estado === "vacaciones") {
    document.getElementById("fechaInicioVacaciones").min = hoy;
    document.getElementById("fechaFinVacaciones").min = hoy;
  }
});

// ✏️ Editar trabajador
function editarTrabajador(index) {
  const t = trabajadores[index];
  document.getElementById("editIndex").value = t.id;
  document.getElementById("nombres").value = t.nombres;
  document.getElementById("apellidos").value = t.apellidos;
  document.getElementById("dni").value = t.dni;
  document.getElementById("correo").value = t.correo;
  document.getElementById("fechaNacimiento").value = t.fechaNacimiento;
  document.getElementById("puesto").value = t.puesto;
  document.getElementById("fechaContratacion").value = t.fechaContratacion;
  document.getElementById("sueldo").value = t.sueldo;
  document.getElementById("estado").value = t.estado;

  // Mostrar campos adicionales según estado
  estadoSelect.dispatchEvent(new Event("change"));

  if (t.estado === "suspendido") {
    document.getElementById("fechaInicioSuspension").value = t.fechaInicioSuspension || "";
    document.getElementById("fechaFinSuspension").value = t.fechaFinSuspension || "";
  }
  if (t.estado === "vacaciones") {
    document.getElementById("fechaInicioVacaciones").value = t.fechaInicioVacaciones || "";
    document.getElementById("fechaFinVacaciones").value = t.fechaFinVacaciones || "";
  }

  document.getElementById("cancelarBtn").style.display = "inline-block";
}

// 💾 Guardar o actualizar trabajador
document.getElementById("formTrabajador").addEventListener("submit", function (e) {
  e.preventDefault();

  const editId = document.getElementById("editIndex").value.trim();
  const esEdicion = editId !== "";
  const formData = new FormData(this);
  formData.append("accion", "guardar");

  fetch("admin-trabajadores.php", { method: "POST", body: formData })
    .then(async (res) => {
      if (!res.ok) throw new Error("Respuesta HTTP no válida");
      const text = await res.text();
      try {
        return JSON.parse(text);
      } catch {
        console.error("Respuesta no JSON:", text);
        throw new Error("Respuesta inválida del servidor");
      }
    })
    .then((res) => {
      if (res.success) {
        fetch("admin-trabajadores.php?listar=1")
          .then((r) => r.json())
          .then((data) => {
            trabajadores = data;
            renderTrabajadores();
            limpiarFormulario();
            const msg = esEdicion
              ? "Cambios actualizados correctamente"
              : "Nuevo trabajador guardado correctamente";
            showMessage(msg, "success");
          });
      } else {
        showMessage("Error al guardar: " + (res.error || "Error desconocido"), "danger");
      }
    })
    .catch((err) => {
      console.error("Error en fetch:", err);
      showMessage("⚠️ No se pudo comunicar con el servidor", "danger");
    });
});

// 🟡 Botón cancelar
document.getElementById("cancelarBtn").addEventListener("click", function () {
  limpiarFormulario();
  showMessage("Acción cancelada", "warning");
});

// 🔴 Despedir trabajador
function despedirTrabajador(id) {
  if (!confirm("¿Seguro que deseas despedir a este trabajador?")) return;
  const formData = new FormData();
  formData.append("accion", "despedir");
  formData.append("id", id);
  fetch("admin-trabajadores.php", { method: "POST", body: formData })
    .then((r) => r.json())
    .then((res) => {
      if (res.success) {
        showMessage("Trabajador despedido correctamente", "danger");
        fetch("admin-trabajadores.php?listar=1")
          .then((r) => r.json())
          .then((data) => {
            trabajadores = data;
            renderTrabajadores();
          });
      }
    });
}

// ❌ Eliminar trabajador
function eliminarTrabajador(id) {
  if (!confirm("¿Eliminar permanentemente este trabajador?")) return;
  const formData = new FormData();
  formData.append("accion", "eliminar");
  formData.append("id", id);
  fetch("admin-trabajadores.php", { method: "POST", body: formData })
    .then((r) => r.json())
    .then((res) => {
      if (res.success) {
        showMessage("Trabajador eliminado correctamente", "warning");
        trabajadores = trabajadores.filter((t) => t.id != id);
        renderTrabajadores();
      }
    });
}
// 🔍 Búsqueda instantánea
document.getElementById("buscadorTrabajador").addEventListener("input", renderTrabajadores);
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>