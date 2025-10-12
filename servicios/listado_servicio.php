<?php
// Procesamiento de datos al inicio
include_once "../db.php";

$estado = isset($_GET['estado']) ? $_GET['estado'] : "99";
$categoria = isset($_GET['categoria']) ? trim($_GET['categoria']) : "todas";
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : "";

// Construir consulta con parámetros seguros
$sql = "SELECT s.* FROM servicios s WHERE 1=1";
$params = array();

// Filtro por estado
if ($estado !== "99") {
    $sql .= " AND s.estado_servicio = :estado";
    $params[':estado'] = intval($estado);
}

// Filtro por categoría
if ($categoria !== "todas" && !empty($categoria)) {
    $sql .= " AND s.categoria_servicio = :categoria";
    $params[':categoria'] = $categoria;
}

// Filtro por búsqueda en nombre
if (!empty($busqueda)) {
    $sql .= " AND s.nombre_servicio LIKE :busqueda";
    $params[':busqueda'] = '%' . $busqueda . '%';
}

$sql .= " ORDER BY s.categoria_servicio ASC, s.nombre_servicio ASC";

// Ejecutar consulta
$sentencia = $conexion->prepare($sql);
$sentencia->execute($params);
$servicios = $sentencia->fetchAll(PDO::FETCH_ASSOC);

// Obtener categorías únicas
$sqlCategorias = "SELECT DISTINCT categoria_servicio FROM servicios WHERE categoria_servicio IS NOT NULL AND categoria_servicio != '' ORDER BY categoria_servicio ASC";
$sentenciaCategorias = $conexion->prepare($sqlCategorias);
$sentenciaCategorias->execute();
$categorias = $sentenciaCategorias->fetchAll(PDO::FETCH_COLUMN);

// Pasar datos a JS
$serviciosJSON = json_encode($servicios);
$categoriasJSON = json_encode($categorias);
$estadoActual = $estado;
$categoriaActual = $categoria;
$busquedaActual = $busqueda;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Servicios</title>
    <link href="../css/bulma.min.css" rel="stylesheet">
    <link href="../css/listados.css" rel="stylesheet">
    <style>
        /* Pequeño ajuste local por si acaso (no debería interferir) */
        #main-content { min-height: 120px; }
        @media (max-width: 880px) {
            #main-content { padding: 12px; }
        }
    </style>
</head>
<body>
    <?php include '../menu.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mainContent = document.getElementById('main-content');
            const servicios = <?php echo $serviciosJSON; ?> || [];
            const categorias = <?php echo $categoriasJSON; ?> || [];
            const estadoActual = '<?php echo addslashes($estadoActual); ?>';
            const categoriaActual = '<?php echo addslashes($categoriaActual); ?>';
            const busquedaActual = '<?php echo addslashes($busquedaActual); ?>';

            // Construir opciones de select de categorías
            let categoriasOptions = `<option value='todas' ${categoriaActual === 'todas' ? 'selected' : ''}>-- TODAS --</option>`;
            categorias.forEach(cat => {
                const sel = (cat === categoriaActual) ? 'selected' : '';
                categoriasOptions += `<option value="${cat}" ${sel}>${cat}</option>`;
            });

            // Construir filas de tabla
            let serviciosRows = '';
            if (servicios.length > 0) {
                servicios.forEach(s => {
                    const estadoBadge = (s.estado_servicio == 1)
                        ? '<span class="status-active">ACTIVO</span>'
                        : '<span class="status-inactive">INACTIVO</span>';

                    const categoriaBadge = `<span class="category-badge">${s.categoria_servicio || '-'}</span>`;

                    serviciosRows += `
                        <tr>
                            <td><strong>${s.id}</strong></td>
                            <td>${s.nombre_servicio ? escapeHtml(s.nombre_servicio) : '-'}</td>
                            <td>${categoriaBadge}</td>
                            <td>${estadoBadge}</td>
                            <td><a href="frm_editar_servicio.php?id=${s.id}" class="edit-link">EDITAR</a></td>
                        </tr>
                    `;
                });
            } else {
                serviciosRows = `
                    <tr>
                        <td colspan="5" class="no-results">
                            No se encontraron servicios con los criterios seleccionados
                        </td>
                    </tr>
                `;
            }

            // HTML completo a insertar en el main
            const contentHTML = `
                <div class='list-container'>
                    <h1 class='list-title'>Listado de Servicios</h1>
                    
                    <div class='filter-container'>
                        <form method='GET' action=''>
                            <label class='label'>Filtrar Servicios</label>
                            <div class='search-controls'>
                                <div class='search-field'>
                                    <label>Buscar por nombre:</label>
                                    <input type='text' name='busqueda' class='search-input' 
                                           placeholder='Nombre del servicio...' 
                                           value='${escapeHtml(busquedaActual)}'>
                                </div>
                                
                                <div class='search-field' style='flex: 0 0 200px;'>
                                    <label>Categoría:</label>
                                    <div class='select'>
                                        <select name='categoria' class='search-input'>
                                            ${categoriasOptions}
                                        </select>
                                    </div>
                                </div>
                                
                                <div class='search-field' style='flex: 0 0 150px;'>
                                    <label>Estado:</label>
                                    <div class='select'>
                                        <select name='estado' class='search-input'>
                                            <option value='99' ${estadoActual == '99' ? 'selected' : ''}>-- TODOS --</option>
                                            <option value='1' ${estadoActual == '1' ? 'selected' : ''}>ACTIVO</option>
                                            <option value='0' ${estadoActual == '0' ? 'selected' : ''}>INACTIVO</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class='search-field' style='flex: 0 0 auto;'>
                                    <button type='submit' class='button' style='margin-top: 20px; padding: 12px 20px;'>
                                        Buscar
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class='table is-fullwidth custom-table'>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>NOMBRE DEL SERVICIO</th>
                                    <th>CATEGORÍA</th>
                                    <th>ESTADO</th>
                                    <th>ACCIONES</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${serviciosRows}
                            </tbody>
                        </table>
                    </div>
                </div>
            `;

            mainContent.innerHTML = contentHTML;

            // función de escape simple para evitar romper HTML (protege comillas)
            function escapeHtml(text) {
                if (!text) return '';
                return String(text)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }
        });
    </script>
</body>
</html>
