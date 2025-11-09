<?php
include_once __DIR__ . "/../auth.php";
include_once "../db.php";

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: listado_deudas.php?error=id_requerido");
    exit();
}

$id_cliente = intval($_GET['id']);

// Obtener datos del cliente
$sentenciaCliente = $conexion->prepare("
    SELECT * FROM clientes WHERE id = ?
");
$sentenciaCliente->execute([$id_cliente]);
$cliente = $sentenciaCliente->fetch(PDO::FETCH_OBJ);

if (!$cliente) {
    header("Location: listado_deudas.php?error=cliente_no_encontrado");
    exit();
}

registrarActividad('ACCESO', 'CUENTAS_CORRIENTES', "Acceso a cuenta corriente del cliente: {$cliente->nombre_cliente} {$cliente->apellido_cliente}", null, null);

// Obtener todos los movimientos de la cuenta corriente
$sentenciaMovimientos = $conexion->prepare("
    SELECT 
        cc.*,
        v.numero_venta,
        v.total_venta,
        v.fecha_venta
    FROM cuentas_corrientes cc
    LEFT JOIN ventas v ON cc.id_venta = v.id
    WHERE cc.id_cliente = ?
    ORDER BY cc.fecha_registro DESC
");
$sentenciaMovimientos->execute([$id_cliente]);
$movimientos = $sentenciaMovimientos->fetchAll(PDO::FETCH_OBJ);

// Calcular saldo actual
$saldo_actual = 0;
foreach ($movimientos as $mov) {
    if ($mov->tipo_movimiento === 'DEBITO') {
        $saldo_actual += floatval($mov->monto);
    } else {
        $saldo_actual -= floatval($mov->monto);
    }
}

// Estadísticas del cliente
$total_debitos = 0;
$total_creditos = 0;
$count_ventas = 0;
$count_pagos = 0;

foreach ($movimientos as $mov) {
    if ($mov->tipo_movimiento === 'DEBITO') {
        $total_debitos += floatval($mov->monto);
        if ($mov->id_venta) $count_ventas++;
    } else {
        $total_creditos += floatval($mov->monto);
        $count_pagos++;
    }
}

$clienteJSON = json_encode($cliente);
$movimientosJSON = json_encode($movimientos);
$statsJSON = json_encode([
    'saldo_actual' => $saldo_actual,
    'total_debitos' => $total_debitos,
    'total_creditos' => $total_creditos,
    'count_ventas' => $count_ventas,
    'count_pagos' => $count_pagos
]);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuenta Corriente - <?php echo htmlspecialchars($cliente->nombre_cliente . ' ' . $cliente->apellido_cliente); ?></title>
    <link href="../css/bulma.min.css" rel="stylesheet">
    <link href="../css/listados.css" rel="stylesheet">
    <link href="../css/estadisticas.css" rel="stylesheet">
    <style>
        .main-content { background: #2c3e50 !important; color: white; }
        .cliente-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
        }
        .cliente-nombre {
            color: #f1c40f;
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 8px;
        }
        .cliente-info {
            color: rgba(255,255,255,0.9);
            font-size: 0.95rem;
            margin: 3px 0;
        }
        .saldo-card {
            padding: 25px;
            border-radius: 12px;
            text-align: center;
            margin-bottom: 25px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
        }
        .saldo-debe {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        }
        .saldo-favor {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
        }
        .saldo-saldado {
            background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%);
        }
        .saldo-label {
            color: rgba(255,255,255,0.9);
            font-size: 1rem;
            margin-bottom: 10px;
        }
        .saldo-monto {
            color: white;
            font-size: 3rem;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        .mov-debito {
            background: rgba(231, 76, 60, 0.1) !important;
            border-left: 4px solid #e74c3c !important;
        }
        .mov-credito {
            background: rgba(39, 174, 96, 0.1) !important;
            border-left: 4px solid #27ae60 !important;
        }
        .tipo-badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: bold;
            display: inline-block;
        }
        .badge-debito {
            background: linear-gradient(45deg, #e74c3c, #c0392b);
            color: white;
        }
        .badge-credito {
            background: linear-gradient(45deg, #27ae60, #2ecc71);
            color: white;
        }
        .btn-cobrar {
            background: linear-gradient(45deg, #27ae60, #2ecc71) !important;
            color: white !important;
            padding: 12px 24px !important;
            border-radius: 8px !important;
            text-decoration: none !important;
            font-weight: bold !important;
            transition: all 0.3s ease;
            display: inline-block;
            border: none !important;
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
        const cliente = <?php echo $clienteJSON; ?>;
        const movimientos = <?php echo $movimientosJSON; ?>;
        const stats = <?php echo $statsJSON; ?>;
        
        const formatMoney = (num) => '₲ ' + parseFloat(num).toLocaleString('es-PY', {minimumFractionDigits: 2});
        const formatDate = (dateStr) => {
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
            
            const saldo = parseFloat(stats.saldo_actual);
            let claseSaldo = '', textoEstado = '';
            
            if (saldo > 0) {
                claseSaldo = 'saldo-debe';
                textoEstado = '⚠️ CLIENTE DEBE';
            } else if (saldo < 0) {
                claseSaldo = 'saldo-favor';
                textoEstado = '✓ SALDO A FAVOR DEL CLIENTE';
            } else {
                claseSaldo = 'saldo-saldado';
                textoEstado = '✓ CUENTA SALDADA';
            }
            
            let movimientosHTML = '';
            let saldoAcumulado = 0;
            
            // Mostrar desde el más antiguo al más reciente para calcular saldo acumulado
            const movsReversed = [...movimientos].reverse();
            
            movsReversed.forEach(mov => {
                const monto = parseFloat(mov.monto);
                
                if (mov.tipo_movimiento === 'DEBITO') {
                    saldoAcumulado += monto;
                } else {
                    saldoAcumulado -= monto;
                }
                
                const claseRow = mov.tipo_movimiento === 'DEBITO' ? 'mov-debito' : 'mov-credito';
                const tipoBadge = mov.tipo_movimiento === 'DEBITO' 
                    ? '<span class="tipo-badge badge-debito">📤 DEBE</span>'
                    : '<span class="tipo-badge badge-credito">📥 PAGO</span>';
                
                const descripcion = mov.descripcion || (mov.id_venta 
                    ? `Venta #${mov.id_venta} ${mov.numero_venta ? '(' + mov.numero_venta + ')' : ''}`
                    : 'Movimiento manual');
                
                movimientosHTML = `
                    <tr class="${claseRow}">
                        <td>${formatDate(mov.fecha_registro)}</td>
                        <td>${tipoBadge}</td>
                        <td style="text-align: left;">${descripcion}</td>
                        <td><strong>${formatMoney(monto)}</strong></td>
                        <td><strong>${formatMoney(saldoAcumulado)}</strong></td>
                    </tr>
                ` + movimientosHTML;
            });
            
            if (movimientos.length === 0) {
                movimientosHTML = '<tr><td colspan="5" class="no-results">Sin movimientos registrados</td></tr>';
            }
            
            const botonCobrar = saldo > 0 
                ? `<a href="./registrar_pago.php?id=${cliente.id}" class="btn-cobrar">💰 REGISTRAR PAGO</a>`
                : '';
            
            const contentHTML = `
                <div class="list-container">
                    <div class="cliente-header">
                        <div class="cliente-nombre">👤 ${cliente.nombre_cliente} ${cliente.apellido_cliente}</div>
                        <div class="cliente-info">📄 CI/RUC: ${cliente.ci_ruc_cliente || 'No especificado'}</div>
                        <div class="cliente-info">📞 Teléfono: ${cliente.telefono_cliente || 'No especificado'}</div>
                        <div class="cliente-info">📍 Dirección: ${cliente.direccion_cliente || 'No especificada'}</div>
                    </div>
                    
                    <div class="saldo-card ${claseSaldo}">
                        <div class="saldo-label">${textoEstado}</div>
                        <div class="saldo-monto">${formatMoney(Math.abs(saldo))}</div>
                    </div>
                    
                    <div class="stats-container" style="margin-bottom: 30px;">
                        <div class="stat-card">
                            <div class="stat-number stat-warning">${stats.count_ventas}</div>
                            <div class="stat-label">📤 Ventas Fiadas</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number stat-success">${stats.count_pagos}</div>
                            <div class="stat-label">📥 Pagos Realizados</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number stat-warning">${formatMoney(stats.total_debitos)}</div>
                            <div class="stat-label">💰 Total Comprado</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number stat-success">${formatMoney(stats.total_creditos)}</div>
                            <div class="stat-label">💵 Total Pagado</div>
                        </div>
                    </div>
                    
                    <h2 style="color: #f1c40f; font-size: 1.5rem; font-weight: bold; text-align: center; margin-bottom: 20px;">📜 Movimientos de la Cuenta</h2>
                    
                    <div style="overflow-x: auto;">
                        <table class="table is-fullwidth custom-table">
                            <thead>
                                <tr>
                                    <th>FECHA</th>
                                    <th>TIPO</th>
                                    <th>DESCRIPCIÓN</th>
                                    <th>MONTO</th>
                                    <th>SALDO</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${movimientosHTML}
                            </tbody>
                        </table>
                    </div>
                    
                    <div style="text-align: center; margin-top: 25px; display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                        ${botonCobrar}
                        <a href="./listado_deudas.php" class="button">⬅️ Volver al Listado</a>
                    </div>
                </div>
            `;
            
            mainContent.innerHTML = contentHTML;
        });
    </script>
</body>
</html>