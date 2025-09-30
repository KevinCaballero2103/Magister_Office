<?php
/*
	Conexion con la base de datos
	@author KeSe
	@date 2025-09-17
*/
$contraseña = "";
$usuario = "root";
$nombre_base_de_datos = "magister_office";
try{
	$conexion = new PDO('mysql:host=localhost;dbname=' . $nombre_base_de_datos, $usuario, $contraseña);
}catch(Exception $e){
	echo "Ocurrió algo con la base de datos: " . $e->getMessage();
}
?>