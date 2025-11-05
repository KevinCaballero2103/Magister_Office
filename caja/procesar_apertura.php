<?php
include_once "../auth.php";

$mensaje = "";
$tipo = "";
$titulo = "";

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        include_once "../db.php";
        
        // Obtener usuario actual
        $usuarioActual = getUsuarioActual();

        // Verificar que no haya caja abierta
        $sentenciaVerificar = $conexion->prepare("SELECT * FROM cierres_caja WHERE estado = 'ABIERTA'");
        $sentenciaVerificar->execute();
        if ($sentenciaVerificar->fetch()) {
            throw new Exception("Ya hay una caja abierta. Debes cerrarla primero.");
        }

        $fecha_apertura = $_POST['fecha_apertura'] ?? null;
        $saldo_inicial = floatval($_POST['saldo_inicial'] ?? 0);
        $observaciones_apertura = trim($_POST['observaciones_apertura'] ?? '') ?: null;

        // Convertir fecha a zona horaria Paraguay
        if (!empty($fecha_apertura)) {
            try {
                $dt = new DateTime($fecha_apertura, new DateTimeZone('UTC'));
                $dt->setTimezone(new DateTimeZone('America/Asuncion'));
                $fecha_apertura = $dt->format('Y-m-d H:i:s');
            } catch (Exception $e) {
                $dt = new DateTime('now', new DateTimeZone('America/Asuncion'));
                $fecha_apertura = $dt->format('Y-m-d H:i:s');
            }
        }

        // Insertar apertura
        $sentencia = $conexion->prepare("
            INSERT INTO cierres_caja (fecha_apertura, saldo_inicial, usuario_apertura, observaciones_apertura, estado)
            VALUES (?, ?, ?, ?, 'ABIERTA')
        ");

        $resultado = $sentencia->execute([
            $fecha_apertura, 
            $saldo_inicial, 
            $usuarioActual['nombre'],  // USAR USUARIO ACTUAL
            $observaciones_apertura
        ]);

        if ($resultado) {
            $id_apertura = $conexion->lastInsertId();
            
            // REGISTRAR EN LOG DE ACTIVIDADES
            registrarActividad(
                'APERTURA_CAJA',
                'CAJA',
                "Apertura de caja #$id_apertura - Saldo inicial: ₲ " . number_format($saldo_inicial, 0, ',', '.'),
                null,
                [
                    'id_cierre' => $id_apertura,
                    'saldo_inicial' => $saldo_inicial,
                    'fecha_apertura' => $fecha_apertura,
                    'observaciones' => $observaciones_apertura
                ]
            );
            
            $titulo = "✅ Caja Abierta Exitosamente";
            $mensaje = "La caja ha sido abierta correctamente.<br><br>
                        <strong>Detalles:</strong><br>
                        • ID Apertura: <strong>#$id_apertura</strong><br>
                        • Usuario: <strong>{$usuarioActual['nombre']}</strong><br>
                        • Saldo Inicial: <strong>₲ " . number_format($saldo_inicial, 0, ',', '.') . "</strong><br>
                        • Fecha/Hora: <strong>" . date('d/m/Y H:i', strtotime($fecha_apertura)) . "</strong><br><br>
                        ✅ Ya puedes empezar a operar";
            $tipo = "success";
        } else {
            throw new Exception("Error al abrir la caja");
        }
    } else {
        throw new Exception("Método inválido");
    }
} catch (Exception $e) {
    $titulo = "❌ Error al Abrir Caja";
    $mensaje = htmlspecialchars($e->getMessage());
    $tipo = "error";
    
    // Registrar error en log
    if (isset($usuarioActual)) {
        registrarActividad('ERROR', 'CAJA', "Error al abrir caja: " . $e->getMessage(), null, null);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo; ?></title>
    <link href="../css/bulma.min.css" rel="stylesheet">
    <link href="../css/mensajes.css" rel="stylesheet">
    <style>
        body { background: #2c3e50 !important; }
        .main-content { display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px; background: #2c3e50 !important; }
    </style>
</head>
<body>
    <?php include '../menu.php'; ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mainContent = document.querySelector('.main-content');
            const tipo = <?php echo json_encode($tipo); ?>;
            const titulo = <?php echo json_encode($titulo); ?>;
            const mensaje = <?php echo json_encode($mensaje); ?>;

            const icono = tipo === 'success' ? '🔓✅' : '❌';
            const botones = tipo === 'success' 
                ? `<a href='../index.php' class='action-button'>Ir a la Pantalla Principal</a><a href='./balance.php' class='secondary-button'>📊 Ver Caja</a>`
                : `<a href='./abrir_caja.php' class='secondary-button'>⬅️ Volver</a>`;

            mainContent.innerHTML = `
                <div class='message-container'>
                    <span class='status-icon'>${icono}</span>
                    <h1 class='message-title'>${titulo}</h1>
                    <div class='message-content'>${mensaje}</div>
                    <div class='button-group'>${botones}</div>
                </div>
            `;
        });
    </script>
</body>
</html>