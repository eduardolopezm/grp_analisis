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
$funcion = 26;

/* INCLUSIÓN DE ARCHIVOS NECESARIOS */
include($PathPrefix . 'config.php');
include($PathPrefix . 'includes/SecurityUrl.php');
include($PathPrefix . 'includes/ConnectDB.inc');
include($PathPrefix . 'includes/SecurityFunctions.inc');
include($PathPrefix . 'includes/SQL_CommonFunctions.inc');

/*********************************************** CONSTANTES ***********************************************/
define('DECPLACE', !empty($_SESSION['DecimalPlaces'])? $_SESSION['DecimalPlaces'] : 2);
define('LNTBLCAT', 'debtorsmaster');
//define('DEPTORNO',  GetNextTransNo ( 500, $db ))
define('IDENTIFICADOR', 'debtorno');
define('ACTIVECOL', 'ind_activo');
define('DATA', [
		'debtorno' => ['col' => 'debtorno', 'tipo' => 'string'],
		'typePerso' => ['col' => 'tipo', 'tipo' => 'string'],
		'paternRazon' => ['col' => 'name1', 'tipo' => 'string'],
		'materno' => ['col' => 'name2', 'tipo' => 'string'],
		'nombres' => ['col' => 'name3', 'tipo' => 'string'],
		'selectRegimenFiscal' => ['col' => 'regimenFiscal_id', 'tipo' => 'string'],
		'selectCFDI' => ['col' => 'usoCFDI', 'tipo' => 'string'],
		'numRegistro' => ['col' => 'numRegistro', 'tipo' => 'string'],
		'reqComprobante' => ['col' => 'reqComprobante', 'tipo' => 'string'],
		'tipoDir' => ['col' => 'tipoDir', 'tipo' => 'string'],
		'calle' => ['col' => 'address1', 'tipo' => 'string'],
		'poblacion' => ['col' => 'address2', 'tipo' => 'string'],
		'selectRegion' => ['col' => 'address3', 'tipo' => 'string'],
		'selectEstado' => ['col' => 'address4', 'tipo' => 'string'],
		'cp' => ['col' => 'address5', 'tipo' => 'string'],
		'distrito' => ['col' => 'distrito', 'tipo' => 'string'],
		'numExt' => ['col' => 'numExt', 'tipo' => 'string'],
		'numInt' => ['col' => 'numInt', 'tipo' => 'string'],
		'ext' => ['col' => 'ext', 'tipo' => 'string'],
		'email' => ['col' => 'email', 'tipo' => 'string'],
		'activo' => ['col' => 'activo', 'tipo' => 'string'],
		'identificador'=>['col'=>'debtorno','tipo'=>'string']
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
	$sql = "SELECT
	debtorsmaster.debtorno,
	debtorsmaster.tipo as 'type',
	debtorsmaster.name as 'name',
	debtorsmaster.address1 as 'street',
	debtorsmaster.numExt as 'numExt',
	debtorsmaster.numInt as 'numInt',
	debtorsmaster.distrito as 'distrito',
	debtorsmaster.address2 as 'colony',
	debtorsmaster.address3 as 'region',
	debtorsmaster.address4 as 'estado',
	debtorsmaster.address5 as 'cp',
	CONCAT(sat_usocfdi.c_UsoCFDI, ' - ' , `sat_usocfdi`.`descripcion`) AS 'cfdi',
	CONCAT(`sat_regimenfiscal`.`c_RegimenFiscal`, ' - ' , `sat_regimenfiscal`.`descripcion`) AS 'regimenFiscal',
	debtorsmaster.numRegistro as 'numRegistro',
	IF(debtorsmaster.reqComprobante='1','SI','NO') AS 'reqComprobante',
	debtorsmaster.tipoDir as 'tipoDir',
	custbranch.taxid as rfc,
	custbranch.phoneno as telefono,
	custbranch.movilno as movil,
	custbranch.custpais as pais,
	custbranch.email as 'email',
	debtorsmaster.activo
	FROM debtorsmaster
	LEFT JOIN custbranch
	ON custbranch.debtorno = debtorsmaster.debtorno
	LEFT JOIN `sat_usocfdi`
	ON `sat_usocfdi`.`c_UsoCFDI` = `debtorsmaster`.`usoCFDI`
	LEFT JOIN `sat_regimenfiscal`
	ON `sat_regimenfiscal`.`c_RegimenFiscal` = `debtorsmaster`.`regimenFiscal_id`
	WHERE 1 = 1";
	// debtorsmaster.activo = '1'

	// datos adicionales de filtrado

	// if(!empty($info['paternRazonFiltro'])){
	// 	$sql .= " AND ( `debtorsmaster`.`name1` LIKE  '%" . $info['paternRazonFiltro'] . "%' )"; 
	// }
	// if(!empty($info['maternoFiltro'])){
	// 	$sql .= " AND ( `debtorsmaster`.`name2` LIKE  '%" . $info['maternoFiltro'] . "%' )"; 
	// }
	if(!empty($info['txtIcFiltro'])){
		$sql .= " AND ( `debtorsmaster`.`debtorno` LIKE  '%" . $info['txtIcFiltro'] . "%' )"; 
	}
	if(!empty($info['apellidoFiltro'])){
		$sql .= " AND ( `debtorsmaster`.`name` LIKE  '%" . $info['apellidoFiltro'] . "%' )"; 
	}
	if(!empty($info['nombresFiltro'])){
		$sql .= " AND ( `debtorsmaster`.`name3` LIKE  '%" . $info['nombresFiltro'] . "%' )"; 
	}
	if(!empty($info['rfcFiltro'])){
		$sql .= " AND ( `custbranch`.`taxid` LIKE  '%" . $info['rfcFiltro'] . "%' )"; 
	}
	if(!empty($info['paisFiltro'])){
		$sql .= " AND ( `custbranch`.`custpais` LIKE  '%" . $info['paisFiltro'] . "%' )"; 
	}
	if(!empty($info['selectEstadoFiltro']) &&  $info['selectEstadoFiltro'] != '-1'){
		$sql .= " AND ( `debtorsmaster`.`address4` LIKE  '%" . $info['selectEstadoFiltro'] . "%' )"; 
	}
	if(!empty($info['selectRegionFiltro']) &&  $info['selectRegionFiltro'] != '-1'){
		$sql .= " AND ( `debtorsmaster`.`address3` LIKE  '%" . $info['selectRegionFiltro'] . "%' )"; 
	}
	
	if(!empty($info['distritoFiltro'])){
		$sql .= " AND (`debtorsmaster`.`distrito` LIKE '%" . $info['distritoFiltro'] . "%' )";
	}
	
	if(!empty($info['poblacionFiltro'])){
		$sql .= " AND (`debtorsmaster`.`address2` LIKE '%" . $info['poblacionFiltro'] . "%' )";
	}
	
	// datos adicionales de filtrado

	// datos adicionales de ordenamiento
	$sql .= " ORDER BY 'debtorno' ASC";

	$data = ['success'=>false,'msg'=>$sql,'content'=>[]];


	$result = DB_query($sql, $db);

	// procesamiento de la información obtenida
	while ($rs = DB_fetch_array($result)) {
		$data['content'][] = [
			'debtorno'=>utf8_encode($rs['debtorno']),// 0
			'type'=>utf8_encode($rs['type']),// 1
			'name'=>utf8_encode($rs['name']),// 1
			'street'=>utf8_encode($rs['street']),// 2
			'numExt'=>utf8_encode($rs['numExt']),// 2
			'numInt'=>utf8_encode($rs['numInt']),// 3
			'distrito'=>utf8_encode($rs['distrito']),// 4
			'colony'=>utf8_encode($rs['colony']),// 5
			'region'=>utf8_encode($rs['region']),// 5
			'estado'=>utf8_encode($rs['estado']),// 6
			'cp'=>utf8_encode($rs['cp']),// 6
			'cfdi'=>utf8_encode($rs['cfdi']),// 6
			'regimenFiscal'=>utf8_encode($rs['regimenFiscal']),// 6
			'numRegistro'=>utf8_encode($rs['numRegistro']),// 6
			'reqComprobante'=>utf8_encode($rs['reqComprobante']),// 6
			'tipoDir'=>utf8_encode($rs['tipoDir']),// 6
			'rfc'=>utf8_encode($rs['rfc']),// 6
			'telefono'=>utf8_encode($rs['telefono']),// 6
			'movil'=>utf8_encode($rs['movil']),// 6
			'pais'=>utf8_encode($rs['pais']),// 6
			'email'=>utf8_encode($rs['email']),// 6
			'activo'=> ($rs['activo'] == 1 ? 'Activo' : 'Inactivo'),
			'modificar'=>'<span class="modificar glyphicon glyphicon-edit"></span>',// 7
			'eliminar'=>'<span class="eliminar glyphicon glyphicon-trash"></span>',// 8
			'identificador'=>utf8_encode($rs['debtorno'])// 9
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
	// $sql = "SELECT * FROM ".LNTBLCAT." WHERE `".IDENTIFICADOR."` = '".$info['identificador']."' ";

	$sql ="SELECT
	debtorsmaster.debtorno,
	debtorsmaster.tipo as 'type',
	debtorsmaster.name1 as 'name1',
	debtorsmaster.name2 as 'name2',
	debtorsmaster.name3 as 'name3',
	debtorsmaster.address1 as 'street',
	debtorsmaster.numExt as 'numExt',
	debtorsmaster.numInt as 'numInt',
	debtorsmaster.distrito as 'distrito',
	debtorsmaster.address2 as 'colony',
	debtorsmaster.address3 as 'region',
	debtorsmaster.address4 as 'estado',
	debtorsmaster.address5 as 'cp',
	debtorsmaster.usoCFDI AS 'cfdi',
	debtorsmaster.regimenFiscal_id AS 'regimenFiscal',
	debtorsmaster.numRegistro as 'numRegistro',
	debtorsmaster.reqComprobante AS 'reqComprobante',
	debtorsmaster.tipoDir as 'tipoDir',
	debtorsmaster.ext as 'ext',
	custbranch.taxid as rfc,
	custbranch.phoneno as telefono,
	custbranch.movilno as movil,
	custbranch.custpais as pais,
	custbranch.email as 'email',
	debtorsmaster.activo
	FROM debtorsmaster
	LEFT JOIN custbranch
	ON custbranch.debtorno = debtorsmaster.debtorno
	LEFT JOIN `sat_usocfdi`
	ON `sat_usocfdi`.`c_UsoCFDI` = `debtorsmaster`.`usoCFDI`
	LEFT JOIN `sat_regimenfiscal`
	ON `sat_regimenfiscal`.`c_RegimenFiscal` = `debtorsmaster`.`regimenFiscal_id`
	WHERE `debtorsmaster`.`debtorno` = '".$info['identificador']."'";

	$result = DB_query($sql, $db);

	// comprobación de existencia de la información
	if(DB_num_rows($result) == 0){
		$data['msg'] = 'No se encontraron los datos solicitados.';
		return $data;
	}
	// procesamiento de la información obtenida
	while ($rs = DB_fetch_array($result)) {
		$content = [];
		// foreach (DATA as $campo => $columna) { $content[$campo] = convierteDato($rs, $columna); }
		$content[] = [
			'debtorno'=>utf8_encode($rs['debtorno']),// 0
			'typePerso'=>utf8_encode($rs['type']),// 1
			'paternRazon'=>utf8_encode($rs['name1']),// 1
			'materno'=>utf8_encode($rs['name2']),// 1
			'nombres'=>utf8_encode($rs['name3']),// 1
			'calle'=>utf8_encode($rs['street']),// 2
			'numExt'=>utf8_encode($rs['numExt']),// 2
			'numInt'=>utf8_encode($rs['numInt']),// 3
			'distrito'=>utf8_encode($rs['distrito']),// 4
			'poblacion'=>utf8_encode($rs['colony']),// 5
			'selectRegion'=>utf8_encode($rs['region']),// 5
			'selectEstado'=>utf8_encode($rs['estado']),// 6
			'cp'=>utf8_encode($rs['cp']),// 6
			'selectCFDI'=>utf8_encode($rs['cfdi']),// 6
			'selectRegimenFiscal'=>utf8_encode($rs['regimenFiscal']),// 6
			'numRegistro'=>utf8_encode($rs['numRegistro']),// 6
			'reqComprobante'=>utf8_encode($rs['reqComprobante']),// 6
			'tipoDir'=>utf8_encode($rs['tipoDir']),// 6
			'rfc'=>utf8_encode($rs['rfc']),// 6
			'telefono'=>utf8_encode($rs['telefono']),// 6
			'ext'=>utf8_encode($rs['ext']),// 6
			'movil'=>utf8_encode($rs['movil']),// 6
			'pais'=>utf8_encode($rs['pais']),// 6
			'email'=>utf8_encode($rs['email']),// 6
			'activo'=>utf8_encode($rs['activo']),// 6
			'identificador'=>utf8_encode($rs['debtorno'])// 9
		];
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
	// $valid = empty($info['valid']);
	$Registro = "$info[debtorno]";
	$producto = "$info[txtNombre]";
	$region = $_POST ['selectPais'] == 'México' ? $_POST ['selectRegion'] : $_POST ['region'];
	$estado = $_POST ['selectPais'] == 'México' ? $_POST ['selectEstado'] : $_POST ['estado'];

	$sql = "SELECT DISTINCT taxid as rfc 
		FROM custbranch  
		WHERE taxid = '".$info['rfc']."'";
	
	try{
		$result = DB_query($sql, $db);
		$rs = DB_fetch_array($result);
		// comprobación de éxito de la información
	} catch (Exception $e) {
		// captura del error
		$data['msg'] .= '<br>'.$e->getMessage();
		DB_Txn_Rollback($db);
	}
	if($rs['rfc'] != ''){
		if($rs['rfc'] != 'XXXX010101XXX' && $rs['rfc'] != 'XEXX010101000'){
			$data['msg'] = "No se puede registrar el contribuyente, el RFC: ".$info['rfc']." ya ha sido capturado previamente en el sistema.";
			return $data;
		}else{
			if($info['numRegistro'] != ''){
				$sql = "SELECT COUNT(numRegistro) as contt 
				FROM debtorsmaster  
				WHERE numRegistro = '".$info['numRegistro']."'";
				try{
					$result = DB_query($sql, $db);
					$rs = DB_fetch_array($result);
					// comprobación de éxito de la información
				} catch (Exception $e) {
					// captura del error
					$data['msg'] .= '<br>'.$e->getMessage();
					DB_Txn_Rollback($db);
				}
				if($rs['contt'] > 0){
					$data['msg'] = "No se puede registrar el contribuyente, el número de registro: ".$info['numRegistro']." ya ha sido capturado previamente en el sistema.";
					return $data;
				}
			}
			
		}

	}


	// comprobación de existencia
	if(compruebaCampoLlave($db, $info)){
		$data['msg'] = "La clave $Registro fue capturado previamente en el sistema.";
		// $data['content'] = show($db)['content'];
		return $data;
	}

	// if(!compruebaCampoProducto($db, $info)){
	// 	$data['msg'] = "El código de producto $producto no existe en el sistema.";
	// 	$data['content'] = show($db)['content'];
	// 	return $data;
	// }

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
		// $sqlIn = obtenInsercion($info);
		$sqlIn = "INSERT INTO debtorsmaster (
			debtorno,
			tipo,
			name,
			name1,
			name2,
			name3,
			regimenFiscal_id,
			usoCFDI,
			numRegistro,
			reqComprobante,
			tipoDir,
			address1,
			address2,
			address3,
			address4,
			address5,
			distrito,
			numExt,
			numInt,
			ext,
			direccion,
			currcode,
			salestype,
			paymentterms

	)
VALUES (
	'" . $_POST ['debtorno'] . "',
	'" . $_POST ['typePerso'] . "',
	'" . (utf8_decode(strtoupper ( trim ( htmlspecialchars_decode ( $_POST ['paternRazon'], ENT_NOQUOTES ) . ' ' . trim ( htmlspecialchars_decode ( $_POST ['materno'], ENT_NOQUOTES ) ) . ' ' . trim ( htmlspecialchars_decode ( $_POST ['nombres'], ENT_NOQUOTES ) ) ) ))). "',
	'" . (utf8_decode(strtoupper ( trim ( htmlspecialchars_decode ( $_POST ['paternRazon'], ENT_NOQUOTES ) ) ))). "',
	'" . (utf8_decode(strtoupper ( trim ( htmlspecialchars_decode ( $_POST ['materno'], ENT_NOQUOTES ) ) )) ). "',
	'" . (utf8_decode(strtoupper ( trim ( htmlspecialchars_decode ( $_POST ['nombres'], ENT_NOQUOTES ) ) )) ). "',
	'" . $_POST ['selectRegimenFiscal'] . "',
	'" . $_POST ['selectCFDI']. "',
	'" . $_POST ['numRegistro']. "',
	'" . $_POST ['reqComprobante']. "',
	'" . (utf8_decode($_POST ['tipoDir']) ). "',
	'" . (utf8_decode($_POST ['calle'] )). "',
	'" . (utf8_decode($_POST ['poblacion'] )). "',
	'" . (utf8_decode($region)). "',
	'" . (utf8_decode($estado)). "',
	'" . $_POST ['cp']. "',
	'" . (utf8_decode($_POST ['distrito'])). "',
	'" . $_POST ['numExt']. "',
	'" . $_POST ['numInt']. "',
	'" . $_POST ['ext']. "',
	'" . (utf8_decode(strtoupper ( trim ( htmlspecialchars_decode ( $_POST ['calle'], ENT_NOQUOTES ) . ' ' . trim ( htmlspecialchars_decode ( $_POST ['poblacion'], ENT_NOQUOTES ) ) . ' ' . trim ( htmlspecialchars_decode ( $_POST ['selectRegion'], ENT_NOQUOTES ) ) . ' ' . trim ( htmlspecialchars_decode ( $_POST ['selectEstado'], ENT_NOQUOTES ) ) . ' ' . trim ( htmlspecialchars_decode ( $_POST ['cp'], ENT_NOQUOTES ) ) ) ))). "',
	'MXN',
	'L1',
	'01')";
		$resultIn = DB_query($sqlIn, $db);
		// comprobación de éxito de la generación de la información
		if($resultIn == true){
			$data['success'] = true;
			// $data['content'] = show($db)['content'];
			DB_Txn_Commit($db);
		}else{
			DB_Txn_Rollback($db);
		}
	} catch (Exception $e) {
		// captura del error
		$data['msg'] .= '<br>'.$e->getMessage();
		DB_Txn_Rollback($db);
	}

	// custbranch
	try {
		// obtención del query para la inserción
		// $sqlIn = obtenInsercion($info);
		$sqlIn = "INSERT INTO custbranch (
			branchcode,
			debtorno,
			brname,
			specialinstructions,
			braddress1,
			braddress2,
			braddress3,
			braddress4,
			braddress6,
			brpostaddr1,
			brpostaddr2,
			brpostaddr3,
			brpostaddr4,
			brnumext,
			brnumint,
			phoneno,
			movilno,
			taxid,
			email,
			custpais
	)
	VALUES (
		'" . $_POST ['debtorno'] . "',
		'" . $_POST ['debtorno'] . "',
		'" . (utf8_decode(strtoupper ( trim ( htmlspecialchars_decode ( $_POST ['paternRazon'], ENT_NOQUOTES ) . ' ' . trim ( htmlspecialchars_decode ( $_POST ['materno'], ENT_NOQUOTES ) ) . ' ' . trim ( htmlspecialchars_decode ( $_POST ['nombres'], ENT_NOQUOTES ) ) ) ))). "',
		'" . (utf8_decode(strtoupper ( trim ( htmlspecialchars_decode ( $_POST ['paternRazon'], ENT_NOQUOTES ) . ' ' . trim ( htmlspecialchars_decode ( $_POST ['materno'], ENT_NOQUOTES ) ) . ' ' . trim ( htmlspecialchars_decode ( $_POST ['nombres'], ENT_NOQUOTES ) ) ) ))). "',
		'" . (utf8_decode ( $_POST ['calle'] ) ). "',
		'" . (utf8_decode ( $region ) ). "',
		'" . (utf8_decode ( $estado ) ). "',
		'" . (utf8_decode ( $_POST ['cp'] ) ). "',
		'" . (utf8_decode($_POST ['poblacion'])). "',
		'" . (utf8_decode ( $_POST ['calle'] ) ). "',
		'" . (utf8_decode ( $_POST ['poblacion'] ) ). "',
		'" . (utf8_decode ( $region ) ). "',
		'" . (utf8_decode ( $estado ) ). "',
		'" . $_POST ['numExt']. "',
		'" . $_POST ['numInt']. "',
		'" . $_POST ['telefono']. "',
		'" . $_POST ['movil']. "',
		'" . $_POST ['rfc']. "',
		'" . $_POST ['email']. "',
		'" . (utf8_decode ($_POST ['selectPais'])). "'
		)";
			$resultIn = DB_query($sqlIn, $db);
			// comprobación de éxito de la generación de la información
			if($resultIn == true){
				$data['success'] = true;
				// $data['content'] = show($db)['content'];
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
	
		$data['msg'] = "<p>El nuevo registro del Contribuyente <strong>$Registro</strong> ha sido insertado.</p>";
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
	$region = $_POST ['selectPais'] == 'México' ? $_POST ['selectRegion'] : $_POST ['region'];
	$estado = $_POST ['selectPais'] == 'México' ? $_POST ['selectEstado'] : $_POST ['estado'];

	// $data['content'] = show($db)['content'];

	$concepto = $info['categoryid'];
	$sql = "SELECT * FROM `".LNTBLCAT."` WHERE `".IDENTIFICADOR."` = '$identificador' ";
	$result = DB_query($sql, $db);
	// comprobación de consistencia de datos
	if(DB_num_rows($result) == 0){
		$data['msg'] = 'No se encontraron los datos solicitados.';
		return $data;
	}

	$info['identificador'] = $identificador;
	DB_Txn_Begin($db);
	try {
	
		$sqlUpdate ="UPDATE debtorsmaster SET
			debtorno = '" . $_POST ['debtorno'] . "',
			tipo = '" . (utf8_decode($_POST ['typePerso']) ). "',
			name = '" . (utf8_decode(strtoupper ( trim ( htmlspecialchars_decode ( $_POST ['paternRazon'], ENT_NOQUOTES ) . ' ' . trim ( htmlspecialchars_decode ( $_POST ['materno'], ENT_NOQUOTES ) ) . ' ' . trim ( htmlspecialchars_decode ( $_POST ['nombres'], ENT_NOQUOTES ) ) ) ))). "',
			name1 = '" . (utf8_decode(strtoupper ( trim ( htmlspecialchars_decode ( $_POST ['paternRazon'], ENT_NOQUOTES ) ) ))). "',
			name2 = '" . (utf8_decode(strtoupper ( trim ( htmlspecialchars_decode ( $_POST ['materno'], ENT_NOQUOTES ) ) )) ). "',
			name3 = '" . (utf8_decode(strtoupper ( trim ( htmlspecialchars_decode ( $_POST ['nombres'], ENT_NOQUOTES ) ) )) ). "',
			regimenFiscal_id = '" . $_POST ['selectRegimenFiscal'] . "',
			usoCFDI = '" . $_POST ['selectCFDI']. "',
			numRegistro = '" . $_POST ['numRegistro']. "',
			reqComprobante = '" . $_POST ['reqComprobante']. "',
			tipoDir = '" . (utf8_decode($_POST ['tipoDir']) ). "',
			address1 = '" . (utf8_decode ( $_POST ['calle'] ) ). "',
			address2 = '" . (utf8_decode ( $_POST ['poblacion'] ) ). "',
			address3 = '" . (utf8_decode ( $region ) ). "',
			address4 = '" . (utf8_decode ( $estado ) ). "',
			address5 = '" . $_POST ['cp']. "',
			distrito = '" . (utf8_decode($_POST ['distrito'])). "',
			numExt = '" . $_POST ['numExt']. "',
			numInt = '" . $_POST ['numInt']. "',
			ext = '" . $_POST ['ext']. "',
			activo = '" . $_POST ['activo']. "',
			direccion = '" . (utf8_decode(strtoupper ( trim ( htmlspecialchars_decode ( $_POST ['calle'], ENT_NOQUOTES ) . ' ' . trim ( htmlspecialchars_decode ( $_POST ['poblacion'], ENT_NOQUOTES ) ) . ' ' . trim ( htmlspecialchars_decode ( $_POST ['selectRegion'], ENT_NOQUOTES ) ) . ' ' . trim ( htmlspecialchars_decode ( $_POST ['selectEstado'], ENT_NOQUOTES ) ) . ' ' . trim ( htmlspecialchars_decode ( $_POST ['cp'], ENT_NOQUOTES ) ) ) ))). "'
			WHERE `".IDENTIFICADOR."` = '".$identificador."'";
		$resultUpdate = DB_query($sqlUpdate, $db);
		// comprobación de éxito de la información
		if($resultUpdate==true){
			$data['msg'] = "El Contribuyente ha sido actualizado";
			$data['success'] = true;
			DB_Txn_Begin($db);
			// $data['content'] = show($db)['content'];
		}else{
			DB_Txn_Rollback($db);
		}
	} catch (Exception $e) {
		// captura del error
		$data['msg'] .= '<br>'.$e->getMessage();
		DB_Txn_Rollback($db);
	}


	try {
	
		$sqlUpdate ="UPDATE custbranch SET
			branchcode = '" . $_POST ['debtorno'] . "',
			debtorno = '" . $_POST ['debtorno'] . "',
			brname = '" . (utf8_decode(strtoupper ( trim ( htmlspecialchars_decode ( $_POST ['paternRazon'], ENT_NOQUOTES ) . ' ' . trim ( htmlspecialchars_decode ( $_POST ['materno'], ENT_NOQUOTES ) ) . ' ' . trim ( htmlspecialchars_decode ( $_POST ['nombres'], ENT_NOQUOTES ) ) ) ))). "',
			specialinstructions = '" . (utf8_decode(strtoupper ( trim ( htmlspecialchars_decode ( $_POST ['paternRazon'], ENT_NOQUOTES ) . ' ' . trim ( htmlspecialchars_decode ( $_POST ['materno'], ENT_NOQUOTES ) ) . ' ' . trim ( htmlspecialchars_decode ( $_POST ['nombres'], ENT_NOQUOTES ) ) ) ))). "',
			braddress1 = '" . (utf8_decode ( $_POST ['calle'] ) ). "',
			braddress2 = '" . (strtoupper ( $region ) ). "',
			braddress3 = '" . (strtoupper ( $estado ) ). "',
			braddress4 = '" . ( $_POST ['cp'] ). "',
			braddress6 = '" . (utf8_decode ( $_POST ['poblacion']) ). "',
			brpostaddr1 = '" . (utf8_decode ( $_POST ['calle'] ) ). "',
			brpostaddr2 = '" . (utf8_decode ( $_POST ['poblacion'] ) ). "',
			brpostaddr3 = '" . (utf8_decode ( $region ) ). "',
			brpostaddr4 = '" . (utf8_decode ( $estado ) ). "',
			brnumext = '" . $_POST ['numExt']. "',
			brnumint = '" . $_POST ['numInt']. "',
			phoneno = '" . $_POST ['telefono']. "',
			movilno = '" . $_POST ['movil']. "',
			email = '" . $_POST ['email']. "',
			taxid = '" . $_POST ['rfc']. "',
			custpais = '" . (utf8_decode($_POST ['selectPais'])). "'
			WHERE `".IDENTIFICADOR."` = '".$identificador."'";
		$resultUpdate = DB_query($sqlUpdate, $db);
		// comprobación de éxito de la información
		if($resultUpdate==true){
			$data['msg'] = "El Contribuyente ha sido actualizado";
			$data['success'] = true;
			DB_Txn_Begin($db);
			// $data['content'] = show($db)['content'];
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
	// $data['content'] = show($db)['content'];

	$data['msg'] = "No puede eliminarse el contribuyente Principal.<br>";
	
	# desarrollo de la transacción de eliminación
    $sql = "UPDATE debtorsmaster  SET  activo = '0' WHERE debtorno = '" . $info['identificador'] . "'";
	
	// DB_Txn_Begin($db);
	$result = DB_query( $sql, $db);
	// $data['content'] = show($db)['content'];

	$data['msg'] = "El Contribuyente $info[identificador] ha sido eliminado.";
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
	$campos = ''; 
	$datos = ''; 
	$flag = 0; 
	$strData = ['string','select'];
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
		WHERE `".IDENTIFICADOR."` = '$info[debtorno]'";
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
