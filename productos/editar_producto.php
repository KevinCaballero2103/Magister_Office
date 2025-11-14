<?php
include_once __DIR__ . "/../auth.php";
$mensaje = "";
$tipo = "";
$titulo = "";

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        include_once "../db.php";
        
        // Obtener usuario actual
        $usuarioActual = getUsuarioActual();
        
        // Iniciar transacción
        $conexion->beginTransaction();
        
        $id = $_POST["id"];
        $nombre_producto = strtoupper(trim($_POST["nombre_producto"]));
        $codigo_producto = trim($_POST["codigo_producto"]);
        $precio_venta = $_POST["precio_venta"];
        $stock_actual = $_POST["stock_actual"];
        $stock_minimo = $_POST["stock_minimo"];
        $estado_producto = $_POST["estado_producto"];
        $razon_cambio = trim($_POST["razon_cambio"]); // NUEVO
        $proveedores = isset($_POST["proveedores"]) ? $_POST["proveedores"] : array();
        
        // NUEVO: Obtener datos anteriores para auditoría
        $sentenciaAnterior = $conexion->prepare("SELECT * FROM productos WHERE id = ?");
        $sentenciaAnterior->execute([$id]);
        $datosAnteriores = $sentenciaAnterior->fetch(PDO::FETCH_ASSOC);
        
        if (!$datosAnteriores) {
            throw new Exception("El producto no existe");
        }
        
        // Actualizar datos del producto con usuario de modificación
        $sentencia = $conexion->prepare("UPDATE productos SET 
            nombre_producto=?, 
            codigo_producto=?, 
            precio_venta=?, 
            stock_actual=?, 
            stock_minimo=?, 
            estado_producto=?,
            usuario_modificacion=?,
            fecha_modificacion=NOW()
            WHERE id = ?");
        
        $resultado = $sentencia->execute([
            $nombre_producto, 
            $codigo_producto, 
            $precio_venta, 
            $stock_actual, 
            $stock_minimo, 
            $estado_producto,
            $usuarioActual['nombre'],
            $id
        ]);
        
        if ($resultado === TRUE) {
            // NUEVO: Preparar datos nuevos para auditoría
            $datosNuevos = [
                'nombre_producto' => $nombre_producto,
                'codigo_producto' => $codigo_producto,
                'precio_venta' => $precio_venta,
                'stock_actual' => $stock_actual,
                'stock_minimo' => $stock_minimo,
                'estado_producto' => $estado_producto
            ];
            
            // NUEVO: Detectar cambios específicos
            $cambios = [];
            if ($datosAnteriores['nombre_producto'] != $nombre_producto) {
                $cambios[] = "Nombre: '{$datosAnteriores['nombre_producto']}' → '$nombre_producto'";
            }
            if ($datosAnteriores['precio_venta'] != $precio_venta) {
                $cambios[] = "Precio: ₲ {$datosAnteriores['precio_venta']} → ₲ $precio_venta";
            }
            if ($datosAnteriores['stock_actual'] != $stock_actual) {
                $cambios[] = "Stock: {$datosAnteriores['stock_actual']} → $stock_actual";
            }
            if ($datosAnteriores['stock_minimo'] != $stock_minimo) {
                $cambios[] = "Stock mínimo: {$datosAnteriores['stock_minimo']} → $stock_minimo";
            }
            if ($datosAnteriores['estado_producto'] != $estado_producto) {
                $estadoAntes = $datosAnteriores['estado_producto'] == 1 ? 'Activo' : 'Inactivo';
                $estadoDespues = $estado_producto == 1 ? 'Activo' : 'Inactivo';
                $cambios[] = "Estado: $estadoAntes → $estadoDespues";
            }
            
            $descripcionCambios = !empty($cambios) ? implode(', ', $cambios) : 'Sin cambios detectados';
            
            // NUEVO: Registrar en log de actividades con razón del cambio
            registrarActividad(
                'EDITAR',
                'PRODUCTOS',
                "Producto editado: $nombre_producto (ID: $id) - Cambios: $descripcionCambios - Razón: $razon_cambio",
                $datosAnteriores,
                $datosNuevos
            );
            
            // Eliminar relaciones proveedor-producto existentes
            $sentenciaEliminar = $conexion->prepare("DELETE FROM proveedor_producto WHERE id_producto = ?");
            $sentenciaEliminar->execute([$id]);
            
            // Insertar nuevas relaciones con proveedores y precios
            if (!empty($proveedores)) {
                $sentenciaRelacion = $conexion->prepare("INSERT INTO proveedor_producto (id_proveedor, id_producto, precio_compra) VALUES (?, ?, ?)");
                
                foreach ($proveedores as $proveedor_data) {
                    if (isset($proveedor_data['id']) && is_numeric($proveedor_data['id'])) {
                        $precio_compra = isset($proveedor_data['precio']) && is_numeric($proveedor_data['precio']) ? $proveedor_data['precio'] : 0.00;
                        $sentenciaRelacion->execute([$proveedor_data['id'], $id, $precio_compra]);
                    }
                }
            }
            
            // Confirmar transacción
            $conexion->commit();
            
            $titulo = "✅ Producto Actualizado Correctamente";
            $cantidadProveedores = count($proveedores);
            
            $mensaje = "El producto <strong>$nombre_producto</strong> ha sido actualizado exitosamente por <strong>{$usuarioActual['nombre']}</strong>.<br><br>";
            $mensaje .= "<strong>Cambios realizados:</strong><br>";
            $mensaje .= !empty($cambios) ? "• " . implode("<br>• ", $cambios) : "• Sin cambios en los datos principales";
            $mensaje .= "<br><br><strong>Razón del cambio:</strong><br>$razon_cambio<br><br>";
            
            if ($cantidadProveedores > 0) {
                $mensaje .= "Proveedores asociados: <strong>$cantidadProveedores</strong>";
            } else {
                $mensaje .= "Sin proveedores asociados actualmente";
            }
            
            $tipo = "success";
        } else {
            $conexion->rollback();
            $titulo = "❌ Error al Actualizar Producto";
            $mensaje = "No se pudo actualizar el producto. Por favor, verifica los datos e intenta nuevamente.";
            $tipo = "error";
            
            // Registrar error
            registrarActividad('ERROR', 'PRODUCTOS', "Error al actualizar producto ID: $id", null, null);
        }

    } else {
        throw new Exception("Método de solicitud no válido");
    }
} catch (Exception $e) {
    if (isset($conexion) && $conexion->inTransaction()) {
        $conexion->rollback();
    }
    $titulo = "❌ Error del Sistema";
    $mensaje = "Ocurrió un error inesperado: " . htmlspecialchars($e->getMessage());
    $tipo = "error";
    
    // Registrar error en log
    if (isset($usuarioActual)) {
        registrarActividad('ERROR', 'PRODUCTOS', "Error al editar producto: " . $e->getMessage(), null, null);
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
            
            const icono = tipo === 'success' ? '✅' : '❌';
            const claseIcono = tipo === 'success' ? 'success-icon' : 'error-icon';
            
            const contentHTML = `
                <div class='message-container'>
                    <span class='status-icon \${claseIcono}'>\${icono}</span>
                    
                    <h1 class='message-title'>\${titulo}</h1>
                    
                    <div class='message-content'>
                        \${mensaje}
                    </div>
                    
                    <div class='button-group'>
                        <a href='./listado_producto.php' class='action-button'>
                            📋 Ver Listado de Productos
                        </a>
                        
                        <a href='./frm_guardar_producto.php' class='secondary-button'>
                            ➕ Registrar Nuevo Producto
                        </a>
                    </div>
                </div>
            `;
            
            mainContent.innerHTML = contentHTML;
        });
    </script>
</body>
</html>