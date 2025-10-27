<?php include('conexion.php'); ?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Registro de Administrador</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
body {
  background: radial-gradient(circle at center, #0f0f0f, #000);
  color: white;
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  animation: fadeIn 1s ease-in;
  overflow: hidden;
  font-family: 'Poppins', sans-serif;
}
@keyframes fadeIn {
  from { opacity: 0; transform: scale(0.9); filter: blur(5px); }
  to { opacity: 1; transform: scale(1); filter: blur(0); }
}
.register-container {
  background: rgba(30,30,30,0.9);
  backdrop-filter: blur(10px);
  padding: 2rem;
  border-radius: 20px;
  box-shadow: 0 0 25px rgba(0,255,100,0.3);
  width: 100%;
  max-width: 480px;
  transition: transform 0.4s ease;
}
.register-container:hover {
  transform: scale(1.02);
  box-shadow: 0 0 30px rgba(0,255,150,0.4);
}
h2 {
  text-align: center;
  color: #00ff6a;
  margin-bottom: 20px;
  text-shadow: 0 0 10px #00ff6a;
}
.btn-gamer {
  background: linear-gradient(90deg, #00ff6a, #00c853);
  color: #000;
  font-weight: bold;
  border: none;
  border-radius: 10px;
  transition: all 0.3s ease-in-out;
}
.btn-gamer:hover {
  transform: translateY(-3px) scale(1.03);
  box-shadow: 0 0 15px #00ff6a;
}
</style>
</head>
<body>
<div class="register-container">
  <h2><i class="fas fa-user-shield"></i> Registro de Administrador</h2>
  <form id="adminRegisterForm" method="POST">
    <input type="text" name="nombre" class="form-control mb-3" placeholder="Nombre" required>
    <input type="text" name="apellido" class="form-control mb-3" placeholder="Apellido" required>
    <input type="text" name="usuario" class="form-control mb-3" placeholder="Usuario" required>
    <input type="email" name="correo" class="form-control mb-3" placeholder="Correo" required>
    <input type="tel" name="telefono" class="form-control mb-3" placeholder="Teléfono" required>
    <input type="password" name="contrasena" class="form-control mb-3" placeholder="Contraseña" required>
    <button type="submit" class="btn btn-gamer w-100"><i class="fas fa-user-plus"></i> Registrar</button>
    <a href="admin-login.php" class="btn btn-secondary mt-2 w-100">Ya tengo cuenta</a>
  </form>
</div>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $usuario = $_POST['usuario'];
    $correo = $_POST['correo'];
    $telefono = $_POST['telefono'];
    $contrasena = password_hash($_POST['contrasena'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO administradores (nombre, apellido, usuario_admin, correo, telefono, contrasena)
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $nombre, $apellido, $usuario, $correo, $telefono, $contrasena);

    if ($stmt->execute()) {
        echo "<script>
            Swal.fire({
              icon: 'success',
              title: 'Registro exitoso',
              text: 'Administrador registrado correctamente',
              confirmButtonColor: '#00ff6a'
            }).then(() => window.location='admin-login.php');
        </script>";
    } else {
        echo "<script>
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: 'No se pudo registrar el administrador',
              confirmButtonColor: '#ff4d4d'
            });
        </script>";
    }
}
?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>