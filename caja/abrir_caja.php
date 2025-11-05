<?php
include_once "../auth.php";
include_once "../db.php";

// Registrar acceso al módulo
registrarActividad('ACCESO', 'CAJA', 'Acceso a apertura de caja', null, null);

// Verificar si ya hay una caja abierta
$sentencia = $conexion->prepare("SELECT * FROM cierres_caja WHERE estado = 'ABIERTA' ORDER BY fecha_apertura DESC LIMIT 1");
$sentencia->execute();
$cajaAbierta = $sentencia->fetch(PDO::FETCH_OBJ);

if ($cajaAbierta) {
    header("Location: balance.php?error=caja_ya_abierta");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Abrir Caja</title>
    <link href="../css/bulma.min.css" rel="stylesheet">
    <link href="../css/formularios.css" rel="stylesheet">
    <style>
        .main-content { background: #2c3e50 !important; color: white; }
        .apertura-icon {
            text-align: center;
            font-size: 5rem;
            margin-bottom: 20px;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        .info-usuario {
            background: rgba(52, 152, 219, 0.1);
            border: 2px solid rgba(52, 152, 219, 0.3);
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <?php include '../menu.php'; ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mainContent = document.querySelector('.main-content');
            if (!mainContent) return;

            const now = new Date();
            const fechaHora = `${now.getFullYear()}-${String(now.getMonth()+1).padStart(2,'0')}-${String(now.getDate()).padStart(2,'0')}T${String(now.getHours()).padStart(2,'0')}:${String(now.getMinutes()).padStart(2,'0')}`;

            const usuarioNombre = '<?php echo addslashes($_SESSION['usuario_nombre']); ?>';

            const formHTML = `
                <div class="form-container">
                    <div class="apertura-icon">🔓💰</div>
                    <h1 class="form-title">Apertura de Caja</h1>
                    
                    <div class="info-usuario">
                        👤 Apertura realizada por: <strong style="color: #f1c40f;">${usuarioNombre}</strong>
                    </div>
                    
                    <form action="./procesar_apertura.php" method="post" onsubmit="return confirm('¿Confirmar apertura de caja?')">
                        <div class="columns">
                            <div class="column is-6 is-offset-3">
                                <div class="field">
                                    <label class="label">Fecha y Hora de Apertura *</label>
                                    <div class="control">
                                        <input class="input" type="datetime-local" name="fecha_apertura" value="${fechaHora}" required>
                                    </div>
                                </div>

                                <div class="field">
                                    <label class="label">Saldo Inicial en Caja *</label>
                                    <div class="control">
                                        <input class="input" type="number" step="0.01" min="0" name="saldo_inicial" placeholder="0.00" required style="font-size: 1.3rem; font-weight: bold;">
                                    </div>
                                    <p class="help" style="color: rgba(255,255,255,0.7);">Dinero físico con el que inicias (puede ser 0)</p>
                                </div>

                                <div class="field">
                                    <label class="label">Observaciones (Opcional)</label>
                                    <div class="control">
                                        <textarea class="textarea" name="observaciones_apertura" rows="3" placeholder="Notas adicionales..."></textarea>
                                    </div>
                                </div>

                                <div class="field is-grouped" style="justify-content: center; margin-top: 30px;">
                                    <div class="control">
                                        <button type="submit" class="button">🔓 Abrir Caja</button>
                                    </div>
                                    <div class="control">
                                        <a href="../index.php" class="secondary-button">❌ Cancelar</a>
                                    </div>
                                </div>
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