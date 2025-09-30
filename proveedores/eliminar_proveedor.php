<?php
// Variables para JavaScript
$mensaje = "";
$tipo = "";
$titulo = "";

// Validar que se proporcione el ID
if (!isset($_GET["id"])) {
    $titulo = "Error de Solicitud";
    $mensaje = "No se proporcionó el ID del proveedor a eliminar.";
    $tipo = "error";
} else {
    $id = $_GET["id"];
    
    try {
        include_once "../db.php";
        
        // Iniciar transacción
        $conexion->beginTransaction();
        
        // Verificar que el proveedor existe y obtener su información
        $sentenciaVerificar = $conexion->prepare("SELECT nombre_proveedor FROM proveedores WHERE id = ?");
        $sentenciaVerificar->execute([$id]);
        $proveedor = $sentenciaVerificar->fetch(PDO::FETCH_OBJ);
        
        if ($proveedor === FALSE) {
            $titulo = "Proveedor No Encontrado";
            $mensaje = "El proveedor que intentas eliminar no existe en el sistema.";
            $tipo = "error";
        } else {
            $nombreProveedor = $proveedor->nombre_proveedor;
            
            // Verificar si tiene productos asociados
            $sentenciaProductos = $conexion->prepare("SELECT COUNT(*) as total FROM proveedor_producto WHERE id_proveedor = ?");
            $sentenciaProductos->execute([$id]);
            $totalProductos = $sentenciaProductos->fetch(PDO::FETCH_OBJ)->total;
            
            // Eliminar primero las relaciones con productos (si existen)
            if ($totalProductos > 0) {
                $sentenciaEliminarRelaciones = $conexion->prepare("DELETE FROM proveedor_producto WHERE id_proveedor = ?");
                $sentenciaEliminarRelaciones->execute([$id]);
            }
            
            // Eliminar el proveedor
            $sentenciaEliminar = $conexion->prepare("DELETE FROM proveedores WHERE id = ?");
            $resultado = $sentenciaEliminar->execute([$id]);
            
            if ($resultado === TRUE) {
                // Confirmar transacción
                $conexion->commit();
                
                $titulo = "Proveedor Eliminado Correctamente";
                if ($totalProductos > 0) {
                    $mensaje = "El proveedor <strong>$nombreProveedor</strong> y sus <strong>$totalProductos</strong> producto(s) asociado(s) han sido eliminados exitosamente del sistema.";
                } else {
                    $mensaje = "El proveedor <strong>$nombreProveedor</strong> ha sido eliminado exitosamente del sistema.";
                }
                $tipo = "success";
            } else {
                $conexion->rollback();
                $titulo = "Error al Eliminar Proveedor";
                $mensaje = "No se pudo eliminar el proveedor <strong>$nombreProveedor</strong>. Por favor, intenta nuevamente.";
                $tipo = "error";
            }
        }
    } catch (Exception $e) {
        if (isset($conexion) && $conexion->inTransaction()) {
            $conexion->rollback();
        }
        $titulo = "Error del Sistema";
        $mensaje = "Ocurrió un error inesperado: " . $e->getMessage();
        $tipo = "error";
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
                        <a href='./listado_proveedor.php' class='action-button'>
                            Ver Listado de Proveedores
                        </a>
                        
                        <a href='./frm_guardar_proveedor.php' class='secondary-button'>
                            Registrar Nuevo Proveedor
                        </a>
                    </div>
                </div>
            `;
            
            mainContent.innerHTML = contentHTML;
        });
    </script>
</body>
</html>