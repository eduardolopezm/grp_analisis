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
		'contribuyente' => ['col' => '', 'tipo' => 'string'],
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
	$sql = "SELECT 
	adminContratos.id_administracion_contratos as adminContratoID,
	adminContratos.id_contrato as contratoID,
	adminContratos.id_contribuyente as contribuyenteID,
	adminContratos.id_periodo as periodo,
	adminContratos.id_objeto_principal as objetoPrincipal,
	adminContratos.id_objeto_parcial as objetoParcial,
	adminContratos.nu_cantidad  as cantidad,
	adminContratos.mtn_importe as importe,
	adminContratos.nu_descuento as descuento,
	(adminContratos.mtn_total - (adminContratos.mtn_total * (IF(adminContratos.nu_descuento, adminContratos.nu_descuento, 0) / 100))) as total,
	adminContratos.dt_vencimineto as vencimiento,
	adminContratos.pase_cobro as cobro,
	adminContratos.folio_recibo as recibo,
	adminContratos.cajero as cajero,
	adminContratos.dt_fechadepago as fechaPago,
	adminContratos.estatus as status,
	adminContratos.dtm_fecha_efectiva as fechaEfectiva,
	tb_contratos_contribuyentes.reporte
	FROM tb_administracion_contratos AS adminContratos
	LEFT JOIN tb_descuentos on id_parcial = adminContratos.id_objeto_parcial
	LEFT JOIN tb_contratos on tb_contratos.id_contrato = adminContratos.id_contrato
	LEFT JOIN tb_contratos_contribuyentes on tb_contratos_contribuyentes.id_contratos = tb_contratos.id_confcontratos
	WHERE adminContratos.id_contrato = '".$_POST['contrato_id']."'";

	

	// datos adicionales de ordenamiento
	$sql .= " ORDER BY adminContratoID ASC";
	
	$data = ['success'=>false,'msg'=>$sql,'content'=>[]];

	$result = DB_query($sql, $db);
	

	// procesamiento de la información obtenida
	while ($rs = DB_fetch_array($result)) {
		$mes = 0;
		$me = 0;
		if($rs['periodo'] == 'Mes'){
			$mes = (int)date("m",strtotime($rs['fechaVigencia']));
			$me = round(12 - $mes + 1 );
			$cantidadPeriodo = $me / (int)$rs['periodicidad'];
		}
		// if(empty($rs['cobro'])){
		// 	$imprimir = '';
		//  }
		if(empty($rs['reporte'])){
			$imprimir = '';
		}else{
			if(empty($rs['recibo']))
				$imprimir = '';
			else
				$imprimir = '<a href="impresion_boleta_estacionamiento.php?idContrato='.$rs['contratoID'].'&folioRecibo='.$rs['recibo'].'&folioPase='.$rs['cobro'].'" target="_blank"><span class="glyphicon glyphicon-print"></span></a>';
		}
		
		$data['content'][] = [
			'checkbox' => '<input class="selectCheck" data-id="'.$rs['adminContratoID'].'" type="checkbox" >',			
			'imprimir' => $imprimir,	
			'clave'=>utf8_encode($rs['adminContratoID']),// 0
			'contrato'=>utf8_encode($rs['contratoID']),// 0
			'contribuyente'=>utf8_encode($rs['contribuyenteID']),// 0
			'periodo'=>utf8_encode($rs['periodo']),// 0
			'objetoPrincipal'=>utf8_encode($rs['objetoPrincipal']),// 0
			'objetoParcial'=>utf8_encode($rs['objetoParcial']),// 0
			'cantidad'=> utf8_encode($rs['cantidad']),// 0
			'importe'=>utf8_encode($rs['importe']),// 0
			'descuento'=> utf8_encode($rs['descuento']).'%',// 0
			'total'=>number_format($rs['total'],2),// 1
			'vencimiento'=>utf8_encode($rs['vencimiento']),// 1
			'cobro'=>utf8_encode($rs['cobro']),// 2
			'recibo'=>utf8_encode($rs['recibo']),// 2
			'cajero'=>utf8_encode($rs['cajero']),// 2
			'fechaEfectiva'=> date("d-m-Y", strtotime($rs['fechaEfectiva'])),
			'fechaPago'=>date("d-m-Y", strtotime($rs['fechaPago'])),
			'modificar'=>'<span class="modificar glyphicon glyphicon-edit"></span>',// 7
			'eliminar'=>'<span class="eliminar glyphicon glyphicon-trash"></span>',// 7
			'identificador'=>utf8_encode($rs['clave'])// 9
		];

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

	$ids = join("','",$info['selects_id']);


	$sqlSelect = "SELECT 
	adminContratos.id_administracion_contratos as adminContratoID,
	adminContratos.id_contrato as contratoID,
	adminContratos.id_contribuyente as contribuyenteID,
	adminContratos.id_periodo as periodo,
	adminContratos.id_objeto_principal as objetoPrincipalID,
	adminContratos.id_objeto_parcial as objetoParcialID,
	adminContratos.nu_cantidad  as cantidad,
	adminContratos.mtn_importe as importe,
	adminContratos.mtn_total as total,
	adminContratos.dt_vencimineto as vencimiento,
	adminContratos.pase_cobro as cobro,
	adminContratos.folio_recibo as recibo,
	adminContratos.cajero as cajero,
	adminContratos.dt_fechadepago as fechaPago,
	adminContratos.estatus as status,
	adminContratos.dtm_fecha_efectiva as ordate,
	debtorsmaster.address1 as 'calle',
	debtorsmaster.numExt as 'numExt',
	debtorsmaster.numInt as 'numInt',
	debtorsmaster.address2 as 'colonia',
	debtorsmaster.address3 as 'municipio',
	debtorsmaster.address4 as 'estado',
	debtorsmaster.address5 as 'cp',
	custbranch.taxid as rfc,
	custbranch.email as email,
	tags.tagref AS unidadNegocioID,
	ues.ue AS unidadEjecutoraID,
	(SELECT GROUP_CONCAT(tb_propiedades_atributos.ln_valor separator ', ') FROM tb_propiedades_atributos Where tb_propiedades_atributos.id_folio_contrato = adminContratos.id_contrato) as observaciones,
	IF(tb_descuentos.nu_porcentaje, tb_descuentos.nu_porcentaje, 0) / 100  as descuento
	FROM tb_administracion_contratos AS adminContratos
	JOIN tb_contratos AS contratos ON contratos.id_contrato = adminContratos.id_contrato
	JOIN `debtorsmaster` ON `debtorsmaster`.`debtorno` = adminContratos.id_contribuyente
	LEFT JOIN `custbranch` ON `custbranch`.`debtorno` = adminContratos.id_contribuyente
	LEFT JOIN tags on (tags.tagref = contratos.tagref)
	LEFT JOIN tb_cat_unidades_ejecutoras as ues on (ues.ue = contratos.ln_ue) 
	LEFT JOIN tb_descuentos on id_parcial = adminContratos.id_objeto_parcial
	WHERE adminContratos.id_administracion_contratos IN ('".$ids."')" ;

	$resultSelect = DB_query($sqlSelect, $db);

	// comprobación de existencia de la información
	if(DB_num_rows($resultSelect) == 0){
		$data['msg'] = 'No se encontraron los datos solicitados.';
		return $data;
	}
	// procesamiento de la información obtenida
	$cantidadPeriodo = 0;
	$total = 0;
	
	$row = DB_fetch_array($resultSelect);

	$documentnumber = GetNextTransNo(30, $db);
		
	$sql = "INSERT INTO `salesorders` (
				`orderno`, 
				`debtorno`, 
				`branchcode`, 
				`customerref`, 
				`buyername`, 
				`comments`, 
				`orddate`, 
				`ordertype`, 
				`shipvia`, 
				`deladd1`, 
				`deladd2`, 
				`deladd3`, 
				`deladd4`, 
				`deladd5`, 
				`deladd6`, 
				`contactphone`, 
				`contactemail`, 
				`deliverto`, 
				`deliverblind`, 
				`freightcost`, 
				`fromstkloc`, 
				`deliverydate`, 
				`quotedate`, 
				`confirmeddate`, 
				`printedpackingslip`, 
				`datepackingslipprinted`, 
				`quotation`, 
				`placa`, 
				`serie`, 
				`kilometraje`, 
				`salesman`, 
				`tagref`, 
				`taxtotal`, 
				`totaltaxret`, 
				`currcode`, 
				`paytermsindicator`, 
				`contract_type`, 
				`advance`, 
				`userregister`, 
				`typeorder`, 
				`refundpercentsale`, 
				`vehicleno`, 
				`idtarea`, 
				`contid`, 
				`codigobarras`, 
				`idprospect`, 
				`nopedido`, 
				`noentrada`, 
				`extratext`, 
				`noremision`, 
				`totalrefundpercentsale`, 
				`puestaenmarcha`, 
				`paymentname`, 
				`nocuenta`, 
				`deliverytext`, 
				`estatusprocesing`, 
				`serviceorder`, 
				`usetype`, 
				`statuscancel`, 
				`fromcr`, 
				`ordenprioridad`, 
				`discountcard`, 
				`payreference`, 
				`app_cotizador`,
				`ln_ue`)
		VALUES      
		( 
				".$documentnumber.",
				'".$row['contribuyenteID']."', 
				'".$row['contribuyenteID']."', 
				'".$row['rfc']."', 
				NULL, 
				'".$row['observaciones']."', 
				'".$row['ordate']."', 
				'L1', 
				1, 
				'".$row['calle']."', 
				'".$row['municipio']."', 
				'".$row['estado']."', 
				'".$row['cp']."', 
				'', 
				'".$row['colonia']."', 
				'0', 
				'".$row['email']."', 
				'0', 
				1, 
				0, 
				'".$row['objetoPrincipalID']."', 
				now(), 
				now(), 
				now(), 
				0, 
				'0000-00-00', 
				'1', 
				'', 
				'', 
				0, 
				'', 
				'".$row['unidadNegocioID']."',
				0, 
				0, 
				'MXN', 
				'1', 
				0, 
				0, 
				'".$_SESSION['UserID']."', 
				0, 
				0, 
				0, 
				0, 
				0, 
				NULL, 
				0, 
				'', 
				'', 
				'', 
				'', 
				0, 
				'', 
				'', 
				'No Identificado', 
				'', 
				0, 
				NULL, 
				0, 
				0, 
				NULL, 
				0, 
				'', 
				NULL, 
				0,
				'".$row['unidadEjecutoraID']."')
	";
		try {
			$data['sql'] = $info['contrato'];
			$result = DB_query($sql, $db);
			if($result == true){
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
		foreach ($info['selects_id'] as &$valor) {
			$sql = "UPDATE tb_administracion_contratos
			SET 
				pase_cobro = '".$documentnumber."'
			WHERE id_administracion_contratos='".$valor."';";

			try {
				$result = DB_query($sql, $db);
				if($result == true){
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
	$contt = 0;

	$resultSelect = DB_query($sqlSelect, $db);

	while ($rs = DB_fetch_array($resultSelect)) {

		$sql = "INSERT INTO `salesorderdetails` 
					(`orderlineno`, 
					`orderno`, 
					`stkcode`, 
					`fromstkloc`, 
					`qtyinvoiced`, 
					`unitprice`, 
					`quantity`, 
					`alto`, 
					`ancho`, 
					`calculatepricebysize`, 
					`largo`, 
					`quantitydispatched`, 
					`adevengar`, 
					`facturado`, 
					`devengado`, 
					`xfacturar`, 
					`afacturar`, 
					`xdevengar`, 
					`nummes`, 
					`refundpercent`, 
					`saletype`, 
					`estimate`, 
					`discountpercent`, 
					`discountpercent1`, 
					`discountpercent2`, 
					`actualdispatchdate`, 
					`completed`, 
					`narrative`, 
					`itemdue`, 
					`poline`, 
					`warranty`, 
					`salestype`, 
					`servicestatus`, 
					`pocost`, 
					`idtarea`, 
					`cashdiscount`, 
					`showdescrip`, 
					`readonlyvalues`, 
					`modifiedpriceanddiscount`, 
					`totalrefundpercent`, 
					`qtylost`, 
					`datelost`, 
					`woline`, 
					`stkmovid`, 
					`userlost`, 
					`localidad`, 
					`stockidkit`, 
					`anticipo`, 
					`numpredial`,
					`id_administracion_contratos`) 
			VALUES      (
					".$contt.", 
					".$documentnumber.", 
					'".$rs['objetoParcialID']."',
					'".$rs['objetoPrincipalID']."',
					0,
					".$rs['importe'].",
					".$rs['cantidad'].",
					0, 
					0, 
					0, 
					0, 
					".$rs['cantidad'].",
					0, 
					0, 
					0, 
					0, 
					0, 
					0, 
					0, 
					0, 
					0, 
					0, 
					".$rs['descuento'].", 
					0, 
					0, 
					'0000-00-00 00:00:00', 
					0, 
					'', 
					now(), 
					'', 
					0, 
					'L1', 
					0, 
					NULL, 
					0, 
					0, 
					'1', 
					0, 
					0, 
					0, 
					NULL, 
					NULL, 
					NULL, 
					0, 
					NULL, 
					NULL, 
					NULL, 
					NULL, 
					'',
					'".$rs['adminContratoID']."')";

		$contt++;
		try {
			$result = DB_query($sql, $db);
			if($result == true){
				$data['success'] = true;
				// https://grp.onaxis.mx/ap_grp_demo/PDFCotizacionTemplateV2.php?tipodocto=2&TransNo=144&Tagref=10&legalid=2
				// $data['msg'] = '<p>Se ha generado el pase de cobro: '.$documentnumber.' exitosamente.</p><br>';
				$data['msg'] = '<div style ="text-align: center;"><p>Se creo Pase de Cobro no: '.$documentnumber.', correctamente. </p><p><i class="fa fa-print text-success" aria-hidden="true"></i><a target="_blank" href="https://grp.onaxis.mx/ap_grp_demo/PDFCotizacionTemplateV2.php?tipodocto=2&amp;TransNo='.$documentnumber.'&amp;Tagref=10&amp;legalid=2"><!-- react-text: 16 --> <!-- /react-text --><!-- react-text: 17 -->Imprimir Pase de Cobro No. '.$documentnumber.'<!-- /react-text --></a></p></div>';

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
 * Función para el guardado de la información de los ítems generados
 * @param	[DBInstance]	$db		Instancia de la base de datos
 * @return	[Array]			$data	Arreglo con la respuesta verdadera o falsa, según lo obtenido
 */
function store($db){
	// declaracion de variables de la función
	$data = ['success'=>false,'msg'=>'Ocurrió un incidente inesperado. Favor de contactar con el administrador.'];
	$info = $_POST;
	$info['userID'] = $_SESSION['UserID'];
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

	
	DB_Txn_Begin($db);
	try {
		// obtención del query para la inserción
		$sqlIn = obtenInsercion($info);
		$resultIn = DB_query($sqlIn, $db);

		$sql = "SELECT MAX(id_contrato) as lastID FROM ".LNTBLCAT;

		$resultIn = DB_query($sql, $db);
		$rs = DB_fetch_array($resultIn);
		$contratoID = $rs['lastID'];

		// $contratoID = DB_Last_Insert_ID($db);
		$data['sql'] = $info['contrato'];

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

		// foreach($data['msg'] = $info['selectObjetosParciales'] as $selectOP){

		// 	$sql = "INSERT INTO `tb_contratos_objetos_parciales` (`id_contrato`, `id_objetos`, `dtm_fecha_efectiva`)
		// 	VALUES
		// 		(".$contratoID.", ".$selectOP.", now())";

		// 	$resultIn = DB_query($sql, $db);


		// }
	
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
		// comprobación de éxito de la información
		if($resultUpdate==true){
			$data['msg'] = "El Registro ".$identificador." ha sido actualizado";
			DB_Txn_Begin($db);
			$data['success'] = true;
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
	$info = $_POST;

	$registrosEncontrados = "";
	$data = ['success'=>false,'msg'=>'Ocurrió un incidente al momento de eliminar la información.'];
	# comprobación de identificador
	if(empty($info['identificador'])){
		$data['msg'] = 'Es necesario indicar el identificador del almacén';
		return $data;
	}

	// $sql = "DELETE FROM ".LNTBLCAT." WHERE ".IDENTIFICADOR." =  '" . $info['identificador'] . "'";

	$sql = " UPDATE ".LNTBLCAT." SET ".ACTIVECOL." = 0 WHERE `".IDENTIFICADOR."` = '".$info['identificador']."' ";

	$result = DB_query( $sql, $db);
	$data['content'] = show($db)['content'];


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
function obtenInsercion($info)
{
	$campos = ''; $datos = ''; $flag = 0; $strData = ['string','select'];
	foreach ($info as $input => $valor) {
		if(!array_key_exists($input, DATA)){ continue; }
		$data = DATA[$input];
		if($data['col']== ''){continue;};
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

function obtenUpdate($info, $value)
{
	$campos = ''; $flag = 0; $identificador = $info['identificador']; $iterador = DATA;
	unset($info['identificador']);
	unset($iterador['identificador']);
	foreach ($info as $input => $valor) {
		if(!array_key_exists($input, $iterador)){ continue; }
		$data = DATA[$input];
		if($data['col']== ''){continue;};
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
