<?php
include_once "../auth.php";
$cajaAbierta = requiereCajaAbierta();

// Registrar acceso al módulo
registrarActividad('ACCESO', 'CAJA', 'Acceso a registro de movimiento manual', null, null);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Movimiento de Caja</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <link href="../css/bulma.min.css" rel="stylesheet">
    <link href="../css/formularios.css" rel="stylesheet">

    <style>
        .main-content { background: #2c3e50 !important; color: white; }
        .tipo-selector {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-bottom: 30px;
        }
        .tipo-btn {
            flex: 1;
            max-width: 250px;
            padding: 20px;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            border: 3px solid transparent;
        }
        .tipo-btn input[type="radio"] { display: none; }
        .tipo-btn label { cursor: pointer; display: block; }
        .tipo-btn .tipo-icon {
            font-size: 3rem;
            margin-bottom: 10px;
        }
        .tipo-btn .tipo-label {
            font-size: 1.2rem;
            font-weight: bold;
        }
        .tipo-btn.ingreso {
            background: rgba(39, 174, 96, 0.1);
            border-color: rgba(39, 174, 96, 0.3);
        }
        .tipo-btn.ingreso:hover,
        .tipo-btn.ingreso.selected {
            background: rgba(39, 174, 96, 0.3);
            border-color: #27ae60;
            transform: scale(1.05);
            box-shadow: 0 5px 20px rgba(39, 174, 96, 0.4);
        }
        .tipo-btn.egreso {
            background: rgba(231, 76, 60, 0.1);
            border-color: rgba(231, 76, 60, 0.3);
        }
        .tipo-btn.egreso:hover,
        .tipo-btn.egreso.selected {
            background: rgba(231, 76, 60, 0.3);
            border-color: #e74c3c;
            transform: scale(1.05);
            box-shadow: 0 5px 20px rgba(231, 76, 60, 0.4);
        }
        .info-usuario {
            background: rgba(52, 152, 219, 0.1);
            border: 2px solid rgba(52, 152, 219, 0.3);
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        /* Colores de iconos */
        .icon-titulo { color: #f1c40f; }
        .icon-ingreso { color: #27ae60; }
        .icon-egreso { color: #e74c3c; }
        .icon-usuario { color: #3498db; }
        .icon-guardar { color: #2980b9; }
        .icon-limpiar { color: #f39c12; }
        .icon-balance { color: #1a5490; }
        .icon-alerta { color: #f1c40f; }
    </style>
</head>
<body>

    <?php include '../menu.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mainContent = document.querySelector('.main-content');
            if (!mainContent) return console.error('No .main-content');

            const usuarioNombre = '<?php echo addslashes($_SESSION['usuario_nombre']); ?>';

            // Fecha y hora actual
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const fechaHoraActual = `${year}-${month}-${day}T${hours}:${minutes}`;

            const formHTML = `
                <div class="form-container">
                    <h1 class="form-title">
                        <i class="fa-solid fa-money-bill-wave icon-titulo"></i> Registrar Movimiento de Caja
                    </h1>
                    
                    <div class="info-usuario">
                        <i class="fa-solid fa-user icon-usuario"></i> Movimiento registrado por: 
                        <strong style="color: #f1c40f;">${usuarioNombre}</strong>
                    </div>
                    
                    <form action="./guardar_movimiento.php" method="post" onsubmit="return validateForm()">
                        
                        <!-- Selector de Tipo -->
                        <div class="tipo-selector">
                            <div class="tipo-btn ingreso" onclick="selectTipo('INGRESO')">
                                <input type="radio" name="tipo_movimiento" id="tipo_ingreso" value="INGRESO" required>
                                <label for="tipo_ingreso">
                                    <div class="tipo-icon"><i class="fa-solid fa-sack-dollar icon-ingreso"></i></div>
                                    <div class="tipo-label">INGRESO</div>
                                    <div style="color: rgba(255,255,255,0.7); font-size: 0.9rem;">Entrada de dinero</div>
                                </label>
                            </div>
                            
                            <div class="tipo-btn egreso" onclick="selectTipo('EGRESO')">
                                <input type="radio" name="tipo_movimiento" id="tipo_egreso" value="EGRESO" required>
                                <label for="tipo_egreso">
                                    <div class="tipo-icon"><i class="fa-solid fa-money-bill-transfer icon-egreso"></i></div>
                                    <div class="tipo-label">EGRESO</div>
                                    <div style="color: rgba(255,255,255,0.7); font-size: 0.9rem;">Salida de dinero</div>
                                </label>
                            </div>
                        </div>

                        <div class="columns">
                            <div class="column is-6">
                                <div class="field">
                                    <label class="label">Categoría *</label>
                                    <div class="control">
                                        <div class="select is-fullwidth">
                                            <select name="categoria" id="categoria" required>
                                                <option value="">-- Seleccionar --</option>
                                                <option value="OTRO">OTRO (Movimiento manual)</option>
                                                <option value="VENTA" disabled style="color: rgba(255,255,255,0.5);">VENTA (Automático)</option>
                                                <option value="COMPRA" disabled style="color: rgba(255,255,255,0.5);">COMPRA (Automático)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <p class="help" style="color: rgba(255,255,255,0.7);">VENTA/COMPRA se registran automáticamente</p>
                                </div>

                                <div class="field">
                                    <label class="label">Concepto *</label>
                                    <div class="control">
                                        <input class="input" type="text" name="concepto" id="concepto" required maxlength="200" placeholder="Ej: Pago de luz">
                                    </div>
                                </div>

                                <div class="field">
                                    <label class="label">Monto *</label>
                                    <div class="control">
                                        <input class="input" type="number" step="0.01" min="0.01" name="monto" id="monto" required style="font-size: 1.3rem; font-weight: bold;">
                                    </div>
                                </div>
                            </div>

                            <div class="column is-6">
                                <div class="field">
                                    <label class="label">Fecha del Movimiento *</label>
                                    <div class="control">
                                        <input class="input" type="datetime-local" name="fecha_movimiento" id="fecha_movimiento" value="${fechaHoraActual}" required>
                                    </div>
                                </div>

                                <div class="field">
                                    <label class="label">Observaciones (Opcional)</label>
                                    <div class="control">
                                        <textarea class="textarea" name="observaciones" id="observaciones" rows="5" placeholder="Detalles adicionales..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="field is-grouped" style="justify-content: center; margin-top: 30px;">
                            <div class="control">
                                <button type="submit" class="button">
                                    <i class="fa-solid fa-floppy-disk icon-guardar"></i> Guardar Movimiento
                                </button>
                            </div>
                            <div class="control">
                                <button type="reset" class="button" onclick="resetForm()">
                                    <i class="fa-solid fa-rotate icon-limpiar"></i> Limpiar
                                </button>
                            </div>
                            <div class="control">
                                <a href="./balance.php" class="secondary-button">
                                    <i class="fa-solid fa-chart-column icon-balance"></i> Ver Balance
                                </a>
                            </div>
                        </div>

                    </form>
                </div>
            `;

            mainContent.innerHTML = formHTML;

            // ===== Funciones =====
            window.selectTipo = function(tipo) {
                document.getElementById('tipo_' + tipo.toLowerCase()).checked = true;
                document.querySelectorAll('.tipo-btn').forEach(btn => btn.classList.remove('selected'));
                document.querySelector('.tipo-btn.' + tipo.toLowerCase()).classList.add('selected');
            };

            window.validateForm = function() {
                const tipo = document.querySelector('input[name="tipo_movimiento"]:checked');
                const categoria = document.getElementById('categoria').value;
                const concepto = document.getElementById('concepto').value.trim();
                const monto = parseFloat(document.getElementById('monto').value);

                if (!tipo) {
                    alert('⚠️ Selecciona el tipo de movimiento');
                    return false;
                }
                if (!categoria) {
                    alert('⚠️ Selecciona una categoría');
                    return false;
                }
                if (!concepto) {
                    alert('⚠️ Ingresa un concepto');
                    return false;
                }
                if (!monto || monto <= 0) {
                    alert('⚠️ El monto debe ser mayor a 0');
                    return false;
                }

                const tipoLabel = tipo.value === 'INGRESO' ? 'INGRESO' : 'EGRESO';

                return confirm(
                    `¿Confirmar ${tipoLabel} de ₲ ${monto.toLocaleString('es-PY')}?\n\nConcepto: ${concepto}`
                );
            };

            window.resetForm = function() {
                setTimeout(() => {
                    document.querySelectorAll('.tipo-btn').forEach(btn => btn.classList.remove('selected'));
                    document.getElementById('fecha_movimiento').value = fechaHoraActual;
                }, 0);
            };
        });
    </script>
</body>
</html>