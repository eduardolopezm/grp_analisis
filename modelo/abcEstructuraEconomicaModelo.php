<?php
/**
 * AbcGeneral.php
 * @category Modelo
 * @package  ap_grp/modelo
 * @author 	 JP
 * @version  1.0.0
 * @date: 	 10.03.18
 *
 * Programa para afectación de paneles, la captura y administración de la información de los
 * catálogos.
 */
session_start();
$PageSecurity = 1;
$PathPrefix = '../';
include($PathPrefix . 'config.php');
include($PathPrefix . 'includes/SecurityUrl.php');// extrae el numero de función
# extracción de función
// $funcion = 2338;
$funcion = 2353; //la función estaba apuntando a 2352
include($PathPrefix . 'includes/ConnectDB.inc');
include($PathPrefix.'includes/SecurityFunctions.inc');
include($PathPrefix.'includes/SQL_CommonFunctions.inc');

/*********************************************** CONSTANSTANTES ***********************************************/
define('DECPLACE', !empty($_SESSION['DecimalPlaces'])? $_SESSION['DecimalPlaces'] : 2);
define('LNTBLCAT', 'tb_cat_estructura_economica');
define('ACTIVECOL', 'ind_activo');
define('DATA', [
	'partida'=>['col'=>'id_nu_partida','tipo'=>'string'],
	'tg'=>['col'=>'id_nu_tg','tipo'=>'string'],
	'ff'=>['col'=>'id_nu_ff','tipo'=>'string'],
	'identificador'=>['col'=>'id_nu_estructura_economica','tipo'=>'string']
]);
define('CONCAT', "CONCAT(`id_nu_partida`,'-',`id_nu_tg`,'-',`id_nu_ff`)");
/*********************************************** DESARROLLO DE FUNCIONES ***********************************************/
/**
 * Función para l guardado de la información de los items generados
 * @param  [DBInstance] $db 	Instancia de la base de datos
 * @return [Array]      $data	Arreglo con la respuesta verdadera o falsa según lo obtenido
 */
function store($db)
{
	// declaracion de variables de la función
	$data = ['success'=>false,'msg'=>'Ocurrió un incidente inesperado. Favor de contactar con el administrador.'];
	$info = $_POST;
	$valid = empty($info['valid']);
	unset($info['method']);
	unset($info['valid']);
	$clave = implode('-',$info);
	# llamado de la funcion de actualización
	if(updateWhenInserting($db, $info)){
		$data['msg'] = "Se registro con exito la clave <strong>$clave</strong>";
		$data['content'] = show($db)['content'];
		$data['success'] = true;
		return $data;
	}
	// comprobacion de existencia
	if(compruebaClaveEconomica($db, $clave) && !$valid){
		$data['msg'] = "La clave $clave ya se encuentra registrada.";
		$data['content'] = show($db)['content'];
		return $data;
	}
	# generación de registros
	DB_Txn_Begin($db);
	try {
		// obtención del query para la inserción
		$sqlIn = obtenInsercion($info);
		$resultIn = DB_query($sqlIn, $db);
		// compribación de éxito de la generación de la información
		if($resultIn == true){
			$data['success'] = true;
			$data['msg'] = "Se registro con exito la clave <strong>$clave</strong>";
			$data['content'] = show($db)['content'];
			DB_Txn_Commit($db);
		}else{
			DB_Txn_Rollback($db);
		}
	} catch (Exception $e) {
		// captura del error
		$data['msg'] .= '<br>'.$e->getMessage();
		DB_Txn_Rollback($db);
	}
	return $data;
}
/**
 * Función para la busqueda de la información que llenara la tabla prinsipal
 * @param  [DBInstance] $db 	Instancia de la base de datos
 * @return [Array]      $data	Arreglo con la respuesta verdadera o falsa según lo obtenido
 */
