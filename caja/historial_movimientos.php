<?php
include_once __DIR__ . "/../auth.php";
include_once "../db.php";

// Filtros
$fecha_desde = isset($_GET['fecha_desde']) ? $_GET['fecha_desde'] : "";
$fecha_hasta = isset($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : "";
$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : "todos";
$categoria = isset($_GET['categoria']) ? $_GET['categoria'] : "todos";
$buscar = isset($_GET['buscar']) ? trim($_GET['buscar']) : "";

$condiciones = array();

if ($tipo !== "todos") {
    $condiciones[] = "tipo_movimiento = '" . $conexion->quote($tipo) . "'";
}

if ($categoria !== "todos") {
    $condiciones[] = "categoria = '" . $conexion->quote($categoria) . "'";
}

if (!empty($fecha_desde)) {
    $condiciones[] = "DATE(fecha_movimiento) >= '$fecha_desde'";
}

if (!empty($fecha_hasta)) {
    $condiciones[] = "DATE(fecha_movimiento) <= '$fecha_hasta'";
}

if (!empty($buscar)) {
    $condiciones[] = "(concepto LIKE '%" . $conexion->quote($buscar) . "%' OR observaciones LIKE '%" . $conexion->quote($buscar) . "%')";
}

$where_clause = "";
if (!empty($condiciones)) {
    $where_clause = "WHERE " . implode(" AND ", $condiciones);
}

$sentencia = $conexion->prepare("
    SELECT * FROM caja 
    $where_clause
    ORDER BY fecha_movimiento DESC, fecha_registro DESC
");
$sentencia->execute();
$movimientos = $sentencia->fetchAll(PDO::FETCH_OBJ);

// Calcular totales
$total_ingresos = 0;
$total_egresos = 0;
foreach ($movimientos as $mov) {
    if ($mov->tipo_movimiento === 'INGRESO') {
        $total_ingresos += floatval($mov->monto);
    } else {
        $total_egresos += floatval($mov->monto);
    }
}
$saldo_filtrado = $total_ingresos - $total_egresos;

$movimientosJSON = json_encode($movimientos);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Historial de Movimientos</title>
<link href="../css/bulma.min.css" rel="stylesheet">
<link href="../css/listados.css" rel="stylesheet">
<link href="../css/estadisticas.css" rel="stylesheet">
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
<style>
.main-content { background: #2c3e50 !important; color: white; }
.resumen-filtros {
    background: rgba(52, 152, 219, 0.1);
    border: 2px solid rgba(52, 152, 219, 0.3);
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 25px;
}
.resumen-filtros h3 { color: #3498db; font-size: 1.2rem; font-weight: bold; margin-bottom: 15px; text-align: center; }
.mov-badge { padding: 4px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: bold; display: inline-block; }
.mov-badge.ingreso { background: linear-gradient(45deg, #27ae60, #2ecc71); color: white; }
.mov-badge.egreso { background: linear-gradient(45deg, #e74c3c, #c0392b); color: white; }
.cat-badge { padding: 3px 8px; border-radius: 8px; font-size: 0.7rem; font-weight: bold; display: inline-block; }
.cat-venta { background: linear-gradient(45deg, #3498db, #2980b9); color: white; }
.cat-compra { background: linear-gradient(45deg, #9b59b6, #8e44ad); color: white; }
.cat-otro { background: linear-gradient(45deg, #95a5a6, #7f8c8d); color: white; }
.eliminar-link {
    background: linear-gradient(45deg, #e74c3c, #c0392b) !important;
    color: white !important;
    font-weight: bold;
    text-decoration: none !important;
    padding: 4px 10px;
    border-radius: 5px;
    font-size: 0.75rem;
    transition: all 0.3s ease;
    display: inline-block;
}
.eliminar-link:hover { background: linear-gradient(45deg, #c0392b, #e74c3c) !important; transform: translateY(-2px); }
</style>
</head>
<body>
<?php include '../menu.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const mainContent = document.querySelector('.main-content');
    if (!mainContent) return console.error('No .main-content');

    const movs = <?php echo $movimientosJSON; ?>;
    const totalIng = <?php echo $total_ingresos; ?>;
    const totalEgr = <?php echo $total_egresos; ?>;
    const saldoFilt = <?php echo $saldo_filtrado; ?>;

    const formatMoney = (num) => '₲ ' + parseFloat(num).toLocaleString('es-PY', {minimumFractionDigits: 2});
    const formatDate = (dateStr) => {
        const d = new Date(dateStr);
        return d.toLocaleString('es-PY', { 
            day: '2-digit', month: '2-digit', year: 'numeric',
            hour: '2-digit', minute: '2-digit'
        });
    };

    let filasHTML = '';
    if (movs.length > 0) {
        movs.forEach(mov => {
            const tipoClass = mov.tipo_movimiento.toLowerCase();
            const catClass = 'cat-' + mov.categoria.toLowerCase();

            // Solo eliminamos ciertos movimientos manuales, no anulaciones
            let accionesHTML = '';
            if (mov.categoria === 'OTRO' &&
                !mov.concepto.toUpperCase().includes('ANULACIÓN') &&
                !mov.concepto.toUpperCase().includes('ANULADO')
            ) {
                accionesHTML = `
                    <a href="./eliminar_movimiento.php?id=${mov.id}" class="eliminar-link" 
                        onclick="return confirm('¿Eliminar este movimiento? Esta acción no se puede deshacer.');">
                        <i class="fas fa-trash-alt"></i> Eliminar
                    </a>
                `;
            } else {
                accionesHTML = '<span style="color: rgba(255,255,255,0.5); font-size: 0.75rem;">Automático</span>';
            }

            filasHTML += `
                <tr>
                    <td><strong>#${mov.id}</strong></td>
                    <td>${formatDate(mov.fecha_movimiento)}</td>
                    <td><span class="mov-badge ${tipoClass}">${mov.tipo_movimiento}</span></td>
                    <td><span class="cat-badge ${catClass}">${mov.categoria}</span></td>
                    <td style="text-align: left;">${mov.concepto}</td>
                    <td><strong>${formatMoney(mov.monto)}</strong></td>
                    <td>${accionesHTML}</td>
                </tr>
            `;
        });
    } else {
        filasHTML = '<tr><td colspan="7" class="no-results">No se encontraron movimientos con los filtros seleccionados</td></tr>';
    }

    const contentHTML = `
        <div class="list-container">
            <h1 class="list-title"><i class="fas fa-file-alt"></i> Historial de Movimientos de Caja</h1>

            <div class="resumen-filtros">
                <h3><i class="fas fa-chart-bar"></i> Resumen de Resultados Filtrados</h3>
                <div class="stats-container">
                    <div class="stat-card">
                        <div class="stat-number stat-success">${formatMoney(totalIng)}</div>
                        <div class="stat-label"><i class="fas fa-coins"></i> Total Ingresos</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number stat-warning">${formatMoney(totalEgr)}</div>
                        <div class="stat-label"><i class="fas fa-hand-holding-usd"></i> Total Egresos</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number ${saldoFilt >= 0 ? 'stat-success' : 'stat-critico'}">${formatMoney(saldoFilt)}</div>
                        <div class="stat-label"><i class="fas fa-balance-scale"></i> Saldo</div>
                    </div>
                </div>
            </div>

            <div class="filter-container">
                <form method="GET" action="">
                    <label class="label">Filtrar Movimientos</label>
                    <div class="search-controls">
                        <div class="search-field">
                            <label>Desde:</label>
                            <input type="date" name="fecha_desde" class="search-input" value="<?php echo $fecha_desde; ?>">
                        </div>
                        <div class="search-field">
                            <label>Hasta:</label>
                            <input type="date" name="fecha_hasta" class="search-input" value="<?php echo $fecha_hasta; ?>">
                        </div>
                        <div class="search-field">
                            <label>Tipo:</label>
                            <div class="select">
                                <select name="tipo" class="search-input">
                                    <option value="todos" <?php echo $tipo === 'todos' ? 'selected' : ''; ?>>-- TODOS --</option>
                                    <option value="INGRESO" <?php echo $tipo === 'INGRESO' ? 'selected' : ''; ?>>INGRESO</option>
                                    <option value="EGRESO" <?php echo $tipo === 'EGRESO' ? 'selected' : ''; ?>>EGRESO</option>
                                </select>
                            </div>
                        </div>
                        <div class="search-field">
                            <label>Categoría:</label>
                            <div class="select">
                                <select name="categoria" class="search-input">
                                    <option value="todos" <?php echo $categoria === 'todos' ? 'selected' : ''; ?>>-- TODAS --</option>
                                    <option value="VENTA" <?php echo $categoria === 'VENTA' ? 'selected' : ''; ?>>VENTA</option>
                                    <option value="COMPRA" <?php echo $categoria === 'COMPRA' ? 'selected' : ''; ?>>COMPRA</option>
                                    <option value="OTRO" <?php echo $categoria === 'OTRO' ? 'selected' : ''; ?>>OTRO</option>
                                </select>
                            </div>
                        </div>
                        <div class="search-field">
                            <label>Buscar:</label>
                            <input type="text" name="buscar" class="search-input" placeholder="Concepto..." value="<?php echo htmlspecialchars($buscar); ?>">
                        </div>
                        <div class="search-field">
                            <button type="submit" class="button" style="margin-top: 22px;"><i class="fas fa-search"></i> Filtrar</button>
                        </div>
                    </div>
                </form>
            </div>

            <div style="overflow-x: auto;">
                <table class="table is-fullwidth custom-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>FECHA/HORA</th>
                            <th>TIPO</th>
                            <th>CATEGORÍA</th>
                            <th>CONCEPTO</th>
                            <th>MONTO</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${filasHTML}
                    </tbody>
                </table>
            </div>

            <div style="text-align: center; margin-top: 25px; display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                <a href="./balance.php" class="button"><i class="fas fa-chart-pie"></i> Ver Balance</a>
                <a href="./registrar_movimiento.php" class="button"><i class="fas fa-plus"></i> Registrar Movimiento</a>
            </div>
        </div>
    `;

    mainContent.innerHTML = contentHTML;
});
</script>
</body>
</html>
