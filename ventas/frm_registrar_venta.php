<?php
session_start();
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

include_once "../db.php";

// Obtener próximo número de venta
$sentenciaNextId = $conexion->prepare("SELECT AUTO_INCREMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'magister_office' AND TABLE_NAME = 'ventas'");
$sentenciaNextId->execute();
$nextId = $sentenciaNextId->fetchColumn();

// Cargar datos
$sentenciaClientes = $conexion->prepare("SELECT id, nombre_cliente, apellido_cliente, ci_ruc_cliente FROM clientes WHERE estado_cliente = 1 ORDER BY nombre_cliente ASC");
$sentenciaClientes->execute();
$clientes = $sentenciaClientes->fetchAll(PDO::FETCH_OBJ);

$sentenciaProductos = $conexion->prepare("SELECT id, nombre_producto, codigo_producto, precio_venta, stock_actual FROM productos WHERE estado_producto = 1 ORDER BY nombre_producto ASC");
$sentenciaProductos->execute();
$productos = $sentenciaProductos->fetchAll(PDO::FETCH_OBJ);

$sentenciaServicios = $conexion->prepare("SELECT id, nombre_servicio, categoria_servicio, precio_sugerido FROM servicios WHERE estado_servicio = 1 ORDER BY nombre_servicio ASC");
$sentenciaServicios->execute();
$servicios = $sentenciaServicios->fetchAll(PDO::FETCH_OBJ);

