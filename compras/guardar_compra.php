<?php
$mensaje = "";
$tipo = "";
$titulo = "";

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        include_once "../db.php";

        if (!isset($conexion) || !$conexion) {
            throw new Exception("No se pudo conectar a la base de datos.");
        }

        // Iniciar transacción
        $conexion->beginTransaction();

        // Datos de la compra
        $id_proveedor = isset($_POST['id_proveedor']) ? $_POST['id_proveedor'] : null;
        $numero_compra = isset($_POST['numero_compra']) ? trim($_POST['numero_compra']) : null;
        $numero_compra = $numero_compra === "" ? null : $numero_compra;
        $fecha_compra = isset($_POST['fecha_compra']) ? $_POST['fecha_compra'] : null;
        $total_compra = isset($_POST['total_compra']) ? floatval($_POST['total_compra']) : 0;
        $observaciones = isset($_POST['observaciones']) ? trim($_POST['observaciones']) : null;
        $productos = isset($_POST['productos']) ? $_POST['productos'] : array();
        
        // NUEVO: Datos de forma de pago
        $forma_pago = isset($_POST['forma_pago']) ? $_POST['forma_pago'] : 'CONTADO';
        $cuotas = null;
        $monto_cuota = null;
        $fecha_vencimiento = null;
        
        // Solo asignar valores si es CREDITO y los campos tienen valor
        if ($forma_pago === 'CREDITO') {
            $cuotas = (isset($_POST['cuotas']) && $_POST['cuotas'] !== '') ? intval($_POST['cuotas']) : null;
            $monto_cuota = (isset($_POST['monto_cuota']) && $_POST['monto_cuota'] !== '') ? floatval($_POST['monto_cuota']) : null;
            $fecha_vencimiento = (isset($_POST['fecha_vencimiento']) && $_POST['fecha_vencimiento'] !== '') ? $_POST['fecha_vencimiento'] : null;
        }

        // Validaciones
        if (empty($id_proveedor)) {
            throw new Exception("Debe seleccionar un proveedor");
        }

        if (empty($productos) || count($productos) == 0) {
            throw new Exception("Debe agregar al menos un producto a la compra");
        }

        if (empty($fecha_compra)) {
            throw new Exception("Debe ingresar la fecha de compra");
        }
        
        // Validar datos de crédito si aplica
        if ($forma_pago === 'CREDITO') {
            if (empty($cuotas) || $cuotas < 1) {
                throw new Exception("Debe ingresar el número de cuotas para compra a crédito");
            }
            if (empty($monto_cuota) || $monto_cuota <= 0) {
                throw new Exception("El monto de cuota debe ser mayor a 0");
            }
            if (empty($fecha_vencimiento)) {
                throw new Exception("Debe ingresar la fecha de vencimiento de la primera cuota");
            }
        }

        // 1. Insertar la compra (cabecera)
        $sentenciaCompra = $conexion->prepare(
            "INSERT INTO compras (id_proveedor, numero_compra, fecha_compra, total_compra, observaciones, estado_compra) 
             VALUES (?, ?, ?, ?, ?, 1)"
        );
        $resultadoCompra = $sentenciaCompra->execute([
            $id_proveedor,
            $numero_compra,
            $fecha_compra,
            $total_compra,
            $observaciones
        ]);

        if (!$resultadoCompra) {
            throw new Exception("Error al registrar la compra");
        }

        // Obtener ID de la compra insertada
        $id_compra = $conexion->lastInsertId();


        // 2. NUEVO: Insertar forma de pago
        $sentenciaPago = $conexion->prepare(
            "INSERT INTO pagos_compra (id_compra, forma_pago, cuotas, monto_cuota, fecha_vencimiento) 
             VALUES (?, ?, ?, ?, ?)"
        );
        
        // Preparar los valores para el bind
        $valores_pago = [
            $id_compra,
            $forma_pago,
            $cuotas,
            $monto_cuota,
            $fecha_vencimiento
        ];
        
        $resultadoPago = $sentenciaPago->execute($valores_pago);
        
        if (!$resultadoPago) {
            $errorInfo = $sentenciaPago->errorInfo();
            throw new Exception("Error al registrar la forma de pago: " . $errorInfo[2]);
        }

        // 3. Insertar detalle de compra y actualizar stock
        $sentenciaDetalle = $conexion->prepare(
            "INSERT INTO detalle_compras (id_compra, id_producto, cantidad, precio_unitario, subtotal) 
             VALUES (?, ?, ?, ?, ?)"
        );

        $sentenciaStock = $conexion->prepare(
            "UPDATE productos SET stock_actual = stock_actual + ? WHERE id = ?"
        );

        $sentenciaHistorial = $conexion->prepare(
            "INSERT INTO historial_stock (id_producto, tipo_movimiento, cantidad, stock_anterior, stock_nuevo, motivo, id_referencia) 
             VALUES (?, 'ENTRADA', ?, ?, ?, ?, ?)"
        );

        $sentenciaStockActual = $conexion->prepare(
            "SELECT stock_actual FROM productos WHERE id = ?"
        );

        foreach ($productos as $producto) {
            if (!isset($producto['id']) || !isset($producto['cantidad']) || !isset($producto['precio'])) {
                throw new Exception("Formato de producto inválido.");
            }

            $id_producto = $producto['id'];
            $cantidad = intval($producto['cantidad']);
            $precio = floatval($producto['precio']);
            $subtotal = $cantidad * $precio;

            // Obtener stock actual antes de actualizar
            $sentenciaStockActual->execute([$id_producto]);
            $stockAnterior = $sentenciaStockActual->fetchColumn();
            $stockAnterior = $stockAnterior === false ? 0 : intval($stockAnterior);

            // Insertar detalle de compra
            $resultadoDetalle = $sentenciaDetalle->execute([
                $id_compra,
                $id_producto,
                $cantidad,
                $precio,
                $subtotal
            ]);

            if (!$resultadoDetalle) {
                throw new Exception("Error al registrar el detalle del producto ID: $id_producto");
            }

            // Actualizar stock del producto (SUMAR)
            $resultadoStock = $sentenciaStock->execute([$cantidad, $id_producto]);

            if (!$resultadoStock) {
                throw new Exception("Error al actualizar el stock del producto ID: $id_producto");
            }

            // Calcular nuevo stock
            $stockNuevo = $stockAnterior + $cantidad;

            // Registrar en historial de stock
            $sentenciaHistorial->execute([
                $id_producto,
                $cantidad,
                $stockAnterior,
                $stockNuevo,
                "COMPRA #$id_compra - Proveedor ID: $id_proveedor",
                $id_compra
            ]);
        }

        // 4. Registrar movimiento en caja (EGRESO)
        $sentenciaCaja = $conexion->prepare(
            "INSERT INTO caja (tipo_movimiento, categoria, id_referencia, concepto, monto, fecha_movimiento) 
             VALUES ('EGRESO', 'COMPRA', ?, ?, ?, ?)"
        );

        // Obtener nombre del proveedor para el concepto
        $sentenciaNombreProveedor = $conexion->prepare("SELECT nombre_proveedor FROM proveedores WHERE id = ?");
        $sentenciaNombreProveedor->execute([$id_proveedor]);
        $nombreProveedor = $sentenciaNombreProveedor->fetchColumn();
        $nombreProveedor = $nombreProveedor ?: "Proveedor #$id_proveedor";

        $conceptoCaja = "COMPRA #$id_compra - $nombreProveedor" . ($numero_compra ? " (Factura: $numero_compra)" : "");

        $resultadoCaja = $sentenciaCaja->execute([
            $id_compra,
            $conceptoCaja,
            $total_compra,
            $fecha_compra
        ]);

        if (!$resultadoCaja) {
            throw new Exception("Error al registrar el movimiento de caja");
        }

        // Confirmar transacción
        $conexion->commit();

        // Mensaje de éxito con detalles de pago
        $detallePago = "";
        if ($forma_pago === 'CONTADO') {
            $detallePago = "• Forma de pago: <strong>Contado</strong><br>";
        } else {
            $detallePago = "• Forma de pago: <strong>Crédito</strong><br>" .
                          "• Cuotas: <strong>$cuotas</strong><br>" .
                          "• Monto por cuota: <strong>₲ " . number_format($monto_cuota, 0, ',', '.') . "</strong><br>" .
                          "• Vencimiento 1ra cuota: <strong>" . date('d/m/Y', strtotime($fecha_vencimiento)) . "</strong><br>";
        }

        $titulo = "✅ Compra Registrada Exitosamente";
        $mensaje = "La compra ha sido registrada correctamente.<br><br>
                    <strong>Detalles:</strong><br>
                    • Compra ID: <strong>#$id_compra</strong><br>
                    • Proveedor: <strong>$nombreProveedor</strong><br>
                    • Total: <strong>₲ " . number_format($total_compra, 0, ',', '.') . "</strong><br>
                    $detallePago
                    • Productos: <strong>" . count($productos) . "</strong><br>
                    • Stock actualizado automáticamente ✓<br>
                    • Movimiento de caja registrado ✓";
        $tipo = "success";

    } else {
        throw new Exception("Método de solicitud no válido");
    }
} catch (Exception $e) {
    // Revertir transacción en caso de error
    if (isset($conexion) && $conexion && $conexion->inTransaction()) {
        $conexion->rollBack();
    }

    error_log("guardar_compra.php - ERROR: " . $e->getMessage());

    $titulo = "❌ Error al Registrar Compra";
    $mensaje = "No se pudo completar el registro de la compra:<br><br>" . htmlspecialchars($e->getMessage());
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
        .main-content {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
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
        }
        .message-content {
            margin-bottom: 18px;
            text-align: left;
        }
        .status-icon {
            font-size: 2.4rem;
            display: inline-block;
            margin-bottom: 6px;
        }
        .button-group { display:flex; gap:12px; justify-content:center; margin-top:12px; }
        .action-button, .secondary-button { padding: 10px 18px; border-radius: 8px; text-decoration: none; color: #2c3e50; background: linear-gradient(45deg,#f39c12,#f1c40f); font-weight: bold; }
        .secondary-button { background: rgba(236,240,241,0.1); color: white; border: 2px solid rgba(241,196,15,0.2); }
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

            var icono = tipo === 'success' ? '📦✅' : '❌';
            var claseIcono = tipo === 'success' ? 'success-icon' : 'error-icon';

            var contentHTML = ""
                + "<div class='message-container'>"
                + "  <span class='status-icon " + claseIcono + "'>" + icono + "</span>"
                + "  <h1 class='message-title'>" + titulo + "</h1>"
                + "  <div class='message-content'>" + mensaje + "</div>"
                + "  <div class='button-group'>"
                + "    <a href='./listado_compras.php' class='action-button'>📋 Ver Listado de Compras</a>"
                + "    <a href='./frm_registrar_compra.php' class='secondary-button'>➕ Registrar Nueva Compra</a>"
                + "  </div>"
                + "</div>";

            mainContent.innerHTML = contentHTML;
        });
    </script>
</body>
</html>