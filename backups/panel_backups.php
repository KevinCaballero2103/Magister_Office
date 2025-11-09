<?php
include_once "../auth.php";

// Solo administradores
if (!tienePermiso(['ADMINISTRADOR'])) {
    header("Location: ../index.php?error=sin_permisos");
    exit();
}

require_once __DIR__ . '/generar_backup.php';

// Manejar acciones
$mensaje = "";
$tipo = "";

if (isset($_GET['accion'])) {
    if ($_GET['accion'] === 'generar') {
        $resultado = generarBackup(true);
        if ($resultado['exito']) {
            registrarActividad('BACKUP', 'SISTEMA', "Backup manual generado: {$resultado['archivo']}", null, [
                'archivo' => $resultado['archivo'],
                'tamano' => $resultado['tamano_legible']
            ]);
        }
        $mensaje = $resultado['exito']
            ? "Backup generado: {$resultado['archivo']} ({$resultado['tamano_legible']})"
            : "Error: {$resultado['error']}";
        $tipo = $resultado['exito'] ? 'success' : 'error';
    } elseif ($_GET['accion'] === 'descargar' && isset($_GET['archivo'])) {
        registrarActividad('BACKUP', 'SISTEMA', "Backup descargado: {$_GET['archivo']}", null, null);
        descargarBackup($_GET['archivo']);
    }
}