function show($db)
{
	// declaraciónd e variables de la función
	$data = ['success'=>false,'msg'=>'No se encontraron datos de la estructura economica','content'=>[]];
	$sql = "SELECT `id_nu_estructura_economica` as id,`id_nu_partida` as partida,`id_nu_tg` as tg,`id_nu_ff` as ff 
		,concat(`id_nu_partida`,'-',`id_nu_tg`,'-',`id_nu_ff`) as clave FROM ".LNTBLCAT." WHERE `".ACTIVECOL."`='1' ORDER BY `id_nu_partida`,`id_nu_tg`,`id_nu_ff`";
	$result = DB_query($sql, $db);
	// procesamiento de la información obtenida
	while ($rs = DB_fetch_array($result)) {
		$data['content'][] = [
            'partida'=>$rs['partida'],// 0
            'tg'=>$rs['tg'],// 1
            'ff'=>$rs['ff'],// 2
            'identificador'=>$rs['id'],// 3
            'descripcion'=>$rs['clave'],// 4
			'eliminar'=>'<span class="eliminar glyphicon glyphicon-trash"></span>',
			'modificar'=>'<span class="modificar glyphicon glyphicon-edit"></span>'
		];

	}
	$data['success'] = true;
	// retorno de la información
	return $data;
}
/**
 * Función de obtención de información por item espesificado
 * @param  [DBInstance] $db 	Instancia de la base de datos
 * @return [Array]      $data	Arreglo con la respuesta verdadera o falsa según lo obtenido
 */
function edit($db)
{
	// declaración de variables de la función
	$data = ['success'=>false, 'msg'=>'Ocurrió un incidente al obtener la información. Favor de contactar al administrador.'];
	$info = $_POST;
	$sql = "SELECT * FROM ".LNTBLCAT." WHERE `".ACTIVECOL."`='1' AND `".DATA['identificador']['col']."` = '".$info['identificador']."' ";
	$result = DB_query($sql, $db);
	// comprobación de existencia de la información
	if(DB_num_rows($result) == 0){
		$data['msg'] = 'No se encontraron los datos solicitados.';
		return $data;
	}
	// prcesamiento de la información obtenida
	while ($rs = DB_fetch_array($result)) {
		$content = [];
		foreach (DATA as $campo => $columna) { $content[$campo] = convierteDato($rs, $columna); }
		$data['content'] = $content;
	}
	$data['success'] = true;
	// retorno de la información
	return $data;
}
/**
 * Función de actualización de los datos de un item espesifico
 * @param  [DBInstance] $db 	Instancia de la base de datos
 * @return [Array]      $data	Arreglo con la respuesta verdadera o falsa según lo obtenido
 */
function update($db)
{
	// declaración de variables de la función
	$data = ['success'=>false,'msg'=>'Ocurrió un incidente al actualizar la información. Favor de contactar al administrador'];
	$info = $_POST;
	$identificador = $info['identificador'];
	unset($info['method']);
	unset($info['valid']);
	unset($info['identificador']);
	$data['content'] = show($db)['content'];
	$clave = obtenDescripcion($info);
	$sql = "SELECT * FROM ".LNTBLCAT." WHERE `".ACTIVECOL."`='1' AND `".DATA['identificador']['col']."` = '".$identificador."' ";
	$result = DB_query($sql, $db);
	// comprobación de consistencia de datos
	if(DB_num_rows($result) == 0){
		$data['msg'] = 'No se encontraron los datos solicitados.';
		return $data;
	}
	# comprobacion de existencia como inactivo
	$registroInactivo = compruebaClaveEconomicaInactivas($db, $clave);
	// comprobacion de existencia
	if(compruebaClaveEconomica($db, $clave) && !$registroInactivo){
		$data['msg'] = "La clave $clave ya se encuentra registrada.";
		$data['content'] = show($db)['content'];
		return $data;
	}
	# retorno de mensaje según ejecución de precondición
	if(!exePreReq($db, $identificador)){
		$data['msg'] = "No puede modificar la estructura ya que se encuentra utilizada en el presupuesto";
		return $data;
	}
	# activacion de la existencia e inactivacion del registro viejo
	if($registroInactivo){
		activarInactivar($db, $info, $identificador);
		$data['msg'] = "Se realizaron las actualizaciones necesarias";
		$data['success'] = true;
		$data['content'] = show($db)['content'];
		return $data;
	}
	# reasignación de identificador al objeto general
	$info['identificador'] = $identificador;
	# desarrollo de la transacción de eliminación
	DB_Txn_Begin($db);
	try {
		// obtención de la cadena de ejecución para la actualización
		$sqlUpdate = obtenUpdate($info);
		$resultUpdate = DB_query($sqlUpdate, $db);
		// comprobación de éxito de la información
		if($resultUpdate==true){
			$data['msg'] = "Se realizaron las actualizaciones necesarias";
			$data['success'] = true;
			DB_Txn_Begin($db);
			$data['content'] = show($db)['content'];
		}else{
			DB_Txn_Rollback($db);
		}
	} catch (Exception $e) {
		// captura del error
		$data['msg'] .= '<br>'.$e->getMessage();
		DB_Txn_Rollback($db);
	}
	// retorno de la información
	return $data;
}
/**
 * Función para la eliminación logica un item espesifico
 * @param  [DBInstance] $db 	Instancia de la base de datos
 * @return [Array]      $data	Arreglo con la respuesta verdadera o falsa según lo obtenido
 */
