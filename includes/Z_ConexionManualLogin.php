<?

include('MiscFunctions.php');
include('mail.php');

include('LanguageSetup.php');

//Se crea la conexion Manual a la BD
$dbsocket = '/home/mysql/mysql.sock';
$hostlocal = "68.233.228.55";
$dbuserlocal = "desarrollo";
$dbpasswordlocal = "p0rtali70s";
$databaselocal = "erpgubernamental_DES";
$mysqlportlocal = "3306";
$dblocal = mysqli_connect($hostlocal , $dbuserlocal, $dbpasswordlocal,$databaselocal, $mysqlportlocal, $dbsocket);
if (mysqli_connect_errno()) {
	printf("Error al conectarse a la Base de Datos: %s\n", mysqli_connect_error());
	exit();
}else{

	//echo "<br>Conexion BD exitosa";
}

echo '<title>Recuperar Contrase&ntilde;a</title>';
echo '<p align="center"><img src="../images/key_stroke_32x32.gif" height=25" width="25" title="' . _('Recuperar Contrase&ntilde;a') . '" alt="">Recuperar Contrase&ntilde;a<br>';
echo "<form name='formulario' method='post' action=". $_SERVER['PHP_SELF'] . "?". SID . ">";
echo '<div align="center">';


//Metodo de Actualizacion
if(isset($_POST['UserNameEntryField']))
{
	if($_POST['UserNameEntryField'] != "")
			{
			//Optenemos los datos del usuario introducido
			$SQL2 ="SELECT www_users.realname,
							www_users.email,
							www_users.password,
							www_users.userid
					FROM www_users
					WHERE  userid='".$_POST['UserNameEntryField']."' or email = '".$_POST['UserNameEntryField']."'";
			$Result2 =mysqli_query($dblocal,$SQL2);
			$numero_fila = mysqli_num_rows($Result2);
			if($numero_fila == 0)
			{
				echo'<div style="font-size:12px;  color:#FF0404;">Los datos proporcionados son incorrectos..!</div><br/>';
			}
			while ($Row2 = mysqli_fetch_array($Result2)) {
				$name = $Row2['realname'];
				$email = $Row2['email'];
				$password = substr($Row2['password'], 0,6);
				$userid = $Row2['userid'];
				if($numero_fila > 0)
			{
			//Recortamos la contrase–a a 5 caracteres
			$sql= "UPDATE `www_users` SET `password` = '$password' WHERE `userid` = '".$Row2['userid']."'";
			mysqli_query($dblocal,$sql);
			//Se envia el mensaje
				$sql3 ="SELECT confvalue FROM `config`  where confname='SMTP_emailSENDER'";
				$res=mysqli_query($dblocal,$sql3);
				$filas=mysqli_fetch_array($res);
				$emailTo=$email;
				$emailFrom=$filas['confvalue'];
				$emailToName=$name;
				$subject = "Recuperacion de Contrase–a";
				$message = "Se ha restaurado su contrase–a " .".\r\n\r\n
						Servidor: http://erpgubernamental.com \r\n
						Usuario: ".$userid."
						Contrase–a: ".$password."\r\n\r\n
						Le sugerimos modificar su contrase–a dando click en su nombre cuando ingrese al sistema. ";
				$from_name = "Sistema grp";
				$from_mail = $emailFrom;
				$replyto = $from_mail;
				$mail = new Mail();
				$mail->protocol = 'smtp';
				$mail->hostname = 'localhost';
				$mail->port = 25;
				$mail->timeout = 3000;
				$mail->setTo($emailTo);
				$mail->setFrom($from_mail);
				$mail->setSender($from_name);
				$mail->setSubject($subject);
				$mail->setText($message);
				$envio= $mail->send();
				echo'<div style="font-size:12px;  color:#06A100;">Datos de Acceso enviados: '.$emailTo .'</div><br/>';
				//prnMsg('Datos de Acceso enviados:'.$emailTo,'error');
			}
			}
			}
			else
			{
				echo'<div style="font-size:12px;color:#FF0404;">Debe de introducir un nombre o correo electronico..!</div><br/>';
			}
}
		echo "<label>Nombre del Usuario</label><br />";
		echo '<input type="TEXT" name="UserNameEntryField"/><br />';
		echo '<br><input type=submit  name="Procesar" value="' . _('Recuperar') . '">';
		echo '</div>';
		echo '</form>'
?>
