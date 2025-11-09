<?php
include_once __DIR__ . "/../auth.php";
if (!isset($_GET["id"])) {
    $error = "Necesito el parámetro id para identificar el servicio.";
} else {
    include '../db.php';
    $id = $_GET["id"];
    
    $sentencia = $conexion->prepare("SELECT * FROM servicios WHERE id = ?");
    $sentencia->execute([$id]);
    $servicio = $sentencia->fetch(PDO::FETCH_OBJ);
    
    if ($servicio === FALSE) {
        $error = "El servicio indicado no existe en el sistema.";
    } else {
        $sentenciaCategorias = $conexion->prepare("SELECT DISTINCT categoria_servicio FROM servicios WHERE categoria_servicio IS NOT NULL AND categoria_servicio != '' ORDER BY categoria_servicio ASC");
        $sentenciaCategorias->execute();
        $categorias = $sentenciaCategorias->fetchAll(PDO::FETCH_COLUMN);
    }
}

if (isset($servicio)) {
    $servicioJSON = json_encode($servicio);
    $categoriasJSON = json_encode($categorias);
} else {
    $servicioJSON = 'null';
    $categoriasJSON = '[]';
    $mensajeError = isset($error) ? $error : 'Error desconocido';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Servicio</title>
    <link href="../css/bulma.min.css" rel="stylesheet">
    <link href="../css/formularios.css" rel="stylesheet">
    <link href="../css/autocompletado.css" rel="stylesheet">
    
    <style>
        .main-content {
            background: #2c3e50 !important;
            color: white;
        }
        .form-container {
            max-width: 800px;
        }
    </style>
</head>
<body>
    <?php include '../menu.php'; ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var mainContent = document.querySelector('.main-content');
            var servicio = <?php echo $servicioJSON; ?>;
            var categorias = <?php echo $categoriasJSON; ?>;
            
            if (servicio === null) {
                var errorMessage = '<?php echo isset($mensajeError) ? addslashes($mensajeError) : 'Error desconocido'; ?>';
                
                var errorHTML = '<div class="error-container">' +
                    '<div class="error-title">Error</div>' +
                    '<div class="error-message">' + errorMessage + '</div>' +
                    '<a href="./listado_servicio.php" class="button">Volver al Listado</a>' +
                    '</div>';
                
                mainContent.innerHTML = errorHTML;
                return;
            }

            var selectedIndex = -1;
            var filteredSuggestions = [];

            function filterCategories(searchTerm) {
                if (!searchTerm) return [];
                var term = searchTerm.toLowerCase();
                return categorias.filter(function(cat) {
                    return cat.toLowerCase().includes(term);
                });
            }

            function showSuggestions(input, suggestions) {
                var container = input.parentElement;
                var suggestionsDiv = container.querySelector('.autocomplete-suggestions');
                
                if (!suggestionsDiv) {
                    suggestionsDiv = document.createElement('div');
                    suggestionsDiv.className = 'autocomplete-suggestions';
                    container.appendChild(suggestionsDiv);
                }

                if (suggestions.length === 0 && input.value.trim() !== '') {
                    suggestionsDiv.innerHTML = '<div class="suggestion-item suggestion-new" data-value="' + input.value + '">' +
                        '<span class="suggestion-icon">✨</span>Crear nueva categoría: "' + input.value + '"' +
                        '</div>';
                    suggestionsDiv.classList.add('active');
                } else if (suggestions.length > 0) {
                    var html = '';
                    suggestions.forEach(function(cat, index) {
                        html += '<div class="suggestion-item" data-value="' + cat + '" data-index="' + index + '">' +
                            '<span class="suggestion-icon">📁</span>' + cat +
                            '</div>';
                    });
                    suggestionsDiv.innerHTML = html;
                    suggestionsDiv.classList.add('active');
                } else {
                    suggestionsDiv.classList.remove('active');
                }

                var items = suggestionsDiv.querySelectorAll('.suggestion-item');
                items.forEach(function(item) {
                    item.addEventListener('click', function() {
                        input.value = this.getAttribute('data-value');
                        suggestionsDiv.classList.remove('active');
                        selectedIndex = -1;
                    });
                });
            }

            function hideSuggestions(input) {
                var container = input.parentElement;
                var suggestionsDiv = container.querySelector('.autocomplete-suggestions');
                if (suggestionsDiv) {
                    setTimeout(function() {
                        suggestionsDiv.classList.remove('active');
                    }, 200);
                }
            }
            
            var precioSugeridoVal = servicio.precio_sugerido || 0;
            
            var contentHTML = '<div class="form-container">' +
                '<h1 class="form-title">🔧 Editar Servicio</h1>' +
                '<form action="./editar_servicio.php" method="post" onsubmit="return validateForm()">' +
                '<input type="hidden" name="id" value="' + servicio.id + '">' +
                '<div class="columns">' +
                '<div class="column is-8">' +
                '<div class="field">' +
                '<label class="label">Nombre del Servicio</label>' +
                '<div class="control">' +
                '<input class="input" type="text" name="nombre_servicio" id="nombre_servicio" ' +
                'placeholder="Ej: Reparación de impresora" required value="' + (servicio.nombre_servicio || '') + '">' +
                '</div>' +
                '</div>' +
                '</div>' +
                '<div class="column is-4">' +
                '<div class="field">' +
                '<label class="label">Estado</label>' +
                '<div class="control">' +
                '<div class="select is-fullwidth">' +
                '<select name="estado_servicio" id="estado_servicio">' +
                '<option value="1"' + (servicio.estado_servicio == 1 ? ' selected' : '') + '>Activo</option>' +
                '<option value="0"' + (servicio.estado_servicio == 0 ? ' selected' : '') + '>Inactivo</option>' +
                '</select>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '<div class="columns">' +
                '<div class="column is-8">' +
                '<div class="field">' +
                '<label class="label">Categoría del Servicio</label>' +
                '<div class="control autocomplete-container">' +
                '<input class="input" type="text" name="categoria_servicio" id="categoria_servicio" ' +
                'placeholder="Escribe para buscar o crear categoría" autocomplete="off" required ' +
                'value="' + (servicio.categoria_servicio || '') + '">' +
                '</div>' +
                '<p class="help" style="color: rgba(255,255,255,0.7);">Escribe para buscar o crear una categoría</p>' +
                '</div>' +
                '</div>' +
                '<div class="column is-4">' +
                '<div class="field">' +
                '<label class="label">Precio Sugerido</label>' +
                '<div class="control">' +
                '<input class="input" type="number" step="0.01" min="0" name="precio_sugerido" id="precio_sugerido" ' +
                'placeholder="0.00" value="' + precioSugeridoVal + '">' +
                '</div>' +
                '<p class="help" style="color: rgba(255,255,255,0.7);">Precio inicial en ventas</p>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '<div class="button-group">' +
                '<button type="submit" class="button">💾 Guardar Cambios</button>' +
                '<button type="reset" class="secondary-button" onclick="resetForm()">🔄 Restaurar Valores</button>' +
                '<a href="./listado_servicio.php" class="secondary-button">📋 Volver al Listado</a>' +
                '</div>' +
                '</form>' +
                '</div>';
            
            mainContent.innerHTML = contentHTML;

            var categoriaInput = document.getElementById('categoria_servicio');
            
            categoriaInput.addEventListener('input', function() {
                var value = this.value.trim();
                filteredSuggestions = filterCategories(value);
                showSuggestions(this, filteredSuggestions);
                selectedIndex = -1;
            });

            categoriaInput.addEventListener('focus', function() {
                if (this.value.trim()) {
                    filteredSuggestions = filterCategories(this.value);
                    showSuggestions(this, filteredSuggestions);
                }
            });

            categoriaInput.addEventListener('blur', function() {
                hideSuggestions(this);
            });

            window.validateForm = function() {
                var nombre = document.getElementById('nombre_servicio').value.trim();
                var categoria = document.getElementById('categoria_servicio').value.trim();
                
                if (!nombre) {
                    alert('Por favor, ingresa el nombre del servicio');
                    return false;
                }
                
                if (!categoria) {
                    alert('Por favor, ingresa o selecciona una categoría');
                    return false;
                }
                
                return true;
            };

            window.resetForm = function() {
                selectedIndex = -1;
                filteredSuggestions = [];
                var suggestionsDiv = document.querySelector('.autocomplete-suggestions');
                if (suggestionsDiv) {
                    suggestionsDiv.classList.remove('active');
                }
                return true;
            };
        });
    </script>
</body>
</html>