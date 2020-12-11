<?php
/**
 * StockCategoriesV2.php
 * 
 * @category	Modelo
 * @package		ap_grp/modelo
 * @author		JP
 * @version		1.0.0
 * @date:		10.03.18
 *
 * Programa para afectación de páneles, captura y administración de la información de los catálogos.
 * 
 */
session_start();

/* DECLARACIÓN DE VARIABLES */
$PageSecurity = 11;
$PathPrefix = '../';
$funcion = 137;

/* INCLUSIÓN DE ARCHIVOS NECESARIOS */
include($PathPrefix . 'config.php');
include($PathPrefix . 'includes/SecurityUrl.php');
include($PathPrefix . 'includes/ConnectDB.inc');
include($PathPrefix . 'includes/SecurityFunctions.inc');
include($PathPrefix . 'includes/SQL_CommonFunctions.inc');

/*********************************************** CONSTANTES ***********************************************/
define('DECPLACE', !empty($_SESSION['DecimalPlaces'])? $_SESSION['DecimalPlaces'] : 2);
define('LNTBLCAT', 'stockcategory');
define('ACTIVECOL', 'ind_activo');
define('DATA', [
		'categoryid' => ['col' => 'categoryid', 'tipo' => 'string'],
		'categorydescription' => ['col' => 'categorydescription', 'tipo' => 'string'],
	'stocktype' => ['col' => 'stocktype', 'tipo' => 'string'],
		'stockact' => ['col' => 'stockact', 'tipo' => 'string'],
		'adjglact' => ['col' => 'adjglact', 'tipo' => 'string'],
	'wipact' => ['col' => 'wipact', 'tipo' => 'string'],
	'allowNarrativePOLine' => ['col' => 'allowNarrativePOLine', 'tipo' => 'string'],
		'accountegreso' => ['col' => 'accountegreso', 'tipo' => 'string'],
		'nu_tipo_gasto' => ['col' => 'nu_tipo_gasto', 'tipo' => 'string'],
		'ln_abono_salida' => ['col' => 'ln_abono_salida', 'tipo' => 'string'],
		'ln_clave' => ['col' => 'ln_clave', 'tipo' => 'string'],
		'ind_activo' => ['col' => 'ind_activo', 'tipo' => 'string'],
		'identificador'=>['col'=>'id','tipo'=>'string'],
	'prodLineId' => ['col' => 'prodLineId', 'tipo' => 'string'],
	'idflujo' => ['col' => 'idflujo', 'tipo' => 'string'],
	'cashdiscount' => ['col' => 'cashdiscount', 'tipo' => 'string'],
	'CodigoPanelControl' => ['col' => 'CodigoPanelControl', 'tipo' => 'string'],
	'accounttransfer' => ['col' => 'accounttransfer', 'tipo' => 'string']
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
	$data = ['success'=>false,'msg'=>'No se encontraron datos de la matriz pagado de gastos','content'=>[]];
	$sql = "SELECT DISTINCT(categoryid),
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
				stockcategory.ln_abono_salida,
				chartmasterAlmCar.accountname AS nombreCargoAlmacen,
				chartmasterAlmAbo.accountname AS nombreAbonoAlmacen,
				IF(stockcategory.ind_activo=1,'Activo','Inactivo') AS Estatus

			FROM ".LNTBLCAT." AS stockcategory
			LEFT JOIN typeoperationdiot ON stockcategory.typeoperationdiot = typeoperationdiot.u_typeoperation
			LEFT JOIN ProdLine ON stockcategory.prodLineId = ProdLine.Prodlineid
			LEFT JOIN prdflujos ON stockcategory.idflujo = prdflujos.idflujo
			LEFT JOIN accountingtransactiontype ON stockcategory.u_typeoperation = accountingtransactiontype.u_typeoperation
			LEFT JOIN chartmaster ON chartmaster.accountcode = stockcategory.stockact
			LEFT JOIN chartmaster chartmaster2 ON chartmaster2.accountcode = stockcategory.accountegreso
			LEFT JOIN chartmaster chartmasterAlmCar ON chartmasterAlmCar.accountcode = stockcategory.adjglact
			LEFT JOIN chartmaster chartmasterAlmAbo ON chartmasterAlmAbo.accountcode = stockcategory.ln_abono_salida

			WHERE stocktype<>'A'
			GROUP BY categoryid";
	
	// datos adicionales de filtrado
	if(!empty($info['selectUnidadNegocioFiltro'])&&$info['selectUnidadNegocioFiltro']!="-1"){
		$sql .= " AND stockcategory.`ln_clave` LIKE '" . $info['selectUnidadNegocioFiltro'] . "-%' ";
	}
	if(is_array($info['selectUnidadEjecutoraFiltro'])&&count($info['selectUnidadEjecutoraFiltro'])){
		$sql .= " AND ( `stockcategory`.`ln_clave` LIKE '%-".implode("-%' OR `stockcategory`.`ln_clave` LIKE '%-",$info['selectUnidadEjecutoraFiltro'])."-%' ) ";
	}
	if(is_array($info['busquedaPP'])&&count($info['busquedaPP'])){
		$sql .= " AND ( `stockcategory`.`ln_clave` LIKE '%-".implode("' OR `stockcategory`.`ln_clave` LIKE '%-",$info['busquedaPP'])."' ) ";
	}
	if(empty($info['busquedaConcepto'])==false){
		$sql .= " AND stockcategory.`categoryid` LIKE '%" . $info['busquedaConcepto'] . "%' ";
	}
	if(empty($info['busquedaDesc'])==false){
		$sql .= " AND `categorydescription` LIKE '%" . $info['busquedaDesc'] . "%' ";
	}
	if(!empty($info['busquedaTipoGasto'])&&$info['busquedaTipoGasto']!="-1"){
		$sql .= " AND stockcategory.nu_tipo_gasto = '" . $info['busquedaTipoGasto'] . "' ";
	}
	if(!empty($_POST['lineaDesc'])&&$info['lineaDesc']!="-1"){
		$sql .= " AND ProdLine.`Description` LIKE '%" . $_POST['lineaDesc'] . "%' ";
	}
	if(!empty($info['StockAct'])&&$info['StockAct']!="-1"){
		$sql .= " AND `stockact` LIKE '%" . $info['StockAct'] . "%' ";
	}
	if(!empty($info['AccountEgreso'])&&$info['AccountEgreso']!="-1"){
		$sql .= " AND `accountegreso` LIKE '%" . $info['AccountEgreso'] . "%' ";
	}
	if(!empty($info['ADJGLAct'])&&$info['ADJGLAct']!="-1"){
		$sql .= " AND `adjglact` LIKE '%" . $info['ADJGLAct'] . "%' ";
	}
	if(!empty($info['ln_Abono_Salida'])&&$info['ln_Abono_Salida']!="-1"){
		$sql .= " AND `ln_abono_salida` LIKE '%" . $info['ln_Abono_Salida'] . "%' ";
	}
	if(!empty($info['busquedaEstatus'])&&$info['busquedaEstatus']!="-1"){
		$sql .= " AND stockcategory.ind_activo = '" .( $info['busquedaEstatus']==1 ? 1 : 0 ). "' ";
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
			'adjglact'=>utf8_encode($rs['adjglact']),// 8
			'nombreCargoAlmacen'=>utf8_encode($rs['nombreCargoAlmacen']),// 9
			'ln_abono_salida'=>utf8_encode($rs['ln_abono_salida']),// 10
			'nombreAbonoAlmacen'=>utf8_encode($rs['nombreAbonoAlmacen']),// 11
			'estatus'=>utf8_encode($rs['Estatus']),// 12
			'modificar'=>'<span class="modificar glyphicon glyphicon-edit"></span>',// 13
			'eliminar'=>'<span class="eliminar glyphicon glyphicon-trash"></span>',// 14
			'identificador'=>$rs['id']// 15
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
	$data['modificable'] = compruebaRegistroModificable($db, $content);
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
	$viaticos = substr($info['categoryid'],0,2)=="37";
	$Cuenta1 = ( $viaticos ? "Servicio De Traslado Y Viáticos" : "Recepción Cargo" );
	$Cuenta2 = ( $viaticos ? "Otras Cuentas Por Pagar A Corto Plazo" : "Recepción Abono" );
	$Cuenta3 = ( $viaticos ? "Deudores Diversos Por Cobrar A Corto Plazo" : "Salida Cargo" );
	$Registro = "<br><strong>$info[ln_clave]</strong></center>".( $info['adjglact']||$info['ln_abono_salida'] ? "<br>" : "" )."<p><br>Cuenta $Cuenta1<br><strong>$info[stockact] - $info[textoOculto__stockact]</strong><p><br>Cuenta $Cuenta2<br><strong>$info[accountegreso] - $info[textoOculto__accountegreso]</strong>".( $info['adjglact'] ? "<p><br>Cuenta $Cuenta3<br><strong>$info[adjglact] - $info[textoOculto__adjglact]</strong>" : "" ).( $info['ln_abono_salida'] ? "<p><br>Cuenta Salida Abono<br><strong>$info[ln_abono_salida] - $info[textoOculto__ln_abono_salida]</strong>" : "" );
	// comprobación de existencia
	if(compruebaCampoLlave($db, $info) && !$valid){
		$data['msg'] = "El registro Partida Genérica: ". $info['categoryid']." ya existe en el sistema.";
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
	unset($info['textoOculto__adjglact']);
	unset($info['textoOculto__ln_abono_salida']);

	$info['stocktype'] = 'F';
	$info['wipact'] = '1';
	$info['allowNarrativePOLine'] = '1';
	$info['prodLineId'] = '0';
	$info['idflujo'] = '0';
	$info['cashdiscount'] = '0';
	$info['CodigoPanelControl'] = '0';
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
	$sql = "SELECT
			CONCAT(tags.tagref, '-', tb_cat_unidades_ejecutoras.ue, '-', tb_cat_programa_presupuestario.cppt) as identificador
			FROM tags
			JOIN tb_cat_unidades_ejecutoras ON tb_cat_unidades_ejecutoras.ur = tags.tagref
			JOIN tb_cat_programa_presupuestario 
			WHERE tags.tagactive = 1";


	$result = DB_query($sql, $db);

	// comprobación de existencia de la información
	if(DB_num_rows($result) == 0){
		$data['msg'] = 'No se encontraron los datos solicitados.';
		return $data;
	}
	// prcesamiento de la información obtenida
	while ($rs = DB_fetch_array($result)) {
		$info['ln_clave'] = $rs['identificador'];

		DB_Txn_Begin($db);
		try {
			// obtención del query para la inserción
			$sqlIn = obtenInsercion($info);
			$resultIn = DB_query($sqlIn, $db);
			// comprobación de éxito de la generación de la información
			if($resultIn == true){
				$data['success'] = true;
				$data['msg'] = "<center>Se agregó con éxito: $Registro";
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
	$viaticos = substr($info['categoryid'],0,2)=="37";
	$Cuenta1 = ( $viaticos ? "Servicio De Traslado Y Viáticos" : "Recepción Cargo" );
	$Cuenta2 = ( $viaticos ? "Otras Cuentas Por Pagar A Corto Plazo" : "Recepción Abono" );
	$Cuenta3 = ( $viaticos ? "Deudores Diversos Por Cobrar A Corto Plazo" : "Salida Cargo" );
	$Registro = "<br><strong>$info[ln_clave]</strong></center>".( $info['adjglact']||$info['ln_abono_salida'] ? "<br>" : "" )."<p><br>Cuenta $Cuenta1<br><strong>$info[stockact] - $info[textoOculto__stockact]</strong><p><br>Cuenta $Cuenta2<br><strong>$info[accountegreso] - $info[textoOculto__accountegreso]</strong>".( $info['adjglact'] ? "<p><br>Cuenta $Cuenta3<br><strong>$info[adjglact] - $info[textoOculto__adjglact]</strong>" : "" ).( $info['ln_abono_salida'] ? "<p><br>Cuenta Salida Abono<br><strong>$info[ln_abono_salida] - $info[textoOculto__ln_abono_salida]</strong>" : "" );
	// comprobación de existencia
	if(compruebaCampoLlave($db, $info)){
		$data['msg'] = "El registro:".str_replace("<br><br>", "<p>", str_replace("<p><br>", ( $info['adjglact']||$info['ln_abono_salida'] ? "<br>" : "<p>" ), $Registro) )."<p>ya existe en el sistema.";
		//// $data['content'] = show($db)['content'];
		return $data;
	}
	$info['stocktype'] = 'F';
	$info['wipact'] = '1';
	$info['allowNarrativePOLine'] = '1';
	$info['prodLineId'] = '0';
	$info['idflujo'] = '0';
	$info['cashdiscount'] = '0';
	$info['CodigoPanelControl'] = '0';
	$info['accounttransfer'] = '1';

	$identificador = $info['CampoLlave'];
	$CampoLlave = $info['identificador'];
	//$RegistroNoEliminable = compruebaRegistroModificable($db, $info);

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
	unset($info['textoOculto__adjglact']);
	unset($info['textoOculto__ln_abono_salida']);
	//unset($info['ln_clave']);

	//// $data['content'] = show($db)['content'];
	$concepto = $info['categoryid'];
	$sql = "SELECT DISTINCT categoryid, stockact, accountegreso, accountingreso, ln_clave FROM `".LNTBLCAT."` WHERE `id` = '".$_POST['identificador']."' ";
	$result = DB_query($sql, $db);
	
	// comprobación de existencia de la información
	if(DB_num_rows($result) == 0){
		$data['msg'] = 'No se encontraron los datos solicitados.';
		return $data;
	}

	$result = DB_query($sql, $db);
	$rs = DB_fetch_array($result);

	$sql = "INSERT INTO stockcategory_log (
		userid,
		`categoryid`,
		`stockact`,
		`accountegreso`,
		`accountingreso`,
		`ln_clave`)
		VALUES(
			'".$_SESSION['UserID']."',
			'".$rs['categoryid']."',
			'".$rs['stockact']."',
			'".$rs['accountegreso']."',
			'".$rs['accountingreso']."',
			'".$rs['ln_clave']."')";

	try {
		// obtención del querys para la inserción
		$resultIn = DB_query($sql, $db);
		if($resultIn){
			$sql ="DELETE FROM stockcategory WHERE categoryid =  '".$concepto."'";
			try {
				// obtención del query para la inserción
				$result = DB_query($sql, $db);
				// comprobación de éxito de la generación de la información
			} catch (Exception $e) {
				// captura del error
				$data['msg'] .= '<br>'.$e->getMessage();
				DB_Txn_Rollback($db);
			}
		}
		// comprobación de éxito de la generación de la información
	} catch (Exception $e) {
		// captura del error
		$data['msg'] .= '<br>'.$e->getMessage();
		DB_Txn_Rollback($db);
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
	// if($info['ind_activo']!=1){
		/*if($RegistroNoEliminable){
			$data['msg'] = "No puede desactivar el registro porque ya ha sido utilizado previamente.";
			return $data;
		}
		$sql = "SELECT COUNT(*) AS 'RegistrosEncontrados' FROM `stockmaster` WHERE `categoryid` = '".$identificador."'";
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
	// }
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



	$sql = "SELECT
			CONCAT(tags.tagref, '-', tb_cat_unidades_ejecutoras.ue, '-', tb_cat_programa_presupuestario.cppt) as identificador
			FROM tags
			JOIN tb_cat_unidades_ejecutoras ON tb_cat_unidades_ejecutoras.ur = tags.tagref
			JOIN tb_cat_programa_presupuestario 
			WHERE tags.tagactive = 1";


	$result = DB_query($sql, $db);

	// comprobación de existencia de la información
	if(DB_num_rows($result) == 0){
		$data['msg'] = 'No se encontraron los datos solicitados.';
		return $data;
	}
	// prcesamiento de la información obtenida
	while ($rs = DB_fetch_array($result)) {
		$info['ln_clave'] = $rs['identificador'];
		$data['rs'][] = $rs;

		DB_Txn_Begin($db);
		try {
			// obtención del query para la inserción
			$sqlIn = obtenInsercion($info);
			$resultIn = DB_query($sqlIn, $db);
			// comprobación de éxito de la generación de la información
			if($resultIn == true){
				$data['success'] = true;
				//// $data['content'] = show($db)['content']
				$data['msg'] = "<center>Se agregó con éxito: $Registro";
				DB_Txn_Commit($db);
			}else{
				DB_Txn_Rollback($db);
			}
		} catch (Exception $e) {
			// captura del error
			$data['msg'] .= '<br>'.$e->getMessage();
			DB_Txn_Rollback($db);
		}
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
	// Se agrega la variable $ln_clave y se hacen validaciones para que sólo sea llamada en las implementaciones donde deba exisistir una llave única tipo UR-UE-PP por cada registro en la matriz de devengado
	$ln_clave = ( array_key_exists("ln_clave",$info)&&strlen($info['ln_clave'])>2 ? "AND `ln_clave` LIKE '$info[ln_clave]'" : "" );
	$idAModificar = ( array_key_exists("identificador",$info) ? "AND `id` <> '$info[identificador]'" : "" );

	$sql = "SELECT * 
		FROM `".LNTBLCAT."`
		WHERE `categoryid` LIKE '$info[categoryid]'
		AND `stockact` LIKE '$info[stockact]'
		AND `accountegreso` LIKE '$info[accountegreso]'
		AND `adjglact` LIKE '$info[adjglact]'
		AND `ln_abono_salida` LIKE '$info[ln_abono_salida]'
		$ln_clave
		$idAModificar";
	$result = DB_query($sql, $db);
	return DB_num_rows($result);

	/* Código basura, este cambio fue hecho sin detenerse a revisar cómo funcionaba el proceso
		$sql = "SELECT * 
		FROM `".LNTBLCAT."`
		WHERE `categoryid` = '$info[categoryid]'";

		$result = DB_query($sql, $db);
		return DB_num_rows($result);
	*/
}

function compruebaRegistroModificable($db, $info){
	/*
	// Consulta anterior
	$sql = "SELECT * 
		FROM `gltrans`
		WHERE `tag` = '$info[selectUnidadNegocio]'
		AND `ln_ue` = '$info[selectUnidadEjecutora]'
		AND ( `account` = '$info[stockact]'
		OR `account` = '$info[accountegreso]'
		OR `account` = '$info[adjglact]'
		OR `account` = '$info[ln_abono_salida]' )";
	*/
	// Consulta corregida
	$sql = "SELECT COUNT(g1.`counterindex`) AS `RegistrosEncontrados`

		FROM `gltrans` AS g1
		LEFT JOIN `gltrans` AS g2 ON (g2.`amount`*-1) = g1.`amount`
		LEFT JOIN `grns` ON `grns`.`grnbatch` = g1.`typeno`
		LEFT JOIN `purchorderdetails` ON `purchorderdetails`.`podetailitem` = `grns`.`podetailitem`
		LEFT JOIN `stockmaster` ON `stockmaster`.`stockid` LIKE `grns`.`itemcode`

		WHERE g1.`type` = '25'
		AND `stockmaster`.`categoryid` LIKE '$info[categoryid]'
		AND g1.`account` LIKE '$info[stockact]'
		AND g2.`account` LIKE '$info[accountegreso]'
		AND `purchorderdetails`.`ln_clave_iden` LIKE '$info[ln_clave]'";
	$result = DB_query($sql, $db);
	return DB_fetch_array($result)['RegistrosEncontrados'] ? false : true;
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

function datosListaCuentasEntrada($db){
	$sql = "SELECT DISTINCT `accountcode` AS valor, `accountname` AS label
			FROM `chartmaster` AS `cm`
			JOIN `accountgroups` ON `cm`.`group_` = `accountgroups`.`groupname`
			JOIN tb_sec_users_ue ON tb_sec_users_ue.userid = '$_SESSION[UserID]'

			-- LENGTH(`accountcode`) >= 7 AND
			WHERE ( tb_sec_users_ue.ue = `cm`.`ln_clave`
			OR `cm`.`nu_nivel` <= 5 )
			ORDER BY `accountcode`";
	
	return obtenDatosLista($db,$sql);
}

// Sin corrección
function datosListaCuentaCargo($db){
	$sql = "SELECT DISTINCT `accountcode` AS valor, `accountname` AS label
			FROM `chartmaster`, `accountgroups`
			WHERE `chartmaster`.`group_`=`accountgroups`.`groupname`
			AND (`accountcode` LIKE '5%' OR `accountcode` LIKE '1.1.5%' OR `accountcode` LIKE '1.2.4%')
			ORDER BY `accountcode`";
	
	return obtenDatosLista($db,$sql);
}

// Sin corrección
function datosListaCuentaAbono($db){
	$sql = "SELECT DISTINCT `accountcode` AS valor, `accountname` AS label
			FROM `chartmaster`, `accountgroups`
			WHERE `chartmaster`.`group_`=`accountgroups`.`groupname` 
			AND (`accountcode` LIKE '2.1.1%')
			ORDER BY `accountcode`";
	
	return obtenDatosLista($db,$sql);
}

// Sin corrección
function datosListaCuentaCargoSalida($db){
	$sql = "SELECT DISTINCT `accountcode` AS valor, `accountname` AS label
			FROM `chartmaster`, `accountgroups`
			WHERE `chartmaster`.`group_`=`accountgroups`.`groupname`
			AND (`accountcode` LIKE '5%')
			ORDER BY `accountcode`";
	
	return obtenDatosLista($db,$sql);
}

function datosListaCuentaAbonoSalida($db){
	$sql = "SELECT DISTINCT `accountcode` AS valor, `accountname` AS label
			FROM `chartmaster` AS `cm`
			JOIN `accountgroups` ON `cm`.`group_` = `accountgroups`.`groupname`
			JOIN tb_sec_users_ue ON tb_sec_users_ue.userid = '$_SESSION[UserID]'

			WHERE (`accountcode` LIKE '1.1.5%'
			OR `accountcode` LIKE '1.1'
			OR `accountcode` LIKE '1')
			AND (tb_sec_users_ue.ue = `cm`.`ln_clave`
			OR `cm`.`nu_nivel` <= 5)
			ORDER BY `accountcode`";
	
	return obtenDatosLista($db,$sql);
}
