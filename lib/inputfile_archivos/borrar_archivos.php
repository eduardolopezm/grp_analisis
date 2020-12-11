<?php
	//obtenemos el archivo
	parse_str(file_get_contents("php://input"),$datosDELETE);
	//guardamos su clave para borrarla
	$key = $datosDELETE['key'];
	unlink($key);
	echo 0;
?>