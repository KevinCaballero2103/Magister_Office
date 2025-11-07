<?php
/**
 * SISTEMA DE BACKUPS AUTOMÁTICO
 * Genera dump SQL de la base de datos
 * Se ejecuta: manualmente o por trigger automático
 */

// Configuración
define('BACKUP_DIR', __DIR__); // Esta misma carpeta
define('MAX_BACKUPS', 7); // Mantener últimos 7 backups
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'magister_office');

// Zona horaria
date_default_timezone_set('America/Asuncion');

/**
 * Genera el backup y retorna información
 */
function generarBackup($manual = false) {
    $fecha = date('Y-m-d_H-i-s');
    $tipo = $manual ? 'manual' : 'automatico';
    $nombreArchivo = "backup_{$tipo}_{$fecha}.sql";
    $rutaCompleta = BACKUP_DIR . '/' . $nombreArchivo;
    
    // Comando mysqldump (Laragon lo tiene disponible)
    $comando = sprintf(
        'mysqldump --user=%s --password=%s --host=%s %s > %s 2>&1',
        DB_USER,
        DB_PASS,
        DB_HOST,
        DB_NAME,
        escapeshellarg($rutaCompleta)
    );
    
    // Ejecutar
    exec($comando, $output, $resultado);
    
    if ($resultado === 0 && file_exists($rutaCompleta)) {
        $tamano = filesize($rutaCompleta);
        
        // Limpiar backups antiguos
        limpiarBackupsAntiguos();
        
        return [
            'exito' => true,
            'archivo' => $nombreArchivo,
            'ruta' => $rutaCompleta,
            'tamano' => $tamano,
            'tamano_legible' => formatearTamano($tamano),
            'fecha' => date('d/m/Y H:i:s')
        ];
    } else {
        return [
            'exito' => false,
            'error' => 'Error ejecutando mysqldump: ' . implode("\n", $output),
            'comando' => $comando // Para debug
        ];
    }
}

/**
 * Elimina backups mayores a MAX_BACKUPS días
 */
function limpiarBackupsAntiguos() {
    $archivos = glob(BACKUP_DIR . '/backup_*.sql');
    
    if (count($archivos) <= MAX_BACKUPS) {
        return; // No hay que eliminar nada
    }
    
    // Ordenar por fecha (más antiguos primero)
    usort($archivos, function($a, $b) {
        return filemtime($a) - filemtime($b);
    });
    
    // Eliminar los más antiguos
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
    
    // Leer última fecha de backup
    if (file_exists($archivoControl)) {
        $ultimoBackup = file_get_contents($archivoControl);
        $horasTranscurridas = (time() - intval($ultimoBackup)) / 3600;
        
        if ($horasTranscurridas < 24) {
            return false; // No es necesario aún
        }
    }
    
    // Generar backup automático
    $resultado = generarBackup(false);
    
    if ($resultado['exito']) {
        // Actualizar archivo de control
        file_put_contents($archivoControl, time());
        
        // Registrar en log (opcional)
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
        $backups[] = [
            'nombre' => $nombre,
            'ruta' => $archivo,
            'tamano' => filesize($archivo),
            'tamano_legible' => formatearTamano(filesize($archivo)),
            'fecha' => date('d/m/Y H:i:s', filemtime($archivo)),
            'timestamp' => filemtime($archivo),
            'tipo' => strpos($nombre, 'manual') !== false ? 'Manual' : 'Automático'
        ];
    }
    
    // Ordenar por fecha (más recientes primero)
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

// Si se ejecuta directamente (backup manual desde panel)
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