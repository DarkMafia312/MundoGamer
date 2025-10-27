<?php
include 'conexion.php'; // Tu archivo de conexión (debe crear $conn con MySQLi)

$mensaje = '';
$registroExitoso = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = trim($_POST['registerName'] ?? '');
    $apellido = trim($_POST['registerLastname'] ?? '');
    $username = trim($_POST['registerUsername'] ?? '');
    $correo = trim($_POST['registerEmail'] ?? '');
    $telefono = trim($_POST['registerPhone'] ?? '');
    $fechaNacimiento = trim($_POST['registerBirthdate'] ?? '');
    $direccion = trim($_POST['registerAddress'] ?? '');
    $password = trim($_POST['registerPassword'] ?? '');
    $confirmPassword = trim($_POST['registerConfirmPassword'] ?? '');

    if (empty($nombre) || empty($apellido) || empty($username) || empty($correo) || empty($telefono) || empty($fechaNacimiento) || empty($direccion) || empty($password)) {
        $mensaje = "Todos los campos son obligatorios.";
    } elseif ($password !== $confirmPassword) {
        $mensaje = "Las contraseñas no coinciden.";
    } else {
        // Verificar si el usuario o correo ya existen
        $stmt = $conn->prepare("SELECT COUNT(*) FROM usuarios WHERE username = ? OR correo = ?");
        $stmt->bind_param("ss", $username, $correo);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            $mensaje = "El nombre de usuario o correo ya está en uso.";
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO usuarios (nombre, apellido, username, correo, telefono, fechaNacimiento, direccion, password, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Activo')");
            $stmt->bind_param("ssssssss", $nombre, $apellido, $username, $correo, $telefono, $fechaNacimiento, $direccion, $passwordHash);

            if ($stmt->execute()) {
                $registroExitoso = true;
            } else {
                $mensaje = "Error al registrar el usuario: " . $conn->error;
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Registro de Usuario</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
  background-color:#1a1a1d;
  color:white;
  font-family:Arial, sans-serif;
  overflow-x:hidden;
}
.form-container {
  max-width:500px;
  margin:60px auto;
  padding:30px;
  background:#222;
  border-radius:12px;
  box-shadow:0 0 15px rgba(0,0,0,0.5);
  position:relative;
  z-index:2;
  animation: fadeIn 1s ease-in-out;
}
h2 { text-align:center; margin-bottom:20px; color:#4aff47;}
.btn-custom {
  background-color:#4aff47;
  color:black;
  font-weight:bold;
  width:100%;
}
.btn-custom:hover { background-color:#39cc39; color:white; }
#passwordStrength {
  height:8px;
  border-radius:5px;
  display:none;
}
.success-overlay {
  position:fixed;
  top:0; left:0;
  width:100%; height:100%;
  background:radial-gradient(circle at center,#0f0f0f,#1a1a1d,#000);
  display:flex;
  flex-direction:column;
  justify-content:center;
  align-items:center;
  color:#4aff47;
  font-size:24px;
  font-family:monospace;
  z-index:9999;
  animation: fadeIn 0.8s ease-in-out forwards;
}
.success-overlay .checkmark {
  width:80px;
  height:80px;
  border-radius:50%;
  border:6px solid #4aff47;
  position:relative;
  margin-bottom:15px;
  animation: pop 0.6s ease-out;
}
.success-overlay .checkmark::after {
  content:"";
  position:absolute;
  left:20px;
  top:10px;
  width:25px;
  height:50px;
  border-right:6px solid #4aff47;
  border-bottom:6px solid #4aff47;
  transform:rotate(45deg);
  animation: draw 0.6s ease-out 0.3s forwards;
  opacity:0;
}
@keyframes pop {
  0% { transform:scale(0.5); opacity:0; }
  100% { transform:scale(1); opacity:1; }
}
@keyframes draw {
  to { opacity:1; }
}
@keyframes fadeIn {
  from {opacity:0;}
  to {opacity:1;}
}
</style>
</head>
<body>

<div class="form-container">
    <h2>Registro de Usuario</h2>

    <?php if($mensaje): ?>
        <div class="alert alert-info text-center"><?php echo $mensaje; ?></div>
    <?php endif; ?>

    <form method="POST" id="registerForm">
        <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input type="text" class="form-control" name="registerName" required pattern="[A-Za-zÁÉÍÓÚÑáéíóúñ ]{2,30}">
        </div>
        <div class="mb-3">
            <label class="form-label">Apellido</label>
            <input type="text" class="form-control" name="registerLastname" required pattern="[A-Za-zÁÉÍÓÚÑáéíóúñ ]{2,30}">
        </div>
        <div class="mb-3">
            <label class="form-label">Nombre de Usuario</label>
            <input type="text" class="form-control" name="registerUsername" required minlength="4" maxlength="20">
        </div>
        <div class="mb-3">
            <label class="form-label">Correo electrónico</label>
            <input type="email" class="form-control" name="registerEmail" required pattern="^[\\w._%+-]+@(gmail|outlook)\\.com$">
        </div>
        <div class="mb-3">
            <label class="form-label">Teléfono</label>
            <input type="tel" class="form-control" name="registerPhone" required pattern="^[0-9]{9}$" maxlength="9">
        </div>
        <div class="mb-3">
            <label class="form-label">Fecha de Nacimiento</label>
            <input type="date" class="form-control" name="registerBirthdate" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Dirección</label>
            <input type="text" class="form-control" name="registerAddress" required minlength="5" maxlength="100">
        </div>
        <div class="mb-3">
            <label class="form-label">Contraseña</label>
            <input type="password" class="form-control" name="registerPassword" required id="registerPassword">
            <div class="mt-1">
                <div id="passwordStrength" class="bg-danger w-0"></div>
                <small id="passwordFeedback"></small>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Confirmar Contraseña</label>
            <input type="password" class="form-control" name="registerConfirmPassword" required>
        </div>
        <button type="submit" class="btn btn-custom">Registrarse</button>
        <p class="text-center mt-3">¿Ya tienes cuenta? 
          <a href="usuario-login.php" class="text-success">Inicia sesión</a></p>
    </form>
</div>

<?php if($registroExitoso): ?>
<div class="success-overlay" id="successOverlay">
  <div class="checkmark"></div>
  <p>✅ Registro completado correctamente</p>
  <small>Redirigiendo al inicio de sesión...</small>
</div>
<script>
setTimeout(()=>{ window.location.href = "usuario-login.php"; }, 2500);
</script>
<?php endif; ?>

<script>
const passwordInput = document.getElementById('registerPassword');
const strengthBar = document.getElementById('passwordStrength');
const feedback = document.getElementById('passwordFeedback');

passwordInput.addEventListener('input', () => {
    const pwd = passwordInput.value;
    if(pwd.length === 0){
        strengthBar.style.display="none";
        feedback.textContent="";
        return;
    } else {
        strengthBar.style.display="block";
    }

    let strength = 0;
    if(pwd.length >= 8) strength++;
    if(/[A-Z]/.test(pwd)) strength++;
    if(/[a-z]/.test(pwd)) strength++;
    if(/[0-9]/.test(pwd)) strength++;
    if(/[@$!%*?&]/.test(pwd)) strength++;

    switch(strength) {
        case 1: strengthBar.style.width="20%"; strengthBar.className="bg-danger w-20"; feedback.textContent="Débil ❌"; break;
        case 2: case 3: strengthBar.style.width="60%"; strengthBar.className="bg-warning w-60"; feedback.textContent="Moderada ⚠️"; break;
        case 4: case 5: strengthBar.style.width="100%"; strengthBar.className="bg-success w-100"; feedback.textContent="Fuerte ✅"; break;
    }
});
</script>

</body>
</html>