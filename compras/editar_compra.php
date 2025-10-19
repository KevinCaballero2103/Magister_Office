<?php
$mensaje = "";
$tipo = "";
$titulo = "";

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        include_once "../db.php";

        $id_compra = $_POST["id"];
        $productos = isset($_POST['productos']) ? $_POST['productos'] : array();
        $total_compra = isset($_POST['total_compra']) ? floatval($_POST['total_compra']) : 0;

        if (empty($productos)) {
            throw new Exception("Debe tener al menos un producto");
        }

        // Iniciar transacción
        $conexion->beginTransaction();

        // Obtener ID del proveedor de la compra
        $sentenciaObtenerCompra = $conexion->prepare("SELECT id_proveedor FROM compras WHERE id = ?");
        $sentenciaObtenerCompra->execute([$id_compra]);
        $id_proveedor = $sentenciaObtenerCompra->fetchColumn();
        
        if (!$id_proveedor) {
            throw new Exception("No se encontró la compra");
        }

        // 1. OBTENER PRODUCTOS ORIGINALES PARA COMPARAR
        $sentenciaProductosOriginales = $conexion->prepare("
            SELECT id, id_producto, cantidad FROM detalle_compras WHERE id_compra = ?
        ");
        $sentenciaProductosOriginales->execute([$id_compra]);
        $productosOriginales = $sentenciaProductosOriginales->fetchAll(PDO::FETCH_ASSOC);
        $mapOriginal = [];
        foreach ($productosOriginales as $po) {
            $mapOriginal[$po['id']] = $po;
        }

        // 2. PROCESAR PRODUCTOS EXISTENTES (ACTUALIZAR CANTIDADES)
        $sentenciaActualizar = $conexion->prepare(
            "UPDATE detalle_compras SET cantidad = ?, subtotal = ? WHERE id = ?"
        );

        $sentenciaAjustarStock = $conexion->prepare(
            "UPDATE productos SET stock_actual = stock_actual + ? WHERE id = ?"
        );

        $sentenciaHistorial = $conexion->prepare(
            "INSERT INTO historial_stock (id_producto, tipo_movimiento, cantidad, stock_anterior, stock_nuevo, motivo, id_referencia) 
             VALUES (?, 'AJUSTE', ?, ?, ?, ?, ?)"
        );

        $sentenciaStockActual = $conexion->prepare(
            "SELECT stock_actual FROM productos WHERE id = ?"
        );

        // Obtener precios de compra (son fijos del proveedor)
        $sentenciaObtenerPrecios = $conexion->prepare(
            "SELECT id_producto, precio_compra FROM detalle_compras WHERE id_compra = ?"
        );
        $sentenciaObtenerPrecios->execute([$id_compra]);
        $preciosActuales = [];
        foreach ($sentenciaObtenerPrecios->fetchAll(PDO::FETCH_ASSOC) as $p) {
            $preciosActuales[$p['id_producto']] = $p['precio_compra'];
        }

        foreach ($productos as $prod) {
            // Si id_detalle está vacío, es un producto NUEVO
            if (empty($prod['id_detalle']) || $prod['id_detalle'] === '') {
                // INSERTAR NUEVO PRODUCTO
                $id_producto = intval($prod['id_producto']);
                $cantidad_nueva = intval($prod['cantidad_nueva']);
                
                // Obtener ID del proveedor de la compra
                $sentenciaObtenerProveedor = $conexion->prepare("SELECT id_proveedor FROM compras WHERE id = ?");
                $sentenciaObtenerProveedor->execute([$id_compra]);
                $id_proveedor = $sentenciaObtenerProveedor->fetchColumn();
                
                // Obtener precio del proveedor
                $sentenciaObtenerPrecio = $conexion->prepare(
                    "SELECT precio_compra FROM proveedor_producto WHERE id_proveedor = ? AND id_producto = ?"
                );
                $sentenciaObtenerPrecio->execute([$id_proveedor, $id_producto]);
                $precio = $sentenciaObtenerPrecio->fetchColumn();
                
                if ($precio === false) {
                    throw new Exception("No se puede obtener el precio del producto ID $id_producto");
                }
                $precio = floatval($precio);
                $subtotal = $cantidad_nueva * $precio;
                
                // Insertar en detalle_compras
                $sentenciaInsertarDetalle = $conexion->prepare(
                    "INSERT INTO detalle_compras (id_compra, id_producto, cantidad, precio_unitario, subtotal) 
                     VALUES (?, ?, ?, ?, ?)"
                );
                $sentenciaInsertarDetalle->execute([$id_compra, $id_producto, $cantidad_nueva, $precio, $subtotal]);
                
                // Ajustar stock (ENTRADA)
                $sentenciaStockActual->execute([$id_producto]);
                $stockAnterior = intval($sentenciaStockActual->fetchColumn());
                
                $sentenciaAjustarStock->execute([$cantidad_nueva, $id_producto]);
                $stockNuevo = $stockAnterior + $cantidad_nueva;
                
                $sentenciaHistorial->execute([
                    $id_producto,
                    $cantidad_nueva,
                    $stockAnterior,
                    $stockNuevo,
                    "AJUSTE COMPRA #$id_compra: +$cantidad_nueva unidades (nuevo)",
                    $id_compra
                ]);
                
            } else {
                // ACTUALIZAR PRODUCTO EXISTENTE
                $id_detalle = intval($prod['id_detalle']);
                $id_producto = intval($prod['id_producto']);
                $cantidad_original = intval($prod['cantidad_original']);
                $cantidad_nueva = intval($prod['cantidad_nueva']);
                
                // Obtener precio fijo del detalle
                $sentenciaObtenerPrecio = $conexion->prepare(
                    "SELECT precio_unitario FROM detalle_compras WHERE id = ?"
                );
                $sentenciaObtenerPrecio->execute([$id_detalle]);
                $precio_fijo = floatval($sentenciaObtenerPrecio->fetchColumn());
                
                $nuevo_subtotal = $cantidad_nueva * $precio_fijo;
                
                // Actualizar cantidad y subtotal
                $sentenciaActualizar->execute([
                    $cantidad_nueva,
                    $nuevo_subtotal,
                    $id_detalle
                ]);
                
                // Calcular diferencia de cantidad
                $diferencia = $cantidad_nueva - $cantidad_original;
                
                if ($diferencia != 0) {
                    // Obtener stock actual
                    $sentenciaStockActual->execute([$id_producto]);
                    $stockAnterior = intval($sentenciaStockActual->fetchColumn());
                    
                    // Ajustar stock
                    $sentenciaAjustarStock->execute([$diferencia, $id_producto]);
                    $stockNuevo = $stockAnterior + $diferencia;
                    
                    // Registrar en historial
                    $motivo = $diferencia > 0 
                        ? "AJUSTE COMPRA #$id_compra: +$diferencia unidades"
                        : "AJUSTE COMPRA #$id_compra: " . abs($diferencia) . " unidades";
                    
                    $sentenciaHistorial->execute([
                        $id_producto,
                        abs($diferencia),
                        $stockAnterior,
                        $stockNuevo,
                        $motivo,
                        $id_compra
                    ]);
                }
            }
        }

        // 3. ELIMINAR PRODUCTOS QUE YA NO ESTÁN EN LA COMPRA
        $idsProductosActuales = array_filter(array_map(function($p) { 
            return !empty($p['id_detalle']) ? intval($p['id_detalle']) : null; 
        }, $productos));

        foreach ($mapOriginal as $originalId => $original) {
            if (!in_array($originalId, $idsProductosActuales)) {
                // ELIMINAR ESTE PRODUCTO
                $id_producto = $original['id_producto'];
                $cantidad = $original['cantidad'];
                
                // Obtener stock actual
                $sentenciaStockActual->execute([$id_producto]);
                $stockAnterior = intval($sentenciaStockActual->fetchColumn());
                
                // Revertir stock (SALIDA)
                $sentenciaAjustarStock->execute([-$cantidad, $id_producto]);
                $stockNuevo = $stockAnterior - $cantidad;
                
                // Registrar en historial
                $sentenciaHistorial->execute([
                    $id_producto,
                    $cantidad,
                    $stockAnterior,
                    $stockNuevo,
                    "AJUSTE COMPRA #$id_compra: -$cantidad unidades (eliminado)",
                    $id_compra
                ]);
                
                // Eliminar de detalle_compras
                $sentenciaEliminar = $conexion->prepare("DELETE FROM detalle_compras WHERE id = ?");
                $sentenciaEliminar->execute([$originalId]);
            }
        }

        // 4. ACTUALIZAR TOTAL DE LA COMPRA
        $sentenciaActualizarTotal = $conexion->prepare(
            "UPDATE compras SET total_compra = ? WHERE id = ?"
        );
        $sentenciaActualizarTotal->execute([$total_compra, $id_compra]);

        // Confirmar transacción
        $conexion->commit();

        $titulo = "Compra Actualizada Correctamente";
        $mensaje = "Los datos de la compra <strong>#$id_compra</strong> han sido actualizados exitosamente.<br><br>
                    <strong>Cambios realizados:</strong><br>
                    • Productos y cantidades actualizados ✓<br>
                    • Stock ajustado según cambios ✓<br>
                    • Nuevo total: <strong>₲ " . number_format($total_compra, 0, ',', '.') . "</strong>";
        $tipo = "success";

    } else {
        throw new Exception("Método de solicitud no válido");
    }
} catch (Exception $e) {
    if (isset($conexion) && $conexion->inTransaction()) {
        $conexion->rollBack();
    }

    error_log("editar_compra.php - ERROR: " . $e->getMessage());

    $titulo = "Error al Actualizar Compra";
    $mensaje = "No se pudo completar la actualización:<br><br>" . htmlspecialchars($e->getMessage());
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
            const tipo = <?php echo json_encode($tipo); ?>;
            const titulo = <?php echo json_encode($titulo); ?>;
            const mensaje = <?php echo json_encode($mensaje); ?>;
            
            const icono = tipo === 'success' ? '📦✅' : '❌';
            const claseIcono = tipo === 'success' ? 'success-icon' : 'error-icon';
            
            const contentHTML = `
                <div class='message-container'>
                    <span class='status-icon ${claseIcono}'>${icono}</span>
                    
                    <h1 class='message-title'>${titulo}</h1>
                    
                    <div class='message-content'>
                        ${mensaje}
                    </div>
                    
                    <div class='button-group'>
                        <a href='./listado_compras.php' class='action-button'>
                            📋 Ver Listado de Compras
                        </a>
                        
                        <a href='./frm_registrar_compra.php' class='secondary-button'>
                            ➕ Registrar Nueva Compra
                        </a>
                    </div>
                </div>
            `;
            
            mainContent.innerHTML = contentHTML;
        });
    </script>
</body>
</html>