<?php
/**
 * matriz_pagado.php
 * 
 * @category	Modelo
 * @package		ap_grp/modelo
 * @author		Juan José Ledesma Cárdenas
 * @version		1.0.0
 * @date:		11.11.19
 *
 * Programa para afectación de páneles, captura y administración de la información de los catálogos.
 * 
 */
session_start();

/* DECLARACIÓN DE VARIABLES */
$PageSecurity = 11;
$PathPrefix = '../';
$funcion = 2506;

/* INCLUSIÓN DE ARCHIVOS NECESARIOS */
include($PathPrefix . 'config.php');
include($PathPrefix . 'includes/SecurityUrl.php');
include($PathPrefix . 'includes/ConnectDB.inc');
include($PathPrefix . 'includes/SecurityFunctions.inc');
include($PathPrefix . 'includes/SQL_CommonFunctions.inc');

/*********************************************** CONSTANTES ***********************************************/
define('DECPLACE', !empty($_SESSION['DecimalPlaces'])? $_SESSION['DecimalPlaces'] : 2);
define('LNTBLCAT', 'tb_matriz_conv_ingresos');
define('ACTIVECOL', 'ind_activo');
define('DATA', [
		'categoryid' => ['col' => 'categoryid', 'tipo' => 'string'],
		'categorydescription' => ['col' => 'categorydescription', 'tipo' => 'string'],
		'stocktype' => ['col' => 'stocktype', 'tipo' => 'string'],
		'stockact' => ['col' => 'stockact', 'tipo' => 'string'],
		'wipact' => ['col' => 'wipact', 'tipo' => 'string'],
		'allowNarrativePOLine' => ['col' => 'allowNarrativePOLine', 'tipo' => 'string'],
		'accounttransfer' => ['col' => 'accounttransfer', 'tipo' => 'string'],
		'accountegreso' => ['col' => 'accountegreso', 'tipo' => 'string'],
		'nu_tipo_gasto' => ['col' => 'nu_tipo_gasto', 'tipo' => 'string'],
		'ln_clave' => ['col' => 'ln_clave', 'tipo' => 'string'],
		'ind_activo' => ['col' => 'ind_activo', 'tipo' => 'string'],
		'identificador'=>['col'=>'id','tipo'=>'string'],
		'prodLineId' => ['col' => 'prodLineId', 'tipo' => 'string'],
		'idflujo' => ['col' => 'idflujo', 'tipo' => 'string'],
		'cashdiscount' => ['col' => 'cashdiscount', 'tipo' => 'string']
]);
/*********************************************** DESARROLLO DE FUNCIONES ***********************************************/
/**
 * Función para la busqueda de la información que llenará la tabla principal
 * @param	[DBInstance]	$db		Instancia de la base de datos
 * @return	[Array]			$data	Arreglo con la respuesta verdadera o falsa, según lo obtenido
 */
