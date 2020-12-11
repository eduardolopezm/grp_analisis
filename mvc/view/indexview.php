<!DOCTYPE HTML>
<html lang="es">
	<head>
		<meta chartset="utf-8"/>
		<title>VIDEO EJEMPLO MVC PHP POO PHP victorroblesweb.es</title>
		<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
	</head>
	<body>
		<form action="<?php echo $helper->url("usuarios","crear");?>" method="post" class="col-lg-5">
			<h3>Añadir Usuario</h3>
			<hr/>
			Nombre: <input type="text" name="nombre" class="form-control"/>
			Apellido: <input type="text" name="apellido" class="form-control"/>
			Email: <input type="email" name="email" class="form-control"/>
			Contraseña: <input type="password" name="password" class="form-control"/>
			<input type="submit" value="value" class="btn-success"/>
		</form>

		<section class="col-lg-7">
			<h3>Usuarios</h3>
			<?php 
				foreach($allusers as $user){
					echo $user->nombre . "<hr/>";
				}
			?>
		</section>
	</body>
</html>