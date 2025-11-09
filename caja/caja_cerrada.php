<?php
include_once __DIR__ . "/../auth.php";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caja Cerrada</title>
    <link href="../css/bulma.min.css" rel="stylesheet">
    <link href="../css/mensajes.css" rel="stylesheet">
    <!-- FontAwesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        body { background: #2c3e50 !important; }
        .main-content { display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px; background: #2c3e50 !important; }
        .status-icon { font-size: 4rem; margin-bottom: 15px; }
        .action-button i, .secondary-button i { margin-right: 8px; }
    </style>
</head>
<body>
    <?php include '../menu.php'; ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mainContent = document.querySelector('.main-content');
            mainContent.innerHTML = `
                <div class='message-container'>
                    <span class='status-icon' style="color: #e74c3c;">
                        <i class="fas fa-lock"></i>
                    </span>
                    <h1 class='message-title' style="color: #e74c3c;">Caja Cerrada</h1>

                    <div class='message-content'>
                        No puedes realizar esta operación porque <strong>la caja está cerrada</strong>.<br><br>
                        Para registrar ventas, compras o movimientos de caja, primero debes abrir la caja del día.
                    </div>
                    <div class='button-group'>
                        <a href='../caja/abrir_caja.php' class='action-button is-primary'>
                            <i class="fas fa-unlock"></i> Abrir Caja
                        </a>
                        <a href='../index.php' class='secondary-button is-light'>
                            <i class="fas fa-home"></i> Volver al Inicio
                        </a>
                    </div>
                </div>
            `;
        });
    </script>
</body>
</html>
