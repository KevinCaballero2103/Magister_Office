<?php
// Variables para JavaScript
$mensaje = "";
$tipo = "";
$titulo = "";

// Validar que se proporcione el ID
if (!isset($_GET["id"])) {
    $titulo = "Error de Solicitud";
    $mensaje = "No se proporcionó el ID de la venta a anular.";
    $tipo = "error";
} else {
    $id = $_GET["id"];
    
    try {
        include_once "../db.php";
        
        // Iniciar transacción
        $conexion->beginTransaction();
        
        // Verificar que la venta existe y obtener su información
        $sentenciaVerificar = $conexion->prepare("
            SELECT v.*, 
                   CONCAT(COALESCE(c.nombre_cliente, ''), ' ', COALESCE(c.apellido_cliente, '')) as nombre_cliente
            FROM ventas v
            LEFT JOIN clientes c ON v.id_cliente = c.id
            WHERE v.id = ?
        ");
        $sentenciaVerificar->execute([$id]);
        $venta = $sentenciaVerificar->fetch(PDO::FETCH_OBJ);
        
        if ($venta === FALSE) {
            $titulo = "Venta No Encontrada";
            $mensaje = "La venta que intentas anular no existe en el sistema.";
            $tipo = "error";
        } else if ($venta->estado_venta == 0) {
            $titulo = "Venta Ya Anulada";
            $mensaje = "La venta <strong>#$id</strong> ya se encuentra anulada.";
            $tipo = "error";
        } else {
            $nombreCliente = trim($venta->nombre_cliente) ?: "Cliente Genérico";
            $totalVenta = $venta->total_venta;
            
            // Obtener detalles de la venta para revertir stock (solo productos)
            $sentenciaDetalles = $conexion->prepare("
                SELECT id_item, tipo_item, cantidad, descripcion 
                FROM detalle_ventas 
                WHERE id_venta = ?
            ");
            $sentenciaDetalles->execute([$id]);
            $detalles = $sentenciaDetalles->fetchAll(PDO::FETCH_OBJ);
            
            // REVERTIR STOCK: Solo de productos (servicios NO tienen stock)
            $sentenciaRevertirStock = $conexion->prepare("UPDATE productos SET stock_actual = stock_actual + ? WHERE id = ?");
            $sentenciaHistorial = $conexion->prepare("
                INSERT INTO historial_stock (id_producto, tipo_movimiento, cantidad, stock_anterior, stock_nuevo, motivo, id_referencia) 
                VALUES (?, 'ENTRADA', ?, ?, ?, ?, ?)
            ");
            $sentenciaStockActual = $conexion->prepare("SELECT stock_actual FROM productos WHERE id = ?");
            
            $productosRevertidos = 0;
            
            foreach ($detalles as $detalle) {
                // Solo revertir stock de PRODUCTOS (no servicios)
                if ($detalle->tipo_item === 'PRODUCTO') {
                    // Obtener stock actual
                    $sentenciaStockActual->execute([$detalle->id_item]);
                    $stockAnterior = $sentenciaStockActual->fetchColumn();
                    $stockAnterior = $stockAnterior === false ? 0 : intval($stockAnterior);
                    
                    // Revertir stock (sumar lo que se restó)
                    $sentenciaRevertirStock->execute([$detalle->cantidad, $detalle->id_item]);
                    
                    // Registrar en historial
                    $stockNuevo = $stockAnterior + $detalle->cantidad;
                    $sentenciaHistorial->execute([
                        $detalle->id_item,
                        $detalle->cantidad,
                        $stockAnterior,
                        $stockNuevo,
                        "ANULACIÓN VENTA #$id",
                        $id
                    ]);
                    
                    $productosRevertidos++;
                }
            }
            
            // Eliminar movimiento de caja
            $sentenciaEliminarCaja = $conexion->prepare("DELETE FROM caja WHERE tipo_movimiento = 'INGRESO' AND categoria = 'VENTA' AND id_referencia = ?");
            $sentenciaEliminarCaja->execute([$id]);
            
            // ANULAR la venta (cambiar estado a 0, NO eliminar)
            $sentenciaAnular = $conexion->prepare("UPDATE ventas SET estado_venta = 0 WHERE id = ?");
            $resultado = $sentenciaAnular->execute([$id]);
            
            if ($resultado === TRUE) {
                // Confirmar transacción
                $conexion->commit();
                
                $titulo = "Venta Anulada Correctamente";
                $mensaje = "La venta <strong>#$id</strong> del cliente <strong>$nombreCliente</strong> por un total de <strong>₲ " . number_format($totalVenta, 0, ',', '.') . "</strong> ha sido anulada exitosamente.<br><br>
                            <strong>Acciones realizadas:</strong><br>
                            • Venta marcada como ANULADA ✓<br>
                            • Stock revertido en $productosRevertidos producto(s) ✓<br>
                            • Movimiento de caja eliminado ✓<br>
                            • Historial de stock actualizado ✓<br><br>
                            <strong>Nota:</strong> La venta permanece en el sistema para fines de auditoría, pero con estado ANULADA.";
                $tipo = "success";
            } else {
                $conexion->rollback();
                $titulo = "Error al Anular Venta";
                $mensaje = "No se pudo anular la venta <strong>#$id</strong>. Por favor, intenta nuevamente.";
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
            const tipo = <?php echo json_encode($tipo); ?>;
            const titulo = <?php echo json_encode($titulo); ?>;
            const mensaje = <?php echo json_encode($mensaje); ?>;
            
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
                        <a href='./listado_ventas.php' class='action-button'>
                            Ver Listado de Ventas
                        </a>
                        
                        <a href='./frm_registrar_venta.php' class='secondary-button'>
                            Registrar Nueva Venta
                        </a>
                    </div>
                </div>
            `;
            
            mainContent.innerHTML = contentHTML;
        });
    </script>
</body>
</html>