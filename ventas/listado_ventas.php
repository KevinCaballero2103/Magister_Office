<?php
include_once __DIR__ . "/../auth.php";
if (!tienePermiso(['ADMINISTRADOR'])) {
    header("Location: ../index.php?error=sin_permisos");
    exit();
}
include_once "../db.php";
$cajaAbierta = requiereCajaAbierta();

$fecha_desde = isset($_GET['fecha_desde']) ? $_GET['fecha_desde'] : "";
$fecha_hasta = isset($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : "";
$cliente = isset($_GET['cliente']) ? $_GET['cliente'] : "todos";
$estado = isset($_GET['estado']) ? $_GET['estado'] : "1";
$condicion = isset($_GET['condicion']) ? $_GET['condicion'] : "todos";
$orden = isset($_GET['orden']) ? $_GET['orden'] : "fecha_desc";

$condiciones = array();

if ($estado !== "99") {
    $condiciones[] = "v.estado_venta = " . intval($estado);
}

if ($cliente !== "todos") {
    if ($cliente === "sin_cliente") {
        $condiciones[] = "v.id_cliente IS NULL";
    } else {
        $condiciones[] = "v.id_cliente = " . intval($cliente);
    }
}

if ($condicion !== "todos") {
    $condiciones[] = "v.condicion_venta = '" . $conexion->quote($condicion) . "'";
}

if (!empty($fecha_desde)) {
    $condiciones[] = "DATE(v.fecha_venta) >= '$fecha_desde'";
}
if (!empty($fecha_hasta)) {
    $condiciones[] = "DATE(v.fecha_venta) <= '$fecha_hasta'";
}

$where_clause = "";
if (!empty($condiciones)) {
    $where_clause = "WHERE " . implode(" AND ", $condiciones);
}

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
           CONCAT(COALESCE(c.nombre_cliente, ''), ' ', COALESCE(c.apellido_cliente, '')) as nombre_cliente
    FROM ventas v
    LEFT JOIN clientes c ON v.id_cliente = c.id
    $where_clause
    $order_by
");

$sentencia->execute();
$ventas = $sentencia->fetchAll(PDO::FETCH_OBJ);

$ventasConDetalles = array();
foreach ($ventas as $venta) {
    $sentenciaDetalle = $conexion->prepare("
        SELECT dv.* FROM detalle_ventas dv
        WHERE dv.id_venta = ?
    ");
    $sentenciaDetalle->execute([$venta->id]);
    $detalles = $sentenciaDetalle->fetchAll(PDO::FETCH_OBJ);
    
    $cuotas = array();
    if ($venta->condicion_venta === 'CREDITO') {
        $sentenciaCuotas = $conexion->prepare("
            SELECT * FROM cuotas_venta 
            WHERE id_venta = ? 
            ORDER BY numero ASC
        ");
        $sentenciaCuotas->execute([$venta->id]);
        $cuotas = $sentenciaCuotas->fetchAll(PDO::FETCH_OBJ);
    }
    
    $venta->detalles = $detalles;
    $venta->cuotas = $cuotas;
    $ventasConDetalles[] = $venta;
}

$sentenciaClientes = $conexion->prepare("SELECT id, nombre_cliente, apellido_cliente FROM clientes WHERE estado_cliente = 1 ORDER BY nombre_cliente ASC");
$sentenciaClientes->execute();
$clientes = $sentenciaClientes->fetchAll(PDO::FETCH_OBJ);

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

        .expandable-row {
            display: none;
            background: rgba(241, 196, 15, 0.05) !important;
        }

        .expandable-row.show {
            display: table-row;
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

        .details-table th {
            background: rgba(241, 196, 15, 0.2);
            color: #f1c40f;
            padding: 8px;
            font-size: 0.8rem;
        }

        .details-table td {
            padding: 8px;
            font-size: 0.8rem;
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
            display: inline-block !important;
        }

        .expand-btn:hover {
            background: rgba(241, 196, 15, 0.1) !important;
            color: #f39c12 !important;
        }

        .condicion-badge {
            padding: 4px 8px;
            border-radius: 10px;
            font-size: 0.7rem;
            font-weight: bold;
            display: inline-block;
        }

        .condicion-contado {
            background: linear-gradient(45deg, #27ae60, #2ecc71);
            color: white;
        }

        .condicion-credito {
            background: linear-gradient(45deg, #e67e22, #d35400);
            color: white;
        }

        .tipo-badge {
            padding: 4px 8px;
            border-radius: 10px;
            font-size: 0.7rem;
            font-weight: bold;
            display: inline-block;
        }

        .tipo-producto {
            background: linear-gradient(45deg, #3498db, #2980b9);
            color: white;
        }

        .tipo-servicio {
            background: linear-gradient(45deg, #9b59b6, #8e44ad);
            color: white;
        }

        .comprobante-badge {
            padding: 3px 6px;
            border-radius: 8px;
            font-size: 0.65rem;
            font-weight: bold;
            display: inline-block;
            margin-left: 5px;
        }

        .comprobante-factura {
            background: linear-gradient(45deg, #e74c3c, #c0392b);
            color: white;
        }

        .comprobante-ticket {
            background: linear-gradient(45deg, #95a5a6, #7f8c8d);
            color: white;
        }

        .comprobante-ninguno {
            background: rgba(255,255,255,0.1);
            color: rgba(255,255,255,0.5);
        }

        .imprimir-link, .anular-link {
            color: white !important;
            font-weight: bold;
            text-decoration: none !important;
            padding: 4px 8px;
            border-radius: 4px;
            transition: all 0.3s ease;
            display: inline-block;
            font-size: 0.7rem;
            margin-right: 4px;
        }

        .anular-link {
            background: linear-gradient(45deg, #e67e22, #d35400) !important;
        }

        .anular-link:hover {
            background: linear-gradient(45deg, #d35400, #e67e22) !important;
            transform: translateY(-2px);
        }

        .imprimir-link {
            background: linear-gradient(45deg, #3498db, #2980b9) !important;
        }

        .imprimir-link:hover {
            background: linear-gradient(45deg, #2980b9, #3498db) !important;
            transform: translateY(-2px);
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

        .cuotas-section {
            background: rgba(230, 126, 34, 0.1);
            padding: 12px;
            border-radius: 8px;
            margin-top: 15px;
            border: 1px solid rgba(230, 126, 34, 0.3);
        }

        .cuota-item {
            padding: 8px;
            background: rgba(0,0,0,0.1);
            border-radius: 4px;
            margin: 5px 0;
            font-size: 0.8rem;
        }

        .cuota-pendiente {
            color: #f39c12;
        }

        .cuota-pagada {
            color: #27ae60;
        }
        
        .estado-activo {
            background: linear-gradient(45deg, #27ae60, #2ecc71);
            color: white;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: bold;
            display: inline-block;
        }
        
        .estado-anulado {
            background: linear-gradient(45deg, #95a5a6, #7f8c8d);
            color: white;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: bold;
            display: inline-block;
        }
    </style>
</head>
<body>
    <?php include '../menu.php'; ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var mainContent = document.querySelector('.main-content');
            if (!mainContent) {
                console.error('No se encontró .main-content');
                return;
            }

            var ventas = <?php echo $ventasJSON; ?>;
            var clientes = <?php echo $clientesJSON; ?>;
            
            var ventasHTML = '';
            
            if (ventas && ventas.length > 0) {
                ventas.forEach(function(venta, index) {
                    var estadoClass = venta.estado_venta == 1 ? 'estado-activo' : 'estado-anulado';
                    var estadoText = venta.estado_venta == 1 ? 'ACTIVA' : 'ANULADA';
                    var condicionClass = venta.condicion_venta === 'CREDITO' ? 'condicion-credito' : 'condicion-contado';
                    
                    // FIX: Formato correcto de fecha (sin agregar T00:00:00 si ya tiene hora)
                    var fechaVenta = venta.fecha_venta;
                    var fecha = fechaVenta.includes(':') ? new Date(fechaVenta) : new Date(fechaVenta + 'T00:00:00');
                    var fechaFormateada = fecha.toLocaleDateString('es-PY', { day: '2-digit', month: '2-digit', year: 'numeric' });
                    
                    var nombreCliente = venta.nombre_cliente ? venta.nombre_cliente.trim() : 'Cliente Genérico';
                    
                    // Badge de tipo de comprobante
                    var comprobanteBadge = '';
                    if (venta.tipo_comprobante) {
                        var claseComprobante = venta.tipo_comprobante === 'FACTURA' ? 'comprobante-factura' : 'comprobante-ticket';
                        var iconoComprobante = venta.tipo_comprobante === 'FACTURA' ? '📄' : '🎫';
                        comprobanteBadge = '<span class="comprobante-badge ' + claseComprobante + '">' + iconoComprobante + ' ' + venta.tipo_comprobante + '</span>';
                    } else {
                        comprobanteBadge = '<span class="comprobante-badge comprobante-ninguno">Sin comprobante</span>';
                    }
                    
                    var detallesHTML = '';
                    var countProductos = 0;
                    var countServicios = 0;
                    
                    if (venta.detalles && venta.detalles.length > 0) {
                        venta.detalles.forEach(function(detalle) {
                            var tipoBadge = detalle.tipo_item === 'PRODUCTO' ? 
                                '<span class="tipo-badge tipo-producto">PRODUCTO</span>' : 
                                '<span class="tipo-badge tipo-servicio">SERVICIO</span>';
                            
                            if (detalle.tipo_item === 'PRODUCTO') countProductos++;
                            if (detalle.tipo_item === 'SERVICIO') countServicios++;
                            
                            detallesHTML += '<tr>' +
                                '<td>' + tipoBadge + '</td>' +
                                '<td>' + detalle.descripcion + '</td>' +
                                '<td>' + detalle.cantidad + '</td>' +
                                '<td>₲ ' + parseFloat(detalle.precio_unitario).toLocaleString('es-PY', {minimumFractionDigits: 2}) + '</td>' +
                                '<td><strong>₲ ' + parseFloat(detalle.subtotal).toLocaleString('es-PY', {minimumFractionDigits: 2}) + '</strong></td>' +
                                '</tr>';
                        });
                    }

                    var cuotasHTML = '';
                    if (venta.condicion_venta === 'CREDITO' && venta.cuotas && venta.cuotas.length > 0) {
                        cuotasHTML = '<div class="cuotas-section"><strong>Cuotas de Pago:</strong>';
                        venta.cuotas.forEach(function(cuota) {
                            var claseEstado = cuota.estado === 'PAGADA' ? 'cuota-pagada' : 'cuota-pendiente';
                            var iconoEstado = cuota.estado === 'PAGADA' ? '✓' : '⏳';
                            var fechaCuota = new Date(cuota.fecha_vencimiento + 'T00:00:00').toLocaleDateString('es-PY');
                            cuotasHTML += '<div class="cuota-item ' + claseEstado + '">' +
                                iconoEstado + ' Cuota ' + cuota.numero + ': ₲ ' + parseFloat(cuota.monto).toLocaleString('es-PY', {minimumFractionDigits: 2}) + 
                                ' - Vence: ' + fechaCuota + ' (' + cuota.estado + ')' +
                                '</div>';
                        });
                        cuotasHTML += '</div>';
                    }
                    
                    // BOTONES DE ACCIÓN
                    var botonesAccion = '';
                    
                    // IMPRIMIR: Disponible para TODAS las ventas con comprobante (activas Y anuladas)
                    if (venta.tipo_comprobante) {
                        botonesAccion += '<a href="imprimir_comprobante.php?id_venta=' + venta.id + '&tipo=' + venta.tipo_comprobante + '" target="_blank" class="imprimir-link">🖨️ IMPRIMIR</a>';
                    }
                    
                    // ANULAR: Solo para ventas ACTIVAS
                    if (venta.estado_venta == 1) {
                        botonesAccion += '<a href="frm_confirmar_anulacion.php?id=' + venta.id + '" class="anular-link">❌ ANULAR</a>';
                    }

                    // Info de anulación (si está anulada)
                    var infoAnulacion = '';
                    if (venta.estado_venta == 0 && venta.fecha_anulacion) {
                        var fechaAnul = venta.fecha_anulacion.includes(':') ? new Date(venta.fecha_anulacion) : new Date(venta.fecha_anulacion + 'T00:00:00');
                        var fechaAnulStr = fechaAnul.toLocaleString('es-PY', {
                            day: '2-digit',
                            month: '2-digit', 
                            year: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                        
                        infoAnulacion = '<div style="background: rgba(231, 76, 60, 0.1); padding: 10px; border-radius: 6px; margin-top: 10px; border: 1px solid rgba(231, 76, 60, 0.3);">' +
                            '<strong style="color: #e74c3c;">⚠️ VENTA ANULADA</strong><br>' +
                            '<small style="color: rgba(255,255,255,0.8);">Fecha: ' + fechaAnulStr + '</small><br>' +
                            (venta.usuario_anula ? '<small style="color: rgba(255,255,255,0.8);">Usuario: ' + venta.usuario_anula + '</small><br>' : '') +
                            (venta.motivo_anulacion ? '<small style="color: rgba(255,255,255,0.8);">Motivo: ' + venta.motivo_anulacion + '</small>' : '') +
                            '</div>';
                    }

                    ventasHTML += '<tr>' +
                        '<td><strong>#' + venta.id + '</strong>' + comprobanteBadge + '</td>' +
                        '<td>' + fechaFormateada + '</td>' +
                        '<td>' + nombreCliente + '</td>' +
                        '<td><span class="condicion-badge condicion-' + venta.condicion_venta.toLowerCase() + '">' + venta.condicion_venta + '</span></td>' +
                        '<td>₲ ' + parseFloat(venta.total_venta).toLocaleString('es-PY', {minimumFractionDigits: 2}) + '</td>' +
                        '<td><span class="' + estadoClass + '">' + estadoText + '</span></td>' +
                        '<td><button class="expand-btn" onclick="toggleDetails(' + index + ')">Ver Detalle ▼</button></td>' +
                        '</tr>' +
                        '<tr class="expandable-row" id="details-' + index + '">' +
                        '<td colspan="7">' +
                        '<div class="details-container">' +
                        '<div class="details-title">Venta #' + venta.id + ' ' + (venta.tipo_comprobante ? '(' + venta.tipo_comprobante + ': ' + (venta.numero_venta || 'N/A') + ')' : '') + '</div>' +
                        '<div class="resumen-venta">' +
                        '<p><strong>Cliente:</strong> ' + nombreCliente + '</p>' +
                        '<p><strong>Fecha:</strong> ' + fechaFormateada + '</p>' +
                        '<p><strong>Tipo Comprobante:</strong> ' + (venta.tipo_comprobante || 'Sin comprobante') + '</p>' +
                        '<p><strong>Número:</strong> ' + (venta.numero_venta || 'N/A') + '</p>' +
                        '<p><strong>Condición:</strong> ' + venta.condicion_venta + '</p>' +
                        '<p><strong>Items:</strong> ' + (venta.detalles ? venta.detalles.length : 0) + ' (' + countProductos + ' productos, ' + countServicios + ' servicios)</p>' +
                        '<p><strong>Subtotal:</strong> ₲ ' + parseFloat(venta.subtotal).toLocaleString('es-PY', {minimumFractionDigits: 2}) + '</p>' +
                        '<p><strong>Descuento:</strong> ₲ ' + parseFloat(venta.descuento).toLocaleString('es-PY', {minimumFractionDigits: 2}) + '</p>' +
                        '<p style="font-size: 1rem;"><strong>TOTAL:</strong> <strong style="color: #27ae60;">₲ ' + parseFloat(venta.total_venta).toLocaleString('es-PY', {minimumFractionDigits: 2}) + '</strong></p>' +
                        (venta.observaciones ? '<p><strong>Observaciones:</strong> ' + venta.observaciones + '</p>' : '') +
                        '</div>' +
                        infoAnulacion +
                        cuotasHTML +
                        '<div style="margin-top: 15px;"><strong style="color: #f1c40f;">Items Vendidos:</strong></div>' +
                        '<table class="details-table" style="margin-top: 10px; width: 100%; border-radius: 8px; overflow: hidden;">' +
                        '<thead><tr><th>Tipo</th><th>Descripción</th><th>Cantidad</th><th>Precio Unit.</th><th>Subtotal</th></tr></thead>' +
                        '<tbody>' + detallesHTML + '</tbody>' +
                        '</table>' +
                        '<div style="margin-top: 15px; text-align: right;">' + botonesAccion + '</div>' +
                        '</div>' +
                        '</td></tr>';
                });
            } else {
                ventasHTML = '<tr><td colspan="7" class="no-results">No se encontraron ventas</td></tr>';
            }

            var clientesOptions = '<option value="todos">-- TODOS --</option><option value="sin_cliente">SIN CLIENTE</option>';
            clientes.forEach(function(cli) {
                var selected = '<?php echo $cliente; ?>' == cli.id ? 'selected' : '';
                clientesOptions += '<option value="' + cli.id + '" ' + selected + '>' + cli.nombre_cliente + ' ' + cli.apellido_cliente + '</option>';
            });

            var contentHTML = '<div class="list-container">' +
                '<h1 class="list-title">💰 Historial de Ventas</h1>' +
                '<div class="filter-container">' +
                '<form method="GET" action="">' +
                '<label class="label">Filtrar Ventas</label>' +
                '<div class="search-controls">' +
                '<div class="search-field"><label>Desde:</label><input type="date" name="fecha_desde" class="search-input" value="<?php echo $fecha_desde; ?>"></div>' +
                '<div class="search-field"><label>Hasta:</label><input type="date" name="fecha_hasta" class="search-input" value="<?php echo $fecha_hasta; ?>"></div>' +
                '<div class="search-field"><label>Cliente:</label><div class="select"><select name="cliente" class="search-input">' + clientesOptions + '</select></div></div>' +
                '<div class="search-field"><label>Condición:</label><div class="select"><select name="condicion" class="search-input"><option value="todos">-- TODOS --</option><option value="CONTADO">CONTADO</option><option value="CREDITO">CRÉDITO</option></select></div></div>' +
                '<div class="search-field"><label>Estado:</label><div class="select"><select name="estado" class="search-input"><option value="99">-- TODOS --</option><option value="1" selected>ACTIVA</option><option value="0">ANULADA</option></select></div></div>' +
                '<div class="search-field"><label>Ordenar:</label><div class="select"><select name="orden" class="search-input"><option value="fecha_desc">Fecha Reciente</option><option value="fecha_asc">Fecha Antigua</option><option value="total_desc">Total Mayor</option><option value="total_asc">Total Menor</option></select></div></div>' +
                '<div class="search-field"><button type="submit" class="button" style="margin-top: 22px;">Filtrar</button></div>' +
                '</div></form></div>' +
                '<div style="overflow-x: auto;">' +
                '<table class="table is-fullwidth custom-table">' +
                '<thead><tr><th>ID</th><th>FECHA</th><th>CLIENTE</th><th>CONDICIÓN</th><th>TOTAL</th><th>ESTADO</th><th>ACCIONES</th></tr></thead>' +
                '<tbody>' + ventasHTML + '</tbody>' +
                '</table></div>' +
                '<div style="text-align: center; margin-top: 25px;">' +
                '<a href="./frm_registrar_venta.php" class="button">➕ Registrar Nueva Venta</a>' +
                '</div></div>';
            
            mainContent.innerHTML = contentHTML;
            
            window.toggleDetails = function(index) {
                var expandableRow = document.getElementById('details-' + index);
                if (expandableRow) {
                    expandableRow.classList.toggle('show');
                }
            };
        });
    </script>
</body>
</html>