<?php
// ============================================
// FORMULARIO DE CONFIRMACIÓN DE ANULACIÓN
// Valida que la venta pueda ser anulada
// ============================================

if (!isset($_GET["id"])) {
    header("Location: listado_ventas.php?error=id_requerido");
    exit();
}

$id_venta = intval($_GET["id"]);

include_once "../db.php";

// Obtener configuración
$sentenciaConfig = $conexion->prepare("SELECT clave, valor FROM configuracion_sistema WHERE clave IN ('dias_limite_anulacion', 'permitir_anular_factura', 'requiere_motivo_anulacion')");
$sentenciaConfig->execute();
$config = array();
while ($row = $sentenciaConfig->fetch(PDO::FETCH_OBJ)) {
    $config[$row->clave] = $row->valor;
}

$dias_limite = intval($config['dias_limite_anulacion'] ?? 30);
$permitir_factura = intval($config['permitir_anular_factura'] ?? 1);
$requiere_motivo = intval($config['requiere_motivo_anulacion'] ?? 1);

// Obtener datos de la venta con validaciones
$sentenciaVenta = $conexion->prepare("
    SELECT 
        v.*,
        CONCAT(COALESCE(c.nombre_cliente, ''), ' ', COALESCE(c.apellido_cliente, '')) as nombre_cliente,
        c.ci_ruc_cliente,
        DATEDIFF(NOW(), v.fecha_venta) as dias_transcurridos,
        (SELECT COUNT(*) FROM notas_credito WHERE id_venta_original = v.id AND estado != 'ANULADA') as tiene_nota_credito
    FROM ventas v
    LEFT JOIN clientes c ON v.id_cliente = c.id
    WHERE v.id = ?
");
$sentenciaVenta->execute([$id_venta]);
$venta = $sentenciaVenta->fetch(PDO::FETCH_OBJ);

if (!$venta) {
    header("Location: listado_ventas.php?error=venta_no_encontrada");
    exit();
}

// Determinar si puede anularse
$puede_anular = true;
$motivo_bloqueo = "";

if ($venta->estado_venta == 0) {
    $puede_anular = false;
    $motivo_bloqueo = "La venta ya se encuentra ANULADA desde " . date('d/m/Y H:i', strtotime($venta->fecha_anulacion));
}

if ($venta->dias_transcurridos > $dias_limite) {
    $puede_anular = false;
    $motivo_bloqueo = "La venta excede el plazo permitido para anulación ($dias_limite días). Han transcurrido {$venta->dias_transcurridos} días.";
}

if ($venta->tipo_comprobante === 'FACTURA' && !$permitir_factura) {
    $puede_anular = false;
    $motivo_bloqueo = "Las facturas no pueden anularse según la configuración del sistema. Debe generar una nota de crédito.";
}

if ($venta->tiene_nota_credito > 0) {
    $puede_anular = false;
    $motivo_bloqueo = "Esta venta ya tiene una nota de crédito asociada.";
}

// Obtener detalles
$sentenciaDetalles = $conexion->prepare("
    SELECT * FROM detalle_ventas 
    WHERE id_venta = ? 
    ORDER BY id ASC
");
$sentenciaDetalles->execute([$id_venta]);
$detalles = $sentenciaDetalles->fetchAll(PDO::FETCH_OBJ);

// Convertir a JSON
$ventaJSON = json_encode($venta);
$detallesJSON = json_encode($detalles);
$puedeAnular = $puede_anular ? 'true' : 'false';
$motivoBloqueo = json_encode($motivo_bloqueo);
$requiereMotivo = $requiere_motivo ? 'true' : 'false';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar Anulación</title>
    <link href="../css/bulma.min.css" rel="stylesheet">
    <link href="../css/formularios.css" rel="stylesheet">
    <style>
        .main-content { background: #2c3e50 !important; color: white; }
        .warning-container {
            background: rgba(231, 76, 60, 0.1);
            border: 2px solid rgba(231, 76, 60, 0.5);
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            text-align: center;
        }
        .warning-icon {
            font-size: 4rem;
            margin-bottom: 15px;
            color: #e74c3c;
        }
        .warning-title {
            color: #e74c3c;
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .warning-text {
            color: rgba(255,255,255,0.9);
            font-size: 1rem;
            line-height: 1.6;
        }
        .info-venta {
            background: rgba(52, 152, 219, 0.1);
            border: 2px solid rgba(52, 152, 219, 0.3);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .info-venta h3 {
            color: #3498db;
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 15px;
            text-align: center;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: bold;
            color: rgba(255,255,255,0.8);
        }
        .info-value {
            color: #f1c40f;
            font-weight: bold;
        }
        .detalles-table {
            background: rgba(0,0,0,0.2);
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 20px;
        }
        .detalles-table table {
            width: 100%;
            color: white;
        }
        .detalles-table thead {
            background: linear-gradient(45deg, #f39c12, #f1c40f);
        }
        .detalles-table thead th {
            color: #2c3e50;
            font-weight: bold;
            padding: 10px;
            text-align: center;
            font-size: 0.85rem;
        }
        .detalles-table tbody td {
            padding: 10px;
            text-align: center;
            font-size: 0.85rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .blocked-message {
            background: rgba(231, 76, 60, 0.15);
            border: 2px solid rgba(231, 76, 60, 0.5);
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
            text-align: center;
        }
        .blocked-message strong {
            color: #e74c3c;
            font-size: 1.1rem;
        }
    </style>
</head>
<body>
    <?php include '../menu.php'; ?>
    
    <script>
        const venta = <?php echo $ventaJSON; ?>;
        const detalles = <?php echo $detallesJSON; ?>;
        const puedeAnular = <?php echo $puedeAnular; ?>;
        const motivoBloqueo = <?php echo $motivoBloqueo; ?>;
        const requiereMotivo = <?php echo $requiereMotivo; ?>;

        const formatMoney = (num) => '₲ ' + parseFloat(num).toLocaleString('es-PY', {minimumFractionDigits: 2});
        const formatDate = (dateStr) => {
            const d = new Date(dateStr);
            return d.toLocaleString('es-PY', { 
                day: '2-digit', 
                month: '2-digit', 
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        };

        document.addEventListener('DOMContentLoaded', function() {
            const mainContent = document.querySelector('.main-content');
            if (!mainContent) return;

            const nombreCliente = venta.nombre_cliente || 'Cliente Genérico';
            const ciRuc = venta.ci_ruc_cliente || 'S/N';
            const tipoDoc = venta.tipo_comprobante || 'VENTA';
            const numeroDoc = venta.numero_venta || 'N/A';

            let detallesHTML = '';
            let countProductos = 0, countServicios = 0;

            detalles.forEach(det => {
                const tipoBadge = det.tipo_item === 'PRODUCTO' ? 
                    '<span style="background: linear-gradient(45deg, #3498db, #2980b9); color: white; padding: 3px 8px; border-radius: 8px; font-size: 0.75rem; font-weight: bold;">PRODUCTO</span>' :
                    '<span style="background: linear-gradient(45deg, #9b59b6, #8e44ad); color: white; padding: 3px 8px; border-radius: 8px; font-size: 0.75rem; font-weight: bold;">SERVICIO</span>';
                
                if (det.tipo_item === 'PRODUCTO') countProductos++;
                else countServicios++;

                detallesHTML += `<tr>
                    <td>${tipoBadge}</td>
                    <td style="text-align: left;">${det.descripcion}</td>
                    <td>${det.cantidad}</td>
                    <td>${formatMoney(det.precio_unitario)}</td>
                    <td><strong>${formatMoney(det.subtotal)}</strong></td>
                </tr>`;
            });

            let formHTML = '';

            if (puedeAnular) {
                formHTML = `
                    <div class="warning-container">
                        <div class="warning-icon">⚠️</div>
                        <div class="warning-title">¿Confirmar Anulación de Venta?</div>
                        <div class="warning-text">
                            Esta acción es <strong>IRREVERSIBLE</strong>.<br>
                            Se revertirá el stock de productos y se creará un movimiento inverso en caja.<br>
                            ${tipoDoc === 'FACTURA' ? '<strong>IMPORTANTE:</strong> Se generará una nota de crédito para esta factura.' : ''}
                        </div>
                    </div>

                    <form action="./anular_venta.php" method="post" onsubmit="return validarFormulario()">
                        <input type="hidden" name="id" value="${venta.id}">
                        
                        <div class="field">
                            <label class="label">Motivo de la Anulación ${requiereMotivo ? '*' : '(Opcional)'}</label>
                            <div class="control">
                                <textarea class="textarea" name="motivo" id="motivo" rows="4" 
                                    placeholder="Describe el motivo de la anulación (error en factura, devolución, etc.)" 
                                    ${requiereMotivo ? 'required' : ''}></textarea>
                            </div>
                            <p class="help" style="color: rgba(255,255,255,0.7);">
                                ${requiereMotivo ? 'El motivo es obligatorio para la auditoría' : 'El motivo ayuda en la auditoría del sistema'}
                            </p>
                        </div>

                        <div class="field is-grouped" style="justify-content: center; margin-top: 30px;">
                            <div class="control">
                                <button type="submit" class="button" style="background: linear-gradient(45deg, #e74c3c, #c0392b) !important; color: white !important;">
                                    ❌ Confirmar Anulación
                                </button>
                            </div>
                            <div class="control">
                                <a href="./listado_ventas.php" class="secondary-button">
                                    ← Cancelar
                                </a>
                            </div>
                        </div>
                    </form>
                `;
            } else {
                formHTML = `
                    <div class="blocked-message">
                        <div style="font-size: 3rem; margin-bottom: 15px;">🚫</div>
                        <strong>NO SE PUEDE ANULAR ESTA VENTA</strong>
                        <p style="margin-top: 10px; color: rgba(255,255,255,0.9);">${motivoBloqueo}</p>
                    </div>
                    <div style="text-align: center; margin-top: 25px;">
                        <a href="./listado_ventas.php" class="button">← Volver al Listado</a>
                    </div>
                `;
            }

            const contentHTML = `
                <div class="form-container">
                    <h1 class="form-title">Anular Venta #${venta.id}</h1>
                    
                    <div class="info-venta">
                        <h3>📋 Información de la Venta</h3>
                        <div class="info-row">
                            <div class="info-label">Tipo de Documento:</div>
                            <div class="info-value">${tipoDoc} ${numeroDoc != 'N/A' ? '(' + numeroDoc + ')' : ''}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Cliente:</div>
                            <div class="info-value">${nombreCliente}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">CI/RUC:</div>
                            <div class="info-value">${ciRuc}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Fecha de Venta:</div>
                            <div class="info-value">${formatDate(venta.fecha_venta)}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Días Transcurridos:</div>
                            <div class="info-value">${venta.dias_transcurridos} días</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Condición:</div>
                            <div class="info-value">${venta.condicion_venta}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Subtotal:</div>
                            <div class="info-value">${formatMoney(venta.subtotal)}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Descuento:</div>
                            <div class="info-value">${formatMoney(venta.descuento)}</div>
                        </div>
                        <div class="info-row" style="font-size: 1.1rem;">
                            <div class="info-label">TOTAL:</div>
                            <div class="info-value" style="font-size: 1.3rem;">${formatMoney(venta.total_venta)}</div>
                        </div>
                    </div>

                    <div class="detalles-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>TIPO</th>
                                    <th>DESCRIPCIÓN</th>
                                    <th>CANTIDAD</th>
                                    <th>PRECIO UNIT.</th>
                                    <th>SUBTOTAL</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${detallesHTML}
                            </tbody>
                        </table>
                    </div>

                    <div class="info-venta" style="background: rgba(230, 126, 34, 0.1); border-color: rgba(230, 126, 34, 0.3);">
                        <h3 style="color: #e67e22;">⚙️ Acciones que se Realizarán</h3>
                        <div style="text-align: left; padding: 10px;">
                            <p style="padding: 5px 0; color: rgba(255,255,255,0.9);">
                                ✓ Marcar venta como ANULADA
                            </p>
                            <p style="padding: 5px 0; color: rgba(255,255,255,0.9);">
                                ✓ Revertir stock de <strong>${countProductos}</strong> producto(s)
                            </p>
                            <p style="padding: 5px 0; color: rgba(255,255,255,0.9);">
                                ✓ Crear movimiento inverso en caja por <strong>${formatMoney(venta.total_venta)}</strong>
                            </p>
                            <p style="padding: 5px 0; color: rgba(255,255,255,0.9);">
                                ✓ Registrar en historial de anulaciones
                            </p>
                            ${tipoDoc === 'FACTURA' ? '<p style="padding: 5px 0; color: rgba(255,255,255,0.9);">✓ Generar nota de crédito (según configuración)</p>' : ''}
                        </div>
                    </div>

                    ${formHTML}
                </div>
            `;

            mainContent.innerHTML = contentHTML;
        });

        function validarFormulario() {
            const motivo = document.getElementById('motivo').value.trim();
            
            if (requiereMotivo && motivo === '') {
                alert('⚠️ Debe ingresar un motivo para la anulación');
                document.getElementById('motivo').focus();
                return false;
            }

            if (motivo.length < 10) {
                if (!confirm('El motivo es muy corto. ¿Desea continuar de todos modos?')) {
                    return false;
                }
            }

            return confirm('⚠️ ÚLTIMA CONFIRMACIÓN\n\n¿Está completamente seguro de anular esta venta?\n\nEsta acción NO se puede deshacer.');
        }
    </script>
</body>
</html>