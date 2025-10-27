<?php
include_once "db.php";

// ===== VERIFICAR ESTADO DE CAJA =====
$sentenciaCajaAbierta = $conexion->prepare("SELECT * FROM cierres_caja WHERE estado = 'ABIERTA' ORDER BY fecha_apertura DESC LIMIT 1");
$sentenciaCajaAbierta->execute();
$cajaAbierta = $sentenciaCajaAbierta->fetch(PDO::FETCH_OBJ);

// ===== ESTADÍSTICAS DE CAJA HOY =====
$hoy = date('Y-m-d');
$sentenciaHoy = $conexion->prepare("
    SELECT 
        SUM(CASE WHEN tipo_movimiento = 'INGRESO' THEN monto ELSE 0 END) as ingresos_hoy,
        SUM(CASE WHEN tipo_movimiento = 'EGRESO' THEN monto ELSE 0 END) as egresos_hoy
    FROM caja WHERE DATE(fecha_movimiento) = ?
");
$sentenciaHoy->execute([$hoy]);
$cajaHoy = $sentenciaHoy->fetch(PDO::FETCH_OBJ);

$ingresos_hoy = floatval($cajaHoy->ingresos_hoy ?? 0);
$egresos_hoy = floatval($cajaHoy->egresos_hoy ?? 0);
$saldo_hoy = $ingresos_hoy - $egresos_hoy;

// ===== SALDO TOTAL =====
$sentenciaTotal = $conexion->prepare("SELECT SUM(CASE WHEN tipo_movimiento = 'INGRESO' THEN monto ELSE -monto END) as saldo_total FROM caja");
$sentenciaTotal->execute();
$saldo_total = floatval($sentenciaTotal->fetchColumn() ?? 0);

// ===== PRODUCTOS BAJO STOCK =====
$sentenciaBajoStock = $conexion->prepare("
    SELECT COUNT(*) as total,
           SUM(CASE WHEN stock_actual = 0 THEN 1 ELSE 0 END) as criticos,
           SUM(CASE WHEN stock_actual > 0 AND stock_actual <= stock_minimo THEN 1 ELSE 0 END) as bajos
    FROM productos WHERE estado_producto = 1 AND stock_actual <= stock_minimo
");
$sentenciaBajoStock->execute();
$stockStats = $sentenciaBajoStock->fetch(PDO::FETCH_OBJ);

// ===== TOP 5 PRODUCTOS MÁS VENDIDOS =====
$sentenciaTopProductos = $conexion->prepare("
    SELECT dv.descripcion, SUM(dv.cantidad) as total_vendido, SUM(dv.subtotal) as ingresos
    FROM detalle_ventas dv
    JOIN ventas v ON dv.id_venta = v.id
    WHERE dv.tipo_item = 'PRODUCTO' AND v.estado_venta = 1
    GROUP BY dv.id_item, dv.descripcion
    ORDER BY total_vendido DESC LIMIT 5
");
$sentenciaTopProductos->execute();
$topProductos = $sentenciaTopProductos->fetchAll(PDO::FETCH_OBJ);

// ===== VENTAS DEL MES =====
$mes_actual = date('Y-m');
$sentenciaVentasMes = $conexion->prepare("
    SELECT COUNT(*) as total_ventas, SUM(total_venta) as ingresos_ventas
    FROM ventas WHERE DATE_FORMAT(fecha_venta, '%Y-%m') = ? AND estado_venta = 1
");
$sentenciaVentasMes->execute([$mes_actual]);
$ventasMes = $sentenciaVentasMes->fetch(PDO::FETCH_OBJ);

// Convertir a JSON
$dataJSON = json_encode([
    'caja_abierta' => $cajaAbierta ? true : false,
    'caja_info' => $cajaAbierta,
    'ingresos_hoy' => $ingresos_hoy,
    'egresos_hoy' => $egresos_hoy,
    'saldo_hoy' => $saldo_hoy,
    'saldo_total' => $saldo_total,
    'stock_criticos' => intval($stockStats->criticos ?? 0),
    'stock_bajos' => intval($stockStats->bajos ?? 0),
    'stock_total_problemas' => intval($stockStats->total ?? 0),
    'top_productos' => $topProductos,
    'ventas_mes' => intval($ventasMes->total_ventas ?? 0),
    'ingresos_mes' => floatval($ventasMes->ingresos_ventas ?? 0)
]);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Magister Office</title>
    <link href="css/bulma.min.css" rel="stylesheet">
    <link href="css/estadisticas.css" rel="stylesheet">
    <style>
        body { margin: 0; padding: 0; background: #2c3e50; }
        .main-content { background: #2c3e50 !important; color: white; padding: 20px; }
        .dashboard-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .dashboard-title {
            color: #f1c40f;
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        .dashboard-subtitle {
            color: rgba(255,255,255,0.9);
            font-size: 1.1rem;
        }
        .caja-alert {
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            text-align: center;
            font-weight: bold;
            font-size: 1.1rem;
        }
        .caja-cerrada {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            box-shadow: 0 5px 20px rgba(231, 76, 60, 0.4);
        }
        .caja-abierta {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            color: white;
            box-shadow: 0 5px 20px rgba(39, 174, 96, 0.4);
        }
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        .quick-btn {
            background: linear-gradient(45deg, #f39c12, #f1c40f);
            color: #2c3e50;
            font-weight: bold;
            padding: 20px;
            border-radius: 12px;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 15px rgba(243, 156, 18, 0.3);
        }
        .quick-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(243, 156, 18, 0.5);
        }
        .quick-btn-icon {
            font-size: 2.5rem;
        }
        .section-title {
            color: #f1c40f;
            font-size: 1.5rem;
            font-weight: bold;
            margin: 30px 0 15px 0;
            text-align: center;
        }
        .top-productos-list {
            background: rgba(0,0,0,0.2);
            border-radius: 12px;
            padding: 20px;
        }
        .producto-item {
            background: rgba(255,255,255,0.05);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
        }
        .producto-item:hover {
            background: rgba(241, 196, 15, 0.1);
            transform: translateX(5px);
        }
        .producto-nombre {
            font-weight: bold;
            color: #ecf0f1;
        }
        .producto-stats {
            color: rgba(255,255,255,0.7);
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <?php include 'menu.php'; ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mainContent = document.querySelector('.main-content');
            if (!mainContent) return console.error('No .main-content');

            const data = <?php echo $dataJSON; ?>;
            const formatMoney = (num) => '₲ ' + parseFloat(num).toLocaleString('es-PY', {minimumFractionDigits: 2});
            const fechaHoy = new Date().toLocaleDateString('es-PY', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });

            // Estado de caja
            let cajaAlertHTML = '';
            if (data.caja_abierta) {
                const fechaApertura = new Date(data.caja_info.fecha_apertura).toLocaleString('es-PY');
                cajaAlertHTML = `
                    <div class="caja-alert caja-abierta">
                        ✅ CAJA ABIERTA desde ${fechaApertura}
                        <br><small style="font-size: 0.9rem; font-weight: normal;">Saldo inicial: ${formatMoney(data.caja_info.saldo_inicial)}</small>
                    </div>
                `;
            } else {
                cajaAlertHTML = `
                    <div class="caja-alert caja-cerrada">
                        ⚠️ CAJA CERRADA - Debes abrir caja para operar
                        <br><a href="caja/abrir_caja.php" style="color: white; text-decoration: underline; font-size: 0.95rem;">Clic aquí para abrir caja</a>
                    </div>
                `;
            }

            // Top productos
            let topProductosHTML = '';
            if (data.top_productos.length > 0) {
                data.top_productos.forEach((prod, idx) => {
                    const medalla = idx === 0 ? '🥇' : idx === 1 ? '🥈' : idx === 2 ? '🥉' : '📦';
                    topProductosHTML += `
                        <div class="producto-item">
                            <div>
                                <div class="producto-nombre">${medalla} ${prod.descripcion}</div>
                                <div class="producto-stats">Vendido: ${prod.total_vendido} unidades | Ingresos: ${formatMoney(prod.ingresos)}</div>
                            </div>
                        </div>
                    `;
                });
            } else {
                topProductosHTML = '<div style="text-align: center; color: rgba(255,255,255,0.6); padding: 20px;">No hay ventas registradas</div>';
            }

            const contentHTML = `
                <div class="dashboard-header">
                    <h1 class="dashboard-title">Magister Office</h1>
                    <p class="dashboard-subtitle">${fechaHoy}</p>
                </div>

                ${cajaAlertHTML}

                <div class="quick-actions">
                    <a href="ventas/frm_registrar_venta.php" class="quick-btn">
                        <div class="quick-btn-icon">💰</div>
                        <div>Nueva Venta</div>
                    </a>
                    <a href="caja/balance.php" class="quick-btn">
                        <div class="quick-btn-icon">📊</div>
                        <div>Ver Caja</div>
                    </a>
                    <a href="productos/listado_producto.php" class="quick-btn">
                        <div class="quick-btn-icon">📦</div>
                        <div>Productos</div>
                    </a>
                    <a href="compras/frm_registrar_compra.php" class="quick-btn">
                        <div class="quick-btn-icon">🛒</div>
                        <div>Nueva Compra</div>
                    </a>
                </div>

                <h2 class="section-title">💵 Resumen de Caja Hoy</h2>
                <div class="stats-container">
                    <div class="stat-card">
                        <div class="stat-number stat-success">${formatMoney(data.ingresos_hoy)}</div>
                        <div class="stat-label">💰 Ingresos Hoy</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number stat-warning">${formatMoney(data.egresos_hoy)}</div>
                        <div class="stat-label">💸 Egresos Hoy</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number ${data.saldo_hoy >= 0 ? 'stat-success' : 'stat-critico'}">${formatMoney(data.saldo_hoy)}</div>
                        <div class="stat-label">📊 Saldo Hoy</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number stat-primary">${formatMoney(data.saldo_total)}</div>
                        <div class="stat-label">💎 Saldo Total</div>
                    </div>
                </div>

                <h2 class="section-title">📈 Ventas del Mes</h2>
                <div class="stats-container">
                    <div class="stat-card">
                        <div class="stat-number stat-info">${data.ventas_mes}</div>
                        <div class="stat-label">🛍️ Total Ventas</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number stat-success">${formatMoney(data.ingresos_mes)}</div>
                        <div class="stat-label">💰 Ingresos Totales</div>
                    </div>
                </div>

                <h2 class="section-title">⚠️ Alertas de Inventario</h2>
                <div class="stats-container">
                    <div class="stat-card">
                        <div class="stat-number stat-critico">${data.stock_criticos}</div>
                        <div class="stat-label">🔴 SIN STOCK</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number stat-warning">${data.stock_bajos}</div>
                        <div class="stat-label">⚠️ STOCK BAJO</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number stat-info">${data.stock_total_problemas}</div>
                        <div class="stat-label">📦 TOTAL ALERTAS</div>
                    </div>
                    <div class="stat-card" style="display: flex; align-items: center; justify-content: center;">
                        <a href="productos/listado_producto.php" style="color: #f1c40f; text-decoration: none; font-weight: bold;">Ver Productos →</a>
                    </div>
                </div>

                <h2 class="section-title">🏆 Top 5 Productos Más Vendidos</h2>
                <div class="top-productos-list">
                    ${topProductosHTML}
                </div>
            `;

            mainContent.innerHTML = contentHTML;
        });
    </script>
</body>
</html>