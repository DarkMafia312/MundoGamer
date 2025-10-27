<?php
session_start();
include 'conexion.php';

// Evitar errores visibles en salida JSON
ob_start();
error_reporting(0);
ini_set('display_errors', 0);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    header('Content-Type: application/json; charset=utf-8');

    $idUsuario = $_SESSION['usuario']['id'] ?? null;
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $asunto = trim($_POST['asunto'] ?? '');
    $mensaje = trim($_POST['mensaje'] ?? '');

    // Validación básica
    if ($nombre === '' || $apellido === '' || $correo === '' || $asunto === '' || $mensaje === '') {
        echo json_encode(["status" => "error", "mensaje" => "Por favor completa todos los campos obligatorios."]);
        exit;
    }

    // Preparar SQL
    $sql = "INSERT INTO soporte_cliente 
            (id_usuario, nombre, apellido, correo, telefono, asunto, mensaje, fecha_envio, estado)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 'Pendiente')";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        echo json_encode(["status" => "error", "mensaje" => "Error al preparar la consulta: " . $conn->error]);
        exit;
    }

    $stmt->bind_param("issssss", $idUsuario, $nombre, $apellido, $correo, $telefono, $asunto, $mensaje);

    if ($stmt->execute()) {
        echo json_encode(["status" => "ok", "mensaje" => "Tu solicitud fue enviada correctamente."]);
    } else {
        echo json_encode(["status" => "error", "mensaje" => "Error al registrar la solicitud: " . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
    ob_end_flush();
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Ayuda al Cliente</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      background: radial-gradient(circle at top, #0b0f19 30%, #000 100%);
      color: #e0e0e0;
      font-family: 'Trebuchet MS', sans-serif;
    }
    .navbar {
      background: linear-gradient(90deg, #1f0036, #420075, #1f0036);
      border-bottom: 2px solid #00ffc6;
      background-size: 200% 200%;
      animation: gradientMove 8s ease infinite;
    }
    @keyframes gradientMove {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }
    .navbar-brand, .nav-link { color: #fff !important; font-weight: bold; transition: all 0.3s; }
    .nav-link:hover { color: #00ffc6 !important; transform: scale(1.1); }
    h1, h2 { color: #00ffc6; text-shadow: 0 2px 8px rgba(0,255,198,0.8); font-weight: bold; }
    .card {
      background: rgba(255,255,255,0.05);
      border: 2px solid #00ffc6;
      border-radius: 15px;
      box-shadow: 0 0 20px rgba(0,255,198,0.3);
      transition: transform 0.3s, box-shadow 0.3s;
    }
    .card:hover { transform: scale(1.01); box-shadow: 0 0 25px rgba(0,255,198,0.6); }
    .btn-custom {
      background: linear-gradient(45deg,#00ffc6,#00b3ff);
      border: none;
      font-weight: bold;
      color: #000;
      text-transform: uppercase;
      box-shadow: 0 0 15px #00ffc6;
      transition: all 0.3s;
    }
    .btn-custom:hover {
      background: linear-gradient(45deg,#00b3ff,#00ffc6);
      transform: translateY(-3px);
      box-shadow: 0 0 25px #00b3ff;
    }
    label { color: #ffffff !important; font-weight: bold; }
    form { animation: fadeInUp 1s ease; }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
    .accordion-button { background: rgba(0,0,0,0.6); color: #00ffc6; font-weight: bold; transition: all 0.3s; }
    .accordion-button:not(.collapsed) { background: #00ffc6; color: #000; }
    .accordion-body { background: rgba(255,255,255,0.05); color: #e0e0e0; }
    .social-icons a { font-size: 2rem; margin: 0 10px; color: #00ffc6; transition: transform .2s, color .2s; }
    .social-icons a:hover { transform: scale(1.3); color: #00b3ff; }
  </style>
</head>
<body>
  <nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
      <a class="navbar-brand" href="#"><i class="bi bi-life-preserver"></i> Soporte</a>
      <button class="navbar-toggler text-white" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <i class="bi bi-list"></i>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="usuario-index.php">Inicio</a></li>
          <li class="nav-item"><a class="nav-link" href="usuario-galeria.php">Galería</a></li>
          <li class="nav-item"><a class="nav-link" href="usuario-carrito.php">Carrito</a></li>
          <li class="nav-item"><a class="nav-link active" href="ayuda-cliente.php">Ayuda</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container py-5">
    <h1 class="text-center mb-4">📞 Centro de Ayuda al Cliente</h1>

    <div class="card p-4 mb-5">
      <h2>Envíanos tu consulta</h2>
      <form id="ayudaForm" method="POST" novalidate>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Nombre</label>
            <input type="text" class="form-control" name="nombre" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Apellido</label>
            <input type="text" class="form-control" name="apellido" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Correo</label>
            <input type="email" class="form-control" name="correo" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Teléfono</label>
            <input type="tel" class="form-control" name="telefono">
          </div>
          <div class="col-md-6">
            <label class="form-label">Asunto</label>
            <select class="form-select" name="asunto" required>
              <option value="">-- Selecciona un asunto --</option>
              <option>Problemas con la compra</option>
              <option>Consultas sobre membresía</option>
              <option>Soporte técnico</option>
              <option>Reembolso</option>
              <option>Problemas con el login</option>
              <option>Error en la galería</option>
              <option>Carrito no funciona</option>
              <option>Queja o sugerencia</option>
              <option>Otros</option>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label">Mensaje</label>
            <textarea class="form-control" name="mensaje" rows="4" required></textarea>
          </div>
        </div>
        <button type="submit" class="btn btn-custom mt-3">Enviar</button>
      </form>
    </div>

    <!-- 🔹 AYUDA RÁPIDA -->
<section class="container my-5">
  <h2 class="text-center mb-4">⚡ Ayuda Rápida</h2>
  <div class="accordion" id="faqAccordion">

    <div class="accordion-item">
      <h2 class="accordion-header">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
          ¿Cómo puedo restablecer mi contraseña?
        </button>
      </h2>
      <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
        <div class="accordion-body">
          Puedes restablecer tu contraseña desde la opción “Olvidé mi contraseña” en la página de inicio de sesión. 
          Ingresa tu correo registrado y recibirás un enlace para crear una nueva contraseña.
        </div>
      </div>
    </div>

    <div class="accordion-item">
      <h2 class="accordion-header">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
          ¿Cómo solicitar un reembolso?
        </button>
      </h2>
      <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
        <div class="accordion-body">
          Para solicitar un reembolso, completa el formulario de contacto seleccionando “Reembolso” como asunto 
          e indica el código de tu compra junto con una breve descripción del motivo.
        </div>
      </div>
    </div>

    <div class="accordion-item">
      <h2 class="accordion-header">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
          ¿Puedo modificar o cancelar un pedido?
        </button>
      </h2>
      <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
        <div class="accordion-body">
          Sí. Puedes cancelar o modificar tu pedido siempre que aún no haya sido procesado. 
          Comunícate con soporte lo antes posible indicando el número de pedido y los cambios deseados.
        </div>
      </div>
    </div>

    <div class="accordion-item">
      <h2 class="accordion-header">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
          ¿Cómo puedo contactar con soporte técnico?
        </button>
      </h2>
      <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
        <div class="accordion-body">
          Puedes contactarnos a través de este formulario o directamente desde tu cuenta en la sección “Soporte”. 
          También puedes escribirnos a <strong>soporte@empresa.com</strong>.
        </div>
      </div>
    </div>

    <div class="accordion-item">
      <h2 class="accordion-header">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
          ¿Dónde puedo ver el estado de mis solicitudes?
        </button>
      </h2>
      <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
        <div class="accordion-body">
          Una vez enviada tu solicitud, podrás consultar su estado en la sección “Mis solicitudes” dentro de tu perfil. 
          Los estados posibles son: <em>Pendiente</em>, <em>En proceso</em> o <em>Resuelto</em>.
        </div>
      </div>
    </div>

    <div class="accordion-item">
      <h2 class="accordion-header">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq6">
          ¿Qué hago si el sitio web no carga correctamente?
        </button>
      </h2>
      <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
        <div class="accordion-body">
          Intenta borrar la caché del navegador o acceder desde otro dispositivo. 
          Si el problema persiste, repórtalo mediante el formulario de ayuda con el asunto “Soporte técnico”.
        </div>
      </div>
    </div>

    <div class="accordion-item">
      <h2 class="accordion-header">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq7">
          ¿Cuánto tiempo tardan en responder?
        </button>
      </h2>
      <div id="faq7" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
        <div class="accordion-body">
          Generalmente respondemos en un plazo de <strong>24 a 48 horas hábiles</strong>. 
          Si tu caso es urgente, selecciona el asunto correspondiente y lo priorizaremos.
        </div>
      </div>
    </div>

  </div>
</section>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
  document.getElementById("ayudaForm").addEventListener("submit", async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    try {
      const response = await fetch("enviar_ayuda.php", { method: "POST", body: formData });
      const result = await response.json();
      if (result.status === "ok") {
        alert("✅ " + result.mensaje);
        this.reset();
      } else {
        alert("❌ " + result.mensaje);
      }
    } catch (error) {
      console.error(error);
      alert("⚠️ Error de conexión o respuesta inválida del servidor.");
    }
  });
  </script>
</body>
</html>