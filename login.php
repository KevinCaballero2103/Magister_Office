<?php
session_start();

// Si ya está autenticado, redirigir al index
if (isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

include_once "db.php";

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($usuario) || empty($password)) {
        $error = "Por favor completa todos los campos";
    } else {
        try {
            // Buscar usuario
            $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE usuario = ? AND estado = 1");
            $stmt->execute([$usuario]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // Verificar si está bloqueado
                if ($user['bloqueado_hasta'] && strtotime($user['bloqueado_hasta']) > time()) {
                    $error = "Usuario bloqueado temporalmente por múltiples intentos fallidos";
                } else {
                    // Verificar contraseña
                    if (password_verify($password, $user['password'])) {
                        // Login exitoso
                        $_SESSION['usuario_id'] = $user['id'];
                        $_SESSION['usuario_nombre'] = $user['nombre_usuario'];
                        $_SESSION['usuario_login'] = $user['usuario'];
                        $_SESSION['usuario_rol'] = $user['rol'];
                        $_SESSION['ultimo_acceso'] = time();
                        
                        // Actualizar último acceso y resetear intentos
                        $stmt = $conexion->prepare("UPDATE usuarios SET ultimo_acceso = NOW(), intentos_fallidos = 0, bloqueado_hasta = NULL WHERE id = ?");
                        $stmt->execute([$user['id']]);
                        
                        // Registrar en log
                        $stmt = $conexion->prepare("INSERT INTO log_actividades (usuario, accion, modulo, descripcion, ip_address) VALUES (?, 'LOGIN', 'SISTEMA', 'Inicio de sesión exitoso', ?)");
                        $stmt->execute([$user['nombre_usuario'], $_SERVER['REMOTE_ADDR']]);
                        
                        // Redirigir
                        $redirect = $_SESSION['redirect_after_login'] ?? 'index.php';
                        unset($_SESSION['redirect_after_login']);
                        header("Location: $redirect");
                        exit();
                    } else {
                        // Contraseña incorrecta - incrementar intentos
                        $intentos = $user['intentos_fallidos'] + 1;
                        $bloqueado = null;
                        
                        if ($intentos >= 5) {
                            $bloqueado = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                            $error = "Demasiados intentos fallidos. Usuario bloqueado por 15 minutos";
                        } else {
                            $error = "Credenciales incorrectas. Intentos restantes: " . (5 - $intentos);
                        }
                        
                        $stmt = $conexion->prepare("UPDATE usuarios SET intentos_fallidos = ?, bloqueado_hasta = ? WHERE id = ?");
                        $stmt->execute([$intentos, $bloqueado, $user['id']]);
                    }
                }
            } else {
                $error = "Credenciales incorrectas";
            }
        } catch (Exception $e) {
            error_log("Error en login: " . $e->getMessage());
            $error = "Error del sistema. Intenta nuevamente";
        }
    }
}

// Mensajes según parámetro GET
if (isset($_GET['error'])) {
    if ($_GET['error'] === 'sesion_expirada') {
        $error = "Tu sesión ha expirado. Por favor inicia sesión nuevamente";
    } elseif ($_GET['error'] === 'no_autorizado') {
        $error = "Debes iniciar sesión para acceder al sistema";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Magister Office</title>
    <link href="css/bulma.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 50%, #2c3e50 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-container {
            background: rgba(44, 62, 80, 0.95);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
            max-width: 450px;
            width: 100%;
            animation: slideDown 0.5s ease-out;
        }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-50px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-logo {
            font-size: 4rem;
            margin-bottom: 10px;
        }
        .logo-img {
            width: 260px;
            height: 100px;
        }
        .login-title {
            color: #f1c40f;
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .login-subtitle {
            color: rgba(255,255,255,0.8);
            font-size: 1rem;
        }
        .field label {
            color: #ecf0f1 !important;
            font-weight: 600;
            margin-bottom: 8px !important;
        }
        .input {
            background: rgba(236, 240, 241, 0.1) !important;
            border: 2px solid rgba(241, 196, 15, 0.3) !important;
            color: white !important;
            font-size: 1.1rem !important;
            padding: 12px !important;
        }
        .input::placeholder {
            color: rgba(255,255,255,0.5) !important;
        }
        .input:focus {
            background: rgba(236, 240, 241, 0.15) !important;
            border-color: #f1c40f !important;
            box-shadow: 0 0 0 0.125em rgba(241, 196, 15, 0.25) !important;
        }
        .button.is-primary {
            background: linear-gradient(45deg, #f39c12, #f1c40f) !important;
            border: none !important;
            color: #2c3e50 !important;
            font-weight: bold !important;
            font-size: 1.1rem !important;
            padding: 12px !important;
            width: 100%;
            margin-top: 10px;
        }
        .button.is-primary:hover {
            background: linear-gradient(45deg, #e67e22, #f39c12) !important;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(243, 156, 18, 0.4) !important;
        }
        .error-message {
            background: rgba(231, 76, 60, 0.2);
            border: 2px solid #e74c3c;
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
        }
        .icon-input {
            position: relative;
        }
        .icon-input .input {
            padding-left: 45px !important;
        }
        .icon-input::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.2rem;
            color: rgba(241, 196, 15, 0.7);
            z-index: 1;
        }
        .icon-user::before { content: '👤'; }
        .icon-password::before { content: '🔒'; }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="login-logo">
                <img src="img/logo.png" alt="Logo" class="logo-img">
            </div>
            <h1 class="login-title">Magister Office</h1>
            <p class="login-subtitle">Sistema de Gestión</p>
        </div>
        
        <?php if ($error): ?>
        <div class="error-message">
            ⚠️ <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="field">
                <label class="label">Usuario</label>
                <div class="control icon-input icon-user">
                    <input class="input" type="text" name="usuario" placeholder="Ingresa tu usuario" 
                           value="<?php echo isset($_POST['usuario']) ? htmlspecialchars($_POST['usuario']) : ''; ?>" 
                           required autofocus>
                </div>
            </div>
            
            <div class="field">
                <label class="label">Contraseña</label>
                <div class="control icon-input icon-password">
                    <input class="input" type="password" name="password" placeholder="Ingresa tu contraseña" required>
                </div>
            </div>
            
            <button type="submit" class="button is-primary">
                Iniciar Sesión
            </button>
        </form>
        

    </div>
</body>
</html>