<?php
include_once __DIR__ . "/../auth.php";
$mensaje = "";
$tipo = "";
$titulo = "";

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        include_once "../db.php";
        
        // Intentamos obtener el usuario actual si existe la función
        $usuarioActual = function_exists('getUsuarioActual') ? getUsuarioActual() : ['nombre' => 'Sistema'];

        // Iniciar transacción
        $conexion->beginTransaction();
        
        // Datos del formulario
        $id = $_POST["id"];
        $nombre_producto = strtoupper(trim($_POST["nombre_producto"]));
        $codigo_producto = trim($_POST["codigo_producto"]);
        $precio_venta = $_POST["precio_venta"];
        $stock_actual = $_POST["stock_actual"];
        $stock_minimo = $_POST["stock_minimo"];
        $estado_producto = $_POST["estado_producto"];
        $razon_cambio = trim($_POST["razon_cambio"]);
        $proveedores = isset($_POST["proveedores"]) ? $_POST["proveedores"] : array();

        // Obtener datos anteriores para auditoría
        $sentenciaAnterior = $conexion->prepare("SELECT * FROM productos WHERE id = ?");
        $sentenciaAnterior->execute([$id]);
        $datosAnteriores = $sentenciaAnterior->fetch(PDO::FETCH_ASSOC);

        if (!$datosAnteriores) {
            throw new Exception("El producto no existe");
        }

        // Obtener proveedores anteriores (map id => precio)
        $sqlProvAntes = $conexion->prepare("SELECT id_proveedor, precio_compra FROM proveedor_producto WHERE id_producto = ?");
        $sqlProvAntes->execute([$id]);
        $proveedoresAntes = $sqlProvAntes->fetchAll(PDO::FETCH_ASSOC);
        $mapProvAntes = [];
        foreach ($proveedoresAntes as $p) {
            $mapProvAntes[$p["id_proveedor"]] = $p["precio_compra"];
        }

        // Actualizar producto
        $sentencia = $conexion->prepare("
            UPDATE productos SET 
                nombre_producto=?, 
                codigo_producto=?, 
                precio_venta=?, 
                stock_actual=?, 
                stock_minimo=?, 
                estado_producto=?,
                usuario_modificacion=?,
                fecha_modificacion=NOW()
            WHERE id = ?
        ");
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
            // Detectar cambios en datos principales
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

            // ---- Proveedores: borrar y volver a insertar ----
            $conexion->prepare("DELETE FROM proveedor_producto WHERE id_producto = ?")->execute([$id]);

            $sqlInsert = $conexion->prepare("
                INSERT INTO proveedor_producto (id_proveedor, id_producto, precio_compra)
                VALUES (?, ?, ?)
            ");

            // Auditoría de proveedores
            $cambiosProv = [];
            foreach ($proveedores as $provKey => $provData) {
                // forma esperada: proveedores[ID][id], proveedores[ID][precio]
                if (!isset($provData['id'])) continue;
                $pID = intval($provData['id']);
                $precio = isset($provData['precio']) && $provData['precio'] !== '' ? floatval($provData['precio']) : 0.00;

                $sqlInsert->execute([$pID, $id, $precio]);

                if (!isset($mapProvAntes[$pID])) {
                    $cambiosProv[] = "Proveedor agregado ID $pID (₲ $precio)";
                } else {
                    if ($mapProvAntes[$pID] != $precio) {
                        $cambiosProv[] = "Precio proveedor ID $pID: ₲ {$mapProvAntes[$pID]} → ₲ $precio";
                    }
                    unset($mapProvAntes[$pID]);
                }
            }

            // Proveedores eliminados
            foreach ($mapProvAntes as $idProvEliminado => $precioAnterior) {
                $cambiosProv[] = "Proveedor eliminado ID $idProvEliminado";
            }

            if (!empty($cambiosProv)) {
                $cambios = array_merge($cambios, $cambiosProv);
            }

            $descripcionCambios = !empty($cambios) ? implode(', ', $cambios) : 'Sin cambios detectados';

            // Registrar auditoría si existe la función (evita fatal si no está)
            if (function_exists('registrarActividad')) {
                registrarActividad(
                    'EDITAR',
                    'PRODUCTOS',
                    "Producto editado: $nombre_producto (ID: $id) - Cambios: $descripcionCambios - Razón: $razon_cambio",
                    $datosAnteriores,
                    [
                        'nombre_producto' => $nombre_producto,
                        'codigo_producto' => $codigo_producto,
                        'precio_venta' => $precio_venta,
                        'stock_actual' => $stock_actual,
                        'stock_minimo' => $stock_minimo,
                        'estado_producto' => $estado_producto
                    ]
                );
            }

            // Confirmar transacción
            $conexion->commit();

            // Preparar mensaje visible (como en tu ejemplo que funciona)
            $titulo = "✅ Producto Actualizado Correctamente";
            $tipo = "success";

            $mensaje = "El producto <strong>$nombre_producto</strong> ha sido actualizado exitosamente por <strong>{$usuarioActual['nombre']}</strong>.<br><br>";
            if (!empty($cambios)) {
                $mensaje .= "<strong>Cambios realizados:</strong><br>• " . implode("<br>• ", $cambios) . "<br><br>";
            } else {
                $mensaje .= "<strong>Sin cambios en los datos principales</strong><br><br>";
            }
            $mensaje .= "<strong>Razón del cambio:</strong><br>" . ($razon_cambio !== "" ? htmlspecialchars($razon_cambio) : "No especificada") . "<br><br>";

        } else {
            // rollback y mensaje de error
            $conexion->rollback();
            $titulo = "❌ Error al Actualizar Producto";
            $mensaje = "No se pudo actualizar el producto. Por favor, verifica los datos e intenta nuevamente.";
            $tipo = "error";

            if (function_exists('registrarActividad')) {
                registrarActividad('ERROR', 'PRODUCTOS', "Error al actualizar producto ID: $id", null, null);
            }
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

    if (function_exists('registrarActividad')) {
        registrarActividad('ERROR', 'PRODUCTOS', "Error al editar producto: " . $e->getMessage(), null, null);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo addslashes($titulo); ?></title>
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
            padding: 20px;
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
            const tipo = '<?php echo addslashes($tipo); ?>';
            const titulo = '<?php echo addslashes($titulo); ?>';
            const mensaje = '<?php echo addslashes($mensaje); ?>';
            
            const icono = tipo === 'success' ? '✅' : '❌';
            const claseIcono = tipo === 'success' ? 'success-icon' : 'error-icon';
            
            const contentHTML = `
                <div class='message-container'>
                    <span class='status-icon ${claseIcono}'>${icono}</span>
                    
                    <h1 class='message-title'>${titulo}</h1>
                    
                    <div class='message-content'>
                        ${mensaje}
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
