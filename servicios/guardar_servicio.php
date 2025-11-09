<?php
include_once __DIR__ . "/../auth.php";
$mensaje = "";
$tipo = "";
$titulo = "";

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        include_once "../db.php";
        
        $nombre_servicio = strtoupper(trim($_POST["nombre_servicio"]));
        $categoria_servicio = strtoupper(trim($_POST["categoria_servicio"]));
        $estado_servicio = $_POST["estado_servicio"];
        $precio_sugerido = isset($_POST["precio_sugerido"]) && $_POST["precio_sugerido"] !== '' ? floatval($_POST["precio_sugerido"]) : 0.00;

        $sentencia = $conexion->prepare("INSERT INTO servicios 
            (nombre_servicio, categoria_servicio, precio_sugerido, estado_servicio) 
            VALUES (?, ?, ?, ?);");
        
        $resultado = $sentencia->execute([
            $nombre_servicio, $categoria_servicio, $precio_sugerido, $estado_servicio
        ]);

        if ($resultado === TRUE) {
            $titulo = "✅ Servicio Registrado Exitosamente";
            $precioInfo = $precio_sugerido > 0 ? " con precio sugerido de ₲ " . number_format($precio_sugerido, 0, ',', '.') : "";
            $mensaje = "El servicio <strong>$nombre_servicio</strong> en la categoría <strong>$categoria_servicio</strong>$precioInfo ha sido registrado correctamente en el sistema.";
            $tipo = "success";
        } else {
            $titulo = "❌ Error al Registrar Servicio";
            $mensaje = "No se pudo registrar el servicio. Por favor, verifica los datos e intenta nuevamente.";
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
            background: #2c3e50 !important;
            color: white;
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
                '<a href="./frm_guardar_servicio.php" class="secondary-button">➕ Registrar Otro Servicio</a>' +
                '</div>' +
                '</div>';
            
            mainContent.innerHTML = contentHTML;
        });
    </script>
</body>
</html>