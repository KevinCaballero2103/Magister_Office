<?php
include_once __DIR__ . "/../auth.php";
include_once "../db.php";

// Registrar acceso
registrarActividad('ACCESO', 'CUENTAS_CORRIENTES', 'Acceso a listado de deudas', null, null);

// Filtros
$estado = isset($_GET['estado']) ? $_GET['estado'] : 'con_deuda';

// Obtener resumen de cuentas corrientes por cliente
$condicion = "";
if ($estado === 'con_deuda') {
    $condicion = "HAVING saldo_actual > 0";
} elseif ($estado === 'a_favor') {
    $condicion = "HAVING saldo_actual < 0";
} elseif ($estado === 'saldado') {
    $condicion = "HAVING saldo_actual = 0";
}

$sentencia = $conexion->prepare("
    SELECT 
        c.id as id_cliente,
        CONCAT(c.nombre_cliente, ' ', c.apellido_cliente) as nombre_completo,
        c.telefono_cliente,
        c.ci_ruc_cliente,
        COALESCE(SUM(CASE WHEN cc.tipo_movimiento = 'DEBITO' THEN cc.monto ELSE -cc.monto END), 0) as saldo_actual,
        MAX(cc.fecha_registro) as ultima_operacion,
        COUNT(DISTINCT cc.id_venta) as ventas_fiadas,
        COUNT(CASE WHEN cc.tipo_movimiento = 'CREDITO' THEN 1 END) as cantidad_pagos
    FROM clientes c
    LEFT JOIN cuentas_corrientes cc ON c.id = cc.id_cliente
    WHERE c.estado_cliente = 1
    GROUP BY c.id, c.nombre_cliente, c.apellido_cliente, c.telefono_cliente, c.ci_ruc_cliente
    $condicion
    ORDER BY saldo_actual DESC, c.nombre_cliente ASC
");
$sentencia->execute();
$cuentas = $sentencia->fetchAll(PDO::FETCH_OBJ);

// Estadísticas generales
$sentenciaStats = $conexion->prepare("
    SELECT 
        COUNT(DISTINCT id_cliente) as total_clientes,
        SUM(CASE WHEN tipo_movimiento = 'DEBITO' THEN monto ELSE 0 END) as total_debitos,
        SUM(CASE WHEN tipo_movimiento = 'CREDITO' THEN monto ELSE 0 END) as total_creditos,
        SUM(CASE WHEN tipo_movimiento = 'DEBITO' THEN monto ELSE -monto END) as saldo_total
    FROM cuentas_corrientes
");
$sentenciaStats->execute();
$stats = $sentenciaStats->fetch(PDO::FETCH_OBJ);

$cuentasJSON = json_encode($cuentas);
$statsJSON = json_encode($stats);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuentas Corrientes - Deudas</title>
    <link href="../css/bulma.min.css" rel="stylesheet">
    <link href="../css/listados.css" rel="stylesheet">
    <link href="../css/estadisticas.css" rel="stylesheet">
    <style>
        .main-content { background: #2c3e50 !important; color: white; }
        .saldo-positivo { color: #e74c3c; font-weight: bold; }
        .saldo-negativo { color: #27ae60; font-weight: bold; }
        .saldo-cero { color: #95a5a6; font-weight: bold; }
        .btn-ver-cuenta {
            background: linear-gradient(45deg, #3498db, #2980b9) !important;
            color: white !important;
            padding: 5px 12px;
            border-radius: 6px;
            text-decoration: none !important;
            font-weight: bold;
            font-size: 0.75rem;
            transition: all 0.3s ease;
            display: inline-block;
        }
        .btn-ver-cuenta:hover {
            background: linear-gradient(45deg, #2980b9, #3498db) !important;
            transform: translateY(-2px);
            color: white !important;
        }
        .btn-cobrar {
            background: linear-gradient(45deg, #27ae60, #2ecc71) !important;
            color: white !important;
            padding: 5px 12px;
            border-radius: 6px;
            text-decoration: none !important;
            font-weight: bold;
            font-size: 0.75rem;
            transition: all 0.3s ease;
            display: inline-block;
            margin-left: 5px;
        }
        .btn-cobrar:hover {
            background: linear-gradient(45deg, #2ecc71, #27ae60) !important;
            transform: translateY(-2px);
            color: white !important;
        }
    </style>
</head>
<body>
    <?php include '../menu.php'; ?>
    
    <script>
        const cuentas = <?php echo $cuentasJSON; ?>;
        const stats = <?php echo $statsJSON; ?>;
        
        const formatMoney = (num) => '₲ ' + parseFloat(num).toLocaleString('es-PY', {minimumFractionDigits: 2});
        const formatDate = (dateStr) => {
            if (!dateStr) return 'Sin operaciones';
            const d = new Date(dateStr);
            return d.toLocaleString('es-PY', { 
                day: '2-digit', 
                month: '2-digit', 
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        };
        
        document.addEventListener('DOMContentLoaded', function() {
            const mainContent = document.querySelector('.main-content');
            if (!mainContent) return;
            
            let filasHTML = '';
            if (cuentas.length > 0) {
                cuentas.forEach(cuenta => {
                    const saldo = parseFloat(cuenta.saldo_actual);
                    let claseEstado = '', textoEstado = '', colorSaldo = '';
                    
                    if (saldo > 0) {
                        claseEstado = 'status-inactive';
                        textoEstado = '⚠️ DEBE';
                        colorSaldo = 'saldo-positivo';
                    } else if (saldo < 0) {
                        claseEstado = 'status-active';
                        textoEstado = '✓ A FAVOR';
                        colorSaldo = 'saldo-negativo';
                    } else {
                        claseEstado = 'status-active';
                        textoEstado = '✓ SALDADO';
                        colorSaldo = 'saldo-cero';
                    }
                    
                    const accionesHTML = saldo > 0 
                        ? `<a href="./detalle_cuenta.php?id=${cuenta.id_cliente}" class="btn-ver-cuenta">👁️ Ver Cuenta</a>
                           <a href="./registrar_pago.php?id=${cuenta.id_cliente}" class="btn-cobrar">💰 Cobrar</a>`
                        : `<a href="./detalle_cuenta.php?id=${cuenta.id_cliente}" class="btn-ver-cuenta">👁️ Ver Cuenta</a>`;
                    
                    filasHTML += `
                        <tr>
                            <td><strong>${cuenta.nombre_completo}</strong></td>
                            <td>${cuenta.ci_ruc_cliente || 'S/N'}</td>
                            <td>${cuenta.telefono_cliente || '-'}</td>
                            <td><span class="${colorSaldo}">${formatMoney(Math.abs(saldo))}</span></td>
                            <td><span class="${claseEstado}">${textoEstado}</span></td>
                            <td>${cuenta.ventas_fiadas || 0}</td>
                            <td>${cuenta.cantidad_pagos || 0}</td>
                            <td style="font-size: 0.75rem;">${formatDate(cuenta.ultima_operacion)}</td>
                            <td>${accionesHTML}</td>
                        </tr>
                    `;
                });
            } else {
                filasHTML = '<tr><td colspan="9" class="no-results">No hay cuentas corrientes con los filtros seleccionados</td></tr>';
            }
            
            const saldoTotal = parseFloat(stats.saldo_total || 0);
            const colorSaldoTotal = saldoTotal > 0 ? 'stat-critico' : (saldoTotal < 0 ? 'stat-success' : 'stat-info');
            
            const contentHTML = `
                <div class="list-container">
                    <h1 class="list-title">💳 Cuentas Corrientes - Gestión de Deudas</h1>
                    
                    <div class="stats-container" style="margin-bottom: 30px;">
                        <div class="stat-card">
                            <div class="stat-number stat-info">${stats.total_clientes || 0}</div>
                            <div class="stat-label">👥 Clientes con Cuenta</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number stat-warning">${formatMoney(stats.total_debitos || 0)}</div>
                            <div class="stat-label">📤 Total Vendido Fiado</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number stat-success">${formatMoney(stats.total_creditos || 0)}</div>
                            <div class="stat-label">📥 Total Cobrado</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number ${colorSaldoTotal}">${formatMoney(Math.abs(saldoTotal))}</div>
                            <div class="stat-label">${saldoTotal > 0 ? '⚠️ Pendiente de Cobro' : (saldoTotal < 0 ? '✓ A Favor de Clientes' : '✓ Todo Saldado')}</div>
                        </div>
                    </div>
                    
                    <div class="filter-container">
                        <form method="GET" action="">
                            <label class="label">Filtrar Cuentas</label>
                            <div class="search-controls">
                                <div class="search-field">
                                    <label>Estado:</label>
                                    <div class="select">
                                        <select name="estado" class="search-input">
                                            <option value="todos" ${<?php echo $estado === 'todos' ? 'true' : 'false'; ?> ? 'selected' : ''}>-- TODOS --</option>
                                            <option value="con_deuda" ${<?php echo $estado === 'con_deuda' ? 'true' : 'false'; ?> ? 'selected' : ''}>⚠️ CON DEUDA</option>
                                            <option value="a_favor" ${<?php echo $estado === 'a_favor' ? 'true' : 'false'; ?> ? 'selected' : ''}>✓ A FAVOR</option>
                                            <option value="saldado" ${<?php echo $estado === 'saldado' ? 'true' : 'false'; ?> ? 'selected' : ''}>✓ SALDADO</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="search-field">
                                    <button type="submit" class="button" style="margin-top: 22px;">🔍 Filtrar</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <div style="overflow-x: auto;">
                        <table class="table is-fullwidth custom-table">
                            <thead>
                                <tr>
                                    <th>CLIENTE</th>
                                    <th>CI/RUC</th>
                                    <th>TELÉFONO</th>
                                    <th>SALDO</th>
                                    <th>ESTADO</th>
                                    <th>VENTAS FIADAS</th>
                                    <th>PAGOS</th>
                                    <th>ÚLTIMA OPERACIÓN</th>
                                    <th>ACCIONES</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${filasHTML}
                            </tbody>
                        </table>
                    </div>
                    
                    <div style="text-align: center; margin-top: 25px;">
                        <a href="../ventas/frm_registrar_venta.php" class="button">💰 Nueva Venta</a>
                        <a href="../index.php" class="secondary-button">🏠 Inicio</a>
                    </div>
                </div>
            `;
            
            mainContent.innerHTML = contentHTML;
        });
    </script>
</body>
</html>