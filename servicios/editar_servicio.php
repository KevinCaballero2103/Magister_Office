<?php
// Variables para JavaScript
$mensaje = "";
$tipo = "";
$titulo = "";

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        include_once "../db.php";

        $id = $_POST["id"];
        $nombre_servicio = strtoupper(trim($_POST["nombre_servicio"]));
        $categoria_servicio = strtoupper(trim($_POST["categoria_servicio"]));
        $estado_servicio = $_POST["estado_servicio"];

        $sentencia = $conexion->prepare("UPDATE servicios SET nombre_servicio = ?, categoria_servicio = ?, estado_servicio = ? WHERE id = ?;");
        $resultado = $sentencia->execute([$nombre_servicio, $categoria_servicio, $estado_servicio, $id]);

        if ($resultado === TRUE) {
            $titulo = "Servicio Actualizado Correctamente";
            $mensaje = "Los datos del servicio <strong>$nombre_servicio</strong> de la categoría <strong>$categoria_servicio</strong> han sido actualizados exitosamente en el sistema.";
            $tipo = "success";
        } else {
            $titulo = "Error al Actualizar Servicio";
            $mensaje = "No se pudo actualizar el servicio. Por favor, verifica los datos e intenta nuevamente.";
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
        /* Override específico para centrar contenido */
        .main-content {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
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
            
            // Determinar icono según el tipo
            const icono = tipo === 'success' ? '🔧✅' : '❌';
            const claseIcono = tipo === 'success' ? 'success-icon' : 'error-icon';
            
            const contentHTML = `
                <div class='message-container'>
                    <span class='status-icon ${claseIcono}'>${icono}</span>
                    
                    <h1 class='message-title'>${titulo}</h1>
                    
                    <div class='message-content'>
                        ${mensaje}
                    </div>
                    
                    <div class='button-group'>
                        <a href='./listado_servicio.php' class='action-button'>
                            📋 Ver Listado de Servicios
                        </a>
                        
                        <a href='./frm_guardar_servicio.php' class='secondary-button'>
                            ➕ Registrar Nuevo Servicio
                        </a>
                    </div>
                </div>
            `;
            
            mainContent.innerHTML = contentHTML;
        });
    </script>
</body>
</html>