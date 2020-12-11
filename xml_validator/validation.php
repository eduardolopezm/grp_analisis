<!DOCTYPE html>
<html lang="en">
<head>
	<title>Validador</title>
	<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
	<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap-theme.min.css">
	<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
</head>

<body>

<div class="container">
	
	<h1>Validador XML</h1>
	<form action="" method="post" enctype="multipart/form-data" class="form-horizontal" role="form">
		<input type="file" name="xml" id="xml" /><br />
		<button type="submit" class="btn btn-lg btn-primary">Enviar Consulta</button>
	</form>

<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
include 'lib/Main.class.php';

if (empty($_FILES) == false) {
	
	$xmlData = $_FILES['xml'];
	$allowed = array('xml');
	$filename = $xmlData['name'];
	$path = "temp/";
	$ext = pathinfo($filename, PATHINFO_EXTENSION);
	
	if (in_array($ext, $xmlData)) {

		$tmpName = $xmlData["tmp_name"];
		
		if (move_uploaded_file($tmpName, $path . $filename)) {
			
			$db = mysqli_connect("erpdesarrollo.portalito.com", "root", "pr*mysql013", "hidalgo_DES");
			
			$manager = Main::getValidationManager($path, $filename, $db);
			$manager->validate();
			$errors = $manager->getErrors();
			echo "<p><pre>" . htmlentities($manager->getXmlString()) . "</pre></p>";
			if (empty($errors) == false) {
				foreach ($errors as $error) {
					print $error->toHtml();
				}
			} else {
				echo "<div class='alert alert-success'>El XML es valido</div>";
			}
		}
	}
}
?>
</div>
</body>
</html>