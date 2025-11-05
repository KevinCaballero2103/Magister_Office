<?php
/*
	Conexion con la base de datos
	@author KeSe
	@date 2025-09-17
*/
$contraseña = "";
$usuario = "root";
$nombre_base_de_datos = "magister_office";

try {
	$conexion = new PDO(
		'mysql:host=localhost;dbname=' . $nombre_base_de_datos . ';charset=utf8mb4',
		$usuario, 
		$contraseña,
		array(
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
			PDO::ATTR_EMULATE_PREPARES => false
		)
	);
	

	
} catch(Exception $e) {
	// En producción, NO mostrar el error real al usuario
	error_log("Error de conexión BD: " . $e->getMessage());
	die("Error al conectar con la base de datos. Contacte al administrador.");
}
?>