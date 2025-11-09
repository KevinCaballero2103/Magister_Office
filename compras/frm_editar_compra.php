<?php
include_once __DIR__ . "/../auth.php";
$cajaAbierta = requiereCajaAbierta();
if (!isset($_GET["id"])) {
    $error = "Necesito el parámetro id para identificar la compra.";
} else {
    include '../db.php';
    $id = $_GET["id"];
    
    // Obtener compra con proveedor
    $sentencia = $conexion->prepare("SELECT c.*, p.nombre_proveedor FROM compras c INNER JOIN proveedores p ON c.id_proveedor = p.id WHERE c.id = ?");
    $sentencia->execute([$id]);
    $compra = $sentencia->fetch(PDO::FETCH_OBJ);
    
    if ($compra === FALSE) {
        $error = "La compra indicada no existe en el sistema.";
    } else {
        // Obtener detalles de la compra CON PRECIOS de proveedor_producto
        $sentenciaDetalle = $conexion->prepare("
            SELECT 
                dc.id,
                dc.id_producto,
                dc.cantidad,
                dc.precio_unitario,
                dc.subtotal,
                prod.nombre_producto,
                prod.codigo_producto,
                pp.precio_compra
            FROM detalle_compras dc
            INNER JOIN productos prod ON dc.id_producto = prod.id
            INNER JOIN proveedor_producto pp ON pp.id_proveedor = ? AND pp.id_producto = prod.id
            WHERE dc.id_compra = ?
        ");
        $sentenciaDetalle->execute([$compra->id_proveedor, $id]);
        $detalles = $sentenciaDetalle->fetchAll(PDO::FETCH_OBJ);
        
        // Obtener productos disponibles del proveedor que NO están en la compra
        $idsProductosActuales = array_map(function($d) { return $d->id_producto; }, $detalles);
        $placeholders = implode(',', $idsProductosActuales ?: [0]);
        
        $sentenciaProductosDisponibles = $conexion->prepare("
            SELECT 
                prod.id,
                prod.nombre_producto,
                prod.codigo_producto,
                pp.precio_compra
            FROM productos prod
            INNER JOIN proveedor_producto pp ON pp.id_producto = prod.id
            WHERE pp.id_proveedor = ? 
            AND prod.estado_producto = 1
            AND prod.id NOT IN ($placeholders)
            ORDER BY prod.nombre_producto ASC
        ");
        $sentenciaProductosDisponibles->execute([$compra->id_proveedor]);
        $productosDisponibles = $sentenciaProductosDisponibles->fetchAll(PDO::FETCH_OBJ);
        
        // Obtener info de pago
        $sentenciaPago = $conexion->prepare("SELECT * FROM pagos_compra WHERE id_compra = ?");
        $sentenciaPago->execute([$id]);
        $pago = $sentenciaPago->fetch(PDO::FETCH_OBJ);
    }
}

if (isset($compra)) {
    $compraJSON = json_encode($compra);
    $detallesJSON = json_encode($detalles);
    $productosDisponiblesJSON = json_encode($productosDisponibles);
    $pagoJSON = $pago ? json_encode($pago) : 'null';
} else {
    $compraJSON = 'null';
    $detallesJSON = '[]';
    $productosDisponiblesJSON = '[]';
    $pagoJSON = 'null';
    $mensajeError = isset($error) ? $error : 'Error desconocido';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Compra</title>
    <link href="../css/bulma.min.css" rel="stylesheet">
    <link href="../css/formularios.css" rel="stylesheet">
    
    <style>
        .main-content {
            background: #2c3e50 !important;
            color: white;
        }

        .info-section {
            background: rgba(52, 152, 219, 0.1);
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            border: 2px solid rgba(52, 152, 219, 0.3);
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            color: #3498db;
            font-weight: bold;
        }

        .info-value {
            color: white;
        }

        .product-row {
            display: grid;
            grid-template-columns: 2fr 1fr 1.5fr 1.5fr 1fr;
            gap: 10px;
            margin-bottom: 12px;
            padding: 12px;
            background: rgba(0,0,0,0.2);
            border-radius: 8px;
            border: 1px solid rgba(52, 152, 219, 0.3);
            align-items: center;
        }

        .product-row input {
            background: rgba(236, 240, 241, 0.1) !important;
            border: 1px solid rgba(241, 196, 15, 0.3) !important;
            color: white !important;
            padding: 6px !important;
            font-size: 0.9rem !important;
        }

        .product-row label {
            color: rgba(255,255,255,0.7) !important;
            font-size: 0.75rem !important;
            margin-bottom: 3px !important;
        }

        .price-display {
            background: rgba(39, 174, 96, 0.2);
            padding: 6px;
            border-radius: 4px;
            text-align: center;
            color: #27ae60;
            font-weight: bold;
            font-size: 0.9rem;
        }

        .subtotal-display {
            background: rgba(39, 174, 96, 0.2);
            padding: 6px;
            border-radius: 4px;
            text-align: center;
            color: #27ae60;
            font-weight: bold;
            font-size: 0.9rem;
        }

        .btn-remove-product {
            background: linear-gradient(45deg, #e74c3c, #c0392b) !important;
            color: white !important;
            border: none !important;
            padding: 4px 8px !important;
            border-radius: 4px !important;
            cursor: pointer;
            font-size: 0.75rem !important;
            font-weight: bold !important;
            transition: all 0.3s ease;
        }

        .btn-remove-product:hover {
            background: linear-gradient(45deg, #c0392b, #e74c3c) !important;
        }

        .add-product-section {
            background: rgba(39, 174, 96, 0.1);
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            border: 1px solid rgba(39, 174, 96, 0.3);
        }

        .add-product-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1.5fr auto;
            gap: 10px;
            align-items: end;
        }

        .add-product-grid select,
        .add-product-grid input {
            background: rgba(236, 240, 241, 0.1) !important;
            border: 1px solid rgba(241, 196, 15, 0.3) !important;
            color: white !important;
            padding: 8px !important;
        }

        .add-product-grid select option {
            background: #2c3e50 !important;
            color: white !important;
        }

        .btn-add-product {
            background: linear-gradient(45deg, #27ae60, #2ecc71) !important;
            color: white !important;
            border: none !important;
            padding: 8px 16px !important;
            border-radius: 4px !important;
            cursor: pointer;
            font-weight: bold !important;
            transition: all 0.3s ease;
        }

        .btn-add-product:hover {
            background: linear-gradient(45deg, #2ecc71, #27ae60) !important;
        }

        .btn-add-product:disabled {
            background: rgba(128, 128, 128, 0.5) !important;
            cursor: not-allowed;
            opacity: 0.5;
        }

        .total-display {
            background: rgba(39, 174, 96, 0.2);
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            margin-top: 15px;
            border: 2px solid rgba(39, 174, 96, 0.5);
        }

        .total-display strong {
            font-size: 1.5rem;
            color: #27ae60;
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
            const compra = <?php echo $compraJSON; ?>;
            const detalles = <?php echo $detallesJSON; ?>;
            const productosDisponibles = <?php echo $productosDisponiblesJSON; ?>;
            const pago = <?php echo $pagoJSON; ?>;
            
            if (compra === null) {
                const errorMessage = '<?php echo isset($mensajeError) ? addslashes($mensajeError) : 'Error desconocido'; ?>';
                const errorHTML = `
                    <div class='error-container'>
                        <div class='error-title'>Error</div>
                        <div class='error-message'>${errorMessage}</div>
                        <a href='./listado_compras.php' class='button'>Volver al Listado</a>
                    </div>
                `;
                mainContent.innerHTML = errorHTML;
                return;
            }

            let productosActuales = detalles.map(d => ({
                id_detalle: d.id,
                id_producto: d.id_producto,
                nombre: d.nombre_producto,
                codigo: d.codigo_producto,
                cantidad_original: d.cantidad,
                cantidad_nueva: d.cantidad,
                precio_fijo: parseFloat(d.precio_compra)
            }));

            function calcularTotales() {
                let total = 0;
                productosActuales.forEach((prod, idx) => {
                    const subtotal = prod.cantidad_nueva * prod.precio_fijo;
                    document.getElementById(`subtotal-${idx}`).textContent = '₲ ' + subtotal.toLocaleString('es-PY', {minimumFractionDigits: 2});
                    total += subtotal;
                });
                document.getElementById('total-compra').textContent = '₲ ' + total.toLocaleString('es-PY', {minimumFractionDigits: 2});
                document.getElementById('input-total').value = total.toFixed(2);
            }

            function renderProductos() {
                let html = '';
                
                productosActuales.forEach((prod, idx) => {
                    html += `
                        <div class="product-row">
                            <div>
                                <label>Producto</label>
                                <div style="color: white;">${prod.nombre} ${prod.codigo ? '(' + prod.codigo + ')' : ''}</div>
                            </div>
                            <div>
                                <label>Cantidad</label>
                                <input type="number" min="1" value="${prod.cantidad_nueva}" 
                                       onchange="actualizarCantidad(${idx}, this.value)">
                            </div>
                            <div>
                                <label>Precio Unit. (Fijo)</label>
                                <div class="price-display">₲ ${prod.precio_fijo.toLocaleString('es-PY', {minimumFractionDigits: 2})}</div>
                            </div>
                            <div>
                                <label>Subtotal</label>
                                <div class="subtotal-display" id="subtotal-${idx}">₲ 0.00</div>
                            </div>
                            <button type="button" class="btn-remove-product" onclick="quitarProducto(${idx})">
                                🗑️ Quitar
                            </button>
                            <input type="hidden" name="productos[${idx}][id_detalle]" value="${prod.id_detalle}">
                            <input type="hidden" name="productos[${idx}][id_producto]" value="${prod.id_producto}">
                            <input type="hidden" name="productos[${idx}][cantidad_original]" value="${prod.cantidad_original}">
                            <input type="hidden" name="productos[${idx}][cantidad_nueva]" value="${prod.cantidad_nueva}">
                        </div>
                    `;
                });
                
                document.getElementById('productos-container').innerHTML = html;
                actualizarSelectoDisponibles();
                calcularTotales();
            }

            function actualizarSelectoDisponibles() {
                // Obtener IDs de productos ya agregados
                const idsAgregados = productosActuales.map(p => p.id_producto);
                
                // Actualizar opciones del selector
                const selectProducto = document.getElementById('select-producto-agregar');
                if (selectProducto) {
                    Array.from(selectProducto.options).forEach(option => {
                        if (option.value === '') return;
                        option.disabled = idsAgregados.includes(parseInt(option.value));
                    });
                }
            }

            window.actualizarCantidad = function(idx, valor) {
                productosActuales[idx].cantidad_nueva = parseInt(valor) || 0;
                document.querySelector(`input[name="productos[${idx}][cantidad_nueva]"]`).value = productosActuales[idx].cantidad_nueva;
                calcularTotales();
            };

            window.quitarProducto = function(idx) {
                if (confirm('¿Quitar este producto de la compra?')) {
                    productosActuales.splice(idx, 1);
                    renderProductos();
                }
            };

            window.agregarProducto = function() {
                const select = document.getElementById('select-producto-agregar');
                const cantidad = document.getElementById('cantidad-agregar');
                
                const productoId = select.value;
                
                // Validaciones
                if (!productoId) {
                    alert('Selecciona un producto');
                    return;
                }
                
                if (!cantidad.value || cantidad.value <= 0) {
                    alert('Cantidad debe ser mayor a 0');
                    return;
                }
                
                // Validar que NO esté ya agregado
                if (productosActuales.some(p => p.id_producto == productoId)) {
                    alert('Este producto ya está en la compra');
                    return;
                }
                
                const producto = productosDisponibles.find(p => p.id == productoId);
                
                if (!producto) {
                    alert('Producto no encontrado');
                    return;
                }
                
                // Agregar el producto
                productosActuales.push({
                    id_detalle: '',
                    id_producto: producto.id,
                    nombre: producto.nombre_producto,
                    codigo: producto.codigo_producto,
                    cantidad_original: 0,
                    cantidad_nueva: parseInt(cantidad.value),
                    precio_fijo: parseFloat(producto.precio_compra)
                });
                
                // Limpiar campos
                select.value = '';
                cantidad.value = '1';
                
                // Actualizar UI
                renderProductos();
            };

            let pagoInfo = '';
            if (pago) {
                if (pago.forma_pago === 'CONTADO') {
                    pagoInfo = `<div class="info-row"><span class="info-label">Forma de Pago:</span><span class="info-value">Contado</span></div>`;
                } else {
                    const fechaVenc = pago.fecha_vencimiento ? new Date(pago.fecha_vencimiento + 'T00:00:00').toLocaleDateString('es-PY') : '-';
                    pagoInfo = `
                        <div class="info-row"><span class="info-label">Forma de Pago:</span><span class="info-value">Crédito - ${pago.cuotas} cuotas</span></div>
                        <div class="info-row"><span class="info-label">Monto por Cuota:</span><span class="info-value">₲ ${parseFloat(pago.monto_cuota).toLocaleString('es-PY', {minimumFractionDigits: 2})}</span></div>
                        <div class="info-row"><span class="info-label">Vencimiento 1ra Cuota:</span><span class="info-value">${fechaVenc}</span></div>
                    `;
                }
            }

            const contentHTML = `
                <div class='form-container'>
                    <h1 class='form-title'>✏️ Editar Compra #${compra.id}</h1>
                    
                    <div class="info-section">
                        <div class="info-row">
                            <span class="info-label">Proveedor:</span>
                            <span class="info-value">${compra.nombre_proveedor}</span>
                        </div>
                        ${pagoInfo}
                    </div>

                    <h3 style="color: #f1c40f; margin-top: 25px; margin-bottom: 15px;">📦 Productos en Compra</h3>
                    
                    <form action='./editar_compra.php' method='post' onsubmit='return validateForm()'>
                        <input type='hidden' name='id' value='${compra.id}'>
                        
                        <div id="productos-container"></div>

                        ${productosDisponibles.length > 0 ? `
                        <div class="add-product-section">
                            <label style="color: #27ae60; font-weight: bold; display: block; margin-bottom: 10px;">➕ Agregar Producto</label>
                            <div class="add-product-grid">
                                <div>
                                    <label>Producto</label>
                                    <select id="select-producto-agregar">
                                        <option value="">-- Seleccionar --</option>
                                        ${productosDisponibles.map(p => `<option value="${p.id}">${p.nombre_producto} ${p.codigo_producto ? '(' + p.codigo_producto + ')' : ''}</option>`).join('')}
                                    </select>
                                </div>
                                <div>
                                    <label>Cantidad</label>
                                    <input type="number" id="cantidad-agregar" min="1" value="1">
                                </div>
                                <div>
                                    <label>Precio Unit.</label>
                                    <div style="color: #27ae60; padding: 8px; text-align: center;">
                                        <small>Del proveedor</small>
                                    </div>
                                </div>
                                <button type="button" class="btn-add-product" onclick="agregarProducto()">
                                    Agregar
                                </button>
                            </div>
                        </div>
                        ` : ''}

                        <div class="total-display">
                            <label style="color: rgba(255,255,255,0.8);">TOTAL COMPRA:</label><br>
                            <strong id="total-compra">₲ 0.00</strong>
                            <input type='hidden' name='total_compra' id='input-total' value='0'>
                        </div>

                        <div class='button-group' style='margin-top: 30px;'>
                            <button type='submit' class='button'>
                                💾 Guardar Cambios
                            </button>
                            
                            <a href='./listado_compras.php' class='secondary-button'>
                                Volver al Listado
                            </a>
                        </div>
                    </form>
                </div>
            `;
            
            mainContent.innerHTML = contentHTML;
            renderProductos();

            window.validateForm = function() {
                if (productosActuales.length === 0) {
                    alert('Debes tener al menos un producto en la compra');
                    return false;
                }
                
                const tieneProductoValido = productosActuales.some(p => p.cantidad_nueva > 0);
                if (!tieneProductoValido) {
                    alert('Todos los productos deben tener cantidad mayor a 0');
                    return false;
                }
                
                return confirm('¿Guardar cambios?\\n\\nEl stock se ajustará según las nuevas cantidades.');
            };
        });
    </script>
</body>
</html>