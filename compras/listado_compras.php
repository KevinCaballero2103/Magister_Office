<?php
// Procesamiento de datos al inicio
include_once __DIR__ . "/../auth.php";
include_once "../db.php";
$cajaAbierta = requiereCajaAbierta();

$fecha_desde = isset($_GET['fecha_desde']) ? $_GET['fecha_desde'] : "";
$fecha_hasta = isset($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : "";
$proveedor = isset($_GET['proveedor']) ? $_GET['proveedor'] : "todos";
$estado = isset($_GET['estado']) ? $_GET['estado'] : "1";
$orden = isset($_GET['orden']) ? $_GET['orden'] : "fecha_desc";

// Construir condiciones WHERE
$condiciones = array();

// Filtro por estado
if ($estado !== "99") {
    $condiciones[] = "c.estado_compra = " . intval($estado);
}

// Filtro por proveedor
if ($proveedor !== "todos") {
    $condiciones[] = "c.id_proveedor = " . intval($proveedor);
}

// Filtro por fechas
if (!empty($fecha_desde)) {
    $condiciones[] = "c.fecha_compra >= '$fecha_desde'";
}
if (!empty($fecha_hasta)) {
    $condiciones[] = "c.fecha_compra <= '$fecha_hasta'";
}

// Construir WHERE clause
$where_clause = "";
if (!empty($condiciones)) {
    $where_clause = "WHERE " . implode(" AND ", $condiciones);
}

// Determinar orden
switch ($orden) {
    case "id_asc":
        $order_by = "ORDER BY c.id ASC";
        break;
    case "id_desc":
        $order_by = "ORDER BY c.id DESC";
        break;
    case "fecha_asc":
        $order_by = "ORDER BY c.fecha_compra ASC";
        break;
    case "total_asc":
        $order_by = "ORDER BY c.total_compra ASC";
        break;
    case "total_desc":
        $order_by = "ORDER BY c.total_compra DESC";
        break;
    default:
        $order_by = "ORDER BY c.fecha_compra DESC";
        break;
}

$sentencia = $conexion->prepare("
    SELECT c.*, p.nombre_proveedor
    FROM compras c
    INNER JOIN proveedores p ON c.id_proveedor = p.id
    $where_clause
    $order_by
");

$sentencia->execute();
$compras = $sentencia->fetchAll(PDO::FETCH_OBJ);

// Obtener detalles de cada compra
$comprasConDetalles = array();
foreach ($compras as $compra) {
    $sentenciaDetalle = $conexion->prepare("
        SELECT dc.*, prod.nombre_producto, prod.codigo_producto
        FROM detalle_compras dc
        INNER JOIN productos prod ON dc.id_producto = prod.id
        WHERE dc.id_compra = ?
    ");
    $sentenciaDetalle->execute([$compra->id]);
    $detalles = $sentenciaDetalle->fetchAll(PDO::FETCH_OBJ);
    
    $compra->detalles = $detalles;
    $comprasConDetalles[] = $compra;
}

// Obtener proveedores para el filtro
$sentenciaProveedores = $conexion->prepare("SELECT id, nombre_proveedor FROM proveedores WHERE estado_proveedor = 1 ORDER BY nombre_proveedor ASC");
$sentenciaProveedores->execute();
$proveedores = $sentenciaProveedores->fetchAll(PDO::FETCH_OBJ);

// Convertir a JSON para JavaScript
$comprasJSON = json_encode($comprasConDetalles);
$proveedoresJSON = json_encode($proveedores);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Compras</title>
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

        .expandable-row {
            display: none;
            background: rgba(241, 196, 15, 0.05) !important;
        }

        .expandable-row.show {
            display: table-row;
            animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .details-container {
            padding: 15px 20px;
            background: rgba(0,0,0,0.2);
            border-radius: 8px;
            margin: 10px;
        }

        .details-title {
            color: #f1c40f;
            font-weight: bold;
            margin-bottom: 10px;
            font-size: 0.9rem;
        }

        .details-table {
            width: 100%;
            background: rgba(255,255,255,0.05);
            border-radius: 8px;
            overflow: hidden;
        }

        .details-table th {
            background: rgba(241, 196, 15, 0.2);
            color: #f1c40f;
            padding: 8px;
            font-size: 0.8rem;
            text-align: center;
        }

        .details-table td {
            padding: 8px;
            font-size: 0.8rem;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .expand-btn {
            background: transparent !important;
            border: none !important;
            color: #f1c40f !important;
            cursor: pointer !important;
            padding: 4px 8px !important;
            border-radius: 4px !important;
            transition: all 0.3s ease !important;
            font-size: 0.75rem !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 4px !important;
        }

        .expand-btn:hover {
            background: rgba(241, 196, 15, 0.1) !important;
            color: #f39c12 !important;
        }

        .expand-icon {
            transition: transform 0.3s ease;
        }

        .expand-icon.rotated {
            transform: rotate(180deg);
        }

        .total-badge {
            background: linear-gradient(45deg, #27ae60, #2ecc71);
            color: white;
            padding: 6px 12px;
            border-radius: 12px;
            font-weight: bold;
            font-size: 0.85rem;
        }

        .estado-activo {
            color: #27ae60;
            font-weight: bold;
        }

        .estado-anulado {
            color: #e74c3c;
            font-weight: bold;
            text-decoration: line-through;
        }
        
        .edit-link {
            background: linear-gradient(45deg, #3498db, #2980b9) !important;
            color: white !important;
            font-weight: bold;
            text-decoration: none !important;
            padding: 4px 8px;
            border-radius: 4px;
            transition: all 0.3s ease;
            display: inline-block;
            font-size: 0.7rem;
        }

        .edit-link:hover {
            background: linear-gradient(45deg, #2980b9, #3498db) !important;
            transform: translateY(-1px);
            box-shadow: 0 3px 8px rgba(52, 152, 219, 0.4);
            color: white !important;
        }
        
        .delete-link {
            background: linear-gradient(45deg, #e74c3c, #c0392b) !important;
            color: white !important;
            font-weight: bold;
            text-decoration: none !important;
            padding: 4px 8px;
            border-radius: 4px;
            transition: all 0.3s ease;
            display: inline-block;
            font-size: 0.7rem;
        }

        .delete-link:hover {
            background: linear-gradient(45deg, #c0392b, #e74c3c) !important;
            transform: translateY(-1px);
            box-shadow: 0 3px 8px rgba(231, 76, 60, 0.4);
            color: white !important;
        }
    </style>
</head>
<body>
    <?php include '../menu.php'; ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mainContent = document.querySelector('.main-content');
            const compras = <?php echo $comprasJSON; ?>;
            const proveedores = <?php echo $proveedoresJSON; ?>;
            
            let comprasHTML = '';
            
            if (compras && compras.length > 0) {
                compras.forEach((compra, index) => {
                    const estadoClass = compra.estado_compra == 1 ? 'estado-activo' : 'estado-anulado';
                    const estadoText = compra.estado_compra == 1 ? 'ACTIVA' : 'ANULADA';
                    
                    const fecha = new Date(compra.fecha_compra + 'T00:00:00');
                    const fechaFormateada = fecha.toLocaleDateString('es-PY', { 
                        day: '2-digit', 
                        month: '2-digit', 
                        year: 'numeric' 
                    });
                    
                    let detallesHTML = '';
                    if (compra.detalles && compra.detalles.length > 0) {
                        compra.detalles.forEach(detalle => {
                            detallesHTML += `
                                <tr>
                                    <td>${detalle.nombre_producto}</td>
                                    <td>${detalle.codigo_producto || '-'}</td>
                                    <td>${detalle.cantidad}</td>
                                    <td>₲ ${parseFloat(detalle.precio_unitario).toLocaleString('es-PY', {minimumFractionDigits: 2})}</td>
                                    <td><strong>₲ ${parseFloat(detalle.subtotal).toLocaleString('es-PY', {minimumFractionDigits: 2})}</strong></td>
                                </tr>
                            `;
                        });
                    }
                    
                    comprasHTML += `
                        <tr>
                            <td><strong>#${compra.id}</strong></td>
                            <td>${fechaFormateada}</td>
                            <td>${compra.nombre_proveedor}</td>
                            <td>${compra.numero_compra || '-'}</td>
                            <td><span class="total-badge">₲ ${parseFloat(compra.total_compra).toLocaleString('es-PY', {minimumFractionDigits: 2})}</span></td>
                            <td><span class="${estadoClass}">${estadoText}</span></td>
                            <td>
                                <button class="expand-btn" onclick="toggleDetails(${index})" id="btn-${index}">
                                    <span class="expand-icon" id="icon-${index}">▼</span>
                                    <span id="text-${index}">Ver Detalle</span>
                                </button>
                                <br style="margin-bottom: 5px;">
                                <a href='frm_editar_compra.php?id=${compra.id}' class='edit-link' style='margin-right: 5px;'>
                                    ✏️ EDITAR
                                </a>
                                <a href='eliminar_compra.php?id=${compra.id}' class='delete-link'
                                   onclick="return confirm('⚠️ ¿Estás seguro de eliminar esta compra?\\n\\nEsto revertirá el stock de los productos.');">
                                    🗑️ ELIMINAR
                                </a>
                            </td>
                        </tr>
                        <tr class="expandable-row" id="details-${index}">
                            <td colspan="7">
                                <div class="details-container">
                                    <div class="details-title">
                                        📦 Productos Comprados (${compra.detalles ? compra.detalles.length : 0} items)
                                    </div>
                                    ${compra.observaciones ? `<p style="color: rgba(255,255,255,0.7); font-size: 0.85rem; margin-bottom: 10px;"><strong>Observaciones:</strong> ${compra.observaciones}</p>` : ''}
                                    <table class="details-table">
                                        <thead>
                                            <tr>
                                                <th>Producto</th>
                                                <th>Código</th>
                                                <th>Cantidad</th>
                                                <th>Precio Unit.</th>
                                                <th>Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${detallesHTML}
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
                    `;
                });
            } else {
                comprasHTML = `
                    <tr>
                        <td colspan="7" class="no-results">
                            No se encontraron compras con los criterios seleccionados
                        </td>
                    </tr>
                `;
            }

            // Construir opciones de proveedores
            let proveedoresOptions = '<option value="todos">-- TODOS --</option>';
            proveedores.forEach(prov => {
                const selected = '<?php echo $proveedor; ?>' == prov.id ? 'selected' : '';
                proveedoresOptions += `<option value="${prov.id}" ${selected}>${prov.nombre_proveedor}</option>`;
            });

            const contentHTML = `
                <div class='list-container'>
                    <h1 class='list-title'>📦 Historial de Compras</h1>
                    
                    <div class='filter-container'>
                        <form method='GET' action=''>
                            <label class='label'>Filtrar Compras</label>
                            <div class='search-controls'>
                                <div class='search-field' style='min-width: 140px;'>
                                    <label>Desde:</label>
                                    <input type='date' name='fecha_desde' class='search-input' value='<?php echo $fecha_desde; ?>'>
                                </div>
                                
                                <div class='search-field' style='min-width: 140px;'>
                                    <label>Hasta:</label>
                                    <input type='date' name='fecha_hasta' class='search-input' value='<?php echo $fecha_hasta; ?>'>
                                </div>
                                
                                <div class='search-field' style='min-width: 200px;'>
                                    <label>Proveedor:</label>
                                    <div class='select'>
                                        <select name='proveedor' class='search-input'>
                                            ${proveedoresOptions}
                                        </select>
                                    </div>
                                </div>
                                
                                <div class='search-field' style='min-width: 140px;'>
                                    <label>Estado:</label>
                                    <div class='select'>
                                        <select name='estado' class='search-input'>
                                            <option value='99' ${'<?php echo $estado; ?>' == '99' ? 'selected' : ''}>-- TODOS --</option>
                                            <option value='1' ${'<?php echo $estado; ?>' == '1' ? 'selected' : ''}>ACTIVA</option>
                                            <option value='0' ${'<?php echo $estado; ?>' == '0' ? 'selected' : ''}>ANULADA</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class='search-field' style='min-width: 160px;'>
                                    <label>Ordenar por:</label>
                                    <div class='select'>
                                        <select name='orden' class='search-input'>
                                            <option value='fecha_desc' ${'<?php echo $orden; ?>' == 'fecha_desc' ? 'selected' : ''}>Fecha Reciente</option>
                                            <option value='fecha_asc' ${'<?php echo $orden; ?>' == 'fecha_asc' ? 'selected' : ''}>Fecha Antigua</option>
                                            <option value='total_desc' ${'<?php echo $orden; ?>' == 'total_desc' ? 'selected' : ''}>Total Mayor</option>
                                            <option value='total_asc' ${'<?php echo $orden; ?>' == 'total_asc' ? 'selected' : ''}>Total Menor</option>
                                            <option value='id_desc' ${'<?php echo $orden; ?>' == 'id_desc' ? 'selected' : ''}>ID Reciente</option>
                                            <option value='id_asc' ${'<?php echo $orden; ?>' == 'id_asc' ? 'selected' : ''}>ID Antiguo</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class='search-field' style='min-width: auto;'>
                                    <button type='submit' class='button' style='margin-top: 22px; padding: 12px 20px; height: 44px;'>
                                        Filtrar
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div style='overflow-x: auto;'>
                        <table class='table is-fullwidth custom-table'>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>FECHA</th>
                                    <th>PROVEEDOR</th>
                                    <th>N° FACTURA</th>
                                    <th>TOTAL</th>
                                    <th>ESTADO</th>
                                    <th>ACCIONES</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${comprasHTML}
                            </tbody>
                        </table>
                    </div>

                    <div style='text-align: center; margin-top: 25px;'>
                        <a href='./frm_registrar_compra.php' class='button'>
                            ➕ Registrar Nueva Compra
                        </a>
                    </div>
                </div>
            `;
            
            mainContent.innerHTML = contentHTML;
            
            window.toggleDetails = function(index) {
                const expandableRow = document.getElementById(`details-${index}`);
                const icon = document.getElementById(`icon-${index}`);
                const text = document.getElementById(`text-${index}`);
                
                if (expandableRow.classList.contains('show')) {
                    expandableRow.classList.remove('show');
                    icon.classList.remove('rotated');
                    text.textContent = 'Ver Detalle';
                } else {
                    expandableRow.classList.add('show');
                    icon.classList.add('rotated');
                    text.textContent = 'Ocultar';
                }
            };
        });
    </script>
</body>
</html>