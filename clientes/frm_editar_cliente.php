<?php
include_once __DIR__ . "/../auth.php";

if (!tienePermiso(['ADMINISTRADOR'])) {
    header("Location: ../index.php?error=sin_permisos");
    exit();
}

// Validación y obtención de datos al inicio
if (!isset($_GET["id"])) {
    $error = "Necesito el parámetro id para identificar el cliente.";
} else {
    include '../db.php';
    $id = $_GET["id"];
    $sentencia = $conexion->prepare("SELECT * FROM clientes WHERE id = ?");
    $sentencia->execute([$id]);
    $cliente = $sentencia->fetch(PDO::FETCH_OBJ);
    if ($cliente === FALSE) {
        $error = "El cliente indicado no existe.";
    }
}

// Convertir datos para JavaScript
if (isset($cliente)) {
    $clienteJSON = json_encode($cliente);
} else {
    $clienteJSON = 'null';
    $mensajeError = isset($error) ? $error : 'Error desconocido';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Cliente</title>
    <link href="../css/bulma.min.css" rel="stylesheet">
    <link href="../css/formularios.css" rel="stylesheet">
    
    <!-- Solo estilos específicos -->
    <style>
        .main-content {
            background: #2c3e50 !important;
            color: white;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <?php include '../menu.php'; ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mainContent = document.querySelector('.main-content');
            const cliente = <?php echo $clienteJSON; ?>;
            
            if (cliente === null) {
                const errorMessage = '<?php echo isset($mensajeError) ? addslashes($mensajeError) : 'Error desconocido'; ?>';
                
                const errorHTML = `
                    <div class='error-container'>
                        <div class='error-title'>Error</div>
                        <div class='error-message'>${errorMessage}</div>
                        <a href='./listado_cliente.php' class='button'>
                            Volver al Listado
                        </a>
                    </div>
                `;
                
                mainContent.innerHTML = errorHTML;
                return;
            }
            
            const contentHTML = `
                <div class='form-container'>
                    <h1 class='form-title'>Editar Cliente</h1>
                    
                    <form action='./editar_cliente.php' method='post'>
                        <input type='hidden' name='id' value='${cliente.id}'>
                        
                        <div class='columns'>
                            <div class='column is-6'>
                                <div class='field'>
                                    <label class='label'>Nombre</label>
                                    <div class='control'>
                                        <input class='input' type='text' name='nombre_cliente' id='nombre_cliente' 
                                               placeholder='Ingresa el nombre' required 
                                               value='${cliente.nombre_cliente || ''}'>
                                    </div>
                                </div>

                                <div class='field'>
                                    <label class='label'>Apellido</label>
                                    <div class='control'>
                                        <input class='input' type='text' name='apellido_cliente' id='apellido_cliente' 
                                               placeholder='Ingresa el apellido' required 
                                               value='${cliente.apellido_cliente || ''}'>
                                    </div>
                                </div>

                                <div class='field'>
                                    <label class='label'>CI / RUC</label>
                                    <div class='control'>
                                        <input class='input' type='text' name='ci_ruc_cliente' id='ci_ruc_cliente' 
                                               placeholder='Ingresa CI o RUC' required 
                                               value='${cliente.ci_ruc_cliente || ''}'>
                                    </div>
                                </div>

                                <div class='field'>
                                    <label class='label'>Teléfono</label>
                                    <div class='control'>
                                        <input class='input' type='text' name='telefono_cliente' id='telefono_cliente' 
                                               placeholder='Ingresa el teléfono'
                                               value='${cliente.telefono_cliente || ''}'>
                                    </div>
                                </div>
                            </div>

                            <div class='column is-6'>
                                <div class='field'>
                                    <label class='label'>Correo</label>
                                    <div class='control'>
                                        <input class='input' type='email' name='correo_cliente' id='correo_cliente' 
                                               placeholder='correo@ejemplo.com'
                                               value='${cliente.correo_cliente || ''}'>
                                    </div>
                                </div>

                                <div class='field'>
                                    <label class='label'>Dirección</label>
                                    <div class='control'>
                                        <input class='input' type='text' name='direccion_cliente' id='direccion_cliente' 
                                               placeholder='Ingresa la dirección'
                                               value='${cliente.direccion_cliente || ''}'>
                                    </div>
                                </div>

                                <div class='field'>
                                    <label class='label'>Estado</label>
                                    <div class='control'>
                                        <div class='select is-fullwidth'>
                                            <select name='estado_cliente' id='estado_cliente'>
                                                <option value='1' ${cliente.estado_cliente == 1 ? 'selected' : ''}>Activo</option>
                                                <option value='0' ${cliente.estado_cliente == 0 ? 'selected' : ''}>Inactivo</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class='field'>
                                    <div style='height: 48px;'></div>
                                </div>
                            </div>
                        </div>            

                        <div class='button-group'>
                            <button type='submit' class='button'>
                                Guardar Cambios
                            </button>
                            
                            <button type='reset' class='secondary-button'>
                                Restaurar Valores
                            </button>
                            
                            <a href='./listado_cliente.php' class='secondary-button'>
                                Volver al Listado
                            </a>
                        </div>
                    </form>
                </div>
            `;
            
            mainContent.innerHTML = contentHTML;
        });
    </script>
</body>
</html>