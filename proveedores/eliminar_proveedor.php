<?php
// Variables para JavaScript
$mensaje = "";
$tipo = "";
$titulo = "";

// Validar que se proporcione el ID
if (!isset($_GET["id"])) {
    $titulo = "Error de Solicitud";
    $mensaje = "No se proporcionó el ID del proveedor a eliminar.";
    $tipo = "error";
} else {
    $id = $_GET["id"];
    
    try {
        include_once "../db.php";
        
        // Iniciar transacción
        $conexion->beginTransaction();
        
        // Verificar que el proveedor existe y obtener su información
        $sentenciaVerificar = $conexion->prepare("SELECT nombre_proveedor FROM proveedores WHERE id = ?");
        $sentenciaVerificar->execute([$id]);
        $proveedor = $sentenciaVerificar->fetch(PDO::FETCH_OBJ);
        
        if ($proveedor === FALSE) {
            $titulo = "Proveedor No Encontrado";
            $mensaje = "El proveedor que intentas eliminar no existe en el sistema.";
            $tipo = "error";
        } else {
            $nombreProveedor = $proveedor->nombre_proveedor;
            
            // Verificar si tiene productos asociados
            $sentenciaProductos = $conexion->prepare("SELECT COUNT(*) as total FROM proveedor_producto WHERE id_proveedor = ?");
            $sentenciaProductos->execute([$id]);
            $totalProductos = $sentenciaProductos->fetch(PDO::FETCH_OBJ)->total;
            
            // Eliminar primero las relaciones con productos (si existen)
            if ($totalProductos > 0) {
                $sentenciaEliminarRelaciones = $conexion->prepare("DELETE FROM proveedor_producto WHERE id_proveedor = ?");
                $sentenciaEliminarRelaciones->execute([$id]);
            }
            
            // Eliminar el proveedor
            $sentenciaEliminar = $conexion->prepare("DELETE FROM proveedores WHERE id = ?");
            $resultado = $sentenciaEliminar->execute([$id]);
            
            if ($resultado === TRUE) {
                // Confirmar transacción
                $conexion->commit();
                
                $titulo = "Proveedor Eliminado Correctamente";
                if ($totalProductos > 0) {
                    $mensaje = "El proveedor <strong>$nombreProveedor</strong> y sus <strong>$totalProductos</strong> producto(s) asociado(s) han sido eliminados exitosamente del sistema.";
                } else {
                    $mensaje = "El proveedor <strong>$nombreProveedor</strong> ha sido eliminado exitosamente del sistema.";
                }
                $tipo = "success";
            } else {
                $conexion->rollback();
                $titulo = "Error al Eliminar Proveedor";
                $mensaje = "No se pudo eliminar el proveedor <strong>$nombreProveedor</strong>. Por favor, intenta nuevamente.";
                $tipo = "error";
            }
        }
    } catch (Exception $e) {
        if (isset($conexion) && $conexion->inTransaction()) {
            $conexion->rollback();
        }
        $titulo = "Error del Sistema";
        $mensaje = "Ocurrió un error inesperado: " . $e->getMessage();
        $tipo = "error";
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
            const icono = tipo === 'success' ? '🗑️✅' : '❌';
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