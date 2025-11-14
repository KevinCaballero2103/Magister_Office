<?php
include_once __DIR__ . "/../auth.php";
if (!tienePermiso(['ADMINISTRADOR', 'CAJERO'])) {
    header("Location: ../index.php?error=sin_permisos");
    exit();
}
include_once "../db.php";

// ========================================
// FUNCIONES AUXILIARES (GLOBALES)
// ========================================
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
        $letras .= ($millones == 1) ? 'UN MILLON ' : numeroALetrasBasico($millones) . ' MILLONES ';
        $numero %= 1000000;
    }
    
    if ($numero >= 1000) {
        $miles = intval($numero / 1000);
        $letras .= ($miles == 1) ? 'MIL ' : numeroALetrasBasico($miles) . ' MIL ';
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

// Validar parámetros
if (!isset($_GET['tipo'])) {
    die("Error: Tipo de comprobante no especificado");
}

$tipo = strtoupper($_GET['tipo']);

// ========================================
// NUEVO: RECIBO DE CUOTA
// ========================================
if ($tipo === 'RECIBO_CUOTA') {
    if (!isset($_GET['id_cuota'])) {
        die("Error: ID de cuota no especificado");
    }
    
    $id_cuota = intval($_GET['id_cuota']);
    
    $sentenciaCuota = $conexion->prepare("
        SELECT c.*,
               v.numero_venta,
               v.total_venta,
               v.fecha_venta,
               v.condicion_venta,
               CONCAT(COALESCE(cli.nombre_cliente, ''), ' ', COALESCE(cli.apellido_cliente, '')) as nombre_cliente,
               cli.ci_ruc_cliente,
               cli.telefono_cliente,
               (SELECT COUNT(*) FROM cuotas_venta WHERE id_venta = c.id_venta) as total_cuotas,
               (SELECT COUNT(*) FROM cuotas_venta WHERE id_venta = c.id_venta AND estado = 'PAGADA') as cuotas_pagadas
        FROM cuotas_venta c
        INNER JOIN ventas v ON c.id_venta = v.id
        LEFT JOIN clientes cli ON v.id_cliente = cli.id
        WHERE c.id = ?
    ");
    $sentenciaCuota->execute([$id_cuota]);
    $cuota = $sentenciaCuota->fetch(PDO::FETCH_OBJ);
    
    if (!$cuota) {
        die("Error: Cuota no encontrada");
    }
    
    if ($cuota->estado !== 'PAGADA') {
        die("Error: Esta cuota no ha sido pagada aún");
    }
    
    $fecha_pago = date('d/m/Y H:i:s', strtotime($cuota->fecha_pago));
    $fecha_vencimiento = date('d/m/Y', strtotime($cuota->fecha_vencimiento));
    $cliente_nombre = trim($cuota->nombre_cliente) ?: 'CLIENTE GENÉRICO';
    $monto_cuota = floatval($cuota->monto);
    
    $monto_letras = numeroALetras($monto_cuota);
    
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Recibo Cuota #<?php echo $cuota->numero; ?> - Venta #<?php echo $cuota->id_venta; ?></title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            @page { size: 58mm auto; margin: 2mm 2mm 10mm 2mm; }
            body {
                font-family: 'Courier New', 'Courier', monospace;
                font-size: 9px;
                line-height: 1.3;
                color: #000;
                background: #f5f5f5;
            }
            @media screen {
                body { display: flex; justify-content: center; align-items: flex-start; padding: 20px; min-height: 100vh; }
                .container { width: 58mm; background: white; box-shadow: 0 0 20px rgba(0,0,0,0.2); padding: 3mm; }
                .btn-imprimir { display: block; }
            }
            @media print {
                body { background: white; padding: 0; margin: 0; }
                .container { width: 58mm; padding: 0; }
                .no-print { display: none !important; }
            }
            .container { width: 58mm; position: relative; }
            .center { text-align: center; }
            .bold { font-weight: bold; }
            .header { margin-bottom: 2mm; }
            .empresa-nombre { font-weight: bold; font-size: 11px; margin-bottom: 1mm; }
            .empresa-info { font-size: 7px; margin-bottom: 0.5mm; line-height: 1.2; }
            .linea { border-top: 1px dashed #000; margin: 2mm 0; }
            .linea-doble { border-top: 2px solid #000; margin: 2mm 0; }
            .seccion-titulo { font-weight: bold; font-size: 11px; margin: 2mm 0; }
            .fila { display: flex; justify-content: space-between; margin: 1mm 0; font-size: 8px; }
            .fila-label { font-size: 7px; }
            .fila-valor { text-align: right; word-wrap: break-word; font-weight: bold; }
            .monto-destacado {
                background: #000;
                color: #fff;
                padding: 3mm;
                margin: 2mm 0;
                text-align: center;
                border-radius: 2mm;
            }
            .monto-numero { font-size: 14px; font-weight: bold; margin-bottom: 1mm; }
            .monto-letras { font-size: 6px; margin-top: 1mm; text-align: left; word-wrap: break-word; line-height: 1.2; }
            .pie { margin-top: 2mm; font-size: 7px; line-height: 1.3; }
            .espacio-corte { height: 15mm; }
            .btn-imprimir {
                position: fixed; top: 10px; right: 10px;
                background: #27ae60; color: white; border: none;
                padding: 12px 24px; border-radius: 8px;
                cursor: pointer; font-weight: bold;
                z-index: 1000; font-size: 14px;
                box-shadow: 0 4px 15px rgba(39, 174, 96, 0.4);
                transition: all 0.3s ease;
            }
            .btn-imprimir:hover { background: #2ecc71; transform: translateY(-2px); }
            .progreso-box {
                border: 1px solid #000;
                padding: 2mm;
                margin: 2mm 0;
                text-align: center;
            }
        </style>
    </head>
    <body>
        <button class="btn-imprimir no-print" onclick="window.print()">🖨️ IMPRIMIR</button>
        
        <div class="container">
            <div class="header center">
                <div class="empresa-nombre">MAGISTER OFFICE</div>
                <div class="empresa-info">CIBER - FOTOCOPIAS - LIBRERIA</div>
                <div class="seccion-titulo">RECIBO DE CUOTA</div>
            </div>
            
            <div class="linea"></div>
            
            <div class="fila">
                <div class="fila-label">Venta N°:</div>
                <div class="fila-valor"><?php echo $cuota->id_venta; ?> <?php echo $cuota->numero_venta ? '(' . $cuota->numero_venta . ')' : ''; ?></div>
            </div>
            <div class="fila">
                <div class="fila-label">Cliente:</div>
                <div class="fila-valor"><?php echo $cliente_nombre; ?></div>
            </div>
            <?php if ($cuota->ci_ruc_cliente): ?>
            <div class="fila">
                <div class="fila-label">CI/RUC:</div>
                <div class="fila-valor"><?php echo $cuota->ci_ruc_cliente; ?></div>
            </div>
            <?php endif; ?>
            <?php if ($cuota->telefono_cliente): ?>
            <div class="fila">
                <div class="fila-label">Telefono:</div>
                <div class="fila-valor"><?php echo $cuota->telefono_cliente; ?></div>
            </div>
            <?php endif; ?>
            
            <div class="linea"></div>
            
            <div class="fila">
                <div class="fila-label">Cuota N°:</div>
                <div class="fila-valor"><?php echo $cuota->numero; ?> de <?php echo $cuota->total_cuotas; ?></div>
            </div>
            <div class="fila">
                <div class="fila-label">Vencimiento:</div>
                <div class="fila-valor"><?php echo $fecha_vencimiento; ?></div>
            </div>
            <div class="fila">
                <div class="fila-label">Fecha Pago:</div>
                <div class="fila-valor"><?php echo $fecha_pago; ?></div>
            </div>
            
            <div class="linea-doble"></div>
            
            <div class="monto-destacado">
                <div style="font-size: 8px;">MONTO PAGADO</div>
                <div class="monto-numero">₲ <?php echo number_format($monto_cuota, 0, ',', '.'); ?></div>
            </div>
            
            <div class="monto-letras">
                Son: <?php echo $monto_letras; ?>
            </div>
            
            <div class="linea"></div>
            
            <div class="progreso-box">
                <div style="font-size: 8px; font-weight: bold;">ESTADO DE PAGO</div>
                <div style="font-size: 10px; margin: 1mm 0;">
                    <?php echo $cuota->cuotas_pagadas; ?> / <?php echo $cuota->total_cuotas; ?> CUOTAS
                </div>
                <?php if ($cuota->cuotas_pagadas == $cuota->total_cuotas): ?>
                <div style="font-size: 9px; font-weight: bold; margin-top: 1mm;">
                    *** DEUDA CANCELADA ***
                </div>
                <?php else: ?>
                <div style="font-size: 7px; color: #666;">
                    Pendiente: <?php echo ($cuota->total_cuotas - $cuota->cuotas_pagadas); ?> cuota(s)
                </div>
                <?php endif; ?>
            </div>
            
            <div class="linea"></div>
            
            <div class="fila">
                <div class="fila-label">Total Venta:</div>
                <div class="fila-valor">₲ <?php echo number_format($cuota->total_venta, 0, ',', '.'); ?></div>
            </div>
            
            <div class="linea-doble"></div>
            
            <div class="pie center">
                GRACIAS POR SU PAGO
            </div>
            <div class="pie center">
                Tel: 0972-617447
            </div>
            <div class="pie center" style="font-size: 6px; margin-top: 2mm;">
                San Juan Bautista - Misiones
            </div>
            
            <div class="espacio-corte"></div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// ========================================
// NUEVO: PAGARÉ (DEUDA CANCELADA)
// ========================================
if ($tipo === 'PAGARE') {
    if (!isset($_GET['id_venta'])) {
        die("Error: ID de venta no especificado");
    }
    
    $id_venta = intval($_GET['id_venta']);
    
    $sentenciaVenta = $conexion->prepare("
        SELECT v.*,
               CONCAT(COALESCE(c.nombre_cliente, ''), ' ', COALESCE(c.apellido_cliente, '')) as nombre_cliente,
               c.ci_ruc_cliente,
               c.telefono_cliente,
               (SELECT COUNT(*) FROM cuotas_venta WHERE id_venta = v.id) as total_cuotas,
               (SELECT COUNT(*) FROM cuotas_venta WHERE id_venta = v.id AND estado = 'PAGADA') as cuotas_pagadas
        FROM ventas v
        LEFT JOIN clientes c ON v.id_cliente = c.id
        WHERE v.id = ? AND v.condicion_venta = 'CREDITO'
    ");
    $sentenciaVenta->execute([$id_venta]);
    $venta = $sentenciaVenta->fetch(PDO::FETCH_OBJ);
    
    if (!$venta) {
        die("Error: Venta no encontrada o no es a crédito");
    }
    
    if ($venta->cuotas_pagadas != $venta->total_cuotas) {
        die("Error: Esta venta aún tiene cuotas pendientes de pago");
    }
    
    // Obtener fechas de las cuotas
    $sentenciaCuotas = $conexion->prepare("
        SELECT * FROM cuotas_venta 
        WHERE id_venta = ? 
        ORDER BY numero ASC
    ");
    $sentenciaCuotas->execute([$id_venta]);
    $cuotas = $sentenciaCuotas->fetchAll(PDO::FETCH_OBJ);
    
    $fecha_venta = date('d/m/Y', strtotime($venta->fecha_venta));
    $fecha_finalizacion = $cuotas ? date('d/m/Y', strtotime($cuotas[count($cuotas)-1]->fecha_pago)) : date('d/m/Y');
    $cliente_nombre = trim($venta->nombre_cliente) ?: 'CLIENTE GENÉRICO';
    $total_venta = floatval($venta->total_venta);
    
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Pagaré - Venta #<?php echo $id_venta; ?></title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            @page { size: 58mm auto; margin: 2mm 2mm 10mm 2mm; }
            body {
                font-family: 'Courier New', 'Courier', monospace;
                font-size: 9px;
                line-height: 1.3;
                color: #000;
                background: #f5f5f5;
            }
            @media screen {
                body { display: flex; justify-content: center; align-items: flex-start; padding: 20px; min-height: 100vh; }
                .container { width: 58mm; background: white; box-shadow: 0 0 20px rgba(0,0,0,0.2); padding: 3mm; }
                .btn-imprimir { display: block; }
            }
            @media print {
                body { background: white; padding: 0; margin: 0; }
                .container { width: 58mm; padding: 0; }
                .no-print { display: none !important; }
            }
            .container { width: 58mm; position: relative; }
            .center { text-align: center; }
            .bold { font-weight: bold; }
            .header { margin-bottom: 2mm; }
            .empresa-nombre { font-weight: bold; font-size: 11px; margin-bottom: 1mm; }
            .empresa-info { font-size: 7px; margin-bottom: 0.5mm; line-height: 1.2; }
            .linea { border-top: 1px dashed #000; margin: 2mm 0; }
            .linea-doble { border-top: 2px solid #000; margin: 2mm 0; }
            .seccion-titulo { font-weight: bold; font-size: 11px; margin: 2mm 0; }
            .fila { display: flex; justify-content: space-between; margin: 1mm 0; font-size: 8px; }
            .fila-label { font-size: 7px; }
            .fila-valor { text-align: right; word-wrap: break-word; font-weight: bold; }
            .banner-cancelado {
                background: #000;
                color: #fff;
                padding: 3mm;
                margin: 2mm 0;
                text-align: center;
                font-weight: bold;
                font-size: 12px;
                border-radius: 2mm;
            }
            .tabla-cuotas {
                border: 1px solid #000;
                margin: 2mm 0;
                padding: 2mm;
            }
            .tabla-header {
                display: flex;
                justify-content: space-between;
                font-weight: bold;
                border-bottom: 1px solid #000;
                padding-bottom: 1mm;
                margin-bottom: 1mm;
                font-size: 7px;
            }
            .tabla-row {
                display: flex;
                justify-content: space-between;
                font-size: 7px;
                margin: 0.5mm 0;
            }
            .col-cuota { width: 20%; }
            .col-monto { width: 35%; text-align: right; }
            .col-fecha { width: 45%; text-align: right; }
            .pie { margin-top: 2mm; font-size: 7px; line-height: 1.3; }
            .espacio-corte { height: 15mm; }
            .btn-imprimir {
                position: fixed; top: 10px; right: 10px;
                background: #27ae60; color: white; border: none;
                padding: 12px 24px; border-radius: 8px;
                cursor: pointer; font-weight: bold;
                z-index: 1000; font-size: 14px;
                box-shadow: 0 4px 15px rgba(39, 174, 96, 0.4);
                transition: all 0.3s ease;
            }
            .btn-imprimir:hover { background: #2ecc71; transform: translateY(-2px); }
        </style>
    </head>
    <body>
        <button class="btn-imprimir no-print" onclick="window.print()">🖨️ IMPRIMIR</button>
        
        <div class="container">
            <div class="header center">
                <div class="empresa-nombre">MAGISTER OFFICE</div>
                <div class="empresa-info">CIBER - FOTOCOPIAS - LIBRERIA</div>
                <div class="seccion-titulo">PAGARE</div>
                <div class="empresa-info">CONSTANCIA DE PAGO</div>
            </div>
            
            <div class="linea"></div>
            
            <div class="banner-cancelado">
                *** DEUDA CANCELADA ***
            </div>
            
            <div class="linea"></div>
            
            <div class="fila">
                <div class="fila-label">Venta N°:</div>
                <div class="fila-valor"><?php echo $id_venta; ?> <?php echo $venta->numero_venta ? '(' . $venta->numero_venta . ')' : ''; ?></div>
            </div>
            <div class="fila">
                <div class="fila-label">Cliente:</div>
                <div class="fila-valor"><?php echo $cliente_nombre; ?></div>
            </div>
            <?php if ($venta->ci_ruc_cliente): ?>
            <div class="fila">
                <div class="fila-label">CI/RUC:</div>
                <div class="fila-valor"><?php echo $venta->ci_ruc_cliente; ?></div>
            </div>
            <?php endif; ?>
            
            <div class="linea"></div>
            
            <div class="fila">
                <div class="fila-label">Fecha Venta:</div>
                <div class="fila-valor"><?php echo $fecha_venta; ?></div>
            </div>
            <div class="fila">
                <div class="fila-label">Total Adeudado:</div>
                <div class="fila-valor">₲ <?php echo number_format($total_venta, 0, ',', '.'); ?></div>
            </div>
            <div class="fila">
                <div class="fila-label">Total Cuotas:</div>
                <div class="fila-valor"><?php echo $venta->total_cuotas; ?></div>
            </div>
            <div class="fila">
                <div class="fila-label">Finalizacion:</div>
                <div class="fila-valor"><?php echo $fecha_finalizacion; ?></div>
            </div>
            
            <div class="linea"></div>
            
            <div class="tabla-cuotas">
                <div style="text-align: center; font-weight: bold; font-size: 8px; margin-bottom: 2mm;">
                    DETALLE DE CUOTAS PAGADAS
                </div>
                <div class="tabla-header">
                    <div class="col-cuota">#</div>
                    <div class="col-monto">Monto</div>
                    <div class="col-fecha">F.Pago</div>
                </div>
                <?php foreach ($cuotas as $cuota): ?>
                <div class="tabla-row">
                    <div class="col-cuota"><?php echo $cuota->numero; ?></div>
                    <div class="col-monto">₲<?php echo number_format($cuota->monto, 0, ',', '.'); ?></div>
                    <div class="col-fecha"><?php echo date('d/m/Y', strtotime($cuota->fecha_pago)); ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="linea-doble"></div>
            
            <div class="center bold" style="font-size: 9px; margin: 2mm 0;">
                TODAS LAS CUOTAS HAN SIDO
            </div>
            <div class="center bold" style="font-size: 9px; margin: 2mm 0;">
                CANCELADAS EN SU TOTALIDAD
            </div>
            
            <div class="linea"></div>
            
            <div class="pie center">
                GRACIAS POR SU CONFIANZA
            </div>
            <div class="pie center">
                Tel: 0972-617447
            </div>
            <div class="pie center" style="font-size: 6px; margin-top: 2mm;">
                San Juan Bautista - Misiones
            </div>
            
            <div class="espacio-corte"></div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// ========================================
// FACTURA Y TICKET (CÓDIGO ORIGINAL)
// ========================================
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