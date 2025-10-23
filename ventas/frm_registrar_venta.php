<?php
session_start();
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

include_once "../db.php";

$sentenciaClientes = $conexion->prepare("SELECT id, nombre_cliente, apellido_cliente, ci_ruc_cliente FROM clientes WHERE estado_cliente = 1 ORDER BY nombre_cliente ASC");
$sentenciaClientes->execute();
$clientes = $sentenciaClientes->fetchAll(PDO::FETCH_OBJ);

$sentenciaProductos = $conexion->prepare("SELECT id, nombre_producto, codigo_producto, precio_venta, stock_actual FROM productos WHERE estado_producto = 1 ORDER BY nombre_producto ASC");
$sentenciaProductos->execute();
$productos = $sentenciaProductos->fetchAll(PDO::FETCH_OBJ);

$sentenciaServicios = $conexion->prepare("SELECT id, nombre_servicio, categoria_servicio, precio_sugerido FROM servicios WHERE estado_servicio = 1 ORDER BY nombre_servicio ASC");
$sentenciaServicios->execute();
$servicios = $sentenciaServicios->fetchAll(PDO::FETCH_OBJ);

$SERIE_DEFAULT = '001-001';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registrar Venta</title>
    <link href="../css/bulma.min.css" rel="stylesheet">
    <link href="../css/formularios.css" rel="stylesheet">
    <style>
        .main-content {
            background: #2c3e50 !important;
            color: white;
        }

        .item-selector {
            background: rgba(0,0,0,0.2);
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            border: 1px solid rgba(241, 196, 15, 0.2);
        }

        .item-selector label.label {
            color: #f1c40f !important;
            font-weight: bold;
            font-size: 1.1rem;
        }

        .items-table {
            width: 100%;
            background: rgba(0,0,0,0.3);
            border-radius: 10px;
            overflow: hidden;
            margin-top: 20px;
        }

        .items-table table {
            width: 100%;
            color: white;
        }

        .items-table thead {
            background: linear-gradient(45deg, #f39c12, #f1c40f);
        }

        .items-table thead th {
            color: #2c3e50;
            font-weight: bold;
            padding: 12px 8px;
            text-align: center;
            font-size: 0.9rem;
        }

        .items-table tbody tr {
            background: rgba(255,255,255,0.05);
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .items-table tbody tr:hover {
            background: rgba(241, 196, 15, 0.1);
        }

        .items-table tbody td {
            padding: 10px 8px;
            text-align: center;
            font-size: 0.9rem;
        }

        .btn-remove {
            background: linear-gradient(45deg, #e74c3c, #c0392b);
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            font-size: 0.8rem;
            transition: all 0.3s ease;
        }

        .btn-remove:hover {
            background: linear-gradient(45deg, #c0392b, #e74c3c);
            transform: translateY(-2px);
        }

        .credit-panel {
            background: rgba(52, 152, 219, 0.1);
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            border: 2px solid rgba(52, 152, 219, 0.3);
            display: none;
        }

        .credit-panel.active {
            display: block;
            animation: slideDown 0.3s ease-out;
        }

        .credit-panel label.label {
            color: #3498db !important;
            font-weight: bold;
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

        .add-item-btn {
            background: linear-gradient(45deg, #27ae60, #2ecc71) !important;
            color: white !important;
            border: none !important;
            padding: 10px 20px !important;
            border-radius: 8px !important;
            font-weight: bold !important;
            cursor: pointer;
            transition: all 0.3s ease !important;
        }

        .add-item-btn:hover {
            background: linear-gradient(45deg, #2ecc71, #27ae60) !important;
            transform: translateY(-2px);
        }

        .add-item-btn:disabled {
            background: rgba(128, 128, 128, 0.5) !important;
            cursor: not-allowed;
            opacity: 0.5;
        }

        .stock-info {
            font-size: 0.8rem;
            color: rgba(255,255,255,0.7);
            font-style: italic;
            margin-top: 4px;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: rgba(255,255,255,0.6);
            font-style: italic;
        }

        .serie-display {
            color: rgba(255,255,255,0.7);
            font-style: italic;
            font-size: 0.9rem;
            margin-top: 8px;
        }
    </style>
</head>
<body>
    <?php include '../menu.php'; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const mainContent = document.querySelector('.main-content');
        if (!mainContent) {
            console.error('❌ No se encontró .main-content - revisa el menu.php');
            return;
        }

        const clientes = <?php echo json_encode($clientes); ?>;
        const productos = <?php echo json_encode($productos); ?>;
        const servicios = <?php echo json_encode($servicios); ?>;
        const IVA_RATE = 10.0;
        const SERIE_DEFAULT = "<?php echo $SERIE_DEFAULT; ?>";

        let selectedItems = [];

        // Helpers
        const $ = (id) => document.getElementById(id);
        const escapeHtml = (text) => {
            if (!text) return '';
            return text.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        };

        const formHTML = `
            <div class="form-container">
                <h1 class="form-title">💰 Registrar Venta</h1>

                <form id="formVenta" action="./guardar_venta.php" method="post" onsubmit="return validateForm()">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                    <div class="columns">
                        <div class="column is-6">
                            <div class="field">
                                <label class="label">Cliente (Opcional)</label>
                                <div class="control" style="display: flex; gap: 8px;">
                                    <div class="select is-fullwidth" style="flex: 1;">
                                        <select name="id_cliente" id="id_cliente">
                                            <option value="">-- Sin cliente (venta genérica) --</option>
                                            ${clientes.map(c => `<option value="${c.id}">${c.nombre_cliente} ${c.apellido_cliente} (${c.ci_ruc_cliente})</option>`).join('')}
                                        </select>
                                    </div>
                                    <a href="../clientes/frm_guardar_cliente.php" target="_blank" class="button is-small" style="background: linear-gradient(45deg, #27ae60, #2ecc71) !important; color: white !important; border: none !important; padding: 0 15px; height: 42px; display: flex; align-items: center; font-weight: bold; text-decoration: none;">
                                        ➕ Nuevo
                                    </a>
                                </div>
                            </div>

                            <div class="field">
                                <label class="label">RUC / Cédula</label>
                                <div class="control">
                                    <input class="input" type="text" name="cliente_ruc_ci" id="cliente_ruc_ci" placeholder="RUC o Cédula del cliente">
                                </div>
                                <p class="help" style="color: rgba(255,255,255,0.7);">Obligatorio si seleccionas FACTURA</p>
                            </div>

                            <div class="field">
                                <label class="label">Nombre o Razón Social</label>
                                <div class="control">
                                    <input class="input" type="text" name="cliente_nombre" id="cliente_nombre" placeholder="Nombre del cliente o razón social">
                                </div>
                            </div>

                            <div class="field">
                                <label class="label">Fecha de Venta *</label>
                                <div class="control">
                                    <input class="input" type="date" name="fecha_venta" id="fecha_venta" value="${new Date().toISOString().split('T')[0]}" required>
                                </div>
                            </div>
                        </div>

                        <div class="column is-6">
                            <div class="field">
                                <label class="label">Número (Factura/Ticket)</label>
                                <div class="control">
                                    <input class="input" type="text" name="numero_venta" id="numero_venta" placeholder="Ej: 0000123">
                                </div>
                                <p class="serie-display">Serie: <strong>${SERIE_DEFAULT}</strong></p>
                                <input type="hidden" name="numero_factura" id="numero_factura_hidden" value="">
                            </div>

                            <div class="field">
                                <label class="label">Tipo de Comprobante</label>
                                <div class="control">
                                    <div class="select is-fullwidth">
                                        <select name="tipo_comprobante" id="tipo_comprobante">
                                            <option value="">-- Ninguno --</option>
                                            <option value="TICKET">TICKET (venta simple)</option>
                                            <option value="FACTURA">FACTURA (requiere RUC)</option>
                                        </select>
                                    </div>
                                </div>
                                <p class="help" style="color: rgba(255,255,255,0.7);">FACTURA requiere RUC/Cédula del cliente</p>
                            </div>

                            <div class="field">
                                <label class="label">Observaciones</label>
                                <div class="control">
                                    <textarea class="textarea" name="observaciones" id="observaciones" rows="3" placeholder="Notas..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="columns">
                        <div class="column is-4">
                            <div class="field">
                                <label class="label">Condición de venta</label>
                                <div class="control">
                                    <div class="select is-fullwidth">
                                        <select name="condicion_venta" id="condicion_venta">
                                            <option value="CONTADO">CONTADO</option>
                                            <option value="CREDITO">CRÉDITO</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="column is-4">
                            <div class="field">
                                <label class="label">Forma de pago</label>
                                <div class="control">
                                    <div class="select is-fullwidth">
                                        <select name="forma_pago" id="forma_pago">
                                            <option value="CONTADO">Contado</option>
                                            <option value="FIADO">Fiado</option>
                                            <option value="TARJETA">Tarjeta</option>
                                            <option value="TRANSFERENCIA">Transferencia</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="column is-4">
                            <div class="field">
                                <label class="label">Descuento</label>
                                <div class="control">
                                    <input type="number" step="0.01" min="0" class="input" id="input-descuento" name="descuento" value="0.00">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Panel crédito -->
                    <div id="creditPanel" class="credit-panel">
                        <label class="label">💳 Condiciones de Crédito</label>
                        <div class="columns">
                            <div class="column is-4">
                                <div class="field">
                                    <label class="label">Número de Cuotas</label>
                                    <div class="control">
                                        <input type="number" min="1" class="input" id="input-cuotas" name="cuotas" value="1">
                                    </div>
                                </div>
                            </div>
                            <div class="column is-4">
                                <div class="field">
                                    <label class="label">Monto por Cuota</label>
                                    <div class="control">
                                        <div style="background: rgba(236, 240, 241, 0.1); border: 2px solid rgba(52, 152, 219, 0.3); padding: 10px; border-radius: 8px; text-align: center; font-weight: bold; color: #3498db;">
                                            <span id="monto-cuota-display">₲ 0.00</span>
                                        </div>
                                        <input type="hidden" name="monto_por_cuota" id="input-monto-cuota">
                                    </div>
                                    <p class="help" style="color: rgba(255,255,255,0.7); margin-top: 8px;">Se calcula automáticamente</p>
                                </div>
                            </div>
                            <div class="column is-4">
                                <div class="field">
                                    <label class="label">Vencimiento 1ra Cuota</label>
                                    <div class="control">
                                        <input type="date" class="input" id="fecha_vencimiento_primera" name="fecha_vencimiento_primera">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Selector de items -->
                    <div class="item-selector">
                        <label class="label">➕ Agregar Item a la Venta</label>
                        <div class="columns">
                            <div class="column is-3">
                                <div class="field">
                                    <label class="label" style="font-size: 0.9rem;">Tipo</label>
                                    <div class="control">
                                        <div class="select is-fullwidth">
                                            <select id="select-tipo">
                                                <option value="">-- Seleccionar --</option>
                                                <option value="PRODUCTO">Producto</option>
                                                <option value="SERVICIO">Servicio</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="column is-4">
                                <div class="field">
                                    <label class="label" style="font-size: 0.9rem;">Item</label>
                                    <div class="control">
                                        <div class="select is-fullwidth">
                                            <select id="select-item" disabled>
                                                <option value="">-- Primero selecciona el tipo --</option>
                                            </select>
                                        </div>
                                    </div>
                                    <p id="stock-info" class="stock-info"></p>
                                </div>
                            </div>

                            <div class="column is-2">
                                <div class="field">
                                    <label class="label" style="font-size: 0.9rem;">Cantidad</label>
                                    <div class="control">
                                        <input type="number" min="1" class="input" id="input-cantidad" value="1" disabled>
                                    </div>
                                </div>
                            </div>

                            <div class="column is-2">
                                <div class="field">
                                    <label class="label" style="font-size: 0.9rem;">Precio Unit.</label>
                                    <div class="control">
                                        <input type="number" step="0.01" min="0" class="input" id="input-precio" placeholder="0.00" disabled>
                                    </div>
                                </div>
                            </div>

                            <div class="column is-1">
                                <div class="field">
                                    <label class="label" style="font-size: 0.9rem;">&nbsp;</label>
                                    <div class="control">
                                        <button type="button" class="add-item-btn" id="btn-add-item" disabled>➕</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de items -->
                    <div id="items-table-container"></div>

                    <!-- Totales -->
                    <div id="totals-container" class="total-section" style="display: none;">
                        <label style="text-align: center; color: #27ae60; display: block; margin-bottom: 20px;">TOTALES DE LA VENTA</label>
                        <div class="columns">
                            <div class="column is-6">
                                <div><strong>Subtotal:</strong> <span id="subtotal-amount">₲ 0.00</span></div>
                                <div><strong>IVA (10%):</strong> <span id="iva-amount">₲ 0.00</span></div>
                                <div><strong>Descuento:</strong> <span id="descuento-amount">₲ 0.00</span></div>
                            </div>
                            <div class="column is-6" style="text-align: right;">
                                <div style="font-size: 1.5rem; color: #27ae60;"><strong>TOTAL: <span id="total-amount">₲ 0.00</span></strong></div>
                            </div>
                        </div>

                        <input type="hidden" name="subtotal" id="input-subtotal" value="0.00">
                        <input type="hidden" name="total_venta" id="input-total" value="0.00">
                    </div>

                    <!-- Botones -->
                    <div class="field is-grouped" style="justify-content: center; margin-top: 30px;">
                        <div class="control">
                            <button type="submit" class="button">💾 Guardar Venta</button>
                        </div>
                        <div class="control">
                            <a href="./listado_ventas.php" class="secondary-button">📋 Ver Listado</a>
                        </div>
                    </div>
                </form>
            </div>
        `;

        mainContent.innerHTML = formHTML;

        // Event listeners
        $('id_cliente').addEventListener('change', function(){
            const id = this.value;
            if (!id) return;
            const client = clientes.find(c => c.id == id);
            if (client) {
                $('cliente_ruc_ci').value = client.ci_ruc_cliente || '';
                $('cliente_nombre').value = (client.nombre_cliente + ' ' + client.apellido_cliente).trim();
            }
        });

        // Tipo comprobante change - Si es FACTURA, RUC es obligatorio
        $('tipo_comprobante').addEventListener('change', function(){
            const tipo = this.value;
            const rucInput = $('cliente_ruc_ci');
            const numInput = $('numero_venta');

            if (tipo === 'FACTURA') {
                numInput.value = '';
                numInput.placeholder = 'Se asignará automáticamente';
                numInput.disabled = true;
                rucInput.required = true;
                rucInput.style.borderColor = '#f1c40f';
            } else {
                numInput.disabled = false;
                numInput.placeholder = 'Ej: 0000123';
                rucInput.required = false;
                rucInput.style.borderColor = '';
            }
        });

        $('condicion_venta').addEventListener('change', function(){
            const panel = $('creditPanel');
            if (this.value === 'CREDITO') {
                panel.classList.add('active');
                $('input-cuotas').required = true;
                $('fecha_vencimiento_primera').required = true;
            } else {
                panel.classList.remove('active');
                $('input-cuotas').required = false;
                $('fecha_vencimiento_primera').required = false;
            }
        });

        function refreshItemSelectorOptions() {
            const tipo = $('select-tipo').value;
            const sel = $('select-item');
            sel.innerHTML = '<option value="">-- Seleccionar --</option>';
            
            if (!tipo) {
                sel.disabled = true;
                $('input-cantidad').disabled = true;
                $('input-precio').disabled = true;
                $('btn-add-item').disabled = true;
                return;
            }

            sel.disabled = false;
            $('input-cantidad').disabled = false;
            $('input-precio').disabled = (tipo === 'PRODUCTO');

            if (tipo === 'PRODUCTO') {
                productos.forEach(p => {
                    const already = selectedItems.some(si => si.tipo === 'PRODUCTO' && si.id == p.id);
                    if (!already) {
                        sel.innerHTML += `<option value="${p.id}" data-precio="${p.precio_venta}" data-stock="${p.stock_actual}">${p.nombre_producto} ${p.codigo_producto ? '('+p.codigo_producto+')' : ''}</option>`;
                    }
                });
            } else {
                servicios.forEach(s => {
                    const already = selectedItems.some(si => si.tipo === 'SERVICIO' && si.id == s.id);
                    if (!already) {
                        sel.innerHTML += `<option value="${s.id}" data-precio="${s.precio_sugerido || 0}">${s.nombre_servicio} (${s.categoria_servicio})</option>`;
                    }
                });
            }

            $('input-precio').value = '';
            $('input-cantidad').value = '1';
            $('stock-info').textContent = '';
            $('btn-add-item').disabled = true;

            sel.onchange = function(e) {
                const val = e.target.value;
                if (!val) {
                    $('btn-add-item').disabled = true;
                    $('stock-info').textContent = '';
                    $('input-precio').value = '';
                    return;
                }
                $('btn-add-item').disabled = false;
                
                if (tipo === 'PRODUCTO') {
                    const option = e.target.options[e.target.selectedIndex];
                    const precio = option.getAttribute('data-precio');
                    const stock = option.getAttribute('data-stock');
                    $('input-precio').value = parseFloat(precio).toFixed(2);
                    $('stock-info').textContent = `Stock disponible: ${stock}`;
                    if (parseInt(stock) <= 0) {
                        $('stock-info').classList.add('has-text-danger');
                        $('btn-add-item').disabled = true;
                    } else {
                        $('stock-info').classList.remove('has-text-danger');
                    }
                } else {
                    // Para servicios: tomar precio sugerido pero permitir edición
                    const option = e.target.options[e.target.selectedIndex];
                    const precioSugerido = parseFloat(option.getAttribute('data-precio')) || 0;
                    $('input-precio').value = precioSugerido > 0 ? precioSugerido.toFixed(2) : '';
                    $('stock-info').textContent = precioSugerido > 0 ? 'Precio sugerido (modificable)' : 'Ingrese precio del servicio';
                }
            };
        }

        $('select-tipo').addEventListener('change', refreshItemSelectorOptions);

        $('btn-add-item').addEventListener('click', function(){
            const tipo = $('select-tipo').value;
            const itemId = $('select-item').value;
            const cantidad = parseFloat($('input-cantidad').value) || 0;
            const precio = parseFloat($('input-precio').value) || 0;

            if (!tipo || !itemId || cantidad <= 0 || precio <= 0) {
                alert('Completa todos los campos');
                return;
            }

            if (tipo === 'PRODUCTO') {
                const prod = productos.find(p => p.id == itemId);
                if (cantidad > parseFloat(prod.stock_actual)) { 
                    alert('Stock insuficiente'); 
                    return; 
                }
            }

            const descripcion = (tipo === 'PRODUCTO') 
                ? productos.find(p => p.id == itemId).nombre_producto 
                : servicios.find(s => s.id == itemId).nombre_servicio;
            const codigo = (tipo === 'PRODUCTO') 
                ? (productos.find(p => p.id == itemId).codigo_producto || '-') 
                : '-';

            const subtotal = parseFloat((cantidad * precio).toFixed(2));
            selectedItems.push({
                tipo, id: itemId, descripcion, codigo, cantidad, precio_unitario: precio, subtotal
            });

            renderItemsTable();
            refreshItemSelectorOptions();
            recalcularTotales();
        });

        function renderItemsTable(){
            const container = $('items-table-container');
            if (selectedItems.length === 0) {
                container.innerHTML = '<div class="empty-state">No hay items agregados a la venta</div>';
                $('totals-container').style.display = 'none';
                return;
            }

            let html = `
                <div class="items-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Item</th>
                                <th>Código</th>
                                <th>Cantidad</th>
                                <th>Precio Unit.</th>
                                <th>Subtotal</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            selectedItems.forEach((it, idx) => {
                html += `
                    <tr>
                        <td>${it.tipo}</td>
                        <td>${it.descripcion}</td>
                        <td>${it.codigo}</td>
                        <td>${it.cantidad}</td>
                        <td>₲ ${parseFloat(it.precio_unitario).toLocaleString('es-PY', {minimumFractionDigits:2})}</td>
                        <td><strong>₲ ${parseFloat(it.subtotal).toLocaleString('es-PY', {minimumFractionDigits:2})}</strong></td>
                        <td>
                            <button type="button" class="btn-remove" onclick="removeItem(${idx})">🗑️ Quitar</button>
                            <input type="hidden" name="items[${idx}][tipo]" value="${it.tipo}">
                            <input type="hidden" name="items[${idx}][id]" value="${it.id}">
                            <input type="hidden" name="items[${idx}][descripcion]" value="${escapeHtml(it.descripcion)}">
                            <input type="hidden" name="items[${idx}][cantidad]" value="${it.cantidad}">
                            <input type="hidden" name="items[${idx}][precio]" value="${it.precio_unitario}">
                        </td>
                    </tr>
                `;
            });

            html += `
                        </tbody>
                    </table>
                </div>
            `;

            container.innerHTML = html;
            $('totals-container').style.display = 'block';
        }

        window.removeItem = function(index){
            selectedItems.splice(index, 1);
            renderItemsTable();
            refreshItemSelectorOptions();
            recalcularTotales();
        };

        function recalcularTotales(){
            let subtotal = 0;
            selectedItems.forEach(it => subtotal += parseFloat(it.subtotal || 0));
            const descuento = parseFloat($('input-descuento').value) || 0;
            const subtotalAfterDiscount = Math.max(0, subtotal - descuento);
            const iva = parseFloat((subtotalAfterDiscount * (IVA_RATE/100)).toFixed(2));
            const total = parseFloat((subtotalAfterDiscount + iva).toFixed(2));

            $('subtotal-amount').textContent = '₲ ' + subtotal.toLocaleString('es-PY', {minimumFractionDigits:2});
            $('iva-amount').textContent = '₲ ' + iva.toLocaleString('es-PY', {minimumFractionDigits:2});
            $('descuento-amount').textContent = '₲ ' + descuento.toLocaleString('es-PY', {minimumFractionDigits:2});
            $('total-amount').textContent = '₲ ' + total.toLocaleString('es-PY', {minimumFractionDigits:2});

            $('input-subtotal').value = subtotal.toFixed(2);
            $('input-total').value = total.toFixed(2);

            if ($('condicion_venta').value === 'CREDITO') {
                const num = parseInt($('input-cuotas').value) || 1;
                const cuota = (total / num).toFixed(2);
                $('monto-cuota-display').textContent = '₲ ' + parseFloat(cuota).toLocaleString('es-PY', {minimumFractionDigits:2});
                $('input-monto-cuota').value = cuota;
            }
        }

        window.validateForm = function() {
            if (selectedItems.length === 0) {
                alert('Debes agregar al menos un item');
                return false;
            }

            const tipoComp = $('tipo_comprobante').value;
            if (tipoComp === 'FACTURA') {
                const ruc = $('cliente_ruc_ci').value.trim();
                if (!ruc) {
                    alert('RUC/Cédula obligatorio para FACTURA');
                    return false;
                }
            }

            const cond = $('condicion_venta').value;
            if (cond === 'CREDITO') {
                const fecha = $('fecha_vencimiento_primera').value;
                const cuotas = parseInt($('input-cuotas').value) || 0;
                if (!fecha || cuotas <= 0) {
                    alert('Completa fecha de vencimiento y cuotas para crédito');
                    return false;
                }
            }

            recalcularTotales();
            return confirm('¿Confirmar registro de venta? Esto actualizará el stock.');
        };

        // Inicializar
        refreshItemSelectorOptions();
        renderItemsTable();
        
        $('input-descuento').addEventListener('change', recalcularTotales);
        $('input-cuotas').addEventListener('input', recalcularTotales);
    });
    </script>

</body>
</html>