$FACTURA_INICIAL = 826;
$FACTURA_NUMERO = $FACTURA_INICIAL + ($nextId - 1);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registrar Venta</title>
    <link href="../css/bulma.min.css" rel="stylesheet">
    <link href="../css/formularios.css" rel="stylesheet">
    <link href="../css/autocompletado.css" rel="stylesheet">
    <style>
        .main-content { background: #2c3e50 !important; color: white; }
        .item-selector { background: rgba(0,0,0,0.2); padding: 20px; border-radius: 10px; margin: 20px 0; border: 1px solid rgba(241, 196, 15, 0.2); }
        .item-selector label.label { color: #f1c40f !important; font-weight: bold; font-size: 1.1rem; }
        .items-table { width: 100%; background: rgba(0,0,0,0.3); border-radius: 10px; overflow: hidden; margin-top: 20px; }
        .items-table table { width: 100%; color: white; }
        .items-table thead { background: linear-gradient(45deg, #f39c12, #f1c40f); }
        .items-table thead th { color: #2c3e50; font-weight: bold; padding: 12px 8px; text-align: center; font-size: 0.9rem; }
        .items-table tbody tr { background: rgba(255,255,255,0.05); border-bottom: 1px solid rgba(255,255,255,0.1); }
        .items-table tbody tr:hover { background: rgba(241, 196, 15, 0.1); }
        .items-table tbody td { padding: 10px 8px; text-align: center; font-size: 0.9rem; }
        .btn-remove { background: linear-gradient(45deg, #e74c3c, #c0392b); color: white; border: none; padding: 6px 12px; border-radius: 5px; cursor: pointer; font-weight: bold; font-size: 0.8rem; transition: all 0.3s ease; }
        .btn-remove:hover { background: linear-gradient(45deg, #c0392b, #e74c3c); transform: translateY(-2px); }
        .add-item-btn { background: linear-gradient(45deg, #27ae60, #2ecc71) !important; color: white !important; border: none !important; padding: 10px 20px !important; border-radius: 8px !important; font-weight: bold !important; cursor: pointer; transition: all 0.3s ease !important; }
        .add-item-btn:hover { background: linear-gradient(45deg, #2ecc71, #27ae60) !important; transform: translateY(-2px); }
        .add-item-btn:disabled { background: rgba(128, 128, 128, 0.5) !important; cursor: not-allowed; opacity: 0.5; }
        .empty-state { text-align: center; padding: 40px; color: rgba(255,255,255,0.6); font-style: italic; }
        .numero-preview { background: rgba(39, 174, 96, 0.15); border: 2px solid rgba(39, 174, 96, 0.4); padding: 12px; border-radius: 8px; margin-top: 10px; text-align: center; }
        .numero-preview-text { color: #27ae60; font-weight: bold; font-size: 1.1rem; }
        .info-message { background: rgba(52, 152, 219, 0.1); border: 1px solid rgba(52, 152, 219, 0.3); padding: 12px; border-radius: 8px; margin-bottom: 15px; color: #3498db; font-size: 0.9rem; }
        .iva-info { background: rgba(230, 126, 34, 0.1); border: 1px solid rgba(230, 126, 34, 0.3); padding: 10px; border-radius: 8px; margin-top: 10px; font-size: 0.85rem; color: #e67e22; }
        .total-section { background: rgba(39, 174, 96, 0.1); border: 2px solid rgba(39, 174, 96, 0.3); padding: 20px; border-radius: 10px; margin-top: 20px; }
    </style>
</head>
<body>
    <?php include '../menu.php'; ?>
    <script>
    const clientes = <?= json_encode($clientes); ?>;
    const productos = <?= json_encode($productos); ?>;
    const servicios = <?= json_encode($servicios); ?>;
    const FACTURA_NUM = <?= $FACTURA_NUMERO; ?>;
    const TICKET_NUM = <?= $nextId; ?>;
    const $ = id => document.getElementById(id);
    const escHtml = text => text ? text.replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])) : '';
    
    let selectedItems = [], clienteFilt = clientes, itemFilt = [], clienteIdx = -1, itemIdx = -1;

    document.addEventListener('DOMContentLoaded', function() {
        const mainContent = document.querySelector('.main-content');
        if (!mainContent) return console.error('No .main-content');

        mainContent.innerHTML = `<div class="form-container">
            <h1 class="form-title">💰 Registrar Venta</h1>
            <div class="info-message">ℹ️ <strong>TICKET</strong> y <strong>FACTURA</strong> se numeran automáticamente al guardar</div>
            <form id="formVenta" action="./guardar_venta.php" method="post" onsubmit="return validateForm()">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">
                <div class="columns">
                    <div class="column is-6">
                        <div class="field">
                            <label class="label">🔍 Cliente</label>
                            <div class="control" style="display: flex; gap: 8px;">
                                <div class="autocomplete-container" style="flex: 1;">
                                    <input class="input" type="text" id="buscar_cliente" placeholder="Buscar..." autocomplete="off">
                                    <div class="autocomplete-suggestions" id="clientes_sug"></div>
                                </div>
                                <a href="../clientes/frm_guardar_cliente.php" target="_blank" class="button is-small" style="background: linear-gradient(45deg, #27ae60, #2ecc71) !important; color: white !important; border: none !important; padding: 0 15px; height: 42px; display: flex; align-items: center; font-weight: bold; text-decoration: none; white-space: nowrap;">➕ Nuevo</a>
                            </div>
                            <p class="help" style="color: rgba(255,255,255,0.7);">Presiona ↓ para ver todos</p>
                        </div>
                        <input type="hidden" name="id_cliente" id="id_cliente">
                        <div class="field"><label class="label">RUC / CI</label><input class="input" type="text" name="cliente_ruc_ci" id="cliente_ruc_ci" placeholder="RUC o CI"></div>
                        <div class="field"><label class="label">Nombre</label><input class="input" type="text" name="cliente_nombre" id="cliente_nombre" placeholder="Nombre"></div>
                        <div class="field"><label class="label">Fecha *</label><input class="input" type="datetime-local" name="fecha_venta" id="fecha_venta" value="" required></div>
                    </div>
                    <div class="column is-6">
                        <div class="field">
                            <label class="label">Tipo Comprobante</label>
                            <select name="tipo_comprobante" id="tipo_comprobante" class="input">
                                <option value="">-- Ninguno --</option>
                                <option value="TICKET">TICKET</option>
                                <option value="FACTURA">FACTURA (requiere RUC)</option>
                            </select>
                            <div id="num-prev" style="display:none;"><div class="numero-preview"><div class="numero-preview-text" id="num-prev-txt"></div></div></div>
                        </div>
                        <div class="field"><label class="label">Condición</label><select name="condicion_venta" id="condicion_venta" class="input"><option value="CONTADO">CONTADO</option><option value="CREDITO">CRÉDITO</option></select></div>
                        <div class="field"><label class="label">Forma pago</label><select name="forma_pago" id="forma_pago" class="input"><option value="CONTADO">Contado</option><option value="FIADO">Fiado</option><option value="TARJETA">Tarjeta</option><option value="TRANSFERENCIA">Transferencia</option></select></div>
                        <div class="field"><label class="label">Observaciones</label><textarea class="textarea" name="observaciones" id="observaciones" rows="2"></textarea></div>
                    </div>
                </div>
                <div class="item-selector">
                    <label class="label">➕ Agregar Producto/Servicio</label>
                    <div class="columns">
                        <div class="column is-3"><div class="field"><label class="label" style="font-size: 0.9rem;">Tipo</label><select id="sel-tipo" class="input"><option value="">Seleccionar</option><option value="PRODUCTO">Producto</option><option value="SERVICIO">Servicio</option></select></div></div>
                        <div class="column is-4"><div class="field"><label class="label" style="font-size: 0.9rem;">🔍 Buscar</label><div class="autocomplete-container"><input type="text" class="input" id="buscar_item" placeholder="Buscar..." autocomplete="off" disabled><div class="autocomplete-suggestions" id="items_sug"></div></div><p id="stock-info" style="font-size: 0.8rem; color: rgba(255,255,255,0.7); margin-top: 4px;"></p></div></div>
                        <div class="column is-2"><div class="field"><label class="label" style="font-size: 0.9rem;">Cant.</label><input type="number" min="1" class="input" id="inp-cant" value="1" disabled></div></div>
                        <div class="column is-2"><div class="field"><label class="label" style="font-size: 0.9rem;">Precio</label><input type="number" step="0.01" min="0" class="input" id="inp-precio" placeholder="0.00" disabled></div></div>
                        <div class="column is-1"><div class="field"><label class="label" style="font-size: 0.9rem;">&nbsp;</label><button type="button" class="add-item-btn" id="btn-add" disabled>➕</button></div></div>
                    </div>
                </div>
                <div id="items-table"></div>
                <div id="totals" class="total-section" style="display: none;">
                    <label style="text-align: center; color: #27ae60; display: block; margin-bottom: 15px; font-size: 1.1rem; font-weight: bold;">📊 TOTALES</label>
                    <div class="columns">
                        <div class="column is-6">
                            <div style="margin-bottom: 8px;"><strong>Subtotal:</strong> <span id="sub-amt">₲ 0</span></div>
                            <div class="iva-info"><strong>💡 IVA (10%) - Informativo:</strong> <span id="iva-amt">₲ 0</span><br><small>Ya incluido en precios (Total ÷ 11)</small></div>
                        </div>
                        <div class="column is-6" style="text-align: right; display: flex; align-items: center; justify-content: flex-end;"><div style="font-size: 2rem; color: #27ae60; font-weight: bold;">TOTAL: <span id="tot-amt">₲ 0</span></div></div>
                    </div>
                    
                    <!-- SECCIÓN DE PAGO Y VUELTO -->
                    <div style="background: rgba(52, 152, 219, 0.1); border: 2px solid rgba(52, 152, 219, 0.3); padding: 20px; border-radius: 10px; margin-top: 20px;">
                        <div class="columns">
                            <div class="column is-6">
                                <div class="field">
                                    <label class="label" style="color: #3498db; font-size: 1rem;">💵 Dinero Recibido</label>
                                    <div class="control">
                                        <input type="number" step="0.01" min="0" class="input" id="dinero-recibido" placeholder="0.00" oninput="calcularVuelto()" onchange="calcularVuelto()" style="font-size: 1.2rem; font-weight: bold; background: rgba(236, 240, 241, 0.15) !important; border: 2px solid rgba(52, 152, 219, 0.5) !important; color: white !important;">
                                    </div>
                                    <p class="help" style="color: rgba(255,255,255,0.7);">Ingresa el monto que entregó el cliente</p>
                                </div>
                            </div>
                            <div class="column is-6" style="display: flex; align-items: center; justify-content: center;">
                                <div id="vuelto-display" style="text-align: center; width: 100%;">
                                    <div style="color: #3498db; font-size: 0.9rem; margin-bottom: 5px;">💰 VUELTO</div>
                                    <div style="font-size: 2.5rem; font-weight: bold; color: #2ecc71;" id="vuelto-amt">₲ 0,00</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <input type="hidden" name="subtotal" id="inp-sub" value="0"><input type="hidden" name="total_venta" id="inp-tot" value="0">
                </div>
                <div class="field is-grouped" style="justify-content: center; margin-top: 30px;">
                    <div class="control"><button type="submit" class="button">💾 Guardar</button></div>
                    <div class="control"><a href="./listado_ventas.php" class="secondary-button">📋 Listado</a></div>
                </div>
            </form>
        </div>`;

        // Autocompletado clientes
        const inpCli = $('buscar_cliente'), sugCli = $('clientes_sug');
        inpCli.onfocus = () => inpCli.value.trim() === '' ? mostrarCli() : filtrarCli(inpCli.value);
        inpCli.oninput = () => inpCli.value.trim() === '' ? mostrarCli() : filtrarCli(inpCli.value);
        inpCli.onkeydown = e => navegar(e, sugCli, 'clienteIdx', () => sugCli.querySelectorAll('.suggestion-item')[clienteIdx]?.click());
        document.onclick = e => !e.target.closest('.autocomplete-container') && (sugCli.classList.remove('active'), clienteIdx = -1);

        function mostrarCli() {
            sugCli.innerHTML = clientes.length === 0 ? '<div class="suggestion-item" style="cursor:default;">Sin clientes</div>' : clientes.map(c => itemCli(c)).join('');
            sugCli.classList.add('active');
        }
        function filtrarCli(term) {
            const t = term.toLowerCase();
            clienteFilt = clientes.filter(c => c.nombre_cliente.toLowerCase().includes(t) || c.apellido_cliente.toLowerCase().includes(t) || c.ci_ruc_cliente.toLowerCase().includes(t));
            sugCli.innerHTML = clienteFilt.length > 0 ? clienteFilt.map(c => itemCli(c)).join('') : '<div class="suggestion-item" style="cursor:default;">Sin resultados</div>';
            sugCli.classList.add('active');
        }
        function itemCli(c) {
            return `<div class="suggestion-item" onclick="selCli(${c.id},'${escHtml(c.nombre_cliente)}','${escHtml(c.apellido_cliente)}','${escHtml(c.ci_ruc_cliente)}')">👤 ${c.nombre_cliente} ${c.apellido_cliente} <small>(${c.ci_ruc_cliente})</small></div>`;
        }
        window.selCli = (id,nom,ape,ruc) => {
            inpCli.value = nom + ' ' + ape;
            $('id_cliente').value = id;
            $('cliente_ruc_ci').value = ruc || '';
            $('cliente_nombre').value = (nom + ' ' + ape).trim();
            sugCli.classList.remove('active');
        };

        // Autocompletado items
        const selTipo = $('sel-tipo'), inpItem = $('buscar_item'), sugItem = $('items_sug'), inpPrecio = $('inp-precio');
        selTipo.onchange = () => {
            inpItem.value = '';
            $('stock-info').textContent = '';
            inpPrecio.value = '';
            $('btn-add').disabled = true;
            if (selTipo.value) {
                inpItem.disabled = false;
                $('inp-cant').disabled = false;
                inpPrecio.disabled = false;
                inpItem.focus();
            } else {
                inpItem.disabled = true;
                $('inp-cant').disabled = true;
                inpPrecio.disabled = true;
                sugItem.classList.remove('active');
            }
        };
        inpItem.onfocus = () => selTipo.value && (inpItem.value.trim() === '' ? mostrarItems(selTipo.value) : filtrarItems(selTipo.value, inpItem.value));
        inpItem.oninput = () => selTipo.value && (inpItem.value.trim() === '' ? mostrarItems(selTipo.value) : filtrarItems(selTipo.value, inpItem.value));
        inpItem.onkeydown = e => navegar(e, sugItem, 'itemIdx', () => sugItem.querySelectorAll('.suggestion-item')[itemIdx]?.click());

        function mostrarItems(tipo) {
            const lista = tipo === 'PRODUCTO' ? productos : servicios;
            const disp = lista.filter(it => !selectedItems.some(si => si.tipo === tipo && si.id == it.id));
            sugItem.innerHTML = disp.length === 0 ? '<div class="suggestion-item" style="cursor:default;">Sin items</div>' : disp.map(it => itemSug(it, tipo)).join('');
            sugItem.classList.add('active');
        }
        function filtrarItems(tipo, term) {
            const t = term.toLowerCase();
            const lista = tipo === 'PRODUCTO' ? productos : servicios;
            const disp = lista.filter(it => !selectedItems.some(si => si.tipo === tipo && si.id == it.id));
            itemFilt = disp.filter(it => tipo === 'PRODUCTO' ? (it.nombre_producto.toLowerCase().includes(t) || (it.codigo_producto && it.codigo_producto.toLowerCase().includes(t))) : it.nombre_servicio.toLowerCase().includes(t));
            sugItem.innerHTML = itemFilt.length > 0 ? itemFilt.map(it => itemSug(it, tipo)).join('') : '<div class="suggestion-item" style="cursor:default;">Sin resultados</div>';
            sugItem.classList.add('active');
        }
        function itemSug(it, tipo) {
            if (tipo === 'PRODUCTO') {
                const cls = it.stock_actual <= 0 ? 'has-text-danger' : '';
                return `<div class="suggestion-item" onclick="${it.stock_actual > 0 ? `selItem(${it.id},'PRODUCTO')` : 'alert(\'Sin stock\')'}" style="${it.stock_actual <= 0 ? 'cursor:not-allowed;opacity:0.5;' : ''}">📦 ${it.nombre_producto} ${it.codigo_producto ? '<small>('+it.codigo_producto+')</small>' : ''} <small class="${cls}">Stock: ${it.stock_actual}</small></div>`;
            } else {
                return `<div class="suggestion-item" onclick="selItem(${it.id},'SERVICIO')">🔧 ${it.nombre_servicio} <small>(${it.categoria_servicio})</small></div>`;
            }
        }
        window.selItem = (id, tipo) => {
            const item = tipo === 'PRODUCTO' ? productos.find(p => p.id == id) : servicios.find(s => s.id == id);
            inpItem.value = tipo === 'PRODUCTO' ? item.nombre_producto : item.nombre_servicio;
            inpItem.dataset.selectedId = id;
            if (tipo === 'PRODUCTO') {
                inpPrecio.value = parseFloat(item.precio_venta).toFixed(2);
                inpPrecio.readOnly = true;
                inpPrecio.style.backgroundColor = 'rgba(200,200,200,0.1)';
                inpPrecio.style.cursor = 'not-allowed';
                $('stock-info').textContent = `📦 Stock: ${item.stock_actual}`;
                $('stock-info').style.color = '#27ae60';
            } else {
                const ps = parseFloat(item.precio_sugerido) || 0;
                inpPrecio.value = ps > 0 ? ps.toFixed(2) : '';
                inpPrecio.readOnly = false;
                inpPrecio.style.backgroundColor = 'rgba(236,240,241,0.1)';
                inpPrecio.style.cursor = 'text';
                $('stock-info').textContent = ps > 0 ? '💡 Precio sugerido (modificable)' : '⚠️ Ingrese precio';
                $('stock-info').style.color = ps > 0 ? '#f39c12' : '#e67e22';
            }
            sugItem.classList.remove('active');
            $('btn-add').disabled = false;
            $('inp-cant').focus();
        };

        function navegar(e, sug, idxVar, onEnter) {
            const items = sug.querySelectorAll('.suggestion-item');
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                window[idxVar] = Math.min((window[idxVar] || -1) + 1, items.length - 1);
                actualizarNav(items, window[idxVar]);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                window[idxVar] = Math.max((window[idxVar] || 0) - 1, 0);
                actualizarNav(items, window[idxVar]);
            } else if (e.key === 'Enter' && window[idxVar] >= 0) {
                e.preventDefault();
                onEnter();
                window[idxVar] = -1;
            } else if (e.key === 'Escape') {
                sug.classList.remove('active');
                window[idxVar] = -1;
            }
        }
        function actualizarNav(items, idx) {
            items.forEach((it, i) => {
                if (i === idx) {
                    it.classList.add('highlighted');
                    it.scrollIntoView({ block: 'nearest' });
                } else {
                    it.classList.remove('highlighted');
                }
            });
        }

        // Agregar item
        $('btn-add').onclick = () => {
            const tipo = selTipo.value, id = inpItem.dataset.selectedId;
            const cant = parseFloat($('inp-cant').value) || 0;
            const precio = parseFloat(inpPrecio.value) || 0;
            if (!tipo || !id || cant <= 0) return alert('⚠️ Completa todos los campos');
            if (precio <= 0) { inpPrecio.focus(); return alert('⚠️ Precio > 0'); }
            
            let desc = '', cod = '-';
            if (tipo === 'PRODUCTO') {
                const p = productos.find(pr => pr.id == id);
                desc = p.nombre_producto;
                cod = p.codigo_producto || '-';
                if (cant > p.stock_actual) return alert('⚠️ Stock insuficiente: ' + p.stock_actual);
            } else {
                desc = servicios.find(s => s.id == id).nombre_servicio;
            }
            
            selectedItems.push({ tipo, id, descripcion: desc, codigo: cod, cantidad: cant, precio_unitario: precio, subtotal: parseFloat((cant * precio).toFixed(2)) });
            selTipo.value = '';
            inpItem.value = '';
            inpItem.disabled = true;
            inpItem.dataset.selectedId = '';
            $('inp-cant').value = '1';
            $('inp-cant').disabled = true;
            inpPrecio.value = '';
            inpPrecio.disabled = true;
            inpPrecio.readOnly = false;
            inpPrecio.style.backgroundColor = 'rgba(236,240,241,0.1)';
            $('stock-info').textContent = '';
            $('btn-add').disabled = true;
            renderTabla();
            calcTotales();
        };

        function renderTabla() {
            const cont = $('items-table');
            if (selectedItems.length === 0) {
                cont.innerHTML = '<div class="empty-state">Sin items</div>';
                $('totals').style.display = 'none';
                return;
            }
            let html = '<div class="items-table"><table><thead><tr><th>Tipo</th><th>Item</th><th>Código</th><th>Cant.</th><th>Precio</th><th>Subtotal</th><th>Acción</th></tr></thead><tbody>';
            selectedItems.forEach((it, i) => {
                html += `<tr><td>${it.tipo}</td><td>${escHtml(it.descripcion)}</td><td>${it.codigo}</td><td>${it.cantidad}</td><td>₲ ${it.precio_unitario.toLocaleString('es-PY',{minimumFractionDigits:2})}</td><td><strong>₲ ${it.subtotal.toLocaleString('es-PY',{minimumFractionDigits:2})}</strong></td><td><button type="button" class="btn-remove" onclick="quitarItem(${i})">🗑️</button><input type="hidden" name="items[${i}][tipo]" value="${it.tipo}"><input type="hidden" name="items[${i}][id]" value="${it.id}"><input type="hidden" name="items[${i}][descripcion]" value="${escHtml(it.descripcion)}"><input type="hidden" name="items[${i}][cantidad]" value="${it.cantidad}"><input type="hidden" name="items[${i}][precio]" value="${it.precio_unitario}"></td></tr>`;
            });
            html += '</tbody></table></div>';
            cont.innerHTML = html;
            $('totals').style.display = 'block';
        }

        window.quitarItem = idx => {
            selectedItems.splice(idx, 1);
            renderTabla();
            calcTotales();
        };

        function calcTotales() {
            let sub = 0;
            selectedItems.forEach(it => sub += it.subtotal);
            const iva = parseFloat((sub / 11).toFixed(2));
            $('sub-amt').textContent = '₲ ' + sub.toLocaleString('es-PY', {minimumFractionDigits:2});
            $('iva-amt').textContent = '₲ ' + iva.toLocaleString('es-PY', {minimumFractionDigits:2});
            $('tot-amt').textContent = '₲ ' + sub.toLocaleString('es-PY', {minimumFractionDigits:2});
            $('inp-sub').value = sub.toFixed(2);
            $('inp-tot').value = sub.toFixed(2);
            
            // Recalcular vuelto
            calcularVuelto();
            
            // Asegurar que el evento esté conectado
            const dineroInput = $('dinero-recibido');
            if (dineroInput && !dineroInput.dataset.eventAttached) {
                dineroInput.addEventListener('input', calcularVuelto);
                dineroInput.addEventListener('change', calcularVuelto);
                dineroInput.dataset.eventAttached = 'true';
            }
        }

        // Calcular vuelto en tiempo real
        function calcularVuelto() {
            const total = parseFloat($('inp-tot').value) || 0;
            const recibidoInput = $('dinero-recibido');
            const recibido = recibidoInput ? parseFloat(recibidoInput.value) || 0 : 0;
            const vuelto = recibido - total;
            
            const vueltoDisplay = $('vuelto-amt');
            if (!vueltoDisplay) return;
            
            if (recibido === 0) {
                vueltoDisplay.textContent = '₲ 0,00';
                vueltoDisplay.style.color = '#3498db';
            } else if (vuelto < 0) {
                vueltoDisplay.textContent = '⚠️ FALTA: ₲ ' + Math.abs(vuelto).toLocaleString('es-PY', {minimumFractionDigits:2});
                vueltoDisplay.style.color = '#e74c3c';
            } else {
                vueltoDisplay.textContent = '₲ ' + vuelto.toLocaleString('es-PY', {minimumFractionDigits:2});
                vueltoDisplay.style.color = '#2ecc71';
            }
        }

        // Preview numeración
        $('tipo_comprobante').onchange = function() {
            const tipo = this.value;
            const prev = $('num-prev'), txt = $('num-prev-txt'), ruc = $('cliente_ruc_ci');
            if (tipo === 'FACTURA') {
                txt.innerHTML = '📄 Próxima FACTURA: <strong>001-001-' + String(FACTURA_NUM).padStart(7, '0') + '</strong>';
                prev.style.display = 'block';
                ruc.required = true;
                ruc.style.borderColor = '#f39c12';
            } else if (tipo === 'TICKET') {
                txt.innerHTML = '🎫 Próximo TICKET: <strong>' + String(TICKET_NUM).padStart(7, '0') + '</strong>';
                prev.style.display = 'block';
                ruc.required = false;
                ruc.style.borderColor = '';
            } else {
                prev.style.display = 'none';
                ruc.required = false;
                ruc.style.borderColor = '';
            }
        };

        window.validateForm = () => {
            if (selectedItems.length === 0) return alert('❌ Agrega al menos un item'), false;
            const tipo = $('tipo_comprobante').value;
            if (tipo === 'FACTURA' && !$('cliente_ruc_ci').value.trim()) {
                $('cliente_ruc_ci').focus();
                return alert('❌ RUC/CI obligatorio para FACTURA'), false;
            }
            calcTotales();
            return confirm('✅ ¿Confirmar venta?\n\nSe actualizará el stock automáticamente.');
        };

        // Establecer fecha/hora local de Paraguay al cargar
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        $('fecha_venta').value = `${year}-${month}-${day}T${hours}:${minutes}`;

        renderTabla();
    });
    </script>
</body>
</html>