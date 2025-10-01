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
        
        // Capturar datos del formulario
        $id_producto = intval($_POST["id_producto"]);
        $stock_actual = intval($_POST["stock_actual"]);
        $cantidad = intval($_POST["cantidad"]);
        $motivo = trim($_POST["motivo"]);
        $observaciones = trim($_POST["observaciones"]);
        $id_proveedor = !empty($_POST["id_proveedor"]) ? intval($_POST["id_proveedor"]) : null;
        $precio_compra = !empty($_POST["precio_compra"]) ? floatval($_POST["precio_compra"]) : null;
        
        // Validaciones
        if ($cantidad <= 0) {
            throw new Exception("La cantidad debe ser mayor a 0");
        }
        
        // Obtener información del producto para el mensaje
        $sentenciaProducto = $conexion->prepare("SELECT nombre_producto, stock_actual FROM productos WHERE id = ?");
        $sentenciaProducto->execute([$id_producto]);
        $producto = $sentenciaProducto->fetch(PDO::FETCH_OBJ);
        
        if (!$producto) {
            throw new Exception("El producto no existe");
        }
        
        // Calcular nuevo stock
        $stock_anterior = $producto->stock_actual;
        $stock_nuevo = $stock_anterior + $cantidad;
        
        // 1. Actualizar stock en tabla productos
        $sentenciaUpdateStock = $conexion->prepare("UPDATE productos SET stock_actual = ? WHERE id = ?");
        $resultadoUpdate = $sentenciaUpdateStock->execute([$stock_nuevo, $id_producto]);
        
        if (!$resultadoUpdate) {
            throw new Exception("Error al actualizar el stock del producto");
        }
        
        // 2. Insertar registro en movimientos_stock
        $sentenciaMovimiento = $conexion->prepare("
            INSERT INTO movimientos_stock 
            (id_producto, tipo_movimiento, cantidad, stock_anterior, stock_nuevo, motivo, id_proveedor, precio_compra, observaciones) 
            VALUES (?, 'entrada', ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $resultadoMovimiento = $sentenciaMovimiento->execute([
            $id_producto,
            $cantidad,
            $stock_anterior,
            $stock_nuevo,
            $motivo,
            $id_proveedor,
            $precio_compra,
            $observaciones
        ]);
        
        if (!$resultadoMovimiento) {
            throw new Exception("Error al registrar el movimiento de stock");
        }
        
        // Confirmar transacción
        $conexion->commit();
        
        $titulo = "Entrada de Stock Registrada";
        $mensaje = "Se registró la entrada de <strong>$cantidad unidades</strong> del producto <strong>{$producto->nombre_producto}</strong>.<br>Stock anterior: <strong>$stock_anterior</strong> → Stock nuevo: <strong>$stock_nuevo</strong>";
        $tipo = "success";
        
    } else {
        throw new Exception("Método de solicitud no válido");
    }
} catch (Exception $e) {
    if (isset($conexion) && $conexion->inTransaction()) {
        $conexion->rollback();
    }
    $titulo = "Error al Registrar Entrada";
    $mensaje = "Ocurrió un error: " . $e->getMessage();
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
                        <a href='./gestionar_stock.php' class='action-button'>
                            Volver a Gestión de Stock
                        </a>
                        
                        <a href='./frm_entrada_stock.php?id=<?php echo isset($id_producto) ? $id_producto : ''; ?>' class='secondary-button'>
                            Registrar Otra Entrada
                        </a>
                    </div>
                </div>
            `;
            
            mainContent.innerHTML = contentHTML;
        });
    </script>
</body>
</html>