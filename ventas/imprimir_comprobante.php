<?php
include_once "../db.php";

if (!isset($_GET['id_venta']) || !isset($_GET['tipo'])) {
    die("Error: Parámetros inválidos");
}

$id_venta = intval($_GET['id_venta']);
$tipo = strtoupper($_GET['tipo']);

// Obtener datos de la venta
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

// Obtener detalle de la venta
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

// IVA es INFORMATIVO (Paraguay: total / 11)
$iva_10 = $total_a_pagar / 11;
$gravadas_10 = $total_a_pagar;
$exentas = 0;

$fecha_hora = date('d/m/Y H:i:s', strtotime($venta->fecha_venta));
$cliente_nombre = trim($venta->nombre_cliente) ?: 'CLIENTE GENÉRICO';
$cliente_ruc = $venta->ci_ruc_cliente ?: 'S/N';
$numero_comprobante = $venta->numero_venta ?: str_pad($id_venta, 7, '0', STR_PAD_LEFT);

$condicion_mostrar = $venta->condicion_venta === 'CREDITO' ? 'CREDITO' : 'CONTADO';

// Función para convertir número a letras
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
    <title><?php echo $tipo; ?> #<?php echo $numero_comprobante; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            size: 5.1cm auto;
            margin: 0.4cm 0.4cm 2.54cm 0.4cm;
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 8px;
            line-height: 1.2;
            color: #000;
            background: #2c3e50;
        }

        /* Vista previa en pantalla - centrada y más grande */
        @media screen {
            body {
                display: flex;
                justify-content: center;
                align-items: flex-start;
                padding: 20px;
                min-height: 100vh;
            }
            
            .container {
                width: 5.1cm;
                background: white;
                box-shadow: 0 0 30px rgba(241, 196, 15, 0.3);
                transform: scale(1.8);
                transform-origin: top center;
                margin-top: 50px;
            }
        }

        /* Para impresión real */
        @media print {
            body {
                background: white;
                padding: 0;
                margin: 0;
            }
            
            .container {
                width: 5.1cm;
                transform: none !important;
                box-shadow: none !important;
            }
            
            .no-print {
                display: none !important;
            }
        }

        .container {
            width: 5.1cm;
            padding: 0;
        }

        .center {
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }

        .header {
            margin-bottom: 2px;
        }

        .empresa-nombre {
            font-weight: bold;
            font-size: 10px;
            margin-bottom: 1px;
        }

        .empresa-info {
            font-size: 6.5px;
            margin-bottom: 0.5px;
            line-height: 1.1;
        }

        .linea {
            border-top: 1px dashed #000;
            margin: 2px 0;
        }

        .linea-doble {
            border-top: 1px solid #000;
            margin: 2px 0;
        }

        .seccion-titulo {
            font-weight: bold;
            font-size: 9px;
            margin: 2px 0;
        }

        .fila {
            display: flex;
            justify-content: space-between;
            margin: 0.5px 0;
            font-size: 7px;
        }

        .fila-label {
            width: 48%;
            font-size: 6.5px;
        }

        .fila-valor {
            width: 52%;
            text-align: right;
            word-wrap: break-word;
        }

        .cliente-nombre {
            font-size: 6.5px;
            text-align: right;
            word-wrap: break-word;
            line-height: 1.2;
            white-space: normal;
        }

        .tabla-productos {
            margin: 2px 0;
        }

        .tabla-header {
            font-weight: bold;
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid #000;
            padding-bottom: 1px;
            margin-bottom: 1px;
            font-size: 7px;
        }

        .tabla-row {
            display: flex;
            justify-content: space-between;
            margin: 0.5px 0;
            font-size: 6.5px;
        }

        .col-descripcion {
            width: 50%;
            word-wrap: break-word;
        }

        .col-cant {
            width: 15%;
            text-align: center;
        }
        .col-precio-uni { 
            width: 16%; 
            text-align: right; }

        .col-precio {
            width: 20%;
            text-align: right;
        }

        .col-iva {
            width: 15%;
            text-align: right;
        }

        .totales {
            margin-top: 2px;
        }

        .total-principal {
            font-weight: bold;
            font-size: 8px;
        }

        .total-letras {
            font-size: 6px;
            margin-top: 1px;
            text-align: left;
            word-wrap: break-word;
            line-height: 1.2;
        }

        .pie {
            margin-top: 3px;
            font-size: 6.5px;
            line-height: 1.2;
        }

        .btn-imprimir {
            position: fixed;
            top: 10px;
            right: 10px;
            background: linear-gradient(45deg, #27ae60, #2ecc71);
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
            background: linear-gradient(45deg, #2ecc71, #27ae60);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(39, 174, 96, 0.6);
        }
    </style>
</head>
<body>
    <button class="btn-imprimir no-print" onclick="window.print()">🖨️ IMPRIMIR</button>

    <div class="container">
        <?php if ($tipo === 'FACTURA'): ?>
            <!-- FACTURA -->
            <div class="header center">
                <div class="empresa-nombre">MAGISTER OFFICE</div>
                <div class="empresa-info">CIBER - FOTOCOPIAS - IMPRESIONES - LIBRERÍA</div>
                <div class="empresa-info">VENTA DE ARTÍCULOS INFORMÁTICOS Y UNIFORMES PARA DAMA</div>
                <div class="empresa-info">Martín María Llano y Yegros - Tel. 0972-617447 - 0217212072</div>
                <div class="empresa-info">San Juan Bautista - Misiones - Paraguay</div>
                <div class="empresa-info bold">RUC: 1723448-4</div>
            </div>

            <div class="linea"></div>

            <div class="center seccion-titulo">FACTURA</div>

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
                <div class="fila-label">Factura <?php echo $condicion_mostrar; ?> N°:</div>
                <div class="fila-valor bold"><?php echo $numero_comprobante; ?></div>
            </div>
            <div class="fila">
                <div class="fila-label">Condición:</div>
                <div class="fila-valor bold"><?php echo $condicion_mostrar; ?></div>
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
                <div class="fila-label" style="flex-shrink: 0;">Razón Social:</div>
                <div class="cliente-nombre"><?php echo $cliente_nombre; ?></div>
            </div>

            <div class="linea"></div>

            <div class="tabla-productos">
                <div class="tabla-header">
                    <div class="col-descripcion">Descripción</div>
                    <div class="col-cant">Cant.</div>
                    <div class="col-precio-uni">Precio uni.</div>
                    <div class="col-precio">Importe</div>
                    <div class="col-iva">IVA</div>
                </div>

                <?php foreach ($detalles as $item): ?>
                    <div class="tabla-row">
                        <div class="col-descripcion"><?php echo substr($item->descripcion, 0, 20); ?></div>
                        <div class="col-cant"><?php echo $item->cantidad; ?></div>
                        <div class="col-precio-uni"><?php echo number_format($item->precio_unitario, 0, ',', '.'); ?></div>
                        <div class="col-precio"><?php echo number_format($item->subtotal, 0, ',', '.'); ?></div>
                        <div class="col-iva">10</div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="linea"></div>

            <div class="totales">
                <div class="fila">
                    <div class="fila-label">SUB TOTAL A PAGAR</div>
                    <div class="fila-valor"><?php echo number_format($subtotal, 0, ',', '.'); ?></div>
                </div>
                <div class="fila">
                    <div class="fila-label">Descuento</div>
                    <div class="fila-valor"><?php echo number_format($descuento, 0, ',', '.'); ?></div>
                </div>

                <div class="linea"></div>

                <div class="fila total-principal">
                    <div class="fila-label">TOTAL A PAGAR:</div>
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
                    <div class="fila-label">TOTAL IVA (Inform.):</div>
                    <div class="fila-valor"><?php echo number_format($iva_10, 0, ',', '.'); ?></div>
                </div>
            </div>

            <div class="linea-doble"></div>

            <div class="pie center">
                Original: Cliente
            </div>
            <div class="pie center">
                NO SE ACEPTAN RECLAMOS PASADAS LAS 24 HORAS
            </div>

        <?php else: ?>
            <!-- TICKET -->
            <div class="header center">
                <div class="empresa-nombre">MAGISTER OFFICE</div>
                <div class="empresa-info">CIBER - FOTOCOPIAS - LIBRERÍA</div>
                <div class="seccion-titulo">RECIBO</div>
            </div>

            <div class="linea"></div>

            <div class="fila">
                <div class="fila-label">Ticket N°:</div>
                <div class="fila-valor bold"><?php echo $numero_comprobante; ?></div>
            </div>
            <div class="fila">
                <div class="fila-label">Fecha:</div>
                <div class="fila-valor"><?php echo $fecha_hora; ?></div>
            </div>
            <div class="fila">
                <div class="fila-label">Cliente:</div>
                <div class="fila-valor"><?php echo substr($cliente_nombre, 0, 20); ?></div>
            </div>

            <div class="linea"></div>

            <div class="tabla-productos">
                <div class="tabla-header">
                    <div class="col-descripcion">Descripción</div>
                    <div class="col-cant">Cant.</div>
                    <div class="col-precio-uni">Precio uni.</div>
                    <div class="col-precio">Importe</div>
                </div>

                <?php foreach ($detalles as $item): ?>
                    <div class="tabla-row">
                        <div class="col-descripcion"><?php echo substr($item->descripcion, 0, 22); ?></div>
                        <div class="col-cant"><?php echo $item->cantidad; ?></div>
                        <div class="col-precio-uni"><?php echo number_format($item->precio_unitario, 0, ',', '.'); ?></div>
                        <div class="col-precio"><?php echo number_format($item->subtotal, 0, ',', '.'); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="linea"></div>

            <div class="totales">
                <div class="fila">
                    <div class="fila-label">Subtotal:</div>
                    <div class="fila-valor"><?php echo number_format($subtotal, 0, ',', '.'); ?></div>
                </div>
                <div class="fila total-principal">
                    <div class="fila-label">TOTAL:</div>
                    <div class="fila-valor"><?php echo number_format($total_a_pagar, 0, ',', '.'); ?></div>
                </div>
                <div class="fila" style="font-size: 6px; margin-top: 2px;">
                    <div class="fila-label">IVA Incluido (10%):</div>
                    <div class="fila-valor"><?php echo number_format($iva_10, 0, ',', '.'); ?></div>
                </div>
            </div>

            <div class="linea"></div>

            <div class="pie center">
                GRACIAS POR SU PREFERENCIA
            </div>
            <div class="pie center">
                Tel: 0972-617447
            </div>

        <?php endif; ?>
    </div>
</body>
</html>