<?php
include_once __DIR__ . "/../auth.php";
$mensaje = "";
$tipo = "";
$titulo = "";

// Validar que se proporcione el ID
if (!isset($_GET["id"])) {
    $titulo = "Error de Solicitud";
    $mensaje = "No se proporcionó el ID del producto a eliminar.";
    $tipo = "error";
} else {
    $id = $_GET["id"];
    
    try {
        include_once "../db.php";
        
        // Iniciar transacción
        $conexion->beginTransaction();
        
        // Verificar que el producto existe y obtener su información
        $sentenciaVerificar = $conexion->prepare("SELECT nombre_producto FROM productos WHERE id = ?");
        $sentenciaVerificar->execute([$id]);
        $producto = $sentenciaVerificar->fetch(PDO::FETCH_OBJ);
        
        if ($producto === FALSE) {
            $titulo = "Producto No Encontrado";
            $mensaje = "El producto que intentas eliminar no existe en el sistema.";
            $tipo = "error";
        } else {
            $nombreProducto = $producto->nombre_producto;
            
            // Verificar si tiene proveedores asociados
            $sentenciaProveedores = $conexion->prepare("SELECT COUNT(*) as total FROM proveedor_producto WHERE id_producto = ?");
            $sentenciaProveedores->execute([$id]);
            $totalProveedores = $sentenciaProveedores->fetch(PDO::FETCH_OBJ)->total;
            
            // Eliminar primero las relaciones con proveedores (si existen)
            if ($totalProveedores > 0) {
                $sentenciaEliminarRelaciones = $conexion->prepare("DELETE FROM proveedor_producto WHERE id_producto = ?");
                $sentenciaEliminarRelaciones->execute([$id]);
            }
            
            // Eliminar el producto
            $sentenciaEliminar = $conexion->prepare("DELETE FROM productos WHERE id = ?");
            $resultado = $sentenciaEliminar->execute([$id]);
            
            if ($resultado === TRUE) {
                // Confirmar transacción
                $conexion->commit();
                
                $titulo = "Producto Eliminado Correctamente";
                if ($totalProveedores > 0) {
                    $mensaje = "El producto <strong>$nombreProducto</strong> y sus <strong>$totalProveedores</strong> proveedor(es) asociado(s) han sido eliminados exitosamente del sistema.";
                } else {
                    $mensaje = "El producto <strong>$nombreProducto</strong> ha sido eliminado exitosamente del sistema.";
                }
                $tipo = "success";
            } else {
                $conexion->rollback();
                $titulo = "Error al Eliminar Producto";
                $mensaje = "No se pudo eliminar el producto <strong>$nombreProducto</strong>. Por favor, intenta nuevamente.";
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
                        <a href='./listado_producto.php' class='action-button'>
                            Ver Listado de Productos
                        </a>
                        
                        <a href='./frm_guardar_producto.php' class='secondary-button'>
                            Registrar Nuevo Producto
                        </a>
                    </div>
                </div>
            `;
            
            mainContent.innerHTML = contentHTML;
        });
    </script>
</body>
</html>