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
        
        $id = intval($_POST['id']);
        $nombre_usuario = strtoupper(trim($_POST['nombre_usuario']));
        $usuario = strtolower(trim($_POST['usuario']));
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';
        $rol = $_POST['rol'];
        $estado = intval($_POST['estado']);
        
        // Validaciones
        if (empty($nombre_usuario) || empty($usuario) || empty($rol)) {
            throw new Exception("Todos los campos obligatorios deben estar completos");
        }
        
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $usuario)) {
            throw new Exception("El usuario solo puede contener letras, números y guiones bajos");
        }
        
        if (!in_array($rol, ['ADMINISTRADOR', 'CAJERO', 'VENDEDOR'])) {
            throw new Exception("Rol inválido");
        }
        
        // Obtener datos anteriores para el log
        $sentenciaAntes = $conexion->prepare("SELECT * FROM usuarios WHERE id = ?");
        $sentenciaAntes->execute([$id]);
        $datosAnteriores = $sentenciaAntes->fetch(PDO::FETCH_ASSOC);
        
        if (!$datosAnteriores) {
            throw new Exception("El usuario no existe");
        }
        
        // Verificar que el usuario no esté duplicado (excepto el mismo)
        $sentenciaVerificar = $conexion->prepare("SELECT id FROM usuarios WHERE usuario = ? AND id != ?");
        $sentenciaVerificar->execute([$usuario, $id]);
        if ($sentenciaVerificar->fetch()) {
            throw new Exception("El usuario '$usuario' ya está siendo usado por otro usuario");
        }
        
        // Si se proporcionó contraseña, validar y actualizar
        $cambioPassword = false;
        if (!empty($password) || !empty($password_confirm)) {
            if (strlen($password) < 6) {
                throw new Exception("La contraseña debe tener al menos 6 caracteres");
            }
            
            if ($password !== $password_confirm) {
                throw new Exception("Las contraseñas no coinciden");
            }
            
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $cambioPassword = true;
            
            // Actualizar con contraseña
            $sentencia = $conexion->prepare("
                UPDATE usuarios SET 
                    nombre_usuario = ?, 
                    usuario = ?, 
                    password = ?,
                    rol = ?, 
                    estado = ?,
                    intentos_fallidos = 0,
                    bloqueado_hasta = NULL
                WHERE id = ?
            ");
            
            $resultado = $sentencia->execute([
                $nombre_usuario,
                $usuario,
                $passwordHash,
                $rol,
                $estado,
                $id
            ]);
        } else {
            // Actualizar sin contraseña
            $sentencia = $conexion->prepare("
                UPDATE usuarios SET 
                    nombre_usuario = ?, 
                    usuario = ?, 
                    rol = ?, 
                    estado = ?
                WHERE id = ?
            ");
            
            $resultado = $sentencia->execute([
                $nombre_usuario,
                $usuario,
                $rol,
                $estado,
                $id
            ]);
        }
        
        if ($resultado) {
            // Preparar descripción de cambios
            $cambios = [];
            if ($datosAnteriores['nombre_usuario'] !== $nombre_usuario) {
                $cambios[] = "Nombre: '{$datosAnteriores['nombre_usuario']}' → '$nombre_usuario'";
            }
            if ($datosAnteriores['usuario'] !== $usuario) {
                $cambios[] = "Usuario: '{$datosAnteriores['usuario']}' → '$usuario'";
            }
            if ($datosAnteriores['rol'] !== $rol) {
                $cambios[] = "Rol: '{$datosAnteriores['rol']}' → '$rol'";
            }
            if ($datosAnteriores['estado'] != $estado) {
                $estadoAntes = $datosAnteriores['estado'] == 1 ? 'Activo' : 'Inactivo';
                $estadoDespues = $estado == 1 ? 'Activo' : 'Inactivo';
                $cambios[] = "Estado: '$estadoAntes' → '$estadoDespues'";
            }
            if ($cambioPassword) {
                $cambios[] = "Contraseña actualizada";
            }
            
            $descripcionCambios = empty($cambios) ? "Sin cambios" : implode(", ", $cambios);
            
            // Registrar en log
            registrarActividad(
                'EDITAR',
                'USUARIOS',
                "Usuario editado: $nombre_usuario (ID: $id) - Cambios: $descripcionCambios",
                [
                    'nombre' => $datosAnteriores['nombre_usuario'],
                    'usuario' => $datosAnteriores['usuario'],
                    'rol' => $datosAnteriores['rol'],
                    'estado' => $datosAnteriores['estado']
                ],
                [
                    'nombre' => $nombre_usuario,
                    'usuario' => $usuario,
                    'rol' => $rol,
                    'estado' => $estado,
                    'password_cambiado' => $cambioPassword
                ]
            );
            
            $titulo = "✅ Usuario Actualizado Exitosamente";
            $mensaje = "Los datos del usuario han sido actualizados correctamente.<br><br>
                        <strong>Detalles:</strong><br>
                        • ID: <strong>#$id</strong><br>
                        • Nombre: <strong>$nombre_usuario</strong><br>
                        • Usuario: <strong>$usuario</strong><br>
                        • Rol: <strong>$rol</strong><br>
                        • Estado: <strong>" . ($estado == 1 ? '✅ Activo' : '❌ Inactivo') . "</strong><br>
                        " . ($cambioPassword ? "• ✅ Contraseña actualizada<br>" : "") . "
                        • Modificado por: <strong>{$usuarioActual['nombre']}</strong>";
            $tipo = "success";
        } else {
            throw new Exception("Error al actualizar el usuario");
        }
    }
} catch (Exception $e) {
    $titulo = "❌ Error al Actualizar Usuario";
    $mensaje = htmlspecialchars($e->getMessage());
    $tipo = "error";
    
    if (isset($usuarioActual)) {
        registrarActividad('ERROR', 'USUARIOS', "Error al editar usuario: " . $e->getMessage(), null, null);
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
                        <a href='./frm_guardar_usuario.php' class='secondary-button'>➕ Crear Nuevo</a>
                    </div>
                </div>
            `;
        });
    </script>
</body>
</html>