function destroy($db)
{
	// declaración de variables de la función
	$data = ['success'=>false,'msg'=>'Ocurrió un incidente al momento de eliminar la información.'];
	$info = $_POST;
	$data['content'] = show($db)['content'];
	# comprobación de identificador
	if(empty($info['identificador'])){
		$data['msg'] = 'Es necesario indicar el identificador del registro';
		return $data;
	}
	# retorno de mensaje según ejecución de precondición
	if(!exePreReq($db, $info['identificador'])){
		$data['msg'] = "No puede eliminar la estructura ya que se encuentra utilizada en el presupuesto";
		return $data;
	}
	# desarrollo de la transacción de eliminación
	DB_Txn_Begin($db);
	try {
		$sql = "UPDATE ".LNTBLCAT." SET `".ACTIVECOL."` = 0
			WHERE `".DATA['identificador']['col']."` = '".$info['identificador']."'
			AND `".ACTIVECOL."` = 1 ";
		$result = DB_query($sql, $db);
		// comprobación de éxito de la ejecución
		if($result==true){
			$data['msg'] = "Se elimino con éxito la estructura";
			$data['success'] = true;
			DB_Txn_Commit($db);
			$data['content'] = show($db)['content'];
		}else{
			DB_Txn_Rollback($db);
		}
	} catch (Exception $e) {
		// captura del error
		$data['msg'] .= '<br>'.$e->getMessage();
		DB_Txn_Rollback($db);
	}
	// retorno de la información
	return $data;
}
/*********************************************** DESARROLLO DE FUNCIONES ***********************************************/

/* EJECUCION DE FUNCIONES */
$data = call_user_func_array($_POST['method'],[$db]);
/* MODIFICACION DE HEADER */
header('Content-type:application/json;charset=utf-8');
/* ENVIO DE INFORMACIÓN */
echo json_encode($data);

/*********************************************** FUNCIONES UTILES ***********************************************/
function obtenFuncionProgramatica($db)
{
	$data = ['succes'=>false,'content'=>[['label'=>'Seleccione una opción', 'value'=>'0']]];
	$info = $_POST; $condicion = '';
	$condicion .= (!empty($info['identificador'])? " AND `id_finalidad`='".$info['identificador']."' ":'');
	$sql="SELECT `id_funcion` as id, CONCAT(`id_funcion`,' - ',`desc_fun`) as text FROM g_cat_funcion WHERE `activo`=1 ".$condicion;
	$result = DB_query($sql, $db);
	if(DB_num_rows($result)!=0){ $data['succes']=true; }
	while ($rs = DB_fetch_array($result)) {
		$data['content'][] = ['label'=>utf8_encode($rs['text']), 'value'=>$rs['id']] ;
	}
	return $data;
}

/**
 * Función para la obtención del sql para genrar datos o items
 * @param  Array $info Datos a ser procesados
 * @return String      Sql para la ejecución de la generación de datos
 */
function obtenInsercion($info)
{
	$campos = ''; $datos = ''; $flag = 0; $strData = ['string','select'];
	foreach ($info as $input => $valor) {
		if(!array_key_exists($input, DATA)){ continue; }
		if($flag!=0){ $campos .= ', '; $datos .= ', '; }
		$data  = DATA[$input];
		$campos .= $data['col'];
		$datos .= in_array($data['tipo'], $strData)? utf8_decode(" '$valor'") : " $valor";
		$flag++;
	}
	# agregado de campo activo
	$campos .= ", ".ACTIVECOL; $datos .= ", '1'";
	return " INSERT INTO ".LNTBLCAT." ($campos) VALUES($datos) ";
}

