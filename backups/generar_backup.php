<?php
/**
 * SISTEMA DE BACKUPS AUTOMÁTICO
 * Genera dump SQL de la base de datos
 * Se ejecuta: manualmente o por trigger automático
 */

// Configuración
define('BACKUP_DIR', __DIR__);
define('MAX_BACKUPS', 10);
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'magister_office');

// Zona horaria
date_default_timezone_set('America/Asuncion');

/**
 * Detecta la ubicación de mysqldump en Laragon
 */
function detectarMysqldump() {
    $posiblesRutas = [
        'C:/laragon/bin/mysql/mysql-8.0.30/bin/mysqldump.exe',
        'C:/laragon/bin/mysql/mysql-5.7.33/bin/mysqldump.exe',
        'C:/laragon/bin/mysql/mariadb-10.4.27/bin/mysqldump.exe',
        'C:/laragon/bin/mysql/mariadb-10.11.2/bin/mysqldump.exe',
    ];
    
    $mysqlDir = 'C:/laragon/bin/mysql/';
    if (is_dir($mysqlDir)) {
        $carpetas = glob($mysqlDir . '*', GLOB_ONLYDIR);
        foreach ($carpetas as $carpeta) {
            $mysqldump = $carpeta . '/bin/mysqldump.exe';
            if (file_exists($mysqldump)) {
                return $mysqldump;
            }
        }
    }
    
    foreach ($posiblesRutas as $ruta) {
        if (file_exists($ruta)) {
            return $ruta;
        }
    }
    
    exec('where mysqldump 2>nul', $output, $returnCode);
    if ($returnCode === 0 && !empty($output[0])) {
        return trim($output[0]);
    }
    
    return false;
}

/**
 * Genera el backup y retorna información
 */
function generarBackup($manual = false) {
    $dt = new DateTime('now', new DateTimeZone('America/Asuncion'));
    $fecha = $dt->format('Y-m-d_H-i-s');
    $tipo = $manual ? 'manual' : 'automatico';
    $nombreArchivo = "backup_{$tipo}_{$fecha}.sql";
    $rutaCompleta = BACKUP_DIR . '/' . $nombreArchivo;
    
    $mysqldumpPath = detectarMysqldump();
    
    if (!$mysqldumpPath) {
        return [
            'exito' => false,
            'error' => 'No se encontró mysqldump. Verifica la instalación de MySQL/MariaDB en Laragon.'
        ];
    }
    
    $comando = sprintf(
        '"%s" --user=%s --password=%s --host=%s --skip-comments --add-drop-table %s > %s 2>&1',
        $mysqldumpPath,
        DB_USER,
        DB_PASS,
        DB_HOST,
        DB_NAME,
        escapeshellarg($rutaCompleta)
    );
    
    exec($comando, $output, $resultado);
    
    if ($resultado === 0 && file_exists($rutaCompleta) && filesize($rutaCompleta) > 100) {
        $tamano = filesize($rutaCompleta);
        limpiarBackupsAntiguos();
        
        return [
            'exito' => true,
            'archivo' => $nombreArchivo,
            'ruta' => $rutaCompleta,
            'tamano' => $tamano,
            'tamano_legible' => formatearTamano($tamano),
            'fecha' => $dt->format('d/m/Y H:i:s')
        ];
    } else {
        return [
            'exito' => false,
            'error' => 'Error ejecutando mysqldump: ' . implode("\n", $output),
            'comando' => $comando,
            'resultado_code' => $resultado
        ];
    }
}

/**
 * Elimina backups mayores a MAX_BACKUPS días
 */
function limpiarBackupsAntiguos() {
    $archivos = glob(BACKUP_DIR . '/backup_*.sql');
    
    if (count($archivos) <= MAX_BACKUPS) {
        return;
    }
    
    usort($archivos, function($a, $b) {
        return filemtime($a) - filemtime($b);
    });
    
    $aEliminar = count($archivos) - MAX_BACKUPS;
    for ($i = 0; $i < $aEliminar; $i++) {
        if (file_exists($archivos[$i])) {
            unlink($archivos[$i]);
        }
    }
}

