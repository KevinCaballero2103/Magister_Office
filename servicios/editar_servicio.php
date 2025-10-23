<?php
$mensaje = "";
$tipo = "";
$titulo = "";

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        include_once "../db.php";

        $id = $_POST["id"];
        $nombre_servicio = strtoupper(trim($_POST["nombre_servicio"]));
        $categoria_servicio = strtoupper(trim($_POST["categoria_servicio"]));
        $estado_servicio = $_POST["estado_servicio"];
        $precio_sugerido = isset($_POST["precio_sugerido"]) && $_POST["precio_sugerido"] !== '' ? floatval($_POST["precio_sugerido"]) : 0.00;

        $sentencia = $conexion->prepare("UPDATE servicios SET nombre_servicio = ?, categoria_servicio = ?, precio_sugerido = ?, estado_servicio = ? WHERE id = ?;");
        $resultado = $sentencia->execute([$nombre_servicio, $categoria_servicio, $precio_sugerido, $estado_servicio, $id]);

        if ($resultado === TRUE) {
            $titulo = "✅ Servicio Actualizado Correctamente";
            $precioInfo = $precio_sugerido > 0 ? " con precio sugerido de ₲ " . number_format($precio_sugerido, 0, ',', '.') : "";
            $mensaje = "Los datos del servicio <strong>$nombre_servicio</strong> de la categoría <strong>$categoria_servicio</strong>$precioInfo han sido actualizados exitosamente.";
            $tipo = "success";
        } else {
            $titulo = "❌ Error al Actualizar Servicio";
            $mensaje = "No se pudo actualizar el servicio. Por favor, verifica los datos e intenta nuevamente.";
            $tipo = "error";
        }
    }
} catch (Exception $e) {
    $titulo = "❌ Error del Sistema";
    $mensaje = "Ocurrió un error inesperado: " . $e->getMessage();
    $tipo = "error";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($titulo); ?></title>
    <link href="../css/bulma.min.css" rel="stylesheet">
    <link href="../css/mensajes.css" rel="stylesheet">
    <style>
        .main-content {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
    </style>
</head>
<body>
    <?php include '../menu.php'; ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var mainContent = document.querySelector('.main-content');
            var tipo = '<?php echo $tipo; ?>';
            var titulo = '<?php echo addslashes($titulo); ?>';
            var mensaje = '<?php echo addslashes($mensaje); ?>';
            
            var icono = tipo === 'success' ? '🔧✅' : '❌';
            var claseIcono = tipo === 'success' ? 'success-icon' : 'error-icon';
            
            var contentHTML = '<div class="message-container">' +
                '<span class="status-icon ' + claseIcono + '">' + icono + '</span>' +
                '<h1 class="message-title">' + titulo + '</h1>' +
                '<div class="message-content">' + mensaje + '</div>' +
                '<div class="button-group">' +
                '<a href="./listado_servicio.php" class="action-button">📋 Ver Listado de Servicios</a>' +
                '<a href="./frm_guardar_servicio.php" class="secondary-button">➕ Registrar Nuevo Servicio</a>' +
                '</div>' +
                '</div>';
            
            mainContent.innerHTML = contentHTML;
        });
    </script>
</body>
</html>