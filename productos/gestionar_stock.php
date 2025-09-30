<?php
// Procesamiento de datos al inicio
include_once "../db.php";

$estado = isset($_GET['estado']) ? $_GET['estado'] : "99";
$nivel_stock = isset($_GET['nivel_stock']) ? $_GET['nivel_stock'] : "todos";
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : "";

// Construir condiciones WHERE
$condiciones = array();

// Filtro por estado
if ($estado !== "99") {
    $condiciones[] = "p.estado_producto = " . intval($estado);
}

// Filtro por nivel de stock
if ($nivel_stock !== "todos") {
    switch ($nivel_stock) {
        case "critico":
            $condiciones[] = "p.stock_actual = 0";
            break;
        case "bajo":
            $condiciones[] = "p.stock_actual > 0 AND p.stock_actual <= p.stock_minimo";
            break;
        case "normal":
            $condiciones[] = "p.stock_actual > p.stock_minimo";
            break;
    }
}

// Filtro por búsqueda de nombre
if (!empty($busqueda)) {
    $condiciones[] = "p.nombre_producto LIKE '%" . $busqueda . "%'";
}

// Construir WHERE clause
$where_clause = "";
if (!empty($condiciones)) {
    $where_clause = "WHERE " . implode(" AND ", $condiciones);
}