/**
 * Verifica si es necesario hacer backup automático
 * (Llamar desde login.php o index.php)
 */
function verificarBackupAutomatico() {
    $archivoControl = BACKUP_DIR . '/ultimo_backup.txt';
    
    $dt = new DateTime('now', new DateTimeZone('America/Asuncion'));
    $ahora = $dt->getTimestamp();
    
    if (file_exists($archivoControl)) {
        $contenido = trim(file_get_contents($archivoControl));
        
        if (!empty($contenido) && is_numeric($contenido)) {
            $ultimoBackup = intval($contenido);
            
            $timestamp2020 = 1577836800;
            $timestamp2030 = 1893456000;
            
            if ($ultimoBackup < $timestamp2020 || $ultimoBackup > $timestamp2030) {
                error_log("BACKUP ERROR - Timestamp fuera de rango válido");
                @unlink($archivoControl);
            } else {
                $segundosTranscurridos = $ahora - $ultimoBackup;
                $horasTranscurridas = $segundosTranscurridos / 3600;
                
                if ($horasTranscurridas < 24) {
                    return false;
                }
            }
        } else {
            @unlink($archivoControl);
        }
    }
    
    $resultado = generarBackup(false);
    
    if ($resultado['exito']) {
        @file_put_contents($archivoControl, $ahora);
        error_log("Backup automático generado: " . $resultado['archivo']);
    }
    
    return $resultado;
}

/**
 * Listar todos los backups disponibles
 */
function listarBackups() {
    $archivos = glob(BACKUP_DIR . '/backup_*.sql');
    $backups = [];
    
    foreach ($archivos as $archivo) {
        $nombre = basename($archivo);
        $timestamp = filemtime($archivo);
        $dt = new DateTime('@' . $timestamp);
        $dt->setTimezone(new DateTimeZone('America/Asuncion'));
        
        $backups[] = [
            'nombre' => $nombre,
            'ruta' => $archivo,
            'tamano' => filesize($archivo),
            'tamano_legible' => formatearTamano(filesize($archivo)),
            'fecha' => $dt->format('d/m/Y H:i:s'),
            'timestamp' => $timestamp,
            'tipo' => strpos($nombre, 'manual') !== false ? 'Manual' : 'Automático'
        ];
    }
    
    usort($backups, function($a, $b) {
        return $b['timestamp'] - $a['timestamp'];
    });
    
    return $backups;
}

/**
 * Formatear tamaño de archivo
 */
function formatearTamano($bytes) {
    $unidades = ['B', 'KB', 'MB', 'GB'];
    $i = 0;
    
    while ($bytes >= 1024 && $i < count($unidades) - 1) {
        $bytes /= 1024;
        $i++;
    }
    
    return round($bytes, 2) . ' ' . $unidades[$i];
}

/**
 * Descargar backup
 */
function descargarBackup($nombreArchivo) {
    $rutaArchivo = BACKUP_DIR . '/' . basename($nombreArchivo);
    
    if (!file_exists($rutaArchivo)) {
        return ['exito' => false, 'error' => 'Archivo no encontrado'];
    }
    
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($rutaArchivo) . '"');
    header('Content-Length: ' . filesize($rutaArchivo));
    header('Cache-Control: no-cache, must-revalidate');
    
    readfile($rutaArchivo);
    exit;
}

if (php_sapi_name() === 'cli' || (isset($_GET['accion']) && $_GET['accion'] === 'generar_manual')) {
    $resultado = generarBackup(true);
    
    if (php_sapi_name() === 'cli') {
        echo $resultado['exito'] ? "Backup generado: {$resultado['archivo']}\n" : "Error: {$resultado['error']}\n";
    } else {
        echo json_encode($resultado);
    }
    exit;
}
?>
