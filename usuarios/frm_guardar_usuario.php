<?php
include_once "../auth.php";

// Solo administradores pueden crear usuarios
if (!tienePermiso(['ADMINISTRADOR'])) {
    header("Location: ../index.php?error=sin_permisos");
    exit();
}

registrarActividad('ACCESO', 'USUARIOS', 'Acceso al formulario de creación de usuario', null, null);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Usuario</title>
    <link href="../css/bulma.min.css" rel="stylesheet">
    <link href="../css/formularios.css" rel="stylesheet">
    <style>
        .main-content { background: #2c3e50 !important; color: white; }
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
    </style>
</head>
<body>
    <?php include '../menu.php'; ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mainContent = document.querySelector('.main-content');
            
            const formHTML = `
                <div class="form-container">
                    <h1 class="form-title">👤 Crear Nuevo Usuario</h1>
                    
                    <form action="./guardar_usuario.php" method="post" onsubmit="return validateForm()">
                        <div class="columns">
                            <div class="column is-6">
                                <div class="field">
                                    <label class="label">Nombre Completo *</label>
                                    <div class="control">
                                        <input class="input" type="text" name="nombre_usuario" id="nombre_usuario" 
                                               placeholder="Ej: Juan Pérez" required>
                                    </div>
                                </div>

                                <div class="field">
                                    <label class="label">Usuario (Login) *</label>
                                    <div class="control">
                                        <input class="input" type="text" name="usuario" id="usuario" 
                                               placeholder="Ej: jperez" required minlength="4" maxlength="50">
                                    </div>
                                    <p class="help" style="color: rgba(255,255,255,0.7);">
                                        Mínimo 4 caracteres. Solo letras, números y guiones bajos.
                                    </p>
                                </div>

                                <div class="field">
                                    <label class="label">Contraseña *</label>
                                    <div class="control">
                                        <input class="input" type="password" name="password" id="password" 
                                               placeholder="Mínimo 6 caracteres" required minlength="6" 
                                               oninput="checkPasswordStrength()">
                                    </div>
                                    <div id="password-strength" class="password-strength"></div>
                                </div>

                                <div class="field">
                                    <label class="label">Confirmar Contraseña *</label>
                                    <div class="control">
                                        <input class="input" type="password" name="password_confirm" id="password_confirm" 
                                               placeholder="Repite la contraseña" required minlength="6">
                                    </div>
                                </div>
                            </div>

                            <div class="column is-6">
                                <div class="field">
                                    <label class="label">Rol *</label>
                                    <div class="control">
                                        <div class="select is-fullwidth">
                                            <select name="rol" id="rol" required>
                                                <option value="">-- Seleccionar --</option>
                                                <option value="ADMINISTRADOR">🔴 ADMINISTRADOR (Acceso total)</option>
                                                <option value="CAJERO">🔵 CAJERO (Ventas y caja)</option>
                                                <option value="VENDEDOR">🟢 VENDEDOR (Solo ventas)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <p class="help" style="color: rgba(255,255,255,0.7);">
                                        Define los permisos del usuario en el sistema
                                    </p>
                                </div>

                                <div class="field">
                                    <label class="label">Estado *</label>
                                    <div class="control">
                                        <div class="select is-fullwidth">
                                            <select name="estado" id="estado" required>
                                                <option value="1" selected>✅ Activo</option>
                                                <option value="0">❌ Inactivo</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div style="background: rgba(52, 152, 219, 0.1); padding: 15px; border-radius: 8px; border: 2px solid rgba(52, 152, 219, 0.3); margin-top: 20px;">
                                    <strong style="color: #3498db;">ℹ️ Permisos por Rol:</strong><br><br>
                                    <strong>ADMINISTRADOR:</strong> Todo el sistema<br>
                                    <strong>CAJERO:</strong> Ventas, caja, productos<br>
                                    <strong>VENDEDOR:</strong> Solo ventas y clientes
                                </div>
                            </div>
                        </div>

                        <div class="field is-grouped" style="justify-content: center; margin-top: 30px;">
                            <div class="control">
                                <button type="submit" class="button">💾 Crear Usuario</button>
                            </div>
                            <div class="control">
                                <button type="reset" class="button">🔄 Limpiar</button>
                            </div>
                            <div class="control">
                                <a href="./listado_usuarios.php" class="secondary-button">📋 Ver Listado</a>
                            </div>
                        </div>
                    </form>
                </div>
            `;
            
            mainContent.innerHTML = formHTML;
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
            const password = document.getElementById('password').value;
            const passwordConfirm = document.getElementById('password_confirm').value;
            const nombre = document.getElementById('nombre_usuario').value.trim();
            const rol = document.getElementById('rol').value;
            
            if (!nombre) {
                alert('Por favor ingresa el nombre completo');
                return false;
            }
            
            if (!/^[a-zA-Z0-9_]+$/.test(usuario)) {
                alert('El usuario solo puede contener letras, números y guiones bajos');
                return false;
            }
            
            if (password.length < 6) {
                alert('La contraseña debe tener al menos 6 caracteres');
                return false;
            }
            
            if (password !== passwordConfirm) {
                alert('Las contraseñas no coinciden');
                return false;
            }
            
            if (!rol) {
                alert('Por favor selecciona un rol');
                return false;
            }
            
            return confirm('¿Confirmar creación del usuario?');
        }
    </script>
</body>
</html>