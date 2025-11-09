<?php
include_once __DIR__ . "/../auth.php";
if (!tienePermiso(['ADMINISTRADOR'])) {
    header("Location: ../index.php?error=sin_permisos");
    exit();
}
include_once "../db.php";

if (!isset($_GET['id_venta']) || !isset($_GET['tipo'])) {
    die("Error: Parámetros inválidos");
}

$id_venta = intval($_GET['id_venta']);
$tipo = strtoupper($_GET['tipo']);

$sentenciaVenta = $conexion->prepare("
    SELECT v.*, 
           CONCAT(COALESCE(c.nombre_cliente, ''), ' ', COALESCE(c.apellido_cliente, '')) as nombre_cliente,
           c.ci_ruc_cliente
    FROM ventas v
    LEFT JOIN clientes c ON v.id_cliente = c.id
    WHERE v.id = ?
");
$sentenciaVenta->execute([$id_venta]);
$venta = $sentenciaVenta->fetch(PDO::FETCH_OBJ);

if (!$venta) {
    die("Error: Venta no encontrada");
}

$esta_anulada = ($venta->estado_venta == 0);

$sentenciaDetalle = $conexion->prepare("
    SELECT * FROM detalle_ventas 
    WHERE id_venta = ? 
    ORDER BY id ASC
");
$sentenciaDetalle->execute([$id_venta]);
$detalles = $sentenciaDetalle->fetchAll(PDO::FETCH_OBJ);

$subtotal = floatval($venta->subtotal);
$descuento = floatval($venta->descuento);
$total_a_pagar = floatval($venta->total_venta);

$iva_10 = $subtotal / 11;
$gravadas_10 = $total_a_pagar;
$exentas = 0;

$fecha_hora = date('d/m/Y H:i:s', strtotime($venta->fecha_venta));
$cliente_nombre = trim($venta->nombre_cliente) ?: 'SIN ESPECIFICAR';
$cliente_ruc = $venta->ci_ruc_cliente ?: 'S/N';
$numero_comprobante = $venta->numero_venta ?: str_pad($id_venta, 7, '0', STR_PAD_LEFT);

$condicion_mostrar = $venta->condicion_venta === 'CREDITO' ? 'CREDITO' : 'CONTADO';

$fecha_anulacion = $esta_anulada && $venta->fecha_anulacion ? date('d/m/Y H:i:s', strtotime($venta->fecha_anulacion)) : '';
$motivo_anulacion = $esta_anulada ? ($venta->motivo_anulacion ?: 'Sin especificar') : '';
$usuario_anula = $esta_anulada ? ($venta->usuario_anula ?: 'N/A') : '';

function numeroALetras($numero) {
    $numero = intval($numero);
    if ($numero == 0) return 'CERO GUARANIES';
    if ($numero == 100) return 'CIEN GUARANIES';
    
    $unidades = ['', 'UN', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE'];
    $decenas = ['', '', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
    $especiales = ['DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISEIS', 'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE'];
    $centenas = ['', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS', 'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS'];
    
    $letras = '';
    
    if ($numero >= 1000000) {
        $millones = intval($numero / 1000000);
        if ($millones == 1) {
            $letras .= 'UN MILLON ';
        } else {
            $letras .= numeroALetrasBasico($millones) . ' MILLONES ';
        }
        $numero %= 1000000;
    }
    
    if ($numero >= 1000) {
        $miles = intval($numero / 1000);
        if ($miles == 1) {
            $letras .= 'MIL ';
        } else {
            $letras .= numeroALetrasBasico($miles) . ' MIL ';
        }
        $numero %= 1000;
    }
    
    if ($numero > 0) {
        $letras .= numeroALetrasBasico($numero);
    }
    
    return trim($letras) . ' GUARANIES';
}

function numeroALetrasBasico($numero) {
    $unidades = ['', 'UN', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE'];
    $decenas = ['', '', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
    $especiales = ['DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISEIS', 'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE'];
    $centenas = ['', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS', 'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS'];
    
    $letras = '';
    
    if ($numero >= 100) {
        if ($numero == 100) return 'CIEN';
        $letras .= $centenas[intval($numero / 100)] . ' ';
        $numero %= 100;
    }
    
    if ($numero >= 10 && $numero < 20) {
        $letras .= $especiales[$numero - 10];
    } else {
        if ($numero >= 20) {
            $letras .= $decenas[intval($numero / 10)];
            if ($numero % 10 > 0) {
                $letras .= ' Y ' . $unidades[$numero % 10];
            }
        } else {
            $letras .= $unidades[$numero];
        }
    }
    
    return trim($letras);
}

$total_en_letras = numeroALetras($total_a_pagar);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $tipo; ?> #<?php echo $numero_comprobante; ?><?php echo $esta_anulada ? ' - ANULADA' : ''; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            size: 58mm auto;
            margin: 2mm 2mm 10mm 2mm;
        }

        body {
            font-family: 'Courier New', 'Courier', monospace;
            font-size: 9px;
            line-height: 1.3;
            color: #000;
            background: #f5f5f5;
        }

        /* Vista previa en pantalla */
        @media screen {
            body {
                display: flex;
                justify-content: center;
                align-items: flex-start;
                padding: 20px;
                min-height: 100vh;
            }
            
            .container {
                width: 58mm;
                background: white;
                box-shadow: 0 0 20px rgba(0,0,0,0.2);
                padding: 3mm;
            }
            
            .btn-imprimir {
                display: block;
            }
        }

        /* Para impresión térmica real */
        @media print {
            body {
                background: white;
                padding: 0;
                margin: 0;
            }
            
            .container {
                width: 58mm;
                padding: 0;
            }
            
            .no-print {
                display: none !important;
            }
        }

        .container {
            width: 58mm;
            position: relative;
        }

        /* MARCA DE AGUA ANULADA - Optimizada para térmica */
        .marca-anulada {
            position: absolute;
            top: 40%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 28px;
            font-weight: bold;
            color: rgba(0, 0, 0, 0.08);
            pointer-events: none;
            z-index: 1;
            white-space: nowrap;
            letter-spacing: 1px;
        }

        /* BANNER ANULADA - Simplificado para térmica */
        .banner-anulada {
            background: #000;
            color: #fff;
            text-align: center;
            padding: 3px 0;
            font-weight: bold;
            font-size: 10px;
            margin-bottom: 3px;
            border: 2px solid #000;
        }

        /* SECCIÓN DE ANULACIÓN - Optimizada */
        .seccion-anulacion {
            border: 2px solid #000;
            padding: 4px;
            margin: 4px 0;
            font-size: 7px;
            background: #f0f0f0;
        }

        .seccion-anulacion .titulo {
            font-weight: bold;
            font-size: 8px;
            text-align: center;
            margin-bottom: 2px;
            border-bottom: 1px solid #000;
            padding-bottom: 1px;
        }

        .seccion-anulacion .dato {
            margin: 1px 0;
            word-wrap: break-word;
        }

        .center {
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }

        .header {
            margin-bottom: 2mm;
        }

        .empresa-nombre {
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 1mm;
        }

        .empresa-info {
            font-size: 7px;
            margin-bottom: 0.5mm;
            line-height: 1.2;
        }

        .linea {
            border-top: 1px dashed #000;
            margin: 2mm 0;
        }

        .linea-doble {
            border-top: 2px solid #000;
            margin: 2mm 0;
        }

        .seccion-titulo {
            font-weight: bold;
            font-size: 11px;
            margin: 2mm 0;
        }

        .fila {
            display: flex;
            justify-content: space-between;
            margin: 1mm 0;
            font-size: 8px;
        }

        .fila-label {
            font-size: 7px;
        }

        .fila-valor {
            text-align: right;
            word-wrap: break-word;
            font-weight: bold;
        }

        .cliente-nombre {
            font-size: 7px;
            text-align: right;
            word-wrap: break-word;
            line-height: 1.2;
            font-weight: bold;
        }

        .tabla-productos {
            margin: 2mm 0;
        }

        .tabla-header {
            font-weight: bold;
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid #000;
            padding-bottom: 1mm;
            margin-bottom: 1mm;
            font-size: 8px;
        }

        .tabla-row {
            display: flex;
            justify-content: space-between;
            margin: 1mm 0;
            font-size: 7px;
        }

        .col-descripcion {
            width: 43%;
            word-wrap: break-word;
            padding-right: 1mm;
            font-size: 6.5px;
        }

        .col-cant {
            width: 10%;
            text-align: center;
        }

        .col-precio-unit {
            width: 22%;
            text-align: right;
        }

        .col-subtotal {
            width: 25%;
            text-align: right;
            font-weight: bold;
        }

        .col-iva {
            width: 10%;
            text-align: right;
            padding-left: 2px;
        }

        .totales {
            margin-top: 2mm;
        }

        .total-principal {
            font-weight: bold;
            font-size: 10px;
        }

        .total-letras {
            font-size: 6px;
            margin-top: 1mm;
            text-align: left;
            word-wrap: break-word;
            line-height: 1.2;
        }

        .pie {
            margin-top: 2mm;
            font-size: 7px;
            line-height: 1.3;
        }

        /* Espaciado para corte de papel */
        .espacio-corte {
            height: 15mm;
        }

        .btn-imprimir {
            position: fixed;
            top: 10px;
            right: 10px;
            background: #27ae60;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            z-index: 1000;
            font-size: 14px;
            box-shadow: 0 4px 15px rgba(39, 174, 96, 0.4);
            transition: all 0.3s ease;
        }

        .btn-imprimir:hover {
            background: #2ecc71;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <button class="btn-imprimir no-print" onclick="window.print()">🖨️ IMPRIMIR</button>

    <div class="container">
        <?php if ($esta_anulada): ?>
            <div class="marca-anulada">ANULADA</div>
            <div class="banner-anulada">*** DOCUMENTO ANULADO ***</div>
        <?php endif; ?>

        <?php if ($tipo === 'FACTURA'): ?>
            <!-- FACTURA -->
            <div class="header center">
                <div class="empresa-nombre">MAGISTER OFFICE</div>
                <div class="empresa-info">CIBER - FOTOCOPIAS - IMPRESIONES</div>
                <div class="empresa-info">LIBRERIA</div>
                <div class="empresa-info">VENTA DE ARTICULOS INFORMATICOS</div>
                <div class="empresa-info">Y UNIFORMES PARA DAMA</div>
                <div class="empresa-info">Martin Maria Llano y Yegros</div>
                <div class="empresa-info">Tel. 0972-617447 - 0217212072</div>
                <div class="empresa-info">San Juan Bautista - Misiones</div>
                <div class="empresa-info bold">RUC: 1723448-4</div>
            </div>

            <div class="linea"></div>

            <div class="center seccion-titulo">FACTURA<?php echo $esta_anulada ? ' - ANULADA' : ''; ?></div>

            <div class="linea"></div>

            <div class="fila">
                <div class="fila-label">Nro. Timbrado:</div>
                <div class="fila-valor">[SIN TIMBRADO]</div>
            </div>
            <div class="fila">
                <div class="fila-label">Fecha Ini.Vigencia:</div>
                <div class="fila-valor">--/--/----</div>
            </div>

            <div class="linea"></div>

            <div class="fila">
                <div class="fila-label">Factura N°:</div>
                <div class="fila-valor"><?php echo $numero_comprobante; ?></div>
            </div>
            <div class="fila">
                <div class="fila-label">Condicion:</div>
                <div class="fila-valor"><?php echo $condicion_mostrar; ?></div>
            </div>
            <div class="fila">
                <div class="fila-label">Fecha Hora:</div>
                <div class="fila-valor"><?php echo $fecha_hora; ?></div>
            </div>
            <div class="fila">
                <div class="fila-label">R.U.C. C.I.:</div>
                <div class="fila-valor"><?php echo $cliente_ruc; ?></div>
            </div>
            <div class="fila">
                <div class="fila-label">Razon Social:</div>
                <div class="cliente-nombre"><?php echo $cliente_nombre; ?></div>
            </div>

            <?php if ($esta_anulada): ?>
                <div class="linea"></div>
                <div class="seccion-anulacion">
                    <div class="titulo">INFORMACION DE ANULACION</div>
                    <div class="dato"><strong>Fecha:</strong> <?php echo $fecha_anulacion; ?></div>
                    <div class="dato"><strong>Usuario:</strong> <?php echo $usuario_anula; ?></div>
                    <div class="dato"><strong>Motivo:</strong> <?php echo substr($motivo_anulacion, 0, 80); ?></div>
                </div>
            <?php endif; ?>

            <div class="linea"></div>

            <div class="tabla-productos">
                <div class="tabla-header">
                    <div class="col-descripcion">Descrip.</div>
                    <div class="col-cant">Cant</div>
                    <div class="col-precio-unit">P.Unit</div>
                    <div class="col-subtotal">Subto.</div>
                    <div class="col-iva">IVA</div>
                </div>

                <?php foreach ($detalles as $item): ?>
                    <div class="tabla-row">
                        <div class="col-descripcion"><?php echo substr($item->descripcion, 0, 18); ?></div>
                        <div class="col-cant"><?php echo $item->cantidad; ?></div>
                        <div class="col-precio-unit"><?php echo number_format($item->precio_unitario, 0, ',', '.'); ?></div>
                        <div class="col-subtotal"><?php echo number_format($item->subtotal, 0, ',', '.'); ?></div>
                        <div class="col-iva">10</div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="linea"></div>

            <div class="totales">
                <div class="fila">
                    <div class="fila-label">SUB TOTAL</div>
                    <div class="fila-valor"><?php echo number_format($subtotal, 0, ',', '.'); ?></div>
                </div>
                <div class="fila">
                    <div class="fila-label">Descuento</div>
                    <div class="fila-valor"><?php echo number_format($descuento, 0, ',', '.'); ?></div>
                </div>

                <div class="linea"></div>

                <div class="fila total-principal">
                    <div class="fila-label">TOTAL <?php echo $esta_anulada ? '(ANULADO)' : ''; ?>:</div>
                    <div class="fila-valor"><?php echo number_format($total_a_pagar, 0, ',', '.'); ?></div>
                </div>
                <div class="total-letras">
                    Son: <?php echo $total_en_letras; ?>
                </div>
                
                <div class="linea"></div>
                
                <div class="fila">
                    <div class="fila-label">EXENTA:</div>
                    <div class="fila-valor"><?php echo number_format($exentas, 0, ',', '.'); ?></div>
                </div>
                <div class="fila">
                    <div class="fila-label">GRAVADAS 10%:</div>
                    <div class="fila-valor"><?php echo number_format($gravadas_10, 0, ',', '.'); ?></div>
                </div>
                <div class="fila bold">
                    <div class="fila-label">TOTAL IVA:</div>
                    <div class="fila-valor"><?php echo number_format($iva_10, 0, ',', '.'); ?></div>
                </div>
            </div>

            <div class="linea-doble"></div>

            <?php if ($esta_anulada): ?>
                <div class="pie center bold">
                    *** DOCUMENTO SIN VALOR ***
                </div>
                <div class="pie center bold">
                    *** FACTURA ANULADA ***
                </div>
            <?php else: ?>
                <div class="pie center">
                    Original: Cliente
                </div>
                <div class="pie center">
                    NO SE ACEPTAN RECLAMOS
                </div>
                <div class="pie center">
                    PASADAS LAS 24 HORAS
                </div>
            <?php endif; ?>

        <?php else: ?>
            <!-- TICKET -->
            <div class="header center">
                <div class="empresa-nombre">MAGISTER OFFICE</div>
                <div class="empresa-info">CIBER - FOTOCOPIAS - LIBRERIA</div>
                <div class="seccion-titulo">RECIBO<?php echo $esta_anulada ? ' - ANULADO' : ''; ?></div>
            </div>

            <div class="linea"></div>

            <div class="fila">
                <div class="fila-label">Ticket N°:</div>
                <div class="fila-valor"><?php echo $numero_comprobante; ?></div>
            </div>
            <div class="fila">
                <div class="fila-label">Fecha:</div>
                <div class="fila-valor"><?php echo $fecha_hora; ?></div>
            </div>
            <div class="fila">
                <div class="fila-label">Cliente:</div>
                <div class="fila-valor"><?php echo $cliente_nombre; ?></div>
            </div>

            <?php if ($esta_anulada): ?>
                <div class="linea"></div>
                <div class="seccion-anulacion">
                    <div class="titulo">INFO ANULACION</div>
                    <div class="dato"><strong>Fecha:</strong> <?php echo $fecha_anulacion; ?></div>
                    <div class="dato"><strong>Usuario:</strong> <?php echo $usuario_anula; ?></div>
                    <div class="dato"><strong>Motivo:</strong> <?php echo substr($motivo_anulacion, 0, 70); ?></div>
                </div>
            <?php endif; ?>

            <div class="linea"></div>

            <div class="tabla-productos">
                <div class="tabla-header">
                    <div class="col-descripcion">Descrip.</div>
                    <div class="col-cant">Cant</div>
                    <div class="col-precio-unit">P.Unit</div>
                    <div class="col-subtotal">Subto.</div>
                </div>

                <?php foreach ($detalles as $item): ?>
                    <div class="tabla-row">
                        <div class="col-descripcion"><?php echo substr($item->descripcion, 0, 18); ?></div>
                        <div class="col-cant"><?php echo $item->cantidad; ?></div>
                        <div class="col-precio-unit"><?php echo number_format($item->precio_unitario, 0, ',', '.'); ?></div>
                        <div class="col-subtotal"><?php echo number_format($item->subtotal, 0, ',', '.'); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="linea"></div>

            <div class="totales">
                <div class="fila total-principal">
                    <div class="fila-label">TOTAL A PAGAR<?php echo $esta_anulada ? ' (ANULADO)' : ''; ?>:</div>
                    <div class="fila-valor"><?php echo number_format($subtotal, 0, ',', '.'); ?></div>
                </div>
                <div class="fila" style="font-size: 7px; margin-top: 1mm;">
                    <div class="fila-label">IVA (10%):</div>
                    <div class="fila-valor"><?php echo number_format($iva_10, 0, ',', '.'); ?></div>
                </div>
            </div>

            <div class="linea"></div>

            <?php if ($esta_anulada): ?>
                <div class="pie center bold">
                    *** DOCUMENTO SIN VALOR ***
                </div>
                <div class="pie center bold">
                    *** TICKET ANULADO ***
                </div>
            <?php else: ?>
                <div class="pie center">
                    GRACIAS POR SU PREFERENCIA
                </div>
                <div class="pie center">
                    Tel: 0972-617447
                </div>
            <?php endif; ?>

        <?php endif; ?>
        
        <!-- Espacio para el corte de papel -->
        <div class="espacio-corte"></div>
    </div>
</body>
</html>