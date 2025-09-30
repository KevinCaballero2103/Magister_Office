<?php
// Obtener productos para el selector
include_once "../db.php";

$sentenciaProductos = $conexion->prepare("SELECT id, nombre_producto FROM productos WHERE estado_producto = 1 ORDER BY nombre_producto ASC");
$sentenciaProductos->execute();
$productos = $sentenciaProductos->fetchAll(PDO::FETCH_OBJ);
$productosJSON = json_encode($productos);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Proveedor</title>
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
    </style>
</head>
<body>
    <?php include '../menu.php'; ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mainContent = document.querySelector('.main-content');
            const productos = <?php echo $productosJSON; ?>;
            let selectedProducts = [];

            function filterProducts(searchTerm) {
                const filteredProducts = productos.filter(product => 
                    product.nombre_producto.toLowerCase().includes(searchTerm.toLowerCase())
                );
                renderProductsList(filteredProducts);
            }

            function renderProductsList(productsList) {
                const productsListContainer = document.getElementById('products-list');
                let html = '';
                
                productsList.forEach(product => {
                    const selected = selectedProducts.find(p => p.id === product.id);
                    const isSelected = selected !== undefined;
                    const precio = selected ? selected.precio : '';
                    
                    html += `
                        <div class="provider-item ${isSelected ? 'selected' : ''}" onclick="toggleProduct(${product.id}, '${product.nombre_producto.replace(/'/g, "\\'")}', event)" id="product-${product.id}">
                            <div class="provider-info">
                                <input type="checkbox" class="provider-checkbox" ${isSelected ? 'checked' : ''} 
                                       onchange="toggleProduct(${product.id}, '${product.nombre_producto.replace(/'/g, "\\'")}', event)"
                                       id="checkbox-${product.id}">
                                <span>${product.nombre_producto}</span>
                            </div>
                            <div>
                                <span style="color: rgba(255,255,255,0.7); font-size: 0.8rem; margin-right: 5px;">Precio:</span>
                                <input type="number" step="0.01" min="0" class="price-input" 
                                       placeholder="0.00" value="${precio}"
                                       onchange="updatePrice(${product.id}, this.value)" 
                                       oninput="updatePrice(${product.id}, this.value)"
                                       onclick="event.stopPropagation()"
                                       id="price-${product.id}"
                                       ${!isSelected ? 'disabled' : ''}>
                            </div>
                        </div>
                    `;
                });
                
                if (html === '') {
                    html = '<div style="text-align: center; color: rgba(255,255,255,0.6); padding: 20px;">No se encontraron productos</div>';
                }
                
                productsListContainer.innerHTML = html;
            }

            function renderSelectedProducts() {
                const selectedContainer = document.getElementById('selected-products');
                let html = '';
                
                selectedProducts.forEach(product => {
                    html += `
                        <span class="selected-provider-tag" onclick="removeProduct(${product.id})" title="Click para eliminar">
                            ${product.nombre} (${product.precio || '0.00'}) ×
                        </span>
                        <input type="hidden" name="productos[${product.id}][id]" value="${product.id}">
                        <input type="hidden" name="productos[${product.id}][precio]" value="${product.precio || '0.00'}">
                    `;
                });
                
                selectedContainer.innerHTML = html;
                
                const selectedProductsDiv = document.getElementById('selected-products-container');
                if (selectedProducts.length > 0) {
                    selectedProductsDiv.style.display = 'block';
                } else {
                    selectedProductsDiv.style.display = 'none';
                }
            }

            window.toggleProduct = function(id, nombre, event) {
                if (event && event.target && event.target.type === 'checkbox') {
                    event.stopPropagation();
                }
                
                const existingIndex = selectedProducts.findIndex(p => p.id === id);
                
                if (existingIndex > -1) {
                    selectedProducts.splice(existingIndex, 1);
                } else {
                    selectedProducts.push({ id: id, nombre: nombre, precio: '0.00' });
                }
                
                updateProductItem(id);
                renderSelectedProducts();
            };

            function updateProductItem(id) {
                const selected = selectedProducts.find(p => p.id === id);
                const isSelected = selected !== undefined;
                const precio = selected ? selected.precio : '';
                
                const productElement = document.getElementById(`product-${id}`);
                const checkbox = document.getElementById(`checkbox-${id}`);
                const priceInput = document.getElementById(`price-${id}`);
                
                if (productElement && checkbox && priceInput) {
                    if (isSelected) {
                        productElement.classList.add('selected');
                        checkbox.checked = true;
                        priceInput.disabled = false;
                        priceInput.value = precio;
                    } else {
                        productElement.classList.remove('selected');
                        checkbox.checked = false;
                        priceInput.disabled = true;
                        priceInput.value = '';
                    }
                }
            }

            window.updatePrice = function(id, precio) {
                const product = selectedProducts.find(p => p.id === id);
                if (product) {
                    product.precio = precio || '0.00';
                    renderSelectedProducts();
                }
            };

            window.removeProduct = function(id) {
                selectedProducts = selectedProducts.filter(p => p.id != id);
                
                const productElement = document.getElementById(`product-${id}`);
                const checkbox = document.getElementById(`checkbox-${id}`);
                const priceInput = document.getElementById(`price-${id}`);
                
                if (productElement && checkbox && priceInput) {
                    productElement.classList.remove('selected');
                    checkbox.checked = false;
                    priceInput.disabled = true;
                    priceInput.value = '';
                }
                
                renderSelectedProducts();
            };

            const formHTML = `
                <div class="form-container">
                    <h1 class="form-title">Registrar Proveedor</h1>
                    
                    <form action="./guardar_proveedor.php" method="post" onsubmit="return validateForm()">
                        <div class="columns">
                            <div class="column is-6">
                                <div class="field">
                                    <label class="label">Nombre del Proveedor</label>
                                    <div class="control">
                                        <input class="input" type="text" name="nombre_proveedor" id="nombre_proveedor" placeholder="Ingresa el nombre completo" required>
                                    </div>
                                </div>

                                <div class="field">
                                    <label class="label">Teléfono</label>
                                    <div class="control">
                                        <input class="input" type="text" name="telefono_proveedor" id="telefono_proveedor" placeholder="Ingresa el teléfono">
                                    </div>
                                </div>
                            </div>

                            <div class="column is-6">
                                <div class="field">
                                    <label class="label">Dirección</label>
                                    <div class="control">
                                        <input class="input" type="text" name="direccion_proveedor" id="direccion_proveedor" placeholder="Ingresa la dirección">
                                    </div>
                                </div>

                                <div class="field">
                                    <label class="label">Estado</label>
                                    <div class="control">
                                        <div class="select is-fullwidth">
                                            <select name="estado_proveedor" id="estado_proveedor">
                                                <option value="1">Activo</option>
                                                <option value="0">Inactivo</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="providers-selector">
                            <label class="label">Productos y precios de compra</label>
                            <p style="color: rgba(255,255,255,0.7); margin-bottom: 15px; font-size: 0.9rem;">
                                Selecciona los productos que este proveedor suministra y define el precio de compra para cada uno
                            </p>
                            
                            <input type="text" class="search-box" placeholder="Buscar productos..." 
                                   oninput="filterProducts(this.value)">
                            
                            <div class="providers-list" id="products-list"></div>
                            
                            <div class="selected-providers" id="selected-products-container" style="display: none;">
                                <strong style="color: #27ae60; display: block; margin-bottom: 10px;">
                                    Productos seleccionados:
                                </strong>
                                <div id="selected-products"></div>
                            </div>
                        </div>

                        <div class="field is-grouped" style="justify-content: center; margin-top: 30px;">
                            <div class="control">
                                <button type="submit" class="button">
                                    Guardar Proveedor
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
            renderProductsList(productos);
            
            window.validateForm = function() {
                const nombre = document.getElementById('nombre_proveedor').value.trim();
                if (!nombre) {
                    alert('Por favor, ingresa el nombre del proveedor');
                    return false;
                }
                return true;
            };
            
            window.resetForm = function() {
                selectedProducts = [];
                renderProductsList(productos);
                renderSelectedProducts();
                document.querySelector('.search-box').value = '';
                return true;
            };

            window.filterProducts = filterProducts;
        });
    </script>
</body>
</html>