<?php
include_once __DIR__ . "/../auth.php";
$mensaje = "";
$tipo = "";
$titulo = "";

// Validar que se proporcione el ID
if (!isset($_GET["id"])) {
    $titulo = "Error de Solicitud";
    $mensaje = "No se proporcionó el ID de la compra a eliminar.";
    $tipo = "error";
} else {
    $id = $_GET["id"];
    
    try {
        include_once "../db.php";
        
        // Iniciar transacción
        $conexion->beginTransaction();
        
        // Verificar que la compra existe y obtener su información
        $sentenciaVerificar = $conexion->prepare("
            SELECT c.*, p.nombre_proveedor 
            FROM compras c 
            INNER JOIN proveedores p ON c.id_proveedor = p.id 
            WHERE c.id = ?
        ");
        $sentenciaVerificar->execute([$id]);
        $compra = $sentenciaVerificar->fetch(PDO::FETCH_OBJ);
        
        if ($compra === FALSE) {
            $titulo = "Compra No Encontrada";
            $mensaje = "La compra que intentas eliminar no existe en el sistema.";
            $tipo = "error";
        } else {
            $nombreProveedor = $compra->nombre_proveedor;
            $totalCompra = $compra->total_compra;
            
            // Obtener detalles de la compra para revertir stock
            $sentenciaDetalles = $conexion->prepare("SELECT id_producto, cantidad FROM detalle_compras WHERE id_compra = ?");
            $sentenciaDetalles->execute([$id]);
            $detalles = $sentenciaDetalles->fetchAll(PDO::FETCH_OBJ);
            
            // REVERTIR STOCK: Restar las cantidades que se sumaron
            $sentenciaRevertirStock = $conexion->prepare("UPDATE productos SET stock_actual = stock_actual - ? WHERE id = ?");
            $sentenciaHistorial = $conexion->prepare("
                INSERT INTO historial_stock (id_producto, tipo_movimiento, cantidad, stock_anterior, stock_nuevo, motivo, id_referencia) 
                VALUES (?, 'SALIDA', ?, ?, ?, ?, ?)
            ");
            $sentenciaStockActual = $conexion->prepare("SELECT stock_actual FROM productos WHERE id = ?");
            
            foreach ($detalles as $detalle) {
                // Obtener stock actual
                $sentenciaStockActual->execute([$detalle->id_producto]);
                $stockAnterior = $sentenciaStockActual->fetchColumn();
                $stockAnterior = $stockAnterior === false ? 0 : intval($stockAnterior);
                
                // Revertir stock (restar lo que se sumó)
                $sentenciaRevertirStock->execute([$detalle->cantidad, $detalle->id_producto]);
                
                // Registrar en historial
                $stockNuevo = $stockAnterior - $detalle->cantidad;
                $sentenciaHistorial->execute([
                    $detalle->id_producto,
                    $detalle->cantidad,
                    $stockAnterior,
                    $stockNuevo,
                    "ELIMINACIÓN COMPRA #$id",
                    $id
                ]);
            }
            
            // Eliminar movimiento de caja
            $sentenciaEliminarCaja = $conexion->prepare("DELETE FROM caja WHERE tipo_movimiento = 'EGRESO' AND categoria = 'COMPRA' AND id_referencia = ?");
            $sentenciaEliminarCaja->execute([$id]);
            
            // Eliminar forma de pago
            $sentenciaEliminarPago = $conexion->prepare("DELETE FROM pagos_compra WHERE id_compra = ?");
            $sentenciaEliminarPago->execute([$id]);
            
            // Eliminar detalles de compra
            $sentenciaEliminarDetalles = $conexion->prepare("DELETE FROM detalle_compras WHERE id_compra = ?");
            $sentenciaEliminarDetalles->execute([$id]);
            
            // Eliminar la compra
            $sentenciaEliminar = $conexion->prepare("DELETE FROM compras WHERE id = ?");
            $resultado = $sentenciaEliminar->execute([$id]);
            
            if ($resultado === TRUE) {
                // Confirmar transacción
                $conexion->commit();
                
                $titulo = "Compra Eliminada Correctamente";
                $mensaje = "La compra <strong>#$id</strong> del proveedor <strong>$nombreProveedor</strong> por un total de <strong>₲ " . number_format($totalCompra, 0, ',', '.') . "</strong> ha sido eliminada exitosamente del sistema.<br><br>
                            <strong>Acciones realizadas:</strong><br>
                            • Stock revertido en " . count($detalles) . " producto(s) ✓<br>
                            • Movimiento de caja eliminado ✓<br>
                            • Historial de stock actualizado ✓";
                $tipo = "success";
            } else {
                $conexion->rollback();
                $titulo = "Error al Eliminar Compra";
                $mensaje = "No se pudo eliminar la compra <strong>#$id</strong>. Por favor, intenta nuevamente.";
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
                        <a href='./listado_compras.php' class='action-button'>
                            Ver Listado de Compras
                        </a>
                        
                        <a href='./frm_registrar_compra.php' class='secondary-button'>
                            Registrar Nueva Compra
                        </a>
                    </div>
                </div>
            `;
            
            mainContent.innerHTML = contentHTML;
        });
    </script>
</body>
</html>