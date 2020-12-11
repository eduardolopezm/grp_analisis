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

$ErrMsg = _('');
$contenido = array();
$result = false;
$SQL = '';
$leasing = array();
$LeasingDetails = array();
$rowsSelected = "";
$RootPath = "";
$Mensaje = "";
header('Content-type: text/html; charset=ISO-8859-1');

$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

$option = $_POST['option'];


// if ($option == 'AgregarImporte') {
		
// 		$id_administracion_contrato = $_POST['id_administracion_contrato'];
// 		$importe = $_POST['importe'];
// 		$comments = $_POST['comments'];
// 		$proceso = $_POST['proceso'];

// 		if ($proceso == 'Modificar') {
			
// 			$SQL = "UPDATE tb_administracion_contratos SET mtn_importe = '$importe', comments = '$comments', userid = '$_SESSION[UserID]' WHERE  id_administracion_contratos = '$id_administracion_contrato'";
// 			$ErrMsg = "No se agrego la informacion de ".$id_administracion_contrato." - ".$comments;
// 			$TransResult = DB_query($SQL, $db, $ErrMsg);

// 			$contenido = "Se modificó el registro con el importe $".$importe." del pase de cobro con éxito";
// 			$result = true;
			
// 	}
// 	$dataObj = array('sql' => "", 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
// 	echo json_encode($dataObj);
// }
function AgregarImporte($db){


	$data = ['success'=>false,'msg'=>'No hay lugares que coincidan con un registro provincia tributaria para mostrar.','content'=>[]];
		
	$id_administracion_contrato = $_POST['id_administracion_contrato'];
	$importe = $_POST['importe'];
	$comments = $_POST['comments'];
	$proceso = $_POST['proceso'];


	$id_administracion_contratos = "";
	$id_contrato = "";
	$id_contribuyente = "";
	$id_periodo = "";
	$id_objeto_principal = "";
	$id_objeto_parcial = "";
	$nu_cantidad = "";
	$mtn_importe = "";
	$nu_descuento = "";
	$mtn_total= "";
	$dt_vencimineto = "";
	$pase = "";
	$folio_recibo = "";
	$cajero = "";
	$dt_fechadepago = "";
	$estatus = "";
	$dtm_fecha_aterior = "";
	
	$SQL = "SELECT 
		adminContratos.id_administracion_contratos as adminContratoID,
		adminContratos.id_contrato as contratoID,
		adminContratos.id_contribuyente as contribuyenteID,
		adminContratos.id_periodo as periodo,
		adminContratos.id_objeto_principal as objetoPrincipal,
		adminContratos.id_objeto_parcial as objetoParcial,
		adminContratos.nu_cantidad  as cantidad,
		adminContratos.mtn_importe as importe,
		adminContratos.nu_descuento as descuento,
		adminContratos.mtn_total as total,
		adminContratos.dt_vencimineto as vencimiento,
		adminContratos.pase_cobro as cobro,
		adminContratos.folio_recibo as recibo,
		adminContratos.cajero as cajero,
		adminContratos.dt_fechadepago as fechaPago,
		adminContratos.estatus as status,
		adminContratos.dtm_fecha_efectiva as fechaEfectiva
		FROM tb_administracion_contratos AS adminContratos
		WHERE adminContratos.id_administracion_contratos = '".$id_administracion_contrato."'";
		$ErrMsg = "No hay informacion";
		
		$TransResult = DB_query($SQL, $db, $ErrMsg);
		while ($myrow = DB_fetch_array($TransResult)) {
			$id_administracion_contratos = $myrow ['adminContratoID'];
			$id_contrato = $myrow ['contratoID'];
			$id_contribuyente = $myrow ['contribuyenteID'];
			$id_periodo = $myrow ['periodo'];
			$id_objeto_principal = $myrow ['objetoPrincipal'];
			$id_objeto_parcial = $myrow ['objetoParcial'];
			$nu_cantidad = $myrow ['cantidad'];
			$mtn_importe = $myrow ['importe'];
			$nu_descuento = $myrow ['descuento'];
			$mtn_total= $myrow ['total'];
			$dt_vencimineto = $myrow ['vencimiento'];
			$pase = $myrow ['cobro'];
			$folio_recibo = $myrow ['recibo'];
			$cajero = $myrow ['cajero'];
			$dt_fechadepago = $myrow ['fechaPago'];
			$estatus = $myrow ['status'];
			$dtm_fecha_aterior = $myrow ['fechaEfectiva'];	
		}

		$SQL = "INSERT INTO `tb_administracion_contratos_log` (
		`id_administracion_contratos`, 
		`id_contrato`, 
		`id_contribuyente`, 
		`id_periodo`, 
		`id_objeto_principal`, 
		`id_objeto_parcial`, 
		`nu_cantidad`, 
		`mtn_importe_anterior`, 
		`nu_descuento`, 
		`mtn_total`, 
		`dt_vencimineto`, 
		`pase_cobro`, 
		`folio_recibo`, 
		`cajero`, 
		`dt_fechadepago`, 
		`estatus`, 
		`dtm_fecha_aterior`, 
		`comments`, 
		`userid`,
		`mtn_importe_mofificado`)
		VALUES(
		'".$id_administracion_contratos."', 
		'".$id_contrato."', 
		'".$id_contribuyente."', 
		'".$id_periodo."', 
		'".$id_objeto_principal."', 
		'".$id_objeto_parcial."', 
		'".$nu_cantidad."', 
		'".$mtn_importe."', 
		'".$nu_descuento."', 
		'".$mtn_total."', 
		'".$dt_vencimineto."', 
		'".$pase."', 
		'".$folio_recibo."', 
		'".$cajero."', 
		'".$dt_fechadepago."', 
		'".$estatus."', 
		'".$dtm_fecha_aterior."', 
		'".$comments."',
		'".$_SESSION['UserID']."',
		'".$importe."')";
		$ErrMsg = "No hay informacion";

		$TransResult = DB_query($SQL, $db, $ErrMsg);
		

	$pase="";
	$cantidad= 0;
	$estatusPaseCobro = 0;
	$SQL = "SELECT tb_administracion_contratos.pase_cobro, tb_administracion_contratos.nu_cantidad, salesorders.quotation
	FROM tb_administracion_contratos 
	LEFT JOIN salesorders ON salesorders.orderno = tb_administracion_contratos.pase_cobro
	WHERE tb_administracion_contratos.id_administracion_contratos = '$id_administracion_contrato'";
	$ErrMsg = "No hay informacion";
	$TransResult = DB_query($SQL, $db, $ErrMsg);
	while ($myrow = DB_fetch_array($TransResult)) {
		$pase = $myrow ['pase_cobro'];
		$cantidad = $myrow ['nu_cantidad'];
		$estatusPaseCobro = $myrow ['quotation'];
	}
	if($estatusPaseCobro <> 5){
		$totalNew = $cantidad * $importe;

		// actualizar adeuddo
		$SQL = "UPDATE tb_administracion_contratos 
		SET mtn_importe = '$importe', mtn_total = '$totalNew' 
		WHERE  id_administracion_contratos = '$id_administracion_contrato'";
		$ErrMsg = "No se agrego la informacion de ".$id_administracion_contrato." - ".$comments;
		$TransResult = DB_query($SQL, $db, $ErrMsg);

		if (!empty($pase)) {
			// actulizar pase de cobro
			$SQL = "UPDATE salesorderdetails 
			SET unitprice = '$importe'
			WHERE id_administracion_contratos = '$id_administracion_contrato'
			AND orderno = '$pase'";
			$TransResult = DB_query($SQL, $db, $ErrMsg);
		}

		$data['result'] = true;
		$data['contenido'] = 'Se modificó el registro con el importe $'.$importe.' del adeudo con éxito';
		
		return $data;
	}else{
		$data['result'] = false;
		$data['contenido'] = 'No se puede modificar el importe, el pase de cobro ya se encuentra cobrado';
		return $data;
	}
	
}

