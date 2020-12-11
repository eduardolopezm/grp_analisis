<?php 
/**
 * @date: 26/12/2017
 * @package: ap_grp
 * @version 0.1
 *
 * DESCRIPCION: Programa principal para la ejecucion de las funciones según su llamada
 * permitiendo validar la existencia de las mismas dentro de una clase, ademas de permitir
 * la adopcion del uso de clases dentro del sistema, dando como resultado un codigo mas limpio
 * y orgaizado.
 * 
 */
session_start();
/* DECLARACION DE VARIABLES */
$PageSecurity = 1;
$PathPrefix = './';
$funcion=2244;
/* INCLUCION DE ARCHIVOS NECESARIOS */
include($PathPrefix . 'config.php');
include($PathPrefix . 'includes/ConnectDB.inc');
include($PathPrefix . 'includes/SecurityFunctions.inc');
include($PathPrefix . 'includes/SQL_CommonFunctions.inc');
/* EXTRACCION DE INFORMACION */
$method = empty($_POST['method'])? 'error' : $_POST['method']; // metodo o funcion
$params = empty($_POST['params'])? array() : $_POST['params']; // paramatros de la funcion
// TODO: generar la inclucion de prefigos para sub carpetas
# se evalua que coloquen la clase
if(!empty($_POST['class'])){
	# si la clase es imcluida se asigna a una variable
	$inc = $_POST['class'];
	# inclucion del archivo a ejecutar
	include_once './modelo/'.$inc.'.php';
	# ceacion de instancia
	$class = new $inc($db);
	# creacion de estructura de llamada
	$llamada = array($class,$method);
	# comprobacion de la llamada
	if(is_callable($llamada)){
		# si existe la funcion se ejecuta y se asigna la respuesta
		$respuesta = call_user_func_array([$class,$method], $params);
	}else{
		# si no existe la llamada se crea la instancia de master y se ejecuta
		$master = new masterController();
		$respuesta = call_user_func_array([$master,'error'], $params);
	}
}else{
	// si no se inlculle la clase se crea la instancia de master y se manda un error
	$master = new masterController();
	$respuesta = call_user_func_array([$master,'error'], $params);
}
/* RESPUESTA DE EJECUCION */
echo $respuesta;

/**
 * clase base para la generacion de respuestas y mensajes de error
 */
class masterController
{
	/**
	 * Funcion para el envio de mensajes de error, en caso de que no se coloque un mensaje
	 * se enviara uno por defecto.
	 * @param  string $msg Mensaje que sera enviado
	 * @return JSON      Mensaje
	 */
	public function error($msg='')
	{
		#construccion de estructura base
		$data = ['success'=>false,'msg'=>''];
		# asignacion de mensaje
		$data['msg'] = empty($msg)? "La función solicitada no puede ejecutarse. Contactar con el administrador." : $msg;
		# emicion de mensaje
		return $this->response($data);
	}

	/**
	 * Funcion para el emvio de respuesta estandar en formato JSON
	 * Si no es parado nada como data se genera un mensaje de error.
	 * @param  string $data Informacion que desea ser enviada.
	 * @return JSON       Datos
	 */
	public function response($data='')
	{
		if(empty($data)){ $data = $this->error(); }
		header('Content-type:application/json;charset=utf-8');
		return json_encode($data);
	}
}