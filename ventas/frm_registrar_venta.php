<?php
include_once "../db.php";

// Obtener clientes activos
$sentenciaClientes = $conexion->prepare("SELECT id, nombre_cliente, apellido_cliente, ci_ruc_cliente FROM clientes WHERE estado_cliente = 1 ORDER BY nombre_cliente ASC");
$sentenciaClientes->execute();
$clientes = $sentenciaClientes->fetchAll(PDO::FETCH_OBJ);
$clientesJSON = json_encode($clientes);

// Obtener productos activos con stock
$sentenciaProductos = $conexion->prepare("SELECT id, nombre_producto, codigo_producto, precio_venta, stock_actual FROM productos WHERE estado_producto = 1 AND stock_actual > 0 ORDER BY nombre_producto ASC");
$sentenciaProductos->execute();
$productos = $sentenciaProductos->fetchAll(PDO::FETCH_OBJ);
$productosJSON = json_encode($productos);

// Obtener servicios activos
$sentenciaServicios = $conexion->prepare("SELECT id, nombre_servicio, categoria_servicio FROM servicios WHERE estado_servicio = 1 ORDER BY nombre_servicio ASC");
$sentenciaServicios->execute();
$servicios = $sentenciaServicios->fetchAll(PDO::FETCH_OBJ);
$serviciosJSON = json_encode($servicios);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Venta</title>
    <link href="../css/bulma.min.css" rel="stylesheet">
    <link href="../css/formularios.css" rel="stylesheet">
    
    <style>
        .main-content {
            background: #2c3e50 !important;
            color: white;
        }

        .products-table {
            width: 100%;
            background: rgba(0,0,0,0.3);
            border-radius: 10px;
            overflow: hidden;
            margin-top: 20px;
        }

        .products-table table {
            width: 100%;
            color: white;
        }

        .products-table thead {
            background: linear-gradient(45deg, #f39c12, #f1c40f);
        }

        .products-table thead th {
            color: #2c3e50;
            font-weight: bold;
            padding: 12px 8px;
            text-align: center;
            font-size: 0.9rem;
        }

        .products-table tbody tr {
            background: rgba(255,255,255,0.05);
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .products-table tbody tr:hover {
            background: rgba(241, 196, 15, 0.1);
        }

        .products-table tbody td {
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

        .total-section {
            background: rgba(39, 174, 96, 0.2);
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
            border: 2px solid rgba(39, 174, 96, 0.5);
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }

        .total-row:last-child {
            border-bottom: none;
            margin-top: 10px;
            padding-top: 15px;
            border-top: 2px solid rgba(39, 174, 96, 0.5);
        }

        .total-label {
            font-size: 1.1rem;
            color: #ecf0f1;
        }

        .total-amount {
            font-size: 1.8rem;
            font-weight: bold;
            color: #27ae60;
        }

        .item-selector {
            background: rgba(0,0,0,0.2);
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            border: 1px solid rgba(241, 196, 15, 0.2);
        }

        .add-product-btn {
            background: linear-gradient(45deg, #27ae60, #2ecc71) !important;
            color: white !important;
            border: none !important;
            padding: 10px 20px !important;
            border-radius: 8px !important;
            font-weight: bold !important;
            cursor: pointer;
            transition: all 0.3s ease !important;
            width: 100%;
            margin-top: 10px;
        }

        .add-product-btn:hover {
            background: linear-gradient(45deg, #2ecc71, #27ae60) !important;
            transform: translateY(-2px);
        }

        .add-product-btn:disabled {
            background: rgba(128, 128, 128, 0.5) !important;
            cursor: not-allowed;
            opacity: 0.5;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: rgba(255,255,255,0.6);
            font-style: italic;
        }

        .stock-info {
            font-size: 0.8rem;
            color: rgba(255,255,255,0.7);
            font-style: italic;
        }

        .stock-warning {
            color: #e74c3c;
            font-weight: bold;
        }

        .checkbox-container {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 15px;
            background: rgba(52, 152, 219, 0.1);
            border-radius: 8px;
            border: 2px solid rgba(52, 152, 219, 0.3);
            margin: 20px 0;
        }

        .checkbox-container input[type="checkbox"] {
            width: 20px;
            height: 20px;
            accent-color: #3498db;
            cursor: pointer;
        }

        .checkbox-container label {
            color: #3498db !important;
            font-weight: bold !important;
            cursor: pointer;
            margin: 0 !important;
        }

        .tipo-badge {
            padding: 4px 8px;
            border-radius: 10px;
            font-size: 0.75rem;
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
    </style>
</head>
<body>
    <?php include '../menu.php'; ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mainContent = document.querySelector('.main-content');
            const clientes = <?php echo $clientesJSON; ?>;
            const productos = <?php echo $productosJSON; ?>;
            const servicios = <?php echo $serviciosJSON; ?>;
            
            let selectedItems = [];
            let incluirServicios = false;

            function renderItemSelector() {
                const container = document.getElementById('item-selector-container');
                
                let html = `
                    <div class="item-selector">
                        <label class="label">Agregar Item a la Venta</label>
                        <div class="columns">
                            <div class="column is-4">
                                <div class="field">
                                    <label class="label" style="font-size: 0.9rem;">Tipo</label>
                                    <div class="select is-fullwidth">
                                        <select id="select-tipo" onchange="onTipoChange()">
                                            <option value="">-- Seleccionar --</option>
                                            <option value="PRODUCTO">Producto</option>
                                            ${incluirServicios ? '<option value="SERVICIO">Servicio</option>' : ''}
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="column is-4">
                                <div class="field">
                                    <label class="label" style="font-size: 0.9rem;">Item</label>
                                    <div class="select is-fullwidth">
                                        <select id="select-item" disabled>
                                            <option value="">-- Primero selecciona el tipo --</option>
                                        </select>
                                    </div>
                                    <p class="stock-info" id="stock-info"></p>
                                </div>
                            </div>
                            <div class="column is-2">
                                <div class="field">
                                    <label class="label" style="font-size: 0.9rem;">Cantidad</label>
                                    <input type="number" class="input" id="input-cantidad" min="1" value="1" placeholder="1" disabled>
                                </div>
                            </div>
                            <div class="column is-2">
                                <div class="field">
                                    <label class="label" style="font-size: 0.9rem;">Precio Unit.</label>
                                    <input type="number" step="0.01" class="input" id="input-precio" min="0" placeholder="0.00" disabled>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="add-product-btn" onclick="addItem()" id="btn-add-item" disabled>
                            ➕ Agregar Item
                        </button>
                    </div>
                `;
                
                container.innerHTML = html;
            }

            window.onTipoChange = function() {
                const tipo = document.getElementById('select-tipo').value;
                const selectItem = document.getElementById('select-item');
                const inputCantidad = document.getElementById('input-cantidad');
                const inputPrecio = document.getElementById('input-precio');
                const btnAdd = document.getElementById('btn-add-item');
                
                if (!tipo) {
                    selectItem.disabled = true;
                    inputCantidad.disabled = true;
                    inputPrecio.disabled = true;
                    btnAdd.disabled = true;
                    selectItem.innerHTML = '<option value="">-- Primero selecciona el tipo --</option>';
                    document.getElementById('stock-info').textContent = '';
                    return;
                }
                
                selectItem.disabled = false;
                inputCantidad.disabled = false;
                inputPrecio.disabled = false;
                
                let options = '<option value="">-- Seleccionar --</option>';
                
                if (tipo === 'PRODUCTO') {
                    productos.forEach(prod => {
                        const alreadyAdded = selectedItems.find(i => i.tipo === 'PRODUCTO' && i.id == prod.id);
                        if (!alreadyAdded) {
                            options += `<option value="${prod.id}" data-precio="${prod.precio_venta}" data-stock="${prod.stock_actual}">${prod.nombre_producto} ${prod.codigo_producto ? '(' + prod.codigo_producto + ')' : ''}</option>`;
                        }
                    });
                    inputPrecio.readOnly = true;
                } else if (tipo === 'SERVICIO') {
                    servicios.forEach(serv => {
                        const alreadyAdded = selectedItems.find(i => i.tipo === 'SERVICIO' && i.id == serv.id);
                        if (!alreadyAdded) {
                            options += `<option value="${serv.id}">${serv.nombre_servicio} (${serv.categoria_servicio})</option>`;
                        }
                    });
                    inputPrecio.readOnly = false;
                }
                
                selectItem.innerHTML = options;
                inputPrecio.value = '';
                inputCantidad.value = '1';
                document.getElementById('stock-info').textContent = '';
                btnAdd.disabled = true;
                
                selectItem.addEventListener('change', function(e) {
                    const itemId = e.target.value;
                    const btnAdd = document.getElementById('btn-add-item');
                    const stockInfo = document.getElementById('stock-info');
                    
                    if (itemId) {
                        if (tipo === 'PRODUCTO') {
                            const option = e.target.options[e.target.selectedIndex];
                            const precio = option.getAttribute('data-precio');
                            const stock = option.getAttribute('data-stock');
                            
                            inputPrecio.value = parseFloat(precio).toFixed(2);
                            stockInfo.textContent = `Stock disponible: ${stock} unidades`;
                            
                            if (parseInt(stock) === 0) {
                                stockInfo.classList.add('stock-warning');
                                stockInfo.textContent = '⚠️ Sin stock disponible';
                                btnAdd.disabled = true;
                            } else {
                                stockInfo.classList.remove('stock-warning');
                                btnAdd.disabled = false;
                            }
                        } else {
                            inputPrecio.value = '';
                            stockInfo.textContent = 'Ingresa el precio del servicio';
                            btnAdd.disabled = false;
                        }
                    } else {
                        inputPrecio.value = '';
                        stockInfo.textContent = '';
                        btnAdd.disabled = true;
                    }
                });
            };

            window.addItem = function() {
                const tipo = document.getElementById('select-tipo').value;
                const itemId = document.getElementById('select-item').value;
                const cantidad = parseInt(document.getElementById('input-cantidad').value);
                const precio = parseFloat(document.getElementById('input-precio').value);
                
                if (!tipo || !itemId || cantidad <= 0 || precio <= 0) {
                    alert('Por favor completa todos los campos correctamente');
                    return;
                }
                
                let itemData;
                let nombre;
                let codigo = '-';
                
                if (tipo === 'PRODUCTO') {
                    itemData = productos.find(p => p.id == itemId);
                    if (cantidad > itemData.stock_actual) {
                        alert(`Stock insuficiente. Disponible: ${itemData.stock_actual} unidades`);
                        return;
                    }
                    nombre = itemData.nombre_producto;
                    codigo = itemData.codigo_producto || '-';
                } else {
                    itemData = servicios.find(s => s.id == itemId);
                    nombre = itemData.nombre_servicio;
                }
                
                selectedItems.push({
                    tipo: tipo,
                    id: itemId,
                    nombre: nombre,
                    codigo: codigo,
                    cantidad: cantidad,
                    precio_unitario: precio,
                    subtotal: cantidad * precio
                });
                
                renderItemsTable();
                renderItemSelector();
                
                // Reset campos
                document.getElementById('select-tipo').value = '';
                document.getElementById('select-item').innerHTML = '<option value="">-- Primero selecciona el tipo --</option>';
                document.getElementById('select-item').disabled = true;
                document.getElementById('input-cantidad').value = '1';
                document.getElementById('input-cantidad').disabled = true;
                document.getElementById('input-precio').value = '';
                document.getElementById('input-precio').disabled = true;
                document.getElementById('stock-info').textContent = '';
                document.getElementById('btn-add-item').disabled = true;
            };

            window.removeItem = function(index) {
                selectedItems.splice(index, 1);
                renderItemsTable();
                renderItemSelector();
            };

            function renderItemsTable() {
                const container = document.getElementById('items-table-container');
                
                if (selectedItems.length === 0) {
                    container.innerHTML = '<div class="empty-state">No hay items agregados a la venta</div>';
                    document.getElementById('totals-container').style.display = 'none';
                    return;
                }
                
                let subtotal = 0;
                let html = `
                    <div class="products-table">
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
                
                selectedItems.forEach((item, index) => {
                    subtotal += item.subtotal;
                    const tipoBadge = item.tipo === 'PRODUCTO' ? 
                        '<span class="tipo-badge tipo-producto">PRODUCTO</span>' : 
                        '<span class="tipo-badge tipo-servicio">SERVICIO</span>';
                    
                    html += `
                        <tr>
                            <td>${tipoBadge}</td>
                            <td>${item.nombre}</td>
                            <td>${item.codigo}</td>
                            <td>${item.cantidad}</td>
                            <td>₲ ${item.precio_unitario.toLocaleString('es-PY', {minimumFractionDigits: 2})}</td>
                            <td><strong>₲ ${item.subtotal.toLocaleString('es-PY', {minimumFractionDigits: 2})}</strong></td>
                            <td>
                                <button type="button" class="btn-remove" onclick="removeItem(${index})">
                                    🗑️ Quitar
                                </button>
                                <input type="hidden" name="items[${index}][tipo]" value="${item.tipo}">
                                <input type="hidden" name="items[${index}][id]" value="${item.id}">
                                <input type="hidden" name="items[${index}][descripcion]" value="${item.nombre}">
                                <input type="hidden" name="items[${index}][cantidad]" value="${item.cantidad}">
                                <input type="hidden" name="items[${index}][precio]" value="${item.precio_unitario}">
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
                
                // Mostrar totales
                document.getElementById('totals-container').style.display = 'block';
                updateTotals(subtotal);
            }

            function updateTotals(subtotal) {
                const descuento = parseFloat(document.getElementById('input-descuento').value) || 0;
                const total = subtotal - descuento;
                
                document.getElementById('subtotal-amount').textContent = '₲ ' + subtotal.toLocaleString('es-PY', {minimumFractionDigits: 2});
                document.getElementById('total-amount').textContent = '₲ ' + total.toLocaleString('es-PY', {minimumFractionDigits: 2});
                document.getElementById('input-subtotal').value = subtotal.toFixed(2);
                document.getElementById('input-total').value = total.toFixed(2);
            }

            window.toggleServicios = function(checkbox) {
                incluirServicios = checkbox.checked;
                renderItemSelector();
            };

            window.onDescuentoChange = function() {
                const subtotal = parseFloat(document.getElementById('input-subtotal').value) || 0;
                updateTotals(subtotal);
            };

            window.validateForm = function() {
                if (selectedItems.length === 0) {
                    alert('Debes agregar al menos un item a la venta');
                    return false;
                }
                
                const fecha = document.getElementById('fecha_venta').value;
                if (!fecha) {
                    alert('Por favor ingresa la fecha de venta');
                    return false;
                }
                
                const total = parseFloat(document.getElementById('input-total').value);
                if (total <= 0) {
                    alert('El total de la venta debe ser mayor a 0');
                    return false;
                }
                
                return confirm('¿Confirmar registro de venta?\n\nEsto actualizará el stock de los productos.');
            };

            const formHTML = `
                <div class="form-container">
                    <h1 class="form-title">💰 Registrar Venta</h1>
                    
                    <form action="./guardar_venta.php" method="post" onsubmit="return validateForm()">
                        
                        <div class="columns">
                            <div class="column is-6">
                                <div class="field">
                                    <label class="label">Cliente (Opcional)</label>
                                    <div class="control">
                                        <div class="select is-fullwidth">
                                            <select name="id_cliente" id="id_cliente">
                                                <option value="">-- Sin cliente (venta genérica) --</option>
                                                ${clientes.map(c => `<option value="${c.id}">${c.nombre_cliente} ${c.apellido_cliente} (${c.ci_ruc_cliente})</option>`).join('')}
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="field">
                                    <label class="label">Fecha de Venta *</label>
                                    <div class="control">
                                        <input class="input" type="date" name="fecha_venta" id="fecha_venta" 
                                               value="${new Date().toISOString().split('T')[0]}" required>
                                    </div>
                                </div>
                            </div>

                            <div class="column is-6">
                                <div class="field">
                                    <label class="label">Número de Factura/Ticket</label>
                                    <div class="control">
                                        <input class="input" type="text" name="numero_venta" id="numero_venta" 
                                               placeholder="Ej: 001-001-0001234">
                                    </div>
                                </div>

                                <div class="field">
                                    <label class="label">Observaciones</label>
                                    <div class="control">
                                        <textarea class="textarea" name="observaciones" id="observaciones" 
                                                  rows="3" placeholder="Notas adicionales sobre la venta"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Checkbox para incluir servicios -->
                        <div class="checkbox-container">
                            <input type="checkbox" id="check-servicios" onchange="toggleServicios(this)">
                            <label for="check-servicios">¿Esta venta incluye servicios? (Marcar para habilitar)</label>
                        </div>

                        <!-- Selector de items -->
                        <div id="item-selector-container"></div>

                        <!-- Tabla de items seleccionados -->
                        <div id="items-table-container">
                            <div class="empty-state">No hay items agregados a la venta</div>
                        </div>

                        <!-- Totales -->
                        <div id="totals-container" class="total-section" style="display: none;">
                            <div class="total-row">
                                <span class="total-label">Subtotal:</span>
                                <span class="total-label" id="subtotal-amount">₲ 0.00</span>
                            </div>
                            <div class="total-row">
                                <span class="total-label">Descuento:</span>
                                <input type="number" step="0.01" min="0" class="input" 
                                       name="descuento" id="input-descuento" value="0" 
                                       onchange="onDescuentoChange()" 
                                       style="width: 200px; text-align: right;">
                            </div>
                            <div class="total-row">
                                <span class="total-label" style="font-size: 1.3rem;"><strong>TOTAL:</strong></span>
                                <span class="total-amount" id="total-amount">₲ 0.00</span>
                            </div>
                            <input type="hidden" name="subtotal" id="input-subtotal" value="0">
                            <input type="hidden" name="total_venta" id="input-total" value="0">
                        </div>

                        <!-- Botones -->
                        <div class="field is-grouped" style="justify-content: center; margin-top: 30px;">
                            <div class="control">
                                <button type="submit" class="button">
                                    💾 Guardar Venta
                                </button>
                            </div>
                            <div class="control">
                                <a href="./listado_ventas.php" class="secondary-button">
                                    📋 Ver Listado
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            `;
            
            mainContent.innerHTML = formHTML;
            renderItemSelector();
        });
    </script>
</body>
</html>