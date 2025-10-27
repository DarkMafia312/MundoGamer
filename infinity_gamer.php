<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Infinity Gamer | MundoGamer</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Poppins', sans-serif;
}
body {
    background: url('https://www.creativefabrica.com/wp-content/uploads/2023/06/01/Gaming-desktop-background-neon-tech-Graphics-71086915-1-1-580x387.png') no-repeat center center fixed;
    background-size: cover;
    color: #fff;
    overflow-x: hidden;
}

/* === HEADER === */
header {
    position: fixed;
    top: 0;
    width: 100%;
    background: rgba(0,0,0,0.75);
    backdrop-filter: blur(10px);
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 50px;
    z-index: 1000;
}
header h1 {
    color: #00ffcc;
    font-size: 1.8rem;
    letter-spacing: 2px;
}
header nav a {
    color: #fff;
    text-decoration: none;
    margin: 0 15px;
    font-weight: 500;
    transition: color 0.3s ease;
}
header nav a:hover {
    color: #00ffcc;
}

/* === HERO === */
.hero {
    height: 100vh;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    background: rgba(0,0,0,0.65);
    text-align: center;
}
.hero h2 {
    font-size: 3.5rem;
    color: #00ffcc;
    text-shadow: 0 0 15px #00ffcc;
    animation: glowText 3s infinite alternate;
}
.hero p {
    font-size: 1.2rem;
    max-width: 700px;
    margin-top: 20px;
    color: #ddd;
}
.btn-login {
    margin-top: 40px;
    background: #00ffcc;
    border: none;
    padding: 15px 40px;
    border-radius: 50px;
    font-size: 1.1rem;
    color: #000;
    font-weight: 600;
    cursor: pointer;
    transition: 0.3s ease;
}
.btn-login:hover {
    transform: scale(1.05);
    box-shadow: 0 0 20px #00ffcc;
}

/* === SECCIONES === */
section {
    padding: 100px 10%;
    text-align: center;
    background: rgba(0,0,0,0.75);
    margin-bottom: 20px;
    border-radius: 20px;
}
section h2 {
    font-size: 2.5rem;
    color: #00ffcc;
    margin-bottom: 30px;
    text-transform: uppercase;
}
section p {
    color: #ccc;
    max-width: 900px;
    margin: 0 auto;
    line-height: 1.8;
    font-size: 1.1rem;
}

/* === VALORES === */
.valores-list {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 25px;
    margin-top: 30px;
}
.valor {
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.1);
    width: 250px;
    padding: 25px;
    border-radius: 15px;
    transition: 0.4s ease;
}
.valor:hover {
    transform: translateY(-10px);
    box-shadow: 0 0 20px #00ffcc;
}
.valor h3 {
    color: #00ffcc;
    margin-bottom: 10px;
}

/* === EQUIPO === */
.equipo {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 40px;
}
.miembro {
    background: rgba(255,255,255,0.05);
    border-radius: 15px;
    padding: 20px;
    width: 220px;
    transition: 0.3s ease;
}
.miembro:hover {
    box-shadow: 0 0 15px #00ffcc;
    transform: translateY(-8px);
}
.miembro img {
    width: 100%;
    border-radius: 50%;
    border: 3px solid #00ffcc;
}
.miembro h4 {
    margin-top: 10px;
    color: #00ffcc;
}

/* === TESTIMONIOS === */
.testimonios {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 30px;
    margin-top: 40px;
}
.testimonio {
    background: rgba(255,255,255,0.05);
    border-radius: 15px;
    padding: 25px;
    border-left: 4px solid #00ffcc;
    text-align: left;
}
.testimonio p {
    color: #ccc;
    font-style: italic;
}
.testimonio h4 {
    color: #00ffcc;
    margin-top: 10px;
}

/* === FOOTER === */
footer {
    background: rgba(0,0,0,0.85);
    text-align: center;
    padding: 25px;
    font-size: 0.9rem;
    color: #aaa;
}

/* === MODAL LOGIN === */
.modal {
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.8);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 2000;
}
.modal-content {
    background: #111;
    padding: 40px;
    border-radius: 20px;
    text-align: center;
    box-shadow: 0 0 30px #00ffcc;
}
.modal-content h2 {
    color: #00ffcc;
}
.login-option {
    background: #00ffcc;
    color: #000;
    border: none;
    padding: 12px 30px;
    border-radius: 8px;
    margin: 10px;
    font-size: 1rem;
    cursor: pointer;
    transition: 0.3s ease;
}
.login-option:hover {
    background: #1db954;
    color: #fff;
}
.close-btn {
    position: absolute;
    top: 20px;
    right: 30px;
    font-size: 2rem;
    cursor: pointer;
    color: #00ffcc;
}

