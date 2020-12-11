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
$funcion = 94;

/* INCLUSIÓN DE ARCHIVOS NECESARIOS */
include($PathPrefix . 'config.php');
include($PathPrefix . 'includes/SecurityUrl.php');
include($PathPrefix . 'includes/ConnectDB.inc');
include($PathPrefix . 'includes/SecurityFunctions.inc');
include($PathPrefix . 'includes/SQL_CommonFunctions.inc');

/*********************************************** CONSTANTES ***********************************************/
define('DECPLACE', !empty($_SESSION['DecimalPlaces'])? $_SESSION['DecimalPlaces'] : 2);
define('LNTBLCAT', 'bankaccounts');
define('IDENTIFICADOR', 'accountcode');
define('ACTIVECOL', 'nu_activo');
define('DATA', [
		'glAccount' => ['col' => 'accountcode', 'tipo' => 'string'],
		'claveBank' => ['col' => 'bankaccountcode', 'tipo' => 'string'],
		'nameBank' => ['col' => 'bankaccountname', 'tipo' => 'string'],
		'numBank' => ['col' => 'bankaccountnumber', 'tipo' => 'string'],
		'addressBank' => ['col' => 'bankaddress', 'tipo' => 'string'],
		'currCode' => ['col' => 'currcode', 'tipo' => 'string'],
		'invoice' => ['col' => 'invoice', 'tipo' => 'string'],
		'bank' => ['col' => 'bankid', 'tipo' => 'string'],
		'identificador'=>['col'=>'claveBank','tipo'=>'string']
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
	$sql = "SELECT bankaccounts.accountcode as glAccount,
	bankaccounts.bankaccountcode as claveBank,
	chartmaster.accountname as bank,
	bankaccountname as nameBank,
	bankaccountnumber as numBank,
	bankaddress as addressBank,
	currcode as currCode,
	IF(invoice = '0','NO','SI') as invoice,
	bank_name as nameBank
FROM bankaccounts 
LEFT JOIN banks ON bankaccounts.bankid = banks.bank_id,
	chartmaster
WHERE bankaccounts.accountcode = chartmaster.accountcode
AND nu_activo = '1'";

	// datos adicionales de filtrado
	if(!empty($info['glAccountFiltro'])&&$info['glAccountFiltro']!="-1"){
		$sql .= " AND bankaccounts.accountcode = '" . $info['glAccountFiltro'] . "' ";
	}
	if(!empty($info['bankFiltro'])&&$info['bankFiltro']!="-1"){
		$sql .= " AND chartmaster.accountname = '" . $info['bankFiltro'] . "' ";
	}
	if(!empty($info['nameBankFiltro'])){
		$sql .= " AND ( bankaccountname =  '" . $info['nameBankFiltro'] . "' )"; 
	}
	if(!empty($info['claveBankFiltro'])){
		$sql .= " AND ( bankaccounts.bankaccountcode =  '" . $info['claveBankFiltro'] . "' )"; 
	}
	if(!empty($info['numBankFiltro'])){
		$sql .= " AND ( bankaccountnumber =  '" . $info['numBankFiltro'] . "' )"; 
	}
	if(!empty($info['addressBankFiltro'])){
		$sql .= " AND ( bankaddress LIKE  '%" . $info['addressBankFiltro'] . "' )"; 
	}

	
	
	// datos adicionales de filtrado

	// datos adicionales de ordenamiento
	$sql .= " ORDER BY 'clave' ASC";

	$data = ['success'=>false,'msg'=>$sql,'content'=>[]];


	$result = DB_query($sql, $db);

	// procesamiento de la información obtenida
	while ($rs = DB_fetch_array($result)) {
		$data['content'][] = [
			'glAccount'=>utf8_encode($rs['glAccount']),// 0
			'claveBank'=>utf8_encode($rs['claveBank']),// 1
			'nameBank'=>utf8_encode($rs['nameBank']),// 1
			'numBank'=>utf8_encode($rs['numBank']),// 2
			'addressBank'=>utf8_encode($rs['addressBank']),// 3
			'currCode'=>utf8_encode($rs['currCode']),// 5
			'invoice'=>utf8_encode($rs['invoice']),// 5
			'bank'=>utf8_encode($rs['bank']),// 2
			'modificar'=>'<span class="modificar glyphicon glyphicon-edit"></span>',// 7
			'eliminar'=>'<span class="eliminar glyphicon glyphicon-trash"></span>',// 8
			'identificador'=>utf8_encode($rs['glAccount'])// 9
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
	$sql = "SELECT  (tagref) as glAccount
	FROM tagsxbankaccounts WHERE accountcode = '".$info['identificador']."'";

	try {
		// obtención del query para la inserción
		$result = DB_query($sql, $db);
		// comprobación de éxito de la generación de la información
		if($result == true){
			$data['success'] = true;
			while ($rs = DB_fetch_array($result)) {
				$data['tagrefs'][] = $rs['glAccount'];
			}
			DB_Txn_Commit($db);
			
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
 * Función para el guardado de la información de los ítems generados
 * @param	[DBInstance]	$db		Instancia de la base de datos
 * @return	[Array]			$data	Arreglo con la respuesta verdadera o falsa, según lo obtenido
 */
function store($db){
	// declaracion de variables de la función
	$data = ['success'=>false,'msg'=>'Ocurrió un incidente inesperado. Favor de contactar con el administrador.'];
	$info = $_POST;


	$sql="SELECT count(accountcode) as contt
			FROM bankaccounts WHERE accountcode = '".$info['glAccount']."' AND nu_activo = '1'";
	$resultIn = DB_query($sql, $db);
	$myrow = DB_fetch_array($resultIn);
	$data['contt'] = $myrow['contt'];

	if ($myrow['contt'] > 0) {
		$data['msg'] = 'El codigo de la cuenta bancaria ya existe en la base de datos';
		return $data;
	}
	if (strlen($_POST['nameBank']) >50) {
		$data['msg'] = 'El nombre de la cuenta bancaria debe ser cincuenta caracteres o menos';
		return $data;
	}
	if ( trim($_POST['bank']) == '' ) {
		$data['msg'] = 'El nombre del banco no puede estar vacío';
		return $data;
	}
	if ( trim($_POST['nameBank']) == '' ) {
		$data['msg'] = 'El nombre de la cuenta bancaria no puede estar vacío.';
		return $data;
		
	}
	if ( trim($_POST['numBank']) == '' ) {
		$data['msg'] = 'El numero de la cuenta bancaria no puede estar vacío.';
		return $data;
	}
	if (strlen($_POST['numBank']) >50) {
		$data['msg'] = 'El numero de cuenta bancaria debe ser cincuenta caracteres o menos';
		return $data;
			
	}
	if (strlen($_POST['addressBank']) >50) {
		$data['msg'] = 'La dirección del banco debe ser cincuenta caracteres o menos';
		return $data;
		
	}

	$sqlIn = "INSERT INTO bankaccounts (
		accountcode,
		bankaccountname,
		bankaccountcode,
		bankaccountnumber,
		bankaddress,
		currcode,
		invoice,
		tagref,
		bankid,
		nu_activo)
	VALUES ('" . $_POST['glAccount'] . "',
		'" . $_POST['nameBank'] . "',
		'" . $_POST['claveBank'] . "',
		'" . $_POST['numBank'] . "',
		'" . $_POST['addressBank'] . "', 
		'" . $_POST['currCode'] . "',
		'" . $_POST['invoice'] . "',
		'', 
		'" . $_POST['bank'] . "',
		'1')";
	
	
	DB_Txn_Begin($db);

	try {
		// obtención del query para la inserción
		
		$resultIn = DB_query($sqlIn, $db);
		// comprobación de éxito de la generación de la información
		if($resultIn == true){
			$data['success'] = true;
			DB_Txn_Commit($db);
		}else{
			DB_Txn_Rollback($db);
		}
	} catch (Exception $e) {
		// captura del error
		$data['msg'] .= '<br>'.$e->getMessage();
		DB_Txn_Rollback($db);
	}
	foreach ($info['selectUnidadNegocio'] as $key => $value) {
		# code...
		$sql="INSERT INTO tagsxbankaccounts (tagref,accountcode)
			VALUES(
				'".$value."',
				'".$_POST['glAccount']."'
				)";
		try {
			// obtención del query para la inserción
			$resultIn = DB_query($sql, $db);
			// comprobación de éxito de la generación de la información
			if($resultIn == true){
				$data['success'] = true;
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
	
	if($data['success']){
		$data['msg'] = "<p>La nueva cuenta bancaria se ha introducido.</p>";
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
	$identificador = $info['glAccount'];
	$value = 0;
	unset($info['method']);
	unset($info['valid']);
	unset($info['identificador']);
	unset($info['CampoLlave']);


	// $data['content'] = show($db)['content'];

	$sql = "SELECT * FROM `".LNTBLCAT."` WHERE `".IDENTIFICADOR."` = '$identificador' ";
	$result = DB_query($sql, $db);
	// comprobación de consistencia de datos
	if(DB_num_rows($result) == 0){
		$data['msg'] = 'No se encontraron los datos solicitados.';
		return $data;
	}



	
	# reasignación de identificador al objeto general
	$info['identificador'] = $identificador;
	# desarrollo de la transacción de eliminación
	DB_Txn_Begin($db);
	try {
		// obtención de la cadena de ejecución para la actualización
		$sqlUpdate = obtenUpdate($info, $value);
		$resultUpdate = DB_query($sqlUpdate, $db);
		// comprobación de éxito de la información
		if($resultUpdate==true){
			$data['msg'] = "El Registro ".$identificador." ha sido actualizado";
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

	$sql = "DELETE FROM tagsxbankaccounts WHERE accountcode = '".$info['identificador']."'";

	try {
		// obtención del query para la inserción
		$result = DB_query($sql, $db);
		// comprobación de éxito de la generación de la información
		if($result == true){
			$data['success'] = true;
			foreach ($info['selectUnidadNegocio'] as $key => $value) {
				# code...
				$sql="INSERT INTO tagsxbankaccounts (tagref,accountcode)
					VALUES(
						'".$value."',
						'".$_POST['glAccount']."'
						)";
				try {
					// obtención del query para la inserción
					$resultIn = DB_query($sql, $db);
					// comprobación de éxito de la generación de la información
					if($resultIn == true){
						$data['success'] = true;
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
			DB_Txn_Commit($db);
			
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
	# comprobación de identificador
	if(empty($info['identificador'])){
		$data['msg'] = 'Es necesario indicar la cuenta de banco';
		return $data;
	}
	# retorno de mensaje según ejecución de precondición
	$data['msg'] = "No puede eliminarse la cuenta de banco.<br>";

	
	# desarrollo de la transacción de eliminación
	$sql = "UPDATE bankaccounts SET nu_activo = '0'  WHERE accountcode =  '" . $info['identificador'] . "'";
	$result = DB_query( $sql, $db);

	$data['msg'] = "El registro $info[identificador] ha sido eliminado.";
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
function obtenInsercion($info, $value)
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

function obtenUpdate($info, $value)
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
		WHERE `".IDENTIFICADOR."` = '$info[selectObjetoParcial]'";
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
