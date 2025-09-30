<?php
// Variables para JavaScript
$mensaje = "";
$tipo = "";
$titulo = "";

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        include_once "../db.php";
        
        // Iniciar transacción
        $conexion->beginTransaction();
        
        $id = $_POST["id"];
        $nombre_proveedor = strtoupper(trim($_POST["nombre_proveedor"]));
        $telefono_proveedor = trim($_POST["telefono_proveedor"]);
        $direccion_proveedor = strtoupper(trim($_POST["direccion_proveedor"]));
        $estado_proveedor = $_POST["estado_proveedor"];
        $productos = isset($_POST["productos"]) ? $_POST["productos"] : array();
        
        // Actualizar datos del proveedor
        $sentencia = $conexion->prepare("UPDATE proveedores SET nombre_proveedor=?, telefono_proveedor=?, direccion_proveedor=?, estado_proveedor=? WHERE id = ?");
        $resultado = $sentencia->execute([$nombre_proveedor, $telefono_proveedor, $direccion_proveedor, $estado_proveedor, $id]);
        
        if ($resultado === TRUE) {
            // Eliminar productos asociados existentes
            $sentenciaEliminar = $conexion->prepare("DELETE FROM proveedor_producto WHERE id_proveedor = ?");
            $sentenciaEliminar->execute([$id]);
            
            // Insertar nuevas relaciones con productos
            if (!empty($productos)) {
                $sentenciaRelacion = $conexion->prepare("INSERT INTO proveedor_producto (id_proveedor, id_producto) VALUES (?, ?)");
                
                foreach ($productos as $producto_id) {
                    if (is_numeric($producto_id)) {
                        $sentenciaRelacion->execute([$id, $producto_id]);
                    }
                }
            }
            
            // Confirmar transacción
            $conexion->commit();
            
            $titulo = "Proveedor Actualizado Correctamente";
            $cantidadProductos = count($productos);
            if ($cantidadProductos > 0) {
                $mensaje = "Los datos del proveedor <strong>$nombre_proveedor</strong> han sido actualizados exitosamente con <strong>$cantidadProductos</strong> producto(s) asociado(s).";
            } else {
                $mensaje = "Los datos del proveedor <strong>$nombre_proveedor</strong> han sido actualizados exitosamente. No tiene productos asociados actualmente.";
            }
            $tipo = "success";
        } else {
            $conexion->rollback();
            $titulo = "Error al Actualizar Proveedor";
            $mensaje = "No se pudo actualizar el proveedor. Por favor, verifica los datos e intenta nuevamente.";
            $tipo = "error";
        }
    }
} catch (Exception $e) {
    if ($conexion->inTransaction()) {
        $conexion->rollback();
    }
    $titulo = "Error del Sistema";
    $mensaje = "Ocurrió un error inesperado: " . $e->getMessage();
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
    <style>
        /* Override del fondo principal */
        .main-content {
            background: #2c3e50 !important;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        /* Container del mensaje */
        .message-container {
            background: linear-gradient(135deg, #34495e 0%, #2c3e50 100%);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.4);
            text-align: center;
            max-width: 600px;
            margin: 20px;
            animation: slideIn 0.6s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Iconos de estado */
        .status-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            display: block;
        }

        .success-icon {
            color: #27ae60;
        }

        .error-icon {
            color: #e74c3c;
        }

        /* Títulos */
        .message-title {
            color: #f1c40f;
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        /* Contenido del mensaje */
        .message-content {
            color: #ecf0f1;
            font-size: 1.2rem;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        /* Botones de acción */
        .button-group {
            text-align: center;
            margin-top: 20px;
        }

        .action-button {
            background: linear-gradient(45deg, #f39c12, #f1c40f) !important;
            border: none !important;
            color: #2c3e50 !important;
            font-weight: bold !important;
            padding: 15px 30px !important;
            border-radius: 10px !important;
            font-size: 1.1rem !important;
            text-decoration: none !important;
            display: inline-block !important;
            transition: all 0.3s ease !important;
            box-shadow: 0 5px 15px rgba(243, 156, 18, 0.3);
            margin: 10px 15px !important;
        }

        .action-button:hover {
            background: linear-gradient(45deg, #e67e22, #f39c12) !important;
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(243, 156, 18, 0.5) !important;
            color: #2c3e50 !important;
        }

        .action-button:active {
            transform: translateY(-1px);
        }

        /* Botón secundario */
        .secondary-button {
            background: transparent !important;
            border: 2px solid #f1c40f !important;
            color: #f1c40f !important;
            font-weight: bold !important;
            padding: 12px 25px !important;
            border-radius: 10px !important;
            text-decoration: none !important;
            display: inline-block !important;
            margin: 10px 15px !important;
            transition: all 0.3s ease !important;
        }

        .secondary-button:hover {
            background: rgba(241, 196, 15, 0.1) !important;
            color: #f39c12 !important;
            border-color: #f39c12 !important;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .message-container {
                margin: 10px;
                padding: 30px 20px;
            }
            
            .message-title {
                font-size: 1.5rem;
            }
            
            .message-content {
                font-size: 1rem;
            }
            
            .action-button, .secondary-button {
                display: block !important;
                margin: 10px auto !important;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <?php include '../menu.php'; ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mainContent = document.querySelector('.main-content');
            const tipo = '<?php echo $tipo; ?>';
            const titulo = '<?php echo addslashes($titulo); ?>';
            const mensaje = '<?php echo addslashes($mensaje); ?>';
            
            // Determinar icono según el tipo
            const icono = tipo === 'success' ? '✏️✅' : '❌';
            const claseIcono = tipo === 'success' ? 'success-icon' : 'error-icon';
            
            const contentHTML = `
                <div class='message-container'>
                    <span class='status-icon ${claseIcono}'>${icono}</span>
                    
                    <h1 class='message-title'>${titulo}</h1>
                    
                    <div class='message-content'>
                        ${mensaje}
                    </div>
                    
                    <div class='button-group'>
                        <a href='./listado_proveedor.php' class='action-button'>
                            📋 Ver Listado de Proveedores
                        </a>
                        
                        <a href='./frm_guardar_proveedor.php' class='secondary-button'>
                            ➕ Registrar Nuevo Proveedor
                        </a>
                    </div>
                </div>
            `;
            
            mainContent.innerHTML = contentHTML;
        });
    </script>
</body>
</html>