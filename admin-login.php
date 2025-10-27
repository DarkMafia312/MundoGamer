<?php
session_start();
include('conexion.php');

// Si ya está logueado, redirigir directamente
if (isset($_SESSION['usuario_admin'])) {
    header("Location: admin-dashboard.php");
    exit();
}

// Inicializa contador de intentos
if (!isset($_SESSION['intentos'])) $_SESSION['intentos'] = 0;
if (!isset($_SESSION['bloqueo'])) $_SESSION['bloqueo'] = 0;

$error = "";

// Calcula tiempo restante si está bloqueado
$tiempoRestante = 0;
if ($_SESSION['bloqueo'] > time()) {
    $tiempoRestante = $_SESSION['bloqueo'] - time();
}

// Procesa login si no está bloqueado
if (isset($_POST['login']) && $_SESSION['bloqueo'] <= time()) {
    $usuario = trim($_POST['usuario']);
    $contrasena = trim($_POST['contrasena']);

    $stmt = $conn->prepare("SELECT * FROM administradores WHERE usuario_admin = ?");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $admin = $resultado->fetch_assoc();

        if (password_verify($contrasena, $admin['contrasena']) || $contrasena === $admin['contrasena']) {
            $_SESSION['usuario_admin'] = $admin['usuario_admin'];
            $conn->query("UPDATE administradores SET ultimo_login = NOW() WHERE id_admin = {$admin['id_admin']}");
            $_SESSION['intentos'] = 0;
            $_SESSION['bloqueo'] = 0;
            header("Location: admin-dashboard.php");
            exit();
        } else {
            $_SESSION['intentos']++;
            $error = "Contraseña incorrecta.";

            // Bloqueo después de 4 intentos
            if ($_SESSION['intentos'] >= 4) {
                $_SESSION['bloqueo'] = time() + 30 * ($_SESSION['intentos'] - 3); // incrementa 30s por fallo extra
            }
        }
    } else {
        $_SESSION['intentos']++;
        $error = "Usuario no encontrado.";
        if ($_SESSION['intentos'] >= 4) {
            $_SESSION['bloqueo'] = time() + 30 * ($_SESSION['intentos'] - 3);
        }
    }
}

// Tiempo restante actualizado
if ($_SESSION['bloqueo'] > time()) {
    $tiempoRestante = $_SESSION['bloqueo'] - time();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login Administrador - MundoGamer</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>
body {
    background: radial-gradient(circle at top left, #0f0f0f, #1a1a1a, #000);
    color: white;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    font-family: 'Poppins', sans-serif;
    overflow: hidden;
    animation: fadeBody 1.2s ease-in-out;
}
@keyframes fadeBody {
    from {opacity: 0;} to {opacity: 1;}
}
.login-container {
    background: rgba(25,25,25,0.95);
    padding: 3rem;
    border-radius: 20px;
    box-shadow: 0 0 35px rgba(0,255,100,0.3);
    max-width: 400px;
    width: 100%;
    text-align: center;
    transform: scale(0);
    animation: zoomIn 0.8s forwards;
}
@keyframes zoomIn {
    to {transform: scale(1);}
}
h2 {
    color: #00ff6a;
    text-shadow: 0 0 10px #00ff6a;
    margin-bottom: 2rem;
    animation: glowText 2s infinite alternate;
}
@keyframes glowText {
    from {text-shadow: 0 0 5px #00ff6a, 0 0 10px #00ff6a;}
    to {text-shadow: 0 0 20px #00ff6a, 0 0 40px #00ff6a;}
}
input { background: #111; color: #fff; border: 1px solid #00ff6a; margin-bottom: 10px; transition: 0.3s; }
input:focus { box-shadow: 0 0 10px #00ff6a; border-color: #00ff6a; }
.btn-gamer {
    background: linear-gradient(90deg, #00ff6a, #00c853);
    color: black;
    font-weight: bold;
    border: none;
    border-radius: 8px;
    transition: 0.3s;
}
.btn-gamer:hover {
    transform: scale(1.05);
    box-shadow: 0 0 15px #00ff6a;
}
#temporizador {
    position: fixed;
    top: 15px;
    right: 15px;
    background: rgba(255, 0, 0, 0.6);
    padding: 10px 15px;
    border-radius: 10px;
    font-weight: bold;
    display: none;
}
</style>
</head>
<body>

<div id="temporizador">Tiempo restante: <span id="tiempo"><?= $tiempoRestante ?></span>s</div>

<div class="login-container">
  <h2><i class="fas fa-user-lock"></i> Admin Login</h2>
  <form method="POST">
    <input type="text" name="usuario" class="form-control" placeholder="Usuario" required <?= $tiempoRestante>0?'disabled':'' ?>>
    <input type="password" name="contrasena" class="form-control" placeholder="Contraseña" required <?= $tiempoRestante>0?'disabled':'' ?>>
    <button type="submit" name="login" class="btn btn-gamer w-100 mt-3" <?= $tiempoRestante>0?'disabled':'' ?>><i class="fas fa-sign-in-alt"></i> Iniciar Sesión</button>
  </form>
  <?php if(!empty($error)) { echo "<p style='color:red;margin-top:10px;'>$error</p>"; } ?>
  
  <p style="margin-top:15px; color:#00ff6a; text-align:center;">
    ¿No tienes cuenta? 
    <a href="admin-register.php" style="color:#00c853; text-decoration:underline;">Regístrate</a>
  </p>
</div>

<script>
let tiempo = <?= $tiempoRestante ?>;
const temporizador = document.getElementById('temporizador');
const inputs = document.querySelectorAll('input, button');

if(tiempo > 0){
    temporizador.style.display = 'block';
    const interval = setInterval(()=>{
        tiempo--;
        document.getElementById('tiempo').innerText = tiempo;
        if(tiempo <= 0){
            clearInterval(interval);
            temporizador.style.display = 'none';
            inputs.forEach(el => el.disabled = false);
        }
    },1000);
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>