function show($db){
	// declaración de variables de la función
	$info = $_POST['info'];
	$data = ['success'=>false,'msg'=>$info,'content'=>[]];
	$sql = "SELECT categoryid,
				categorydescription,
				stocktype,
				stockact,
				adjglact,
				purchpricevaract,
				materialuseagevarac,
				wipact,
				adjglacttransf,
				allowNarrativePOLine,
				margenaut,
				stockcategory.prodLineId,
				ProdLine.Description,
				redinvoice,
				prdflujos.flujo AS flujo,
				disabledprice,
				internaluse,
				cashdiscount,
				minimummarginsales,
				warrantycost,
				CASE WHEN deductibleflag = 1 THEN 'SI' ELSE 'NO' END AS deductibleflag,
				stockcategory.u_typeoperation,
				accountingtransactiontype.typeoperation,
				IFNULL(typeoperationdiot.typeoperation,'') AS typeoperationdiot,
				ProdLine.textimage,
				ProdLine.image,
				stockshipty,
				accountegreso,
				chartmaster.accountname AS nombreCargo,
				chartmaster2.accountname AS nombreAbono,
				stockcategory.nu_tipo_gasto,
				stockcategory.ln_clave,
				stockcategory.id,
				IF(stockcategory.ind_activo=1,'Activo','Inactivo') AS Estatus

			FROM ".LNTBLCAT." AS stockcategory
			LEFT JOIN typeoperationdiot ON stockcategory.typeoperationdiot = typeoperationdiot.u_typeoperation
			LEFT JOIN ProdLine ON stockcategory.prodLineId = ProdLine.Prodlineid
			LEFT JOIN prdflujos ON stockcategory.idflujo = prdflujos.idflujo
			LEFT JOIN accountingtransactiontype ON stockcategory.u_typeoperation = accountingtransactiontype.u_typeoperation
			LEFT JOIN chartmaster ON chartmaster.accountcode = stockcategory.stockact
			LEFT JOIN chartmaster chartmaster2 ON chartmaster2.accountcode = stockcategory.accountegreso

			WHERE stocktype<>'A' AND stockcategory.ind_activo = 1";

	// datos adicionales de filtrado
	if(!empty($info['stockactBuscador'])){
		$sql .= " AND stockcategory.stockact = '" . $info['stockactBuscador'] . "' ";
	}
	if(!empty($info['accountegresoBuscador'])){
		$sql .= " AND ( stockcategory.accountegreso =  '" . $info['accountegresoBuscador'] . "' )"; 
	}
	if(!empty($info['busquedaClas'])){
		$sql .= " AND ( stockcategory.categoryid LIKE  '%" . $info['busquedaClas'] . "' )"; 
	}
	
	if(!empty($info['busquedaDesc'])){
		$sql .= " AND (stockcategory.categorydescription LIKE '%" . $info['busquedaDesc'] . "%' )";
	}
	

	// datos adicionales de ordenamiento
	$sql .= " ORDER BY categoryid ASC";

	$result = DB_query($sql, $db);

	// procesamiento de la información obtenida
	while ($rs = DB_fetch_array($result)) {
		$data['content'][] = [
			'ln_clave'=>utf8_encode($rs['ln_clave']),// 0
			'categoryid'=>utf8_encode($rs['categoryid']),// 1
			'categorydescription'=>utf8_encode($rs['categorydescription']),// 2
			'nu_tipo_gasto'=>utf8_encode($rs['nu_tipo_gasto']),// 3
			'stockact'=>utf8_encode($rs['stockact']),// 4
			'nombreCargo'=>utf8_encode($rs['nombreCargo']),// 5
			'accountegreso'=>utf8_encode($rs['accountegreso']),// 6
			'nombreAbono'=>utf8_encode($rs['nombreAbono']),// 7
			'estatus'=>utf8_encode($rs['Estatus']),// 8
			'modificar'=>'<span class="modificar glyphicon glyphicon-edit"></span>',// 9
			'eliminar'=>'<span class="eliminar glyphicon glyphicon-trash"></span>',// 10
			'identificador'=>$rs['id']// 11
		];

	}
	$data['success'] = true;
	// retorno de la información
	return $data;
}
/**
 * Función para obtención de información por ítem especificado
 * @param	[DBInstance]	$db		Instancia de la base de datos
 * @return	[Array]			$data	Arreglo con la respuesta verdadera o falsa, según lo obtenido
 */
