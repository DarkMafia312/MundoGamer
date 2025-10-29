<?php
include 'conexion.php';
ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>🧪 Test de Base de Datos - MundoGamer</title>
<style>
    body {
        font-family: 'Poppins', sans-serif;
        background: linear-gradient(135deg, #1f0362, #3e2ecf);
        color: #fff;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100vh;
        overflow: hidden;
        margin: 0;
    }

    h1 {
        font-size: 2rem;
        margin-bottom: 15px;
        animation: fadeInDown 1s ease;
    }

    .card {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(12px);
        border-radius: 20px;
        padding: 25px 35px;
        width: 500px;
        max-width: 90%;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
        animation: slideUp 1s ease;
    }

    ul {
        list-style: none;
        padding: 0;
    }

    li {
        background: rgba(255, 255, 255, 0.15);
        margin: 8px 0;
        padding: 10px;
        border-radius: 8px;
        animation: fadeIn 1.5s ease;
    }

    .status {
        font-size: 1.1rem;
        margin: 15px 0;
    }

    .ok {
        color: #00ff88;
    }

    .warning {
        color: #ffbb33;
    }

    .error {
        color: #ff4d4d;
    }

    footer {
        margin-top: 20px;
        font-size: 0.9rem;
        opacity: 0.8;
    }

    @keyframes slideUp {
        from { transform: translateY(50px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    @keyframes fadeInDown {
        from { transform: translateY(-30px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .btn {
        margin-top: 15px;
        background: #00ff88;
        color: #000;
        border: none;
        padding: 10px 20px;
        border-radius: 10px;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn:hover {
        background: #fff;
        transform: scale(1.05);
    }
</style>
</head>
<body>
    <h1>🧪 Test de Conexión a la Base de Datos</h1>
    <div class="card">
        <?php
        if (!isset($conn)) {
            echo "<p class='status error'>❌ Error: No se ha encontrado la variable de conexión \$conn.</p>";
        } else {
            try {
                $sql = "SHOW TABLES";
                $resultado = $conn->query($sql);

                if ($resultado && $resultado->num_rows > 0) {
                    echo "<p class='status ok'>✅ Conexión exitosa con la base de datos.</p>";
                    echo "<p><strong>Tablas detectadas:</strong></p><ul>";
                    while ($fila = $resultado->fetch_array()) {
                        echo "<li>🗂️ " . htmlspecialchars($fila[0]) . "</li>";
                    }
                    echo "</ul>";
                } else {
                    echo "<p class='status warning'>⚠️ Conexión establecida, pero no se encontraron tablas.</p>";
                }
            } catch (Exception $e) {
                echo "<p class='status error'>❌ Error ejecutando la consulta: " . htmlspecialchars($e->getMessage()) . "</p>";
            }

            $conn->close();
            echo "<p class='status ok'>🔒 Conexión cerrada correctamente.</p>";
        }
        ?>
        <button class="btn" onclick="window.location.reload()">🔁 Volver a probar</button>
    </div>
    <footer>© 2025 MundoGamer | Test de Base de Datos</footer>
</body>
</html>