// Obtener lista de backups
$backups = listarBackups();
$backupsJSON = json_encode($backups);
$mensajeJSON = json_encode($mensaje);
$tipoJSON = json_encode($tipo);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Backups</title>

    <!-- ✅ FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <link href="../css/bulma.min.css" rel="stylesheet">
    <link href="../css/listados.css" rel="stylesheet">

    <style>
        .main-content {
            background: #2c3e50 !important;
            color: white;
        }

        .backup-card {
            background: rgba(0,0,0,0.2);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 15px;
            border: 2px solid rgba(241, 196, 15, 0.3);
            transition: all 0.3s ease;
        }

        .backup-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(241, 196, 15, 0.2);
        }

        .backup-manual i {
            color: #2ecc71;
        }

        .backup-automatico i {
            color: #3498db;
        }

        .backup-detalles i {
            color: #f1c40f;
        }

        .btn-generar {
            background: linear-gradient(45deg, #27ae60, #2ecc71) !important;
            color: white !important;
            font-weight: bold !important;
            padding: 15px 30px !important;
            border-radius: 8px !important;
            border: none !important;
            cursor: pointer;
            font-size: 1.1rem !important;
            transition: all 0.3s ease;
            text-decoration: none !important;
            display: inline-block;
        }

        .btn-generar:hover {
            background: linear-gradient(45deg, #2ecc71, #27ae60) !important;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(39, 174, 96, 0.4);
        }

        .btn-descargar {
            background: linear-gradient(45deg, #3498db, #2980b9);
            color: white !important;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none !important;
            display: inline-block;
            font-size: 0.9rem;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .btn-descargar:hover {
            background: linear-gradient(45deg, #2980b9, #3498db);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.4);
        }

        .mensaje {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: bold;
            text-align: center;
        }

        .mensaje.success {
            background: rgba(39, 174, 96, 0.2);
            border: 2px solid #27ae60;
            color: #2ecc71;
        }

        .mensaje.error {
            background: rgba(231, 76, 60, 0.2);
            border: 2px solid #e74c3c;
            color: #e74c3c;
        }

        .backup-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .backup-info {
            flex: 1;
            min-width: 250px;
        }

        .backup-nombre {
            color: #f1c40f;
            font-size: 1.1rem;
            font-weight: bold;
            margin-bottom: 8px;
            word-break: break-all;
        }

        .backup-detalles {
            font-size: 0.85rem;
            color: rgba(255,255,255,0.8);
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }

        .backup-actions {
            display: flex;
            align-items: center;
        }

        .info-box {
            background: rgba(52, 152, 219, 0.1);
            border: 2px solid rgba(52, 152, 219, 0.3);
            padding: 20px;
            border-radius: 10px;
            margin-top: 30px;
        }

        .info-box strong {
            color: #3498db;
            font-size: 1.1rem;
        }

        .info-box ul {
            margin-top: 15px;
            margin-left: 20px;
            color: rgba(255,255,255,0.8);
            line-height: 1.8;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: rgba(255,255,255,0.6);
            font-size: 1.1rem;
            background: rgba(0,0,0,0.2);
            border-radius: 10px;
            border: 2px dashed rgba(241, 196, 15, 0.3);
        }

        .empty-state-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
            color: #f1c40f;
        }
    </style>
</head>
<body>
    <?php include '../menu.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mainContent = document.querySelector('.main-content');
            if (!mainContent) return;

            const backups = <?php echo $backupsJSON; ?>;
            const mensaje = <?php echo $mensajeJSON; ?>;
            const tipo = <?php echo $tipoJSON; ?>;

            let mensajeHTML = '';
            if (mensaje) {
                mensajeHTML = `<div class="mensaje ${tipo}">${mensaje}</div>`;
            }

            let backupsHTML = '';
            if (backups.length === 0) {
                backupsHTML = `
                    <div class="empty-state">
                        <div class="empty-state-icon"><i class="fa-solid fa-box-open"></i></div>
                        <p>No hay backups disponibles.</p>
                        <p style="margin-top: 10px; font-size: 0.9rem;">Genera tu primer backup usando el botón de arriba.</p>
                    </div>
                `;
            } else {
                backups.forEach(backup => {
                    const tipoClass = backup.tipo === 'Manual' ? 'backup-manual' : 'backup-automatico';
                    const icono = backup.tipo === 'Manual'
                        ? '<i class="fa-solid fa-wrench"></i>'
                        : '<i class="fa-solid fa-gear"></i>';

                    backupsHTML += `
                        <div class="backup-card ${tipoClass}">
                            <div class="backup-header">
                                <div class="backup-info">
                                    <div class="backup-nombre">
                                        ${icono} ${backup.nombre}
                                    </div>
                                    <div class="backup-detalles">
                                        <span><i class="fa-regular fa-calendar"></i> ${backup.fecha}</span>
                                        <span><i class="fa-solid fa-database"></i> ${backup.tamano_legible}</span>
                                        <span><i class="fa-solid fa-tag"></i> ${backup.tipo}</span>
                                    </div>
                                </div>
                                <div class="backup-actions">
                                    <a href="?accion=descargar&archivo=${encodeURIComponent(backup.nombre)}"
                                       class="btn-descargar">
                                        ⬇️ Descargar
                                    </a>
                                </div>
                            </div>
                        </div>
                    `;
                });
            }

            const contentHTML = `
                <div class="list-container">
                    <h1 class="list-title"><i class="fa-solid fa-database" style="color:#f1c40f;"></i> Panel de Backups</h1>

                    ${mensajeHTML}

                    <div style="text-align: center; margin-bottom: 30px;">
                        <a href="?accion=generar" class="btn-generar"
                           onclick="return confirm('¿Generar backup manual ahora?\\n\\nEsto puede tardar unos segundos.')">
                            Generar Backup Manual
                        </a>
                    </div>

                    <h2 style="color: #f1c40f; margin-bottom: 20px; font-size: 1.3rem;">
                        <i class="fa-solid fa-box-archive"></i> Backups Disponibles (${backups.length})
                    </h2>

                    ${backupsHTML}

                    <div class="info-box">
                        <strong><i class="fa-solid fa-circle-info"></i> Información sobre Backups</strong>
                        <ul>
                            <li>Los backups automáticos se generan cada 24 horas al iniciar sesión</li>
                            <li>Se mantienen los últimos <?php echo MAX_BACKUPS; ?> backups automáticamente</li>
                            <li>Los backups más antiguos se eliminan automáticamente</li>
                            <li>Los backups manuales tienen prioridad sobre los automáticos</li>
                            <li>Recomendamos descargar backups importantes antes de actualizaciones</li>
                        </ul>
                    </div>

                    <div style="text-align: center; margin-top: 25px;">
                        <a href="../index.php" class="secondary-button">
                            <i class="fa-solid fa-house"></i> Volver al Inicio
                        </a>
                    </div>
                </div>
            `;

            mainContent.innerHTML = contentHTML;
        });
    </script>
</body>
</html>
