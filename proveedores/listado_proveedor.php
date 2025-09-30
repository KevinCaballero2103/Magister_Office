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
    $condiciones[] = "p.estado_proveedor = " . intval($estado);
}

// Filtro por búsqueda
if (!empty($busqueda) && $tipo_busqueda !== "todos") {
    switch ($tipo_busqueda) {
        case "nombre":
            $condiciones[] = "p.nombre_proveedor LIKE '%" . $busqueda . "%'";
            break;
        case "telefono":
            $condiciones[] = "p.telefono_proveedor LIKE '%" . $busqueda . "%'";
            break;
        case "direccion":
            $condiciones[] = "p.direccion_proveedor LIKE '%" . $busqueda . "%'";
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
        $order_by = "ORDER BY p.nombre_proveedor DESC";
        break;
    default:
        $order_by = "ORDER BY p.nombre_proveedor ASC";
        break;
}

$sentencia = $conexion->prepare("
    SELECT p.* 
    FROM proveedores p
    $where_clause
    $order_by
");

$sentencia->execute();
$proveedores = $sentencia->fetchAll(PDO::FETCH_OBJ);

// Obtener productos asociados para cada proveedor
$proveedoresConProductos = array();
foreach ($proveedores as $proveedor) {
    $sentenciaProductos = $conexion->prepare("
        SELECT pr.nombre_producto, pr.codigo_producto, pp.precio_compra
        FROM productos pr
        INNER JOIN proveedor_producto pp ON pr.id = pp.id_producto
        WHERE pp.id_proveedor = ?
        ORDER BY pr.nombre_producto ASC
    ");
    $sentenciaProductos->execute([$proveedor->id]);
    $productos = $sentenciaProductos->fetchAll(PDO::FETCH_OBJ);
    
    $proveedor->productos = $productos;
    $proveedoresConProductos[] = $proveedor;
}

// Convertir a JSON para JavaScript
$proveedoresJSON = json_encode($proveedoresConProductos);
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
    <title>Listado de Proveedores</title>
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

        /* Estilos específicos para filas expandibles de productos */
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

        .products-container {
            padding: 15px 20px;
            background: rgba(0,0,0,0.2);
            border-radius: 8px;
            margin: 10px;
        }

        .products-title {
            color: #f1c40f;
            font-weight: bold;
            margin-bottom: 10px;
            font-size: 0.9rem;
        }

        .product-item-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .product-tag {
            background: linear-gradient(45deg, #3498db, #2980b9);
            color: white;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
            box-shadow: 0 2px 5px rgba(52, 152, 219, 0.3);
        }

        .no-products {
            color: rgba(255, 255, 255, 0.6);
            font-style: italic;
            font-size: 0.8rem;
        }

        .expand-btn {
            background: transparent !important;
            border: none !important;
            color: #f1c40f !important;
            cursor: pointer !important;
            padding: 4px 8px !important;
            border-radius: 4px !important;
            transition: all 0.3s ease !important;
            font-size: 0.7rem !important;
            margin-right: 5px !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 5px !important;
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
            padding: 6px 12px;
            border-radius: 6px;
            transition: all 0.3s ease;
            display: inline-block;
            font-size: 0.8rem;
        }

        .delete-link:hover {
            background: linear-gradient(45deg, #c0392b, #e74c3c) !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(231, 76, 60, 0.4);
            color: white !important;
        }
    </style>
</head>
<body>
    <?php include '../menu.php'; ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mainContent = document.querySelector('.main-content');
            const proveedores = <?php echo $proveedoresJSON; ?>;
            const estadoActual = '<?php echo $estadoActual; ?>';
            const tipoBusquedaActual = '<?php echo $tipoBusquedaActual; ?>';
            const busquedaActual = '<?php echo addslashes($busquedaActual); ?>';
            const ordenActual = '<?php echo $ordenActual; ?>';
            
            let proveedoresHTML = '';
            
            if (proveedores && proveedores.length > 0) {
                proveedores.forEach((proveedor, index) => {
                    const estado = proveedor.estado_proveedor == 1 
                        ? '<span class="status-active">ACTIVO</span>' 
                        : '<span class="status-inactive">INACTIVO</span>';
                    
                    let productosHTML = '';
                    if (proveedor.productos && proveedor.productos.length > 0) {
                        productosHTML = proveedor.productos.map(producto => 
                            `<span class="product-tag">${producto.nombre_producto} (${parseFloat(producto.precio_compra || 0).toFixed(2)})</span>`
                        ).join('');
                    } else {
                        productosHTML = '<span class="no-products">Sin productos asociados</span>';
                    }
                    
                    proveedoresHTML += `
                        <tr>
                            <td><strong>${proveedor.id}</strong></td>
                            <td>${proveedor.nombre_proveedor || '-'}</td>
                            <td>${proveedor.telefono_proveedor || '-'}</td>
                            <td>${proveedor.direccion_proveedor || '-'}</td>
                            <td>${estado}</td>
                            <td>
                                <button class="expand-btn" onclick="toggleProducts(${index})" id="btn-${index}">
                                    <span class="expand-icon" id="icon-${index}">▼</span>
                                    <span id="text-${index}">Ver Productos</span>
                                </button>
                                <br style="margin-bottom: 5px;">
                                <a href='frm_editar_proveedor.php?id=${proveedor.id}' class='edit-link'>
                                    EDITAR
                                </a>
                                <a href='eliminar_proveedor.php?id=${proveedor.id}' class='delete-link'
                                   onclick="return confirm('¿Estás seguro de que deseas eliminar este proveedor?');">
                                    ELIMINAR
                                </a>
                            </td>
                        </tr>
                        <tr class="expandable-row" id="products-${index}">
                            <td colspan="6">
                                <div class="products-container">
                                    <div class="products-title">
                                        Productos asociados (${proveedor.productos ? proveedor.productos.length : 0}):
                                    </div>
                                    <div class="product-item-list">
                                        ${productosHTML}
                                    </div>
                                </div>
                            </td>
                        </tr>
                    `;
                });
            } else {
                proveedoresHTML = `
                    <tr>
                        <td colspan="6" class="no-results">
                            No se encontraron proveedores con los criterios seleccionados
                        </td>
                    </tr>
                `;
            }

            const contentHTML = `
                <div class='list-container'>
                    <h1 class='list-title'>Listado de Proveedores</h1>
                    
                    <div class='filter-container'>
                        <form method='GET' action=''>
                            <label class='label'>Buscar Proveedores</label>
                            <div class='search-controls'>
                                <div class='search-field' style='min-width: 180px;'>
                                    <label>Buscar por:</label>
                                    <div class='select'>
                                        <select name='tipo_busqueda' class='search-input'>
                                            <option value='todos' ${tipoBusquedaActual == 'todos' ? 'selected' : ''}>-- TODOS --</option>
                                            <option value='nombre' ${tipoBusquedaActual == 'nombre' ? 'selected' : ''}>Nombre</option>
                                            <option value='telefono' ${tipoBusquedaActual == 'telefono' ? 'selected' : ''}>Teléfono</option>
                                            <option value='direccion' ${tipoBusquedaActual == 'direccion' ? 'selected' : ''}>Dirección</option>
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
                                    <th>NOMBRE</th>
                                    <th>TELÉFONO</th>
                                    <th>DIRECCIÓN</th>
                                    <th>ESTADO</th>
                                    <th>ACCIONES</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${proveedoresHTML}
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
            
            mainContent.innerHTML = contentHTML;
            
            window.toggleProducts = function(index) {
                const expandableRow = document.getElementById(`products-${index}`);
                const icon = document.getElementById(`icon-${index}`);
                const text = document.getElementById(`text-${index}`);
                
                if (expandableRow.classList.contains('show')) {
                    expandableRow.classList.remove('show');
                    icon.classList.remove('rotated');
                    text.textContent = 'Ver Productos';
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