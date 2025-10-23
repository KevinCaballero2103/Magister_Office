<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Servicio</title>
    <link href="../css/bulma.min.css" rel="stylesheet">
    <link href="../css/formularios.css" rel="stylesheet">
    <link href="../css/autocompletado.css" rel="stylesheet">
    
    <style>
        .main-content {
            background: #2c3e50 !important;
            color: white;
        }
    </style>
</head>
<body>
    <?php 
    include '../menu.php'; 
    include_once '../db.php';
    
    // Obtener categorías existentes desde la BD
    try {
        $sentenciaCategorias = $conexion->prepare("SELECT DISTINCT categoria_servicio FROM servicios WHERE categoria_servicio IS NOT NULL AND categoria_servicio != '' ORDER BY categoria_servicio ASC");
        $sentenciaCategorias->execute();
        $categoriasExistentes = $sentenciaCategorias->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {
        $categoriasExistentes = array();
    }
    
    $categoriasJSON = json_encode($categoriasExistentes);
    ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var mainContent = document.querySelector('.main-content');
            
            var formHTML = '<div class="form-container">' +
                '<h1 class="form-title">🔧 Registrar Servicio</h1>' +
                '<form action="./guardar_servicio.php" method="post" onsubmit="return validateForm()">' +
                '<div class="columns">' +
                '<div class="column is-8">' +
                '<div class="field">' +
                '<label class="label">Nombre del Servicio *</label>' +
                '<div class="control">' +
                '<input class="input" type="text" name="nombre_servicio" id="nombre_servicio" placeholder="Ej: Reparación de impresora" required>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '<div class="column is-4">' +
                '<div class="field">' +
                '<label class="label">Estado</label>' +
                '<div class="control">' +
                '<div class="select is-fullwidth">' +
                '<select name="estado_servicio" id="estado_servicio">' +
                '<option value="1" selected>Activo</option>' +
                '<option value="0">Inactivo</option>' +
                '</select>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '<div class="columns">' +
                '<div class="column is-8">' +
                '<div class="field">' +
                '<label class="label">Categoría *</label>' +
                '<div class="control autocomplete-container">' +
                '<input class="input" type="text" id="categoria_input" placeholder="Escribe para buscar o crear categoría" autocomplete="off">' +
                '<input type="hidden" name="categoria_servicio" id="categoria_servicio" required>' +
                '<div class="autocomplete-suggestions" id="categoria_suggestions"></div>' +
                '</div>' +
                '<p class="help" style="color: rgba(255,255,255,0.7);">Escribe para buscar una categoría existente o crear una nueva</p>' +
                '</div>' +
                '</div>' +
                '<div class="column is-4">' +
                '<div class="field">' +
                '<label class="label">Precio Sugerido (Opcional)</label>' +
                '<div class="control">' +
                '<input class="input" type="number" step="0.01" min="0" name="precio_sugerido" id="precio_sugerido" placeholder="0.00">' +
                '</div>' +
                '<p class="help" style="color: rgba(255,255,255,0.7);">Se usará como precio inicial en ventas</p>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '<div class="field is-grouped" style="justify-content: center; margin-top: 30px;">' +
                '<div class="control">' +
                '<button type="submit" class="button">💾 Guardar Servicio</button>' +
                '</div>' +
                '<div class="control">' +
                '<button type="reset" class="button" onclick="resetFormulario()">🔄 Limpiar Formulario</button>' +
                '</div>' +
                '<div class="control">' +
                '<a href="./listado_servicio.php" class="secondary-button">📋 Ver Listado</a>' +
                '</div>' +
                '</div>' +
                '</form>' +
                '</div>';
            
            mainContent.innerHTML = formHTML;

            initAutocompletado();
        });

        function initAutocompletado() {
            // Cargar categorías desde la base de datos
            var categorias = <?php echo $categoriasJSON; ?> || [];

            var inputVisible = document.getElementById('categoria_input');
            var inputHidden = document.getElementById('categoria_servicio');
            var suggestions = document.getElementById('categoria_suggestions');

            inputVisible.addEventListener('focus', function() {
                if (this.value.trim() === '') {
                    mostrarTodasCategorias();
                } else {
                    filtrarCategorias(this.value);
                }
            });

            inputVisible.addEventListener('input', function() {
                var valor = this.value.trim();
                if (valor === '') {
                    mostrarTodasCategorias();
                } else {
                    filtrarCategorias(valor);
                }
            });

            document.addEventListener('click', function(e) {
                if (!e.target.closest('.autocomplete-container')) {
                    suggestions.classList.remove('active');
                }
            });

            function mostrarTodasCategorias() {
                suggestions.innerHTML = '';
                
                if (categorias.length === 0) {
                    suggestions.innerHTML = '<div class="suggestion-item" style="cursor: default;">No hay categorías</div>';
                } else {
                    categorias.forEach(function(cat) {
                        var item = crearItemSugerencia(cat, false);
                        suggestions.appendChild(item);
                    });
                }
                
                suggestions.classList.add('active');
            }

            function filtrarCategorias(termino) {
                suggestions.innerHTML = '';
                
                var terminoLower = termino.toLowerCase();
                var coincidencias = categorias.filter(function(cat) {
                    return cat.toLowerCase().includes(terminoLower);
                });

                if (coincidencias.length > 0) {
                    coincidencias.forEach(function(cat) {
                        var item = crearItemSugerencia(cat, false);
                        suggestions.appendChild(item);
                    });
                }

                var existeExacta = categorias.some(function(cat) {
                    return cat.toLowerCase() === terminoLower;
                });

                if (!existeExacta && termino.trim() !== '') {
                    var itemNuevo = crearItemSugerencia(termino, true);
                    suggestions.appendChild(itemNuevo);
                }

                if (suggestions.children.length === 0) {
                    suggestions.innerHTML = '<div class="suggestion-item" style="cursor: default;">No se encontraron</div>';
                }

                suggestions.classList.add('active');
            }

            function crearItemSugerencia(texto, esNuevo) {
                var item = document.createElement('div');
                item.className = 'suggestion-item';
                
                if (esNuevo) {
                    item.classList.add('suggestion-new');
                    item.innerHTML = '<span class="suggestion-icon">✨</span> Crear: "<strong>' + texto + '</strong>"';
                    item.addEventListener('click', function() {
                        crearYSeleccionarCategoria(texto);
                    });
                } else {
                    item.innerHTML = '<span class="suggestion-icon">📁</span> ' + texto;
                    item.addEventListener('click', function() {
                        seleccionarCategoria(texto);
                    });
                }

                return item;
            }

            function seleccionarCategoria(categoria) {
                inputVisible.value = categoria;
                inputHidden.value = categoria;
                suggestions.classList.remove('active');
            }

            function crearYSeleccionarCategoria(nuevaCategoria) {
                var categoriaCapitalizada = nuevaCategoria.toUpperCase();
                
                if (categorias.indexOf(categoriaCapitalizada) === -1) {
                    categorias.push(categoriaCapitalizada);
                    categorias.sort();
                }
                
                seleccionarCategoria(categoriaCapitalizada);
            }

            var indiceSeleccionado = -1;
            
            inputVisible.addEventListener('keydown', function(e) {
                var items = suggestions.querySelectorAll('.suggestion-item');
                
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    indiceSeleccionado = Math.min(indiceSeleccionado + 1, items.length - 1);
                    actualizarSeleccion(items);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    indiceSeleccionado = Math.max(indiceSeleccionado - 1, 0);
                    actualizarSeleccion(items);
                } else if (e.key === 'Enter' && indiceSeleccionado >= 0) {
                    e.preventDefault();
                    items[indiceSeleccionado].click();
                    indiceSeleccionado = -1;
                } else if (e.key === 'Escape') {
                    suggestions.classList.remove('active');
                    indiceSeleccionado = -1;
                }
            });

            function actualizarSeleccion(items) {
                items.forEach(function(item, index) {
                    if (index === indiceSeleccionado) {
                        item.classList.add('highlighted');
                        item.scrollIntoView({ block: 'nearest' });
                    } else {
                        item.classList.remove('highlighted');
                    }
                });
            }
        }

        function validateForm() {
            var nombre = document.getElementById('nombre_servicio').value.trim();
            var categoriaHidden = document.getElementById('categoria_servicio').value;
            
            if (!nombre) {
                alert('Por favor ingresa el nombre del servicio');
                return false;
            }
            
            if (!categoriaHidden) {
                alert('Por favor selecciona o crea una categoría');
                return false;
            }
            
            return true;
        }

        function resetFormulario() {
            setTimeout(function() {
                document.getElementById('categoria_input').value = '';
                document.getElementById('categoria_servicio').value = '';
                document.getElementById('categoria_suggestions').classList.remove('active');
            }, 0);
        }
    </script>
</body>
</html>