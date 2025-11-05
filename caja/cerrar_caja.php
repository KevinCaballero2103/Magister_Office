<?php
include_once "../auth.php";
include_once "../db.php";

// Registrar acceso
registrarActividad('ACCESO', 'CAJA', 'Acceso a cierre de caja', null, null);

// Verificar si hay caja abierta
$sentenciaCaja = $conexion->prepare("SELECT * FROM cierres_caja WHERE estado = 'ABIERTA' ORDER BY fecha_apertura DESC LIMIT 1");
$sentenciaCaja->execute();
$cajaAbierta = $sentenciaCaja->fetch(PDO::FETCH_OBJ);

if (!$cajaAbierta) {
    header("Location: balance.php?error=no_caja_abierta");
    exit();
}

// Calcular movimientos desde la apertura
$sentenciaMovs = $conexion->prepare("
    SELECT 
        SUM(CASE WHEN tipo_movimiento = 'INGRESO' THEN monto ELSE 0 END) as ingresos,
        SUM(CASE WHEN tipo_movimiento = 'EGRESO' THEN monto ELSE 0 END) as egresos
    FROM caja 
    WHERE fecha_movimiento >= ?
");
$sentenciaMovs->execute([$cajaAbierta->fecha_apertura]);
$movs = $sentenciaMovs->fetch(PDO::FETCH_OBJ);

$ingresos = floatval($movs->ingresos ?? 0);
$egresos = floatval($movs->egresos ?? 0);
$saldo_sistema = floatval($cajaAbierta->saldo_inicial) + $ingresos - $egresos;

$dataJSON = json_encode([
    'id' => $cajaAbierta->id,
    'fecha_apertura' => $cajaAbierta->fecha_apertura,
    'saldo_inicial' => floatval($cajaAbierta->saldo_inicial),
    'ingresos' => $ingresos,
    'egresos' => $egresos,
    'saldo_sistema' => $saldo_sistema,
    'usuario_apertura' => $cajaAbierta->usuario_apertura
]);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cerrar Caja</title>
    <link href="../css/bulma.min.css" rel="stylesheet">
    <link href="../css/formularios.css" rel="stylesheet">
    <style>
        .main-content { background: #2c3e50 !important; color: white; }
        .cierre-icon {
            text-align: center;
            font-size: 5rem;
            margin-bottom: 20px;
        }
        .resumen-caja {
            background: rgba(52, 152, 219, 0.1);
            border: 2px solid rgba(52, 152, 219, 0.3);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
        }
        .resumen-title {
            color: #3498db;
            font-size: 1.3rem;
            font-weight: bold;
            margin-bottom: 15px;
            text-align: center;
        }
        .resumen-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .resumen-item:last-child {
            border-bottom: none;
        }
        .resumen-label {
            font-weight: bold;
            color: rgba(255,255,255,0.9);
        }
        .resumen-value {
            font-weight: bold;
            color: #3498db;
        }
        .saldo-sistema {
            background: rgba(39, 174, 96, 0.2);
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            margin: 20px 0;
        }
        .saldo-sistema-label {
            color: rgba(255,255,255,0.8);
            font-size: 0.9rem;
        }
        .saldo-sistema-value {
            color: #2ecc71;
            font-size: 2rem;
            font-weight: bold;
            margin-top: 5px;
        }
        .diferencia-display {
            background: rgba(0,0,0,0.2);
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            margin-top: 15px;
        }
        .info-usuario {
            background: rgba(52, 152, 219, 0.1);
            border: 2px solid rgba(52, 152, 219, 0.3);
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <?php include '../menu.php'; ?>
    
    <script>
        const data = <?php echo $dataJSON; ?>;
        const formatMoney = (num) => '₲ ' + parseFloat(num).toLocaleString('es-PY', {minimumFractionDigits: 2});
        const usuarioCierre = '<?php echo addslashes($_SESSION['usuario_nombre']); ?>';

        document.addEventListener('DOMContentLoaded', function() {
            const mainContent = document.querySelector('.main-content');
            if (!mainContent) return;

            const now = new Date();
            const fechaHora = `${now.getFullYear()}-${String(now.getMonth()+1).padStart(2,'0')}-${String(now.getDate()).padStart(2,'0')}T${String(now.getHours()).padStart(2,'0')}:${String(now.getMinutes()).padStart(2,'0')}`;

            const fechaApertura = new Date(data.fecha_apertura).toLocaleString('es-PY');

            const formHTML = `
                <div class="form-container">
                    <div class="cierre-icon">🔒💰</div>
                    <h1 class="form-title">Cierre de Caja</h1>
                    
                    <div class="info-usuario">
                        👤 Cierre realizado por: <strong style="color: #f1c40f;">${usuarioCierre}</strong>
                    </div>
                    
                    <div class="resumen-caja">
                        <div class="resumen-title">📊 Resumen de Caja</div>
                        <div class="resumen-item">
                            <div class="resumen-label">Usuario Apertura:</div>
                            <div class="resumen-value">${data.usuario_apertura}</div>
                        </div>
                        <div class="resumen-item">
                            <div class="resumen-label">Fecha Apertura:</div>
                            <div class="resumen-value">${fechaApertura}</div>
                        </div>
                        <div class="resumen-item">
                            <div class="resumen-label">💵 Saldo Inicial:</div>
                            <div class="resumen-value">${formatMoney(data.saldo_inicial)}</div>
                        </div>
                        <div class="resumen-item">
                            <div class="resumen-label">💰 Total Ingresos:</div>
                            <div class="resumen-value" style="color: #2ecc71;">${formatMoney(data.ingresos)}</div>
                        </div>
                        <div class="resumen-item">
                            <div class="resumen-label">💸 Total Egresos:</div>
                            <div class="resumen-value" style="color: #e74c3c;">${formatMoney(data.egresos)}</div>
                        </div>
                    </div>

                    <div class="saldo-sistema">
                        <div class="saldo-sistema-label">💻 SALDO SEGÚN SISTEMA</div>
                        <div class="saldo-sistema-value">${formatMoney(data.saldo_sistema)}</div>
                    </div>

                    <form action="./procesar_cierre.php" method="post" onsubmit="return validarCierre()">
                        <input type="hidden" name="id_cierre" value="${data.id}">
                        <input type="hidden" name="saldo_sistema" value="${data.saldo_sistema}">
                        <input type="hidden" name="total_ingresos" value="${data.ingresos}">
                        <input type="hidden" name="total_egresos" value="${data.egresos}">
                        
                        <div class="columns">
                            <div class="column is-8 is-offset-2">
                                <div class="field">
                                    <label class="label">Fecha y Hora de Cierre *</label>
                                    <div class="control">
                                        <input class="input" type="datetime-local" name="fecha_cierre" value="${fechaHora}" required>
                                    </div>
                                </div>

                                <div class="field">
                                    <label class="label">💵 Saldo Físico Real (Conteo de Dinero) *</label>
                                    <div class="control">
                                        <input class="input" type="number" step="0.01" min="0" name="saldo_fisico" id="saldo_fisico" placeholder="0.00" required style="font-size: 1.3rem; font-weight: bold;" oninput="calcularDiferencia()">
                                    </div>
                                    <p class="help" style="color: rgba(255,255,255,0.7);">Cuenta el dinero físico que hay en caja</p>
                                </div>

                                <div class="diferencia-display" id="diferencia-display" style="display: none;">
                                    <div style="font-size: 1rem; margin-bottom: 5px;">📊 DIFERENCIA</div>
                                    <div id="diferencia-valor" style="font-size: 1.8rem; font-weight: bold;"></div>
                                    <div id="diferencia-mensaje" style="margin-top: 10px; font-size: 0.9rem;"></div>
                                </div>

                                <div class="field">
                                    <label class="label">Observaciones de Cierre</label>
                                    <div class="control">
                                        <textarea class="textarea" name="observaciones_cierre" rows="3" placeholder="Notas sobre el cierre, explicación de diferencias..."></textarea>
                                    </div>
                                </div>

                                <div class="field is-grouped" style="justify-content: center; margin-top: 30px;">
                                    <div class="control">
                                        <button type="submit" class="button">🔒 Cerrar Caja</button>
                                    </div>
                                    <div class="control">
                                        <a href="./balance.php" class="secondary-button">❌ Cancelar</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            `;

            mainContent.innerHTML = formHTML;
        });

        function calcularDiferencia() {
            const saldoFisico = parseFloat(document.getElementById('saldo_fisico').value) || 0;
            const saldoSistema = data.saldo_sistema;
            const diferencia = saldoFisico - saldoSistema;

            const display = document.getElementById('diferencia-display');
            const valor = document.getElementById('diferencia-valor');
            const mensaje = document.getElementById('diferencia-mensaje');

            if (saldoFisico > 0) {
                display.style.display = 'block';
                valor.textContent = formatMoney(Math.abs(diferencia));

                if (diferencia === 0) {
                    valor.style.color = '#2ecc71';
                    mensaje.textContent = '✅ ¡Perfecto! El dinero físico coincide con el sistema';
                    mensaje.style.color = '#2ecc71';
                } else if (diferencia > 0) {
                    valor.style.color = '#3498db';
                    mensaje.textContent = '💰 HAY MÁS dinero físico que en sistema (sobrante)';
                    mensaje.style.color = '#3498db';
                } else {
                    valor.style.color = '#e74c3c';
                    mensaje.textContent = '⚠️ FALTA dinero físico respecto al sistema';
                    mensaje.style.color = '#e74c3c';
                }
            } else {
                display.style.display = 'none';
            }
        }

        function validarCierre() {
            const saldoFisico = parseFloat(document.getElementById('saldo_fisico').value) || 0;
            const diferencia = saldoFisico - data.saldo_sistema;

            let mensaje = '¿Confirmar cierre de caja?\\n\\n';
            mensaje += 'Saldo Sistema: ' + formatMoney(data.saldo_sistema) + '\\n';
            mensaje += 'Saldo Físico: ' + formatMoney(saldoFisico) + '\\n';
            mensaje += 'Diferencia: ' + formatMoney(Math.abs(diferencia));

            if (diferencia !== 0) {
                mensaje += '\\n\\n⚠️ HAY DIFERENCIA. ¿Continuar?';
            }

            return confirm(mensaje);
        }
    </script>
</body>
</html>