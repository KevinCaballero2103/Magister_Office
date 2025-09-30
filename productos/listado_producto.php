<?php
// Procesamiento de datos al inicio
include_once "../db.php";

$estado = isset($_GET['estado']) ? $_GET['estado'] : "99";
$tipo_busqueda = isset($_GET['tipo_busqueda']) ? $_GET['tipo_busqueda'] : "todos";
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : "";
$orden = isset($_GET['orden']) ? $_GET['orden'] : "nombre_asc";

// Construir condiciones WHERE
$condiciones = array();

// Filtro por estado
if ($estado !== "99") {
    $condiciones[] = "p.estado_producto = " . intval($estado);
}

// Filtro por búsqueda
if (!empty($busqueda) && $tipo_busqueda !== "todos") {
    switch ($tipo_busqueda) {
        case "nombre":
            $condiciones[] = "p.nombre_producto LIKE '%" . $busqueda . "%'";
            break;
        case "codigo":
            $condiciones[] = "p.codigo_producto LIKE '%" . $busqueda . "%'";
            break;
        case "precio":
            $condiciones[] = "p.precio_venta = " . floatval($busqueda);
            break;
    }
}

// Construir WHERE clause
$where_clause = "";
if (!empty($condiciones)) {
    $where_clause = "WHERE " . implode(" AND ", $condiciones);
}

// Determinar orden
switch ($orden) {
    case "id_asc":
        $order_by = "ORDER BY p.id ASC";
        break;
    case "id_desc":
        $order_by = "ORDER BY p.id DESC";
        break;
    case "nombre_desc":
        $order_by = "ORDER BY p.nombre_producto DESC";
        break;
    case "precio_asc":
        $order_by = "ORDER BY p.precio_venta ASC";
        break;
    case "precio_desc":
        $order_by = "ORDER BY p.precio_venta DESC";
        break;
    case "stock_asc":
        $order_by = "ORDER BY p.stock_actual ASC";
        break;
    case "stock_desc":
        $order_by = "ORDER BY p.stock_actual DESC";
        break;
    default:
        $order_by = "ORDER BY p.nombre_producto ASC";
        break;
}