function convierteDato($resulset, $detColumna)
{
	if($detColumna['tipo'] == 'string'){
		return utf8_encode($resulset[$detColumna['col']]);
	}else if($detColumna['tipo'] == 'decimal'){
		return number_format($resulset[$detColumna['col']], DECPLACE, '.');
	}else if($detColumna['tipo'] == 'date'){
		// @TODO: pendiente de colocar
	}
	# retorno de dato numerico
	return $resulset[$detColumna['col']];
}

function obtenUpdate($info)
{
	$campos = ''; $flag = 0; $identificador = $info['identificador']; $iterador = DATA;
	unset($info['identificador']);
	unset($iterador['identificador']);
	foreach ($info as $input => $valor) {
		if(!array_key_exists($input, $iterador)){ continue; }
		if($flag!=0){ $campos .= ', '; }
		$data  = DATA[$input];
		$campos .= $data['col']." = ".($data['tipo']=='string'? utf8_decode(" '$valor'") : " $valor");
		$flag++;
	}
	return " UPDATE ".LNTBLCAT." SET $campos WHERE `".ACTIVECOL."`='1' AND `".DATA['identificador']['col']."` = '".$identificador."' ";
}

function exePreReq($db, $identificador)
{
	$sqlCampos = "select `campoPresupuesto` as dato, `nu_programatica_orden` from budgetConfigClave where `nu_economica` = 1";
	$resultCampos = DB_query($sqlCampos, $db);
	$campos = ""; $cantidad = DB_num_rows($resultCampos);
	while ($rs = DB_fetch_array($resultCampos)) {
		if($cantidad == 1){ $campos .= $rs['dato']; }
		else{ $campos .= $rs['dato'].",'-', "; }
		$cantidad--;
	}
	# consulta de clave
	$sqlCalve = "select concat(`id_nu_partida`,'-',`id_nu_tg`,'-',`id_nu_ff`) as clave from `tb_cat_estructura_economica` where `id_nu_estructura_economica` = '$identificador'";
	$resultClave = DB_query($sqlCalve, $db);
	$clave = DB_fetch_array($resultClave)['clave'];
	# consulta de existencia
	$sql = "select concat($campos) from chartdetailsbudgetbytag where concat($campos) = '$clave' ";
	$result = DB_query($sql, $db);

	return (DB_num_rows($result) == 0);
}

function updateWhenInserting($db, $info)
{
	DB_Txn_Begin($db);
	$descripcion = obtenDescripcion($info);
	$preSql = "SELECT * FROM ".LNTBLCAT." WHERE `".ACTIVECOL."` = 0 AND ".CONCAT." = '".$descripcion."' ";
	$preResult = DB_query($preSql, $db);
	if(DB_num_rows($preResult) == 0){ return false; }

	try {
		$sql = "UPDATE ".LNTBLCAT." SET `".ACTIVECOL."` = 1 WHERE ".CONCAT." = '".$descripcion."' ";
		$result = mysqli_query($db, $sql);
		DB_Txn_Commit($db);
		return true;
	} catch (Exception $e) {
		// captura del error
		DB_Txn_Begin($db);
	}
	return false;
}

function obtenDescripcion($datos)
{
	return implode('-', $datos);
}

function compruebaClaveEconomicaInactivas($db, $clave)
{
	$sql = "SELECT CONCAT(`id_nu_partida`,'-',`id_nu_tg`,'-',`id_nu_ff`) 
		FROM `tb_cat_estructura_economica` WHERE `ind_activo`='0' AND CONCAT(`id_nu_partida`,'-',`id_nu_tg`,'-',`id_nu_ff`) = '$clave'";
	$result = DB_query($sql, $db);
	return DB_num_rows($result);
}

function activarInactivar($db, $info, $identificador)
{
	# activo el elemento deceado
	updateWhenInserting($db, $info);
	# se inactiva el elemento no deseado
	DB_Txn_Begin($db);
	try {
		$sql = "UPDATE ".LNTBLCAT." SET `".ACTIVECOL."` = 0 WHERE `".DATA['identificador']['col']."` = '$identificador' ";
		$result = mysqli_query($db, $sql);
		DB_Txn_Commit($db);
		return true;
	} catch (Exception $e) {
		// captura del error
		DB_Txn_Begin($db);
	}
	return false;
}