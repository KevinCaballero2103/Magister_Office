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

        // Recibir datos
        $tipo_movimiento = isset($_POST['tipo_movimiento']) ? $_POST['tipo_movimiento'] : null;
        $categoria = isset($_POST['categoria']) ? $_POST['categoria'] : null;
        $concepto = isset($_POST['concepto']) ? trim($_POST['concepto']) : null;
        $monto = isset($_POST['monto']) ? floatval($_POST['monto']) : 0;
        $fecha_movimiento = isset($_POST['fecha_movimiento']) ? $_POST['fecha_movimiento'] : null;
        $observaciones = isset($_POST['observaciones']) ? trim($_POST['observaciones']) : null;

        // Convertir fecha/hora a zona horaria de Paraguay
        if (!empty($fecha_movimiento)) {
            try {
                $dt = new DateTime($fecha_movimiento, new DateTimeZone('UTC'));
                $dt->setTimezone(new DateTimeZone('America/Asuncion'));
                $fecha_movimiento = $dt->format('Y-m-d H:i:s');
            } catch (Exception $e) {
                $dt = new DateTime('now', new DateTimeZone('America/Asuncion'));
                $fecha_movimiento = $dt->format('Y-m-d H:i:s');
            }
        }

        // Validaciones
        if (!in_array($tipo_movimiento, ['INGRESO', 'EGRESO'])) {
            throw new Exception("Tipo de movimiento inválido");
        }

        if (!in_array($categoria, ['VENTA', 'COMPRA', 'OTRO'])) {
            throw new Exception("Categoría inválida");
        }

        // NO permitir registrar manualmente VENTA o COMPRA
        if ($categoria !== 'OTRO') {
            throw new Exception("Solo puedes registrar movimientos de categoría OTRO. Las ventas y compras se registran automáticamente.");
        }

        if (empty($concepto)) {
            throw new Exception("El concepto es obligatorio");
        }

        if ($monto <= 0) {
            throw new Exception("El monto debe ser mayor a 0");
        }

        if (empty($fecha_movimiento)) {
            throw new Exception("La fecha del movimiento es obligatoria");
        }

        // Insertar movimiento con usuario
        $sentencia = $conexion->prepare("
            INSERT INTO caja (tipo_movimiento, categoria, concepto, monto, fecha_movimiento, observaciones, usuario_registro) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $resultado = $sentencia->execute([
            $tipo_movimiento,
            $categoria,
            $concepto,
            $monto,
            $fecha_movimiento,
            $observaciones,
            $usuarioActual['nombre']  // REGISTRAR USUARIO
        ]);

        if ($resultado === TRUE) {
            $id_movimiento = $conexion->lastInsertId();
            
            // REGISTRAR EN LOG DE ACTIVIDADES
            registrarActividad(
                'CREAR',
                'CAJA',
                "Movimiento de caja registrado: $tipo_movimiento - $concepto (ID: $id_movimiento)",
                null,
                [
                    'id_movimiento' => $id_movimiento,
                    'tipo' => $tipo_movimiento,
                    'categoria' => $categoria,
                    'concepto' => $concepto,
                    'monto' => $monto,
                    'fecha' => $fecha_movimiento
                ]
            );
            
            $titulo = "✅ Movimiento Registrado Exitosamente";
            $icono = $tipo_movimiento === 'INGRESO' ? '💰' : '💸';
            $mensaje = "El movimiento ha sido registrado correctamente en caja por <strong>{$usuarioActual['nombre']}</strong>.<br><br>
                        <strong>Detalles:</strong><br>
                        • ID: <strong>#$id_movimiento</strong><br>
                        • Tipo: <strong>$icono $tipo_movimiento</strong><br>
                        • Concepto: <strong>$concepto</strong><br>
                        • Monto: <strong>₲ " . number_format($monto, 0, ',', '.') . "</strong><br>
                        • Fecha: <strong>" . date('d/m/Y H:i', strtotime($fecha_movimiento)) . "</strong><br>
                        • Registrado por: <strong>{$usuarioActual['nombre']}</strong>";
            $tipo = "success";
        } else {
            throw new Exception("Error al registrar el movimiento en la base de datos");
        }

    } else {
        throw new Exception("Método de solicitud no válido");
    }
} catch (Exception $e) {
    error_log("guardar_movimiento.php - ERROR: " . $e->getMessage());
    $titulo = "❌ Error al Registrar Movimiento";
    $mensaje = "No se pudo completar el registro:<br><br>" . htmlspecialchars($e->getMessage());
    $tipo = "error";
    
    // Registrar error en log
    if (isset($usuarioActual)) {
        registrarActividad('ERROR', 'CAJA', "Error al registrar movimiento: " . $e->getMessage(), null, null);
    }
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
        body { background: #2c3e50 !important; }
        .main-content {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
            background: #2c3e50 !important;
        }
        .message-container {
            max-width: 700px;
            width: 100%;
            background: rgba(44, 62, 80, 0.95);
            padding: 28px;
            border-radius: 10px;
            color: white;
            text-align: center;
        }
        .message-title {
            margin-top: 10px;
            margin-bottom: 12px;
            font-size: 1.6rem;
            color: #f1c40f;
        }
        .message-content {
            margin-bottom: 18px;
            text-align: left;
            line-height: 1.6;
        }
        .status-icon {
            font-size: 2.4rem;
            display: inline-block;
            margin-bottom: 6px;
        }
        .button-group {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin-top: 12px;
            flex-wrap: wrap;
        }
        .action-button {
            padding: 10px 18px;
            border-radius: 8px;
            text-decoration: none;
            color: #2c3e50;
            background: linear-gradient(45deg, #f39c12, #f1c40f);
            font-weight: bold;
            border: none;
            cursor: pointer;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            display: inline-block;
        }
        .action-button:hover {
            background: linear-gradient(45deg, #e67e22, #f39c12);
            transform: translateY(-2px);
        }
        .secondary-button {
            padding: 10px 18px;
            border-radius: 8px;
            text-decoration: none;
            background: rgba(236, 240, 241, 0.1);
            color: white;
            border: 2px solid rgba(241, 196, 15, 0.2);
            font-weight: bold;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            display: inline-block;
        }
        .secondary-button:hover {
            background: rgba(241, 196, 15, 0.15);
            border-color: #f1c40f;
            color: white;
        }
    </style>
</head>
<body>
    <?php include '../menu.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var mainContent = document.querySelector('.main-content');
            if (!mainContent) {
                mainContent = document.createElement('div');
                mainContent.className = 'main-content';
                document.body.appendChild(mainContent);
            }

            var tipo = <?php echo json_encode($tipo); ?>;
            var titulo = <?php echo json_encode($titulo); ?>;
            var mensaje = <?php echo json_encode($mensaje); ?>;

            var icono = tipo === 'success' ? '💰✅' : '❌';
            var claseIcono = tipo === 'success' ? 'success-icon' : 'error-icon';

            var botonesHTML = "";
            
            if (tipo === 'success') {
                botonesHTML += "<a href='./balance.php' class='action-button'>📊 Ver Balance de Caja</a>";
                botonesHTML += "<a href='./registrar_movimiento.php' class='secondary-button'>➕ Registrar Otro Movimiento</a>";
                botonesHTML += "<a href='./historial_movimientos.php' class='secondary-button'>📋 Ver Historial</a>";
            } else {
                botonesHTML += "<a href='./registrar_movimiento.php' class='secondary-button'>⬅️ Volver al Formulario</a>";
                botonesHTML += "<a href='./balance.php' class='action-button'>📊 Ver Balance</a>";
            }

            var contentHTML = ""
                + "<div class='message-container'>"
                + "  <span class='status-icon " + claseIcono + "'>" + icono + "</span>"
                + "  <h1 class='message-title'>" + titulo + "</h1>"
                + "  <div class='message-content'>" + mensaje + "</div>"
                + "  <div class='button-group'>"
                + botonesHTML
                + "  </div>"
                + "</div>";

            mainContent.innerHTML = contentHTML;
        });
    </script>
</body>
</html>