<?php
// Obtener proveedores activos
include_once "../db.php";

$sentenciaProveedores = $conexion->prepare("SELECT id, nombre_proveedor FROM proveedores WHERE estado_proveedor = 1 ORDER BY nombre_proveedor ASC");
$sentenciaProveedores->execute();
$proveedores = $sentenciaProveedores->fetchAll(PDO::FETCH_OBJ);
$proveedoresJSON = json_encode($proveedores);

// Obtener todos los productos activos
$sentenciaProductos = $conexion->prepare("SELECT id, nombre_producto, codigo_producto, stock_actual FROM productos WHERE estado_producto = 1 ORDER BY nombre_producto ASC");
$sentenciaProductos->execute();
$productos = $sentenciaProductos->fetchAll(PDO::FETCH_OBJ);
$productosJSON = json_encode($productos);

// Obtener relación proveedor-producto con precios
$sentenciaRelacion = $conexion->prepare("SELECT id_proveedor, id_producto, precio_compra FROM proveedor_producto");
$sentenciaRelacion->execute();
$relaciones = $sentenciaRelacion->fetchAll(PDO::FETCH_OBJ);
$relacionesJSON = json_encode($relaciones);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Compra</title>
    <link href="../css/bulma.min.css" rel="stylesheet">
    <link href="../css/formularios.css" rel="stylesheet">
    
    <style>
        .main-content {
            background: #2c3e50 !important;
            color: white;
        }

        .select select {
            background: rgba(236, 240, 241, 0.1) !important;
            border: 2px solid rgba(241, 196, 15, 0.3) !important;
            color: white !important;
            font-size: 1rem;
            padding: 10px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .select select option {
            background: #2c3e50 !important;
            color: white !important;
            padding: 8px !important;
        }

        .select select option:checked {
            background: linear-gradient(45deg, #f39c12, #f1c40f) !important;
            color: #2c3e50 !important;
            font-weight: bold !important;
        }

        .select select:focus {
            background: rgba(236, 240, 241, 0.15) !important;
            border-color: #f1c40f !important;
            box-shadow: 0 0 0 0.125em rgba(241, 196, 15, 0.25) !important;
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

        .total-amount {
            font-size: 2rem;
            font-weight: bold;
            color: #27ae60;
            text-align: center;
        }

        .product-selector {
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

        /* NUEVO: Sección de forma de pago */
        .payment-section {
            background: rgba(52, 152, 219, 0.1);
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            border: 2px solid rgba(52, 152, 219, 0.3);
        }

        .payment-section label.label {
            color: #3498db !important;
            font-weight: bold;
            font-size: 1.1rem;
        }

        .radio-group {
            display: flex;
            gap: 30px;
            margin: 15px 0;
        }

        .radio-option {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
        }

        .radio-option input[type="radio"] {
            width: 20px;
            height: 20px;
            accent-color: #3498db;
            cursor: pointer;
        }

        .radio-option label {
            color: white !important;
            font-size: 1rem;
            cursor: pointer;
            margin: 0 !important;
        }

        .credito-fields {
            display: none;
            margin-top: 15px;
            padding: 15px;
            background: rgba(0,0,0,0.2);
            border-radius: 8px;
            border: 1px solid rgba(52, 152, 219, 0.3);
        }

        .credito-fields.active {
            display: block;
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
            const proveedores = <?php echo $proveedoresJSON; ?>;
            const productos = <?php echo $productosJSON; ?>;
            const relaciones = <?php echo $relacionesJSON; ?>;
            
            let selectedProducts = [];
            let selectedProveedor = null;

            function getProveedorProductos(idProveedor) {
                return relaciones
                    .filter(rel => rel.id_proveedor == idProveedor)
                    .map(rel => {
                        const producto = productos.find(p => p.id == rel.id_producto);
                        return {
                            ...producto,
                            precio_compra: parseFloat(rel.precio_compra)
                        };
                    })
                    .filter(p => p !== undefined);
            }

            function onProveedorChange(idProveedor) {
                selectedProveedor = idProveedor;
                selectedProducts = [];
                renderProductsTable();
                renderProductSelector();
                
                const productosProveedor = getProveedorProductos(idProveedor);
            }

            function renderProductSelector() {
                const container = document.getElementById('product-selector-container');
                
                if (!selectedProveedor) {
                    container.innerHTML = '<p style="color: rgba(255,255,255,0.6); text-align: center; padding: 20px;">Primero selecciona un proveedor</p>';
                    return;
                }

                const productosProveedor = getProveedorProductos(selectedProveedor);
                
                if (productosProveedor.length === 0) {
                    container.innerHTML = `
                        <div style="background: rgba(231, 76, 60, 0.2); padding: 20px; border-radius: 10px; border: 2px solid rgba(231, 76, 60, 0.5); text-align: center;">
                            <p style="color: #e74c3c; font-weight: bold; margin-bottom: 10px;">⚠️ Sin productos asignados</p>
                            <p style="color: rgba(255,255,255,0.8); margin-bottom: 15px;">
                                Este proveedor no tiene productos asociados.
                            </p>
                            <a href="../productos/frm_guardar_producto.php" 
                               style="background: linear-gradient(45deg, #f39c12, #f1c40f); 
                                      color: #2c3e50; padding: 10px 20px; border-radius: 8px; 
                                      text-decoration: none; font-weight: bold; display: inline-block;">
                                ➕ Registrar Producto
                            </a>
                        </div>
                    `;
                    return;
                }

                let html = `
                    <div class="product-selector">
                        <label class="label">Agregar Producto a la Compra</label>
                        <div class="columns">
                            <div class="column is-5">
                                <div class="field">
                                    <label class="label" style="font-size: 0.9rem;">Producto</label>
                                    <div class="select is-fullwidth">
                                        <select id="select-producto">
                                            <option value="">-- Seleccionar --</option>
                `;
                
                productosProveedor.forEach(prod => {
                    const alreadyAdded = selectedProducts.find(p => p.id == prod.id);
                    if (!alreadyAdded) {
                        html += `<option value="${prod.id}">${prod.nombre_producto} ${prod.codigo_producto ? '(' + prod.codigo_producto + ')' : ''}</option>`;
                    }
                });
                
                html += `
                                        </select>
                                    </div>
                                    <p class="stock-info" id="stock-info"></p>
                                </div>
                            </div>
                            <div class="column is-3">
                                <div class="field">
                                    <label class="label" style="font-size: 0.9rem;">Cantidad</label>
                                    <input type="number" class="input" id="input-cantidad" min="1" value="1" placeholder="Cantidad">
                                </div>
                            </div>
                            <div class="column is-3">
                                <div class="field">
                                    <label class="label" style="font-size: 0.9rem;">Precio Unit.</label>
                                    <input type="number" step="0.01" class="input" id="input-precio" min="0" placeholder="0.00" readonly>
                                </div>
                            </div>
                            <div class="column is-1">
                                <div class="field">
                                    <label class="label" style="font-size: 0.9rem; opacity: 0;">.</label>
                                    <button type="button" class="add-product-btn" onclick="addProduct()" id="btn-add-product" disabled>
                                        ➕
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                container.innerHTML = html;
                
                document.getElementById('select-producto').addEventListener('change', function(e) {
                    const idProducto = e.target.value;
                    const btnAdd = document.getElementById('btn-add-product');
                    
                    if (idProducto) {
                        const producto = productosProveedor.find(p => p.id == idProducto);
                        document.getElementById('input-precio').value = producto.precio_compra.toFixed(2);
                        document.getElementById('stock-info').textContent = `Stock actual: ${producto.stock_actual} unidades`;
                        btnAdd.disabled = false;
                    } else {
                        document.getElementById('input-precio').value = '';
                        document.getElementById('stock-info').textContent = '';
                        btnAdd.disabled = true;
                    }
                });
            }

            window.addProduct = function() {
                const idProducto = document.getElementById('select-producto').value;
                const cantidad = parseInt(document.getElementById('input-cantidad').value);
                const precio = parseFloat(document.getElementById('input-precio').value);
                
                if (!idProducto || cantidad <= 0 || precio < 0) {
                    alert('Por favor completa todos los campos correctamente');
                    return;
                }
                
                const productosProveedor = getProveedorProductos(selectedProveedor);
                const producto = productosProveedor.find(p => p.id == idProducto);
                
                selectedProducts.push({
                    id: producto.id,
                    nombre: producto.nombre_producto,
                    codigo: producto.codigo_producto,
                    cantidad: cantidad,
                    precio_unitario: precio,
                    subtotal: cantidad * precio
                });
                
                renderProductsTable();
                renderProductSelector();
                
                document.getElementById('select-producto').value = '';
                document.getElementById('input-cantidad').value = '1';
                document.getElementById('input-precio').value = '';
                document.getElementById('stock-info').textContent = '';
            };

            window.removeProduct = function(index) {
                selectedProducts.splice(index, 1);
                renderProductsTable();
                renderProductSelector();
            };

            function renderProductsTable() {
                const container = document.getElementById('products-table-container');
                
                if (selectedProducts.length === 0) {
                    container.innerHTML = '<div class="empty-state">No hay productos agregados a la compra</div>';
                    document.getElementById('total-container').style.display = 'none';
                    return;
                }
                
                let total = 0;
                let html = `
                    <div class="products-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Código</th>
                                    <th>Cantidad</th>
                                    <th>Precio Unit.</th>
                                    <th>Subtotal</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                
                selectedProducts.forEach((prod, index) => {
                    total += prod.subtotal;
                    html += `
                        <tr>
                            <td>${prod.nombre}</td>
                            <td>${prod.codigo || '-'}</td>
                            <td>${prod.cantidad}</td>
                            <td>₲ ${prod.precio_unitario.toLocaleString('es-PY', {minimumFractionDigits: 2})}</td>
                            <td><strong>₲ ${prod.subtotal.toLocaleString('es-PY', {minimumFractionDigits: 2})}</strong></td>
                            <td>
                                <button type="button" class="btn-remove" onclick="removeProduct(${index})">
                                    🗑️ Quitar
                                </button>
                                <input type="hidden" name="productos[${index}][id]" value="${prod.id}">
                                <input type="hidden" name="productos[${index}][cantidad]" value="${prod.cantidad}">
                                <input type="hidden" name="productos[${index}][precio]" value="${prod.precio_unitario}">
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
                
                document.getElementById('total-container').style.display = 'block';
                document.getElementById('total-amount').textContent = '₲ ' + total.toLocaleString('es-PY', {minimumFractionDigits: 2});
                document.getElementById('input-total').value = total.toFixed(2);
                
                // Actualizar monto de cuota si está en crédito
                updateCuotaMonto();
            }

            window.onFormaPagoChange = function(formaPago) {
                const creditoFields = document.getElementById('credito-fields');
                if (formaPago === 'CREDITO') {
                    creditoFields.classList.add('active');
                    document.getElementById('cuotas').required = true;
                    document.getElementById('fecha_vencimiento').required = true;
                } else {
                    creditoFields.classList.remove('active');
                    document.getElementById('cuotas').required = false;
                    document.getElementById('fecha_vencimiento').required = false;
                }
            };

            window.updateCuotaMonto = function() {
                const total = parseFloat(document.getElementById('input-total').value) || 0;
                const cuotas = parseInt(document.getElementById('cuotas').value) || 1;
                const montoCuota = cuotas > 0 ? (total / cuotas).toFixed(2) : '0.00';
                document.getElementById('monto-cuota-display').textContent = '₲ ' + parseFloat(montoCuota).toLocaleString('es-PY', {minimumFractionDigits: 2});
                document.getElementById('monto_cuota').value = montoCuota;
            };

            window.validateForm = function() {
                if (!selectedProveedor) {
                    alert('Por favor selecciona un proveedor');
                    return false;
                }
                
                if (selectedProducts.length === 0) {
                    alert('Debes agregar al menos un producto a la compra');
                    return false;
                }
                
                const fecha = document.getElementById('fecha_compra').value;
                if (!fecha) {
                    alert('Por favor ingresa la fecha de compra');
                    return false;
                }
                
                const formaPago = document.querySelector('input[name="forma_pago"]:checked').value;
                if (formaPago === 'CREDITO') {
                    const cuotas = parseInt(document.getElementById('cuotas').value);
                    const fechaVencimiento = document.getElementById('fecha_vencimiento').value;
                    
                    if (!cuotas || cuotas < 1) {
                        alert('Por favor ingresa el número de cuotas');
                        return false;
                    }
                    
                    if (!fechaVencimiento) {
                        alert('Por favor ingresa la fecha de vencimiento de la primera cuota');
                        return false;
                    }
                }
                
                return confirm('¿Confirmar registro de compra?\n\nEsto actualizará el stock de los productos.');
            };

            const formHTML = `
                <div class="form-container">
                    <h1 class="form-title">📦 Registrar Compra</h1>
                    
                    <form action="./guardar_compra.php" method="post" onsubmit="return validateForm()">
                        
                        <div class="columns">
                            <div class="column is-6">
                                <div class="field">
                                    <label class="label">Proveedor *</label>
                                    <div class="control">
                                        <div class="select is-fullwidth">
                                            <select name="id_proveedor" id="id_proveedor" required onchange="onProveedorChange(this.value)">
                                                <option value="">-- Seleccionar Proveedor --</option>
                                                ${proveedores.map(p => `<option value="${p.id}">${p.nombre_proveedor}</option>`).join('')}
                                            </select>
                                        </div>
                                    </div>
                                    <p class="help" style="color: rgba(255,255,255,0.7);">
                                        Solo se mostrarán los productos asignados a este proveedor
                                    </p>
                                </div>

                                <div class="field">
                                    <label class="label">Fecha de Compra *</label>
                                    <div class="control">
                                        <input class="input" type="date" name="fecha_compra" id="fecha_compra" 
                                               value="${new Date().toISOString().split('T')[0]}" required>
                                    </div>
                                </div>
                            </div>

                            <div class="column is-6">
                                <div class="field">
                                    <label class="label">Número de Factura/Recibo</label>
                                    <div class="control">
                                        <input class="input" type="text" name="numero_compra" id="numero_compra" 
                                               placeholder="Ej: 001-001-0001234">
                                    </div>
                                </div>

                                <div class="field">
                                    <label class="label">Observaciones</label>
                                    <div class="control">
                                        <textarea class="textarea" name="observaciones" id="observaciones" 
                                                  rows="3" placeholder="Notas adicionales sobre la compra"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- NUEVO: Forma de Pago -->
                        <div class="payment-section">
                            <label class="label">💳 Forma de Pago *</label>
                            <div class="radio-group">
                                <div class="radio-option">
                                    <input type="radio" name="forma_pago" id="radio-contado" value="CONTADO" checked onchange="onFormaPagoChange('CONTADO')">
                                    <label for="radio-contado">Contado</label>
                                </div>
                                <div class="radio-option">
                                    <input type="radio" name="forma_pago" id="radio-credito" value="CREDITO" onchange="onFormaPagoChange('CREDITO')">
                                    <label for="radio-credito">Crédito (Cuotas)</label>
                                </div>
                            </div>

                            <!-- Campos de crédito (ocultos por defecto) -->
                            <div class="credito-fields" id="credito-fields">
                                <div class="columns">
                                    <div class="column is-4">
                                        <div class="field">
                                            <label class="label">Número de Cuotas</label>
                                            <div class="control">
                                                <input class="input" type="number" name="cuotas" id="cuotas" min="1" placeholder="Ej: 3" oninput="updateCuotaMonto()">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="column is-4">
                                        <div class="field">
                                            <label class="label">Monto por Cuota</label>
                                            <div class="control">
                                                <div style="background: rgba(236, 240, 241, 0.1); border: 2px solid rgba(241, 196, 15, 0.3); padding: 10px; border-radius: 8px; text-align: center; font-weight: bold; color: #27ae60;">
                                                    <span id="monto-cuota-display">₲ 0.00</span>
                                                </div>
                                                <input type="hidden" name="monto_cuota" id="monto_cuota" value="0">
                                            </div>
                                            <p class="help" style="color: rgba(255,255,255,0.7);">
                                                Se calcula automáticamente: Total / Cuotas
                                            </p>
                                        </div>
                                    </div>
                                    <div class="column is-4">
                                        <div class="field">
                                            <label class="label">Vencimiento 1ra Cuota</label>
                                            <div class="control">
                                                <input class="input" type="date" name="fecha_vencimiento" id="fecha_vencimiento">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Selector de productos -->
                        <div id="product-selector-container"></div>

                        <!-- Tabla de productos seleccionados -->
                        <div id="products-table-container">
                            <div class="empty-state">No hay productos agregados a la compra</div>
                        </div>

                        <!-- Total -->
                        <div id="total-container" class="total-section" style="display: none;">
                            <label class="label" style="text-align: center; color: #27ae60;">TOTAL DE LA COMPRA</label>
                            <div class="total-amount" id="total-amount">₲ 0.00</div>
                            <input type="hidden" name="total_compra" id="input-total" value="0">
                        </div>

                        <!-- Botones -->
                        <div class="field is-grouped" style="justify-content: center; margin-top: 30px;">
                            <div class="control">
                                <button type="submit" class="button">
                                    💾 Guardar Compra
                                </button>
                            </div>
                            <div class="control">
                                <a href="./listado_compras.php" class="secondary-button">
                                    📋 Ver Listado
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            `;
            
            mainContent.innerHTML = formHTML;
            
            const selectProveedor = document.getElementById('id_proveedor');
            if (selectProveedor) {
                selectProveedor.addEventListener('change', function(e) {
                    onProveedorChange(e.target.value);
                });
            }

            renderProductSelector();
        });
    </script>
</body>
</html>