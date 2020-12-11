<?
  error_reporting(E_ALL);
  ini_set('display_errors', '1');
//Librerías para el envío de mail
include_once('includes/phpmailer/class.phpmailer.php');
include_once('includes/phpmailer/class.smtp.php');
 
//Recibir todos los parámetros del formulario
$para = $_POST['email'];
$asunto = $_POST['asunto'];
$mensaje = $_POST['mensaje'];
$archivo = $_FILES['hugo'];
 
//Este bloque es importante
$mail = new PHPMailer();
$mail->IsSMTP();
$mail->SMTPDebug  = 1;
$mail->SMTPAuth = true;
$mail->SMTPSecure = "tls";
$mail->Host = "smtp.gmail.com";
$mail->Port = 587;
$mail->From = "fsagarpa.desarrollo@gmail.com";
$mail->FromName = "SAGARPA PRUEBA DESDE LOCALHOST ADRIAN";
 
//Nuestra cuenta
$mail->Username ='sagarpa.desarrollo@gmail.com';
$mail->Password = 'lxskeiaansyrkkri'; //Su password
 
//Agregar destinatario
$mail->AddAddress($para);
$mail->Subject = $asunto;
$mail->Body = $mensaje;
//Para adjuntar archivo
$mail->AddAttachment($archivo['tmp_name'], $archivo['name']);
$mail->MsgHTML($mensaje);
 
//Avisar si fue enviado o no y dirigir al index
if($mail->Send())
{
    echo'OK';
}
else{
    echo'error';
}
?>
