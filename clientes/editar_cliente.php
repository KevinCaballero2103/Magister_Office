<?php
// Variables para JavaScript
$mensaje = "";
$tipo = "";
$titulo = "";

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        include_once "../db.php";

        $id = $_POST["id"];
        $nombre_cliente = strtoupper(trim($_POST["nombre_cliente"]));
        $apellido_cliente = strtoupper(trim($_POST["apellido_cliente"]));
        $ci_ruc_cliente = trim($_POST["ci_ruc_cliente"]);
        $telefono_cliente = trim($_POST["telefono_cliente"]);
        $correo_cliente = trim($_POST["correo_cliente"]);
        $direccion_cliente = strtoupper(trim($_POST["direccion_cliente"]));
        $estado_cliente = $_POST["estado_cliente"];

        $sentencia = $conexion->prepare("UPDATE clientes SET nombre_cliente = ?, apellido_cliente = ?, ci_ruc_cliente = ?, telefono_cliente = ?, correo_cliente = ?, direccion_cliente = ?, estado_cliente = ? WHERE id = ?;");
        $resultado = $sentencia->execute([$nombre_cliente, $apellido_cliente, $ci_ruc_cliente, $telefono_cliente, $correo_cliente, $direccion_cliente, $estado_cliente, $id]);

        if ($resultado === TRUE) {
            $titulo = "Cliente Actualizado Correctamente";
            $mensaje = "Los datos del cliente <strong>$nombre_cliente $apellido_cliente</strong> han sido actualizados exitosamente en el sistema.";
            $tipo = "success";
        } else {
            $titulo = "Error al Actualizar Cliente";
            $mensaje = "No se pudo actualizar el cliente. Por favor, verifica los datos e intenta nuevamente.";
            $tipo = "error";
        }
    }
} catch (Exception $e) {
    $titulo = "Error del Sistema";
    $mensaje = "Ocurrió un error inesperado: " . $e->getMessage();
    $tipo = "error";
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
        .main-content {
            background: #2c3e50 !important;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
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
            const tipo = '<?php echo $tipo; ?>';
            const titulo = '<?php echo addslashes($titulo); ?>';
            const mensaje = '<?php echo addslashes($mensaje); ?>';
            
            const icono = tipo === 'success' ? '✅' : '❌';
            const claseIcono = tipo === 'success' ? 'success-icon' : 'error-icon';
            
            const contentHTML = `
                <div class='message-container'>
                    <span class='status-icon ${claseIcono}'>${icono}</span>
                    
                    <h1 class='message-title'>${titulo}</h1>
                    
                    <div class='message-content'>
                        ${mensaje}
                    </div>
                    
                    <div class='button-group'>
                        <a href='./listado_cliente.php' class='action-button'>
                            Ver Listado de Clientes
                        </a>
                        
                        <a href='./frm_guardar_cliente.php' class='secondary-button'>
                            Registrar Nuevo Cliente
                        </a>
                    </div>
                </div>
            `;
            
            mainContent.innerHTML = contentHTML;
        });
    </script>
</body>
</html>