<?php
include_once "../auth.php";

// Solo administradores pueden ver usuarios
if (!tienePermiso(['ADMINISTRADOR'])) {
    header("Location: ../index.php?error=sin_permisos");
    exit();
}

include_once "../db.php";

registrarActividad('ACCESO', 'USUARIOS', 'Acceso al listado de usuarios', null, null);

// Obtener usuarios
$sentencia = $conexion->prepare("SELECT * FROM usuarios ORDER BY nombre_usuario ASC");
$sentencia->execute();
$usuarios = $sentencia->fetchAll(PDO::FETCH_OBJ);
$usuariosJSON = json_encode($usuarios);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Usuarios</title>
    <link href="../css/bulma.min.css" rel="stylesheet">
    <link href="../css/listados.css" rel="stylesheet">
    <style>
        .main-content { background: #2c3e50 !important; color: white; }
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
        .ultimo-acceso {
            font-size: 0.8rem;
            color: rgba(255,255,255,0.7);
        }
    </style>
</head>
<body>
    <?php include '../menu.php'; ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mainContent = document.querySelector('.main-content');
            const usuarios = <?php echo $usuariosJSON; ?>;
            
            const formatFecha = (fecha) => {
                if (!fecha) return 'Nunca';
                const d = new Date(fecha);
                return d.toLocaleString('es-PY', { 
                    day: '2-digit', 
                    month: '2-digit', 
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            };
            
            let filasHTML = '';
            usuarios.forEach(user => {
                const estadoBadge = user.estado == 1 
                    ? '<span class="status-active">✅ Activo</span>' 
                    : '<span class="status-inactive">❌ Inactivo</span>';
                
                const rolBadge = `<span class="role-badge role-${user.rol.toLowerCase()}">${user.rol}</span>`;
                
                const ultimoAcceso = user.ultimo_acceso 
                    ? `<div class="ultimo-acceso">Último acceso: ${formatFecha(user.ultimo_acceso)}</div>`
                    : '<div class="ultimo-acceso">Sin accesos</div>';
                
                filasHTML += `
                    <tr>
                        <td><strong>#${user.id}</strong></td>
                        <td>
                            <strong>${user.nombre_usuario}</strong><br>
                            ${ultimoAcceso}
                        </td>
                        <td><strong>${user.usuario}</strong></td>
                        <td>${rolBadge}</td>
                        <td>${estadoBadge}</td>
                        <td>
                            <a href="./frm_editar_usuario.php?id=${user.id}" class="edit-link">✏️ Editar</a>
                        </td>
                    </tr>
                `;
            });
            
            const contentHTML = `
                <div class="list-container">
                    <h1 class="list-title">👥 Gestión de Usuarios</h1>
                    
                    <div style="text-align: center; margin-bottom: 20px;">
                        <a href="./frm_guardar_usuario.php" class="button" style="margin-right: 10px;">
                            ➕ Crear Nuevo Usuario
                        </a>
                        <a href="../index.php" class="secondary-button">
                            🏠 Volver al Inicio
                        </a>
                    </div>
                    
                    <div style="overflow-x: auto;">
                        <table class="table is-fullwidth custom-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>NOMBRE</th>
                                    <th>USUARIO (LOGIN)</th>
                                    <th>ROL</th>
                                    <th>ESTADO</th>
                                    <th>ACCIONES</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${filasHTML}
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
            
            mainContent.innerHTML = contentHTML;
        });
    </script>
</body>
</html>