function edit($db){
	// declaración de variables de la función
	$data = ['success'=>false, 'msg'=>'Ocurrió un incidente al obtener la información. Favor de contactar al administrador.'];
	$info = $_POST;
	$sql = "SELECT * FROM `".LNTBLCAT."` WHERE `".DATA['identificador']['col']."` = '$info[identificador]' ";

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
 * Función para el guardado de la información de los ítems generados
 * @param	[DBInstance]	$db		Instancia de la base de datos
 * @return	[Array]			$data	Arreglo con la respuesta verdadera o falsa, según lo obtenido
 */
function store($db){
	// declaracion de variables de la función
	$data = ['success'=>false,'msg'=>'Ocurrió un incidente inesperado. Favor de contactar con el administrador.'];
	$info = $_POST;
	$info['ln_clave'] = "$info[selectUnidadNegocio]-$info[selectUnidadEjecutora]-$info[txtpp]";
	$valid = empty($info['valid']);
	$Registro = "CRI: <strong>$info[categoryid] - $info[categorydescription]</strong>";
	// comprobación de existencia
	if(compruebaCampoLlave($db, $info) && !$valid){
		$data['msg'] = "El registro:".$Registro." ya existe en el sistema.";
		//// $data['content'] = show($db)['content'];
		return $data;
	}

	unset($info['method']);
	unset($info['valid']);

	unset($info['selectRazonSocial']);
	unset($info['selectUnidadNegocio']);
	unset($info['selectUnidadEjecutora']);
	unset($info['txtpp']);
	unset($info['textoOculto__stockact']);
	unset($info['textoOculto__accountegreso']);

	$info['stocktype'] = 'F';
	$info['wipact'] = '1';
	$info['allowNarrativePOLine'] = '1';
	$info['prodLineId'] = '0';
	$info['idflujo'] = '0';
	$info['cashdiscount'] = '0';
	$info['accounttransfer'] = '1';

	$concepto = $info['categoryid'];
	/*# llamado de la función de actualización
	if(updateWhenInserting($db, $info)){
		$data['msg'] = "Se registró con éxito el concepto <strong>$concepto</strong>";
		$data['content'] = show($db)['content'];
		$data['success'] = true;
		return $data;
	}*/
	# generación de registros
	DB_Txn_Begin($db);
	try {
		// obtención del query para la inserción
		$sqlIn = obtenInsercion($info);
		$resultIn = DB_query($sqlIn, $db);
		// comprobación de éxito de la generación de la información
		if($resultIn == true){
			$data['success'] = true;
			$data['msg'] = "<center>Se agregó con éxito:$Registro";
			//// $data['content'] = show($db)['content'];
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
 * Función de actualización de los datos de un ítem específico
 * @param	[DBInstance]	$db		Instancia de la base de datos
 * @return	[Array]			$data	Arreglo con la respuesta verdadera o falsa, según lo obtenido
 */
function update($db){
	// declaración de variables de la función
	$data = ['success'=>false,'msg'=>'Ocurrió un incidente al actualizar la información. Favor de contactar al administrador'];
	$info = $_POST;
	$info['ln_clave'] = "$info[selectUnidadNegocio]-$info[selectUnidadEjecutora]-$info[txtpp]";
	$Registro = "<br><strong>$info[ln_clave]</strong></center><p><br>Cuenta Cargo<br><strong>$info[stockact] - $info[textoOculto__stockact]</strong><p><br>Cuenta Abono<br><strong>$info[accountegreso] - $info[textoOculto__accountegreso]</strong>";
	$identificador = $info['CampoLlave'];
	$CampoLlave = $info['identificador'];
	$RegistroNoEliminable = compruebaIntegridadReferencial($db, $info);

	unset($info['method']);
	unset($info['valid']);
	unset($info['identificador']);
	unset($info['CampoLlave']);

	unset($info['selectRazonSocial']);
	unset($info['selectUnidadNegocio']);
	unset($info['selectUnidadEjecutora']);
	unset($info['txtpp']);
	unset($info['textoOculto__stockact']);
	unset($info['textoOculto__accountegreso']);
	unset($info['categoryid']);
	//unset($info['ln_clave']);

	//// $data['content'] = show($db)['content'];
	$concepto = $info['categoryid'];
	$sql = "SELECT * FROM `".LNTBLCAT."` WHERE `categoryid` = '$identificador' ";
	$result = DB_query($sql, $db);
	// comprobación de consistencia de datos
	if(DB_num_rows($result) == 0){
		$data['msg'] = 'No se encontraron los datos solicitados.';
		return $data;
	}
	/*# comprobacion de existencia como inactivo
	$registroInactivo = compruebaCampoLlaveInactivo($db, $concepto);
	// comprobacion de existencia
	if(compruebaCampoLlave($db, $concepto) && !$registroInactivo){
		$data['msg'] = "El concepto $concepto ya se encuentra registrado.";
		$data['content'] = show($db)['content'];
		return $data;
	}*/
	# retorno de mensaje según ejecución de precondición
	if($info['ind_activo']!=1){
		if($RegistroNoEliminable){
			$data['msg'] = "No puede desactivar el registro porque ya ha sido utilizado previamente.";
			return $data;
		}
		/*$sql = "SELECT COUNT(*) AS 'RegistrosEncontrados' FROM `stockmaster` WHERE `categoryid` = '".$identificador."'";
		if(!ejecutaQuery($db, $sql)){
			$data['msg'] = "No puede desactivar el registro porque ya ha sido utilizado previamente.";
			return $data;
		}
		$sql = "SELECT COUNT(*) AS 'RegistrosEncontrados' FROM `salesglpostings` WHERE `stkcat` = '".$identificador."'";
		if(!ejecutaQuery($db, $sql)){
			$data['msg'] = "No puede desactivar el registro porque ya ha sido utilizado previamente.";
			return $data;
		}
		$sql = "SELECT COUNT(*) AS 'RegistrosEncontrados' FROM `cogsglpostings` WHERE `stkcat` = '".$identificador."'";
		if(!ejecutaQuery($db, $sql)){
			$data['msg'] = "No puede desactivar el registro porque ya ha sido utilizado previamente.";
			return $data;
		}*/
	}
	/*if(!exePreReq($db, $identificador)){
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
	}*/
	# reasignación de identificador al objeto general
	$info['identificador'] = $CampoLlave;
	# desarrollo de la transacción de eliminación
	DB_Txn_Begin($db);
	try {
		// obtención de la cadena de ejecución para la actualización
		$sqlUpdate = obtenUpdate($info);
		$resultUpdate = DB_query($sqlUpdate, $db);
		// comprobación de éxito de la información
		if($resultUpdate==true){
			$data['msg'] = "<center>Se actualizó correctamente:$Registro";
			$data['success'] = true;
			DB_Txn_Begin($db);
			//// $data['content'] = show($db)['content'];
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
 * Función para la eliminación lógica un ítem específico
 * @param	[DBInstance]	$db		Instancia de la base de datos
 * @return	[Array]			$data	Arreglo con la respuesta verdadera o falsa, según lo obtenido
 */
function destroy($db){
	// declaración de variables de la función
	$data = ['success'=>false,'msg'=>'Ocurrió un incidente al momento de eliminar la información.'];
	$info = $_POST;
	//// $data['content'] = show($db)['content'];
	# comprobación de identificador
	if(empty($info['identificador'])){
		$data['msg'] = 'Es necesario indicar el identificador del registro';
		return $data;
	}
	# retorno de mensaje según ejecución de precondición
	$sql = "SELECT COUNT(*) AS 'RegistrosEncontrados' FROM `stockmaster` WHERE `categoryid` = '".$info['identificador']."'";
	if(!ejecutaQuery($db, $sql)){
		$data['msg'] = "No puede desactivar el registro porque ya ha sido utilizado previamente.";
		return $data;
	}
	$sql = "SELECT COUNT(*) AS 'RegistrosEncontrados' FROM `salesglpostings` WHERE `stkcat` = '".$info['identificador']."'";
	if(!ejecutaQuery($db, $sql)){
		$data['msg'] = "No puede desactivar el registro porque ya ha sido utilizado previamente.";
		return $data;
	}
	$sql = "SELECT COUNT(*) AS 'RegistrosEncontrados' FROM `cogsglpostings` WHERE `stkcat` = '".$info['identificador']."'";
	if(!ejecutaQuery($db, $sql)){
		$data['msg'] = "No puede desactivar el registro porque ya ha sido utilizado previamente.";
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
			$data['msg'] = "Se elimino con éxito la estructura.";
			$data['success'] = true;
			DB_Txn_Begin($db);
			//// $data['content'] = show($db)['content'];
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

/* EJECUCIÓN DE FUNCIONES */
$data = call_user_func_array($_POST['method'],[$db]);
/* MODIFICACIÓN DE HEADER */
header('Content-type:application/json;charset=utf-8');
/* ENVÍO DE INFORMACIÓN */
echo json_encode($data);

/*********************************************** FUNCIONES ÚTILES ***********************************************/



/**
 * Función para la obtención del sql para genrar datos o items
 * @param	Array		$info Datos a ser procesados
 * @return	String		Sql para la ejecución de la generación de datos
 */
function obtenInsercion($info)
{
	$campos = ''; $datos = ''; $flag = 0; $strData = ['string','select'];
	foreach ($info as $input => $valor) {
		if(!array_key_exists($input, DATA)){ continue; }
		if($flag!=0){ $campos .= ', '; $datos .= ', '; }
		$data = DATA[$input];
		$campos .= "`$data[col]`";
		$datos .= in_array($data['tipo'], $strData)? utf8_decode(" '$valor'") : " $valor";
		$flag++;
	}
	# agregado de campo activo
	//$campos .= ", ".ACTIVECOL;
	//$datos .= ", '1'";
	return " INSERT INTO `".LNTBLCAT."` ($campos) VALUES ($datos) ";
}

function obtenUpdate($info)
{
	$campos = ''; $flag = 0; $identificador = $info['identificador']; $iterador = DATA;
	unset($info['identificador']);
	unset($iterador['identificador']);
	foreach ($info as $input => $valor) {
		if(!array_key_exists($input, $iterador)){ continue; }
		if($flag!=0){ $campos .= ', '; }
		$data = DATA[$input];
		$campos .= $data['col']." = ".($data['tipo']=='string'? utf8_decode(" '$valor'") : " $valor");
		$flag++;
	}
	return " UPDATE `".LNTBLCAT."` SET $campos WHERE `".DATA['identificador']['col']."` = '$identificador' ";
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
	$preSql = "SELECT * FROM ".LNTBLCAT." WHERE categoryid = '".$categoryid."' ";
	$preResult = DB_query($preSql, $db);
	if(DB_num_rows($preResult) == 0){ return false; }

	/*try {
		$sql = "UPDATE ".LNTBLCAT." SET `".ACTIVECOL."` = 1 WHERE ".CONCAT." = '".$descripcion."' ";
		$result = mysqli_query($db, $sql);
		DB_Txn_Commit($db);
		return true;
	} catch (Exception $e) {
		// captura del error
		DB_Txn_Begin($db);
	}*/
	return false;
}

function obtenDescripcion($datos)
{
	return implode('-', $datos);
}

function compruebaCampoLlave($db, $info){
	$idAModificar = ( array_key_exists("identificador",$info) ? "AND `id` <> '$info[identificador]'" : "" );
	$sql = "SELECT * 
		FROM `".LNTBLCAT."`
		WHERE  `categoryid` =  '$info[categoryid]' AND ind_activo = 0";
	$result = DB_query($sql, $db);
	return DB_num_rows($result);
}

function compruebaIntegridadReferencial($db, $info){
	$sql = "SELECT * 
		FROM `gltrans`
		WHERE `tag` = '$info[selectUnidadNegocio]'
		AND `ln_ue` = '$info[selectUnidadEjecutora]'
		AND ( `account` = '$info[stockact]'
		OR `account` = '$info[accountegreso]' )";
	$result = DB_query($sql, $db);
	return DB_num_rows($result) ? true : false;
}

function ejecutaQuery($db, $sql){
	# consulta de existencia
	$result = DB_query($sql, $db);
	return (DB_fetch_array($result)['RegistrosEncontrados']==0);
}

/**
 * Función para obtención de información para los distintos selects
 * @param	[DBInstance]	$db		Instancia de la base de datos
 * @return	[Array]			$data	Arreglo con la respuesta verdadera o falsa, según lo obtenido
 */
function obtenDatosSelect($db,$sql){
	// declaración de variables de la función
	$data = ['success'=>false, 'msg'=>'Ocurrió un incidente al obtener la información. Favor de contactar al administrador.'];
	$info = $_POST;

	$result = DB_query($sql, $db);

	// comprobación de existencia de la información
	if(DB_num_rows($result) == 0){
		$data['msg'] = 'No se encontraron los datos solicitados.';
		return $data;
	}
	// prcesamiento de la información obtenida
	while ($rs = DB_fetch_array($result)) {
		$data['content'][] = [
			'label'=>utf8_encode($rs['label']),
			'title'=>utf8_encode($rs['label']),
			'value'=>$rs['valor']
		];
	}
	$data['success'] = true;
	// retorno de la información
	return $data;
}

function obtenDatosLista($db,$sql){
	// declaración de variables de la función
	$data = ['success'=>false, 'msg'=>'Ocurrió un incidente al obtener la información. Favor de contactar al administrador.'];
	$info = $_POST;

	$datosCortos = array();
	$datosLargos = array();

	$result = DB_query($sql, $db);

	// comprobación de existencia de la información
	if(DB_num_rows($result) == 0){
		$data['msg'] = 'No se encontraron los datos solicitados.';
		return $data;
	}

	while ($rs = DB_fetch_array($result)) {
		$veces = substr_count($rs['valor'],'.');

		if($veces<5){
			$datosCortos[] = [
				'value' => $rs['valor'],
				'text' => utf8_encode($rs['label'])
			];
		}else{
			$datosLargos[] = [
				'value' => $rs['valor'],
				'text' => utf8_encode($rs['label'])
			];
		}
	}

	$data['cuentasMenores'] = $datosCortos;
	$data['cuentasMayores'] = $datosLargos;
	$data['success'] = true;
	// retorno de la información
	return $data;
}

function datosselectLinea($db){
	$sql = "SELECT DISTINCT ProdGroup.`Prodgroupid` AS valor, ProdGroup.`Description` AS label
			FROM `ProdGroup`
			INNER JOIN `ProdLine` USING(Prodgroupid)
			ORDER BY ProdGroup.`Description`";
	
	return obtenDatosSelect($db,$sql);
}

function datosListaCuentaCargo($db){
	$sql = "SELECT DISTINCT `accountcode` AS valor, `accountname` AS label
			FROM `chartmaster` AS `cm`
			JOIN `accountgroups` ON `cm`.`group_` = `accountgroups`.`groupname`
			JOIN tb_sec_users_ue ON tb_sec_users_ue.userid = '$_SESSION[UserID]'

			WHERE (`accountcode` LIKE '2.1.1%'
			OR `accountcode` LIKE '2.1'
			OR `accountcode` LIKE '2')
			AND (tb_sec_users_ue.ue = `cm`.`ln_clave`
			OR `cm`.`nu_nivel` <= 5)
			ORDER BY `accountcode`";
	
	return obtenDatosLista($db,$sql);
}

function datosListaCuentaAbono($db){
	$sql = "SELECT DISTINCT `bankaccounts`.`accountcode` AS valor,
			`bankaccounts`.`bankaccountname` AS label,
			`bankaccounts`.`currcode` AS moneda
			FROM `bankaccounts`, `chartmaster`, `tagsxbankaccounts`, `sec_unegsxuser`
			WHERE `bankaccounts`.`accountcode`=`chartmaster`.`accountcode`
			AND `bankaccounts`.`accountcode` = `tagsxbankaccounts`.`accountcode`
			AND `tagsxbankaccounts`.`tagref` = `sec_unegsxuser`.`tagref`
			AND `sec_unegsxuser`.`userid` = '".$_SESSION['UserID']."'";
	
	return obtenDatosLista($db,$sql);
}


