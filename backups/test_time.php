<?php
date_default_timezone_set('America/Asuncion');

echo "<h1>Diagnóstico de Timestamps</h1>";

echo "<h2>Información del Sistema</h2>";
echo "<strong>PHP Version:</strong> " . phpversion() . "<br>";
echo "<strong>Zona horaria PHP:</strong> " . date_default_timezone_get() . "<br>";
echo "<strong>Zona horaria del sistema:</strong> " . @date('e') . "<br>";

echo "<h2>Timestamps</h2>";
echo "<strong>time():</strong> " . time() . "<br>";
echo "<strong>Convertido a fecha:</strong> " . date('Y-m-d H:i:s', time()) . "<br>";
echo "<strong>Fecha actual (date):</strong> " . date('Y-m-d H:i:s') . "<br>";
echo "<strong>strtotime('now'):</strong> " . strtotime('now') . "<br>";

echo "<h2>Cálculo Manual</h2>";
$dt = new DateTime('now', new DateTimeZone('America/Asuncion'));
echo "<strong>DateTime->getTimestamp():</strong> " . $dt->getTimestamp() . "<br>";
echo "<strong>DateTime->format():</strong> " . $dt->format('Y-m-d H:i:s') . "<br>";

echo "<h2>Comparación</h2>";
$correcto = 1731198000; // Aprox. 2025-11-09 23:00:00
$tuValor = time();
$diferencia = $tuValor - $correcto;
$años = round($diferencia / (365 * 24 * 3600), 1);

echo "<strong>Timestamp correcto esperado:</strong> ~$correcto<br>";
echo "<strong>Tu timestamp actual:</strong> $tuValor<br>";
echo "<strong>Diferencia:</strong> $diferencia segundos<br>";
echo "<strong>Diferencia en años:</strong> $años años<br>";

if ($años > 5) {
    echo "<p style='color:red; font-weight:bold;'>⚠️ ERROR CRÍTICO: El reloj del sistema está " . abs($años) . " años " . ($años > 0 ? "adelantado" : "atrasado") . "</p>";
}
?>