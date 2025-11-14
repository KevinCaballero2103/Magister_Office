<?php
// ============================================
// SISTEMA DE ANULACIÓN DE VENTAS
// Con auditoría completa y control de permisos
// ============================================


$mensaje = "";
$tipo = "";
$titulo = "";
$detalles_anulacion = array();


include_once "../auth.php"; 

$usuarioActual = getUsuarioActual();
$usuario_anula = $usuarioActual['nombre'];
try {
    // Validar método y parámetros
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Método no permitido. Use el formulario de confirmación.");
    }

    if (!isset($_POST["id"]) || !isset($_POST["motivo"])) {
        throw new Exception("Faltan parámetros requeridos (ID y motivo).");
    }

    $id_venta = intval($_POST["id"]);
    $motivo_anulacion = trim($_POST["motivo"]);

    if (empty($motivo_anulacion)) {
        throw new Exception("El motivo de anulación es obligatorio.");
    }

    include_once "../db.php";

    // ===== PASO 1: OBTENER CONFIGURACIÓN DEL SISTEMA =====
    $sentenciaConfig = $conexion->prepare("SELECT clave, valor FROM configuracion_sistema WHERE clave IN ('dias_limite_anulacion', 'permitir_anular_factura', 'generar_nota_credito_auto')");
    $sentenciaConfig->execute();
    $config = array();
    while ($row = $sentenciaConfig->fetch(PDO::FETCH_OBJ)) {
        $config[$row->clave] = $row->valor;
    }

    $dias_limite = intval($config['dias_limite_anulacion'] ?? 30);
    $permitir_factura = intval($config['permitir_anular_factura'] ?? 1);
    $nota_auto = intval($config['generar_nota_credito_auto'] ?? 0);

    // ===== PASO 2: VERIFICAR LA VENTA =====
    $sentenciaVenta = $conexion->prepare("
        SELECT v.*, 
            CONCAT(COALESCE(c.nombre_cliente, ''), ' ', COALESCE(c.apellido_cliente, '')) as nombre_cliente,
            c.ci_ruc_cliente,
            DATEDIFF(NOW(), v.fecha_venta) as dias_transcurridos
        FROM ventas v
        LEFT JOIN clientes c ON v.id_cliente = c.id
        WHERE v.id = ?
    ");
    $sentenciaVenta->execute([$id_venta]);
    $venta = $sentenciaVenta->fetch(PDO::FETCH_OBJ);

    if (!$venta) {
        throw new Exception("La venta #$id_venta no existe en el sistema.");
    }

    // Validar si ya está anulada
    if ($venta->estado_venta == 0) {
        throw new Exception("La venta #$id_venta ya se encuentra ANULADA desde " . date('d/m/Y H:i', strtotime($venta->fecha_anulacion)));
    }

    // NUEVA VALIDACIÓN: Verificar si es venta a crédito con cuotas pagadas
    if ($venta->condicion_venta === 'CREDITO') {
        $sentenciaVerificarCuotas = $conexion->prepare("
            SELECT COUNT(*) as cuotas_pagadas 
            FROM cuotas_venta 
            WHERE id_venta = ? AND estado = 'PAGADA'
        ");
        $sentenciaVerificarCuotas->execute([$id_venta]);
        $cuotas_pagadas = intval($sentenciaVerificarCuotas->fetchColumn());
        
        if ($cuotas_pagadas > 0) {
            throw new Exception("No se puede anular esta venta a CRÉDITO porque ya tiene $cuotas_pagadas cuota(s) pagada(s). Las ventas a crédito solo pueden anularse si ninguna cuota ha sido cobrada.");
        }
    }

    // Validar plazo de anulación
    if ($venta->dias_transcurridos > $dias_limite) {
        throw new Exception("La venta excede el plazo permitido para anulación ($dias_limite días). Han transcurrido {$venta->dias_transcurridos} días.");
    }

    // Validar si es factura y está permitido anular
    if ($venta->tipo_comprobante === 'FACTURA' && !$permitir_factura) {
        throw new Exception("Las facturas no pueden ser anuladas según configuración del sistema. Debe generar una nota de crédito.");
    }

    // Validar si ya tiene nota de crédito
    $sentenciaNotaPrevia = $conexion->prepare("SELECT COUNT(*) FROM notas_credito WHERE id_venta_original = ? AND estado != 'ANULADA'");
    $sentenciaNotaPrevia->execute([$id_venta]);
    if ($sentenciaNotaPrevia->fetchColumn() > 0) {
        throw new Exception("Esta venta ya tiene una nota de crédito asociada. No se puede anular nuevamente.");
    }

    // ===== PASO 3: INICIAR TRANSACCIÓN =====
    $conexion->beginTransaction();

    $nombreCliente = trim($venta->nombre_cliente) ?: "Cliente Genérico";
    $totalVenta = floatval($venta->total_venta);
    $tipoDocumento = $venta->tipo_comprobante ?: 'VENTA';
    $numeroDocumento = $venta->numero_venta ?: "N/A";

    // ===== PASO 4: REVERTIR STOCK (SOLO PRODUCTOS) =====
    $sentenciaDetalles = $conexion->prepare("
        SELECT id_item, tipo_item, cantidad, descripcion 
        FROM detalle_ventas 
        WHERE id_venta = ?
    ");
    $sentenciaDetalles->execute([$id_venta]);
    $detalles = $sentenciaDetalles->fetchAll(PDO::FETCH_OBJ);

    $sentenciaRevertirStock = $conexion->prepare("UPDATE productos SET stock_actual = stock_actual + ? WHERE id = ?");
    $sentenciaHistorial = $conexion->prepare("
        INSERT INTO historial_stock (id_producto, tipo_movimiento, cantidad, stock_anterior, stock_nuevo, motivo, id_referencia) 
        VALUES (?, 'ENTRADA', ?, ?, ?, ?, ?)
    ");
    $sentenciaStockActual = $conexion->prepare("SELECT stock_actual FROM productos WHERE id = ?");

    // NUEVA: Verificar que el producto existe
    $sentenciaVerificarProducto = $conexion->prepare("SELECT id FROM productos WHERE id = ?");

    $productosRevertidos = 0;
    $serviciosContados = 0;
    $productosNoExisten = []; // Para registrar productos que ya no existen

    foreach ($detalles as $detalle) {
        if ($detalle->tipo_item === 'PRODUCTO') {
            // VERIFICAR QUE EL PRODUCTO EXISTE
            $sentenciaVerificarProducto->execute([$detalle->id_item]);
            $productoExiste = $sentenciaVerificarProducto->fetch(PDO::FETCH_OBJ);
            
            if (!$productoExiste) {
                // El producto fue eliminado, no podemos revertir el stock
                $productosNoExisten[] = $detalle->descripcion . " (ID: {$detalle->id_item})";
                continue; // Saltar al siguiente item
            }
            
            // Obtener stock actual
            $sentenciaStockActual->execute([$detalle->id_item]);
            $stockAnterior = intval($sentenciaStockActual->fetchColumn());

            // Revertir stock
            $sentenciaRevertirStock->execute([$detalle->cantidad, $detalle->id_item]);

            // Registrar historial
            $stockNuevo = $stockAnterior + $detalle->cantidad;
            $motivoHistorial = "ANULACIÓN {$tipoDocumento} #{$id_venta} - Usuario: {$usuario_anula} - Motivo: " . substr($motivo_anulacion, 0, 100);
            
            $sentenciaHistorial->execute([
                $detalle->id_item,
                $detalle->cantidad,
                $stockAnterior,
                $stockNuevo,
                $motivoHistorial,
                $id_venta
            ]);

            $productosRevertidos++;
        } else {
            $serviciosContados++;
        }
    }

    // Si hay productos que no existen, agregar advertencia
    if (count($productosNoExisten) > 0) {
        $detalles_anulacion['productos_no_existen'] = $productosNoExisten;
    }

    // ===== PASO 5: MOVIMIENTO INVERSO EN CAJA =====
    // NO eliminar el movimiento original, crear uno inverso
    $sentenciaMovimientoOriginal = $conexion->prepare("
        SELECT id FROM caja 
        WHERE tipo_movimiento = 'INGRESO' 
        AND categoria = 'VENTA' 
        AND id_referencia = ?
        LIMIT 1
    ");
    $sentenciaMovimientoOriginal->execute([$id_venta]);
    $movimientoOriginal = $sentenciaMovimientoOriginal->fetch(PDO::FETCH_OBJ);

    if ($movimientoOriginal) {
        // Crear movimiento inverso (EGRESO)
        $sentenciaMovimientoInverso = $conexion->prepare("
            INSERT INTO caja (tipo_movimiento, categoria, id_referencia, concepto, monto, fecha_movimiento, observaciones, usuario_registro, movimiento_relacionado) 
            VALUES ('EGRESO', 'OTRO', ?, ?, ?, NOW(), ?, ?, ?)
        ");

        $conceptoInverso = "ANULACIÓN {$tipoDocumento} #{$id_venta} - {$nombreCliente} ({$numeroDocumento})";
        $observacionInverso = "Anulado por: {$usuario_anula}\nMotivo: {$motivo_anulacion}";

        $sentenciaMovimientoInverso->execute([
            $id_venta,
            $conceptoInverso,
            $totalVenta,
            $observacionInverso,
            $usuario_anula,
            $movimientoOriginal->id
        ]);

        $detalles_anulacion['movimiento_inverso_id'] = $conexion->lastInsertId();
    }

    // ===== PASO 6: ANULAR LA VENTA =====
    $sentenciaAnular = $conexion->prepare("
        UPDATE ventas 
        SET estado_venta = 0,
            fecha_anulacion = NOW(),
            motivo_anulacion = ?,
            usuario_anula = ?
        WHERE id = ?
    ");
    $resultadoAnular = $sentenciaAnular->execute([$motivo_anulacion, $usuario_anula, $id_venta]);

    if (!$resultadoAnular) {
        throw new Exception("Error al actualizar el estado de la venta.");
    }

    // ===== PASO 6.5: ELIMINAR CUOTAS SI ES VENTA A CRÉDITO =====
    $cuotas_eliminadas = 0;
    if ($venta->condicion_venta === 'CREDITO') {
        $sentenciaEliminarCuotas = $conexion->prepare("DELETE FROM cuotas_venta WHERE id_venta = ?");
        $resultadoEliminarCuotas = $sentenciaEliminarCuotas->execute([$id_venta]);
        
        if ($resultadoEliminarCuotas) {
            $cuotas_eliminadas = $sentenciaEliminarCuotas->rowCount();
        }
    }

    // ===== PASO 7: REGISTRAR EN HISTORIAL DE ANULACIONES =====
    $detallesJSON = json_encode([
        'productos_revertidos' => $productosRevertidos,
        'servicios' => $serviciosContados,
        'items_totales' => count($detalles),
        'cliente' => $nombreCliente,
        'movimiento_caja_id' => $detalles_anulacion['movimiento_inverso_id'] ?? null
    ]);

    $sentenciaHistorialAnul = $conexion->prepare("
        INSERT INTO historial_anulaciones (id_venta, tipo_documento, numero_documento, monto_anulado, motivo, usuario_anula, fecha_anulacion, detalles_json)
        VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)
    ");
    $sentenciaHistorialAnul->execute([
        $id_venta,
        $tipoDocumento,
        $numeroDocumento,
        $totalVenta,
        $motivo_anulacion,
        $usuario_anula,
        $detallesJSON
    ]);

    // ===== PASO 8: GENERAR NOTA DE CRÉDITO (si aplica) =====
    $nota_credito_generada = false;
    if ($venta->tipo_comprobante === 'FACTURA' && $nota_auto) {
        // Obtener último número de nota de crédito
        $sentenciaUltimaNC = $conexion->prepare("SELECT MAX(CAST(SUBSTRING(numero_nota, -7) AS UNSIGNED)) as ultimo FROM notas_credito WHERE numero_nota LIKE '001-001-%'");
        $sentenciaUltimaNC->execute();
        $ultimaNC = $sentenciaUltimaNC->fetchColumn() ?: 0;
        $numeroNC = '001-001-' . str_pad($ultimaNC + 1, 7, '0', STR_PAD_LEFT);

        $sentenciaNC = $conexion->prepare("
            INSERT INTO notas_credito (id_venta_original, numero_nota, serie, fecha_emision, monto_total, motivo, estado, usuario_genera, observaciones)
            VALUES (?, ?, '001-001', NOW(), ?, ?, 'EMITIDA', ?, ?)
        ");
        $sentenciaNC->execute([
            $id_venta,
            $numeroNC,
            $totalVenta,
            $motivo_anulacion,
            $usuario_anula,
            "Nota de crédito generada automáticamente por anulación"
        ]);

        $nota_credito_generada = true;
        $detalles_anulacion['numero_nota_credito'] = $numeroNC;
    }

    // ===== CONFIRMAR TRANSACCIÓN =====
    $conexion->commit();

    // ===== GENERAR MENSAJE DE ÉXITO =====
    $titulo = "✅ Venta Anulada Exitosamente";
    
    $mensaje = "<strong>ANULACIÓN COMPLETADA</strong><br><br>";
    $mensaje .= "<strong>Información de la Venta:</strong><br>";
    $mensaje .= "• Venta ID: <strong>#$id_venta</strong><br>";
    $mensaje .= "• Documento: <strong>$tipoDocumento</strong> " . ($numeroDocumento != 'N/A' ? "($numeroDocumento)" : "") . "<br>";
    $mensaje .= "• Cliente: <strong>$nombreCliente</strong><br>";
    $mensaje .= "• Monto Total: <strong>₲ " . number_format($totalVenta, 0, ',', '.') . "</strong><br>";
    $mensaje .= "• Anulado por: <strong>$usuario_anula</strong><br>";
    $mensaje .= "• Motivo: <em>$motivo_anulacion</em><br><br>";
    
    $mensaje .= "<strong>Acciones Realizadas:</strong><br>";
    $mensaje .= "✅ Venta marcada como ANULADA<br>";
    $mensaje .= "✅ Stock revertido ($productosRevertidos producto(s))<br>";
    $mensaje .= "✅ Movimiento inverso en caja registrado<br>";
    $mensaje .= "✅ Historial de anulación guardado<br>";

    if ($venta->condicion_venta === 'CREDITO' && $cuotas_eliminadas > 0) {
        $mensaje .= "✅ <strong>$cuotas_eliminadas</strong> cuota(s) eliminada(s)<br>";
    }

    if ($nota_credito_generada) {
        $mensaje .= "✅ Nota de crédito generada: <strong>" . $detalles_anulacion['numero_nota_credito'] . "</strong><br>";
    } else if ($venta->tipo_comprobante === 'FACTURA') {
        $mensaje .= "⚠️ Nota de crédito pendiente (generación manual)<br>";
    }
    
    $mensaje .= "<br><strong>Registro de Auditoría:</strong><br>";
    $mensaje .= "• Fecha/Hora: <strong>" . date('d/m/Y H:i:s') . "</strong><br>";
    $mensaje .= "• Usuario: <strong>$usuario_anula</strong><br>";
    $mensaje .= "• Productos afectados: <strong>$productosRevertidos</strong><br>";
    $mensaje .= "• Servicios: <strong>$serviciosContados</strong><br>";

    $tipo = "success";

} catch (Exception $e) {
    // Revertir transacción
    if (isset($conexion) && $conexion->inTransaction()) {
        $conexion->rollBack();
    }

    error_log("anular_venta.php - ERROR: " . $e->getMessage());

    $titulo = "❌ Error al Anular Venta";
    $mensaje = "No se pudo completar la anulación:<br><br><strong>" . htmlspecialchars($e->getMessage()) . "</strong>";
    $tipo = "error";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($titulo); ?></title>
    <link href="../css/bulma.min.css" rel="stylesheet">
    <link href="../css/mensajes.css" rel="stylesheet">
    <style>
        body { background: #2c3e50 !important; }
        .main-content {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
            background: #2c3e50 !important;
        }
        .message-container {
            max-width: 850px;
            width: 100%;
            background: rgba(44, 62, 80, 0.95);
            padding: 35px;
            border-radius: 12px;
            color: white;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0,0,0,0.4);
        }
        .message-title {
            margin-top: 10px;
            margin-bottom: 15px;
            font-size: 1.8rem;
            color: #f1c40f;
        }
        .message-content {
            margin-bottom: 20px;
            text-align: left;
            line-height: 1.7;
            font-size: 0.95rem;
        }
        .status-icon {
            font-size: 3rem;
            display: inline-block;
            margin-bottom: 10px;
        }
        .button-group {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        .action-button, .secondary-button {
            padding: 12px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            display: inline-block;
            border: none;
            cursor: pointer;
        }
        .action-button {
            color: #2c3e50;
            background: linear-gradient(45deg, #f39c12, #f1c40f);
        }
        .action-button:hover {
            background: linear-gradient(45deg, #e67e22, #f39c12);
            transform: translateY(-2px);
        }
        .secondary-button {
            background: rgba(236, 240, 241, 0.1);
            color: white;
            border: 2px solid rgba(241, 196, 15, 0.2);
        }
        .secondary-button:hover {
            background: rgba(241, 196, 15, 0.15);
            border-color: #f1c40f;
            color: white;
        }
    </style>
</head>
<body>
    <?php include '../menu.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var mainContent = document.querySelector('.main-content');
            if (!mainContent) {
                mainContent = document.createElement('div');
                mainContent.className = 'main-content';
                document.body.appendChild(mainContent);
            }

            var tipo = <?php echo json_encode($tipo); ?>;
            var titulo = <?php echo json_encode($titulo); ?>;
            var mensaje = <?php echo json_encode($mensaje); ?>;

            var icono = tipo === 'success' ? '✅' : '❌';
            var claseIcono = tipo === 'success' ? 'success-icon' : 'error-icon';

            var botonesHTML = tipo === 'success' 
                ? "<a href='./listado_ventas.php' class='action-button'>📋 Ver Listado de Ventas</a>" +
                  "<a href='./frm_registrar_venta.php' class='secondary-button'>➕ Nueva Venta</a>" +
                  "<a href='../caja/balance.php' class='secondary-button'>💰 Ver Caja</a>"
                : "<a href='./listado_ventas.php' class='secondary-button'>⬅️ Volver al Listado</a>" +
                  "<a href='./frm_registrar_venta.php' class='action-button'>➕ Nueva Venta</a>";

            var contentHTML = 
                "<div class='message-container'>" +
                "  <span class='status-icon " + claseIcono + "'>" + icono + "</span>" +
                "  <h1 class='message-title'>" + titulo + "</h1>" +
                "  <div class='message-content'>" + mensaje + "</div>" +
                "  <div class='button-group'>" + botonesHTML + "</div>" +
                "</div>";

            mainContent.innerHTML = contentHTML;
        });
    </script>
</body>
</html>