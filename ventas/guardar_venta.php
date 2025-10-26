<?php
$mensaje = "";
$tipo = "";
$titulo = "";
$id_venta = null;
$tipo_comprobante = "";
$numero_comprobante_asignado = "";

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        include_once "../db.php";

        if (!isset($conexion) || !$conexion) {
            throw new Exception("No se pudo conectar a la base de datos.");
        }

        // Iniciar transacción
        $conexion->beginTransaction();

        // Datos de la venta
        $id_cliente = isset($_POST['id_cliente']) && $_POST['id_cliente'] !== '' ? $_POST['id_cliente'] : null;
        $fecha_venta = isset($_POST['fecha_venta']) ? $_POST['fecha_venta'] : null;
        $subtotal = isset($_POST['subtotal']) ? floatval($_POST['subtotal']) : 0;
        $total_venta = isset($_POST['total_venta']) ? floatval($_POST['total_venta']) : 0;
        $observaciones = isset($_POST['observaciones']) ? trim($_POST['observaciones']) : null;
        $tipo_comprobante = isset($_POST['tipo_comprobante']) && $_POST['tipo_comprobante'] !== '' ? $_POST['tipo_comprobante'] : null;
        $condicion_venta = isset($_POST['condicion_venta']) ? $_POST['condicion_venta'] : 'CONTADO';
        $forma_pago = isset($_POST['forma_pago']) ? $_POST['forma_pago'] : 'CONTADO';
        $items = isset($_POST['items']) ? $_POST['items'] : array();

        // Validaciones
        if (empty($items) || count($items) == 0) {
            throw new Exception("Debe agregar al menos un item a la venta");
        }

        if (empty($fecha_venta)) {
            throw new Exception("Debe ingresar la fecha de venta");
        }

        if ($total_venta <= 0) {
            throw new Exception("El total de la venta debe ser mayor a 0");
        }

        // 1. Insertar la venta (SIN número todavía)
        $sentenciaVenta = $conexion->prepare(
            "INSERT INTO ventas (id_cliente, numero_venta, fecha_venta, subtotal, descuento, total_venta, observaciones, tipo_comprobante, condicion_venta, forma_pago, estado_venta) 
             VALUES (?, NULL, ?, ?, 0.00, ?, ?, ?, ?, ?, 1)"
        );
        $resultadoVenta = $sentenciaVenta->execute([
            $id_cliente,
            $fecha_venta,
            $subtotal,
            $total_venta,
            $observaciones,
            $tipo_comprobante,
            $condicion_venta,
            $forma_pago
        ]);

        if (!$resultadoVenta) {
            throw new Exception("Error al registrar la venta");
        }

        // Obtener ID de la venta insertada
        $id_venta = $conexion->lastInsertId();

        // 2. GENERAR NUMERACIÓN AUTOMÁTICA según tipo de comprobante
        $numero_comprobante_asignado = null;
        
        if ($tipo_comprobante === 'FACTURA') {
            // FACTURA: 001-001-0000826 (continúa desde 826)
            $FACTURA_BASE = 825; // La última factura real fue 825
            $numero_factura = $FACTURA_BASE + $id_venta;
            $numero_comprobante_asignado = '001-001-' . str_pad($numero_factura, 7, '0', STR_PAD_LEFT);
        } elseif ($tipo_comprobante === 'TICKET') {
            // TICKET: 0000001 (simple contador basado en ID)
            $numero_comprobante_asignado = str_pad($id_venta, 7, '0', STR_PAD_LEFT);
        }

        // Actualizar venta con el número generado
        if ($numero_comprobante_asignado) {
            $sentenciaActualizarNumero = $conexion->prepare("UPDATE ventas SET numero_venta = ? WHERE id = ?");
            $sentenciaActualizarNumero->execute([$numero_comprobante_asignado, $id_venta]);
        }

        // 3. Insertar detalle de venta y actualizar stock (solo productos)
        $sentenciaDetalle = $conexion->prepare(
            "INSERT INTO detalle_ventas (id_venta, tipo_item, id_item, descripcion, cantidad, precio_unitario, subtotal) 
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );

        $sentenciaStock = $conexion->prepare(
            "UPDATE productos SET stock_actual = stock_actual - ? WHERE id = ?"
        );

        $sentenciaHistorial = $conexion->prepare(
            "INSERT INTO historial_stock (id_producto, tipo_movimiento, cantidad, stock_anterior, stock_nuevo, motivo, id_referencia) 
             VALUES (?, 'SALIDA', ?, ?, ?, ?, ?)"
        );

        $sentenciaStockActual = $conexion->prepare(
            "SELECT stock_actual FROM productos WHERE id = ?"
        );

        $cantidadProductos = 0;
        $cantidadServicios = 0;

        foreach ($items as $item) {
            if (!isset($item['tipo']) || !isset($item['id']) || !isset($item['descripcion']) || !isset($item['cantidad']) || !isset($item['precio'])) {
                throw new Exception("Formato de item inválido.");
            }

            $tipo_item = $item['tipo'];
            $id_item = $item['id'];
            $descripcion = $item['descripcion'];
            $cantidad = intval($item['cantidad']);
            $precio = floatval($item['precio']);
            $subtotal_item = $cantidad * $precio;

            // Validar stock si es producto
            if ($tipo_item === 'PRODUCTO') {
                $sentenciaStockActual->execute([$id_item]);
                $stockAnterior = $sentenciaStockActual->fetchColumn();
                $stockAnterior = $stockAnterior === false ? 0 : intval($stockAnterior);

                if ($cantidad > $stockAnterior) {
                    throw new Exception("Stock insuficiente para el producto: $descripcion (Disponible: $stockAnterior)");
                }

                // Actualizar stock del producto (RESTAR)
                $resultadoStock = $sentenciaStock->execute([$cantidad, $id_item]);

                if (!$resultadoStock) {
                    throw new Exception("Error al actualizar el stock del producto ID: $id_item");
                }

                // Calcular nuevo stock
                $stockNuevo = $stockAnterior - $cantidad;

                // Registrar en historial de stock
                $sentenciaHistorial->execute([
                    $id_item,
                    $cantidad,
                    $stockAnterior,
                    $stockNuevo,
                    "VENTA #$id_venta",
                    $id_venta
                ]);

                $cantidadProductos++;
            } else {
                $cantidadServicios++;
            }

            // Insertar detalle de venta
            $resultadoDetalle = $sentenciaDetalle->execute([
                $id_venta,
                $tipo_item,
                $id_item,
                $descripcion,
                $cantidad,
                $precio,
                $subtotal_item
            ]);

            if (!$resultadoDetalle) {
                throw new Exception("Error al registrar el detalle del item: $descripcion");
            }
        }

        // 4. Registrar movimiento en caja (INGRESO)
        $sentenciaCaja = $conexion->prepare(
            "INSERT INTO caja (tipo_movimiento, categoria, id_referencia, concepto, monto, fecha_movimiento) 
             VALUES ('INGRESO', 'VENTA', ?, ?, ?, ?)"
        );

        // Obtener nombre del cliente para el concepto (si existe)
        $nombreCliente = "Cliente Genérico";
        if ($id_cliente) {
            $sentenciaNombreCliente = $conexion->prepare("SELECT CONCAT(nombre_cliente, ' ', apellido_cliente) as nombre_completo FROM clientes WHERE id = ?");
            $sentenciaNombreCliente->execute([$id_cliente]);
            $nombreClienteDB = $sentenciaNombreCliente->fetchColumn();
            if ($nombreClienteDB) {
                $nombreCliente = $nombreClienteDB;
            }
        }

        $conceptoCaja = "VENTA #$id_venta - $nombreCliente" . ($numero_comprobante_asignado ? " ($tipo_comprobante: $numero_comprobante_asignado)" : "");

        $resultadoCaja = $sentenciaCaja->execute([
            $id_venta,
            $conceptoCaja,
            $total_venta,
            $fecha_venta
        ]);

        if (!$resultadoCaja) {
            throw new Exception("Error al registrar el movimiento de caja");
        }

        // Confirmar transacción
        $conexion->commit();

        $iva_informativo = round($total_venta / 11, 2);
        
        $infoNumero = $numero_comprobante_asignado ? 
            "<br>• Número asignado: <strong style='color: #27ae60;'>$numero_comprobante_asignado</strong>" : 
            "";

        $titulo = "✅ Venta Registrada Exitosamente";
        $mensaje = "La venta ha sido registrada correctamente.<br><br>
                    <strong>Detalles:</strong><br>
                    • Venta ID: <strong>#$id_venta</strong>$infoNumero<br>
                    • Cliente: <strong>$nombreCliente</strong><br>
                    • Subtotal: <strong>₲ " . number_format($subtotal, 0, ',', '.') . "</strong><br>
                    • IVA (10% - Informativo): <strong>₲ " . number_format($iva_informativo, 0, ',', '.') . "</strong><br>
                    • <strong style='font-size: 1.1rem;'>Total: ₲ " . number_format($total_venta, 0, ',', '.') . "</strong><br>
                    • Condición: <strong>$condicion_venta</strong><br>
                    • Productos: <strong>$cantidadProductos</strong><br>
                    • Servicios: <strong>$cantidadServicios</strong><br><br>
                    <strong>Acciones realizadas:</strong><br>
                    • Stock actualizado automáticamente ✓<br>
                    • Movimiento de caja registrado ✓<br>
                    • Historial de stock actualizado ✓";
        $tipo = "success";

    } else {
        throw new Exception("Método de solicitud no válido");
    }
} catch (Exception $e) {
    // Revertir transacción en caso de error
    if (isset($conexion) && $conexion && $conexion->inTransaction()) {
        $conexion->rollBack();
    }

    error_log("guardar_venta.php - ERROR: " . $e->getMessage());

    $titulo = "❌ Error al Registrar Venta";
    $mensaje = "No se pudo completar el registro de la venta:<br><br>" . htmlspecialchars($e->getMessage());
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
        body {
            background: #2c3e50 !important;
        }
        
        .main-content {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
            background: #2c3e50 !important;
        }
        
        .message-container {
            max-width: 820px;
            width: 100%;
            background: rgba(44, 62, 80, 0.95);
            padding: 28px;
            border-radius: 10px;
            color: white;
            text-align: center;
        }
        
        .message-title {
            margin-top: 10px;
            margin-bottom: 12px;
            font-size: 1.6rem;
            color: #f1c40f;
        }
        
        .message-content {
            margin-bottom: 18px;
            text-align: left;
            line-height: 1.6;
        }
        
        .status-icon {
            font-size: 2.4rem;
            display: inline-block;
            margin-bottom: 6px;
        }
        
        .button-group {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin-top: 12px;
            flex-wrap: wrap;
        }
        
        .action-button {
            padding: 10px 18px;
            border-radius: 8px;
            text-decoration: none;
            color: #2c3e50;
            background: linear-gradient(45deg, #f39c12, #f1c40f);
            font-weight: bold;
            border: none;
            cursor: pointer;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            display: inline-block;
        }
        
        .action-button:hover {
            background: linear-gradient(45deg, #e67e22, #f39c12);
            transform: translateY(-2px);
        }
        
        .secondary-button {
            padding: 10px 18px;
            border-radius: 8px;
            text-decoration: none;
            background: rgba(236, 240, 241, 0.1);
            color: white;
            border: 2px solid rgba(241, 196, 15, 0.2);
            font-weight: bold;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            display: inline-block;
        }
        
        .secondary-button:hover {
            background: rgba(241, 196, 15, 0.15);
            border-color: #f1c40f;
            color: white;
        }
        
        .print-button {
            padding: 10px 18px;
            border-radius: 8px;
            text-decoration: none;
            color: white;
            background: linear-gradient(45deg, #3498db, #2980b9);
            font-weight: bold;
            border: none;
            cursor: pointer;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            display: inline-block;
        }
        
        .print-button:hover {
            background: linear-gradient(45deg, #2980b9, #3498db);
            transform: translateY(-2px);
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
            var idVenta = <?php echo json_encode($id_venta); ?>;
            var tipoComprobante = <?php echo json_encode($tipo_comprobante); ?>;

            var icono = tipo === 'success' ? '💰✅' : '❌';
            var claseIcono = tipo === 'success' ? 'success-icon' : 'error-icon';

            var botonesHTML = "";
            
            if (tipo === 'success' && idVenta) {
                // Botón de imprimir (solo si hay tipo de comprobante seleccionado)
                if (tipoComprobante) {
                    var labelImpresion = tipoComprobante === 'FACTURA' ? '🖨️ Imprimir Factura' : '🖨️ Imprimir Ticket';
                    botonesHTML += "<button class='print-button' onclick=\"imprimirComprobante(" + idVenta + ", '" + tipoComprobante + "')\">" + labelImpresion + "</button>";
                }
                
                botonesHTML += "<a href='./listado_ventas.php' class='action-button'>📋 Ver Listado de Ventas</a>";
                botonesHTML += "<a href='./frm_registrar_venta.php' class='secondary-button'>➕ Registrar Nueva Venta</a>";
            } else if (tipo === 'error') {
                botonesHTML += "<a href='./frm_registrar_venta.php' class='secondary-button'>⬅️ Volver al Formulario</a>";
                botonesHTML += "<a href='./listado_ventas.php' class='action-button'>📋 Ver Historial</a>";
            }

            var contentHTML = ""
                + "<div class='message-container'>"
                + "  <span class='status-icon " + claseIcono + "'>" + icono + "</span>"
                + "  <h1 class='message-title'>" + titulo + "</h1>"
                + "  <div class='message-content'>" + mensaje + "</div>"
                + "  <div class='button-group'>"
                + botonesHTML
                + "  </div>"
                + "</div>";

            mainContent.innerHTML = contentHTML;
        });

        function imprimirComprobante(idVenta, tipoComprobante) {
            if (!idVenta || !tipoComprobante) {
                alert('Datos inválidos para imprimir');
                return;
            }
            
            var url = './imprimir_comprobante.php?id_venta=' + idVenta + '&tipo=' + tipoComprobante;
            window.open(url, '_blank', 'width=900,height=700');
        }
    </script>
</body>
</html>