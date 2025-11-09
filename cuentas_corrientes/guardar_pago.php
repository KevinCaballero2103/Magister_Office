<?php
include_once __DIR__ . "/../auth.php";
include_once "../db.php";

$cajaAbierta = requiereCajaAbierta();

$mensaje = "";
$tipo = "";
$titulo = "";

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $usuarioActual = getUsuarioActual();
        
        $id_cliente = isset($_POST['id_cliente']) ? intval($_POST['id_cliente']) : 0;
        $fecha_pago = isset($_POST['fecha_pago']) ? $_POST['fecha_pago'] : date('Y-m-d');
        $monto_pago = isset($_POST['monto_pago']) ? floatval($_POST['monto_pago']) : 0;
        $observaciones = isset($_POST['observaciones']) ? trim($_POST['observaciones']) : '';
        $saldo_anterior = isset($_POST['saldo_actual']) ? floatval($_POST['saldo_actual']) : 0;
        
        // Validaciones
        if ($id_cliente <= 0) {
            throw new Exception("ID de cliente inválido");
        }
        
        if ($monto_pago <= 0) {
            throw new Exception("El monto del pago debe ser mayor a 0");
        }
        
        // Obtener datos del cliente
        $sentenciaCliente = $conexion->prepare("SELECT * FROM clientes WHERE id = ?");
        $sentenciaCliente->execute([$id_cliente]);
        $cliente = $sentenciaCliente->fetch(PDO::FETCH_OBJ);
        
        if (!$cliente) {
            throw new Exception("Cliente no encontrado");
        }
        
        // Calcular saldo real actual
        $sentenciaSaldoReal = $conexion->prepare("
            SELECT COALESCE(SUM(CASE WHEN tipo_movimiento = 'DEBITO' THEN monto ELSE -monto END), 0) as saldo_real
            FROM cuentas_corrientes
            WHERE id_cliente = ?
        ");
        $sentenciaSaldoReal->execute([$id_cliente]);
        $saldo_real = floatval($sentenciaSaldoReal->fetchColumn());
        
        // Iniciar transacción
        $conexion->beginTransaction();
        
        // Calcular nuevo saldo
        $saldo_nuevo = $saldo_real - $monto_pago;
        
        // DETERMINAR AUTOMÁTICAMENTE EL TIPO DE PAGO
        if ($monto_pago >= $saldo_real) {
            $tipo_pago = 'TOTAL'; // Paga todo o más
        } else {
            $tipo_pago = 'PARCIAL'; // Paga menos de lo que debe
        }
        
        // 1. Registrar pago en cuenta corriente (CREDITO)
        $descripcion = "Pago ";
        if ($tipo_pago === 'TOTAL') {
            if ($monto_pago > $saldo_real) {
                $descripcion .= "TOTAL - Excedente a favor del cliente";
            } else {
                $descripcion .= "TOTAL - Saldo liquidado";
            }
        } else {
            $descripcion .= "PARCIAL";
        }
        
        if (!empty($observaciones)) {
            $descripcion .= " - " . $observaciones;
        }
        
        $sentenciaPago = $conexion->prepare("
            INSERT INTO cuentas_corrientes (id_cliente, tipo_movimiento, monto, saldo_actual, descripcion, fecha_registro)
            VALUES (?, 'CREDITO', ?, ?, ?, NOW())
        ");
        $sentenciaPago->execute([$id_cliente, $monto_pago, $saldo_nuevo, $descripcion]);
        
        $id_movimiento = $conexion->lastInsertId();
        
        // 2. Registrar ingreso en CAJA
        $nombreCliente = trim($cliente->nombre_cliente . ' ' . $cliente->apellido_cliente);
        $conceptoCaja = "PAGO Cliente: $nombreCliente - ID Mov: #$id_movimiento";
        
        $sentenciaCaja = $conexion->prepare("
            INSERT INTO caja (tipo_movimiento, categoria, concepto, monto, fecha_movimiento, observaciones, usuario_registro)
            VALUES ('INGRESO', 'OTRO', ?, ?, ?, ?, ?)
        ");
        $sentenciaCaja->execute([
            $conceptoCaja,
            $monto_pago,
            $fecha_pago,
            "Pago de cuenta corriente. " . $descripcion,
            $usuarioActual['nombre']
        ]);
        
        // 3. Registrar en log de actividades
        registrarActividad(
            'PAGO_CUENTA_CORRIENTE',
            'CUENTAS_CORRIENTES',
            "Pago registrado - Cliente: $nombreCliente, Monto: ₲ " . number_format($monto_pago, 0, ',', '.') . " (Tipo: $tipo_pago)",
            [
                'saldo_anterior' => $saldo_real
            ],
            [
                'id_cliente' => $id_cliente,
                'monto_pago' => $monto_pago,
                'tipo_pago' => $tipo_pago,
                'saldo_nuevo' => $saldo_nuevo,
                'fecha_pago' => $fecha_pago
            ]
        );
        
        // Confirmar transacción
        $conexion->commit();
        
        // Determinar mensaje de éxito
        $titulo = "✅ Pago Registrado Exitosamente";
        
        $infoSaldo = '';
        $tipoPagoTexto = '';
        
        if ($tipo_pago === 'TOTAL') {
            $tipoPagoTexto = "<strong style='color: #27ae60;'>💰 PAGO TOTAL</strong>";
        } else {
            $tipoPagoTexto = "<strong style='color: #e67e22;'>💵 PAGO PARCIAL</strong>";
        }
        
        if ($saldo_nuevo > 0) {
            $infoSaldo = "<br>• <strong style='color: #e67e22;'>⚠️ Saldo restante:</strong> ₲ " . number_format($saldo_nuevo, 0, ',', '.');
        } else if ($saldo_nuevo < 0) {
            $infoSaldo = "<br>• <strong style='color: #27ae60;'>✓ Saldo a favor del cliente:</strong> ₲ " . number_format(abs($saldo_nuevo), 0, ',', '.');
        } else {
            $infoSaldo = "<br>• <strong style='color: #27ae60;'>✓ Deuda SALDADA completamente</strong>";
        }
        
        $mensaje = "El pago ha sido registrado correctamente.<br><br>
                    <strong>Detalles del Pago:</strong><br>
                    • Tipo: $tipoPagoTexto<br>
                    • Cliente: <strong>$nombreCliente</strong><br>
                    • Monto pagado: <strong style='color: #27ae60;'>₲ " . number_format($monto_pago, 0, ',', '.') . "</strong><br>
                    • Saldo anterior: <strong>₲ " . number_format($saldo_real, 0, ',', '.') . "</strong>$infoSaldo<br>
                    • Fecha: <strong>" . date('d/m/Y', strtotime($fecha_pago)) . "</strong><br>
                    • Registrado por: <strong>{$usuarioActual['nombre']}</strong><br><br>
                    <strong>Acciones realizadas:</strong><br>
                    • Pago registrado en cuenta corriente ✓<br>
                    • Movimiento de caja registrado ✓<br>
                    • Saldo actualizado ✓";
        $tipo = "success";
        
    } else {
        throw new Exception("Método de solicitud no válido");
    }
} catch (Exception $e) {
    if (isset($conexion) && $conexion->inTransaction()) {
        $conexion->rollBack();
    }
    
    error_log("guardar_pago.php - ERROR: " . $e->getMessage());
    
    $titulo = "❌ Error al Registrar Pago";
    $mensaje = "No se pudo completar el registro del pago:<br><br>" . htmlspecialchars($e->getMessage());
    $tipo = "error";
    
    if (isset($usuarioActual)) {
        registrarActividad('ERROR', 'CUENTAS_CORRIENTES', "Error al registrar pago: " . $e->getMessage(), null, null);
    }
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
            max-width: 700px;
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
        .action-button, .secondary-button {
            padding: 10px 18px;
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
            var idCliente = <?php echo json_encode($id_cliente ?? null); ?>;

            var icono = tipo === 'success' ? '💰✅' : '❌';
            var claseIcono = tipo === 'success' ? 'success-icon' : 'error-icon';

            var botonesHTML = "";
            
            if (tipo === 'success' && idCliente) {
                botonesHTML += "<a href='./detalle_cuenta.php?id=" + idCliente + "' class='action-button'>📋 Ver Cuenta del Cliente</a>";
                botonesHTML += "<a href='./listado_deudas.php' class='secondary-button'>📊 Ver Todas las Cuentas</a>";
                botonesHTML += "<a href='../caja/balance.php' class='secondary-button'>💰 Ver Caja</a>";
            } else {
                botonesHTML += "<a href='./listado_deudas.php' class='secondary-button'>⬅️ Volver al Listado</a>";
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
    </script>
</body>
</html>