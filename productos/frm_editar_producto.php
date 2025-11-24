<?php
include_once __DIR__ . "/../auth.php";
include_once "../db.php";

// Registrar acceso
registrarActividad('ACCESO', 'PRODUCTOS', 'Acceso a edición de producto', null, null);

if (!isset($_GET["id"])) {
    $error = "Necesito del parámetro id para identificar al producto.";
} else {
    $id = $_GET["id"];
    
    // Obtener datos del producto
    $sentencia = $conexion->prepare("SELECT * FROM productos WHERE id = ?");
    $sentencia->execute([$id]);
    $producto = $sentencia->fetch(PDO::FETCH_OBJ);
    
    if ($producto === FALSE) {
        $error = "El producto indicado no existe en el sistema.";
    } else {
        // Obtener proveedores disponibles
        $sentenciaProveedores = $conexion->prepare("
            SELECT id, nombre_proveedor 
            FROM proveedores 
            WHERE estado_proveedor = 1 
            ORDER BY nombre_proveedor ASC
        ");
        $sentenciaProveedores->execute();
        $proveedores = $sentenciaProveedores->fetchAll(PDO::FETCH_OBJ);
        
        // Obtener proveedores asociados
        $sentenciaAsociados = $conexion->prepare("
            SELECT p.id, p.nombre_proveedor, pp.precio_compra
            FROM proveedores p
            INNER JOIN proveedor_producto pp ON p.id = pp.id_proveedor
            WHERE pp.id_producto = ?
            ORDER BY p.nombre_proveedor ASC
        ");
        $sentenciaAsociados->execute([$id]);
        $proveedoresAsociados = $sentenciaAsociados->fetchAll(PDO::FETCH_OBJ);
    }
}

// Convertir datos a JSON
if (isset($producto)) {
    $productoJSON = json_encode($producto);
    $proveedoresJSON = json_encode($proveedores);
    $proveedoresAsociadosJSON = json_encode($proveedoresAsociados);
} else {
    $productoJSON = 'null';
    $proveedoresJSON = '[]';
    $proveedoresAsociadosJSON = '[]';
    $mensajeError = isset($error) ? $error : 'Error desconocido';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Producto</title>
    <link href="../css/bulma.min.css" rel="stylesheet">
    <link href="../css/formularios.css" rel="stylesheet">
    <link href="../css/productos-proveedores.css" rel="stylesheet">

    <style>
        .main-content {
            background: #2c3e50 !important;
            color: white;
        }

        .audit-section {
            background: rgba(52, 152, 219, 0.1);
            border: 2px solid rgba(52, 152, 219, 0.3);
            padding: 20px;
            border-radius: 10px;
            margin: 25px 0;
        }

        .audit-title {
            color: #3498db;
            font-size: 1.2rem;
            font-weight: bold;
            text-align: center;
            margin-bottom: 15px;
        }

        .audit-help {
            text-align: center;
            color: rgba(255,255,255,0.75);
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<?php include '../menu.php'; ?>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const mainContent = document.querySelector(".main-content");

    const producto = <?php echo $productoJSON; ?>;
    const proveedores = <?php echo $proveedoresJSON; ?>;
    const proveedoresAsociados = <?php echo $proveedoresAsociadosJSON; ?>;

    if (producto === null) {
        const errorMessage = "<?php echo addslashes($mensajeError ?? 'Error desconocido'); ?>";

        mainContent.innerHTML = `
            <div class="error-container">
                <div class="error-title">Error</div>
                <div class="error-message">${errorMessage}</div>
                <a href="./listado_producto.php" class="button">Volver al listado</a>
            </div>
        `;
        return;
    }

    let selectedProviders = proveedoresAsociados.map(p => ({
        id: p.id,
        nombre: p.nombre_proveedor,
        precio: p.precio_compra || "0.00"
    }));

    // ---------- Renderizar lista de proveedores ----------
    function renderProvidersList(lista) {
        const cont = document.getElementById("providers-list");
        let html = "";

        lista.forEach(prov => {
            const sel = selectedProviders.find(p => p.id == prov.id);
            const checked = sel ? "checked" : "";
            const precio = sel ? sel.precio : "";

            html += `
                <div class="provider-item ${checked ? "selected" : ""}" id="provider-${prov.id}">
                    <div class="provider-info" onclick="toggleProvider(${prov.id}, '${prov.nombre_proveedor.replace(/'/g,"\\'")}')">
                        <input type="checkbox" id="checkbox-${prov.id}" ${checked}
                               onclick="event.stopPropagation(); toggleProvider(${prov.id}, '${prov.nombre_proveedor.replace(/'/g,"\\'")}')">
                        <span style="margin-left: 12px;">${prov.nombre_proveedor}</span>
                    </div>

                    <div>
                        <span style="opacity: .8; font-size: .8rem;">Precio:</span>
                        <input type="number" step="0.01" min="0" id="price-${prov.id}"
                               class="price-input"
                               value="${precio}"
                               ${checked ? "" : "disabled"}
                               onclick="event.stopPropagation()"
                               oninput="updatePrice(${prov.id}, this.value)">
                    </div>
                </div>
            `;
        });

        if (html === "") {
            html = `<div style="text-align:center; padding:20px; opacity:.7">No se encontraron proveedores</div>`;
        }

        cont.innerHTML = html;
    }

    // ---------- Renderizar etiquetas seleccionadas ----------
    function renderSelectedProviders() {
        const cont = document.getElementById("selected-providers");
        let html = "";

        selectedProviders.forEach(p => {
            html += `
                <span class="selected-provider-tag" onclick="removeProvider(${p.id})">
                    ${p.nombre} (${p.precio})
                </span>
                <input type="hidden" name="proveedores[${p.id}][id]" value="${p.id}">
                <input type="hidden" name="proveedores[${p.id}][precio]" value="${p.precio}">
            `;
        });

        cont.innerHTML = html;

        const box = document.getElementById("selected-providers-container");
        box.style.display = selectedProviders.length > 0 ? "block" : "none";
    }

    // ---------- Seleccionar/Des seleccionar proveedor ----------
    window.toggleProvider = function(id, nombre) {
        const index = selectedProviders.findIndex(p => p.id == id);

        if (index >= 0) {
            selectedProviders.splice(index, 1);
        } else {
            selectedProviders.push({ id, nombre, precio: "0.00" });
        }

        updateProviderItem(id);
        renderSelectedProviders();
    };

    function updateProviderItem(id) {
        const sel = selectedProviders.find(p => p.id == id);
        const item = document.getElementById(`provider-${id}`);
        const cb = document.getElementById(`checkbox-${id}`);
        const price = document.getElementById(`price-${id}`);

        if (sel) {
            item.classList.add("selected");
            cb.checked = true;
            price.disabled = false;
            price.value = sel.precio;
        } else {
            item.classList.remove("selected");
            cb.checked = false;
            price.disabled = true;
            price.value = "";
        }
    }

    window.updatePrice = function(id, precio) {
        const prov = selectedProviders.find(p => p.id == id);
        if (prov) {
            prov.precio = precio;
            renderSelectedProviders();
        }
    };

    window.removeProvider = function(id) {
        selectedProviders = selectedProviders.filter(p => p.id != id);
        updateProviderItem(id);
        renderSelectedProviders();
    };

    // ---------- Filtro ----------
    window.filterProviders = function(txt) {
        txt = txt.toLowerCase();
        const filtered = proveedores.filter(p => p.nombre_proveedor.toLowerCase().includes(txt));
        renderProvidersList(filtered);
    };

    // ---------- Plantilla principal ----------
    mainContent.innerHTML = `
        <div class="form-container">
            <h1 class="form-title">Editar Producto</h1>

            <form action="./editar_producto.php" method="post" onsubmit="return validateForm()">

                <input type="hidden" name="id" value="${producto.id}">

                <div class="columns">
                    <div class="column is-6">
                        <div class="field">
                            <label class="label">Nombre del Producto</label>
                            <input class="input" type="text" name="nombre_producto" id="nombre_producto" 
                                   required value="${producto.nombre_producto}">
                        </div>

                        <div class="field">
                            <label class="label">Código del Producto</label>
                            <input class="input" type="text" name="codigo_producto" id="codigo_producto"
                                   value="${producto.codigo_producto || ''}">
                        </div>

                        <div class="field">
                            <label class="label">Precio de Venta</label>
                            <input class="input" type="number" step="0.01" min="0" name="precio_venta" 
                                   id="precio_venta" required value="${producto.precio_venta}">
                        </div>
                    </div>

                    <div class="column is-6">
                        <div class="field">
                            <label class="label">Stock Actual</label>
                            <input class="input" type="number" min="0" name="stock_actual"
                                   id="stock_actual" value="${producto.stock_actual}">
                        </div>

                        <div class="field">
                            <label class="label">Stock Mínimo</label>
                            <input class="input" type="number" min="1" name="stock_minimo"
                                   id="stock_minimo" value="${producto.stock_minimo}">
                        </div>

                        <div class="field">
                            <label class="label">Estado</label>
                            <div class="select is-fullwidth">
                                <select name="estado_producto" id="estado_producto">
                                    <option value="1" ${producto.estado_producto == 1 ? "selected" : ""}>Activo</option>
                                    <option value="0" ${producto.estado_producto == 0 ? "selected" : ""}>Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="audit-section">
                    <div class="audit-title">Razón del Cambio (Auditoría)</div>
                    <div class="audit-help">
                        Explica brevemente por qué estás modificando este producto.
                    </div>

                    <textarea class="textarea" name="razon_cambio" id="razon_cambio" rows="3"
                              required placeholder="Ejemplo: actualización de precio, cambio de proveedor..."
                              style="background: rgba(255,255,255,0.1); color: white; border: 2px solid rgba(52,152,219,0.4);"></textarea>
                </div>

                <div class="providers-selector">
                    <label class="label">Proveedores y precios de compra</label>

                    <input type="text" class="search-box" placeholder="Buscar proveedores..."
                           oninput="filterProviders(this.value)">

                    <div class="providers-list" id="providers-list"></div>

                    <div class="selected-providers" id="selected-providers-container" style="display:none;">
                        <strong style="color:#27ae60;">Proveedores seleccionados:</strong>
                        <div id="selected-providers"></div>
                    </div>
                </div>

                <div class="button-group">
                    <button type="submit" class="button is-primary">Guardar Cambios</button>
                    <button type="reset" class="button is-light" onclick="resetForm()">Restaurar Valores</button>
                    <a href="./listado_producto.php" class="button is-light">Volver al listado</a>
                </div>
            </form>
        </div>
    `;

    renderProvidersList(proveedores);
    renderSelectedProviders();

    window.validateForm = function () {
        const nombre = document.getElementById("nombre_producto").value.trim();
        const precio = document.getElementById("precio_venta").value;
        const razon = document.getElementById("razon_cambio").value.trim();

        if (!nombre) return alert("El nombre del producto es obligatorio.") || false;
        if (!precio || precio <= 0) return alert("El precio debe ser válido.") || false;
        if (!razon || razon.length < 10) return alert("La razón del cambio debe tener al menos 10 caracteres.") || false;

        return confirm("¿Confirmar la actualización del producto?");
    };

    window.resetForm = function () {
        selectedProviders = proveedoresAsociados.map(p => ({
            id: p.id,
            nombre: p.nombre_proveedor,
            precio: p.precio_compra || "0.00"
        }));
        renderProvidersList(proveedores);
        renderSelectedProviders();
    };
});
</script>

</body>
</html>