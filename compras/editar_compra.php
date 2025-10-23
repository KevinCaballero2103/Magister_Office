<?php
session_start();
$mensaje = "";
$tipo = "";
$titulo = "";

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Método de solicitud no válido");
    }

    include_once "../db.php"; // Debe proveer $conexion (PDO)

    // --- Opcional: CSRF (si tu formulario incluye token en sesión) ---
    if (isset($_SESSION['csrf_token']) && isset($_POST['csrf_token'])) {
        if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            throw new Exception("Token CSRF inválido");
        }
    }
    // Si no utilizás CSRF aún, esto no abortará (pero te recomiendo implementarlo).

    // Inputs
    $id_compra = isset($_POST["id"]) ? intval($_POST["id"]) : 0;
    $productos = isset($_POST['productos']) && is_array($_POST['productos']) ? $_POST['productos'] : [];
    $total_post = isset($_POST['total_compra']) ? floatval($_POST['total_compra']) : 0.0;

    if ($id_compra <= 0) throw new Exception("ID de compra inválido");
    if (empty($productos)) throw new Exception("Debe tener al menos un producto");

    // ---- Preparar bloqueo/transaction dependiendo de motor ----
    $useTableLocks = false;
    try {
        $conexion->beginTransaction();
        $inTransaction = $conexion->inTransaction();
    } catch (Throwable $t) {
        $inTransaction = false;
    }

    if (!$inTransaction) {
        // Si no hay transacciones disponibles (MyISAM), usamos LOCK TABLES como plan B
        $useTableLocks = true;
        $conexion->exec("LOCK TABLES compras WRITE, detalle_compras WRITE, productos WRITE, historial_stock WRITE, proveedor_producto READ");
    }

    // --- Obtener id_proveedor de la compra (verificar existencia) ---
    $st = $conexion->prepare("SELECT id_proveedor FROM compras WHERE id = ?");
    $st->execute([$id_compra]);
    $id_proveedor = $st->fetchColumn();
    if (!$id_proveedor) throw new Exception("No se encontró la compra #$id_compra");

    // --- Cargar productos originales para detectar eliminaciones y cambios ---
    $st = $conexion->prepare("SELECT id, id_producto, cantidad, precio_unitario FROM detalle_compras WHERE id_compra = ?");
    $st->execute([$id_compra]);
    $productosOriginales = $st->fetchAll(PDO::FETCH_ASSOC);
    $mapOriginalByDetalle = [];
    foreach ($productosOriginales as $po) {
        $mapOriginalByDetalle[intval($po['id'])] = $po;
    }

    // --- Preparar statements que vamos a reutilizar (mejora rendimiento) ---
    $stmtActualizarDetalle = $conexion->prepare("UPDATE detalle_compras SET cantidad = ?, subtotal = ? WHERE id = ?");
    $stmtInsertDetalle = $conexion->prepare("INSERT INTO detalle_compras (id_compra, id_producto, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)");
    $stmtEliminarDetalle = $conexion->prepare("DELETE FROM detalle_compras WHERE id = ?");
    $stmtObtenerPrecioProveedor = $conexion->prepare("SELECT precio_compra FROM proveedor_producto WHERE id_proveedor = ? AND id_producto = ?");
    $stmtStockActual = $conexion->prepare("SELECT stock_actual FROM productos WHERE id = ?");
    $stmtAjustarStock = $conexion->prepare("UPDATE productos SET stock_actual = stock_actual + ? WHERE id = ?");
    $stmtInsertHistorial = $conexion->prepare("INSERT INTO historial_stock (id_producto, tipo_movimiento, cantidad, stock_anterior, stock_nuevo, motivo, id_referencia) VALUES (?, 'AJUSTE', ?, ?, ?, ?, ?)");

    // --- Para recalcular total por servidor y evitar manipulación desde cliente ---
    $computed_total = 0.0;

    // --- Procesar cada producto enviado ---
    $idsDetallesRecibidos = [];
    foreach ($productos as $index => $prod) {
        // Validaciones base
        $id_producto = isset($prod['id_producto']) ? intval($prod['id_producto']) : 0;
        $cantidad_nueva = isset($prod['cantidad_nueva']) ? intval($prod['cantidad_nueva']) : 0;
        if ($id_producto <= 0) throw new Exception("Producto inválido en la fila $index");
        if ($cantidad_nueva < 0) throw new Exception("Cantidad inválida para producto ID $id_producto");

        // Nuevo producto (agregado)
        if (empty($prod['id_detalle'])) {
            // Obtener precio del proveedor
            $stmtObtenerPrecioProveedor->execute([$id_proveedor, $id_producto]);
            $precio_compra = $stmtObtenerPrecioProveedor->fetchColumn();
            if ($precio_compra === false) {
                throw new Exception("No se puede obtener el precio para producto ID $id_producto (proveedor $id_proveedor)");
            }
            $precio_compra = floatval($precio_compra);
            $subtotal = $cantidad_nueva * $precio_compra;

            // Insertar en detalle
            $stmtInsertDetalle->execute([$id_compra, $id_producto, $cantidad_nueva, $precio_compra, $subtotal]);

            // Ajustar stock (entrada)
            $stmtStockActual->execute([$id_producto]);
            $stockAnterior = intval($stmtStockActual->fetchColumn() ?: 0);
            $stmtAjustarStock->execute([$cantidad_nueva, $id_producto]);
            $stockNuevo = $stockAnterior + $cantidad_nueva;

            $stmtInsertHistorial->execute([
                $id_producto,
                $cantidad_nueva,
                $stockAnterior,
                $stockNuevo,
                "AJUSTE COMPRA #$id_compra: +$cantidad_nueva unidades (nuevo)",
                $id_compra
            ]);

            $computed_total += $subtotal;
            // Nota: id_detalle nuevo no lo conocemos hasta autoincrement; no lo necesitamos abajo
        } else {
            // Producto existente: actualizar cantidad y subtotal
            $id_detalle = intval($prod['id_detalle']);
            $cantidad_original = isset($prod['cantidad_original']) ? intval($prod['cantidad_original']) : 0;

            // Comprobar precio_unitario actual guardado en detalle (precio fijo)
            if (!isset($mapOriginalByDetalle[$id_detalle])) {
                throw new Exception("Detalle con id $id_detalle no encontrado en la compra");
            }
            $precio_unitario = floatval($mapOriginalByDetalle[$id_detalle]['precio_unitario']);
            $nuevo_subtotal = $cantidad_nueva * $precio_unitario;

            // Actualizar detalle
            $stmtActualizarDetalle->execute([$cantidad_nueva, $nuevo_subtotal, $id_detalle]);

            // Ajuste de stock relativo
            $diferencia = $cantidad_nueva - $cantidad_original;
            if ($diferencia !== 0) {
                $stmtStockActual->execute([$id_producto]);
                $stockAnterior = intval($stmtStockActual->fetchColumn() ?: 0);
                $stmtAjustarStock->execute([$diferencia, $id_producto]);
                $stockNuevo = $stockAnterior + $diferencia;

                $motivo = $diferencia > 0
                    ? "AJUSTE COMPRA #$id_compra: +$diferencia unidades"
                    : "AJUSTE COMPRA #$id_compra: -" . abs($diferencia) . " unidades";

                $stmtInsertHistorial->execute([
                    $id_producto,
                    abs($diferencia),
                    $stockAnterior,
                    $stockNuevo,
                    $motivo,
                    $id_compra
                ]);
            }

            $computed_total += $nuevo_subtotal;
            $idsDetallesRecibidos[] = $id_detalle;
        }
    }

    // --- Eliminar detalles que ya no vinieron en el POST (se quitaron del formulario) ---
    foreach ($mapOriginalByDetalle as $detalleId => $orig) {
        if (!in_array($detalleId, $idsDetallesRecibidos)) {
            $id_producto = intval($orig['id_producto']);
            $cantidad = intval($orig['cantidad']);

            // Ajustar stock (revertir la entrada)
            $stmtStockActual->execute([$id_producto]);
            $stockAnterior = intval($stmtStockActual->fetchColumn() ?: 0);
            $stmtAjustarStock->execute([-$cantidad, $id_producto]);
            $stockNuevo = $stockAnterior - $cantidad;

            $stmtInsertHistorial->execute([
                $id_producto,
                $cantidad,
                $stockAnterior,
                $stockNuevo,
                "AJUSTE COMPRA #$id_compra: -$cantidad unidades (eliminado)",
                $id_compra
            ]);

            // Eliminar fila detalle
            $stmtEliminarDetalle->execute([$detalleId]);
        }
    }

    // --- Validar/recalcular total final en servidor ---
    // Si la diferencia entre el total enviado y el calculado es mayor a 1 moneda, sobrescribimos;
    // esto evita que el cliente manipule el total.
    if (abs($computed_total - $total_post) > 0.5) {
        // Loguear la discrepancia
        error_log("editar_compra.php: discrepancia total compra #$id_compra - post: $total_post - calc: $computed_total. Se usará el calculado.");
    }
    $total_final = $computed_total;

    // Actualizar total en compras
    $stUpdateTotal = $conexion->prepare("UPDATE compras SET total_compra = ? WHERE id = ?");
    $stUpdateTotal->execute([$total_final, $id_compra]);

    // Commit / unlock
    if ($useTableLocks) {
        $conexion->exec("UNLOCK TABLES");
    } else {
        $conexion->commit();
    }

    $titulo = "Compra Actualizada Correctamente";
    $mensaje = "Los datos de la compra <strong>#$id_compra</strong> han sido actualizados exitosamente.<br><br>
                <strong>Cambios realizados:</strong><br>
                • Productos y cantidades actualizados ✓<br>
                • Stock ajustado según cambios ✓<br>
                • Nuevo total: <strong>₲ " . number_format($total_final, 0, ',', '.') . "</strong>";
    $tipo = "success";

} catch (Exception $e) {
    // Intentar revertir transacción o desbloquear tablas
    if (isset($conexion)) {
        try {
            if ($conexion->inTransaction()) $conexion->rollBack();
        } catch (Throwable $_) {
            // Ignorar
        }
        try {
            // Si quedaron tablas bloqueadas, liberarlas
            $conexion->exec("UNLOCK TABLES");
        } catch (Throwable $_) {
            // Ignorar
        }
    }

    error_log("editar_compra.php - ERROR: " . $e->getMessage());

    $titulo = "Error al Actualizar Compra";
    $mensaje = "No se pudo completar la actualización:<br><br>" . htmlspecialchars($e->getMessage());
    $tipo = "error";
}
?>
<!-- (el resto del HTML para mostrar mensaje lo puedes usar igual que tu versión) -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($titulo); ?></title>
    <link href="../css/bulma.min.css" rel="stylesheet">
    <link href="../css/mensajes.css" rel="stylesheet">
    <style>.main-content{display:flex;align-items:center;justify-content:center;min-height:100vh;}</style>
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
            <div class='message-content'>${mensaje}</div>
            <div class='button-group'>
                <a href='./listado_compras.php' class='action-button'>📋 Ver Listado de Compras</a>
                <a href='./frm_registrar_compra.php' class='secondary-button'>➕ Registrar Nueva Compra</a>
            </div>
        </div>
    `;
    mainContent.innerHTML = contentHTML;
});
</script>
</body>
</html>
