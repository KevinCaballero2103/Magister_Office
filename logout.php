<?php
session_start();

include_once "db.php";

// Registrar logout en log de actividades
if (isset($_SESSION['usuario_nombre'])) {
    try {
        $stmt = $conexion->prepare("INSERT INTO log_actividades (usuario, accion, modulo, descripcion, ip_address) VALUES (?, 'LOGOUT', 'SISTEMA', 'Cierre de sesión', ?)");
        $stmt->execute([$_SESSION['usuario_nombre'], $_SERVER['REMOTE_ADDR']]);
    } catch (Exception $e) {
        error_log("Error registrando logout: " . $e->getMessage());
    }
}

// Destruir sesión
session_unset();
session_destroy();

// Redirigir al login
header("Location: login.php");
exit();
?>