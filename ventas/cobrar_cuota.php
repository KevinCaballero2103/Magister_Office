<?php
include_once __DIR__ . "/../auth.php";
if (!tienePermiso(['ADMINISTRADOR', 'CAJERO'])) {
    header("Location: ../index.php?error=sin_permisos");
    exit();
}
include_once "../db.php";
$cajaAbierta = requiereCajaAbierta();
$mensaje = "";
$tipo = "";
$titulo = "";
$id_cuota_cobrada = null;
$todasCuotasPagadas = false;
$id_venta_completa = null;

try {
    if (!isset($_GET['id']) && !isset($_POST['id_cuota'])) {
        throw new Exception("ID de cuota no especificado");
    }
    
    $id_cuota = isset($_POST['id_cuota']) ? intval($_POST['id_cuota']) : intval($_GET['id']);
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $fecha_pago = isset($_POST['fecha_pago']) ? $_POST['fecha_pago'] : date('Y-m-d');
        $monto_recibido = isset($_POST['monto_recibido']) ? floatval($_POST['monto_recibido']) : 0;
        
        $sentenciaCuota = $conexion->prepare("
            SELECT c.*, v.numero_venta, v.id_cliente,
                   CONCAT(COALESCE(cli.nombre_cliente, ''), ' ', COALESCE(cli.apellido_cliente, '')) as nombre_cliente
            FROM cuotas_venta c
            INNER JOIN ventas v ON c.id_venta = v.id
            LEFT JOIN clientes cli ON v.id_cliente = cli.id
            WHERE c.id = ?
        ");
        $sentenciaCuota->execute([$id_cuota]);
        $cuota = $sentenciaCuota->fetch(PDO::FETCH_OBJ);
        
        if (!$cuota) {
            throw new Exception("Cuota no encontrada");
        }
        
        if ($cuota->estado === 'PAGADA') {
            throw new Exception("Esta cuota ya fue pagada el " . date('d/m/Y', strtotime($cuota->fecha_pago)));
        }
        
        if ($monto_recibido < $cuota->monto) {
            throw new Exception("El monto recibido (₲ " . number_format($monto_recibido, 0, ',', '.') . ") es menor al monto de la cuota (₲ " . number_format($cuota->monto, 0, ',', '.') . ")");
        }
        
        $conexion->beginTransaction();
        
        $sentenciaActualizar = $conexion->prepare("
            UPDATE cuotas_venta 
            SET estado = 'PAGADA', fecha_pago = ? 
            WHERE id = ?
        ");
        $sentenciaActualizar->execute([$fecha_pago, $id_cuota]);
        
        $usuarioActual = getUsuarioActual();
        $nombreCliente = $cuota->nombre_cliente ?: 'Cliente Genérico';
        $concepto = "COBRO Cuota #{$cuota->numero} - Venta #{$cuota->id_venta} - {$nombreCliente}";
        
        $sentenciaCaja = $conexion->prepare("
            INSERT INTO caja (tipo_movimiento, categoria, id_referencia, concepto, monto, fecha_movimiento, usuario_registro, observaciones) 
            VALUES ('INGRESO', 'VENTA', ?, ?, ?, ?, ?, ?)
        ");
        
        $observacion = "Cobro de cuota {$cuota->numero}. " . ($cuota->numero_venta ? "Comprobante: {$cuota->numero_venta}" : "");
        
        $sentenciaCaja->execute([
            $cuota->id_venta,
            $concepto,
            $cuota->monto,
            $fecha_pago,
            $usuarioActual['nombre'],
            $observacion
        ]);
        
        $sentenciaVerificarCompleto = $conexion->prepare("
            SELECT COUNT(*) as total, 
                   SUM(CASE WHEN estado = 'PAGADA' THEN 1 ELSE 0 END) as pagadas
            FROM cuotas_venta 
            WHERE id_venta = ?
        ");
        $sentenciaVerificarCompleto->execute([$cuota->id_venta]);
        $estadoCuotas = $sentenciaVerificarCompleto->fetch(PDO::FETCH_OBJ);
        
        $todasCuotasPagadas = ($estadoCuotas->total == $estadoCuotas->pagadas);
        $id_venta_completa = $todasCuotasPagadas ? $cuota->id_venta : null;
        
        registrarActividad(
            'COBRO_CUOTA',
            'VENTAS',
            "Cuota cobrada: Venta #{$cuota->id_venta}, Cuota #{$cuota->numero}, Cliente: {$nombreCliente}" . ($todasCuotasPagadas ? " - DEUDA CANCELADA (todas las cuotas pagadas)" : ""),
            null,
            [
                'id_cuota' => $id_cuota,
                'id_venta' => $cuota->id_venta,
                'numero_cuota' => $cuota->numero,
                'monto' => $cuota->monto,
                'fecha_pago' => $fecha_pago,
                'todas_cuotas_pagadas' => $todasCuotasPagadas
            ]
        );
        
        $conexion->commit();
        
        $id_cuota_cobrada = $id_cuota;
        $vuelto = $monto_recibido - $cuota->monto;
        
        $titulo = "✅ Cuota Cobrada Exitosamente";
        $mensaje = "La cuota ha sido cobrada y registrada en caja.<br><br>
                    <strong>Detalles:</strong><br>
                    • Venta: <strong>#" . $cuota->id_venta . "</strong><br>
                    • Cliente: <strong>" . $nombreCliente . "</strong><br>
                    • Cuota: <strong>" . $cuota->numero . "</strong><br>
                    • Monto: <strong>₲ " . number_format($cuota->monto, 0, ',', '.') . "</strong><br>
                    • Recibido: <strong>₲ " . number_format($monto_recibido, 0, ',', '.') . "</strong><br>
                    • Vuelto: <strong style='color: #27ae60;'>₲ " . number_format($vuelto, 0, ',', '.') . "</strong><br>
                    • Fecha: <strong>" . date('d/m/Y', strtotime($fecha_pago)) . "</strong><br>
                    • Cobrado por: <strong>" . $usuarioActual['nombre'] . "</strong><br><br>
                    ✅ Movimiento registrado en caja automáticamente";
        
        if ($todasCuotasPagadas) {
            $mensaje .= "<br><br><strong style='color: #27ae60; font-size: 1.2rem;'>🎉 ¡DEUDA CANCELADA! Todas las cuotas han sido pagadas</strong>";
        }
        
        $tipo = "success";
        
    } else {
        $sentenciaCuota = $conexion->prepare("
            SELECT c.*, v.numero_venta, v.total_venta, v.fecha_venta,
                   CONCAT(COALESCE(cli.nombre_cliente, ''), ' ', COALESCE(cli.apellido_cliente, '')) as nombre_cliente,
                   cli.telefono_cliente,
                   (SELECT COUNT(*) FROM cuotas_venta WHERE id_venta = c.id_venta) as total_cuotas,
                   (SELECT COUNT(*) FROM cuotas_venta WHERE id_venta = c.id_venta AND estado = 'PAGADA') as cuotas_pagadas
            FROM cuotas_venta c
            INNER JOIN ventas v ON c.id_venta = v.id
            LEFT JOIN clientes cli ON v.id_cliente = cli.id
            WHERE c.id = ?
        ");
        $sentenciaCuota->execute([$id_cuota]);
        $cuota = $sentenciaCuota->fetch(PDO::FETCH_OBJ);
        
        if (!$cuota) {
            throw new Exception("Cuota no encontrada");
        }
        
        if ($cuota->estado === 'PAGADA') {
            throw new Exception("Esta cuota ya fue pagada el " . date('d/m/Y', strtotime($cuota->fecha_pago)));
        }
        
        $cuotaJSON = json_encode($cuota);
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Cobrar Cuota</title>
            <link href="../css/bulma.min.css" rel="stylesheet">
            <link href="../css/formularios.css" rel="stylesheet">
            <style>
                .main-content { background: #2c3e50 !important; color: white; }
                .info-cuota {
                    background: rgba(52, 152, 219, 0.1);
                    border: 2px solid rgba(52, 152, 219, 0.3);
                    padding: 20px;
                    border-radius: 10px;
                    margin-bottom: 25px;
                }
                .info-cuota h3 {
                    color: #3498db;
                    font-size: 1.2rem;
                    font-weight: bold;
                    margin-bottom: 15px;
                    text-align: center;
                }
                .info-row {
                    display: flex;
                    justify-content: space-between;
                    padding: 8px 0;
                    border-bottom: 1px solid rgba(255,255,255,0.1);
                }
                .info-row:last-child {
                    border-bottom: none;
                }
                .info-label {
                    font-weight: bold;
                    color: rgba(255,255,255,0.8);
                }
                .info-value {
                    color: #f1c40f;
                    font-weight: bold;
                }
                .monto-display {
                    background: rgba(39, 174, 96, 0.2);
                    padding: 20px;
                    border-radius: 10px;
                    text-align: center;
                    margin: 20px 0;
                    border: 2px solid #27ae60;
                }
                .monto-label {
                    color: rgba(255,255,255,0.8);
                    font-size: 0.9rem;
                    margin-bottom: 5px;
                }
                .monto-valor {
                    color: #2ecc71;
                    font-size: 2.5rem;
                    font-weight: bold;
                }
                .vuelto-display {
                    background: rgba(52, 152, 219, 0.1);
                    padding: 15px;
                    border-radius: 8px;
                    text-align: center;
                    margin-top: 15px;
                }
                .vuelto-valor {
                    font-size: 2rem;
                    font-weight: bold;
                    color: #3498db;
                }
            </style>
        </head>
        <body>
            <?php include '../menu.php'; ?>
            
            <script>
                const cuota = <?php echo $cuotaJSON; ?>;
                const formatMoney = (num) => '₲ ' + parseFloat(num).toLocaleString('es-PY', {minimumFractionDigits: 2});
                const formatDate = (dateStr) => {
                    const d = new Date(dateStr + 'T00:00:00');
                    return d.toLocaleDateString('es-PY', { day: '2-digit', month: '2-digit', year: 'numeric' });
                };
                
                document.addEventListener('DOMContentLoaded', function() {
                    const mainContent = document.querySelector('.main-content');
                    if (!mainContent) return;
                    
                    const hoy = new Date().toISOString().split('T')[0];
                    const nombreCliente = cuota.nombre_cliente || 'Cliente Genérico';
                    
                    const contentHTML = `
                        <div class="form-container">
                            <h1 class="form-title">💰 Cobrar Cuota</h1>
                            
                            <div class="info-cuota">
                                <h3>📋 Información de la Cuota</h3>
                                <div class="info-row">
                                    <div class="info-label">Venta:</div>
                                    <div class="info-value">#${cuota.id_venta} ${cuota.numero_venta ? '(' + cuota.numero_venta + ')' : ''}</div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Cliente:</div>
                                    <div class="info-value">${nombreCliente}</div>
                                </div>
                                ${cuota.telefono_cliente ? `
                                <div class="info-row">
                                    <div class="info-label">Teléfono:</div>
                                    <div class="info-value">☎ ${cuota.telefono_cliente}</div>
                                </div>
                                ` : ''}
                                <div class="info-row">
                                    <div class="info-label">Cuota:</div>
                                    <div class="info-value">${cuota.numero} de ${cuota.total_cuotas}</div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Vencimiento:</div>
                                    <div class="info-value">${formatDate(cuota.fecha_vencimiento)}</div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Progreso:</div>
                                    <div class="info-value">${cuota.cuotas_pagadas} de ${cuota.total_cuotas} cuotas pagadas</div>
                                </div>
                            </div>
                            
                            <div class="monto-display">
                                <div class="monto-label">💵 MONTO A COBRAR</div>
                                <div class="monto-valor">${formatMoney(cuota.monto)}</div>
                            </div>
                            
                            <form method="post" action="" onsubmit="return confirmarCobro()">
                                <input type="hidden" name="id_cuota" value="${cuota.id}">
                                
                                <div class="columns">
                                    <div class="column is-6 is-offset-3">
                                        <div class="field">
                                            <label class="label">Fecha de Pago *</label>
                                            <div class="control">
                                                <input class="input" type="date" name="fecha_pago" id="fecha_pago" value="${hoy}" required>
                                            </div>
                                        </div>
                                        
                                        <div class="field">
                                            <label class="label">💵 Dinero Recibido *</label>
                                            <div class="control">
                                                <input class="input" type="number" step="0.01" min="${cuota.monto}" name="monto_recibido" id="monto_recibido" 
                                                       placeholder="0.00" required style="font-size: 1.3rem; font-weight: bold;" 
                                                       oninput="calcularVuelto()">
                                            </div>
                                            <p class="help" style="color: rgba(255,255,255,0.7);">
                                                Ingresa el monto que entregó el cliente
                                            </p>
                                        </div>
                                        
                                        <div class="vuelto-display" id="vuelto-display" style="display: none;">
                                            <div style="color: #3498db; font-size: 0.9rem; margin-bottom: 5px;">💰 VUELTO</div>
                                            <div class="vuelto-valor" id="vuelto-valor">₲ 0,00</div>
                                        </div>
                                        
                                        <div class="field is-grouped" style="justify-content: center; margin-top: 30px;">
                                            <div class="control">
                                                <button type="submit" class="button">✅ Confirmar Cobro</button>
                                            </div>
                                            <div class="control">
                                                <a href="./gestionar_cuotas.php" class="secondary-button">❌ Cancelar</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    `;
                    
                    mainContent.innerHTML = contentHTML;
                });
                
                function calcularVuelto() {
                    const recibido = parseFloat(document.getElementById('monto_recibido').value) || 0;
                    const montoCuota = parseFloat(cuota.monto);
                    const vuelto = recibido - montoCuota;
                    
                    const display = document.getElementById('vuelto-display');
                    const valor = document.getElementById('vuelto-valor');
                    
                    if (recibido > 0) {
                        display.style.display = 'block';
                        if (vuelto < 0) {
                            valor.textContent = '⚠️ FALTA: ' + formatMoney(Math.abs(vuelto));
                            valor.style.color = '#e74c3c';
                        } else {
                            valor.textContent = formatMoney(vuelto);
                            valor.style.color = '#2ecc71';
                        }
                    } else {
                        display.style.display = 'none';
                    }
                }
                
                function confirmarCobro() {
                    const recibido = parseFloat(document.getElementById('monto_recibido').value) || 0;
                    const montoCuota = parseFloat(cuota.monto);
                    
                    if (recibido < montoCuota) {
                        alert('⚠️ El monto recibido es menor al monto de la cuota');
                        return false;
                    }
                    
                    const vuelto = recibido - montoCuota;
                    return confirm(`¿Confirmar cobro de cuota?\n\nMonto: ${formatMoney(montoCuota)}\nRecibido: ${formatMoney(recibido)}\nVuelto: ${formatMoney(vuelto)}`);
                }
            </script>
        </body>
        </html>
        <?php
        exit;
    }
    
} catch (Exception $e) {
    $titulo = "❌ Error al Cobrar Cuota";
    $mensaje = htmlspecialchars($e->getMessage());
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
        body { background: #2c3e50 !important; }
        .main-content { display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px; background: #2c3e50 !important; }
        .print-button {
            color: white;
            background: linear-gradient(45deg, #3498db, #2980b9);
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
        .print-button:hover {
            background: linear-gradient(45deg, #2980b9, #3498db);
            transform: translateY(-2px);
            color: white;
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
            const idCuota = <?php echo json_encode($id_cuota_cobrada); ?>;
            const todasPagadas = <?php echo $todasCuotasPagadas ? 'true' : 'false'; ?>;
            const idVentaCompleta = <?php echo json_encode($id_venta_completa); ?>;

            const icono = tipo === 'success' ? '💰✅' : '❌';
            
            let botonesHTML = '';
            
            if (tipo === 'success') {
                if (idCuota) {
                    botonesHTML += `<button class='print-button' onclick="imprimirReciboCuota(${idCuota})">🖨️ Imprimir Recibo de Cuota</button>`;
                }
                
                if (todasPagadas && idVentaCompleta) {
                    botonesHTML += `<button class='print-button' onclick="imprimirPagare(${idVentaCompleta})" style="background: linear-gradient(45deg, #27ae60, #2ecc71);">📄 Imprimir Pagaré (Deuda Cancelada)</button>`;
                }
                
                botonesHTML += "<a href='./gestionar_cuotas.php' class='action-button'>📋 Ver Cuotas</a>";
                botonesHTML += "<a href='./listado_ventas.php' class='secondary-button'>💰 Ver Ventas</a>";
            } else {
                botonesHTML += "<a href='./gestionar_cuotas.php' class='secondary-button'>⬅️ Volver</a>";
            }

            mainContent.innerHTML = `
                <div class='message-container'>
                    <span class='status-icon'>${icono}</span>
                    <h1 class='message-title'>${titulo}</h1>
                    <div class='message-content'>${mensaje}</div>
                    <div class='button-group'>
                        ${botonesHTML}
                    </div>
                </div>
            `;
        });
        
        function imprimirReciboCuota(idCuota) {
            window.open('./imprimir_comprobante.php?tipo=RECIBO_CUOTA&id_cuota=' + idCuota, '_blank', 'width=900,height=700');
        }
        
        function imprimirPagare(idVenta) {
            window.open('./imprimir_comprobante.php?tipo=PAGARE&id_venta=' + idVenta, '_blank', 'width=900,height=700');
        }
    </script>
</body>
</html>