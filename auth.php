<?php
// auth.php - Middleware de autenticación
// Incluir en TODAS las páginas protegidas

session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_nombre'])) {
    // Guardar la página que intentó acceder para redirigir después del login
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header("Location: /Magister_Office/login.php");
    exit();
}

// Renovar la sesión cada 4 horas
if (isset($_SESSION['ultimo_acceso'])) {
    $inactivo = time() - $_SESSION['ultimo_acceso'];
    if ($inactivo > 14400) { // 4 horas
        session_unset();
        session_destroy();
        header("Location: /Magister_Office/login.php?error=sesion_expirada");
        exit();
    }
}
$_SESSION['ultimo_acceso'] = time();

// Función helper para obtener datos del usuario actual
function getUsuarioActual() {
    return [
        'id' => $_SESSION['usuario_id'],
        'nombre' => $_SESSION['usuario_nombre'],
        'usuario' => $_SESSION['usuario_login'],
        'rol' => $_SESSION['usuario_rol']
    ];
}

// Función helper para verificar permisos por rol
function tienePermiso($roles_permitidos) {
    if (!is_array($roles_permitidos)) {
        $roles_permitidos = [$roles_permitidos];
    }
    return in_array($_SESSION['usuario_rol'], $roles_permitidos);
}

// Función para registrar actividad
function registrarActividad($accion, $modulo, $descripcion, $datos_anteriores = null, $datos_nuevos = null) {
    global $conexion;
    
    if (!isset($conexion)) {
        include_once __DIR__ . "/db.php";
    }
    
    try {
        $usuario = $_SESSION['usuario_nombre'];
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';
        
        $sql = "INSERT INTO log_actividades (usuario, accion, modulo, descripcion, ip_address, user_agent, datos_anteriores, datos_nuevos) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            $usuario,
            $accion,
            $modulo,
            $descripcion,
            $ip,
            substr($user_agent, 0, 255),
            $datos_anteriores ? json_encode($datos_anteriores) : null,
            $datos_nuevos ? json_encode($datos_nuevos) : null
        ]);
    } catch (Exception $e) {
        error_log("Error registrando actividad: " . $e->getMessage());
    }
}
/**
 * Verifica si hay una caja abierta
 * @return object|false Retorna objeto con datos de caja si está abierta, false si está cerrada
 */
function verificarCajaAbierta() {
    global $conexion;
    
    if (!isset($conexion)) {
        include_once __DIR__ . "/db.php";
    }
    
    try {
        $sentencia = $conexion->prepare("
            SELECT * FROM cierres_caja 
            WHERE estado = 'ABIERTA' 
            ORDER BY fecha_apertura DESC 
            LIMIT 1
        ");
        $sentencia->execute();
        $caja = $sentencia->fetch(PDO::FETCH_OBJ);
        
        return $caja ? $caja : false;
    } catch (Exception $e) {
        error_log("Error verificando caja: " . $e->getMessage());
        return false;
    }
}

/**
 * Requiere que la caja esté abierta
 * Si no está abierta, redirige con error
 */
function requiereCajaAbierta() {
    $caja = verificarCajaAbierta();
    
    if (!$caja) {
        // Determinar la ruta correcta
        $rutaError = str_repeat('../', substr_count(dirname($_SERVER['SCRIPT_NAME']), '/') - 1) . 'caja/caja_cerrada.php';
        header("Location: $rutaError");
        exit();
    }
    
    return $caja;
}
?>