$sentencia = $conexion->prepare("
    SELECT p.* 
    FROM productos p
    $where_clause
    $order_by
");

$sentencia->execute();
$productos = $sentencia->fetchAll(PDO::FETCH_OBJ);

// Obtener proveedores asociados para cada producto
$productosConProveedores = array();
foreach ($productos as $producto) {
    $sentenciaProveedores = $conexion->prepare("
        SELECT pr.nombre_proveedor, pp.precio_compra
        FROM proveedores pr
        INNER JOIN proveedor_producto pp ON pr.id = pp.id_proveedor
        WHERE pp.id_producto = ?
        ORDER BY pr.nombre_proveedor ASC
    ");
    $sentenciaProveedores->execute([$producto->id]);
    $proveedores = $sentenciaProveedores->fetchAll(PDO::FETCH_OBJ);
    
    $producto->proveedores = $proveedores;
    $productosConProveedores[] = $producto;
}

// Convertir a JSON para JavaScript
$productosJSON = json_encode($productosConProveedores);
$estadoActual = isset($_GET['estado']) ? $_GET['estado'] : '99';
$tipoBusquedaActual = isset($_GET['tipo_busqueda']) ? $_GET['tipo_busqueda'] : 'todos';
$busquedaActual = isset($_GET['busqueda']) ? $_GET['busqueda'] : '';
$ordenActual = isset($_GET['orden']) ? $_GET['orden'] : 'nombre_asc';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Productos</title>
    <link href="../css/bulma.min.css" rel="stylesheet">
    <link href="../css/listados.css" rel="stylesheet">
    
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

        /* Stock badges específicos de productos */
        .stock-normal {
            color: #27ae60;
            font-weight: bold;
        }

        .stock-bajo {
            color: #f39c12;
            font-weight: bold;
            background: rgba(243, 156, 18, 0.1);
            padding: 2px 6px;
            border-radius: 8px;
        }

        .stock-critico {
            color: #e74c3c;
            font-weight: bold;
            background: rgba(231, 76, 60, 0.1);
            padding: 2px 6px;
            border-radius: 8px;
        }

        /* Estilos para filas expandibles de proveedores */
        .expandable-row {
            display: none;
            background: rgba(241, 196, 15, 0.05) !important;
        }

        .expandable-row.show {
            display: table-row;
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

        .providers-container {
            padding: 15px 20px;
            background: rgba(0,0,0,0.2);
            border-radius: 8px;
            margin: 10px;
        }

        .providers-title {
            color: #f1c40f;
            font-weight: bold;
            margin-bottom: 10px;
            font-size: 0.9rem;
        }

        .provider-item-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .provider-tag {
            background: linear-gradient(45deg, #9b59b6, #8e44ad);
            color: white;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
            box-shadow: 0 2px 5px rgba(155, 89, 182, 0.3);
        }

        .no-providers {
            color: rgba(255, 255, 255, 0.6);
            font-style: italic;
            font-size: 0.8rem;
        }

        .expand-btn {
            background: transparent !important;
            border: none !important;
            color: #f1c40f !important;
            cursor: pointer !important;
            padding: 3px 6px !important;
            border-radius: 4px !important;
            transition: all 0.3s ease !important;
            font-size: 0.65rem !important;
            margin-right: 3px !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 3px !important;
        }

        .expand-btn:hover {
            background: rgba(241, 196, 15, 0.1) !important;
            color: #f39c12 !important;
        }

        .expand-icon {
            transition: transform 0.3s ease;
        }

        .expand-icon.rotated {
            transform: rotate(180deg);
        }

        .delete-link {
            background: linear-gradient(45deg, #e74c3c, #c0392b) !important;
            color: white !important;
            font-weight: bold;
            text-decoration: none !important;
            padding: 4px 8px;
            border-radius: 4px;
            transition: all 0.3s ease;
            display: inline-block;
            font-size: 0.7rem;
        }

        .delete-link:hover {
            background: linear-gradient(45deg, #c0392b, #e74c3c) !important;
            transform: translateY(-1px);
            box-shadow: 0 3px 8px rgba(231, 76, 60, 0.4);
            color: white !important;
        }
    </style>
</head>
<body>
    <?php include '../menu.php'; ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mainContent = document.querySelector('.main-content');
            const productos = <?php echo $productosJSON; ?>;
            const estadoActual = '<?php echo $estadoActual; ?>';
            const tipoBusquedaActual = '<?php echo $tipoBusquedaActual; ?>';
            const busquedaActual = '<?php echo addslashes($busquedaActual); ?>';
            const ordenActual = '<?php echo $ordenActual; ?>';
            
            let productosHTML = '';
            
            if (productos && productos.length > 0) {
                productos.forEach((producto, index) => {
                    const estado = producto.estado_producto == 1 
                        ? '<span class="status-active">ACTIVO</span>' 
                        : '<span class="status-inactive">INACTIVO</span>';
                    
                    let stockClass = 'stock-normal';
                    if (producto.stock_actual <= producto.stock_minimo) {
                        stockClass = producto.stock_actual == 0 ? 'stock-critico' : 'stock-bajo';
                    }
                    
                    let proveedoresHTML = '';
                    if (producto.proveedores && producto.proveedores.length > 0) {
                        proveedoresHTML = producto.proveedores.map(proveedor => 
                            `<span class="provider-tag">${proveedor.nombre_proveedor} ($${parseFloat(proveedor.precio_compra || 0).toFixed(2)})</span>`
                        ).join('');
                    } else {
                        proveedoresHTML = '<span class="no-providers">Sin proveedores asociados</span>';
                    }
                    
                    productosHTML += `
                        <tr>
                            <td><strong>${producto.id}</strong></td>
                            <td>${producto.nombre_producto || '-'}</td>
                            <td>${producto.codigo_producto || '-'}</td>
                            <td>$${parseFloat(producto.precio_venta).toFixed(2)}</td>
                            <td class="${stockClass}">${producto.stock_actual || 0}</td>
                            <td>${producto.stock_minimo || 0}</td>
                            <td>${estado}</td>
                            <td>
                                <button class="expand-btn" onclick="toggleProviders(${index})" id="btn-${index}">
                                    <span class="expand-icon" id="icon-${index}">▼</span>
                                    <span id="text-${index}">Ver</span>
                                </button>
                                <br style="margin-bottom: 3px;">
                                <a href='frm_editar_producto.php?id=${producto.id}' class='edit-link'>
                                    EDITAR
                                </a>
                                <a href='eliminar_producto.php?id=${producto.id}' class='delete-link'
                                   onclick="return confirm('¿Estás seguro de que deseas eliminar este producto?');">
                                    ELIMINAR
                                </a>
                            </td>
                        </tr>
                        <tr class="expandable-row" id="providers-${index}">
                            <td colspan="8">
                                <div class="providers-container">
                                    <div class="providers-title">
                                        Proveedores asociados (${producto.proveedores ? producto.proveedores.length : 0}):
                                    </div>
                                    <div class="provider-item-list">
                                        ${proveedoresHTML}
                                    </div>
                                </div>
                            </td>
                        </tr>
                    `;
                });
            } else {
                productosHTML = `
                    <tr>
                        <td colspan="8" class="no-results">
                            No se encontraron productos con los criterios seleccionados
                        </td>
                    </tr>
                `;
            }

            const contentHTML = `
                <div class='list-container'>
                    <h1 class='list-title'>Listado de Productos</h1>
                    
                    <div class='filter-container'>
                        <form method='GET' action=''>
                            <label class='label'>Buscar Productos</label>
                            <div class='search-controls'>
                                <div class='search-field' style='min-width: 180px;'>
                                    <label>Buscar por:</label>
                                    <div class='select'>
                                        <select name='tipo_busqueda' class='search-input'>
                                            <option value='todos' ${tipoBusquedaActual == 'todos' ? 'selected' : ''}>-- TODOS --</option>
                                            <option value='nombre' ${tipoBusquedaActual == 'nombre' ? 'selected' : ''}>Nombre</option>
                                            <option value='codigo' ${tipoBusquedaActual == 'codigo' ? 'selected' : ''}>Código</option>
                                            <option value='precio' ${tipoBusquedaActual == 'precio' ? 'selected' : ''}>Precio</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class='search-field' style='flex: 1; min-width: 250px;'>
                                    <label>Término de búsqueda:</label>
                                    <input type='text' name='busqueda' class='search-input' placeholder='Escribe aquí para buscar...' value='${busquedaActual}'>
                                </div>
                                
                                <div class='search-field' style='min-width: 140px;'>
                                    <label>Estado:</label>
                                    <div class='select'>
                                        <select name='estado' class='search-input'>
                                            <option value='99' ${estadoActual == '99' ? 'selected' : ''}> -- TODOS --</option>
                                            <option value='1' ${estadoActual == '1' ? 'selected' : ''}>ACTIVO</option>
                                            <option value='0' ${estadoActual == '0' ? 'selected' : ''}>INACTIVO</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class='search-field' style='min-width: 160px;'>
                                    <label>Ordenar por:</label>
                                    <div class='select'>
                                        <select name='orden' class='search-input'>
                                            <option value='nombre_asc' ${ordenActual == 'nombre_asc' ? 'selected' : ''}>Nombre A-Z</option>
                                            <option value='nombre_desc' ${ordenActual == 'nombre_desc' ? 'selected' : ''}>Nombre Z-A</option>
                                            <option value='precio_asc' ${ordenActual == 'precio_asc' ? 'selected' : ''}>Precio Menor</option>
                                            <option value='precio_desc' ${ordenActual == 'precio_desc' ? 'selected' : ''}>Precio Mayor</option>
                                            <option value='stock_asc' ${ordenActual == 'stock_asc' ? 'selected' : ''}>Stock Menor</option>
                                            <option value='stock_desc' ${ordenActual == 'stock_desc' ? 'selected' : ''}>Stock Mayor</option>
                                            <option value='id_asc' ${ordenActual == 'id_asc' ? 'selected' : ''}>ID Menor</option>
                                            <option value='id_desc' ${ordenActual == 'id_desc' ? 'selected' : ''}>ID Mayor</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class='search-field' style='min-width: auto;'>
                                    <button type='submit' class='button' style='margin-top: 22px; padding: 12px 20px; height: 44px;'>
                                        Buscar
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div style='overflow-x: auto;'>
                        <table class='table is-fullwidth custom-table'>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>PRODUCTO</th>
                                    <th>CÓDIGO</th>
                                    <th>PRECIO</th>
                                    <th>STOCK</th>
                                    <th>MÍN</th>
                                    <th>ESTADO</th>
                                    <th>ACCIONES</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${productosHTML}
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
            
            mainContent.innerHTML = contentHTML;
            
            window.toggleProviders = function(index) {
                const expandableRow = document.getElementById(`providers-${index}`);
                const icon = document.getElementById(`icon-${index}`);
                const text = document.getElementById(`text-${index}`);
                
                if (expandableRow.classList.contains('show')) {
                    expandableRow.classList.remove('show');
                    icon.classList.remove('rotated');
                    text.textContent = 'Ver';
                } else {
                    expandableRow.classList.add('show');
                    icon.classList.add('rotated');
                    text.textContent = 'Ocultar';
                }
            };
        });
    </script>
</body>
</html>