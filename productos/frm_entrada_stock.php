<?php
// Validación y obtención de datos al inicio
if (!isset($_GET["id"])) {
    $error = "Necesito el parámetro id para identificar al producto.";
} else {
    include '../db.php';
    $id = $_GET["id"];
    
    // Obtener datos del producto
    $sentencia = $conexion->prepare("SELECT * FROM productos WHERE id = ?");
    $sentencia->execute([$id]);
    $producto = $sentencia->fetch(PDO::FETCH_OBJ);
    
    if ($producto === FALSE) {
        $error = "El producto indicado no existe en el sistema.";
    } else {
        // Obtener proveedores activos
        $sentenciaProveedores = $conexion->prepare("SELECT id, nombre_proveedor FROM proveedores WHERE estado_proveedor = 1 ORDER BY nombre_proveedor ASC");
        $sentenciaProveedores->execute();
        $proveedores = $sentenciaProveedores->fetchAll(PDO::FETCH_OBJ);
    }
}

// Convertir datos para JavaScript
if (isset($producto)) {
    $productoJSON = json_encode($producto);
    $proveedoresJSON = json_encode($proveedores);
} else {
    $productoJSON = 'null';
    $proveedoresJSON = '[]';
    $mensajeError = isset($error) ? $error : 'Error desconocido';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Entrada de Stock</title>
    <link href="../css/bulma.min.css" rel="stylesheet">
    <link href="../css/formularios.css" rel="stylesheet">
    
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

        .info-box {
            background: rgba(52, 152, 219, 0.1);
            border: 2px solid rgba(52, 152, 219, 0.3);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
        }

        .info-box h3 {
            color: #3498db;
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            color: #f1c40f;
            font-weight: 600;
        }

        .info-value {
            color: #ecf0f1;
        }

        .stock-preview {
            background: rgba(39, 174, 96, 0.1);
            border: 2px solid rgba(39, 174, 96, 0.3);
            border-radius: 10px;
            padding: 15px;
            margin-top: 15px;
            text-align: center;
        }

        .stock-preview h4 {
            color: #27ae60;
            font-size: 1rem;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .stock-calculation {
            font-size: 1.5rem;
            color: #ecf0f1;
            font-weight: bold;
        }

        .stock-current {
            color: #3498db;
        }

        .stock-new {
            color: #27ae60;
        }

        /* ======= Estilos para centrar y espaciar los botones (Opción 1) ======= */
        .button-group {
            display: flex;
            justify-content: center; /* centra el grupo */
            gap: 1.5rem;             /* separación entre botones */
            align-items: center;
            margin-top: 1.5rem;
            flex-wrap: wrap;         /* permite que bajen en pantallas pequeñas */
        }

        .button-group .button,
        .button-group .secondary-button {
            min-width: 180px;       /* ancho mínimo igual para cada botón (ajustalo si querés) */
            text-align: center;
        }

        @media (max-width: 600px) {
            .button-group {
                flex-direction: column;
                gap: 0.75rem;
            }
            .button-group .button,
            .button-group .secondary-button {
                width: 100%;
                min-width: unset;
            }
        }
        /* ====================================================================== */
    </style>
</head>
<body>
    <?php include '../menu.php'; ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mainContent = document.querySelector('.main-content');
            const producto = <?php echo $productoJSON; ?>;
            const proveedores = <?php echo $proveedoresJSON; ?>;
            
            if (producto === null) {
                const errorMessage = '<?php echo isset($mensajeError) ? addslashes($mensajeError) : 'Error desconocido'; ?>';
                
                const errorHTML = `
                    <div class='error-container'>
                        <div class='error-title'>Error</div>
                        <div class='error-message'>${errorMessage}</div>
                        <a href='./gestionar_stock.php' class='button'>
                            Volver a Gestión de Stock
                        </a>
                    </div>
                `;
                
                if (mainContent) {
                    mainContent.innerHTML = errorHTML;
                } else {
                    document.body.innerHTML = errorHTML;
                }
                return;
            }
            
            let proveedoresOptions = '<option value="">-- Seleccione Proveedor (Opcional) --</option>';
            proveedores.forEach(proveedor => {
                proveedoresOptions += `<option value="${proveedor.id}">${proveedor.nombre_proveedor}</option>`;
            });
            
            const contentHTML = `
                <div class='form-container'>
                    <h1 class='form-title'>Registrar Entrada de Stock</h1>
                    
                    <div class='info-box'>
                        <h3>Información del Producto</h3>
                        <div class='info-item'>
                            <span class='info-label'>Producto:</span>
                            <span class='info-value'>${producto.nombre_producto}</span>
                        </div>
                        <div class='info-item'>
                            <span class='info-label'>Código:</span>
                            <span class='info-value'>${producto.codigo_producto || '-'}</span>
                        </div>
                        <div class='info-item'>
                            <span class='info-label'>Stock Actual:</span>
                            <span class='info-value stock-current' id='stock-actual-display'>${producto.stock_actual}</span>
                        </div>
                        <div class='info-item'>
                            <span class='info-label'>Stock Mínimo:</span>
                            <span class='info-value'>${producto.stock_minimo}</span>
                        </div>
                    </div>
                    
                    <form action='./guardar_entrada_stock.php' method='post' onsubmit='return validateForm()'>
                        <input type='hidden' name='id_producto' value='${producto.id}'>
                        <input type='hidden' name='stock_actual' value='${producto.stock_actual}'>
                        
                        <div class='columns'>
                            <div class='column is-6'>
                                <div class='field'>
                                    <label class='label'>Cantidad a Ingresar *</label>
                                    <div class='control'>
                                        <input class='input' type='number' name='cantidad' id='cantidad' 
                                               placeholder='Ingresa la cantidad' required min='1'
                                               oninput='updateStockPreview()'>
                                    </div>
                                    <p class='help'>Ingresa cuántas unidades deseas agregar al inventario</p>
                                </div>

                                <div class='field'>
                                    <label class='label'>Proveedor</label>
                                    <div class='control'>
                                        <div class='select is-fullwidth'>
                                            <select name='id_proveedor' id='id_proveedor'>
                                                ${proveedoresOptions}
                                            </select>
                                        </div>
                                    </div>
                                    <p class='help'>Opcional: Selecciona el proveedor de esta compra</p>
                                </div>
                            </div>

                            <div class='column is-6'>
                                <div class='field'>
                                    <label class='label'>Precio de Compra Unitario</label>
                                    <div class='control'>
                                        <input class='input' type='number' step='0.01' min='0' name='precio_compra' id='precio_compra' 
                                               placeholder='0.00'>
                                    </div>
                                    <p class='help'>Opcional: Precio unitario pagado al proveedor</p>
                                </div>

                                <div class='field'>
                                    <label class='label'>Motivo *</label>
                                    <div class='control'>
                                        <div class='select is-fullwidth'>
                                            <select name='motivo' id='motivo' required>
                                                <option value="">-- Seleccione --</option>
                                                <option value="Compra">Compra</option>
                                                <option value="Devolución Cliente">Devolución de Cliente</option>
                                                <option value="Ajuste Inventario">Ajuste de Inventario</option>
                                                <option value="Producción">Producción Interna</option>
                                                <option value="Otro">Otro</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class='field'>
                            <label class='label'>Observaciones</label>
                            <div class='control'>
                                <textarea class='textarea' name='observaciones' id='observaciones' 
                                          placeholder='Información adicional sobre esta entrada...' rows='3'></textarea>
                            </div>
                        </div>

                        <div class='stock-preview' id='stock-preview' style='display: none;'>
                            <h4>Vista Previa del Stock</h4>
                            <div class='stock-calculation'>
                                <span class='stock-current'>${producto.stock_actual}</span>
                                <span> + </span>
                                <span id='cantidad-preview'>0</span>
                                <span> = </span>
                                <span class='stock-new' id='stock-nuevo-preview'>${producto.stock_actual}</span>
                            </div>
                        </div>

                        <div class='button-group'>
                            <button type='submit' class='button'>
                                Registrar Entrada
                            </button>
                            
                            <button type='reset' class='secondary-button' onclick='resetPreview()'>
                                Limpiar Formulario
                            </button>
                            
                            <a href='./gestionar_stock.php' class='secondary-button'>
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            `;
            
            if (mainContent) {
                mainContent.innerHTML = contentHTML;
            } else {
                document.body.innerHTML = contentHTML;
            }
            
            // ====== Función updateStockPreview (Opción B: leer input hidden 'stock_actual') ======
            window.updateStockPreview = function() {
                const cantidad = parseInt(document.getElementById('cantidad').value, 10) || 0;

                // Opción B: leer el stock actual desde el input hidden del formulario
                const stockActualInput = document.querySelector("input[name='stock_actual']");
                const stockActual = stockActualInput ? Number(stockActualInput.value) || 0 : 0;

                const stockNuevo = stockActual + cantidad;

                const preview = document.getElementById('stock-preview');

                if (cantidad > 0) {
                    preview.style.display = 'block';
                    document.getElementById('cantidad-preview').textContent = cantidad;
                    document.getElementById('stock-nuevo-preview').textContent = stockNuevo;
                } else {
                    preview.style.display = 'none';
                }
            };
            // =============================================================================

            window.resetPreview = function() {
                const preview = document.getElementById('stock-preview');
                if (preview) preview.style.display = 'none';
                // también resetear la vista del contador
                const cantidadPreview = document.getElementById('cantidad-preview');
                const stockNuevoPreview = document.getElementById('stock-nuevo-preview');
                if (cantidadPreview) cantidadPreview.textContent = '0';
                if (stockNuevoPreview && document.querySelector("input[name='stock_actual']")) {
                    stockNuevoPreview.textContent = document.querySelector("input[name='stock_actual']").value;
                }
                return true;
            };
            
            window.validateForm = function() {
                const cantidad = parseInt(document.getElementById('cantidad').value, 10);
                const motivo = document.getElementById('motivo').value;
                
                if (!cantidad || cantidad <= 0) {
                    alert('Por favor, ingresa una cantidad válida mayor a 0');
                    return false;
                }
                
                if (!motivo) {
                    alert('Por favor, selecciona un motivo para esta entrada');
                    return false;
                }
                
                return confirm('¿Confirmas registrar esta entrada de stock?');
            };
        });
    </script>
</body>
</html>
