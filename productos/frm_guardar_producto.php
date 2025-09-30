<?php
// Obtener proveedores para el selector
include_once "../db.php";

$sentenciaProveedores = $conexion->prepare("SELECT id, nombre_proveedor FROM proveedores WHERE estado_proveedor = 1 ORDER BY nombre_proveedor ASC");
$sentenciaProveedores->execute();
$proveedores = $sentenciaProveedores->fetchAll(PDO::FETCH_OBJ);
$proveedoresJSON = json_encode($proveedores);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Producto</title>
    <link href="../css/bulma.min.css" rel="stylesheet">
    <link href="../css/formularios.css" rel="stylesheet">
    <link href="../css/productos-proveedores.css" rel="stylesheet">
    
    <!-- Solo estilos específicos -->
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
    </style>
</head>
<body>
    <?php include '../menu.php'; ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mainContent = document.querySelector('.main-content');
            const proveedores = <?php echo $proveedoresJSON; ?>;
            let selectedProviders = [];

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
                        <div class="provider-item ${isSelected ? 'selected' : ''}" onclick="toggleProvider(${provider.id}, '${provider.nombre_proveedor.replace(/'/g, "\\'")}', event)" id="provider-${provider.id}">
                            <div class="provider-info">
                                <input type="checkbox" class="provider-checkbox" ${isSelected ? 'checked' : ''} 
                                       onchange="toggleProvider(${provider.id}, '${provider.nombre_proveedor.replace(/'/g, "\\'")}', event)"
                                       id="checkbox-${provider.id}">
                                <span>${provider.nombre_proveedor}</span>
                            </div>
                            <div>
                                <span style="color: rgba(255,255,255,0.7); font-size: 0.8rem; margin-right: 5px;">Precio:</span>
                                <input type="number" step="0.01" min="0" class="price-input" 
                                       placeholder="0.00" value="${precio}"
                                       onchange="updatePrice(${provider.id}, this.value)" 
                                       oninput="updatePrice(${provider.id}, this.value)"
                                       onclick="event.stopPropagation()"
                                       id="price-${provider.id}"
                                       ${!isSelected ? 'disabled' : ''}>
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
                        <span class="selected-provider-tag" onclick="removeProvider(${provider.id})" title="Click para eliminar">
                            ${provider.nombre} (${provider.precio || '0.00'}) ×
                        </span>
                        <input type="hidden" name="proveedores[${provider.id}][id]" value="${provider.id}">
                        <input type="hidden" name="proveedores[${provider.id}][precio]" value="${provider.precio || '0.00'}">
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
                
                const providerElement = document.getElementById(`provider-${id}`);
                const checkbox = document.getElementById(`checkbox-${id}`);
                const priceInput = document.getElementById(`price-${id}`);
                
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
                
                const providerElement = document.getElementById(`provider-${id}`);
                const checkbox = document.getElementById(`checkbox-${id}`);
                const priceInput = document.getElementById(`price-${id}`);
                
                if (providerElement && checkbox && priceInput) {
                    providerElement.classList.remove('selected');
                    checkbox.checked = false;
                    priceInput.disabled = true;
                    priceInput.value = '';
                }
                
                renderSelectedProviders();
            };

            const formHTML = `
                <div class="form-container">
                    <h1 class="form-title">Registrar Producto</h1>
                    
                    <form action="./guardar_producto.php" method="post" onsubmit="return validateForm()">
                        <div class="columns">
                            <div class="column is-6">
                                <div class="field">
                                    <label class="label">Nombre del Producto</label>
                                    <div class="control">
                                        <input class="input" type="text" name="nombre_producto" id="nombre_producto" placeholder="Ingresa el nombre del producto" required>
                                    </div>
                                </div>

                                <div class="field">
                                    <label class="label">Código del Producto</label>
                                    <div class="control">
                                        <input class="input" type="text" name="codigo_producto" id="codigo_producto" placeholder="Código de barras o SKU">
                                    </div>
                                </div>

                                <div class="field">
                                    <label class="label">Precio de Venta</label>
                                    <div class="control">
                                        <input class="input" type="number" step="0.01" min="0" name="precio_venta" id="precio_venta" placeholder="0.00" required>
                                    </div>
                                </div>
                            </div>

                            <div class="column is-6">
                                <div class="field">
                                    <label class="label">Stock Actual</label>
                                    <div class="control">
                                        <input class="input" type="number" min="0" name="stock_actual" id="stock_actual" placeholder="Cantidad disponible" value="0">
                                    </div>
                                </div>

                                <div class="field">
                                    <label class="label">Stock Mínimo</label>
                                    <div class="control">
                                        <input class="input" type="number" min="1" name="stock_minimo" id="stock_minimo" placeholder="Cantidad mínima" value="5">
                                    </div>
                                </div>

                                <div class="field">
                                    <label class="label">Estado</label>
                                    <div class="control">
                                        <div class="select is-fullwidth">
                                            <select name="estado_producto" id="estado_producto">
                                                <option value="1">Activo</option>
                                                <option value="0">Inactivo</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="providers-selector">
                            <label class="label">Proveedores y precios de compra</label>
                            <p style="color: rgba(255,255,255,0.7); margin-bottom: 15px; font-size: 0.9rem;">
                                Selecciona los proveedores que suministran este producto y define el precio de compra para cada uno
                            </p>
                            
                            <input type="text" class="search-box" placeholder="Buscar proveedores..." 
                                   oninput="filterProviders(this.value)">
                            
                            <div class="providers-list" id="providers-list"></div>
                            
                            <div class="selected-providers" id="selected-providers-container" style="display: none;">
                                <strong style="color: #27ae60; display: block; margin-bottom: 10px;">
                                    Proveedores seleccionados:
                                </strong>
                                <div id="selected-providers"></div>
                            </div>
                        </div>

                        <div class="field is-grouped" style="justify-content: center; margin-top: 30px;">
                            <div class="control">
                                <button type="submit" class="button">
                                    Guardar Producto
                                </button>
                            </div>
                            <div class="control">
                                <button type="reset" class="button" onclick="resetForm()">
                                    Limpiar Formulario
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            `;
            
            mainContent.innerHTML = formHTML;
            renderProvidersList(proveedores);
            
            window.validateForm = function() {
                const nombre = document.getElementById('nombre_producto').value.trim();
                const precio = document.getElementById('precio_venta').value;
                
                if (!nombre) {
                    alert('Por favor, ingresa el nombre del producto');
                    return false;
                }
                
                if (!precio || parseFloat(precio) <= 0) {
                    alert('Por favor, ingresa un precio de venta válido');
                    return false;
                }
                
                return true;
            };
            
            window.resetForm = function() {
                selectedProviders = [];
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