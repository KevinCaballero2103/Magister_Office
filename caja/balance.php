<?php
include_once __DIR__ . "/../auth.php";
include_once "../db.php";

// Fecha actual
$hoy = date('Y-m-d');
$mes_actual = date('Y-m');

// ESTADÍSTICAS DEL DÍA
$sentenciaDia = $conexion->prepare("
    SELECT 
        SUM(CASE WHEN tipo_movimiento = 'INGRESO' THEN monto ELSE 0 END) as ingresos_dia,
        SUM(CASE WHEN tipo_movimiento = 'EGRESO' THEN monto ELSE 0 END) as egresos_dia
    FROM caja 
    WHERE DATE(fecha_movimiento) = ?
");
$sentenciaDia->execute([$hoy]);
$estadisticasDia = $sentenciaDia->fetch(PDO::FETCH_OBJ);

$ingresos_dia = floatval($estadisticasDia->ingresos_dia ?? 0);
$egresos_dia = floatval($estadisticasDia->egresos_dia ?? 0);
$saldo_dia = $ingresos_dia - $egresos_dia;

// ESTADÍSTICAS DEL MES
$sentenciaMes = $conexion->prepare("
    SELECT 
        SUM(CASE WHEN tipo_movimiento = 'INGRESO' THEN monto ELSE 0 END) as ingresos_mes,
        SUM(CASE WHEN tipo_movimiento = 'EGRESO' THEN monto ELSE 0 END) as egresos_mes
    FROM caja 
    WHERE DATE_FORMAT(fecha_movimiento, '%Y-%m') = ?
");
$sentenciaMes->execute([$mes_actual]);
$estadisticasMes = $sentenciaMes->fetch(PDO::FETCH_OBJ);

$ingresos_mes = floatval($estadisticasMes->ingresos_mes ?? 0);
$egresos_mes = floatval($estadisticasMes->egresos_mes ?? 0);
$saldo_mes = $ingresos_mes - $egresos_mes;

// SALDO TOTAL ACUMULADO (desde el inicio)
$sentenciaTotal = $conexion->prepare("
    SELECT 
        SUM(CASE WHEN tipo_movimiento = 'INGRESO' THEN monto ELSE -monto END) as saldo_total
    FROM caja
");
$sentenciaTotal->execute();
$saldo_total = floatval($sentenciaTotal->fetchColumn() ?? 0);

// ÚLTIMOS 10 MOVIMIENTOS
$sentenciaUltimos = $conexion->prepare("
    SELECT * FROM caja 
    ORDER BY fecha_movimiento DESC, fecha_registro DESC 
    LIMIT 10
");
$sentenciaUltimos->execute();
$ultimos_movimientos = $sentenciaUltimos->fetchAll(PDO::FETCH_OBJ);

// Convertir a JSON para JavaScript
$estadisticasJSON = json_encode([
    'ingresos_dia' => $ingresos_dia,
    'egresos_dia' => $egresos_dia,
    'saldo_dia' => $saldo_dia,
    'ingresos_mes' => $ingresos_mes,
    'egresos_mes' => $egresos_mes,
    'saldo_mes' => $saldo_mes,
    'saldo_total' => $saldo_total
]);
$movimientosJSON = json_encode($ultimos_movimientos);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Balance de Caja</title>
    <link href="../css/bulma.min.css" rel="stylesheet">
    <link href="../css/listados.css" rel="stylesheet">
    <link href="../css/estadisticas.css" rel="stylesheet">
    <style>
        .main-content { background: #2c3e50 !important; color: white; }
        .balance-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .balance-title {
            color: #f1c40f;
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        .balance-subtitle {
            color: rgba(255,255,255,0.9);
            font-size: 1.1rem;
        }
        .saldo-total-card {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            margin-bottom: 25px;
            box-shadow: 0 8px 25px rgba(39, 174, 96, 0.4);
        }
        .saldo-total-label {
            color: rgba(255,255,255,0.9);
            font-size: 1rem;
            margin-bottom: 10px;
        }
        .saldo-total-amount {
            color: white;
            font-size: 3rem;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }
        .quick-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        .quick-btn {
            background: linear-gradient(45deg, #f39c12, #f1c40f) !important;
            color: #2c3e50 !important;
            font-weight: bold !important;
            padding: 15px 30px !important;
            border-radius: 10px !important;
            text-decoration: none !important;
            transition: all 0.3s ease;
            border: none !important;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .quick-btn:hover {
            background: linear-gradient(45deg, #e67e22, #f39c12) !important;
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(243, 156, 18, 0.4);
        }
        .quick-btn.secondary {
            background: linear-gradient(45deg, #3498db, #2980b9) !important;
            color: white !important;
        }
        .quick-btn.secondary:hover {
            background: linear-gradient(45deg, #2980b9, #3498db) !important;
        }
        .movimientos-recientes {
            background: rgba(0,0,0,0.2);
            padding: 25px;
            border-radius: 12px;
            margin-top: 30px;
        }
        .movimientos-title {
            color: #f1c40f;
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 20px;
            text-align: center;
        }
        .mov-item {
            background: rgba(255,255,255,0.05);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            border-left: 4px solid;
            transition: all 0.3s ease;
        }
        .mov-item:hover {
            background: rgba(255,255,255,0.1);
            transform: translateX(5px);
        }
        .mov-item.ingreso { border-left-color: #27ae60; }
        .mov-item.egreso { border-left-color: #e74c3c; }
        .mov-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        .mov-tipo {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        .mov-tipo.ingreso {
            background: linear-gradient(45deg, #27ae60, #2ecc71);
            color: white;
        }
        .mov-tipo.egreso {
            background: linear-gradient(45deg, #e74c3c, #c0392b);
            color: white;
        }
        .mov-monto {
            font-size: 1.3rem;
            font-weight: bold;
        }
        .mov-monto.ingreso { color: #2ecc71; }
        .mov-monto.egreso { color: #e74c3c; }
        .mov-concepto {
            color: rgba(255,255,255,0.9);
            font-size: 0.95rem;
            margin-bottom: 5px;
        }
        .mov-fecha {
            color: rgba(255,255,255,0.6);
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
    <?php include '../menu.php'; ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mainContent = document.querySelector('.main-content');
            if (!mainContent) {
                console.error('No se encontró .main-content');
                return;
            }

            const stats = <?php echo $estadisticasJSON; ?>;
            const movs = <?php echo $movimientosJSON; ?>;
            
            const formatMoney = (num) => '₲ ' + parseFloat(num).toLocaleString('es-PY', {minimumFractionDigits: 2});
            const formatDate = (dateStr) => {
                const d = new Date(dateStr + 'T00:00:00');
                return d.toLocaleDateString('es-PY', { day: '2-digit', month: '2-digit', year: 'numeric' });
            };

            let movimientosHTML = '';
            if (movs.length > 0) {
                movs.forEach(mov => {
                    const tipoClass = mov.tipo_movimiento.toLowerCase();
                    const tipoLabel = mov.tipo_movimiento === 'INGRESO' ? '💰 INGRESO' : '💸 EGRESO';
                    movimientosHTML += `
                        <div class="mov-item ${tipoClass}">
                            <div class="mov-header">
                                <span class="mov-tipo ${tipoClass}">${tipoLabel}</span>
                                <span class="mov-monto ${tipoClass}">${formatMoney(mov.monto)}</span>
                            </div>
                            <div class="mov-concepto">${mov.concepto}</div>
                            <div class="mov-fecha">📅 ${formatDate(mov.fecha_movimiento)} | Categoría: ${mov.categoria}</div>
                        </div>
                    `;
                });
            } else {
                movimientosHTML = '<div style="text-align: center; color: rgba(255,255,255,0.6); padding: 40px;">No hay movimientos registrados</div>';
            }

            const contentHTML = `
                <div class="list-container">
                    <div class="balance-header">
                        <h1 class="balance-title">💰 Balance de Caja</h1>
                        <p class="balance-subtitle">Control de ingresos y egresos - <?php echo date('d/m/Y'); ?></p>
                    </div>

                    <div class="saldo-total-card">
                        <div class="saldo-total-label">💎 SALDO TOTAL ACUMULADO</div>
                        <div class="saldo-total-amount">${formatMoney(stats.saldo_total)}</div>
                    </div>

                    <div class="quick-actions">
                        <a href="./registrar_movimiento.php" class="quick-btn">
                            ➕ Registrar Movimiento
                        </a>
                        <a href="./historial_movimientos.php" class="quick-btn secondary">
                            📋 Ver Historial Completo
                        </a>
                    </div>

                    <div class="stats-container">
                        <div class="stat-card">
                            <div class="stat-number stat-success">${formatMoney(stats.ingresos_dia)}</div>
                            <div class="stat-label">💰 Ingresos Hoy</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number stat-warning">${formatMoney(stats.egresos_dia)}</div>
                            <div class="stat-label">💸 Egresos Hoy</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number ${stats.saldo_dia >= 0 ? 'stat-success' : 'stat-critico'}">${formatMoney(stats.saldo_dia)}</div>
                            <div class="stat-label">📊 Saldo del Día</div>
                        </div>
                    </div>

                    <div class="stats-container" style="margin-top: 20px;">
                        <div class="stat-card">
                            <div class="stat-number stat-info">${formatMoney(stats.ingresos_mes)}</div>
                            <div class="stat-label">💰 Ingresos del Mes</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number stat-warning">${formatMoney(stats.egresos_mes)}</div>
                            <div class="stat-label">💸 Egresos del Mes</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number ${stats.saldo_mes >= 0 ? 'stat-success' : 'stat-critico'}">${formatMoney(stats.saldo_mes)}</div>
                            <div class="stat-label">📊 Saldo del Mes</div>
                        </div>
                    </div>

                    <div class="movimientos-recientes">
                        <h2 class="movimientos-title">📜 Últimos 10 Movimientos</h2>
                        ${movimientosHTML}
                    </div>
                </div>
            `;

            mainContent.innerHTML = contentHTML;
        });
    </script>
</body>
</html>