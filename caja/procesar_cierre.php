<?php
$mensaje = "";
$tipo = "";
$titulo = "";

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        include_once "../db.php";

        $id_cierre = intval($_POST['id_cierre'] ?? 0);
        $fecha_cierre = $_POST['fecha_cierre'] ?? null;
        $saldo_sistema = floatval($_POST['saldo_sistema'] ?? 0);
        $saldo_fisico = floatval($_POST['saldo_fisico'] ?? 0);
        $total_ingresos = floatval($_POST['total_ingresos'] ?? 0);
        $total_egresos = floatval($_POST['total_egresos'] ?? 0);
        $usuario_cierre = trim($_POST['usuario_cierre'] ?? '');
        $observaciones_cierre = trim($_POST['observaciones_cierre'] ?? '') ?: null;

        // Convertir fecha a zona horaria Paraguay
        if (!empty($fecha_cierre)) {
            try {
                $dt = new DateTime($fecha_cierre, new DateTimeZone('UTC'));
                $dt->setTimezone(new DateTimeZone('America/Asuncion'));
                $fecha_cierre = $dt->format('Y-m-d H:i:s');
            } catch (Exception $e) {
                $dt = new DateTime('now', new DateTimeZone('America/Asuncion'));
                $fecha_cierre = $dt->format('Y-m-d H:i:s');
            }
        }

        // Verificar que la caja existe y está abierta
        $sentenciaVerificar = $conexion->prepare("SELECT * FROM cierres_caja WHERE id = ? AND estado = 'ABIERTA'");
        $sentenciaVerificar->execute([$id_cierre]);
        $caja = $sentenciaVerificar->fetch(PDO::FETCH_OBJ);

        if (!$caja) {
            throw new Exception("No se encontró una caja abierta con ese ID");
        }

        if (empty($usuario_cierre)) {
            throw new Exception("El nombre del usuario que cierra es obligatorio");
        }

        // Calcular diferencia
        $diferencia = $saldo_fisico - $saldo_sistema;
        $saldo_final = $saldo_fisico;

        // Actualizar cierre de caja
        $sentencia = $conexion->prepare("
            UPDATE cierres_caja SET
                fecha_cierre = ?,
                saldo_final = ?,
                total_ingresos = ?,
                total_egresos = ?,
                saldo_sistema = ?,
                saldo_fisico = ?,
                diferencia = ?,
                usuario_cierre = ?,
                observaciones_cierre = ?,
                estado = 'CERRADA'
            WHERE id = ?
        ");

        $resultado = $sentencia->execute([
            $fecha_cierre,
            $saldo_final,
            $total_ingresos,
            $total_egresos,
            $saldo_sistema,
            $saldo_fisico,
            $diferencia,
            $usuario_cierre,
            $observaciones_cierre,
            $id_cierre
        ]);

        if ($resultado) {
            $titulo = "✅ Caja Cerrada Exitosamente";
            
            $diferenciaTexto = '';
            if ($diferencia == 0) {
                $diferenciaTexto = '<span style="color: #2ecc71;">✅ SIN DIFERENCIAS - Cuadre perfecto</span>';
            } elseif ($diferencia > 0) {
                $diferenciaTexto = '<span style="color: #3498db;">💰 SOBRANTE de ₲ ' . number_format(abs($diferencia), 0, ',', '.') . '</span>';
            } else {
                $diferenciaTexto = '<span style="color: #e74c3c;">⚠️ FALTANTE de ₲ ' . number_format(abs($diferencia), 0, ',', '.') . '</span>';
            }

            $mensaje = "La caja ha sido cerrada correctamente.<br><br>
                        <strong>Resumen del Cierre:</strong><br>
                        • ID Cierre: <strong>#$id_cierre</strong><br>
                        • Usuario Cierre: <strong>$usuario_cierre</strong><br>
                        • Fecha/Hora: <strong>" . date('d/m/Y H:i', strtotime($fecha_cierre)) . "</strong><br><br>
                        <strong>Movimientos:</strong><br>
                        • Saldo Inicial: <strong>₲ " . number_format($caja->saldo_inicial, 0, ',', '.') . "</strong><br>
                        • Total Ingresos: <strong style='color: #2ecc71;'>₲ " . number_format($total_ingresos, 0, ',', '.') . "</strong><br>
                        • Total Egresos: <strong style='color: #e74c3c;'>₲ " . number_format($total_egresos, 0, ',', '.') . "</strong><br><br>
                        <strong>Cierre:</strong><br>
                        • Saldo Sistema: <strong>₲ " . number_format($saldo_sistema, 0, ',', '.') . "</strong><br>
                        • Saldo Físico: <strong>₲ " . number_format($saldo_fisico, 0, ',', '.') . "</strong><br>
                        • Diferencia: $diferenciaTexto";
            $tipo = "success";
        } else {
            throw new Exception("Error al cerrar la caja");
        }
    } else {
        throw new Exception("Método inválido");
    }
} catch (Exception $e) {
    $titulo = "❌ Error al Cerrar Caja";
    $mensaje = htmlspecialchars($e->getMessage());
    $tipo = "error";
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
        .message-container { max-width: 800px; }
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

            const icono = tipo === 'success' ? '🔒✅' : '❌';
            const botones = tipo === 'success' 
                ? `<a href='../index.php' class='action-button'>Ir a la Pantalla Principal</a><a href='./balance.php' class='secondary-button'>📊 Ver Balance</a>`
                : `<a href='./cerrar_caja.php' class='secondary-button'>⬅️ Volver</a>`;

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