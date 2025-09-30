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
        $nombre_proveedor = strtoupper(trim($_POST["nombre_proveedor"]));
        $telefono_proveedor = trim($_POST["telefono_proveedor"]);
        $direccion_proveedor = strtoupper(trim($_POST["direccion_proveedor"]));
        $estado_proveedor = $_POST["estado_proveedor"];
        $productos = isset($_POST["productos"]) ? $_POST["productos"] : array();
        
        // Actualizar datos del proveedor
        $sentencia = $conexion->prepare("UPDATE proveedores SET nombre_proveedor=?, telefono_proveedor=?, direccion_proveedor=?, estado_proveedor=? WHERE id = ?");
        $resultado = $sentencia->execute([$nombre_proveedor, $telefono_proveedor, $direccion_proveedor, $estado_proveedor, $id]);
        
        if ($resultado === TRUE) {
            // Eliminar productos asociados existentes
            $sentenciaEliminar = $conexion->prepare("DELETE FROM proveedor_producto WHERE id_proveedor = ?");
            $sentenciaEliminar->execute([$id]);
            
            // Insertar nuevas relaciones con productos y precios
            if (!empty($productos)) {
                $sentenciaRelacion = $conexion->prepare("INSERT INTO proveedor_producto (id_proveedor, id_producto, precio_compra) VALUES (?, ?, ?)");
                
                foreach ($productos as $producto_data) {
                    if (isset($producto_data['id']) && is_numeric($producto_data['id'])) {
                        $precio_compra = isset($producto_data['precio']) && is_numeric($producto_data['precio']) ? $producto_data['precio'] : 0.00;
                        $sentenciaRelacion->execute([$id, $producto_data['id'], $precio_compra]);
                    }
                }
            }
            
            // Confirmar transacción
            $conexion->commit();
            
            $titulo = "Proveedor Actualizado Correctamente";
            $cantidadProductos = count($productos);
            if ($cantidadProductos > 0) {
                $mensaje = "Los datos del proveedor <strong>$nombre_proveedor</strong> han sido actualizados exitosamente con <strong>$cantidadProductos</strong> producto(s) asociado(s).";
            } else {
                $mensaje = "Los datos del proveedor <strong>$nombre_proveedor</strong> han sido actualizados exitosamente. No tiene productos asociados actualmente.";
            }
            $tipo = "success";
        } else {
            $conexion->rollback();
            $titulo = "Error al Actualizar Proveedor";
            $mensaje = "No se pudo actualizar el proveedor. Por favor, verifica los datos e intenta nuevamente.";
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