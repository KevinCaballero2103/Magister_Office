<?php
include_once "../db.php";
include_once "../auth.php"; 


$estado = isset($_GET['estado']) ? $_GET['estado'] : "99";
$categoria = isset($_GET['categoria']) ? trim($_GET['categoria']) : "todas";
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : "";

$sql = "SELECT s.* FROM servicios s WHERE 1=1";
$params = array();

if ($estado !== "99") {
    $sql .= " AND s.estado_servicio = :estado";
    $params[':estado'] = intval($estado);
}

if ($categoria !== "todas" && !empty($categoria)) {
    $sql .= " AND s.categoria_servicio = :categoria";
    $params[':categoria'] = $categoria;
}

if (!empty($busqueda)) {
    $sql .= " AND s.nombre_servicio LIKE :busqueda";
    $params[':busqueda'] = '%' . $busqueda . '%';
}

$sql .= " ORDER BY s.categoria_servicio ASC, s.nombre_servicio ASC";

$sentencia = $conexion->prepare($sql);
$sentencia->execute($params);
$servicios = $sentencia->fetchAll(PDO::FETCH_ASSOC);

$sqlCategorias = "SELECT DISTINCT categoria_servicio FROM servicios WHERE categoria_servicio IS NOT NULL AND categoria_servicio != '' ORDER BY categoria_servicio ASC";
$sentenciaCategorias = $conexion->prepare($sqlCategorias);
$sentenciaCategorias->execute();
$categorias = $sentenciaCategorias->fetchAll(PDO::FETCH_COLUMN);

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
        .main-content {
            background: #2c3e50 !important;
            color: white;
        }
    </style>
</head>
<body>
    <?php include '../menu.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var mainContent = document.querySelector('.main-content');
            if (!mainContent) {
                console.error('No se encontró .main-content');
                return;
            }

            var servicios = <?php echo $serviciosJSON; ?> || [];
            var categorias = <?php echo $categoriasJSON; ?> || [];
            var estadoActual = '<?php echo addslashes($estadoActual); ?>';
            var categoriaActual = '<?php echo addslashes($categoriaActual); ?>';
            var busquedaActual = '<?php echo addslashes($busquedaActual); ?>';

            var categoriasOptions = '<option value="todas"' + (categoriaActual === 'todas' ? ' selected' : '') + '>-- TODAS --</option>';
            categorias.forEach(function(cat) {
                var sel = (cat === categoriaActual) ? ' selected' : '';
                categoriasOptions += '<option value="' + cat + '"' + sel + '>' + cat + '</option>';
            });

            var serviciosRows = '';
            if (servicios.length > 0) {
                servicios.forEach(function(s) {
                    var estadoBadge = (s.estado_servicio == 1)
                        ? '<span class="status-active">ACTIVO</span>'
                        : '<span class="status-inactive">INACTIVO</span>';

                    var categoriaBadge = '<span class="category-badge">' + (s.categoria_servicio || '-') + '</span>';
                    
                    var precioTexto = s.precio_sugerido && parseFloat(s.precio_sugerido) > 0 
                        ? '₲ ' + parseFloat(s.precio_sugerido).toLocaleString('es-PY', {minimumFractionDigits: 2})
                        : '-';

                    serviciosRows += '<tr>' +
                        '<td><strong>' + s.id + '</strong></td>' +
                        '<td>' + escapeHtml(s.nombre_servicio || '-') + '</td>' +
                        '<td>' + categoriaBadge + '</td>' +
                        '<td>' + precioTexto + '</td>' +
                        '<td>' + estadoBadge + '</td>' +
                        '<td><a href="frm_editar_servicio.php?id=' + s.id + '" class="edit-link">✏️ EDITAR</a></td>' +
                        '</tr>';
                });
            } else {
                serviciosRows = '<tr><td colspan="6" class="no-results">No se encontraron servicios</td></tr>';
            }

            var contentHTML = '<div class="list-container">' +
                '<h1 class="list-title">🔧 Listado de Servicios</h1>' +
                '<div class="filter-container">' +
                '<form method="GET" action="">' +
                '<label class="label">Filtrar Servicios</label>' +
                '<div class="search-controls">' +
                '<div class="search-field" style="min-width: 200px;">' +
                '<label>Buscar por nombre:</label>' +
                '<input type="text" name="busqueda" class="search-input" placeholder="Nombre del servicio..." value="' + escapeHtml(busquedaActual) + '">' +
                '</div>' +
                '<div class="search-field" style="min-width: 200px;">' +
                '<label>Categoría:</label>' +
                '<div class="select"><select name="categoria" class="search-input">' + categoriasOptions + '</select></div>' +
                '</div>' +
                '<div class="search-field" style="min-width: 150px;">' +
                '<label>Estado:</label>' +
                '<div class="select"><select name="estado" class="search-input">' +
                '<option value="99"' + (estadoActual == '99' ? ' selected' : '') + '>-- TODOS --</option>' +
                '<option value="1"' + (estadoActual == '1' ? ' selected' : '') + '>ACTIVO</option>' +
                '<option value="0"' + (estadoActual == '0' ? ' selected' : '') + '>INACTIVO</option>' +
                '</select></div>' +
                '</div>' +
                '<div class="search-field" style="min-width: auto;">' +
                '<button type="submit" class="button" style="margin-top: 20px; padding: 12px 20px;">Buscar</button>' +
                '</div>' +
                '</div>' +
                '</form>' +
                '</div>' +
                '<div style="overflow-x: auto;">' +
                '<table class="table is-fullwidth custom-table">' +
                '<thead><tr>' +
                '<th>ID</th>' +
                '<th>NOMBRE DEL SERVICIO</th>' +
                '<th>CATEGORÍA</th>' +
                '<th>PRECIO SUGERIDO</th>' +
                '<th>ESTADO</th>' +
                '<th>ACCIONES</th>' +
                '</tr></thead>' +
                '<tbody>' + serviciosRows + '</tbody>' +
                '</table>' +
                '</div>' +
                '<div style="text-align: center; margin-top: 25px;">' +
                '<a href="./frm_guardar_servicio.php" class="button">➕ Registrar Nuevo Servicio</a>' +
                '</div>' +
                '</div>';

            mainContent.innerHTML = contentHTML;

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