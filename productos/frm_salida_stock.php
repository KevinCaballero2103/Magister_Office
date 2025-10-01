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
    }
}

// Convertir datos para JavaScript
if (isset($producto)) {
    $productoJSON = json_encode($producto);
} else {
    $productoJSON = 'null';
    $mensajeError = isset($error) ? $error : 'Error desconocido';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Salida de Stock</title>
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
            background: rgba(231, 76, 60, 0.1);
            border: 2px solid rgba(231, 76, 60, 0.3);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
        }

        .info-box h3 {
            color: #e74c3c;
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
            background: rgba(231, 76, 60, 0.1);
            border: 2px solid rgba(231, 76, 60, 0.3);
            border-radius: 10px;
            padding: 15px;
            margin-top: 15px;
            text-align: center;
        }

        .stock-preview h4 {
            color: #e74c3c;
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
            color: #e74c3c;
        }

        .warning-box {
            background: rgba(230, 126, 34, 0.1);
            border: 2px solid rgba(230, 126, 34, 0.5);
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
            color: #e67e22;
            font-weight: bold;
            text-align: center;
            display: none;
        }

        .button-group {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            align-items: center;
            margin-top: 1.5rem;
            flex-wrap: wrap;
        }

        .button-group .button,
        .button-group .secondary-button {
            min-width: 180px;
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
    </style>
</head>
<body>
    <?php include '../menu.php'; ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mainContent = document.querySelector('.main-content');
            const producto = <?php echo $productoJSON; ?>;
            
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
            
            const contentHTML = `
                <div class='form-container'>
                    <h1 class='form-title'>Registrar Salida de Stock</h1>
                    
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
                    
                    <form action='./guardar_salida_stock.php' method='post' onsubmit='return validateForm()'>
                        <input type='hidden' name='id_producto' value='${producto.id}'>
                        <input type='hidden' name='stock_actual' value='${producto.stock_actual}'>
                        
                        <div class='columns'>
                            <div class='column is-6'>
                                <div class='field'>
                                    <label class='label'>Cantidad a Retirar *</label>
                                    <div class='control'>
                                        <input class='input' type='number' name='cantidad' id='cantidad' 
                                               placeholder='Ingresa la cantidad' required min='1' max='${producto.stock_actual}'
                                               oninput='updateStockPreview()'>
                                    </div>
                                    <p class='help'>Máximo disponible: ${producto.stock_actual} unidades</p>
                                </div>

                                <div class='field'>
                                    <label class='label'>Motivo *</label>
                                    <div class='control'>
                                        <div class='select is-fullwidth'>
                                            <select name='motivo' id='motivo' required>
                                                <option value="">-- Seleccione --</option>
                                                <option value="Venta">Venta</option>
                                                <option value="Merma">Merma / Producto Dañado</option>
                                                <option value="Vencimiento">Vencimiento</option>
                                                <option value="Devolución Proveedor">Devolución a Proveedor</option>
                                                <option value="Uso Interno">Uso Interno</option>
                                                <option value="Ajuste Inventario">Ajuste de Inventario</option>
                                                <option value="Robo">Robo / Pérdida</option>
                                                <option value="Otro">Otro</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class='column is-6'>
                                <div class='field'>
                                    <label class='label'>Observaciones *</label>
                                    <div class='control'>
                                        <textarea class='textarea' name='observaciones' id='observaciones' 
                                                  placeholder='Detalla el motivo de esta salida...' rows='6' required></textarea>
                                    </div>
                                    <p class='help'>Importante: Describe claramente la razón de esta salida</p>
                                </div>
                            </div>
                        </div>

                        <div class='warning-box' id='warning-box'>
                            ⚠️ ADVERTENCIA: La cantidad ingresada supera el stock disponible
                        </div>

                        <div class='stock-preview' id='stock-preview' style='display: none;'>
                            <h4>Vista Previa del Stock</h4>
                            <div class='stock-calculation'>
                                <span class='stock-current'>${producto.stock_actual}</span>
                                <span> - </span>
                                <span id='cantidad-preview'>0</span>
                                <span> = </span>
                                <span class='stock-new' id='stock-nuevo-preview'>${producto.stock_actual}</span>
                            </div>
                        </div>

                        <div class='button-group'>
                            <button type='submit' class='button'>
                                Registrar Salida
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
            
            window.updateStockPreview = function() {
                const cantidad = parseInt(document.getElementById('cantidad').value, 10) || 0;
                const stockActualInput = document.querySelector("input[name='stock_actual']");
                const stockActual = stockActualInput ? Number(stockActualInput.value) || 0 : 0;
                const stockNuevo = stockActual - cantidad;
                
                const preview = document.getElementById('stock-preview');
                const warningBox = document.getElementById('warning-box');
                
                if (cantidad > 0) {
                    preview.style.display = 'block';
                    document.getElementById('cantidad-preview').textContent = cantidad;
                    document.getElementById('stock-nuevo-preview').textContent = stockNuevo;
                    
                    // Mostrar advertencia si se excede el stock
                    if (cantidad > stockActual) {
                        warningBox.style.display = 'block';
                    } else {
                        warningBox.style.display = 'none';
                    }
                } else {
                    preview.style.display = 'none';
                    warningBox.style.display = 'none';
                }
            };
            
            window.resetPreview = function() {
                const preview = document.getElementById('stock-preview');
                const warningBox = document.getElementById('warning-box');
                if (preview) preview.style.display = 'none';
                if (warningBox) warningBox.style.display = 'none';
                
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
                const observaciones = document.getElementById('observaciones').value.trim();
                const stockActualInput = document.querySelector("input[name='stock_actual']");
                const stockActual = stockActualInput ? Number(stockActualInput.value) || 0 : 0;
                
                if (!cantidad || cantidad <= 0) {
                    alert('Por favor, ingresa una cantidad válida mayor a 0');
                    return false;
                }
                
                if (cantidad > stockActual) {
                    alert('ERROR: La cantidad a retirar (' + cantidad + ') excede el stock disponible (' + stockActual + ')');
                    return false;
                }
                
                if (!motivo) {
                    alert('Por favor, selecciona un motivo para esta salida');
                    return false;
                }
                
                if (!observaciones) {
                    alert('Por favor, ingresa observaciones detallando esta salida');
                    return false;
                }
                
                return confirm('¿Confirmas registrar esta salida de stock?\\n\\nCantidad: ' + cantidad + ' unidades\\nStock resultante: ' + (stockActual - cantidad));
            };
        });
    </script>
</body>
</html>