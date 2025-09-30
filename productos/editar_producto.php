<?php
// Variables para JavaScript
$mensaje = "";
$tipo = "";
$titulo = "";

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        include_once "../db.php";
        
        // Iniciar transacción
        $conexion->beginTransaction();
        
        $id = $_POST["id"];
        $nombre_producto = strtoupper(trim($_POST["nombre_producto"]));
        $codigo_producto = trim($_POST["codigo_producto"]);
        $precio_venta = $_POST["precio_venta"];
        $stock_actual = $_POST["stock_actual"];
        $stock_minimo = $_POST["stock_minimo"];
        $estado_producto = $_POST["estado_producto"];
        $proveedores = isset($_POST["proveedores"]) ? $_POST["proveedores"] : array();
        
        // Actualizar datos del producto
        $sentencia = $conexion->prepare("UPDATE productos SET nombre_producto=?, codigo_producto=?, precio_venta=?, stock_actual=?, stock_minimo=?, estado_producto=? WHERE id = ?");
        $resultado = $sentencia->execute([$nombre_producto, $codigo_producto, $precio_venta, $stock_actual, $stock_minimo, $estado_producto, $id]);
        
        if ($resultado === TRUE) {
            // Eliminar relaciones proveedor-producto existentes
            $sentenciaEliminar = $conexion->prepare("DELETE FROM proveedor_producto WHERE id_producto = ?");
            $sentenciaEliminar->execute([$id]);
            
            // Insertar nuevas relaciones con proveedores y precios
            if (!empty($proveedores)) {
                $sentenciaRelacion = $conexion->prepare("INSERT INTO proveedor_producto (id_proveedor, id_producto, precio_compra) VALUES (?, ?, ?)");
                
                foreach ($proveedores as $proveedor_data) {
                    if (isset($proveedor_data['id']) && is_numeric($proveedor_data['id'])) {
                        $precio_compra = isset($proveedor_data['precio']) && is_numeric($proveedor_data['precio']) ? $proveedor_data['precio'] : 0.00;
                        $sentenciaRelacion->execute([$proveedor_data['id'], $id, $precio_compra]);
                    }
                }
            }
            
            // Confirmar transacción
            $conexion->commit();
            
            $titulo = "Producto Actualizado Correctamente";
            $cantidadProveedores = count($proveedores);
            if ($cantidadProveedores > 0) {
                $mensaje = "Los datos del producto <strong>$nombre_producto</strong> han sido actualizados exitosamente con <strong>$cantidadProveedores</strong> proveedor(es) asociado(s).";
            } else {
                $mensaje = "Los datos del producto <strong>$nombre_producto</strong> han sido actualizados exitosamente. No tiene proveedores asociados actualmente.";
            }
            $tipo = "success";
        } else {
            $conexion->rollback();
            $titulo = "Error al Actualizar Producto";
            $mensaje = "No se pudo actualizar el producto. Por favor, verifica los datos e intenta nuevamente.";
            $tipo = "error";
        }
    }
} catch (Exception $e) {
    if ($conexion->inTransaction()) {
        $conexion->rollback();
    }
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