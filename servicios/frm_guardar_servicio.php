<?php
// Obtener categorías existentes para el autocompletado
include_once "../db.php";

$sentenciaCategorias = $conexion->prepare("SELECT DISTINCT categoria_servicio FROM servicios WHERE categoria_servicio IS NOT NULL AND categoria_servicio != '' ORDER BY categoria_servicio ASC");
$sentenciaCategorias->execute();
$categorias = $sentenciaCategorias->fetchAll(PDO::FETCH_COLUMN);
$categoriasJSON = json_encode($categorias);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Servicio</title>
    <link href="../css/bulma.min.css" rel="stylesheet">
    <link href="../css/formularios.css" rel="stylesheet">
    <link href="../css/autocompletado.css" rel="stylesheet">
    
    <!-- Solo estilos específicos -->
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

        /* Ancho específico para formularios de servicios */
        .form-container {
            max-width: 800px;
        }
    </style>
</head>
<body>
    <?php include '../menu.php'; ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mainContent = document.querySelector('.main-content');
            const categorias = <?php echo $categoriasJSON; ?>;
            
            let selectedIndex = -1;
            let filteredSuggestions = [];

            function filterCategories(searchTerm) {
                if (!searchTerm) return [];
                const term = searchTerm.toLowerCase();
                return categorias.filter(cat => cat.toLowerCase().includes(term));
            }

            function showSuggestions(input, suggestions) {
                const container = input.parentElement;
                let suggestionsDiv = container.querySelector('.autocomplete-suggestions');
                
                if (!suggestionsDiv) {
                    suggestionsDiv = document.createElement('div');
                    suggestionsDiv.className = 'autocomplete-suggestions';
                    container.appendChild(suggestionsDiv);
                }

                if (suggestions.length === 0 && input.value.trim() !== '') {
                    suggestionsDiv.innerHTML = `
                        <div class="suggestion-item suggestion-new" data-value="${input.value}">
                            <span class="suggestion-icon">✨</span>
                            Crear nueva categoría: "${input.value}"
                        </div>
                    `;
                    suggestionsDiv.classList.add('active');
                } else if (suggestions.length > 0) {
                    let html = '';
                    suggestions.forEach((cat, index) => {
                        html += `
                            <div class="suggestion-item" data-value="${cat}" data-index="${index}">
                                <span class="suggestion-icon">📁</span>
                                ${cat}
                            </div>
                        `;
                    });
                    suggestionsDiv.innerHTML = html;
                    suggestionsDiv.classList.add('active');
                } else {
                    suggestionsDiv.classList.remove('active');
                }

                const items = suggestionsDiv.querySelectorAll('.suggestion-item');
                items.forEach(item => {
                    item.addEventListener('click', function() {
                        input.value = this.getAttribute('data-value');
                        suggestionsDiv.classList.remove('active');
                        selectedIndex = -1;
                    });
                });
            }

            function hideSuggestions(input) {
                const container = input.parentElement;
                const suggestionsDiv = container.querySelector('.autocomplete-suggestions');
                if (suggestionsDiv) {
                    setTimeout(() => suggestionsDiv.classList.remove('active'), 200);
                }
            }

            function highlightSuggestion(suggestionsDiv, index) {
                const items = suggestionsDiv.querySelectorAll('.suggestion-item');
                items.forEach((item, i) => {
                    if (i === index) {
                        item.classList.add('highlighted');
                        item.scrollIntoView({ block: 'nearest' });
                    } else {
                        item.classList.remove('highlighted');
                    }
                });
            }

            const formHTML = `
                <div class="form-container">
                    <h1 class="form-title">🔧 Registrar Servicio</h1>
                    
                    <form action="./guardar_servicio.php" method="post" onsubmit="return validateForm()">
                        <div class="columns">
                            <div class="column is-8">
                                <div class="field">
                                    <label class="label">Nombre del Servicio</label>
                                    <div class="control">
                                        <input class="input" type="text" name="nombre_servicio" id="nombre_servicio" 
                                               placeholder="Ej: Reparación de impresora, Mantenimiento preventivo, etc." required>
                                    </div>
                                </div>
                            </div>

                            <div class="column is-4">
                                <div class="field">
                                    <label class="label">Estado</label>
                                    <div class="control">
                                        <div class="select is-fullwidth">
                                            <select name="estado_servicio" id="estado_servicio">
                                                <option value="1">Activo</option>
                                                <option value="0">Inactivo</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="field">
                            <label class="label">Categoría del Servicio</label>
                            <div class="control autocomplete-container">
                                <input class="input" type="text" name="categoria_servicio" id="categoria_servicio" 
                                       placeholder="Escribe para buscar o crear una categoría (Ej: Impresión, Reparación, Consultoría...)" 
                                       autocomplete="off" required>
                            </div>
                            <p class="help">
                                💡 Escribe el nombre de la categoría. Si ya existe, aparecerá en las sugerencias. Si no existe, se creará automáticamente.
                            </p>
                        </div>

                        <div class="field is-grouped" style="justify-content: center; margin-top: 30px;">
                            <div class="control">
                                <button type="submit" class="button">
                                    💾 Guardar Servicio
                                </button>
                            </div>
                            <div class="control">
                                <button type="reset" class="button" onclick="resetForm()">
                                    🔄 Limpiar Formulario
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            `;
            
            mainContent.innerHTML = formHTML;

            const categoriaInput = document.getElementById('categoria_servicio');
            
            categoriaInput.addEventListener('input', function() {
                const value = this.value.trim();
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

            categoriaInput.addEventListener('keydown', function(e) {
                const container = this.parentElement;
                const suggestionsDiv = container.querySelector('.autocomplete-suggestions');
                
                if (!suggestionsDiv || !suggestionsDiv.classList.contains('active')) return;

                const items = suggestionsDiv.querySelectorAll('.suggestion-item');
                
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    selectedIndex = Math.min(selectedIndex + 1, items.length - 1);
                    highlightSuggestion(suggestionsDiv, selectedIndex);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    selectedIndex = Math.max(selectedIndex - 1, 0);
                    highlightSuggestion(suggestionsDiv, selectedIndex);
                } else if (e.key === 'Enter' && selectedIndex >= 0) {
                    e.preventDefault();
                    const selectedItem = items[selectedIndex];
                    this.value = selectedItem.getAttribute('data-value');
                    suggestionsDiv.classList.remove('active');
                    selectedIndex = -1;
                } else if (e.key === 'Escape') {
                    suggestionsDiv.classList.remove('active');
                    selectedIndex = -1;
                }
            });

            window.validateForm = function() {
                const nombre = document.getElementById('nombre_servicio').value.trim();
                const categoria = document.getElementById('categoria_servicio').value.trim();
                
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
                const suggestionsDiv = document.querySelector('.autocomplete-suggestions');
                if (suggestionsDiv) {
                    suggestionsDiv.classList.remove('active');
                }
                return true;
            };
        });
    </script>
</body>
</html>