$sentencia = $conexion->prepare("
    SELECT p.* 
    FROM productos p
    $where_clause
    ORDER BY p.stock_actual ASC, p.nombre_producto ASC
");

$sentencia->execute();
$productos = $sentencia->fetchAll(PDO::FETCH_OBJ);

// Calcular estadísticas
$sentenciaStats = $conexion->prepare("
    SELECT 
        COUNT(*) as total_productos,
        SUM(CASE WHEN stock_actual = 0 THEN 1 ELSE 0 END) as productos_criticos,
        SUM(CASE WHEN stock_actual > 0 AND stock_actual <= stock_minimo THEN 1 ELSE 0 END) as productos_bajo,
        SUM(CASE WHEN stock_actual > stock_minimo THEN 1 ELSE 0 END) as productos_normal
    FROM productos
    WHERE estado_producto = 1
");
$sentenciaStats->execute();
$stats = $sentenciaStats->fetch(PDO::FETCH_OBJ);

// Convertir a JSON para JavaScript
$productosJSON = json_encode($productos);
$statsJSON = json_encode($stats);
$estadoActual = $estado;
$nivelStockActual = $nivel_stock;
$busquedaActual = $busqueda;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Stock</title>
    <link href="../css/bulma.min.css" rel="stylesheet">
    <link href="../css/listados.css" rel="stylesheet">
    <link href="../css/estadisticas.css" rel="stylesheet">
    
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
            const stats = <?php echo $statsJSON; ?>;
            const estadoActual = '<?php echo $estadoActual; ?>';
            const nivelStockActual = '<?php echo $nivelStockActual; ?>';
            const busquedaActual = '<?php echo addslashes($busquedaActual); ?>';
            
            let productosHTML = '';
            
            if (productos && productos.length > 0) {
                productos.forEach(producto => {
                    let nivelStock = '';
                    let badgeStock = '';
                    let rowClass = '';
                    
                    if (producto.stock_actual == 0) {
                        nivelStock = 'AGOTADO';
                        badgeStock = 'stock-critico';
                        rowClass = 'row-critico';
                    } else if (producto.stock_actual <= producto.stock_minimo) {
                        nivelStock = 'BAJO';
                        badgeStock = 'stock-bajo';
                        rowClass = 'row-bajo';
                    } else {
                        nivelStock = 'NORMAL';
                        badgeStock = 'stock-normal';
                    }
                    
                    const estado = producto.estado_producto == 1 
                        ? '<span class="status-active">ACTIVO</span>' 
                        : '<span class="status-inactive">INACTIVO</span>';
                    
                    productosHTML += `
                        <tr class="${rowClass}">
                            <td><strong>${producto.id}</strong></td>
                            <td>${producto.nombre_producto || '-'}</td>
                            <td>${producto.codigo_producto || '-'}</td>
                            <td><strong>${producto.stock_actual || 0}</strong></td>
                            <td>${producto.stock_minimo || 0}</td>
                            <td><span class="${badgeStock}">${nivelStock}</span></td>
                            <td>${estado}</td>
                            <td>
                                <div class="action-buttons">
                                    <a href='frm_entrada_stock.php?id=${producto.id}' class='btn-entrada'>
                                        + ENTRADA
                                    </a>
                                    <a href='frm_salida_stock.php?id=${producto.id}' class='btn-salida'>
                                        - SALIDA
                                    </a>
                                    <a href='historial_movimientos.php?id_producto=${producto.id}' class='btn-historial'>
                                        HISTORIAL
                                    </a>
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
                    <h1 class='list-title'>Gestión de Stock</h1>
                    
                    <div class='stats-container'>
                        <div class='stat-card stat-total'>
                            <div class='stat-number'>${stats.total_productos || 0}</div>
                            <div class='stat-label'>Total Productos</div>
                        </div>
                        <div class='stat-card stat-critico'>
                            <div class='stat-number'>${stats.productos_criticos || 0}</div>
                            <div class='stat-label'>Agotados</div>
                        </div>
                        <div class='stat-card stat-bajo'>
                            <div class='stat-number'>${stats.productos_bajo || 0}</div>
                            <div class='stat-label'>Stock Bajo</div>
                        </div>
                        <div class='stat-card stat-normal'>
                            <div class='stat-number'>${stats.productos_normal || 0}</div>
                            <div class='stat-label'>Stock Normal</div>
                        </div>
                    </div>
                    
                    <div class='filter-container'>
                        <form method='GET' action=''>
                            <label class='label'>Filtrar Productos</label>
                            <div class='search-controls'>
                                <div class='search-field' style='flex: 1; min-width: 250px;'>
                                    <label>Buscar por nombre:</label>
                                    <input type='text' name='busqueda' class='search-input' placeholder='Nombre del producto...' value='${busquedaActual}'>
                                </div>
                                
                                <div class='search-field' style='min-width: 160px;'>
                                    <label>Nivel de stock:</label>
                                    <div class='select'>
                                        <select name='nivel_stock' class='search-input'>
                                            <option value='todos' ${nivelStockActual == 'todos' ? 'selected' : ''}>-- TODOS --</option>
                                            <option value='critico' ${nivelStockActual == 'critico' ? 'selected' : ''}>AGOTADO</option>
                                            <option value='bajo' ${nivelStockActual == 'bajo' ? 'selected' : ''}>BAJO</option>
                                            <option value='normal' ${nivelStockActual == 'normal' ? 'selected' : ''}>NORMAL</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class='search-field' style='min-width: 140px;'>
                                    <label>Estado:</label>
                                    <div class='select'>
                                        <select name='estado' class='search-input'>
                                            <option value='99' ${estadoActual == '99' ? 'selected' : ''}>-- TODOS --</option>
                                            <option value='1' ${estadoActual == '1' ? 'selected' : ''}>ACTIVO</option>
                                            <option value='0' ${estadoActual == '0' ? 'selected' : ''}>INACTIVO</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class='search-field' style='min-width: auto;'>
                                    <button type='submit' class='button' style='margin-top: 22px; padding: 12px 20px; height: 44px;'>
                                        Filtrar
                                    </button>
                                </div>
                            </div>
                        </form>
                        
                        <div style='text-align: center; margin-top: 20px;'>
                            <a href='historial_movimientos.php' class='button' style='background: linear-gradient(45deg, #9b59b6, #8e44ad) !important;'>
                                Ver Historial Completo de Movimientos
                            </a>
                        </div>
                    </div>

                    <div style='overflow-x: auto;'>
                        <table class='table is-fullwidth custom-table'>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>PRODUCTO</th>
                                    <th>CÓDIGO</th>
                                    <th>STOCK ACTUAL</th>
                                    <th>STOCK MÍN</th>
                                    <th>NIVEL</th>
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
        });
    </script>
</body>
</html>