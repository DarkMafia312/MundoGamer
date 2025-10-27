<?php
session_start();
if (isset($_SESSION['usuario'])) {
    unset($_SESSION['usuario']);
}
session_unset();
session_destroy();
header("Location: usuario-login.php");
exit();
?>