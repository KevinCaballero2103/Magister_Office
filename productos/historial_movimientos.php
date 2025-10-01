<?php
// Procesamiento de datos al inicio
include_once "../db.php";

$id_producto = isset($_GET['id_producto']) ? intval($_GET['id_producto']) : null;
$tipo_movimiento = isset($_GET['tipo_movimiento']) ? $_GET['tipo_movimiento'] : "todos";
$fecha_desde = isset($_GET['fecha_desde']) ? $_GET['fecha_desde'] : "";
$fecha_hasta = isset($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : "";

// Construir condiciones WHERE
$condiciones = array();
$params = array();

// Filtro por producto específico
if ($id_producto) {
    $condiciones[] = "m.id_producto = ?";
    $params[] = $id_producto;
}

// Filtro por tipo de movimiento
if ($tipo_movimiento !== "todos") {
    $condiciones[] = "m.tipo_movimiento = ?";
    $params[] = $tipo_movimiento;
}

// Filtro por rango de fechas
if (!empty($fecha_desde)) {
    $condiciones[] = "DATE(m.fecha_movimiento) >= ?";
    $params[] = $fecha_desde;
}

if (!empty($fecha_hasta)) {
    $condiciones[] = "DATE(m.fecha_movimiento) <= ?";
    $params[] = $fecha_hasta;
}

// Construir WHERE clause
$where_clause = "";
if (!empty($condiciones)) {
    $where_clause = "WHERE " . implode(" AND ", $condiciones);
}

// Consulta principal
$sentencia = $conexion->prepare("
    SELECT 
        m.*,
        p.nombre_producto,
        p.codigo_producto,
        pr.nombre_proveedor
    FROM movimientos_stock m
    INNER JOIN productos p ON m.id_producto = p.id
    LEFT JOIN proveedores pr ON m.id_proveedor = pr.id
    $where_clause
    ORDER BY m.fecha_movimiento DESC
    LIMIT 500
");

$sentencia->execute($params);
$movimientos = $sentencia->fetchAll(PDO::FETCH_OBJ);

// Si hay un producto específico, obtener su información
$productoInfo = null;
if ($id_producto) {
    $sentenciaProducto = $conexion->prepare("SELECT nombre_producto, stock_actual FROM productos WHERE id = ?");
    $sentenciaProducto->execute([$id_producto]);
    $productoInfo = $sentenciaProducto->fetch(PDO::FETCH_OBJ);
}

// Convertir a JSON para JavaScript
$movimientosJSON = json_encode($movimientos);
$productoInfoJSON = $productoInfo ? json_encode($productoInfo) : 'null';
$tipoMovimientoActual = $tipo_movimiento;
$fechaDesdeActual = $fecha_desde;
$fechaHastaActual = $fecha_hasta;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Movimientos</title>
    <link href="../css/bulma.min.css" rel="stylesheet">
    <link href="../css/listados.css" rel="stylesheet">
    
    <style>
        .main-content {
            background: #2c3e50 !important;
            color: white;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .badge-entrada {
            background: linear-gradient(45deg, #27ae60, #2ecc71);
            color: white;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .badge-salida {
            background: linear-gradient(45deg, #e74c3c, #c0392b);
            color: white;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .producto-info-box {
            background: rgba(52, 152, 219, 0.1);
            border: 2px solid rgba(52, 152, 219, 0.3);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            text-align: center;
        }

        .producto-info-box h3 {
            color: #3498db;
            font-size: 1.3rem;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .producto-info-box .stock-actual {
            color: #f1c40f;
            font-size: 1.1rem;
            font-weight: bold;
        }

        .observaciones-cell {
            max-width: 250px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            cursor: help;
        }

        .observaciones-cell:hover {
            white-space: normal;
            overflow: visible;
        }
    </style>
</head>
<body>
    <?php include '../menu.php'; ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mainContent = document.querySelector('.main-content');
            const movimientos = <?php echo $movimientosJSON; ?>;
            const productoInfo = <?php echo $productoInfoJSON; ?>;
            const tipoMovimientoActual = '<?php echo $tipoMovimientoActual; ?>';
            const fechaDesdeActual = '<?php echo $fechaDesdeActual; ?>';
            const fechaHastaActual = '<?php echo $fechaHastaActual; ?>';
            const idProducto = <?php echo $id_producto ? $id_producto : 'null'; ?>;
            
            let productoInfoHTML = '';
            if (productoInfo) {
                productoInfoHTML = `
                    <div class='producto-info-box'>
                        <h3>Historial de: ${productoInfo.nombre_producto}</h3>
                        <div>Stock Actual: <span class='stock-actual'>${productoInfo.stock_actual} unidades</span></div>
                    </div>
                `;
            }
            
            let movimientosHTML = '';
            
            if (movimientos && movimientos.length > 0) {
                movimientos.forEach(mov => {
                    const tipoBadge = mov.tipo_movimiento === 'entrada' 
                        ? '<span class="badge-entrada">ENTRADA</span>' 
                        : '<span class="badge-salida">SALIDA</span>';
                    
                    const fecha = new Date(mov.fecha_movimiento);
                    const fechaFormato = fecha.toLocaleString('es-PY', {
                        year: 'numeric',
                        month: '2-digit',
                        day: '2-digit',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                    
                    const proveedor = mov.nombre_proveedor || '-';
                    const precioCompra = mov.precio_compra ? '$' + parseFloat(mov.precio_compra).toFixed(2) : '-';
                    const observaciones = mov.observaciones || '-';
                    
                    movimientosHTML += `
                        <tr>
                            <td>${fechaFormato}</td>
                            <td>${mov.nombre_producto}</td>
                            <td>${mov.codigo_producto || '-'}</td>
                            <td>${tipoBadge}</td>
                            <td><strong>${mov.cantidad}</strong></td>
                            <td>${mov.stock_anterior}</td>
                            <td>${mov.stock_nuevo}</td>
                            <td>${mov.motivo || '-'}</td>
                            <td>${proveedor}</td>
                            <td>${precioCompra}</td>
                            <td class='observaciones-cell' title='${observaciones}'>${observaciones}</td>
                        </tr>
                    `;
                });
            } else {
                movimientosHTML = `
                    <tr>
                        <td colspan="11" class="no-results">
                            No se encontraron movimientos con los criterios seleccionados
                        </td>
                    </tr>
                `;
            }

            const contentHTML = `
                <div class='list-container'>
                    <h1 class='list-title'>Historial de Movimientos de Stock</h1>
                    
                    ${productoInfoHTML}
                    
                    <div class='filter-container'>
                        <form method='GET' action=''>
                            ${idProducto ? `<input type='hidden' name='id_producto' value='${idProducto}'>` : ''}
                            <label class='label'>Filtrar Movimientos</label>
                            <div class='search-controls'>
                                <div class='search-field' style='min-width: 150px;'>
                                    <label>Tipo:</label>
                                    <div class='select'>
                                        <select name='tipo_movimiento' class='search-input'>
                                            <option value='todos' ${tipoMovimientoActual == 'todos' ? 'selected' : ''}>-- TODOS --</option>
                                            <option value='entrada' ${tipoMovimientoActual == 'entrada' ? 'selected' : ''}>ENTRADA</option>
                                            <option value='salida' ${tipoMovimientoActual == 'salida' ? 'selected' : ''}>SALIDA</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class='search-field' style='min-width: 180px;'>
                                    <label>Desde:</label>
                                    <input type='date' name='fecha_desde' class='search-input' value='${fechaDesdeActual}'>
                                </div>
                                
                                <div class='search-field' style='min-width: 180px;'>
                                    <label>Hasta:</label>
                                    <input type='date' name='fecha_hasta' class='search-input' value='${fechaHastaActual}'>
                                </div>
                                
                                <div class='search-field' style='min-width: auto;'>
                                    <button type='submit' class='button' style='margin-top: 22px; padding: 12px 20px; height: 44px;'>
                                        Filtrar
                                    </button>
                                </div>
                            </div>
                        </form>
                        
                        <div style='text-align: center; margin-top: 20px;'>
                            <a href='./gestionar_stock.php' class='button'>
                                Volver a Gestión de Stock
                            </a>
                        </div>
                    </div>

                    <div style='overflow-x: auto;'>
                        <table class='table is-fullwidth custom-table'>
                            <thead>
                                <tr>
                                    <th>FECHA</th>
                                    <th>PRODUCTO</th>
                                    <th>CÓDIGO</th>
                                    <th>TIPO</th>
                                    <th>CANTIDAD</th>
                                    <th>STOCK ANT.</th>
                                    <th>STOCK NUEVO</th>
                                    <th>MOTIVO</th>
                                    <th>PROVEEDOR</th>
                                    <th>PRECIO</th>
                                    <th>OBSERVACIONES</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${movimientosHTML}
                            </tbody>
                        </table>
                    </div>
                    
                    <div style='text-align: center; margin-top: 20px; color: #ecf0f1;'>
                        <p>Mostrando últimos 500 registros. Total encontrados: <strong>${movimientos.length}</strong></p>
                    </div>
                </div>
            `;
            
            mainContent.innerHTML = contentHTML;
        });
    </script>
</body>
</html>