<?php
// Procesamiento de datos al inicio
include_once "../db.php";

$fecha_desde = isset($_GET['fecha_desde']) ? $_GET['fecha_desde'] : "";
$fecha_hasta = isset($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : "";
$cliente = isset($_GET['cliente']) ? $_GET['cliente'] : "todos";
$estado = isset($_GET['estado']) ? $_GET['estado'] : "1";
$orden = isset($_GET['orden']) ? $_GET['orden'] : "fecha_desc";

// Construir condiciones WHERE
$condiciones = array();

// Filtro por estado
if ($estado !== "99") {
    $condiciones[] = "v.estado_venta = " . intval($estado);
}

// Filtro por cliente
if ($cliente !== "todos") {
    if ($cliente === "sin_cliente") {
        $condiciones[] = "v.id_cliente IS NULL";
    } else {
        $condiciones[] = "v.id_cliente = " . intval($cliente);
    }
}

// Filtro por fechas
if (!empty($fecha_desde)) {
    $condiciones[] = "v.fecha_venta >= '$fecha_desde'";
}
if (!empty($fecha_hasta)) {
    $condiciones[] = "v.fecha_venta <= '$fecha_hasta'";
}

// Construir WHERE clause
$where_clause = "";
if (!empty($condiciones)) {
    $where_clause = "WHERE " . implode(" AND ", $condiciones);
}

// Determinar orden
switch ($orden) {
    case "id_asc":
        $order_by = "ORDER BY v.id ASC";
        break;
    case "id_desc":
        $order_by = "ORDER BY v.id DESC";
        break;
    case "fecha_asc":
        $order_by = "ORDER BY v.fecha_venta ASC";
        break;
    case "total_asc":
        $order_by = "ORDER BY v.total_venta ASC";
        break;
    case "total_desc":
        $order_by = "ORDER BY v.total_venta DESC";
        break;
    default:
        $order_by = "ORDER BY v.fecha_venta DESC";
        break;
}

$sentencia = $conexion->prepare("
    SELECT v.*, 
           CONCAT(c.nombre_cliente, ' ', c.apellido_cliente) as nombre_cliente
    FROM ventas v
    LEFT JOIN clientes c ON v.id_cliente = c.id
    $where_clause
    $order_by
");

$sentencia->execute();
$ventas = $sentencia->fetchAll(PDO::FETCH_OBJ);

// Obtener detalles de cada venta
$ventasConDetalles = array();
foreach ($ventas as $venta) {
    $sentenciaDetalle = $conexion->prepare("
        SELECT dv.*, 
               CASE 
                   WHEN dv.tipo_item = 'PRODUCTO' THEN 'PRODUCTO'
                   WHEN dv.tipo_item = 'SERVICIO' THEN 'SERVICIO'
               END as tipo
        FROM detalle_ventas dv
        WHERE dv.id_venta = ?
    ");
    $sentenciaDetalle->execute([$venta->id]);
    $detalles = $sentenciaDetalle->fetchAll(PDO::FETCH_OBJ);
    
    $venta->detalles = $detalles;
    $ventasConDetalles[] = $venta;
}

// Obtener clientes para el filtro
$sentenciaClientes = $conexion->prepare("SELECT id, nombre_cliente, apellido_cliente FROM clientes WHERE estado_cliente = 1 ORDER BY nombre_cliente ASC");
$sentenciaClientes->execute();
$clientes = $sentenciaClientes->fetchAll(PDO::FETCH_OBJ);

// Convertir a JSON para JavaScript
$ventasJSON = json_encode($ventasConDetalles);
$clientesJSON = json_encode($clientes);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Ventas</title>
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

        .tipo-badge {
            padding: 4px 8px;
            border-radius: 10px;
            font-size: 0.7rem;
            font-weight: bold;
        }

        .tipo-producto {
            background: linear-gradient(45deg, #3498db, #2980b9);
            color: white;
        }

        .tipo-servicio {
            background: linear-gradient(45deg, #9b59b6, #8e44ad);
            color: white;
        }
        
        .anular-link {
            background: linear-gradient(45deg, #e67e22, #d35400) !important;
            color: white !important;
            font-weight: bold;
            text-decoration: none !important;
            padding: 4px 8px;
            border-radius: 4px;
            transition: all 0.3s ease;
            display: inline-block;
            font-size: 0.7rem;
        }

        .anular-link:hover {
            background: linear-gradient(45deg, #d35400, #e67e22) !important;
            transform: translateY(-1px);
            box-shadow: 0 3px 8px rgba(230, 126, 34, 0.4);
            color: white !important;
        }

        .resumen-venta {
            background: rgba(52, 152, 219, 0.1);
            padding: 12px;
            border-radius: 8px;
            margin-top: 10px;
            border: 1px solid rgba(52, 152, 219, 0.3);
        }

        .resumen-venta p {
            margin: 5px 0;
            font-size: 0.85rem;
            color: rgba(255,255,255,0.9);
        }

        .resumen-venta strong {
            color: #3498db;
        }
    </style>
</head>
<body>
    <?php include '../menu.php'; ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mainContent = document.querySelector('.main-content');
            const ventas = <?php echo $ventasJSON; ?>;
            const clientes = <?php echo $clientesJSON; ?>;
            
            let ventasHTML = '';
            
            if (ventas && ventas.length > 0) {
                ventas.forEach((venta, index) => {
                    const estadoClass = venta.estado_venta == 1 ? 'estado-activo' : 'estado-anulado';
                    const estadoText = venta.estado_venta == 1 ? 'ACTIVA' : 'ANULADA';
                    
                    const fecha = new Date(venta.fecha_venta + 'T00:00:00');
                    const fechaFormateada = fecha.toLocaleDateString('es-PY', { 
                        day: '2-digit', 
                        month: '2-digit', 
                        year: 'numeric' 
                    });
                    
                    const nombreCliente = venta.nombre_cliente || 'Cliente Genérico';
                    
                    let detallesHTML = '';
                    let countProductos = 0;
                    let countServicios = 0;
                    
                    if (venta.detalles && venta.detalles.length > 0) {
                        venta.detalles.forEach(detalle => {
                            const tipoBadge = detalle.tipo_item === 'PRODUCTO' ? 
                                '<span class="tipo-badge tipo-producto">PRODUCTO</span>' : 
                                '<span class="tipo-badge tipo-servicio">SERVICIO</span>';
                            
                            if (detalle.tipo_item === 'PRODUCTO') countProductos++;
                            if (detalle.tipo_item === 'SERVICIO') countServicios++;
                            
                            detallesHTML += `
                                <tr>
                                    <td>${tipoBadge}</td>
                                    <td>${detalle.descripcion}</td>
                                    <td>${detalle.cantidad}</td>
                                    <td>₲ ${parseFloat(detalle.precio_unitario).toLocaleString('es-PY', {minimumFractionDigits: 2})}</td>
                                    <td><strong>₲ ${parseFloat(detalle.subtotal).toLocaleString('es-PY', {minimumFractionDigits: 2})}</strong></td>
                                </tr>
                            `;
                        });
                    }
                    
                    ventasHTML += `
                        <tr>
                            <td><strong>#${venta.id}</strong></td>
                            <td>${fechaFormateada}</td>
                            <td>${nombreCliente}</td>
                            <td>${venta.numero_venta || '-'}</td>
                            <td><span class="total-badge">₲ ${parseFloat(venta.total_venta).toLocaleString('es-PY', {minimumFractionDigits: 2})}</span></td>
                            <td><span class="${estadoClass}">${estadoText}</span></td>
                            <td>
                                <button class="expand-btn" onclick="toggleDetails(${index})" id="btn-${index}">
                                    <span class="expand-icon" id="icon-${index}">▼</span>
                                    <span id="text-${index}">Ver Detalle</span>
                                </button>
                                ${venta.estado_venta == 1 ? `
                                <br style="margin-bottom: 5px;">
                                <a href='anular_venta.php?id=${venta.id}' class='anular-link'
                                   onclick="return confirm('⚠️ ¿Estás seguro de anular esta venta?\\n\\nEsto revertirá el stock de los productos y eliminará el movimiento de caja.');">
                                    ❌ ANULAR
                                </a>
                                ` : ''}
                            </td>
                        </tr>
                        <tr class="expandable-row" id="details-${index}">
                            <td colspan="7">
                                <div class="details-container">
                                    <div class="details-title">
                                        💰 Detalle de Venta #${venta.id}
                                    </div>
                                    
                                    <div class="resumen-venta">
                                        <p><strong>Cliente:</strong> ${nombreCliente}</p>
                                        <p><strong>Fecha:</strong> ${fechaFormateada}</p>
                                        ${venta.numero_venta ? `<p><strong>N° Ticket:</strong> ${venta.numero_venta}</p>` : ''}
                                        <p><strong>Items:</strong> ${venta.detalles ? venta.detalles.length : 0} (${countProductos} productos, ${countServicios} servicios)</p>
                                        <p><strong>Subtotal:</strong> ₲ ${parseFloat(venta.subtotal).toLocaleString('es-PY', {minimumFractionDigits: 2})}</p>
                                        <p><strong>Descuento:</strong> ₲ ${parseFloat(venta.descuento).toLocaleString('es-PY', {minimumFractionDigits: 2})}</p>
                                        <p style="font-size: 1rem;"><strong>TOTAL:</strong> <strong style="color: #27ae60;">₲ ${parseFloat(venta.total_venta).toLocaleString('es-PY', {minimumFractionDigits: 2})}</strong></p>
                                        ${venta.observaciones ? `<p><strong>Observaciones:</strong> ${venta.observaciones}</p>` : ''}
                                    </div>
                                    
                                    <div style="margin-top: 15px;">
                                        <strong style="color: #f1c40f;">Items Vendidos:</strong>
                                    </div>
                                    
                                    <table class="details-table" style="margin-top: 10px;">
                                        <thead>
                                            <tr>
                                                <th>Tipo</th>
                                                <th>Descripción</th>
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
                ventasHTML = `
                    <tr>
                        <td colspan="7" class="no-results">
                            No se encontraron ventas con los criterios seleccionados
                        </td>
                    </tr>
                `;
            }

            // Construir opciones de clientes
            let clientesOptions = '<option value="todos">-- TODOS --</option>';
            clientesOptions += '<option value="sin_cliente">SIN CLIENTE (Genéricos)</option>';
            clientes.forEach(cli => {
                const selected = '<?php echo $cliente; ?>' == cli.id ? 'selected' : '';
                clientesOptions += `<option value="${cli.id}" ${selected}>${cli.nombre_cliente} ${cli.apellido_cliente}</option>`;
            });

            const contentHTML = `
                <div class='list-container'>
                    <h1 class='list-title'>💰 Historial de Ventas</h1>
                    
                    <div class='filter-container'>
                        <form method='GET' action=''>
                            <label class='label'>Filtrar Ventas</label>
                            <div class='search-controls'>
                                <div class='search-field' style='min-width: 140px;'>
                                    <label>Desde:</label>
                                    <input type='date' name='fecha_desde' class='search-input' value='<?php echo $fecha_desde; ?>'>
                                </div>
                                
                                <div class='search-field' style='min-width: 140px;'>
                                    <label>Hasta:</label>
                                    <input type='date' name='fecha_hasta' class='search-input' value='<?php echo $fecha_hasta; ?>'>
                                </div>
                                
                                <div class='search-field' style='min-width: 220px;'>
                                    <label>Cliente:</label>
                                    <div class='select'>
                                        <select name='cliente' class='search-input'>
                                            ${clientesOptions}
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
                                    <th>CLIENTE</th>
                                    <th>N° TICKET</th>
                                    <th>TOTAL</th>
                                    <th>ESTADO</th>
                                    <th>ACCIONES</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${ventasHTML}
                            </tbody>
                        </table>
                    </div>

                    <div style='text-align: center; margin-top: 25px;'>
                        <a href='./frm_registrar_venta.php' class='button'>
                            ➕ Registrar Nueva Venta
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