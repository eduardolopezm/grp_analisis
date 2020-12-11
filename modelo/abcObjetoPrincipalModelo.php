<?php
/**
 * Locations.php
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
$PageSecurity = 8;
$PathPrefix = '../';
$funcion = 2504;

/* INCLUSIÓN DE ARCHIVOS NECESARIOS */
include($PathPrefix . 'config.php');
include($PathPrefix . 'includes/SecurityUrl.php');
include($PathPrefix . 'includes/ConnectDB.inc');
include($PathPrefix . 'includes/SecurityFunctions.inc');
include($PathPrefix . 'includes/SQL_CommonFunctions.inc');

/*********************************************** CONSTANTES ***********************************************/
define('DECPLACE', !empty($_SESSION['DecimalPlaces'])? $_SESSION['DecimalPlaces'] : 2);
define('LNTBLCAT', 'locations');
define('IDENTIFICADOR', 'loccode');
define('ACTIVECOL', 'ind_activo');
define('DATA', [
		'LocCode' => ['col' => 'loccode', 'tipo' => 'string'],
		'LocationName' => ['col' => 'locationname', 'tipo' => 'string'],
		'status' => ['col' => 'estatus', 'tipo' => 'string'],
		'type' => ['col' => 'tipo', 'tipo' => 'string'],
		'leyenda' => ['col' => 'leyenda', 'tipo' => 'string'],
		'txtNombre' => ['col' => 'id_producto', 'tipo' => 'string'],
		'selectUnidad' => ['col' => 'id_unidad', 'tipo' => 'string'],
		'selectPago' => ['col' => 'id_pago', 'tipo' => 'string'],
		'selectUnidadNegocio' => ['col' => 'tagref', 'tipo' => 'string'],
		'selectUnidadEjecutora' => ['col' => 'ln_ue', 'tipo' => 'string'],
		// 'tag' => ['col' => 'tagref', 'tipo' => 'string'],
		// 'selectUnidadEjecutora' => ['col' => 'ln_ue', 'tipo' => 'string'],
		'identificador'=>['col'=>'loccode','tipo'=>'string']
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
	$data = ['success'=>false,'msg'=>'No hay lugares que coincidan con un registro provincia tributaria para mostrar. Compruebe que las provincias tributarias se configuran para todos los lugares de despacho','content'=>[]];
	$sql = "SELECT `locations`.`loccode` as 'clave',
	`locationname` as 'descripcion',
	CONCAT(`tb_cat_unidades_ejecutoras`.`ue`, ' - ' , `tb_cat_unidades_ejecutoras`.`desc_ue`) AS 'uedescription',
	CONCAT(`tags`.`tagref`, ' - ' , `tags`.`tagdescription`) AS 'urdescription',
	locations.leyenda as leyenda,
	locations.estatus as estatus,
	locations.id_producto as id_producto,
	locations.id_unidad as id_unidad,
	locations.id_pago as id_pago
	FROM locations 
	LEFT JOIN `tb_cat_unidades_ejecutoras`
	ON `tb_cat_unidades_ejecutoras`.`ur` = `locations`.`tagref`
	AND `tb_cat_unidades_ejecutoras`.`ue` = `locations`.`ln_ue`
	LEFT JOIN `tags`
	ON `tags`.`tagref` = `locations`.`tagref`
	WHERE locations.tipo = 'ObjetoPrincipal' and locations.activo = '1'";

	// datos adicionales de filtrado
	if(!empty($info['selectUnidadNegocioFiltro'])&&$info['selectUnidadNegocioFiltro']!="-1"){
		$sql .= " AND `locations`.`tagref` LIKE '" . $info['selectUnidadNegocioFiltro'] . "%' ";
	}
	if(!empty($info['selectUnidadEjecutoraFiltro']) &&  $info['selectUnidadEjecutoraFiltro'] != '-1'){
		$sql .= " AND ( `locations`.`ln_ue` LIKE  '%" . $info['selectUnidadEjecutoraFiltro'] . "%' )"; 
	}
	if(!empty($info['busquedaClave'])){
		$sql .= " AND ( `locations`.`loccode` LIKE  '%" . $info['busquedaClave'] . "' )"; 
	}
	
	if(!empty($info['busquedaDesc'])){
		$sql .= " AND (`locations`.`locationname` LIKE '%" . $info['busquedaDesc'] . "%' )";
	}
	
	if(!empty($info['busquedaStatus'])&&$info['busquedaStatus']!="-1"){
		$sql .= " AND `locations`.`estatus` = '" .$info['busquedaStatus']. "' ";
	}
	
	// datos adicionales de filtrado

	// datos adicionales de ordenamiento
	$sql .= " ORDER BY 'clave' ASC";

	$data = ['success'=>false,'msg'=>$sql,'content'=>[]];


	$result = DB_query($sql, $db);

	// procesamiento de la información obtenida
	while ($rs = DB_fetch_array($result)) {
		$data['content'][] = [
			'loccode'=>utf8_encode($rs['clave']),// 0
			'urdescription'=>utf8_encode($rs['urdescription']),// 2
			'uedescription'=>utf8_encode($rs['uedescription']),// 2
			'descripcion'=>utf8_encode($rs['descripcion']),// 1
			'leyenda'=>utf8_encode($rs['leyenda']),// 3
			'id_producto'=>utf8_encode($rs['id_producto']),// 4
			'id_unidad'=>utf8_encode($rs['id_unidad']),// 5
			'id_pago'=>utf8_encode($rs['id_pago']),// 5
			'estatus'=>utf8_encode($rs['estatus']),// 6
			'modificar'=>'<span class="modificar glyphicon glyphicon-edit"></span>',// 7
			'eliminar'=>'<span class="eliminar glyphicon glyphicon-trash"></span>',// 8
			'identificador'=>utf8_encode($rs['clave'])// 9
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
	$sql = "SELECT * FROM ".LNTBLCAT." WHERE `".IDENTIFICADOR."` = '".$info['identificador']."' ";

	$result = DB_query($sql, $db);

	// comprobación de existencia de la información
	if(DB_num_rows($result) == 0){
		$data['msg'] = 'No se encontraron los datos solicitados.';
		return $data;
	}
	// procesamiento de la información obtenida
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
	$valid = empty($info['valid']);
	$Registro = "$info[LocCode]";
	$producto = "$info[txtNombre]";

	// comprobación de existencia
	if(compruebaCampoLlave($db, $info) && !$valid){
		$data['msg'] = "El código $Registro fue capturado previamente en el sistema.";
		$data['content'] = show($db)['content'];
		return $data;
	}

	if(!compruebaCampoProducto($db, $info)){
		$data['msg'] = "El código de producto $producto no existe en el sistema.";
		$data['content'] = show($db)['content'];
		return $data;
	}

	unset($info['method']);
	unset($info['valid']);

	$concepto = $info['LocCode'];
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
	if($data['success']){
	
		$data['msg'] = "<p>El nuevo registro de Objeto Principal <strong>$Registro</strong> ha sido insertado.</p>";
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
	$identificador = $info['identificador'];
	unset($info['method']);
	unset($info['valid']);
	unset($info['identificador']);
	unset($info['CampoLlave']);

	if(!compruebaCampoProducto($db, $info)){
		$data['msg'] = "El código de producto $producto no existe en el sistema.";
		$data['content'] = show($db)['content'];
		return $data;
	}

	// $data['content'] = show($db)['content'];

	$concepto = $info['categoryid'];
	$sql = "SELECT * FROM `".LNTBLCAT."` WHERE `".IDENTIFICADOR."` = '$identificador' ";
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
	/*if($info['ind_activo']!=1){
		if($RegistroNoEliminable){
			$data['msg'] = "No puede desactivar el almacén porque ya ha sido utilizado previamente.";
			return $data;
		}
	}*/
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
	$info['identificador'] = $identificador;
	# desarrollo de la transacción de eliminación
	DB_Txn_Begin($db);
	try {
		// obtención de la cadena de ejecución para la actualización
		$sqlUpdate = obtenUpdate($info);
		$resultUpdate = DB_query($sqlUpdate, $db);
		// comprobación de éxito de la información
		if($resultUpdate==true){
			$data['msg'] = "El Registro de Objeto Principal ha sido actualizado";
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
 * Función para la eliminación lógica un ítem específico
 * @param	[DBInstance]	$db		Instancia de la base de datos
 * @return	[Array]			$data	Arreglo con la respuesta verdadera o falsa, según lo obtenido
 */
function destroy($db){
	// declaración de variables de la función
	$registrosEncontrados = "";
	$data = ['success'=>false,'msg'=>'Ocurrió un incidente al momento de eliminar la información.'];
	$info = $_POST;
	$data['content'] = show($db)['content'];
	# comprobación de identificador
	if(empty($info['identificador'])){
		$data['msg'] = 'Es necesario indicar el identificador del almacén';
		return $data;
	}
	# retorno de mensaje según ejecución de precondición
	$data['msg'] = "No puede eliminarse el Objeto Principal.<br>";
	
	# desarrollo de la transacción de eliminación
    $sql = "UPDATE locations SET estatus = 'Inactivo',  activo = '0' WHERE loccode = '" . $info['identificador'] . "'";
	
	// DB_Txn_Begin($db);
	$result = DB_query( $sql, $db);
	$data['content'] = show($db)['content'];

	$data['msg'] = "El Objeto Principal $info[identificador] ha sido eliminado.";
	$data['success'] = true;
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
		$campos .= $data['col'];
		$datos .= in_array($data['tipo'], $strData)? utf8_decode(" '$valor'") : " $valor";
		$flag++;
	}
	# agregado de campo activo
	//$campos .= ", ".ACTIVECOL;
	//$datos .= ", '1'";
	return " INSERT INTO ".LNTBLCAT." ($campos) VALUES ($datos) ";
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
	return " UPDATE ".LNTBLCAT." SET $campos WHERE `".IDENTIFICADOR."` = '".$identificador."' ";
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

function movimientosAdicionalesFuncionOriginal($db){
	$result = DB_query('SELECT COUNT(taxid) FROM taxauthorities', $db);
	$NoTaxAuths =DB_fetch_row($result);

	$DispTaxProvincesResult = DB_query('SELECT taxprovinceid FROM locations', $db);
	$TaxCatsResult = DB_query('SELECT taxcatid FROM taxcategories', $db);
	if (DB_num_rows($TaxCatsResult) > 0) {
		while ($myrow=DB_fetch_row($DispTaxProvincesResult)) {
			$NoTaxRates = DB_query('SELECT taxauthority FROM taxauthrates WHERE dispatchtaxprovince=' . $myrow[0], $db);
			if (DB_num_rows($NoTaxRates) < $NoTaxAuths[0]) {
				$DelTaxAuths = DB_query('DELETE FROM taxauthrates WHERE dispatchtaxprovince=' . $myrow[0], $db);
				while ($CatRow = DB_fetch_row($TaxCatsResult)) {
					$sql = 'INSERT INTO taxauthrates (taxauthority,
										dispatchtaxprovince,
										taxcatid)
							SELECT taxid,
								' . $myrow[0] . ',
								' . $CatRow[0] . '
							FROM taxauthorities';

					$InsTaxAuthRates = DB_query($sql, $db);
				}
				DB_data_seek($TaxCatsResult, 0);
			}
		}
	}
}

function obtenDescripcion($datos)
{
	return implode('-', $datos);
}

function compruebaCampoLlave($db, $info)
{
	$sql = "SELECT * 
		FROM `".LNTBLCAT."`
		WHERE `".IDENTIFICADOR."` = '$info[LocCode]'";
	$result = DB_query($sql, $db);
	return DB_num_rows($result);
}

function compruebaCampoProducto($db, $info)
{
	$sql = "SELECT * 
		FROM `sat_stock`
		WHERE `c_ClaveProdServ` = '$info[txtNombre]'";
	$result = DB_query($sql, $db);
	return DB_num_rows($result);
}

function compruebaIntegridadReferencial($db, $info){
	$sql = "SELECT * 
		FROM `gltrans`
		WHERE `tag` = '".$info['selectUnidadNegocio']."'
		AND `ln_ue` = '".$info['selectUnidadEjecutora']."'
		AND ( `account` = '".$info['stockact']."'
		OR `account` = '".$info['accountegreso']."' )";
	$result = DB_query($sql, $db);
	return DB_num_rows($result) ? true : false;
}

function ejecutaQuery($db, $sql){
	# consulta de existencia
	$result = DB_query($sql, $db);
	return DB_fetch_array($result)['RegistrosEncontrados'];
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

function datosselectImpuestos($db){
	$sql = "SELECT `taxprovinceid` AS valor, `taxprovincename` AS label
			FROM `taxprovinces`";
	
	return obtenDatosSelect($db,$sql);
}

function cargaProductos($db){
	$sql = "SELECT c_ClaveProdServ as valor, CONCAT(c_ClaveProdServ, ' - ', Descripcion) as label 
	FROM sat_stock 
	ORDER BY c_ClaveProdServ ASC";
	
	return obtenDatosSelect($db,$sql);
}
