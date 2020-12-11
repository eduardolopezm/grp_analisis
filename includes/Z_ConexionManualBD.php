<?
	$hostlocal = "localhost";
	$dbuserlocal = "root";
	$dbpasswordlocal = "p0rtali70s";
	$databaselocal = "erpprodeqro_CAPA";
	$mysqlportlocal = "3306";
	
	$dblocal = mysqli_connect($hostlocal , $dbuserlocal, $dbpasswordlocal,$databaselocal, $mysqlportlocal);
	
	if (mysqli_connect_errno()) {
		printf("Error al conectarse a la Base de Datos: %s\n", mysqli_connect_error());
		exit();
	}else{
		//echo "<br>Conexion BD exitosa";
			
	}
?>