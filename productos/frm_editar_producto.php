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
        $sentenciaProveedores = $conexion->prepare("SELECT id, nombre_proveedor FROM proveedores WHERE estado_proveedor = 1 ORDER BY nombre_proveedor ASC");
        $sentenciaProveedores->execute();
        $proveedores = $sentenciaProveedores->fetchAll(PDO::FETCH_OBJ);
        
        // Obtener proveedores asociados al producto con sus precios
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

// Convertir datos para JavaScript
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

        /* NUEVO: Estilos para sección de auditoría */
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
            margin-bottom: 15px;
            text-align: center;
        }

        .audit-help {
            color: rgba(255,255,255,0.7);
            font-size: 0.9rem;
            text-align: center;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <?php include '../menu.php'; ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mainContent = document.querySelector('.main-content');
            const producto = <?php echo $productoJSON; ?>;
            const proveedores = <?php echo $proveedoresJSON; ?>;
            const proveedoresAsociados = <?php echo $proveedoresAsociadosJSON; ?>;
            
            if (producto === null) {
                const errorMessage = '<?php echo isset($mensajeError) ? addslashes($mensajeError) : 'Error desconocido'; ?>';
                
                const errorHTML = `
                    <div class='error-container'>
                        <div class='error-title'>Error</div>
                        <div class='error-message'>\${errorMessage}</div>
                        <a href='./listado_producto.php' class='button'>
                            Volver al Listado
                        </a>
                    </div>
                `;
                
                mainContent.innerHTML = errorHTML;
                return;
            }
            
            let selectedProviders = proveedoresAsociados.map(p => ({
                id: p.id,
                nombre: p.nombre_proveedor,
                precio: p.precio_compra || '0.00'
            }));

            function filterProviders(searchTerm) {
                const filteredProviders = proveedores.filter(provider => 
                    provider.nombre_proveedor.toLowerCase().includes(searchTerm.toLowerCase())
                );
                renderProvidersList(filteredProviders);
            }

            function renderProvidersList(providersList) {
                const providersListContainer = document.getElementById('providers-list');
                let html = '';
                
                providersList.forEach(provider => {
                    const selected = selectedProviders.find(p => p.id === provider.id);
                    const isSelected = selected !== undefined;
                    const precio = selected ? selected.precio : '';
                    
                    html += `
                        <div class="provider-item \${isSelected ? 'selected' : ''}" onclick="toggleProvider(\${provider.id}, '\${provider.nombre_proveedor.replace(/'/g, "\\'")}', event)" id="provider-\${provider.id}">
                            <div class="provider-info">
                                <input type="checkbox" class="provider-checkbox" \${isSelected ? 'checked' : ''} 
                                       onchange="toggleProvider(\${provider.id}, '\${provider.nombre_proveedor.replace(/'/g, "\\'")}', event)"
                                       id="checkbox-\${provider.id}">
                                <span>\${provider.nombre_proveedor}</span>
                            </div>
                            <div>
                                <span style="color: rgba(255,255,255,0.7); font-size: 0.8rem; margin-right: 5px;">Precio:</span>
                                <input type="number" step="0.01" min="0" class="price-input" 
                                       placeholder="0.00" value="\${precio}"
                                       onchange="updatePrice(\${provider.id}, this.value)" 
                                       oninput="updatePrice(\${provider.id}, this.value)"
                                       onclick="event.stopPropagation()"
                                       id="price-\${provider.id}"
                                       \${!isSelected ? 'disabled' : ''}>
                            </div>
                        </div>
                    `;
                });
                
                if (html === '') {
                    html = '<div style="text-align: center; color: rgba(255,255,255,0.6); padding: 20px;">No se encontraron proveedores</div>';
                }
                
                providersListContainer.innerHTML = html;
            }

            function renderSelectedProviders() {
                const selectedContainer = document.getElementById('selected-providers');
                let html = '';
                
                selectedProviders.forEach(provider => {
                    html += `
                        <span class="selected-provider-tag" onclick="removeProvider(\${provider.id})" title="Click para eliminar">
                            \${provider.nombre} (\${provider.precio || '0.00'}) ×
                        </span>
                        <input type="hidden" name="proveedores[\${provider.id}][id]" value="\${provider.id}">
                        <input type="hidden" name="proveedores[\${provider.id}][precio]" value="\${provider.precio || '0.00'}">
                    `;
                });
                
                selectedContainer.innerHTML = html;
                
                const selectedProvidersDiv = document.querySelector('.selected-providers');
                if (selectedProviders.length > 0) {
                    selectedProvidersDiv.style.display = 'block';
                } else {
                    selectedProvidersDiv.style.display = 'none';
                }
            }

            window.toggleProvider = function(id, nombre, event) {
                if (event && event.target && event.target.type === 'checkbox') {
                    event.stopPropagation();
                }
                
                const existingIndex = selectedProviders.findIndex(p => p.id === id);
                
                if (existingIndex > -1) {
                    selectedProviders.splice(existingIndex, 1);
                } else {
                    selectedProviders.push({ id: id, nombre: nombre, precio: '0.00' });
                }
                
                updateProviderItem(id, nombre);
                renderSelectedProviders();
            };

            function updateProviderItem(id, nombre) {
                const selected = selectedProviders.find(p => p.id === id);
                const isSelected = selected !== undefined;
                const precio = selected ? selected.precio : '';
                
                const providerElement = document.getElementById(`provider-\${id}`);
                const checkbox = document.getElementById(`checkbox-\${id}`);
                const priceInput = document.getElementById(`price-\${id}`);
                
                if (providerElement && checkbox && priceInput) {
                    if (isSelected) {
                        providerElement.classList.add('selected');
                        checkbox.checked = true;
                        priceInput.disabled = false;
                        priceInput.value = precio;
                    } else {
                        providerElement.classList.remove('selected');
                        checkbox.checked = false;
                        priceInput.disabled = true;
                        priceInput.value = '';
                    }
                }
            }

            window.updatePrice = function(id, precio) {
                const provider = selectedProviders.find(p => p.id === id);
                if (provider) {
                    provider.precio = precio || '0.00';
                    renderSelectedProviders();
                }
            };

            window.removeProvider = function(id) {
                selectedProviders = selectedProviders.filter(p => p.id != id);
                
                const providerElement = document.getElementById(`provider-\${id}`);
                const checkbox = document.getElementById(`checkbox-\${id}`);
                const priceInput = document.getElementById(`price-\${id}`);
                
                if (providerElement && checkbox && priceInput) {
                    providerElement.classList.remove('selected');
                    checkbox.checked = false;
                    priceInput.disabled = true;
                    priceInput.value = '';
                }
                
                renderSelectedProviders();
            };
            
            const contentHTML = `
                <div class='form-container'>
                    <h1 class='form-title'>Editar Producto</h1>
                    
                    <form action='./editar_producto.php' method='post' onsubmit='return validateForm()'>
                        <input type='hidden' name='id' value='\${producto.id}'>
                        
                        <div class='columns'>
                            <div class='column is-6'>
                                <div class='field'>
                                    <label class='label'>Nombre del Producto</label>
                                    <div class='control'>
                                        <input class='input' type='text' name='nombre_producto' id='nombre_producto' 
                                               placeholder='Ingresa el nombre del producto' required 
                                               value='\${producto.nombre_producto || ''}'>
                                    </div>
                                </div>

                                <div class='field'>
                                    <label class='label'>Código del Producto</label>
                                    <div class='control'>
                                        <input class='input' type='text' name='codigo_producto' id='codigo_producto' 
                                               placeholder='Código de barras o SKU'
                                               value='\${producto.codigo_producto || ''}'>
                                    </div>
                                </div>

                                <div class='field'>
                                    <label class='label'>Precio de Venta</label>
                                    <div class='control'>
                                        <input class='input' type='number' step='0.01' min='0' name='precio_venta' id='precio_venta' 
                                               placeholder='0.00' required
                                               value='\${producto.precio_venta || ''}'>
                                    </div>
                                </div>
                            </div>

                            <div class='column is-6'>
                                <div class='field'>
                                    <label class='label'>Stock Actual</label>
                                    <div class='control'>
                                        <input class='input' type='number' min='0' name='stock_actual' id='stock_actual' 
                                               placeholder='Cantidad disponible'
                                               value='\${producto.stock_actual || 0}'>
                                    </div>
                                </div>

                                <div class='field'>
                                    <label class='label'>Stock Mínimo</label>
                                    <div class='control'>
                                        <input class='input' type='number' min='1' name='stock_minimo' id='stock_minimo' 
                                               placeholder='Cantidad mínima'
                                               value='\${producto.stock_minimo || 5}'>
                                    </div>
                                </div>

                                <div class='field'>
                                    <label class='label'>Estado</label>
                                    <div class='control'>
                                        <div class='select is-fullwidth'>
                                            <select name='estado_producto' id='estado_producto'>
                                                <option value='1' \${(producto.estado_producto == 1 || producto.estado == 1) ? 'selected' : ''}>Activo</option>
                                                <option value='0' \${(producto.estado_producto == 0 || producto.estado == 0) ? 'selected' : ''}>Inactivo</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class='audit-section'>
                            <div class='audit-title'>📝 Razón del Cambio (Auditoría)</div>
                            <div class='audit-help'>
                                Explica brevemente por qué estás modificando este producto. Esto quedará registrado en el historial de auditoría.
                            </div>
                            <div class='field'>
                                <div class='control'>
                                    <textarea class='textarea' name='razon_cambio' id='razon_cambio' rows='3' 
                                              placeholder='Ejemplo: Actualización de precio por inflación, Cambio de proveedor, Corrección de error en stock...'
                                              required
                                              style='background: rgba(236, 240, 241, 0.1) !important; border: 2px solid rgba(52, 152, 219, 0.5) !important; color: white !important;'></textarea>
                                </div>
                            </div>
                        </div>

                        <div class='providers-selector'>
                            <label class='label'>Proveedores y precios de compra</label>
                            <p style='color: rgba(255,255,255,0.7); margin-bottom: 15px; font-size: 0.9rem;'>
                                Selecciona los proveedores que suministran este producto y define el precio de compra para cada uno
                            </p>
                            
                            <input type='text' class='search-box' placeholder='Buscar proveedores...' 
                                   oninput='filterProviders(this.value)'>
                            
                            <div class='providers-list' id='providers-list'></div>
                            
                            <div class='selected-providers' id='selected-providers-container' style='display: none;'>
                                <strong style='color: #27ae60; display: block; margin-bottom: 10px;'>
                                    Proveedores seleccionados:
                                </strong>
                                <div id='selected-providers'></div>
                            </div>
                        </div>

                        <div class='button-group'>
                            <button type='submit' class='button'>
                                💾 Guardar Cambios
                            </button>
                            
                            <button type='reset' class='secondary-button' onclick='resetForm()'>
                                🔄 Restaurar Valores
                            </button>
                            
                            <a href='./listado_producto.php' class='secondary-button'>
                                ⬅️ Volver al Listado
                            </a>
                        </div>
                    </form>
                </div>
            `;
            
            mainContent.innerHTML = contentHTML;
            renderProvidersList(proveedores);
            renderSelectedProviders();
            
            window.validateForm = function() {
                const nombre = document.getElementById('nombre_producto').value.trim();
                const precio = document.getElementById('precio_venta').value;
                const razon = document.getElementById('razon_cambio').value.trim();
                
                if (!nombre) {
                    alert('⚠️ Por favor, ingresa el nombre del producto');
                    return false;
                }
                
                if (!precio || parseFloat(precio) <= 0) {
                    alert('⚠️ Por favor, ingresa un precio de venta válido');
                    return false;
                }

                if (!razon) {
                    alert('⚠️ Por favor, explica la razón del cambio para la auditoría');
                    document.getElementById('razon_cambio').focus();
                    return false;
                }

                if (razon.length < 10) {
                    alert('⚠️ La razón del cambio debe tener al menos 10 caracteres');
                    document.getElementById('razon_cambio').focus();
                    return false;
                }
                
                return confirm('¿Confirmar la actualización del producto?\\n\\nRazón: ' + razon);
            };
            
            window.resetForm = function() {
                selectedProviders = proveedoresAsociados.map(p => ({
                    id: p.id,
                    nombre: p.nombre_proveedor,
                    precio: p.precio_compra || '0.00'
                }));
                renderProvidersList(proveedores);
                renderSelectedProviders();
                document.querySelector('.search-box').value = '';
                return true;
            };

            window.filterProviders = filterProviders;
        });
    </script>
</body>
</html>