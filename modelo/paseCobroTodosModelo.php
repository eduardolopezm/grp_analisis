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
$funcion = 2522;

/* INCLUSIÓN DE ARCHIVOS NECESARIOS */
include($PathPrefix . 'config.php');
include($PathPrefix . 'includes/SecurityUrl.php');
include($PathPrefix . 'includes/ConnectDB.inc');
include($PathPrefix . 'includes/SecurityFunctions.inc');
include($PathPrefix . 'includes/SQL_CommonFunctions.inc');


/*********************************************** CONSTANTES ***********************************************/
define('DECPLACE', !empty($_SESSION['DecimalPlaces'])? $_SESSION['DecimalPlaces'] : 2);
define('LNTBLCAT', 'prices');
define('IDENTIFICADOR', 'stockid');
define('ACTIVECOL', 'activo');
define('DATA', [
		'selectObjetoPrincipal' => ['col' => 'id_op', 'tipo' => 'string'],
		'selectObjetoParcial' => ['col' => 'stockid', 'tipo' => 'string'],
		'typeabbrev' => ['col' => 'typeabbrev', 'tipo' => 'string'],
		'anio' => ['col' => 'anio', 'tipo' => 'string'],
		'type' => ['col' => 'tipo', 'tipo' => 'string'],
		'importe' => ['col' => 'nu_price', 'tipo' => 'string'],
		'rangoInicial' => ['col' => 'nu_rango_inicial', 'tipo' => 'string'],
		'rangoFinal' => ['col' => 'nu_rango_final', 'tipo' => 'string'],
		'isRango' => ['col' => 'isRango', 'tipo' => 'string'],
		'currabrev' => ['col' => 'currabrev', 'tipo' => 'string'],
		'identificador'=>['col'=>'stockid','tipo'=>'string']
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
	contratos.id_contrato as clave,
	CONCAT(tags.tagref , ' - ' , tags.tagdescription ) AS unidadNegocio,
	CONCAT(ues.ue , ' - ' , ues.desc_ue ) AS unidadEjecutora,    
	CONCAT(configContrato.id_contratos, ' - ', configContrato.id_loccode,' - ', locations.locationname) AS configContrato,
	configContrato.id_contratos AS idconfContrato,
	debtorsmaster.name as contribuyente,
	(SELECT DISTINCT pase_cobro FROM tb_administracion_contratos Where tb_administracion_contratos.id_contrato = contratos.id_contrato) as paseCobro,
	contratos.dtm_fecha_inicio as fechaInicio,
	contratos.enum_status as estatus,
	configContrato.id_loccode as objPrincipal,
	(SELECT SUM(amt_total) FROM tb_contratos_objetos_parciales where tb_contratos_objetos_parciales.id_contrato =  contratos.id_contrato) as importe,
	(SELECT tb_propiedades_atributos.ln_valor FROM tb_propiedades_atributos JOIN tb_cat_atributos_contrato on tb_cat_atributos_contrato.id_atributos = tb_propiedades_atributos.id_etiqueta_atributo Where tb_propiedades_atributos.id_folio_contrato = contratos.id_contrato AND tb_cat_atributos_contrato.ln_etiqueta = 'PLACA'  LIMIT 1 ) as placa,
	(SELECT tb_propiedades_atributos.ln_valor FROM tb_propiedades_atributos JOIN tb_cat_atributos_contrato on tb_cat_atributos_contrato.id_atributos = tb_propiedades_atributos.id_etiqueta_atributo Where tb_propiedades_atributos.id_folio_contrato = contratos.id_contrato AND tb_cat_atributos_contrato.ln_etiqueta = 'FOLIO DE BOLETA'  LIMIT 1 ) as folio
	FROM tb_contratos AS contratos 
	JOIN tags on (tags.tagref = contratos.tagref)    
	JOIN tb_cat_unidades_ejecutoras as ues on (ues.ue = contratos.ln_ue) 
	JOIN tb_contratos_contribuyentes as configContrato on (configContrato.id_contratos = contratos.id_confcontratos) 
	JOIN locations on (configContrato.id_loccode = locations.loccode)  
	JOIN debtorsmaster on (debtorsmaster.debtorno = contratos.id_debtorno)
	AND contratos.id_confcontratos = '7'
	WHERE contratos.ind_activo = '1'";


	// datos adicionales de filtrado

	if(!empty($info['txtFechaInicial']) && !empty($info['txtFechaFinal'])){
		$dateini=date("Y-m-d", strtotime($info['txtFechaInicial']));
		$datefin=date("Y-m-d", strtotime($info['txtFechaFinal']));
		$sql .= " AND contratos.dtm_fecha_inicio BETWEEN '" .  $dateini . " 00:00:00' AND '" .  $datefin . " 23:59:59'";
	}

	if(!empty($info['txtPlaca'])){
		$sql .= " AND (SELECT tb_propiedades_atributos.ln_valor FROM tb_propiedades_atributos JOIN tb_cat_atributos_contrato on tb_cat_atributos_contrato.id_atributos = tb_propiedades_atributos.id_etiqueta_atributo Where tb_propiedades_atributos.id_folio_contrato = contratos.id_contrato AND tb_cat_atributos_contrato.ln_etiqueta = 'PLACA'  LIMIT 1) = '".$info['txtPlaca']."'";
	}
	
	// datos adicionales de filtrado

	// datos adicionales de ordenamiento
	$sql .= " ORDER BY CAST(contratos.id_contrato AS SIGNED) DESC";

	$data['sqlMy'] = $sql;

	// if ($_SESSION ['UserID'] == "desarrollo") {
		// echo '<pre>'.$sql;exit();
	// }
	$result = DB_query($sql, $db);

	// procesamiento de la información obtenida
	while ($rs = DB_fetch_array($result)) {
		//calcular recargo
		$earlier = new DateTime($rs['fechaInicio']);
		$later = new DateTime(date('Y-m-d'));

		$days = $later->diff($earlier)->format("%a");

		$sql = "SELECT id_parcial, nu_porcentaje as porcentaje FROM tb_recargos WHERE loccode = '".$rs['objPrincipal']."'";
		try {
			$result2 = DB_query($sql, $db);
		} catch (Exception $e) {
			// captura del error
			$data['msg'] .= '<br>'.$e->getMessage();
			DB_Txn_Rollback($db);
		}
		$totalPagar = $rs['importe'];
		$recargos = 0;
		if(DB_num_rows($result2) > 0){
			$rs3 = DB_fetch_array($result2);
			$objParcialRecargo = $rs3['id_parcial'];
			if((int)$days > 30){
				$mes = (int)$days/30;
				$mes = round($mes, 0, PHP_ROUND_HALF_DOWN);
				$data['mesRecargo'] = $mes;
				$data['totalPagar'] = $totalPagar;
				$data['recargos'] = $totalPagar * ($rs3['porcentaje']/100)*$mes;
				$recargos = $totalPagar * ($rs3['porcentaje']/100)*$mes;
			}
		}

		//fin
		$data['content'][] = [
			'selected'=> false,// 0
			'clave'=>utf8_encode($rs['clave']),// 0
			'unidadEjecutora'=>utf8_encode($rs['unidadEjecutora']),// 0
			'fechaInicio'=>utf8_encode($rs['fechaInicio']),// 1
			'hora'=>utf8_encode($rs['hora']),// 1
			'placa'=>utf8_encode($rs['placa']),// 2
			'folio'=>utf8_encode($rs['folio']),// 3
			'garantia'=>utf8_encode($rs['garantia']),// 5
			'importe'=>utf8_encode($rs['importe']),// 5
			'recargos'=>number_format($recargos,2),// 1
			'estatus'=>utf8_encode($rs['estatus']),// 5
			'anio'=>utf8_encode($rs['anio']),
			'receptor'=>utf8_encode($rs['receptor']),// 5
			'infractor'=>utf8_encode($rs['infractor']),// 2
			'paseCobro'=>utf8_encode($rs['paseCobro'])!= '' ? '<a target="_blank" href="PDFCotizacionTemplateV2.php?TransNo='.utf8_encode($rs['paseCobro']).'"><span class="imprimir glyphicon glyphicon-print"></span></a>': '',// 7
			'modificar'=>'<span class="modificar glyphicon glyphicon-edit"></span>',// 7
			'eliminar'=>'<span class="eliminar glyphicon glyphicon-trash"></span>',// 8
			'identificador'=>utf8_encode($rs['clave'])// 9
		];

	}
	$data['success'] = true;
	// retorno de la información
	return $data;
}

function posPase($db){
	$data = ['success'=>false];

	$permisoGenerarPaser = Havepermission($_SESSION ['UserID'], 1968, $db); // Poder editar comentario
	$info = $_POST;

	// foreach ($info['placa'] as $placa) {
	// 	if($placa != 'S/P'){
	// 		$sql = "SELECT attr.id_folio_contrato AS idContrato,
	// 		adminCont.folio_recibo
	// 		FROM tb_propiedades_atributos attr 
	// 		JOIN tb_administracion_contratos  adminCont ON adminCont.id_contrato = attr.id_folio_contrato AND adminCont.folio_recibo = ''
	// 		WHERE id_etiqueta_atributo = '23'
	// 		AND attr.ln_valor = '".$placa."'";
	// 		try {
	// 			$result = DB_query($sql, $db);
	// 			if($result == true){
	// 				if(DB_num_rows($result) > 0){
	// 					$data['msg'] = 'No se puede generar el pase de cobro, existe adeudo de parquímetros pendientes. ';
	// 					$data['success'] = false;
	// 					return $data;
	// 				}
	// 				DB_Txn_Commit($db);
	// 			}else{
	// 				DB_Txn_Rollback($db);
	// 			}
	// 		} catch (Exception $e) {
	// 			// captura del error
	// 			$data['msg'] .= '<br>'.$e->getMessage();
	// 			DB_Txn_Rollback($db);
	// 		}
	// 	}
	// }

	if ($permisoGenerarPaser != 1){
		$data['msg'] = 'No tiene permisos para generar pase de cobro';
		$data['success'] = false;
		return $data;
	}

	foreach ($info['selects_id'] as $selectContratoID) {
		$sql ="SELECT pase_cobro FROM tb_administracion_contratos Where tb_administracion_contratos.id_contrato = '".$selectContratoID."'";
		$resultSelect = DB_query($sql, $db);

		$row = DB_fetch_array($resultSelect);

		if($row['pase_cobro'] != ''){
			$data['msg'] = 'No se puede generar pase de cobro, ya cuenta con un pase generado previamente';
			$data['success'] = false;
			return $data;

		}
	}


	$objParcialRecargo = 'TRAN_0206';
	$administracionID;
	$totalPagar = 0;

	$selectContrato = $info['selects_id'][0];
	$data['slects'] = $selectContratoID;
	$data['selects_id'] = $info['selects_id'];

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
		tb_descuentos.nu_porcentaje AS nuDescuento,
		tb_descuentos.tipo_descuento AS tipoDescuento,
		tb_descuentos.dtm_inicio AS fechaInicioDescuento,
		tb_descuentos.dtm_fin AS fechaFinDescuento,
		contratos.dtm_fecha_inicio AS fechaInicio
		FROM tb_administracion_contratos AS adminContratos
		JOIN tb_contratos AS contratos ON contratos.id_contrato = adminContratos.id_contrato
		JOIN `debtorsmaster` ON `debtorsmaster`.`debtorno` = adminContratos.id_contribuyente
		LEFT JOIN `custbranch` ON `custbranch`.`debtorno` = adminContratos.id_contribuyente
		LEFT JOIN tags on (tags.tagref = contratos.tagref)
		LEFT JOIN tb_cat_unidades_ejecutoras as ues on (ues.ue = contratos.ln_ue) 
		LEFT JOIN tb_descuentos on id_parcial = adminContratos.id_objeto_parcial AND  tb_descuentos.nu_estatus = '1'
		WHERE adminContratos.id_contrato = '".$selectContrato."'";

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
					`ln_ue`,
					`ln_tagref_pase`,
					`ln_ue_pase`)
			VALUES      
			( 
					".$documentnumber.",
					'".$row['contribuyenteID']."', 
					'".$row['contribuyenteID']."', 
					'".$row['rfc']."', 
					NULL,
					'', 
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
					'01', 
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
					'".$row['unidadEjecutoraID']."',
					'".$row['unidadNegocioID']."',
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
			foreach ($info['selects_id'] as $valor) {
				$sql = "UPDATE tb_administracion_contratos
				SET 
					pase_cobro = '".$documentnumber."',
					dt_vencimineto = '".$row['fechaVencimiento']."',
					nu_descuento = '".$row['descuento']."'
				WHERE id_contrato='".$valor."';";

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
		$contt2 = 0;
		$comments = '';

		foreach ($info['selects_id'] as $selectContratoID) {
			$placa ="";
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
			tb_descuentos.nu_porcentaje AS nuDescuento,
			tb_descuentos.tipo_descuento AS tipoDescuento,
			tb_descuentos.dtm_inicio AS fechaInicioDescuento,
			tb_descuentos.dtm_fin AS fechaFinDescuento,
			contratos.dtm_fecha_inicio AS fechaInicio,
			(SELECT tb_propiedades_atributos.ln_valor FROM tb_propiedades_atributos WHERE tb_propiedades_atributos.id_folio_contrato = adminContratos.id_contrato and id_etiqueta_atributo = 23) AS placa,
			(SELECT tb_propiedades_atributos.ln_valor FROM tb_propiedades_atributos WHERE tb_propiedades_atributos.id_folio_contrato = adminContratos.id_contrato and id_etiqueta_atributo = 24) AS folioBoleta,
			(SELECT GROUP_CONCAT(' ',tb_cat_atributos_contrato.ln_etiqueta, ': ',tb_propiedades_atributos.ln_valor) FROM tb_propiedades_atributos JOIN  tb_cat_atributos_contrato on tb_cat_atributos_contrato.id_atributos = tb_propiedades_atributos.id_etiqueta_atributo Where tb_propiedades_atributos.id_folio_contrato = adminContratos.id_contrato) as observaciones
			FROM tb_administracion_contratos AS adminContratos
			JOIN tb_contratos AS contratos ON contratos.id_contrato = adminContratos.id_contrato
			JOIN `debtorsmaster` ON `debtorsmaster`.`debtorno` = adminContratos.id_contribuyente
			LEFT JOIN `custbranch` ON `custbranch`.`debtorno` = adminContratos.id_contribuyente
			LEFT JOIN tags on (tags.tagref = contratos.tagref)
			LEFT JOIN tb_cat_unidades_ejecutoras as ues on (ues.ue = contratos.ln_ue) 
			LEFT JOIN tb_descuentos on id_parcial = adminContratos.id_objeto_parcial AND  tb_descuentos.nu_estatus = '1'
			WHERE adminContratos.id_contrato = '".$selectContratoID."'";

		$resultSelect = DB_query($sqlSelect, $db);


		while ($rs = DB_fetch_array($resultSelect)) {
			$placa = $rs['placa'];
			$sql = "SELECT DISTINCT
			contrato.dtm_fecha_inicio as fechaInicio,
			tb_descuentos.num_dias as numDias
			FROM tb_administracion_contratos AS adminContratos
			LEFT JOIN tb_descuentos on id_parcial = adminContratos.id_objeto_parcial AND tb_descuentos.nu_estatus = '1'
			JOIN  tb_contratos as contrato on contrato.id_contrato =  '".$selectContratoID."'
			WHERE adminContratos.id_contrato = '".$selectContratoID."'
			AND adminContratos.id_objeto_parcial = '".$rs['objetoParcialID']."'";
			//$comments .= "FECHA: ".date("d-m-Y", strtotime($rs['fechaInicio']))." FOLIO: ".$rs['contratoID']." FOLIO DE BOLETA: ".$rs['folioBoleta']." ";
			$result = DB_query($sql, $db);
			$rows2 = DB_fetch_array($result);
			
			$data['rows2'] = $rows2;

			$sql = "SELECT Fecha, NombreDia, Feriado
					FROM DWH_Tiempo 
					WHERE Fecha >='".$rows2['fechaInicio']."' LIMIT 30";

		$result3 = DB_query($sql, $db);

		$contt = 0;
		$dias = 0;
		while ($rs4 = DB_fetch_array($result3)) {
			if($contt < $rows2['numDias']){
				if($rs4['Feriado'] == '0' && $rs4['NombreDia'] != 'Domingo'){
					$contt++;
				}
				$dias++;
			} 
		}
			$data['dias'] = $dias;
			$data['fechaInicio'] = $rows2['fechaInicio'];
			$data['numDias'] = $rows2['numDias'];
			
			$descuento = date('Y-m-d') <= date("Y-m-d",strtotime($rows2['fechaInicio']."+ ".$dias." days")) ? $rs['nuDescuento'] : 0;

			if ((date('Y-m-d') >= $rs['fechaInicioDescuento']) && (date('Y-m-d') <= $rs['fechaFinDescuento'])) {
				$descuento = $rs['nuDescuento'];
			}

			$data ['descuento'][] = $descuento;

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
						".$contt2.", 
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
						".($descuento / 100).", 
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
			$administracionID = $rs['adminContratoID'];
			$totalPagar += ($rs['importe']* $rs['cantidad']) - (($rs['importe'] * $rs['cantidad']) * ($descuento / 100));
			$contt2++;
			try {
				$result = DB_query($sql, $db);
				if($result == true){
					$data['success'] = true;
					// https://grp.onaxis.mx/ap_grp_demo/PDFCotizacionTemplateV2.php?tipodocto=2&TransNo=144&Tagref=10&legalid=2
					// $data['msg'] = '<p>Se ha generado el pase de cobro: '.$documentnumber.' exitosamente.</p><br>';

				}
			} catch (Exception $e) {
				// captura del error
				$data['msg'] .= '<br>'.$e->getMessage();
				DB_Txn_Rollback($db);
			}

		}
		$earlier = new DateTime($row['fechaInicio']);
		$later = new DateTime(date('Y-m-d'));

		$days = $later->diff($earlier)->format("%a");

		$sql = "SELECT id_parcial, nu_porcentaje as porcentaje FROM tb_recargos WHERE loccode = '".$row['objetoPrincipalID']."'";
		try {
			$result = DB_query($sql, $db);
		} catch (Exception $e) {
			// captura del error
			$data['msg'] .= '<br>'.$e->getMessage();
			DB_Txn_Rollback($db);
		}
		if(DB_num_rows($result) > 0){
			$rs3 = DB_fetch_array($result);
			$objParcialRecargo = $rs3['id_parcial'];
			if((int)$days > 30){
				$mes = (int)$days/30;
				$mes = round($mes, 0, PHP_ROUND_HALF_DOWN);
				$data['mesRecargo'] = $mes;
				$data['totalPagar'] = $totalPagar;
				$data['recargos'] = $totalPagar * ($rs3['porcentaje']/100)*$mes;
				$recargos = $totalPagar * ($rs3['porcentaje']/100)*$mes;

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
					".$contt2.", 
					".$documentnumber.", 
					'".$objParcialRecargo."',
					'TRAN',
					0,
					".$recargos.",
					1,
					0, 
					0, 
					0, 
					0, 
					1,
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
					0, 
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
					'".$administracionID."')";

		try {
			$result = DB_query($sql, $db);
			if($result == true){
				$data['success'] = true;
				// https://grp.onaxis.mx/ap_grp_demo/PDFCotizacionTemplateV2.php?tipodocto=2&TransNo=144&Tagref=10&legalid=2
				// $data['msg'] = '<p>Se ha generado el pase de cobro: '.$documentnumber.' exitosamente.</p><br>';

			}
		} catch (Exception $e) {
			// captura del error
			$data['msg'] .= '<br>'.$e->getMessage();
			DB_Txn_Rollback($db);
		}

			}
		}


		// $data['days'] = $days;
		// $dateString = date('Y-m-d');
		// $data['fechaInicioNew'] = $row['fechaInicio'];
		// $data['fechaLOY'] = $dateString;
		// $data['fechaFIN'] = date("Y-m-t", strtotime($row['fechaInicio']));
		// $data ['validation'] = date("Y-m-t", strtotime($row['fechaInicio'])) < $dateString ? 'mayor':'menor';
		
		// if ( $dateString > date("Y-m-t", strtotime($row['fechaInicio']))){
		// 	$sql = "INSERT INTO `salesorderdetails` 
		// 				(`orderlineno`, 
		// 				`orderno`, 
		// 				`stkcode`, 
		// 				`fromstkloc`, 
		// 				`qtyinvoiced`, 
		// 				`unitprice`, 
		// 				`quantity`, 
		// 				`alto`, 
		// 				`ancho`, 
		// 				`calculatepricebysize`, 
		// 				`largo`, 
		// 				`quantitydispatched`, 
		// 				`adevengar`, 
		// 				`facturado`, 
		// 				`devengado`, 
		// 				`xfacturar`, 
		// 				`afacturar`, 
		// 				`xdevengar`, 
		// 				`nummes`, 
		// 				`refundpercent`, 
		// 				`saletype`, 
		// 				`estimate`, 
		// 				`discountpercent`, 
		// 				`discountpercent1`, 
		// 				`discountpercent2`, 
		// 				`actualdispatchdate`, 
		// 				`completed`, 
		// 				`narrative`, 
		// 				`itemdue`, 
		// 				`poline`, 
		// 				`warranty`, 
		// 				`salestype`, 
		// 				`servicestatus`, 
		// 				`pocost`, 
		// 				`idtarea`, 
		// 				`cashdiscount`, 
		// 				`showdescrip`, 
		// 				`readonlyvalues`, 
		// 				`modifiedpriceanddiscount`, 
		// 				`totalrefundpercent`, 
		// 				`qtylost`, 
		// 				`datelost`, 
		// 				`woline`, 
		// 				`stkmovid`, 
		// 				`userlost`, 
		// 				`localidad`, 
		// 				`stockidkit`, 
		// 				`anticipo`, 
		// 				`numpredial`,
		// 				`id_administracion_contratos`) 
		// 		VALUES      (
		// 				".$contt2.", 
		// 				".$documentnumber.", 
		// 				'".$objParcialRecargo."',
		// 				'TRAN',
		// 				0,
		// 				100,
		// 				1,
		// 				0, 
		// 				0, 
		// 				0, 
		// 				0, 
		// 				1,
		// 				0, 
		// 				0, 
		// 				0, 
		// 				0, 
		// 				0, 
		// 				0, 
		// 				0, 
		// 				0, 
		// 				0, 
		// 				0, 
		// 				0, 
		// 				0, 
		// 				0, 
		// 				'0000-00-00 00:00:00', 
		// 				0, 
		// 				'', 
		// 				now(), 
		// 				'', 
		// 				0, 
		// 				'L1', 
		// 				0, 
		// 				NULL, 
		// 				0, 
		// 				0, 
		// 				'1', 
		// 				0, 
		// 				0, 
		// 				0, 
		// 				NULL, 
		// 				NULL, 
		// 				NULL, 
		// 				0, 
		// 				NULL, 
		// 				NULL, 
		// 				NULL, 
		// 				NULL, 
		// 				'',
		// 				'".$administracionID."')";

		// 	try {
		// 		$result = DB_query($sql, $db);
		// 		if($result == true){
		// 			$data['success'] = true;
		// 			// https://grp.onaxis.mx/ap_grp_demo/PDFCotizacionTemplateV2.php?tipodocto=2&TransNo=144&Tagref=10&legalid=2
		// 			// $data['msg'] = '<p>Se ha generado el pase de cobro: '.$documentnumber.' exitosamente.</p><br>';

		// 		}
		// 	} catch (Exception $e) {
		// 		// captura del error
		// 		$data['msg'] .= '<br>'.$e->getMessage();
		// 		DB_Txn_Rollback($db);
		// 	}
	}

		// }
		$sql = "INSERT INTO `salesdate` (
			`orderno`,
			`fecha_solicitud`,
			`usersolicitud`,
			`fecha_solicitudmod`,
			`usersolicitudmod`,
			`fecha_cotizacion`,
			`usercotizacion`,
			`fecha_cotizacionmod`,
			`usercotizacionmod`,
			`fecha_abierto`,
			`userabierto`,
			`fecha_abiertomod`,
			`userabiertomod`,
			`fecha_cerrado`,
			`usercerrado`,
			`fecha_cerradomod`,
			`usercerradomod`,
			`fecha_cancelado`,
			`usercancelado`,
			`fecha_canceladomod`,
			`usercanceladomod`,
			`fecha_facturado`,
			`userfacturado`,
			`fecha_facturadomod`,
			`fecha_remisionado`,
			`userremisionado`,
			`fecha_remisionadomod`,
			`autoincrement`)
		VALUES
			(".$documentnumber.", now(), '".$_SESSION['UserID']."', NULL, NULL, now(), '".$_SESSION['UserID']."', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);";

		try {
			$result = DB_query($sql, $db);
			if($result == true){
				$data['success'] = true;

			}
		} catch (Exception $e) {
			// captura del error
			$data['msg'] .= '<br>'.$e->getMessage();
			DB_Txn_Rollback($db);
		}
		$sql = "UPDATE salesorders SET 
		`comments` = '".'PLACA: ' .$placa.' '.$comments."'
		WHERE orderno = '".$documentnumber."'";

	try {
		$result = DB_query($sql, $db);
		if($result == true){
			$data['success'] = true;
			$data['msg'] = '<p style="text-align: center;">Se ha actualizado el registro: '.$contratoID.' exitosamente.</p>';
			DB_Txn_Commit($db);
		}else{
			DB_Txn_Rollback($db);
		}
	}catch (Exception $e) {
		DB_Txn_Rollback($db);
	}
		if($data['success'] == true){

			$data['msg'] .= '<div style ="text-align: center;"><p>Se creo Pase de Cobro no: '.$documentnumber.', correctamente. </p><p><i class="fa fa-print text-success" aria-hidden="true"></i><a target="_blank" href="PDFCotizacionTemplateV2.php?tipodocto=2&amp;TransNo='.$documentnumber.'&amp;Tagref=10&amp;legalid=2"><!-- react-text: 16 --> <!-- /react-text --><!-- react-text: 17 -->Imprimir Pase de Cobro No. '.$documentnumber.'<!-- /react-text --></a></p></div><br>';

		}

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
	$sql = "SELECT 
		contratos.id_contrato as clave,
		CONCAT(tags.tagref , ' - ' , tags.tagdescription ) AS unidadNegocio,
		tags.tagref AS unidadNegocioID,
		CONCAT(ues.ue , ' - ' , ues.desc_ue ) AS unidadEjecutora,
		ues.ue AS unidadEjecutoraID,
		CONCAT(configContrato.id_contratos, ' - ', configContrato.id_loccode,' - ', locations.locationname) AS configContrato,
		configContrato.id_contratos AS idconfContrato,  
		CONCAT(debtorsmaster.debtorno,' - ',debtorsmaster.name) as contribuyente,
		debtorsmaster.debtorno as contribuyenteID,
		IF((SELECT COUNT(*) FROM tb_administracion_contratos Where tb_administracion_contratos.id_contrato = contratos.id_contrato AND tb_administracion_contratos.folio_recibo = '') = 0, 'Pagado','Pendiente') as estatus,
		contratos.dtm_fecha_inicio as fechaInicio,
		(SELECT SUM(amt_total) FROM tb_contratos_objetos_parciales where tb_contratos_objetos_parciales.id_contrato =  contratos.id_contrato) as importe,
		(SELECT tb_propiedades_atributos.ln_valor FROM tb_propiedades_atributos Where tb_propiedades_atributos.id_folio_contrato = contratos.id_contrato
	ORDER BY id_propiedades_atributos ASC LIMIT 1 OFFSET 1) as placa,
		(SELECT tb_propiedades_atributos.ln_valor FROM tb_propiedades_atributos Where tb_propiedades_atributos.id_folio_contrato = contratos.id_contrato
	ORDER BY id_propiedades_atributos ASC LIMIT 1 OFFSET 4) as garantia,
	(SELECT tb_propiedades_atributos.ln_valor FROM tb_propiedades_atributos Where tb_propiedades_atributos.id_folio_contrato = contratos.id_contrato
	ORDER BY id_propiedades_atributos ASC LIMIT 1 OFFSET 2) as folio,
	(SELECT tb_propiedades_atributos.ln_valor FROM tb_propiedades_atributos Where tb_propiedades_atributos.id_folio_contrato = contratos.id_contrato
	ORDER BY id_propiedades_atributos ASC LIMIT 1 OFFSET 0) as hora,
	(SELECT tb_propiedades_atributos.ln_valor FROM tb_propiedades_atributos Where tb_propiedades_atributos.id_folio_contrato = contratos.id_contrato
	ORDER BY id_propiedades_atributos ASC LIMIT 1 OFFSET 3) as receptor,
	(SELECT tb_propiedades_atributos.ln_valor FROM tb_propiedades_atributos Where tb_propiedades_atributos.id_folio_contrato = contratos.id_contrato
	ORDER BY id_propiedades_atributos ASC LIMIT 1 OFFSET 5) as infractor,
	(SELECT tb_propiedades_atributos.ln_valor FROM tb_propiedades_atributos Where tb_propiedades_atributos.id_folio_contrato = contratos.id_contrato
	ORDER BY id_propiedades_atributos ASC LIMIT 1 OFFSET 6) as descripcion
		FROM tb_contratos AS contratos JOIN tags on (tags.tagref = contratos.tagref)    
		JOIN tb_cat_unidades_ejecutoras as ues on (ues.ue = contratos.ln_ue) 
		JOIN tb_contratos_contribuyentes as configContrato on (configContrato.id_contratos = contratos.id_confcontratos) 
		JOIN locations on (configContrato.id_loccode = locations.loccode)  
		JOIN debtorsmaster on (debtorsmaster.debtorno = contratos.id_debtorno)
		WHERE contratos.ind_activo = '1'
		AND contratos.id_confcontratos = '4'
		AND contratos.id_contrato = '".$info['identificador']."'";
	$result = DB_query($sql, $db);

	// comprobación de existencia de la información
	if(DB_num_rows($result) == 0){
		$data['msg'] = 'No se encontraron los datos solicitados.';
		return $data;
	}
	$rs = DB_fetch_array($result);
	
		
	$data['content'] = [
		'selected'=> false,// 0
		'clave'=>utf8_encode($rs['clave']),// 0
		'contribuyente'=>utf8_encode($rs['contribuyente']),// 0
		'contribuyenteID'=>utf8_encode($rs['contribuyenteID']),// 0
		'unidadEjecutora'=>utf8_encode($rs['unidadEjecutora']),// 0
		'txtFechaInicial'=>utf8_encode($rs['fechaInicio']),// 1
		'selectUnidadNegocio'=>utf8_encode($rs['unidadNegocioID']),// 1
		'selectUnidadEjecutora'=>utf8_encode($rs['unidadEjecutoraID']),// 1
		'hora'=>utf8_encode($rs['hora']),// 1
		'placa'=>utf8_encode($rs['placa']),// 2
		'folio'=>utf8_encode($rs['folio']),// 3
		'garantia'=>utf8_encode($rs['garantia']),// 5
		'importe'=>utf8_encode($rs['importe']),// 5
		'receptor'=>utf8_encode($rs['receptor']),// 5
		'infractor'=>utf8_encode($rs['infractor']),// 5
		'descripcion'=>utf8_encode($rs['descripcion']),// 5
		'importe'=>utf8_encode($rs['importe']),// 7
		'identificador'=>utf8_encode($rs['clave'])// 9
	];

	$sql = "SELECT DISTINCT
	tb_contratos_objetos_parciales.id_stock as objetoParcial,
	tb_contratos_objetos_parciales.amt_total as price
	FROM tb_contratos AS contratos 
	JOIN tb_contratos_objetos_parciales on tb_contratos_objetos_parciales.id_contrato = '".$info['identificador']."'
	JOIN prices on prices.stockid = tb_contratos_objetos_parciales.id_stock
	JOIN tb_cat_tarifas on tb_cat_tarifas.id_tarifa = prices.tipo
    JOIN stockmaster on (stockmaster.stockid = prices.stockid)
    JOIN locstock ON (locstock.stockid = prices.stockid)
    JOIN locations on (locations.loccode = prices.id_op)
	WHERE contratos.id_contrato = '".$info['identificador']."' AND contratos.ind_activo = '1'";

	$result = DB_query($sql, $db);

	while ($rs2 = DB_fetch_array($result)) {
		$data['content']['parcials'][] = [
			'id'=> utf8_encode($rs2['objetoParcial']),
			'price'=>utf8_encode($rs2['price'])
		];
	}
	// procesamiento de la información obtenida

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
	// $data = ['success'=>false,'msg'=>'Ocurrió un incidente inesperado. Favor de contactar con el administrador.'];
	$info = $_POST;

	$data = ['success'=>false, 'msg'=>'Ocurrió un incidente al obtener la información o ya existe el folio de la boleta. Favor de contactar al administrador.'];

	$sqlValidacion2 = "SELECT 
	tb_propiedades_atributos.ln_valor
	FROM tb_propiedades_atributos 
	Where tb_propiedades_atributos.ln_valor = '".$info['folio']."'" ;
	$resultSelectVal2 = DB_query($sqlValidacion2, $db);
	if (db_num_rows ( $resultSelectVal2 ) > 1) {
		return $data;
	}

	if(empty($info['selectUnidadEjecutora'])){
		$data['msg'] = 'Seleccione una UE';
		return $data;
	}

	$sql = "SELECT tb_cat_unidades_ejecutoras.ur as selectUnidadNegocio  FROM  tb_cat_unidades_ejecutoras WHERE tb_cat_unidades_ejecutoras.ue = '".$info['selectUnidadEjecutora']."'"; 
	
	try {
		$result = DB_query($sql, $db);
	}
	catch (Exception $e) {
		DB_Txn_Rollback($db);
	}

	$rs = DB_fetch_array($result);

	$sql = "INSERT INTO `tb_contratos` (
			`id_confcontratos`,
			`id_debtorno`,
			`tagref`,
			`ln_ue`,
			`ind_activo`,
			`userid`,
			`enum_status`,
			`enum_periodo`,
			`nu_periodicidad`,
			`dtm_fecha_inicio`,
			`dtm_fecha_vigencia`,
			`ln_descripcion`
		)VALUES
		(
			".$info['idconfContrato'].",
			'".$info['contribuyenteID']."',
			'".$rs['selectUnidadNegocio']."',
			'".$info['selectUnidadEjecutora']."',
			'1',
			'".$_SESSION['UserID']."',
			'Pendiente',
			'Año',
			1,
			'".date("Y-m-d", strtotime($info['txtFechaInicial']))."',
			'1969-12-31',
			'".$info['descripcion']."'
		)";
	try {
		$result = DB_query($sql, $db);
	}
	catch (Exception $e) {
		DB_Txn_Rollback($db);
	}
	
	$sql = "SELECT MAX(id_contrato) as lastID FROM tb_contratos";
	$resultIn = DB_query($sql, $db);
	$rs = DB_fetch_array($resultIn);
	$contratoID = $rs['lastID'];

	$parcials = json_decode($info['parcials']);
	foreach ($parcials as $val) {
		foreach ($val as $valor) {
			$sql ="INSERT INTO `tb_contratos_objetos_parciales` ( 
					`id_contrato`, 
					`id_stock`, 
					`dtm_fecha_efectiva`, 
					`nu_cantidad`, 
					`amt_total`, 
					`ind_activo`
				)VALUES
				(
					".$contratoID.", 
					'".$valor->id."',
					now(), 
					1, 
					".$valor->price.",
					1)";
			try {
				$result = DB_query($sql, $db);
			}catch (Exception $e) {
				DB_Txn_Rollback($db);
			}
		}

	}
	$atributos = json_decode($info['atributos']);

	foreach ($atributos as $val) {
		foreach ($val as $valor) {
			$sql="INSERT INTO `tb_propiedades_atributos` (
				`id_folio_contrato`,
				`id_folio_configuracion`,
				`id_etiqueta_atributo`,
				`ln_valor`, 
				`nu_activo`
				)
				VALUES(
					'".$contratoID."', 
					'".$info['idconfContrato']."', 
					'".$valor->id."', 
					'".$valor->value."', 
					'1')";
			try {
				$result = DB_query($sql, $db);
			}catch (Exception $e) {
				DB_Txn_Rollback($db);
			}
		}
	}


	//GENERATE AUTOMATICO ADEUDO
	$sql = "SELECT contratos.id_contrato as clave,
	CONCAT(tags.tagref , ' - ' , tags.tagdescription ) AS unidadNegocio,
	tags.tagref AS unidadNegocioID,
	CONCAT(ues.ue , ' - ' , ues.desc_ue ) AS unidadEjecutora,
	ues.ue AS unidadEjecutoraID,
	CONCAT(configContrato.id_contratos, ' - ', configContrato.id_loccode,' - ', locations.locationname) AS configContrato,
	configContrato.id_contratos AS idconfContrato,
	debtorsmaster.debtorno as contribuyenteID,
	tb_contratos_objetos_parciales.id_stock as objetoParcial,
	prices.id_op as objetoPrincipal,
	ROUND(if(prices.tipo = 'UMA', prices.nu_price, tb_cat_tarifas.valor * prices.nu_price),2) as importe,
	tb_contratos_objetos_parciales.nu_cantidad as cantidad,
	tb_contratos_objetos_parciales.amt_total as total,
	contratos.dtm_fecha_inicio as fechaInicio,
	contratos.dtm_fecha_vigencia as fechaVigencia,
	contratos.nu_periodicidad as periodicidad,
	contratos.enum_periodo as periodo,
	contratos.enum_status as status
	FROM tb_contratos AS contratos 
	JOIN tags on (tags.tagref = contratos.tagref)    
	JOIN tb_cat_unidades_ejecutoras as ues on (ues.ue = contratos.ln_ue) 
	JOIN tb_contratos_contribuyentes as configContrato on (configContrato.id_contratos = contratos.id_confcontratos) 
	JOIN locations on (configContrato.id_loccode = locations.loccode)  
	JOIN debtorsmaster on (debtorsmaster.debtorno = contratos.id_debtorno)	
	JOIN tb_contratos_objetos_parciales on tb_contratos_objetos_parciales.id_contrato = '".$contratoID."'
	JOIN prices on prices.stockid = tb_contratos_objetos_parciales.id_stock
	JOIN tb_cat_tarifas on tb_cat_tarifas.id_tarifa = prices.tipo
	WHERE contratos.id_contrato = '".$contratoID."' AND contratos.ind_activo = '1'";

	$result = DB_query($sql, $db);

	// comprobación de existencia de la información
	if(DB_num_rows($result) == 0){
		$data['msg'] = 'No se encontraron los datos solicitados. Por favor verifique si tiene objetos detalle configurados';
		return $data;
	}
	// procesamiento de la información obtenida
	$cantidadPeriodo = 0;
	$total = 0;
	$mes = 0;

	while ($rs = DB_fetch_array($result)) {
		// $mes = 0;
		$me = 0;
		$mes = (int)date("m",strtotime($rs['fechaInicio']));

		if($rs['periodo'] == 'Mes'){
			// $me = round(12 - $mes + 1 );
			// $cantidadPeriodo = $me / (int)$rs['periodicidad'];
			$me = 12;
			$cantidadPeriodo = $me / (int)$rs['periodicidad'];
		}else{
			$cantidadPeriodo = 1;
		}
		$data['content'][] = [
			'contrato'=>utf8_encode($rs['clave']),// 0
			'cantidadPeriodo'=> round($cantidadPeriodo),// 0
			'contribuyenteID'=>utf8_encode($rs['contribuyenteID']),// 0
			'periodicidad'=>utf8_encode($rs['periodicidad']),// 0
			'periodo'=>utf8_encode($rs['periodo']),// 0
			'unidadNegocioID'=>utf8_encode($rs['unidadNegocioID']),// 0
			'unidadEjecutoraID'=>utf8_encode($rs['unidadEjecutoraID']),// 0
			'periodoCal'=> date("Y",strtotime($rs['fechaInicio']))."01",// 0
			'objetoPrincipal'=>utf8_encode($rs['objetoPrincipal']),// 0
			'objetoParcial'=>utf8_encode($rs['objetoParcial']),// 1
			'cantidad'=>utf8_encode($rs['cantidad']),// 1
			'importe'=>utf8_encode($rs['importe']),// 2
			'total'=>utf8_encode($rs['total']),// 2
			'fechaInicial'=> utf8_encode($rs['fechaInicio']),
			'fechaFinal'=> utf8_encode($rs['fechaVigencia']),
			'modificar'=>'<span class="modificar glyphicon glyphicon-edit"></span>',// 7
			'eliminar'=>'<span class="eliminar glyphicon glyphicon-trash"></span>',// 7
			'identificador'=>utf8_encode($rs['clave'])// 9
		];
	}


	$anio = 0;
	$contt = 0;
	// $mes = date("n",$data['content']['fechaInicial']);
	$perioNum = 0;
	for ($i=0; $i < $cantidadPeriodo; $i++) {
		$perioNum = ($mes+$contt);
		if($perioNum > 12 ){
			$mes = 1;
			$contt = 0;
			$anio++;
			$perioNum = 1;
		}
		

		foreach ($data['content'] as $key => $value) {
			# code...
			// if($i > 0){
				// $final = date("Y-m-d", strtotime("+1 month", $time));

				// $final = date("Y-m-d",strtotime($value['fechaInicial']."+".$i." month"));
				$final = date("Y-m-t", strtotime($rs['fechaInicial']."+".$i." month"));


			// }else{
			// 	$final = date("Y-m-t", strtotime($rs['fechaInicial']));

			// }
		
		$sql = "INSERT INTO `tb_administracion_contratos` 
			(
				id_contrato, 
				id_contribuyente, 
				id_periodo,
				id_objeto_principal,
				id_objeto_parcial,
				nu_cantidad,
				mtn_importe,
				mtn_total,
				dt_vencimineto, 
				pase_cobro, 
				folio_recibo,
				cajero, 
				dt_fechadepago, 
				estatus, 
				dtm_fecha_efectiva
			)
		VALUES
			(
				".$value['contrato'].",
				".$value['contribuyenteID'].",
				'".(date("Y",strtotime($value['fechaInicial'])) + $anio).str_pad(($perioNum), 2, '0', STR_PAD_LEFT)."',
				'".$value['objetoPrincipal']."',
				'".$value['objetoParcial']."',
				'".$value['cantidad']."',
				'".$value['importe']."',
				'".$value['total']."',
				'".$final."',
				'',
				'',
				'',
				'',
				'En Proceso',
				now()
			)";
			try {
				$data['sql'] = $info['contrato'];
				$result = DB_query($sql, $db);
				if($result == true){
					$data['success'] = true;
					$data['msg'] = '<p style="text-align: center;">Se ha generado el registro: '.$contratoID.' exitosamente.</p><br><button id="btnPaseYes" name="ElementoDefault" type="button" title=""  class="glyphicon glyphicon-copy btn btn-default botonVerde" style="font-weight: bold;margin: auto;display: block;" autocomplete="off">&nbsp;Generar Pase de Cobro</button>';
					$data['contratoID'] = $contratoID;
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
		$contt++;
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
	$contratoID = $info['identificador'];

	$sql = "SELECT id_contrato FROM tb_contratos WHERE id_contrato = '$contratoID' ";
	$result = DB_query($sql, $db);
	// comprobación de consistencia de datos
	if(DB_num_rows($result) == 0){
		$data['msg'] = 'No se encontraron los datos solicitados.';
		return $data;
	}

	

	$sql ="SELECT COUNT(*) as contt
	FROM tb_administracion_contratos 
	Where tb_administracion_contratos.id_contrato = '$contratoID'
	AND tb_administracion_contratos.pase_cobro = ''";
	$result = DB_query($sql, $db);
	$rs = DB_fetch_array($result);
	if($rs['contt'] == 0){
		$data['msg'] = 'No se puede modificar, sólo se puede modificar registros que no tengan Pase de cobro.';
		return $data;
	}
	$sql = "SELECT tb_cat_unidades_ejecutoras.ur as selectUnidadNegocio  FROM  tb_cat_unidades_ejecutoras WHERE tb_cat_unidades_ejecutoras.ue = '".$info['selectUnidadEjecutora']."'"; 
	
	try {
		$result = DB_query($sql, $db);
	}
	catch (Exception $e) {
		DB_Txn_Rollback($db);
	}
	$rs = DB_fetch_array($result);

	$sql = "UPDATE tb_contratos SET 
		`id_debtorno` = '".$info['contribuyenteID']."',
		`dtm_fecha_inicio` = '".date("Y-m-d", strtotime($info['txtFechaInicial']))."',
		`ln_descripcion` = '".$info['descripcion']."',
		`tagref` = '".$rs['selectUnidadNegocio']."',
		`ln_ue` ='".$info['selectUnidadEjecutora']."'
		WHERE id_contrato = '".$info['identificador']."'";

	try {
		$result = DB_query($sql, $db);
		if($result == true){
			$data['success'] = true;
			$data['msg'] = '<p style="text-align: center;">Se ha actualizado el registro: '.$contratoID.' exitosamente.</p>';
			DB_Txn_Commit($db);
		}else{
			DB_Txn_Rollback($db);
		}
	}catch (Exception $e) {
		DB_Txn_Rollback($db);
	}

	$sql = "DELETE FROM tb_contratos_objetos_parciales 
		WHERE  id_contrato = '".$contratoID."'";
	try {
		$result = DB_query($sql, $db);
	}catch (Exception $e) {
		DB_Txn_Rollback($db);
	}
	$parcials = json_decode($info['parcials']);
	foreach ($parcials as $val) {
		foreach ($val as $valor) {
			$sql ="INSERT INTO `tb_contratos_objetos_parciales` ( 
					`id_contrato`, 
					`id_stock`, 
					`dtm_fecha_efectiva`, 
					`nu_cantidad`, 
					`amt_total`, 
					`ind_activo`
				)VALUES
				(
					".$contratoID.", 
					'".$valor->id."',
					now(), 
					1, 
					".$valor->price.",
					1)";
			try {
				$result = DB_query($sql, $db);
			}catch (Exception $e) {
				DB_Txn_Rollback($db);
			}
		}

	}

	$sql = "DELETE FROM tb_propiedades_atributos 
		WHERE  id_folio_contrato = '".$contratoID."'";
	try {
		$result = DB_query($sql, $db);
	}catch (Exception $e) {
		DB_Txn_Rollback($db);
	}

	$atributos = json_decode($info['atributos']);

	foreach ($atributos as $val) {
		foreach ($val as $valor) {
			$sql="INSERT INTO `tb_propiedades_atributos` (
				`id_folio_contrato`,
				`id_folio_configuracion`,
				`id_etiqueta_atributo`,
				`ln_valor`, 
				`nu_activo`
				)
				VALUES(
					'".$contratoID."', 
					'".$info['idconfContrato']."', 
					'".$valor->id."', 
					'".$valor->value."', 
					'1')";
			try {
				$result = DB_query($sql, $db);
			}catch (Exception $e) {
				DB_Txn_Rollback($db);
			}
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
	$registrosEncontrados = "";
	$data = ['success'=>false,'msg'=>'Ocurrió un incidente al momento de eliminar la información.'];
	$info = $_POST;
	$contratoID = $info['identificador'];
	$sql = "SELECT id_contrato FROM tb_contratos WHERE id_contrato = '$contratoID' ";
	$result = DB_query($sql, $db);
	// comprobación de consistencia de datos
	if(DB_num_rows($result) == 0){
		$data['msg'] = 'No se encontraron los datos solicitados.';
		return $data;
	}

	$sql = "UPDATE tb_contratos SET ind_activo = '0' WHERE id_contrato = '$contratoID' ";
	$result = DB_query($sql, $db);
	// comprobación de consistencia de datos
	
	try {
		$result = DB_query($sql, $db);
		if ($result){
			$data['success'] = true;
			$data['msg'] = "El registro ".$contratoID." ha sido cancelado.";
		}

	}catch (Exception $e) {
		DB_Txn_Rollback($db);
	}
	

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
	
	$campos .= ",price";
	$datos .= ", '".$value * $info['importe']."'";
	$campos .= ",norango_inicial";
	$datos .= ", '".$value*$info['rangoInicial']."'";
	$campos .= ",norango_final";
	$datos .= ", '".$value*$info['rangoFinal']."'";
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
	$campos .= ",price = '".$value * $info['importe']."'";
	$campos .= ",norango_inicial = '".$value*$info['rangoInicial']."'";
	$campos .= ",norango_final = '".$value*$info['rangoFinal']."'";

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
