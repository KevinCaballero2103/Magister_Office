<?php
include_once __DIR__ . "/../auth.php";
$mensaje = "";
$tipo = "";
$titulo = "";

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        include_once "../db.php";
        
        // Iniciar transacción
        $conexion->beginTransaction();
        
        // Capturar datos del producto
        $nombre_producto = strtoupper(trim($_POST["nombre_producto"]));
        $codigo_producto = trim($_POST["codigo_producto"]);
        $precio_venta = floatval($_POST["precio_venta"]);
        $stock_actual = intval($_POST["stock_actual"]);
        $stock_minimo = intval($_POST["stock_minimo"]);
        $estado_producto = $_POST["estado_producto"];
        $proveedores = isset($_POST["proveedores"]) ? $_POST["proveedores"] : array();
        
        // Preparar consulta para insertar producto
        $sentencia = $conexion->prepare("INSERT INTO productos 
            (nombre_producto, codigo_producto, precio_venta, stock_actual, stock_minimo, estado_producto) 
            VALUES (?, ?, ?, ?, ?, ?)");
        
        $resultado = $sentencia->execute([
            $nombre_producto, $codigo_producto, $precio_venta, $stock_actual, $stock_minimo, $estado_producto
        ]);
        
        if ($resultado === TRUE) {
            $producto_id = $conexion->lastInsertId();
            
            // Insertar relaciones con proveedores (si hay proveedores seleccionados)
            if (!empty($proveedores)) {
                $sentenciaRelacion = $conexion->prepare("INSERT INTO proveedor_producto (id_proveedor, id_producto, precio_compra) VALUES (?, ?, ?)");
                
                foreach ($proveedores as $proveedor_data) {
                    if (is_array($proveedor_data) && isset($proveedor_data['id']) && isset($proveedor_data['precio'])) {
                        $proveedor_id = intval($proveedor_data['id']);
                        $precio_compra = floatval($proveedor_data['precio']);
                        
                        if ($proveedor_id > 0) {
                            $sentenciaRelacion->execute([$proveedor_id, $producto_id, $precio_compra]);
                        }
                    }
                }
            }
            
            // Confirmar transacción
            $conexion->commit();
            
            $titulo = "Producto Registrado Exitosamente";
            $cantidadProveedores = count($proveedores);
            if ($cantidadProveedores > 0) {
                $mensaje = "El producto <strong>$nombre_producto</strong> ha sido registrado correctamente con <strong>$cantidadProveedores</strong> proveedor(es) asociado(s).";
            } else {
                $mensaje = "El producto <strong>$nombre_producto</strong> ha sido registrado correctamente. Podrás asociar proveedores más adelante.";
            }
            $tipo = "success";
        } else {
            $conexion->rollback();
            $titulo = "Error al Registrar Producto";
            $mensaje = "No se pudo registrar el producto. Por favor, verifica los datos e intenta nuevamente.";
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
                            Registrar Otro Producto
                        </a>
                    </div>
                </div>
            `;
            
            mainContent.innerHTML = contentHTML;
        });
    </script>
</body>
</html>