<?php
include_once __DIR__ . "/../auth.php";
if (!tienePermiso(['ADMINISTRADOR', 'CAJERO'])) {
    header("Location: ../index.php?error=sin_permisos");
    exit();
}
include_once "../db.php";
$cajaAbierta = requiereCajaAbierta();

$puedeModificar = tienePermiso(['ADMINISTRADOR', 'CAJERO']);

registrarActividad('ACCESO', 'VENTAS', 'Acceso a gestión de cuotas', null, null);

// Filtros
$estado_cuota = isset($_GET['estado']) ? $_GET['estado'] : 'PENDIENTE';
$cliente_filtro = isset($_GET['cliente']) ? $_GET['cliente'] : 'todos';

$condiciones = ["v.condicion_venta = 'CREDITO'"];

if ($estado_cuota !== 'TODAS') {
    $condiciones[] = "c.estado = '$estado_cuota'";
}

if ($cliente_filtro !== 'todos') {
    $condiciones[] = "v.id_cliente = " . intval($cliente_filtro);
}

$where_clause = "WHERE " . implode(" AND ", $condiciones);

// Consulta principal
$sentencia = $conexion->prepare("
    SELECT 
        c.id,
        c.id_venta,
        c.numero,
        c.monto,
        c.fecha_vencimiento,
        c.estado,
        c.fecha_pago,
        v.numero_venta,
        v.total_venta,
        v.fecha_venta,
        CONCAT(COALESCE(cli.nombre_cliente, ''), ' ', COALESCE(cli.apellido_cliente, '')) as nombre_cliente,
        cli.telefono_cliente,
        DATEDIFF(c.fecha_vencimiento, CURDATE()) as dias_hasta_vencimiento,
        (SELECT COUNT(*) FROM cuotas_venta WHERE id_venta = c.id_venta) as total_cuotas,
        (SELECT COUNT(*) FROM cuotas_venta WHERE id_venta = c.id_venta AND estado = 'PAGADA') as cuotas_pagadas
    FROM cuotas_venta c
    INNER JOIN ventas v ON c.id_venta = v.id
    LEFT JOIN clientes cli ON v.id_cliente = cli.id
    $where_clause
    ORDER BY c.fecha_vencimiento ASC, c.id ASC
");
$sentencia->execute();
$cuotas = $sentencia->fetchAll(PDO::FETCH_OBJ);

// Obtener clientes con ventas a crédito
$sentenciaClientes = $conexion->prepare("
    SELECT DISTINCT cli.id, cli.nombre_cliente, cli.apellido_cliente
    FROM clientes cli
    INNER JOIN ventas v ON cli.id = v.id_cliente
    WHERE v.condicion_venta = 'CREDITO'
    ORDER BY cli.nombre_cliente ASC
");
$sentenciaClientes->execute();
$clientes = $sentenciaClientes->fetchAll(PDO::FETCH_OBJ);

// Estadísticas
$sentenciaStats = $conexion->prepare("
    SELECT 
        COUNT(*) as total_cuotas,
        SUM(CASE WHEN estado = 'PENDIENTE' THEN 1 ELSE 0 END) as pendientes,
        SUM(CASE WHEN estado = 'PAGADA' THEN 1 ELSE 0 END) as pagadas,
        SUM(CASE WHEN estado = 'PENDIENTE' THEN monto ELSE 0 END) as monto_pendiente,
        SUM(CASE WHEN estado = 'PAGADA' THEN monto ELSE 0 END) as monto_cobrado
    FROM cuotas_venta c
    INNER JOIN ventas v ON c.id_venta = v.id
    WHERE v.estado_venta = 1
");
$sentenciaStats->execute();
$stats = $sentenciaStats->fetch(PDO::FETCH_OBJ);

$cuotasJSON = json_encode($cuotas);
$clientesJSON = json_encode($clientes);
$statsJSON = json_encode($stats);
$puedeModificarJSON = $puedeModificar ? 'true' : 'false';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Cuotas</title>
    <link href="../css/bulma.min.css" rel="stylesheet">
    <link href="../css/listados.css" rel="stylesheet">
    <link href="../css/estadisticas.css" rel="stylesheet">
    <!-- 🔗 Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    
    <style>
        .main-content { background: #2c3e50 !important; color: white; }
        .cuota-pendiente { background: rgba(230, 126, 34, 0.1) !important; }
        .cuota-pagada { background: rgba(39, 174, 96, 0.1) !important; }
        .cuota-vencida { background: rgba(231, 76, 60, 0.15) !important; border-left: 4px solid #e74c3c !important; }
        .estado-badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: bold;
            display: inline-block;
        }
        .badge-pendiente { background: linear-gradient(45deg, #f39c12, #f1c40f); color: #2c3e50; }
        .badge-pagada { background: linear-gradient(45deg, #27ae60, #2ecc71); color: white; }
        .badge-vencida { background: linear-gradient(45deg, #e74c3c, #c0392b); color: white; animation: pulse 2s infinite; }
        @keyframes pulse { 0%,100%{opacity:1;} 50%{opacity:0.7;} }
        .btn-cobrar, .btn-imprimir, .btn-pagare {
            color: white !important;
            padding: 5px 12px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-weight: bold;
            font-size: 0.75rem;
            transition: all 0.3s ease;
            text-decoration: none !important;
            display: inline-block;
            margin: 2px;
        }
        .btn-cobrar { background: linear-gradient(45deg, #27ae60, #2ecc71) !important; }
        .btn-cobrar:hover { background: linear-gradient(45deg, #2ecc71, #27ae60) !important; transform: translateY(-2px); color: white !important; }
        .btn-cobrar:disabled { background: rgba(128,128,128,0.3) !important; cursor: not-allowed; opacity: 0.5; }
        .btn-imprimir { background: linear-gradient(45deg, #3498db, #2980b9) !important; }
        .btn-imprimir:hover { background: linear-gradient(45deg, #2980b9, #3498db) !important; transform: translateY(-2px); color: white !important; }
        .btn-pagare { background: linear-gradient(45deg, #9b59b6, #8e44ad) !important; }
        .btn-pagare:hover { background: linear-gradient(45deg, #8e44ad, #9b59b6) !important; transform: translateY(-2px); color: white !important; }
        .progreso-cuotas { background: rgba(0,0,0,0.2); padding: 8px; border-radius: 6px; font-size: 0.75rem; }
        .barra-progreso { background: rgba(255,255,255,0.1); height: 8px; border-radius: 4px; overflow: hidden; margin-top: 5px; }
        .barra-progreso-fill { background: linear-gradient(45deg, #27ae60, #2ecc71); height: 100%; transition: width 0.3s ease; }
    </style>
</head>
<body>
    <?php include '../menu.php'; ?>

    <script>
        const cuotas = <?php echo $cuotasJSON; ?>;
        const clientes = <?php echo $clientesJSON; ?>;
        const stats = <?php echo $statsJSON; ?>;
        const puedeModificar = <?php echo $puedeModificarJSON; ?>;
        
        const formatMoney = (num) => '₲ ' + parseFloat(num).toLocaleString('es-PY', {minimumFractionDigits: 2});
        const formatDate = (dateStr) => {
            const d = new Date(dateStr + 'T00:00:00');
            return d.toLocaleDateString('es-PY', { day: '2-digit', month: '2-digit', year: 'numeric' });
        };
        
        document.addEventListener('DOMContentLoaded', function() {
            const mainContent = document.querySelector('.main-content');
            if (!mainContent) return;
            
            let clientesOptions = '<option value="todos">-- TODOS LOS CLIENTES --</option>';
            clientes.forEach(cli => {
                const selected = '<?php echo $cliente_filtro; ?>' == cli.id ? 'selected' : '';
                clientesOptions += `<option value="${cli.id}" ${selected}>${cli.nombre_cliente} ${cli.apellido_cliente}</option>`;
            });
            
            let cuotasHTML = '';
            if (cuotas.length > 0) {
                cuotas.forEach(cuota => {
                    const hoy = new Date();
                    const vencimiento = new Date(cuota.fecha_vencimiento + 'T00:00:00');
                    const estaVencida = cuota.estado === 'PENDIENTE' && vencimiento < hoy;
                    
                    let claseEstado = cuota.estado === 'PAGADA' ? 'badge-pagada' : (estaVencida ? 'badge-vencida' : 'badge-pendiente');
                    let textoEstado = cuota.estado === 'PAGADA' 
                        ? '<i class="fa-solid fa-check"></i> PAGADA' 
                        : (estaVencida ? '<i class="fa-solid fa-triangle-exclamation"></i> VENCIDA' 
                        : '<i class="fa-solid fa-hourglass-half"></i> PENDIENTE');
                    let claseRow = cuota.estado === 'PAGADA' ? 'cuota-pagada' : (estaVencida ? 'cuota-vencida' : 'cuota-pendiente');
                    
                    const progreso = Math.round((cuota.cuotas_pagadas / cuota.total_cuotas) * 100);
                    const progresoHTML = `
                        <div class="progreso-cuotas">
                            ${cuota.cuotas_pagadas}/${cuota.total_cuotas} cuotas pagadas (${progreso}%)
                            <div class="barra-progreso">
                                <div class="barra-progreso-fill" style="width: ${progreso}%"></div>
                            </div>
                        </div>
                    `;
                    
                    let accionHTML = '';
                    if (cuota.estado === 'PENDIENTE') {
                        if (puedeModificar) {
                            accionHTML = `<a href="./cobrar_cuota.php?id=${cuota.id}" class="btn-cobrar">
                                <i class="fa-solid fa-hand-holding-dollar"></i> COBRAR
                            </a>`;
                        } else {
                            accionHTML = '<span style="color: rgba(255,255,255,0.5); font-size: 0.75rem;">Sin permisos</span>';
                        }
                    } else {
                        accionHTML = `<button class="btn-imprimir" onclick="imprimirReciboCuota(${cuota.id})">
                            <i class="fa-solid fa-print"></i> Recibo
                        </button>`;
                        
                        if (cuota.cuotas_pagadas === cuota.total_cuotas) {
                            accionHTML += `<button class="btn-pagare" onclick="imprimirPagare(${cuota.id_venta})">
                                <i class="fa-regular fa-file-lines"></i> Pagaré
                            </button>`;
                        }
                        accionHTML += `<br><small style="color: #27ae60;">Pagada: ${formatDate(cuota.fecha_pago)}</small>`;
                    }
                    
                    const diasInfo = cuota.estado === 'PENDIENTE' ? 
                        (cuota.dias_hasta_vencimiento < 0 ? 
                            `<span style="color: #e74c3c;">Vencida hace ${Math.abs(cuota.dias_hasta_vencimiento)} días</span>` :
                            `<span style="color: #f39c12;">Vence en ${cuota.dias_hasta_vencimiento} días</span>`) : '';
                    
                    cuotasHTML += `
                        <tr class="${claseRow}">
                            <td><strong>Venta #${cuota.id_venta}</strong><br><small>${cuota.numero_venta || 'S/N'}</small></td>
                            <td>${cuota.nombre_cliente || 'Cliente Genérico'}<br>${cuota.telefono_cliente ? '<small><i class="fa-solid fa-phone"></i> ' + cuota.telefono_cliente + '</small>' : ''}</td>
                            <td><strong>Cuota ${cuota.numero}/${cuota.total_cuotas}</strong>${progresoHTML}</td>
                            <td><strong>${formatMoney(cuota.monto)}</strong></td>
                            <td>${formatDate(cuota.fecha_vencimiento)}<br>${diasInfo}</td>
                            <td><span class="estado-badge ${claseEstado}">${textoEstado}</span></td>
                            <td>${accionHTML}</td>
                        </tr>
                    `;
                });
            } else {
                cuotasHTML = '<tr><td colspan="7" class="no-results">No hay cuotas con los filtros seleccionados</td></tr>';
            }
            
            const contentHTML = `
                <div class="list-container">
                    <h1 class="list-title"><i class="fa-solid fa-credit-card"></i> Gestión de Cuotas de Crédito</h1>
                    
                    <div class="stats-container" style="margin-bottom: 30px;">
                        <div class="stat-card">
                            <div class="stat-number stat-info">${stats.total_cuotas || 0}</div>
                            <div class="stat-label"><i class="fa-solid fa-clipboard-list"></i> Total Cuotas</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number stat-warning">${stats.pendientes || 0}</div>
                            <div class="stat-label"><i class="fa-solid fa-hourglass-half"></i> Pendientes</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number stat-success">${stats.pagadas || 0}</div>
                            <div class="stat-label"><i class="fa-solid fa-check"></i> Pagadas</div>
                        </div>
                    </div>
                    
                    <div class="stats-container" style="margin-bottom: 30px;">
                        <div class="stat-card">
                            <div class="stat-number stat-warning">${formatMoney(stats.monto_pendiente || 0)}</div>
                            <div class="stat-label"><i class="fa-solid fa-sack-dollar"></i> Por Cobrar</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number stat-success">${formatMoney(stats.monto_cobrado || 0)}</div>
                            <div class="stat-label"><i class="fa-solid fa-check"></i> Cobrado</div>
                        </div>
                    </div>
                    
                    <div class="filter-container">
                        <form method="GET" action="">
                            <label class="label">Filtrar Cuotas</label>
                            <div class="search-controls">
                                <div class="search-field">
                                    <label>Cliente:</label>
                                    <div class="select">
                                        <select name="cliente" class="search-input">
                                            ${clientesOptions}
                                        </select>
                                    </div>
                                </div>
                                <div class="search-field">
                                    <label>Estado:</label>
                                    <div class="select">
                                        <select name="estado" class="search-input">
                                            <option value="PENDIENTE" ${"<?php echo $estado_cuota === 'PENDIENTE' ? 'selected' : ''; ?>"}><i class="fa-solid fa-hourglass-half"></i> PENDIENTE</option>
                                            <option value="PAGADA" ${"<?php echo $estado_cuota === 'PAGADA' ? 'selected' : ''; ?>"}><i class="fa-solid fa-check"></i> PAGADA</option>
                                            <option value="TODAS" ${"<?php echo $estado_cuota === 'TODAS' ? 'selected' : ''; ?>"}>-- TODAS --</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="search-field">
                                    <button type="submit" class="button" style="margin-top: 22px;">
                                        <i class="fa-solid fa-magnifying-glass"></i> Filtrar
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <div style="overflow-x: auto;">
                        <table class="table is-fullwidth custom-table">
                            <thead>
                                <tr>
                                    <th>VENTA</th>
                                    <th>CLIENTE</th>
                                    <th>CUOTA</th>
                                    <th>MONTO</th>
                                    <th>VENCIMIENTO</th>
                                    <th>ESTADO</th>
                                    <th>ACCIONES</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${cuotasHTML}
                            </tbody>
                        </table>
                    </div>
                    
                    <div style="text-align: center; margin-top: 25px;">
                        <a href="./listado_ventas.php" class="button"><i class="fa-solid fa-clipboard-list"></i> Ver Ventas</a>
                        <a href="../index.php" class="secondary-button"><i class="fa-solid fa-house"></i> Inicio</a>
                    </div>
                </div>
            `;
            
            mainContent.innerHTML = contentHTML;
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
