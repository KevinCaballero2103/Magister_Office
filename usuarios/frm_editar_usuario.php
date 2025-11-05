<?php
include_once "../auth.php";

// Solo administradores
if (!tienePermiso(['ADMINISTRADOR'])) {
    header("Location: ../index.php?error=sin_permisos");
    exit();
}

if (!isset($_GET["id"])) {
    $error = "Necesito el parámetro id para identificar al usuario.";
} else {
    include '../db.php';
    $id = intval($_GET["id"]);
    
    $sentencia = $conexion->prepare("SELECT * FROM usuarios WHERE id = ?");
    $sentencia->execute([$id]);
    $usuario = $sentencia->fetch(PDO::FETCH_OBJ);
    
    if ($usuario === FALSE) {
        $error = "El usuario indicado no existe en el sistema.";
    }
}

if (isset($usuario)) {
    $usuarioJSON = json_encode($usuario);
    registrarActividad('ACCESO', 'USUARIOS', "Acceso a edición de usuario: {$usuario->nombre_usuario}", null, null);
} else {
    $usuarioJSON = 'null';
    $mensajeError = isset($error) ? $error : 'Error desconocido';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario</title>
    <link href="../css/bulma.min.css" rel="stylesheet">
    <link href="../css/formularios.css" rel="stylesheet">
    <style>
        .main-content { background: #2c3e50 !important; color: white; }
        .password-section {
            background: rgba(52, 152, 219, 0.1);
            border: 2px solid rgba(52, 152, 219, 0.3);
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
        .password-strength {
            margin-top: 10px;
            padding: 10px;
            border-radius: 5px;
            font-size: 0.9rem;
            display: none;
        }
        .strength-weak { background: rgba(231, 76, 60, 0.2); color: #e74c3c; }
        .strength-medium { background: rgba(243, 156, 18, 0.2); color: #f39c12; }
        .strength-strong { background: rgba(39, 174, 96, 0.2); color: #27ae60; }
        .role-badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: bold;
            display: inline-block;
        }
        .role-admin { background: linear-gradient(45deg, #e74c3c, #c0392b); color: white; }
        .role-cajero { background: linear-gradient(45deg, #3498db, #2980b9); color: white; }
        .role-vendedor { background: linear-gradient(45deg, #27ae60, #2ecc71); color: white; }
    </style>
</head>
<body>
    <?php include '../menu.php'; ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mainContent = document.querySelector('.main-content');
            const usuario = <?php echo $usuarioJSON; ?>;
            
            if (usuario === null) {
                const errorMessage = '<?php echo isset($mensajeError) ? addslashes($mensajeError) : 'Error desconocido'; ?>';
                
                const errorHTML = `
                    <div class='error-container'>
                        <div class='error-title'>Error</div>
                        <div class='error-message'>${errorMessage}</div>
                        <a href='./listado_usuarios.php' class='button'>Volver al Listado</a>
                    </div>
                `;
                
                mainContent.innerHTML = errorHTML;
                return;
            }

            const rolBadge = `<span class="role-badge role-${usuario.rol.toLowerCase()}">${usuario.rol}</span>`;
            
            const contentHTML = `
                <div class='form-container'>
                    <h1 class='form-title'>✏️ Editar Usuario</h1>
                    
                    <div style="text-align: center; margin-bottom: 20px;">
                        <strong style="color: #f1c40f;">ID: #${usuario.id}</strong> | 
                        Rol actual: ${rolBadge}
                    </div>
                    
                    <form action='./editar_usuario.php' method='post' onsubmit='return validateForm()'>
                        <input type='hidden' name='id' value='${usuario.id}'>
                        
                        <div class='columns'>
                            <div class='column is-6'>
                                <div class='field'>
                                    <label class='label'>Nombre Completo *</label>
                                    <div class='control'>
                                        <input class='input' type='text' name='nombre_usuario' id='nombre_usuario' 
                                               placeholder='Ej: Juan Pérez' required
                                               value='${usuario.nombre_usuario || ''}'>
                                    </div>
                                </div>

                                <div class='field'>
                                    <label class='label'>Usuario (Login) *</label>
                                    <div class='control'>
                                        <input class='input' type='text' name='usuario' id='usuario' 
                                               placeholder='Ej: jperez' required minlength='4' maxlength='50'
                                               value='${usuario.usuario || ''}'>
                                    </div>
                                    <p class='help' style='color: rgba(255,255,255,0.7);'>
                                        Mínimo 4 caracteres. Solo letras, números y guiones bajos.
                                    </p>
                                </div>

                                <div class='field'>
                                    <label class='label'>Rol *</label>
                                    <div class='control'>
                                        <div class='select is-fullwidth'>
                                            <select name='rol' id='rol' required>
                                                <option value='ADMINISTRADOR' ${usuario.rol === 'ADMINISTRADOR' ? 'selected' : ''}>🔴 ADMINISTRADOR</option>
                                                <option value='CAJERO' ${usuario.rol === 'CAJERO' ? 'selected' : ''}>🔵 CAJERO</option>
                                                <option value='VENDEDOR' ${usuario.rol === 'VENDEDOR' ? 'selected' : ''}>🟢 VENDEDOR</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class='field'>
                                    <label class='label'>Estado *</label>
                                    <div class='control'>
                                        <div class='select is-fullwidth'>
                                            <select name='estado' id='estado' required>
                                                <option value='1' ${usuario.estado == 1 ? 'selected' : ''}>✅ Activo</option>
                                                <option value='0' ${usuario.estado == 0 ? 'selected' : ''}>❌ Inactivo</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class='column is-6'>
                                <div class='password-section'>
                                    <h3 style='color: #3498db; margin-bottom: 15px; text-align: center;'>
                                        🔒 Cambiar Contraseña (Opcional)
                                    </h3>
                                    <p style='color: rgba(255,255,255,0.7); margin-bottom: 15px; text-align: center; font-size: 0.9rem;'>
                                        Deja en blanco si NO quieres cambiar la contraseña
                                    </p>
                                    
                                    <div class='field'>
                                        <label class='label'>Nueva Contraseña</label>
                                        <div class='control'>
                                            <input class='input' type='password' name='password' id='password' 
                                                   placeholder='Mínimo 6 caracteres' minlength='6' 
                                                   oninput='checkPasswordStrength()'>
                                        </div>
                                        <div id='password-strength' class='password-strength'></div>
                                    </div>

                                    <div class='field'>
                                        <label class='label'>Confirmar Nueva Contraseña</label>
                                        <div class='control'>
                                            <input class='input' type='password' name='password_confirm' id='password_confirm' 
                                                   placeholder='Repite la contraseña' minlength='6'>
                                        </div>
                                    </div>
                                </div>

                                <div style='background: rgba(52, 152, 219, 0.1); padding: 15px; border-radius: 8px; border: 2px solid rgba(52, 152, 219, 0.3); margin-top: 20px;'>
                                    <strong style='color: #3498db;'>ℹ️ Información:</strong><br><br>
                                    <strong>Último acceso:</strong><br>
                                    ${usuario.ultimo_acceso ? new Date(usuario.ultimo_acceso).toLocaleString('es-PY') : 'Sin accesos'}<br><br>
                                    <strong>Intentos fallidos:</strong> ${usuario.intentos_fallidos || 0}
                                </div>
                            </div>
                        </div>

                        <div class='button-group'>
                            <button type='submit' class='button'>💾 Guardar Cambios</button>
                            <button type='reset' class='secondary-button' onclick='resetPassword()'>🔄 Restaurar</button>
                            <a href='./listado_usuarios.php' class='secondary-button'>📋 Volver al Listado</a>
                        </div>
                    </form>
                </div>
            `;
            
            mainContent.innerHTML = contentHTML;
        });

        function checkPasswordStrength() {
            const password = document.getElementById('password').value;
            const strengthDiv = document.getElementById('password-strength');
            
            if (password.length === 0) {
                strengthDiv.style.display = 'none';
                return;
            }
            
            strengthDiv.style.display = 'block';
            
            let strength = 0;
            if (password.length >= 8) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^a-zA-Z0-9]/.test(password)) strength++;
            
            if (strength <= 1) {
                strengthDiv.className = 'password-strength strength-weak';
                strengthDiv.textContent = '⚠️ Contraseña débil';
            } else if (strength === 2) {
                strengthDiv.className = 'password-strength strength-medium';
                strengthDiv.textContent = '⚡ Contraseña media';
            } else {
                strengthDiv.className = 'password-strength strength-strong';
                strengthDiv.textContent = '✅ Contraseña fuerte';
            }
        }

        function validateForm() {
            const usuario = document.getElementById('usuario').value.trim();
            const nombre = document.getElementById('nombre_usuario').value.trim();
            const password = document.getElementById('password').value;
            const passwordConfirm = document.getElementById('password_confirm').value;
            const rol = document.getElementById('rol').value;
            
            if (!nombre) {
                alert('Por favor ingresa el nombre completo');
                return false;
            }
            
            if (!/^[a-zA-Z0-9_]+$/.test(usuario)) {
                alert('El usuario solo puede contener letras, números y guiones bajos');
                return false;
            }
            
            // Si ingresó contraseña, validar
            if (password || passwordConfirm) {
                if (password.length < 6) {
                    alert('La contraseña debe tener al menos 6 caracteres');
                    return false;
                }
                
                if (password !== passwordConfirm) {
                    alert('Las contraseñas no coinciden');
                    return false;
                }
            }
            
            if (!rol) {
                alert('Por favor selecciona un rol');
                return false;
            }
            
            return confirm('¿Confirmar cambios en el usuario?');
        }

        function resetPassword() {
            document.getElementById('password').value = '';
            document.getElementById('password_confirm').value = '';
            document.getElementById('password-strength').style.display = 'none';
        }
    </script>
</body>
</html>