/* === ANIMACIONES === */
@keyframes glowText {
    from { text-shadow: 0 0 10px #00ffcc; }
    to { text-shadow: 0 0 25px #00ffcc, 0 0 50px #1db954; }
}

/* === RESPONSIVE === */
@media (max-width: 768px) {
    .equipo {
        flex-direction: column;
        align-items: center;
    }
}
</style>
</head>
<body>

<header>
    <h1>∞ Infinity Gamer</h1>
    <nav>
        <a href="#about">Nosotros</a>
        <a href="#mision">Misión</a>
        <a href="#equipo">Equipo</a>
        <a href="#contact">Contacto</a>
    </nav>
</header>

<section class="hero">
    <h2>Bienvenido a Infinity Gamer</h2>
    <p>Revolucionamos el mundo gamer con tecnología, comunidad y pasión. Vive la experiencia digital definitiva.</p>
    <button class="btn-login" onclick="openModal()">Iniciar Sesión</button>
</section>

<section id="about">
    <h2>Sobre Nosotros</h2>
    <p>Somos una empresa tecnológica especializada en soluciones para la comunidad gamer. Nuestro ecosistema, <strong>MundoGamer</strong>, integra venta de juegos, soporte técnico, asistencia virtual y gestión empresarial, todo desde una plataforma segura y moderna.</p>
</section>

<section id="mision">
    <h2>Misión, Visión y Valores</h2>
    <p><strong>Misión:</strong> Innovar y conectar a los jugadores mediante herramientas inteligentes que mejoren su experiencia digital.</p>
    <p><strong>Visión:</strong> Ser la plataforma gamer líder en Latinoamérica, impulsando una comunidad unida por la tecnología y la creatividad.</p>

    <div class="valores-list">
        <div class="valor"><h3>🎯 Innovación</h3><p>Buscamos siempre nuevas formas de mejorar la experiencia gamer.</p></div>
        <div class="valor"><h3>🤝 Comunidad</h3><p>Creemos en el poder de los jugadores para construir juntos.</p></div>
        <div class="valor"><h3>💡 Creatividad</h3><p>Nos impulsa la imaginación para romper los límites del entretenimiento.</p></div>
        <div class="valor"><h3>⚙️ Tecnología</h3><p>Desarrollamos soluciones inteligentes y escalables.</p></div>
    </div>
</section>

<section id="equipo">
    <h2>Nuestro Equipo</h2>
    <div class="equipo">
        <div class="miembro">
            <h4>Fernando Antonio</h4>
            <p>CEO & Fundador</p>
        </div>
        <div class="miembro">
            <h4>Angel Fabrizio</h4>
            <p>Director de Tecnología</p>
        </div>
        <div class="miembro">
            <h4>Wilson Esteban</h4>
            <p>Diseñador UX/UI</p>
        </div>
        <div class="miembro">
            <h4>Jhon Arnold</h4>
            <p>Especialista en Seguridad Informática</p>
        </div>
    </div>
</section>

<section id="testimonios">
    <h2>Testimonios</h2>
    <div class="testimonios">
        <div class="testimonio">
            <p>“Infinity Gamer cambió la forma en que administro mis juegos. Todo es más fácil y rápido.”</p>
            <h4>— Luis Romero</h4>
        </div>
        <div class="testimonio">
            <p>“El soporte técnico es excelente. Me ayudaron a recuperar mi cuenta en minutos.”</p>
            <h4>— Sofía García</h4>
        </div>
        <div class="testimonio">
            <p>“Una comunidad gamer de verdad, con beneficios reales y un diseño increíble.”</p>
            <h4>— Kevin Torres</h4>
        </div>
    </div>
</section>

<footer id="contact">
    <p>📍 Lima, Perú | 📧 contacto@infinitygamer.com | ☎ +51 999 888 777</p>
    <p>© 2025 Infinity Gamer — Innovando el Futuro del Juego</p>
</footer>

<!-- MODAL LOGIN -->
<div class="modal" id="loginModal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal()">&times;</span>
        <h2>¿Cómo deseas iniciar sesión?</h2>
        <button class="login-option" onclick="window.location.href='usuario-login.php'">👤 Usuario</button>
        <button class="login-option" onclick="window.location.href='admin-login.php'">🛡️ Administrador</button>
    </div>
</div>

<script>
function openModal() {
    document.getElementById('loginModal').style.display = 'flex';
}
function closeModal() {
    document.getElementById('loginModal').style.display = 'none';
}
</script>

</body>
</html>
