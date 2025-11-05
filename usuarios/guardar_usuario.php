<?php
include_once "../auth.php";

// Solo administradores
if (!tienePermiso(['ADMINISTRADOR'])) {
    header("Location: ../index.php?error=sin_permisos");
    exit();
}

$mensaje = "";
$tipo = "";
$titulo = "";

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        include_once "../db.php";
        
        $usuarioActual = getUsuarioActual();
        
        $nombre_usuario = strtoupper(trim($_POST['nombre_usuario']));
        $usuario = strtolower(trim($_POST['usuario']));
        $password = $_POST['password'];
        $password_confirm = $_POST['password_confirm'];
        $rol = $_POST['rol'];
        $estado = intval($_POST['estado']);
        
        // Validaciones
        if (empty($nombre_usuario) || empty($usuario) || empty($password) || empty($rol)) {
            throw new Exception("Todos los campos obligatorios deben estar completos");
        }
        
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $usuario)) {
            throw new Exception("El usuario solo puede contener letras, números y guiones bajos");
        }
        
        if (strlen($password) < 6) {
            throw new Exception("La contraseña debe tener al menos 6 caracteres");
        }
        
        if ($password !== $password_confirm) {
            throw new Exception("Las contraseñas no coinciden");
        }
        
        if (!in_array($rol, ['ADMINISTRADOR', 'CAJERO', 'VENDEDOR'])) {
            throw new Exception("Rol inválido");
        }
        
        // Verificar que el usuario no exista
        $sentenciaVerificar = $conexion->prepare("SELECT id FROM usuarios WHERE usuario = ?");
        $sentenciaVerificar->execute([$usuario]);
        if ($sentenciaVerificar->fetch()) {
            throw new Exception("El usuario '$usuario' ya existe en el sistema");
        }
        
        // Hash de la contraseña
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
        // Insertar usuario
        $sentencia = $conexion->prepare("
            INSERT INTO usuarios (nombre_usuario, usuario, password, rol, estado) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $resultado = $sentencia->execute([
            $nombre_usuario,
            $usuario,
            $passwordHash,
            $rol,
            $estado
        ]);
        
        if ($resultado) {
            $id_usuario = $conexion->lastInsertId();
            
            // Registrar en log
            registrarActividad(
                'CREAR',
                'USUARIOS',
                "Usuario creado: $nombre_usuario (Login: $usuario, Rol: $rol)",
                null,
                [
                    'id' => $id_usuario,
                    'nombre' => $nombre_usuario,
                    'usuario' => $usuario,
                    'rol' => $rol,
                    'estado' => $estado
                ]
            );
            
            $titulo = "✅ Usuario Creado Exitosamente";
            $mensaje = "El usuario ha sido creado correctamente.<br><br>
                        <strong>Detalles:</strong><br>
                        • ID: <strong>#$id_usuario</strong><br>
                        • Nombre: <strong>$nombre_usuario</strong><br>
                        • Usuario: <strong>$usuario</strong><br>
                        • Rol: <strong>$rol</strong><br>
                        • Creado por: <strong>{$usuarioActual['nombre']}</strong><br><br>
                        ✅ El usuario ya puede iniciar sesión en el sistema";
            $tipo = "success";
        } else {
            throw new Exception("Error al crear el usuario");
        }
    }
} catch (Exception $e) {
    $titulo = "❌ Error al Crear Usuario";
    $mensaje = htmlspecialchars($e->getMessage());
    $tipo = "error";
    
    if (isset($usuarioActual)) {
        registrarActividad('ERROR', 'USUARIOS', "Error al crear usuario: " . $e->getMessage(), null, null);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo; ?></title>
    <link href="../css/bulma.min.css" rel="stylesheet">
    <link href="../css/mensajes.css" rel="stylesheet">
    <style>
        .main-content { display: flex; align-items: center; justify-content: center; min-height: 100vh; }
    </style>
</head>
<body>
    <?php include '../menu.php'; ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mainContent = document.querySelector('.main-content');
            const tipo = <?php echo json_encode($tipo); ?>;
            const titulo = <?php echo json_encode($titulo); ?>;
            const mensaje = <?php echo json_encode($mensaje); ?>;

            const icono = tipo === 'success' ? '👤✅' : '❌';

            mainContent.innerHTML = `
                <div class='message-container'>
                    <span class='status-icon'>${icono}</span>
                    <h1 class='message-title'>${titulo}</h1>
                    <div class='message-content'>${mensaje}</div>
                    <div class='button-group'>
                        <a href='./listado_usuarios.php' class='action-button'>📋 Ver Usuarios</a>
                        <a href='./frm_guardar_usuario.php' class='secondary-button'>➕ Crear Otro</a>
                    </div>
                </div>
            `;
        });
    </script>
</body>
</html>