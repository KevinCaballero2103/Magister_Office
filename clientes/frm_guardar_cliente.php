<?php
include_once __DIR__ . "/../auth.php";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Cliente</title>
    <link href="../css/bulma.min.css" rel="stylesheet">
    <link href="../css/formularios.css" rel="stylesheet">
    
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
    </style>
</head>
<body>
    <?php include '../menu.php'; ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mainContent = document.querySelector('.main-content');
            const formHTML = `
                <div class="form-container">
                    <h1 class="form-title">Registrar Cliente</h1>
                    
                    <form action="./guardar_cliente.php" method="post">
                        <div class="columns">
                            <div class="column is-6">
                                <div class="field">
                                    <label class="label">Nombre</label>
                                    <div class="control">
                                        <input class="input" type="text" name="nombre_cliente" id="nombre_cliente" placeholder="Ingresa el nombre" required>
                                    </div>
                                </div>

                                <div class="field">
                                    <label class="label">Apellido</label>
                                    <div class="control">
                                        <input class="input" type="text" name="apellido_cliente" id="apellido_cliente" placeholder="Ingresa el apellido" required>
                                    </div>
                                </div>

                                <div class="field">
                                    <label class="label">CI / RUC</label>
                                    <div class="control">
                                        <input class="input" type="text" name="ci_ruc_cliente" id="ci_ruc_cliente" placeholder="Ingresa CI o RUC" required>
                                    </div>
                                </div>

                                <div class="field">
                                    <label class="label">Teléfono</label>
                                    <div class="control">
                                        <input class="input" type="text" name="telefono_cliente" id="telefono_cliente" placeholder="Ingresa el teléfono">
                                    </div>
                                </div>
                            </div>

                            <div class="column is-6">
                                <div class="field">
                                    <label class="label">Correo</label>
                                    <div class="control">
                                        <input class="input" type="email" name="correo_cliente" id="correo_cliente" placeholder="correo@ejemplo.com">
                                    </div>
                                </div>

                                <div class="field">
                                    <label class="label">Dirección</label>
                                    <div class="control">
                                        <input class="input" type="text" name="direccion_cliente" id="direccion_cliente" placeholder="Ingresa la dirección">
                                    </div>
                                </div>

                                <div class="field">
                                    <label class="label">Estado</label>
                                    <div class="control">
                                        <div class="select is-fullwidth">
                                            <select name="estado_cliente" id="estado_cliente">
                                                <option value="1">Activo</option>
                                                <option value="0">Inactivo</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="field">
                                    <div style="height: 48px;"></div>
                                </div>
                            </div>
                        </div>            

                        <div class="field is-grouped" style="justify-content: center; margin-top: 30px;">
                            <div class="control">
                                <button type="submit" class="button">
                                    💾 Guardar Cliente
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
        });
    </script>
</body>
</html>