<?php
include_once __DIR__ . "/../auth.php";
include_once "../db.php";

$cajaAbierta = requiereCajaAbierta();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: listado_deudas.php?error=id_requerido");
    exit();
}

$id_cliente = intval($_GET['id']);

// Obtener datos del cliente
$sentenciaCliente = $conexion->prepare("SELECT * FROM clientes WHERE id = ?");
$sentenciaCliente->execute([$id_cliente]);
$cliente = $sentenciaCliente->fetch(PDO::FETCH_OBJ);

if (!$cliente) {
    header("Location: listado_deudas.php?error=cliente_no_encontrado");
    exit();
}

// Calcular saldo actual
$sentenciaSaldo = $conexion->prepare("
    SELECT 
        COALESCE(SUM(CASE WHEN tipo_movimiento = 'DEBITO' THEN monto ELSE -monto END), 0) as saldo_actual
    FROM cuentas_corrientes
    WHERE id_cliente = ?
");
$sentenciaSaldo->execute([$id_cliente]);
$saldo_actual = floatval($sentenciaSaldo->fetchColumn());

if ($saldo_actual <= 0) {
    header("Location: detalle_cuenta.php?id=$id_cliente&error=sin_deuda");
    exit();
}

registrarActividad('ACCESO', 'CUENTAS_CORRIENTES', "Acceso a formulario de pago - Cliente: {$cliente->nombre_cliente} {$cliente->apellido_cliente}", null, null);

$clienteJSON = json_encode($cliente);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Pago - <?php echo htmlspecialchars($cliente->nombre_cliente . ' ' . $cliente->apellido_cliente); ?></title>
    <link href="../css/bulma.min.css" rel="stylesheet">
    <link href="../css/formularios.css" rel="stylesheet">
    <style>
        .main-content { background: #2c3e50 !important; color: white; }
        .info-cliente {
            background: rgba(52, 152, 219, 0.1);
            border: 2px solid rgba(52, 152, 219, 0.3);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
        }
        .info-cliente h3 {
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
        .deuda-display {
            background: rgba(231, 76, 60, 0.2);
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            margin: 20px 0;
            border: 2px solid #e74c3c;
        }
        .deuda-label {
            color: rgba(255,255,255,0.8);
            font-size: 0.9rem;
            margin-bottom: 5px;
        }
        .deuda-monto {
            color: #e74c3c;
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
        const cliente = <?php echo $clienteJSON; ?>;
        const saldoActual = <?php echo $saldo_actual; ?>;
        const formatMoney = (num) => '₲ ' + parseFloat(num).toLocaleString('es-PY', {minimumFractionDigits: 2});
        
        document.addEventListener('DOMContentLoaded', function() {
            const mainContent = document.querySelector('.main-content');
            if (!mainContent) return;
            
            const hoy = new Date().toISOString().split('T')[0];
            const nombreCompleto = `${cliente.nombre_cliente} ${cliente.apellido_cliente}`;
            
            const contentHTML = `
                <div class="form-container">
                    <h1 class="form-title">💰 Registrar Pago de Cliente</h1>
                    
                    <div class="info-cliente">
                        <h3>📋 Información del Cliente</h3>
                        <div class="info-row">
                            <div class="info-label">Cliente:</div>
                            <div class="info-value">${nombreCompleto}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">CI/RUC:</div>
                            <div class="info-value">${cliente.ci_ruc_cliente || 'No especificado'}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Teléfono:</div>
                            <div class="info-value">${cliente.telefono_cliente || 'No especificado'}</div>
                        </div>
                    </div>
                    
                    <div class="deuda-display">
                        <div class="deuda-label">⚠️ SALDO ADEUDADO</div>
                        <div class="deuda-monto">${formatMoney(saldoActual)}</div>
                    </div>
                    
                    <form method="post" action="./guardar_pago.php" onsubmit="return validarPago()">
                        <input type="hidden" name="id_cliente" value="${cliente.id}">
                        <input type="hidden" name="saldo_actual" value="${saldoActual}">
                        <input type="hidden" name="tipo_pago" id="tipo_pago_hidden" value="PARCIAL">
                        
                        <div class="columns">
                            <div class="column is-6 is-offset-3">
                                <div class="field">
                                    <label class="label">📅 Fecha del Pago *</label>
                                    <div class="control">
                                        <input class="input" type="date" name="fecha_pago" id="fecha_pago" value="${hoy}" required>
                                    </div>
                                </div>
                                
                                <div class="field">
                                    <label class="label">💵 Monto del Pago *</label>
                                    <div class="control">
                                        <input class="input" type="number" step="0.01" min="0.01" name="monto_pago" id="monto_pago" 
                                               placeholder="0.00" required style="font-size: 1.3rem; font-weight: bold;" 
                                               oninput="calcularVuelto()">
                                    </div>
                                    <p class="help" style="color: rgba(255,255,255,0.7);">
                                        Ingresa el monto que está pagando el cliente
                                    </p>
                                </div>
                                
                                <div class="vuelto-display" id="vuelto-display" style="display: none;">
                                    <div style="color: #3498db; font-size: 0.9rem; margin-bottom: 5px;">💰 INFORMACIÓN</div>
                                    <div class="vuelto-valor" id="vuelto-valor">₲ 0,00</div>
                                    <div style="margin-top: 8px; font-size: 0.85rem; color: rgba(255,255,255,0.8);" id="info-texto"></div>
                                </div>
                                
                                <div class="field">
                                    <label class="label">Observaciones (Opcional)</label>
                                    <div class="control">
                                        <textarea class="textarea" name="observaciones" id="observaciones" rows="3" 
                                                  placeholder="Notas adicionales sobre el pago..."></textarea>
                                    </div>
                                </div>
                                
                                <div class="field is-grouped" style="justify-content: center; margin-top: 30px;">
                                    <div class="control">
                                        <button type="submit" class="button">✅ Confirmar Pago</button>
                                    </div>
                                    <div class="control">
                                        <a href="./detalle_cuenta.php?id=${cliente.id}" class="secondary-button">❌ Cancelar</a>
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
            const montoPago = parseFloat(document.getElementById('monto_pago').value) || 0;
            const display = document.getElementById('vuelto-display');
            const valor = document.getElementById('vuelto-valor');
            const infoTexto = document.getElementById('info-texto');
            const tipoPagoHidden = document.getElementById('tipo_pago_hidden');
            
            if (montoPago > 0) {
                display.style.display = 'block';
                
                if (montoPago > saldoActual) {
                    // Pago de más = a favor del cliente (TOTAL con excedente)
                    const aFavor = montoPago - saldoActual;
                    valor.textContent = formatMoney(aFavor);
                    valor.style.color = '#27ae60';
                    infoTexto.innerHTML = '✓ <strong>PAGO TOTAL</strong><br>Pagará de más. Quedará a favor del cliente.';
                    infoTexto.style.color = '#27ae60';
                    tipoPagoHidden.value = 'TOTAL';
                } else if (montoPago === saldoActual) {
                    // Pago exacto (TOTAL)
                    valor.textContent = '✓ PAGO EXACTO';
                    valor.style.color = '#3498db';
                    infoTexto.innerHTML = '<strong>PAGO TOTAL</strong><br>El cliente saldará completamente su deuda';
                    infoTexto.style.color = '#3498db';
                    tipoPagoHidden.value = 'TOTAL';
                } else {
                    // Pago parcial
                    const restante = saldoActual - montoPago;
                    valor.textContent = formatMoney(restante);
                    valor.style.color = '#e67e22';
                    infoTexto.innerHTML = '⚠️ <strong>PAGO PARCIAL</strong><br>Quedará debiendo este monto.';
                    infoTexto.style.color = '#e67e22';
                    tipoPagoHidden.value = 'PARCIAL';
                }
            } else {
                display.style.display = 'none';
                tipoPagoHidden.value = 'PARCIAL';
            }
        }
        
        function validarPago() {
            const montoPago = parseFloat(document.getElementById('monto_pago').value) || 0;
            const tipoPago = document.getElementById('tipo_pago_hidden').value;
            
            if (montoPago <= 0) {
                alert('⚠️ El monto del pago debe ser mayor a 0');
                document.getElementById('monto_pago').focus();
                return false;
            }
            
            const nombreCompleto = `${cliente.nombre_cliente} ${cliente.apellido_cliente}`;
            let mensaje = '¿Confirmar pago?\n\n';
            mensaje += 'Cliente: ' + nombreCompleto + '\n';
            mensaje += 'Deuda actual: ' + formatMoney(saldoActual) + '\n';
            mensaje += 'Monto a pagar: ' + formatMoney(montoPago) + '\n';
            mensaje += 'Tipo: ' + tipoPago + '\n';
            
            if (montoPago > saldoActual) {
                const aFavor = montoPago - saldoActual;
                mensaje += '\n⚠️ El cliente pagará de más.\n';
                mensaje += 'Quedará a su favor: ' + formatMoney(aFavor);
            } else if (montoPago === saldoActual) {
                mensaje += '\n✓ Saldará completamente la deuda';
            } else {
                const restante = saldoActual - montoPago;
                mensaje += '\nQuedará debiendo: ' + formatMoney(restante);
            }
            
            return confirm(mensaje);
        }
    </script>
</body>
</html>