function show($db){
	$permisoEdicionImporte = Havepermission($_SESSION ['UserID'], 2535, $db); // Poder editar comentario
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
		adminContratos.dt_vencimineto as vencimiento,
		adminContratos.pase_cobro as cobro,
		adminContratos.folio_recibo as recibo,
		adminContratos.cajero as cajero,
		adminContratos.dt_fechadepago as fechaPago,
		adminContratos.estatus as status,
		adminContratos.dtm_fecha_efectiva as fechaEfectiva,
		contratos.dtm_fecha_inicio as fechaInicio,
		debtorsmaster.name
		FROM tb_administracion_contratos AS adminContratos
		JOIN debtorsmaster ON debtorsmaster.debtorno = adminContratos.id_contribuyente
		LEFT JOIN tb_contratos contratos ON contratos.id_contrato  = adminContratos.id_contrato
		WHERE adminContratos.id_contrato = '".$_POST['contrato_id']."'";
	// datos adicionales de ordenamiento
	$sql .= " ORDER BY adminContratoID ASC";
	$data = ['success'=>false,'msg'=>$sql,'content'=>[]];
	$result = DB_query($sql, $db);

	
	

	// procesamiento de la información obtenida
	while ($rs = DB_fetch_array($result)) {
		// calcular recargo
		$earlier = new DateTime($rs['fechaInicio']);
		$later = new DateTime(date('Y-m-d'));

		$days = $later->diff($earlier)->format("%a");

		$sql = "SELECT id_parcial, nu_porcentaje as porcentaje FROM tb_recargos WHERE loccode = '".$rs['objetoPrincipal']."'";
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
		$mes = 0;
		$me = 0;
		$modificar="";
		if($permisoEdicionImporte == 1){
			$modificar = '<a onclick="fnModificar(\''.$rs ['adminContratoID'].'\',\''.$rs ['importe'].'\',\''.$rs ['comentario'].'\')"><span class="glyphicon glyphicon-edit"></span></a>';
		}

		if($rs['periodo'] == 'Mes'){
			$mes = (int)date("m",strtotime($rs['fechaVigencia']));
			$me = round(12 - $mes + 1 );
			$cantidadPeriodo = $me / (int)$rs['periodicidad'];
		}
		$data['content'][] = [
			'checkbox' => '<input class="selectCheck" data-id="'.$rs['adminContratoID'].'" type="checkbox" >',
			'clave'=>utf8_encode($rs['adminContratoID']),// 0
			'contrato'=>utf8_encode($rs['contratoID']),// 0
			'contribuyente'=>utf8_encode($rs['contribuyenteID']),// 0
			'periodo'=>utf8_encode($rs['periodo']),// 0
			'objetoPrincipal'=>utf8_encode($rs['objetoPrincipal']),// 0
			'objetoParcial'=>utf8_encode($rs['objetoParcial']),// 0
			'cantidad'=> utf8_encode($rs['cantidad']),// 0
			'importe'=>utf8_encode($rs['importe']),// 0
			'recargos'=>number_format($rs['recargos'],2),// 1
			'vencimiento'=>utf8_encode($rs['vencimiento']),// 1
			'cobro'=>utf8_encode($rs['cobro']),// 2
			'recibo'=>utf8_encode($rs['recibo']),// 2
			'cajero'=>utf8_encode($rs['cajero']),// 2
			'fechaEfectiva'=> date("d-m-Y", strtotime($rs['fechaEfectiva'])),
			'fechaPago'=> $rs['fechaPago'] != '0000-00-00' ? date("d-m-Y", strtotime($rs['fechaPago'])) : '',
			'modificar' => $modificar,
			// 'modificar'=>'<span class="modificar glyphicon glyphicon-edit"></span>',// 7
			'eliminar'=>'<span class="eliminar glyphicon glyphicon-trash"></span>',// 7
			'identificador'=>utf8_encode($rs['clave'])// 9
		];

		$data['contribuyenteName'] = $rs['name'];

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
	$data = ['success'=>false, 'msg'=>'Ocurrió un incidente al obtener la información. Favor de contactar al administrador.'];
	
	$info = array();
	$sqlValidacion = "SELECT 
	tb_propiedades_atributos.ln_valor,
	tb_propiedades_atributos.id_folio_configuracion
	FROM tb_propiedades_atributos
	JOIN tb_administracion_contratos on tb_administracion_contratos.id_contrato = tb_propiedades_atributos.id_folio_contrato
	Where tb_propiedades_atributos.id_folio_contrato = '".$_POST['contrato_id']."' LIMIT 1 OFFSET 1  " ;
	
	$resultSelectVal = DB_query($sqlValidacion, $db);
	$rowVal = "";
	$rowVal = DB_fetch_array($resultSelectVal);

    if($rowVal['id_folio_configuracion'] == 7){
		$sqlValidacion2 = "SELECT 
		tb_propiedades_atributos.ln_valor
		FROM tb_propiedades_atributos 
		Where id_folio_configuracion = 7 AND tb_propiedades_atributos.ln_valor = '".$rowVal ['ln_valor']."'" ;
		$resultSelectVal2 = DB_query($sqlValidacion2, $db);
	
		while ($row = DB_fetch_array($resultSelectVal2)) {
				if(empty($row['ln_valor'])){
					$data = ['success'=>false, 'msg'=>'El folio de la boleta esta vacio.'];
					return $data;
				}
		}
	
		if (db_num_rows ( $resultSelectVal2 ) > 1) {
			
			$data = ['success'=>false, 'msg'=>'Ya existe el folio de la boleta.'];
			return $data;
		}
	}
	// $sqlValidacion = "SELECT 
	// tb_propiedades_atributos.ln_valor,
	// contratos.dtm_fecha_inicio as fechaValidacion
	// FROM tb_propiedades_atributos
	// JOIN tb_contratos AS contratos ON contratos.id_contrato = tb_propiedades_atributos.id_folio_contrato
	// JOIN tb_administracion_contratos on tb_administracion_contratos.id_contrato = tb_propiedades_atributos.id_folio_contrato
	// Where tb_propiedades_atributos.id_folio_contrato = '".$_POST['contrato_id']."' LIMIT 1 OFFSET 0" ;
	// $resultSelectVal = DB_query($sqlValidacion, $db);
	// $rowVal = DB_fetch_array($resultSelectVal);
	// $sqlValidacion3 = "SELECT 
	// tb_propiedades_atributos.ln_valor,
	// contratos.dtm_fecha_inicio,
	// contratos.`id_contrato`
	// FROM tb_propiedades_atributos 
	// JOIN tb_contratos AS contratos ON contratos.id_contrato = tb_propiedades_atributos.id_folio_contrato
	// JOIN tb_administracion_contratos ON contratos.id_contrato = tb_administracion_contratos.id_folio_contrato
	// Where id_folio_configuracion = 7 AND tb_propiedades_atributos.ln_valor = '".$rowVal ['ln_valor']."'
	// AND tb_propiedades_atributos.ln_valor != 'S/P'
	// AND contratos.`id_contrato` != '".$_POST['contrato_id']."'
	// AND contratos.dtm_fecha_inicio <= '".$rowVal ['fechaValidacion']."', 
	// AND contratos.`status` != 'Pagado'";
	// $resultSelectVal2 = DB_query($sqlValidacion3, $db);
	// if (db_num_rows ( $resultSelectVal2 ) > 0) {
	// 	$data['msg'] ='No sé puede generar pase de cobro de adeudos posteriores.';
	// 	return $data;
	// }
	// declaración de variables de la función
	
	$info = $_POST;

	foreach ($info['selects_id'] as $selectContratoID) {
		$sql ="SELECT pase_cobro FROM tb_administracion_contratos Where tb_administracion_contratos.id_administracion_contratos = '".$selectContratoID."'";
		$resultSelect = DB_query($sql, $db);
		$row = DB_fetch_array($resultSelect);
		if($row['pase_cobro'] != ''){
			$data['msg'] = 'No se puede generar pase de cobro, ya cuenta con un pase generado previamente';
			$data['success'] = false;
			return $data;

		}
	}

	$ids = join("','",$info['selects_id']);
	$idArray = $info['selects_id'];

	// $sql ="SELECT 
	// 	id_administracion_contratos as id
   	// 	FROM tb_administracion_contratos AS adminContratos
   	// 	WHERE adminContratos.id_administracion_contratos BETWEEN '".$idArray[0]."' AND '".$idArray[count($idArray)-1]."'
	// 	AND  id_administracion_contratos NOT IN ('".$ids."')";
	//Validacion de adeudos anteriores
	// $max = max($idArray);
	// $data['max'] = $max;
	// $sql ="SELECT  
	// 	id_administracion_contratos as id
	//    	FROM tb_administracion_contratos AS adminContratos
	//    	WHERE adminContratos.id_administracion_contratos BETWEEN (SELECT adminContratos.id_administracion_contratos  
	// 	FROM tb_administracion_contratos AS adminContratos
	// 	WHERE adminContratos.id_contrato = ".$info['contrato_id']." ORDER BY adminContratos.id_administracion_contratos  ASC LIMIT 1) AND '".$max."' AND adminContratos.folio_recibo = ''
	// 	AND  id_administracion_contratos NOT IN ('".$ids."')";
	// $resultSelect = DB_query($sql, $db);
	// if(DB_num_rows($resultSelect) > 0){
	// 	$data['msg'] = 'No se puede generar pase de cobro de periodos posteriores a un adeudo.';
	// 	$data['success'] = false;
	// 	return $data;
	// }
	// Aqui va mi insert 

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
	IF(contratos.id_confcontratos = '7',(SELECT GROUP_CONCAT(' ',tb_cat_atributos_contrato.ln_etiqueta, ': ',tb_propiedades_atributos.ln_valor) FROM tb_propiedades_atributos JOIN  tb_cat_atributos_contrato on tb_cat_atributos_contrato.id_atributos = tb_propiedades_atributos.id_etiqueta_atributo Where tb_propiedades_atributos.id_folio_contrato = adminContratos.id_contrato AND tb_propiedades_atributos.id_etiqueta_atributo != '24'),CONCAT('FOLIO: ',contratos.id_contrato,' ',(SELECT GROUP_CONCAT(' ',tb_cat_atributos_contrato.ln_etiqueta, ': ',tb_propiedades_atributos.ln_valor) FROM tb_propiedades_atributos JOIN  tb_cat_atributos_contrato on tb_cat_atributos_contrato.id_atributos = tb_propiedades_atributos.id_etiqueta_atributo Where tb_propiedades_atributos.id_folio_contrato = adminContratos.id_contrato))) as observaciones
	FROM tb_administracion_contratos AS adminContratos
	JOIN tb_contratos AS contratos ON contratos.id_contrato = adminContratos.id_contrato
	JOIN `debtorsmaster` ON `debtorsmaster`.`debtorno` = adminContratos.id_contribuyente
	LEFT JOIN `custbranch` ON `custbranch`.`debtorno` = adminContratos.id_contribuyente
	LEFT JOIN tags on (tags.tagref = contratos.tagref)
	LEFT JOIN tb_cat_unidades_ejecutoras as ues on (ues.ue = contratos.ln_ue) 
	LEFT JOIN tb_descuentos on id_parcial = adminContratos.id_objeto_parcial AND  tb_descuentos.nu_estatus = '1'
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
	$data['descuento'] = $descuento;
	
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
		foreach ($info['selects_id'] as &$valor) {
			$sql = "UPDATE tb_administracion_contratos
			SET 
				pase_cobro = '".$documentnumber."',
				dt_vencimineto = '".$row['fechaVencimiento']."',
				nu_descuento = '".$row['descuento']."'
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
	$contt2 = 0;

	$resultSelect = DB_query($sqlSelect, $db);


	while ($rs = DB_fetch_array($resultSelect)) {

		$sql = "SELECT DISTINCT
		contrato.dtm_fecha_inicio as fechaInicio,
		tb_descuentos.num_dias as numDias
		FROM tb_administracion_contratos AS adminContratos
		LEFT JOIN tb_descuentos on id_parcial = adminContratos.id_objeto_parcial AND tb_descuentos.nu_estatus = '1'
		JOIN  tb_contratos as contrato on contrato.id_contrato =  '".$info['contrato_id']."'
		WHERE adminContratos.id_contrato = '".$info['contrato_id']."'
		AND adminContratos.id_objeto_parcial = '".$rs['objetoParcialID']."'";
 

		$result = DB_query($sql, $db);
		$rows2 = DB_fetch_array($result);
		

		$sql = "SELECT Fecha, NombreDia, Feriado
		FROM DWH_Tiempo 
		WHERE Fecha > '".$rows2['fechaInicio']."' LIMIT 30";

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

		if ($rs['tipoDescuento'] == 'campaña') {
			if ((date('Y-m-d') >= $rs['fechaInicioDescuento']) && (date('Y-m-d') <= $rs['fechaFinDescuento'])) {
				$descuento = $rs['nuDescuento'];
			}
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

		$contt2++;
		try {
			$result = DB_query($sql, $db);
			if($result == true){
				$data['success'] = true;
				// https://grp.onaxis.mx/ap_grp_demo/PDFCotizacionTemplateV2.php?tipodocto=2&TransNo=144&Tagref=10&legalid=2
				// $data['msg'] = '<p>Se ha generado el pase de cobro: '.$documentnumber.' exitosamente.</p><br>';
				$data['msg'] = '<div style ="text-align: center;"><p>Se creo Pase de Cobro no: '.$documentnumber.', correctamente. </p><p><i class="fa fa-print text-success" aria-hidden="true"></i><a target="_blank" href="PDFCotizacionTemplateV2.php?tipodocto=2&amp;TransNo='.$documentnumber.'&amp;Tagref=10&amp;legalid=2"><!-- react-text: 16 --> <!-- /react-text --><!-- react-text: 17 -->Imprimir Pase de Cobro No. '.$documentnumber.'<!-- /react-text --></a></p></div>';

			}
		} catch (Exception $e) {
			// captura del error
			$data['msg'] .= '<br>'.$e->getMessage();
			DB_Txn_Rollback($db);
		}

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
				$data['paseCobro'] = $documentnumber;
				$data['msg'] = '<div style ="text-align: center;"><p>Se creo Pase de Cobro no: '.$documentnumber.', correctamente. </p><p><i class="fa fa-print text-success" aria-hidden="true"></i><a target="_blank" href="PDFCotizacionTemplateV2.php?tipodocto=2&amp;TransNo='.$documentnumber.'&amp;Tagref=10&amp;legalid=2"><!-- react-text: 16 --> <!-- /react-text --><!-- react-text: 17 -->Imprimir Pase de Cobro No. '.$documentnumber.'<!-- /react-text --></a></p></div>';

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

