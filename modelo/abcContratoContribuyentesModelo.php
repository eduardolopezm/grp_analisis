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
$funcion = 2510;
$contratoSelected = '';

/* INCLUSIÓN DE ARCHIVOS NECESARIOS */
include($PathPrefix . 'config.php');
include($PathPrefix . 'includes/SecurityUrl.php');
include($PathPrefix . 'includes/ConnectDB.inc');
include($PathPrefix . 'includes/SecurityFunctions.inc');
include($PathPrefix . 'includes/SQL_CommonFunctions.inc');

/*********************************************** CONSTANTES ***********************************************/
define('DECPLACE', !empty($_SESSION['DecimalPlaces'])? $_SESSION['DecimalPlaces'] : 2);
define('LNTBLCAT', 'tb_contratos');
define('IDENTIFICADOR', 'id_contrato');
define('ACTIVECOL', 'ind_activo');
define('DATA', [
		'selectUnidadNegocio' => ['col' => 'tagref', 'tipo' => 'string'],
		'selectUnidadEjecutora' => ['col' => 'ln_ue', 'tipo' => 'string'],
		'selectContratos' => ['col' => 'id_confcontratos', 'tipo' => 'string'],
		'selectObjetosParciales' => ['col' => '', 'tipo' => 'string'],
		'contribuyente' => ['col' => 'contribuyente', 'tipo' => 'none'],
		'contribuyenteID' => ['col' => 'id_debtorno', 'tipo' => 'string'],
		'userID' => ['col' => 'userid', 'tipo' => 'string'],
		'description' => ['col' => 'ln_descripcion', 'tipo' => 'string'],
		'fechaEfectiva' => ['col' => 'dtm_fecha_efectiva', 'tipo' => 'now'],
		'dtFechaInicio' => ['col' => 'dtm_fecha_inicio', 'tipo' => 'string'],
		'dtFechaVigencia' => ['col' => 'dtm_fecha_vigencia', 'tipo' => 'string'],
		'selectTipoPeriodo' => ['col' => 'enum_periodo', 'tipo' => 'string'],
		'selectEstatus' => ['col' => 'enum_status', 'tipo' => 'string'],
		'nuPeriodicidad' => ['col' => 'nu_periodicidad', 'tipo' => 'string'],
		'identificador'=>['col'=>'id_contrato','tipo'=>'string']
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
	
	$sqlJoinFiltros = "";
	$info['txtFiltrosJson'] = json_decode($info['txtFiltrosJson']);
	$refinadoCementerios = "";
	foreach ($info['txtFiltrosJson'] as $key => $value) {
		// echo "<br>id: ".$value->id." - nombre: ".$value->nombre;
		if (isset($info['txtAtributo_'.$value->id]) && !empty($info['txtAtributo_'.$value->id]) && $value->id != '48') {
			// echo "<br>id: ".$value->id." - nombre: ".$value->nombre." - dato: ".$info['txtAtributo_'.$value->id];
			$nombreTabla = "atrib".$value->id;
			$sqlJoinFiltros .= " JOIN tb_propiedades_atributos ".$nombreTabla." ON ".$nombreTabla.".id_folio_contrato = contratos.id_contrato AND ".$nombreTabla.".id_etiqueta_atributo = '".$value->id."' AND ".$nombreTabla.".ln_valor like '%".$info['txtAtributo_'.$value->id]."%' ";
		}

		// validacion si es cementerios y es finado
		if ($info['selectContratosFiltro'] == '1' && $value->id == '48' && !empty($info['txtAtributo_'.$value->id])) {
			$refinadoCementerios = $info['txtAtributo_'.$value->id];
		}
	}

	if ($info['selectContratosFiltro'] == '1') {
		// Buscar en todos los finados
		$sqlFinado = "SELECT
		id_atributos
		FROM tb_cat_atributos_contrato
		WHERE id_contratos = 1
		AND ln_etiqueta like '%FINADO%'";
		$resultFinado = DB_query($sqlFinado, $db);
		while ($rsFinado = DB_fetch_array($resultFinado)) {
			$nombreTabla = "atrib".$rsFinado['id_atributos'];

			$sqlJoinFiltros .= " LEFT JOIN tb_propiedades_atributos ".$nombreTabla." ON ".$nombreTabla.".id_folio_contrato = contratos.id_contrato AND ".$nombreTabla.".id_etiqueta_atributo = '".$rsFinado['id_atributos']."' AND ".$nombreTabla.".ln_valor like '%".$refinadoCementerios."%' ";
		}

	}
	// echo "<br><pre>".$sqlJoinFiltros."</pre>";
	// exit();

	$sql = "SELECT contratos.id_contrato as clave,    
	CONCAT(tags.tagref , ' - ' , tags.tagdescription ) AS unidadNegocio,
	CONCAT(ues.ue , ' - ' , ues.desc_ue ) AS unidadEjecutora,    
	CONCAT(configContrato.id_contratos, ' - ', configContrato.id_loccode,' - ', locations.locationname) AS configContrato,
	configContrato.id_contratos AS idconfContrato,  
	debtorsmaster.name as contribuyente,
	contratos.dtm_fecha_inicio as fechaInicio,
	contratos.enum_status as estatus,
	(SELECT tb_propiedades_atributos.ln_valor FROM tb_propiedades_atributos Where tb_propiedades_atributos.id_folio_contrato = contratos.id_contrato
	ORDER BY id_propiedades_atributos ASC LIMIT 1 OFFSET 0) as atributo1,
	(SELECT tb_propiedades_atributos.ln_valor FROM tb_propiedades_atributos Where tb_propiedades_atributos.id_folio_contrato = contratos.id_contrato
	ORDER BY id_propiedades_atributos ASC LIMIT 1 OFFSET 1) as atributo2			
	FROM tb_contratos AS contratos JOIN tags on (tags.tagref = contratos.tagref)    
	JOIN tb_cat_unidades_ejecutoras as ues on (ues.ue = contratos.ln_ue) 
	JOIN tb_contratos_contribuyentes as configContrato on (configContrato.id_contratos = contratos.id_confcontratos) 
	JOIN locations on (configContrato.id_loccode = locations.loccode)  
	JOIN debtorsmaster on (debtorsmaster.debtorno = contratos.id_debtorno)
	".$sqlJoinFiltros."
	WHERE contratos.ind_activo = '1'";

	$sqlWhere = "";
	
	if(!empty($info['selectUnidadEjecutoraFiltro']) && $info['selectUnidadEjecutoraFiltro']!="-1"){
		$sqlWhere .= " AND contratos.ln_ue = '" . $info['selectUnidadEjecutoraFiltro'] . "' ";
	}

	if(!empty($info['contribuyenteIDFiltro']) && $info['contribuyenteIDFiltro']!="-1"){
		$sqlWhere .= " AND contratos.id_debtorno = '" . $info['contribuyenteIDFiltro'] . "' ";
	}
	
	if(!empty($info['txtFechaInicial']) && !empty($info['txtFechaFinal'])){
		$dateini=date("Y-m-d", strtotime($info['txtFechaInicial']));
		$datefin=date("Y-m-d", strtotime($info['txtFechaFinal']));
		$sqlWhere .= " AND contratos.dtm_fecha_inicio BETWEEN '" .  $dateini . " 00:00:00' AND '" .  $datefin . " 23:59:59'";
	}
	
	if(!empty($info['txtEstatus'])&&$info['txtEstatus']!="-1"){
		$sqlWhere .= "AND contratos.enum_status = '".$info['txtEstatus']."'";
	}
	
	// if(!empty($info['txtAtributo'])&&$info['txtAtributo']!="-1"){
	// 	$sqlWhere = " AND (SELECT tb_propiedades_atributos.ln_valor FROM tb_propiedades_atributos Where tb_propiedades_atributos.id_folio_contrato = contratos.id_contrato ORDER BY id_propiedades_atributos ASC LIMIT 1 OFFSET 0) = '" . $info['txtAtributo'] . "' ";
	// }

	// if(!empty($info['txtAtributo2'])&&$info['txtAtributo2']!="-1"){
	// 	$sqlWhere = " AND (SELECT tb_propiedades_atributos.ln_valor FROM tb_propiedades_atributos Where tb_propiedades_atributos.id_folio_contrato = contratos.id_contrato ORDER BY id_propiedades_atributos ASC LIMIT 1 OFFSET 1) = '" . $info['txtAtributo2'] . "' ";
	// }

	if(!empty($info['selectContratosFiltro'])&&$info['selectContratosFiltro']!="-1"){
		$sqlWhere .= " AND contratos.id_confcontratos = '" . $info['selectContratosFiltro'] . "' ";
	}

	// datos adicionales de ordenamiento
	$sql .= $sqlWhere." ORDER BY clave ASC";

	$data = ['success'=>false,'msg'=>$sql,'content'=>[]];

	if($_SESSION['UserID'] == 'desarrollo3'){
		// echo "<br><pre>".$sql."</pre>";
		// exit();
	}

	$result = DB_query($sql, $db);

	// procesamiento de la información obtenida
	while ($rs = DB_fetch_array($result)) {

		$contribuyente = "'$rs[contribuyente]'";
		$configContrato = "'$rs[configContrato]'";

		$eliminar = '';
		if (Havepermission($_SESSION['UserID'], 2544, $db) == 1) {
			$eliminar = '<span class="eliminar glyphicon glyphicon-trash"></span>';
		}

		$dataJson = [
			'unidadNegocio'=>utf8_encode($rs['unidadNegocio']),// 0
			'unidadEjecutora'=>utf8_encode($rs['unidadEjecutora']),// 1
			'configContrato'=>utf8_encode($rs['configContrato']),// 1
			'contribuyente'=>utf8_encode($rs['contribuyente']),// 2
			'estatus'=>utf8_encode($rs['estatus']),// 2
			'fechaInicial'=> date("d-m-Y", strtotime($rs['fechaInicio'])),
			'atributo1'=>utf8_encode($rs['atributo1']),// 2
			'atributo2'=>utf8_encode($rs['atributo2']),// 2
			'modificar'=>'<span class="modificar glyphicon glyphicon-edit"></span>',// 7
			'objetoDetalle'=>'<span class="objetoDetalle glyphicon glyphicon-modal-window" onclick="openModalIframe('.$rs['clave'].','.$rs['idconfContrato'].','.utf8_encode($contribuyente).','.utf8_encode($configContrato).')"></span>',// 8
			'atributos'=>'<span class="atributos glyphicon glyphicon-list-alt" onclick="openModalIframeDos('.$rs['clave'].','.$rs['idconfContrato'].','.utf8_encode($contribuyente).')"></span>',// 8
			'adeudo'=>'<span class="atributos glyphicon glyphicon-duplicate" onclick="fnGenerarAdeudos('.$rs['clave'].')"></span>',// 8
			'pase'=>'<span class="atributos glyphicon glyphicon-credit-card" onclick="fnPaseDeCobro('.$rs['clave'].','.utf8_encode($contribuyente).')"></span>',// 8
			'historial'=>'<span class="atributos glyphicon glyphicon-th-list" onclick="fnHistorial('.$rs['clave'].')"></span>',// 8
			'eliminar'=>$eliminar,// 7
			'identificador'=>utf8_encode($rs['clave'])// 9
		];

		$SQLAtributos = "SELECT
		tb_propiedades_atributos.id_etiqueta_atributo,
		tb_cat_atributos_contrato.ln_etiqueta,
		tb_propiedades_atributos.ln_valor
		FROM tb_propiedades_atributos
		JOIN tb_cat_atributos_contrato ON tb_cat_atributos_contrato.id_atributos = tb_propiedades_atributos.id_etiqueta_atributo
		WHERE
		tb_propiedades_atributos.id_folio_contrato = '".$rs['clave']."'
		AND tb_propiedades_atributos.id_folio_configuracion = '".$rs['configContrato']."'
		ORDER BY tb_cat_atributos_contrato.id_atributos ASC";
		$resultAtri = DB_query($SQLAtributos, $db);
		while ($rsAtri = DB_fetch_array($resultAtri)) {
			// $columnasNombresAtributos .= "{ name: 'atributo_".$rs['id']."', type: 'string'},";
			// $columnasNombresGridAtributos .= "{ text: '".$rs['nombre']."', datafield: 'atributo_".$rs['id']."', editable: false, width: '10%', cellsalign: 'center', align: 'center' },";
			$dataJson['atributo_'.$rsAtri['id_etiqueta_atributo']] = utf8_encode($rsAtri['ln_valor']);
		}

		$data['content'][] = $dataJson;

	}
	$data['success'] = true;
	$data['sql'] = $sql;
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
	$sql = "SELECT ".LNTBLCAT.".*, debtorsmaster.name as contribuyente FROM ".LNTBLCAT." JOIN debtorsmaster on (debtorsmaster.debtorno = ".LNTBLCAT.".id_debtorno)
	WHERE `".IDENTIFICADOR."` = '".$info['identificador']."' ";

	$result = DB_query($sql, $db);

	// comprobación de existencia de la información
	if(DB_num_rows($result) == 0){
		$data['msg'] = 'No se encontraron los datos solicitados.';
		return $data;
	}
	// // procesamiento de la información obtenida
	
	while ($rs = DB_fetch_array($result)) {
		$content = [];
		foreach (DATA as $campo => $columna) { $content[$campo] = convierteDato($rs, $columna); }
		$data['content'] = $content;
	}
	
	// $sql = "SELECT debtorsmaster.name FROM debtorsmaster WHERE debtorno = '".$data['content']['contribuyenteID']."'";
	// $result = DB_query($sql, $db);
	// $rs = DB_fetch_array($result);

	// $data['content']['contribuyente'] =  $rs['name'];

	// // $sql = "SELECT id_objetos FROM tb_contratos_objetos_parciales WHERE id_contrato = ".$data['content']['identificador'];
	// // $result = DB_query($sql, $db);

	// // $content = [];

	// // while ($rs = DB_fetch_array($result)) {
	// // 	array_push($content, $rs['id_objetos']);
	// // }

	// // $data['content']['objetosParciales'] = $content;
	$data['content']['dtFechaInicio'] = date("d-m-Y", strtotime($data['content']['dtFechaInicio']));
	// $data['content']['dtFechaVigencia'] = date("d-m-Y", strtotime($data['content']['dtFechaVigencia']));



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
	$info['userID'] = $_SESSION['UserID'];

	// $sql = "SELECT 
	// contratos.id_confcontratos as confContrato,
	// CONCAT(configContrato.id_contratos, ' - ', configContrato.id_loccode,' - ', locations.locationname) AS configContrato,
	// contratos.id_debtorno as contribuyenteID,
	// debtorsmaster.name as contribuyente 	
	// FROM tb_contratos AS contratos
	// LEFT JOIN debtorsmaster on (debtorsmaster.debtorno = contratos.id_debtorno)
	// LEFT JOIN tb_contratos_contribuyentes as configContrato on (configContrato.id_contratos = contratos.id_confcontratos) 
	// LEFT JOIN locations on (configContrato.id_loccode = locations.loccode)  
	// WHERE id_debtorno = '".$_POST['contribuyenteID']."' AND id_confcontratos = '".$_POST['selectContratos']."' AND contratos.ind_activo = '1'";

	// $result = DB_query($sql, $db);
	// $result2 = DB_query($sql, $db);

	// if(DB_num_rows($result) > 0){
	// 	$rs = DB_fetch_array($result2);

	// 	$data['msg'] = "El contribuyente: ".utf8_encode($rs['contribuyente'])." ya ha sido asignado al contrato: ".$rs['configContrato']." previamente en el sistema.";
	// 	return $data;
	// }
	
	// Validar atributos
	$sql = "SELECT
	tb_cat_atributos_contrato.ln_etiqueta
	FROM tb_propiedades_atributos
	JOIN tb_cat_atributos_contrato ON tb_cat_atributos_contrato.id_atributos = tb_propiedades_atributos.id_etiqueta_atributo
	WHERE tb_propiedades_atributos.nu_activo = '1'
	AND tb_propiedades_atributos.id_folio_configuracion = '".$info['selectContratos']."'
	AND tb_propiedades_atributos.id_etiqueta_atributo = '".$info['txtAtributo1ValId']."'
	AND tb_propiedades_atributos.ln_valor = '".$info['txtAtributo1Val']."'";
	$result = DB_query($sql, $db);
	if(DB_num_rows($result) > 0){
		$rs = DB_fetch_array($result);
		$data['msg'] = "".utf8_encode($rs['ln_etiqueta'])." ".$info['txtAtributo1Val']." ya existe";
		return $data;
	}

	$info['dtFechaInicio'] = date("Y-m-d", strtotime($_POST['dtFechaInicio']));
	$info['dtFechaVigencia'] = date("Y-m-d", strtotime($_POST['dtFechaVigencia']));

	$Registro = $_POST['selectContratos']."-".$_POST['selectUnidadNegocio'];
	$value = 0;
	// comprobación de existencia
	// if(compruebaCampoLlave($db, $info)){
	// 	$data['msg'] = "El código $Registro fue capturado previamente en el sistema.";
	// 	$data['content'] = show($db)['content'];
	// 	return $data;
	// }

	$sql = "SELECT valor FROM `tb_cat_tarifas` WHERE  id_tarifa ='".$info["type"]."' AND active = '1'";
	$result = DB_query($sql, $db);
	// comprobación de consistencia de datos
	$rs = DB_fetch_array($result);
	$value = $rs['valor'];
	$contratoID = 0;
	
	unset($info['method']);
	unset($info['valid']);

	// echo "<br>txtAtributo1Val: ".$info['txtAtributo1Val'];
	// echo "<br>txtAtributo1ValId: ".$info['txtAtributo1ValId'];
	// exit();
	
	DB_Txn_Begin($db);
	try {
		// obtención del query para la inserción
		$contratoID = GetNextTransNo(313, $db);

		$info['identificador'] = $contratoID;
		
		$sqlIn = obtenInsercion($info);
		$resultIn = DB_query($sqlIn, $db);

		// $sql = "SELECT MAX(id_contrato) as lastID FROM ".LNTBLCAT;

		// $resultIn = DB_query($sql, $db);
		// $rs = DB_fetch_array($resultIn);
		// $contratoID = $rs['lastID'];

		// $contratoID = DB_Last_Insert_ID($db);
		$data['sql'] = $info['contrato'];

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
 

		$data['contratoID'] = $contratoID;

		$sql = "SELECT ".LNTBLCAT.".*,   debtorsmaster.name as contribuyente FROM ".LNTBLCAT." JOIN debtorsmaster on (debtorsmaster.debtorno = ".LNTBLCAT.".id_debtorno)
		WHERE `".IDENTIFICADOR."` = '".$contratoID."' ";
	
		$result = DB_query($sql, $db);
	
		// comprobación de existencia de la información
		if(DB_num_rows($result) == 0){
			$data['msg'] = 'No se encontraron los datos solicitados.';
			return $data;
		}
		// // procesamiento de la información obtenida
		
		while ($rs = DB_fetch_array($result)) {
			$content = [];
			foreach (DATA as $campo => $columna) { $content[$campo] = convierteDato($rs, $columna); }
			$data['select'] = $content;
		}
		if($_POST['selectContratos'] == 7){
			$sql ="INSERT INTO 
				`tb_contratos_objetos_parciales` ( 
					`id_contrato`, 
					`id_objetos`, 
					`dtm_fecha_efectiva`, 
					`nu_cantidad`, 
					`amt_total`, 
					`ind_activo`
				)VALUES
				(".$contratoID.", 41, now(), 1, 168.98, 1)";

			$result = DB_query($sql, $db);
		}
		$data['msg'] = "<p>El nuevo registro <strong>".$contratoID."</strong> ha sido insertado.</p>";
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
	$value = 0;
	unset($info['method']);
	unset($info['valid']);
	unset($info['identificador']);
	unset($info['CampoLlave']);

	$info['dtFechaInicio'] = date("Y-m-d", strtotime($_POST['dtFechaInicio']));
	$info['dtFechaVigencia'] = date("Y-m-d", strtotime($_POST['dtFechaVigencia']));

	// $data['content'] = show($db)['content'];

	$sql = "SELECT * FROM `".LNTBLCAT."` WHERE `".IDENTIFICADOR."` = '$identificador' ";
	$result = DB_query($sql, $db);
	// comprobación de consistencia de datos
	if(DB_num_rows($result) == 0){
		$data['msg'] = 'No se encontraron los datos solicitados.';
		return $data;
	}

	$sql = "SELECT valor FROM `tb_cat_tarifas` WHERE  id_tarifa ='".$info["type"]."' AND active = '1'";
	$result = DB_query($sql, $db);
	// comprobación de consistencia de datos
	$rs = DB_fetch_array($result);
	$value = $rs['valor'];

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
		$sqlUpdate = obtenUpdate($info, $value);
		$resultUpdate = DB_query($sqlUpdate, $db);

		if (Havepermission($_SESSION ['UserID'], 2548, $db) == 1) {
			// Actualizar contribuyente en el contrato
			$SQL = "UPDATE tb_contratos SET 
			id_debtorno = '".$info['contribuyenteID']."' 
			WHERE id_contrato = '".$identificador."'";
			$res1 = DB_query($SQL, $db);

			// Actualizar pases de cobro no cobrados
			$SQL = "SELECT
			tb_administracion_contratos.id_administracion_contratos,
			tb_administracion_contratos.pase_cobro
			FROM tb_administracion_contratos
			WHERE tb_administracion_contratos.id_contrato = '".$identificador."'
			AND tb_administracion_contratos.pase_cobro <> ''
			AND tb_administracion_contratos.folio_recibo = ''";
			$resultPases = DB_query($SQL, $db);
			while ($myrowPases = DB_fetch_array($resultPases)) {
				$SQL = "UPDATE salesorders SET 
				debtorno = '".$info['contribuyenteID']."', 
				branchcode = '".$info['contribuyenteID']."'
				WHERE orderno = '".$myrowPases['pase_cobro']."'";
				$res2 = DB_query($SQL, $db);
			}
		}
		
		// comprobación de éxito de la información
		if($resultUpdate==true){
			$data['msg'] = "El Registro ".$identificador." ha sido actualizado";
			DB_Txn_Begin($db);
			$data['success'] = true;
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
	$info = $_POST;
	$data = ['success'=>false,'msg'=>'Este contrato no se puede eliminar tiene pase de cobro generado'];

	if(fnValidarExiste($info['identificador'],$db)){
				
		$registrosEncontrados = "";
		$data = ['success'=>false,'msg'=>'Ocurrió un incidente al momento de eliminar la información.'];
		# comprobación de identificador
		if(empty($info['identificador'])){
			$data['msg'] = 'Es necesario indicar el identificador del almacén';
			return $data;
		}

		
		$sql = " UPDATE ".LNTBLCAT." SET ".ACTIVECOL." = 0 WHERE `".IDENTIFICADOR."` = '".$info['identificador']."' ";
		$result = DB_query( $sql, $db);
		$sql = " UPDATE tb_propiedades_atributos SET nu_activo = '0' WHERE id_folio_contrato = '".$info['identificador']."' ";
		$result = DB_query( $sql, $db);


		$data['msg'] = "El registro $info[identificador] ha sido eliminado.";
		$data['success'] = true;
		
		return $data;
	}else{
		
		return $data;

	}
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
		$data = DATA[$input];
		if($data['col']== ''){continue;};
		if($data['tipo']== 'none'){continue;};
		if($flag!=0){ $campos .= ', '; $datos .= ', '; }
		$campos .= $data['col'];

		if($data['tipo']== 'now'){
			$datos .= "now ()";
		}else
			$datos .= in_array($data['tipo'], $strData)? utf8_decode(" '$valor'") : " $valor";
		$flag++;
	}
	# agregado de campo activo
	//$campos .= ", ".ACTIVECOL;
	//$datos .= ", '1'";
	
	return " INSERT INTO ".LNTBLCAT." ($campos) VALUES ($datos) ";
}


function fnValidarExiste($idIdentificador, $db){

	 
	// $idAdminContra = "";
	// $SQL = "SELECT id_administracion_contratos FROM tb_administracion_contratos WHERE id_contrato = '$idIdentificador'";
	// $TransResult = DB_query($SQL, $db);
	// while ($myrow = DB_fetch_array($TransResult)) {
	// 	$idAdminContra = $myrow ['id_administracion_contratos'];
	// }
				
	// $SQL = "SELECT id_administracion_contratos FROM salesorderdetails WHERE id_administracion_contratos = '".$idAdminContra."' ORDER BY orderno ASC";

	// $ErrMsg = "No se encontro la informacion";
	// $TransResult = DB_query($SQL, $db, $ErrMsg);
	// $existeFin="";
	// while ($myrow = DB_fetch_array($TransResult)) {
	// 	if ($myrow['id_administracion_contratos'] == $idAdminContra) {
	// 		$existeFin = true;
	// 	}else{
	// 		$existeFin = false;
	// 	}
	// }
	// return $existeFin;

	$slq = "SELECT COUNT(*) as countt
	FROM tb_administracion_contratos
	JOIN salesorderdetails  on salesorderdetails.id_administracion_contratos = tb_administracion_contratos.id_administracion_contratos
	WHERE id_contrato = '".$idIdentificador."'";
	
	$TransResult = DB_query($slq, $db);
	$row = DB_fetch_array($TransResult);
	return $row['countt'] == 0;
}

function obtenUpdate($info, $value)
{
	$campos = ''; $flag = 0; $identificador = $info['identificador']; $iterador = DATA;
	unset($info['identificador']);
	unset($iterador['identificador']);
	foreach ($info as $input => $valor) {
		if(!array_key_exists($input, $iterador)){ continue; }
		$data = DATA[$input];
		if($data['col']== ''){continue;};
		if($data['tipo']== 'none'){continue;};
		if($flag!=0){ $campos .= ', '; }
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
	}else if($detColumna['tipo'] == 'none'){
		return utf8_encode($resulset[$detColumna['col']]);
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

/**
 * Función para obtener los filtros del contrato seleccionado
 * @param  [type] $db [description]
 * @return [type]     [description]
 */
function fnFiltrosContrato($db) {
	header('Content-type: text/html; charset=ISO-8859-1');

	$SQL = "SET NAMES 'utf8'";
	$TransResult = DB_query($SQL, $db);

	$data = ['success'=>true, 'msg'=>'Se obtuvo la información correctamente'];

	$SQL = "SELECT
	id_atributos as id,
	ln_etiqueta as nombre
	FROM tb_cat_atributos_contrato
	WHERE sn_filtro_panel = 1
	AND ind_activo = 1
	AND id_contratos = '".$_POST['selectContratosFiltro']."'
	ORDER BY id_atributos ASC";
	$result = DB_query($SQL, $db);
	while ($rs = DB_fetch_array($result)) {
		$data['content'][] = [
			'id'=>$rs['id'],
			'nombre'=>$rs['nombre']
		];
	}

	return $data;
}

function fnCamposTabla($db) {
	header('Content-type: text/html; charset=ISO-8859-1');

	$SQL = "SET NAMES 'utf8'";
	$TransResult = DB_query($SQL, $db);

	$data = ['success'=>true, 'msg'=>'Se obtuvo la información correctamente'];

	$columnasNombresAtributos = "";
	$columnasNombresGridAtributos = "";
	$columnasExcel = [0,1,2,3,4,5];
	$valor = 6;
	$SQL = "SELECT
	id_atributos as id,
	ln_etiqueta as nombre
	FROM tb_cat_atributos_contrato
	WHERE sn_filtro_panel = 1
	AND ind_activo = 1
	AND id_contratos = '".$_POST['selectContratosFiltro']."'";
	$result = DB_query($SQL, $db);
	while ($rs = DB_fetch_array($result)) {
		$columnasNombresAtributos .= "{ name: 'atributo_".$rs['id']."', type: 'string'},";
		$columnasNombresGridAtributos .= "{ text: '".$rs['nombre']."', datafield: 'atributo_".$rs['id']."', editable: false, width: '10%', cellsalign: 'center', align: 'center' },";
		$columnasExcel[] = $valor;
		$valor ++;
	}


	$data['tblExcel'] = $columnasExcel; // [0,1,2,3,4,5];
	$data['tblVisual'] = [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14];
	$data['nombreExcel'] = str_replace(' ', '_', traeNombreFuncionGeneral($funcion, $db)).'_'.date('dmY');

	// Nombre campos
	$columnasNombres = "";
	$columnasNombres .= "[";
	$columnasNombres .= "{ name: 'configContrato', type: 'string'},";
	$columnasNombres .= "{ name: 'unidadEjecutora', type: 'string'},";
	$columnasNombres .= "{ name: 'estatus', type: 'string'},";
	$columnasNombres .= "{ name: 'fechaInicial', type: 'string'},";
	$columnasNombres .= "{ name: 'contribuyente', type: 'string'},";
	//$columnasNombres .= "{ name: 'atributo1', type: 'string'},";
	//$columnasNombres .= "{ name: 'atributo2', type: 'string'},";
	$columnasNombres .= $columnasNombresAtributos;
	$columnasNombres .= "{ name: 'modificar', type: 'string'},";
	$columnasNombres .= "{ name: 'objetoDetalle', type: 'string'},";
	$columnasNombres .= "{ name: 'atributos', type: 'string'},";
	$columnasNombres .= "{ name: 'adeudo', type: 'string'},";
	$columnasNombres .= "{ name: 'pase', type: 'string'},";
	$columnasNombres .= "{ name: 'historial', type: 'string'},";
	$columnasNombres .= "{ name: 'eliminar', type: 'string'},";
	$columnasNombres .= "{ name: 'identificador', type: 'string'}";
	$columnasNombres .= "]";
	$data['columnasNombres'] = $columnasNombres;

	$columnasNombresGrid = "";
	$columnasNombresGrid .= "[";
	$columnasNombresGrid .= "{ text: 'Folio', datafield: 'identificador', editable: false, width: '6%', cellsalign: 'center', align: 'center' },";
	$columnasNombresGrid .= "{ text: 'Contrato', datafield: 'configContrato', editable: false, width: '16%', cellsalign: 'center', align: 'center' },";
	$columnasNombresGrid .= "{ text: 'Unidad Ejecutora', datafield: 'unidadEjecutora', editable: false, width: '15%', cellsalign: 'center', align: 'center' },";
	$columnasNombresGrid .= "{ text: 'Contribuyente', datafield: 'contribuyente', editable: false, width: '18%', cellsalign: 'center', align: 'center' },";
	//$columnasNombresGrid .= "{ text: 'Atributo 1', datafield: 'atributo1', editable: false, width: '14%', cellsalign: 'center', align: 'center' },";
	//$columnasNombresGrid .= "{ text: 'Atributo 2', datafield: 'atributo2', editable: false, width: '14%', cellsalign: 'center', align: 'center' },";
	$columnasNombresGrid .= $columnasNombresGridAtributos;
	$columnasNombresGrid .= "{ text: 'Fecha Inicial', datafield: 'fechaInicial', editable: false, width: '8%', cellsalign: 'center', align: 'center' },";
	$columnasNombresGrid .= "{ text: 'Estatus', datafield: 'estatus', editable: false, width: '10%', cellsalign: 'center', align: 'center' },";
	$columnasNombresGrid .= "{ text: 'Modificar', datafield: 'modificar', editable: false, width: '6%', cellsalign: 'center', align: 'center' },";
	$columnasNombresGrid .= "{ text: 'Detalle Objeto', datafield: 'objetoDetalle', editable: false, width: '6%', cellsalign: 'center', align: 'center' },";
	$columnasNombresGrid .= "{ text: 'Atributos', datafield: 'atributos', editable: false, width: '6%', cellsalign: 'center', align: 'center' },";
	$columnasNombresGrid .= "{ text: 'Generar Adeudos', datafield: 'adeudo', editable: false, width: '6%', cellsalign: 'center', align: 'center' },";
	$columnasNombresGrid .= "{ text: 'Pase de Cobro', datafield: 'pase', editable: false, width: '6%', cellsalign: 'center', align: 'center' },";
	$columnasNombresGrid .= "{ text: 'Historial', datafield: 'historial', editable: false, width: '6%', cellsalign: 'center', align: 'center' },";
	$columnasNombresGrid .= "{ text: 'Eliminar', datafield: 'eliminar', editable: false, width: '6%', cellsalign: 'center', align: 'center' }";
	$columnasNombresGrid .= "]";
	$data['columnasNombresGrid'] = $columnasNombresGrid;

	return $data;
}
