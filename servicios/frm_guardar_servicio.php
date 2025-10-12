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
            const formHTML = `
                <div class="form-container">
                    <h1 class="form-title">Registrar Servicio</h1>
                    
                    <form action="./guardar_servicio.php" method="post">
                        <div class="columns">
                            <div class="column is-8">
                                <div class="field">
                                    <label class="label">Nombre del Servicio</label>
                                    <div class="control">
                                        <input class="input" type="text" name="nombre_servicio" id="nombre_servicio" placeholder="Ingresa el nombre del servicio" required>
                                    </div>
                                </div>
                            </div>

                            <div class="column is-4">
                                <div class="field">
                                    <label class="label">Estado</label>
                                    <div class="control">
                                        <div class="select is-fullwidth">
                                            <select name="estado_servicio" id="estado_servicio">
                                                <option value="1" selected>Activo</option>
                                                <option value="0">Inactivo</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="columns">
                            <div class="column is-12">
                                <div class="field">
                                    <label class="label">Categoría</label>
                                    <div class="control autocomplete-container">
                                        <input class="input" type="text" id="categoria_input" placeholder="Buscar o crear categoría" autocomplete="off">
                                        <input type="hidden" name="categoria_servicio" id="categoria_servicio" required>
                                        <div class="autocomplete-suggestions" id="categoria_suggestions"></div>
                                    </div>
                                    <p class="help">Escribe para buscar una categoría existente o crear una nueva</p>
                                </div>
                            </div>
                        </div>

                        <div class="field is-grouped" style="justify-content: center; margin-top: 30px;">
                            <div class="control">
                                <button type="submit" class="button">
                                    💾 Guardar Servicio
                                </button>
                            </div>
                            <div class="control">
                                <button type="reset" class="button">
                                    🔄 Limpiar Formulario
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            `;
            
            mainContent.innerHTML = formHTML;

            // Inicializar el autocompletado
            initAutocompletado();
        });

        function initAutocompletado() {
            // Categorías predefinidas (pueden cargarse desde PHP/base de datos)
            let categorias = [
                'Consultoría',
                'Desarrollo Web',
                'Diseño Gráfico',
                'Marketing Digital',
                'Mantenimiento',
                'Soporte Técnico',
                'Capacitación',
                'Instalación',
                'Reparación',
                'Limpieza',
                'Asesoría'
            ];

            const inputVisible = document.getElementById('categoria_input');
            const inputHidden = document.getElementById('categoria_servicio');
            const suggestions = document.getElementById('categoria_suggestions');

            // Mostrar sugerencias al hacer focus
            inputVisible.addEventListener('focus', function() {
                if (this.value.trim() === '') {
                    mostrarTodasCategorias();
                } else {
                    filtrarCategorias(this.value);
                }
            });

            // Filtrar mientras escribe
            inputVisible.addEventListener('input', function() {
                const valor = this.value.trim();
                
                if (valor === '') {
                    mostrarTodasCategorias();
                } else {
                    filtrarCategorias(valor);
                }
            });

            // Cerrar al hacer clic fuera
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.autocomplete-container')) {
                    suggestions.classList.remove('active');
                }
            });

            // Función para mostrar todas las categorías
            function mostrarTodasCategorias() {
                suggestions.innerHTML = '';
                
                if (categorias.length === 0) {
                    suggestions.innerHTML = '<div class="suggestion-item" style="cursor: default; opacity: 0.7;">No hay categorías disponibles</div>';
                } else {
                    categorias.forEach(cat => {
                        const item = crearItemSugerencia(cat, false);
                        suggestions.appendChild(item);
                    });
                }
                
                suggestions.classList.add('active');
            }

            // Función para filtrar categorías
            function filtrarCategorias(termino) {
                suggestions.innerHTML = '';
                
                const terminoLower = termino.toLowerCase();
                const coincidencias = categorias.filter(cat => 
                    cat.toLowerCase().includes(terminoLower)
                );

                // Mostrar coincidencias
                if (coincidencias.length > 0) {
                    coincidencias.forEach(cat => {
                        const item = crearItemSugerencia(cat, false);
                        suggestions.appendChild(item);
                    });
                }

                // Verificar si la categoría ya existe exactamente
                const existeExacta = categorias.some(cat => 
                    cat.toLowerCase() === terminoLower
                );

                // Mostrar opción para crear nueva categoría
                if (!existeExacta && termino.trim() !== '') {
                    const itemNuevo = crearItemSugerencia(termino, true);
                    suggestions.appendChild(itemNuevo);
                }

                // Mensaje si no hay resultados
                if (suggestions.children.length === 0) {
                    suggestions.innerHTML = '<div class="suggestion-item" style="cursor: default; opacity: 0.7;">No se encontraron categorías</div>';
                }

                suggestions.classList.add('active');
            }

            // Función para crear un item de sugerencia
            function crearItemSugerencia(texto, esNuevo) {
                const item = document.createElement('div');
                item.className = 'suggestion-item';
                
                if (esNuevo) {
                    item.classList.add('suggestion-new');
                    item.innerHTML = `<span class="suggestion-icon">✨</span> Crear: "<strong>${texto}</strong>"`;
                    item.addEventListener('click', function() {
                        crearYSeleccionarCategoria(texto);
                    });
                } else {
                    item.innerHTML = `<span class="suggestion-icon">📁</span> ${texto}`;
                    item.addEventListener('click', function() {
                        seleccionarCategoria(texto);
                    });
                }

                return item;
            }

            // Función para seleccionar una categoría existente
            function seleccionarCategoria(categoria) {
                inputVisible.value = categoria;
                inputHidden.value = categoria;
                suggestions.classList.remove('active');
            }

            // Función para crear y seleccionar una nueva categoría
            function crearYSeleccionarCategoria(nuevaCategoria) {
                // Capitalizar primera letra
                const categoriaCapitalizada = nuevaCategoria.charAt(0).toUpperCase() + nuevaCategoria.slice(1);
                
                // Agregar a la lista si no existe
                if (!categorias.includes(categoriaCapitalizada)) {
                    categorias.push(categoriaCapitalizada);
                    categorias.sort();
                }
                
                seleccionarCategoria(categoriaCapitalizada);
            }

            // Validación antes de enviar
            const form = document.querySelector('form');
            form.addEventListener('submit', function(e) {
                if (inputHidden.value === '') {
                    e.preventDefault();
                    alert('Por favor selecciona o crea una categoría');
                    inputVisible.focus();
                }
            });

            // Limpiar al resetear el formulario
            form.addEventListener('reset', function() {
                setTimeout(() => {
                    inputVisible.value = '';
                    inputHidden.value = '';
                    suggestions.classList.remove('active');
                }, 0);
            });

            // Navegar con teclado (opcional)
            let indiceSeleccionado = -1;
            
            inputVisible.addEventListener('keydown', function(e) {
                const items = suggestions.querySelectorAll('.suggestion-item');
                
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
                items.forEach((item, index) => {
                    if (index === indiceSeleccionado) {
                        item.classList.add('highlighted');
                        item.scrollIntoView({ block: 'nearest' });
                    } else {
                        item.classList.remove('highlighted');
                    }
                });
            }
        }
    </script>
</body>
</html>