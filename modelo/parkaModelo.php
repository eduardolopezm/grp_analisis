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


include ('config_cronjob.php');
include ('android_functions.php');
$host = '23.111.130.190';
$mysqlport = "3306";
$dbuser = 'desarrollo';
$dbpassword = 'p0rtAli70s';
$android_enable = true;
$_SESSION ['DatabaseName'] = 'ap_grp_demo';
$dbsocket = '/home/mysql/mysql.sock';
$dbsocket = '/var/lib/mysql/mysql.sock';
//
// echo "<br>host: ".$host;
// echo "<br>dbuser: ".$dbuser;
// echo "<br>dbpassword: ".$dbpassword;
// echo "<br>DatabaseName: ".$_SESSION ['DatabaseName'];

/* DECLARACIÓN DE VARIABLES */
$PageSecurity = 8;
$PathPrefix = '../';
$funcion = 2508;
$contratoSelected = '';

/* INCLUSIÓN DE ARCHIVOS NECESARIOS */
include($PathPrefix . 'config.php');
// include($PathPrefix . 'includes/SecurityUrl.php');
include($PathPrefix . 'includes/ConnectDB.inc');
// include($PathPrefix . 'includes/SecurityFunctions.inc');
include($PathPrefix . 'includes/SQL_CommonFunctions.inc');

/*********************************************** CONSTANTES ***********************************************/
define('DECPLACE', !empty($_SESSION['DecimalPlaces'])? $_SESSION['DecimalPlaces'] : 2);
define('LNTBLCAT', 'tb_contratos');
define('IDENTIFICADOR', 'id_contrato');
define('ACTIVECOL', 'ind_activo');
define('DATA', [
		'id_parka_infraccion' => ['col' => 'tagref', 'tipo' => 'string'],
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



 


/**
 * Función para el guardado de la información de los ítems generados
 * @param	[DBInstance]	$db		Instancia de la base de datos
 * @return	[Array]			$data	Arreglo con la respuesta verdadera o falsa, según lo obtenido
 */
function store($db){

	$parka_id = 0;
	$confContratoID = 7;
	$etiquetaAtributoPlacaID = 23;
	$etiquetaAtributoFolioID = 24;

	


	$sql = "SELECT 
	debtorsmaster.debtorno as contribuyenteID,
	debtorsmaster.address1 as 'calle',
	debtorsmaster.numExt as 'numExt',
	debtorsmaster.numInt as 'numInt',
	debtorsmaster.address2 as 'colonia',
	debtorsmaster.address3 as 'municipio',
	debtorsmaster.address4 as 'estado',
	debtorsmaster.address5 as 'cp',
	custbranch.taxid as rfc,
	custbranch.email as email
	FROM debtorsmaster
	LEFT JOIN custbranch ON custbranch.debtorno = debtorsmaster.debtorno
	WHERE debtorsmaster.debtorno = '1'";
	$resultSelect = DB_query($sql, $db);
	$row = DB_fetch_array($resultSelect);

	$sql = "SELECT `price` FROM `prices` WHERE stockid = 'ESTN_0001' AND anio = '2019'";
	$resultSelect = DB_query($sql, $db);
	$row2 = DB_fetch_array($resultSelect);

	
	// $json = '[
	// 	{
	// 	"id_parka_infraccion":"21",
	// 	"fecha":"2019-05-04",
	// 	"hora":"23:59:59",
	// 	"id_verificador":"41",
	// 	"vehiculo_placa":"XFD1234",
	// 	"status":"Pendiente",
	// 	"origen":"parka",
	// 	"capturada":0,
	// 	"updated_at":"2019-12-20 12:45:27.435",
	// 	"created_at":"2019-12-20 12:45:27.435",
	// 	"id_infraccion":1100,
	// 	"observaciones_grp":"Placa: XFD1234\nFecha: 2019-05-04\nHora: 23:59:59\nMarca: \nColor: \nModelo: \nEstado: \nTipo: \nObservaciones: \nOrigen de infraccion: parka\nID Infraccion Parkapp: 21\nID Verificador: 41\nNombre Verificador: EDUARDO ALEJANDRO ROMERO JUAREZ\n",
	// 	"verificador":{
	// 		"id_verificador":2,
	// 		"nombre":"EDUARDO ALEJANDRO",
	// 		"apellidos":"ROMERO JUAREZ",
	// 		"status":"Aprobado",
	// 		"created_at":"2019-09-10 09:03:30.780",
	// 		"updated_at":"2019-09-10 09:03:30.780",
	// 		"deleted_at":null,
	// 		"codigo_verificador":"41"
	// 	}
	// 	},
	// 	{
	// 	"id_parka_infraccion":"22",
	// 	"fecha":"2019-05-06",
	// 	"hora":"00:00:00",
	// 	"vehiculo_placa":"XFD5678",
	// 	"vehiculo_marca":"Nissan",
	// 	"vehiculo_color":"Azul marino",
	// 	"vehiculo_modelo":"Frontier",
	// 	"vehiculo_estado":"Tamaulipas",
	// 	"vehiculo_tipo":"Pickup",
	// 	"parquimetro":"Parquimetro 1",
	// 	"calle":"Col\u00f3n",
	// 	"entre_calles":"D\u00edaz Mir\u00f3n y Emilio Carranza",
	// 	"colonia":"Zona Centro",
	// 	"observaciones":"Observaciones del vehiculo",
	// 	"id_verificador":"41",
	// 	"status":"Pendiente",
	// 	"origen":"parka",
	// 	"capturada":0,
	// 	"updated_at":"2019-12-20 12:45:27.438",
	// 	"created_at":"2019-12-20 12:45:27.438",
	// 	"id_infraccion":1101,
	// 	"observaciones_grp":"Placa: XFD5678\nFecha: 2019-05-06\nHora: 00:00:00\nMarca: Nissan\nColor: Azul marino\nModelo: Frontier\nEstado: Tamaulipas\nTipo: Pickup\nObservaciones: Observaciones del vehiculo\nOrigen de infraccion: parka\nID Infraccion Parkapp: 22\nID Verificador: 41\nNombre Verificador: EDUARDO ALEJANDRO ROMERO JUAREZ\n",
	// 	"verificador":{
	// 		"id_verificador":2,
	// 		"nombre":"EDUARDO ALEJANDRO",
	// 		"apellidos":"ROMERO JUAREZ",
	// 		"status":"Aprobado",
	// 		"created_at":"2019-09-10 09:03:30.780",
	// 		"updated_at":"2019-09-10 09:03:30.780",
	// 		"deleted_at":null,
	// 		"codigo_verificador":"41"
	// 	}
	// 	}
	// ]';
	

	$str_json = file_get_contents('php://input');

	$datos = json_decode($str_json);


	foreach ($datos as $valor){

		
		$parka_id = $valor->id_parka_infraccion;
		$sqlCheck = "SELECT id_parka_infraccion, id_contrato FROM tb_parka WHERE id_parka_infraccion = '".$valor->id_parka_infraccion."'";
		$result = DB_query($sqlCheck, $db);
		$rs2 = DB_fetch_array($result);

		$contratoID = $rs2['id_contrato'];


		if(DB_num_rows($result) == 0){

			$sql = "INSERT INTO `tb_parka` 
						(`id_parka_infraccion`, 
						`id_verificador`, 
						`id_infraccion`,
						`vehiculo_placa`, 
						`vehiculo_marca`, 
						`vehiculo_color`, 
						`vehiculo_modelo`, 
						`vehiculo_estado`, 
						`vehiculo_tipo`, 
						`parquimetro`,
						`referencia`,
						`calle`, 
						`entre_calles`, 
						`colonia`, 
						`origen`, 
						`capturada`, 
						`observaciones`,
						`observaciones_grp`,
						`status`, 
						`fecha`, 
						`hora`,
						`updated_at`, 
						`created_at`) 
				VALUES      (
						".$valor->id_parka_infraccion.", 
						".$valor->id_verificador.", 
						".$valor->id_infraccion.", 
						'".utf8_decode($valor->vehiculo_placa)."', 
						'".utf8_decode($valor->vehiculo_marca)."', 
						'".utf8_decode($valor->vehiculo_color)."', 
						'".utf8_decode($valor->vehiculo_modelo)."', 
						'".utf8_decode($valor->vehiculo_estado)."', 
						'".utf8_decode($valor->vehiculo_tipo)."', 
						'".utf8_decode($valor->parquimetro)."', 
						'".utf8_decode($valor->referencia)."', 
						'".utf8_decode($valor->calle)."', 
						'".utf8_decode($valor->entre_calles)."', 
						'".utf8_decode($valor->colonia)."',
						'".utf8_decode($valor->origen)."',
						'".utf8_decode($valor->capturada)."',
						'".utf8_decode($valor->observaciones)."', 
						'".utf8_decode($valor->observaciones_grp)."',
						'".utf8_decode($valor->status)."',
						'".utf8_decode($valor->fecha)."',
						'".utf8_decode($valor->hora)."',
						'".utf8_decode($valor->updated_at)."',
						'".utf8_decode($valor->created_at)."'
					)";

			try {
				$result = DB_query($sql, $db);
				if($result == true){
					$data['success'] = true;
					// $data['msg'] = "<p>Se ha generado el pase de cobro: ".$documentnumber." exitosamente.</p>";

				}
			} catch (Exception $e) {
				// captura del error
				$data['msg'][] = $e->getMessage();
				DB_Txn_Rollback($db);
			}

		$sqlCheck = "SELECT id_verificador FROM tb_parka_verificador WHERE id_verificador = '".$valor->verificador->id_verificador."'";
		$result = DB_query($sqlCheck, $db);

		if(DB_num_rows($result) == 0){
			$sql = "INSERT INTO `tb_parka_verificador` 
					(`id_verificador`,
					`codigo_verificador`,
					`nombre`, 
					`apellidos`, 
					`status`, 
					`created_at`, 
					`updated_at`, 
					`deleted_at`) 
			VALUES      (
					".$valor->verificador->id_verificador.",
					".$valor->verificador->codigo_verificador.",
					'".utf8_decode($valor->verificador->nombre)."', 
					'".utf8_decode($valor->verificador->apellidos)."', 
					'".utf8_decode($valor->verificador->status)."', 
					'".utf8_decode($valor->verificador->created_at)."', 
					'".utf8_decode($valor->verificador->updated_at)."', 
					'".utf8_decode($valor->verificador->deleted_at)."')";
			try {
				$result = DB_query($sql, $db);
				if($result == true){
					$data['success'] = true;

				}
			} catch (Exception $e) {
				// captura del error
				// $data['msg'] .= '<br>'.$e->getMessage();
				DB_Txn_Rollback($db);
			}
		}

		$contratoID = GetNextTransNo(313, $db);

		$sql ="INSERT INTO `tb_contratos` (
			`id_contrato`,
			`id_confcontratos`, 
			`id_debtorno`, 
			`tagref`, 
			`ln_ue`, 
			`ind_activo`, 
			`userid`, 
			`dtm_fecha_efectiva`, 
			`enum_status`, 
			`enum_periodo`, 
			`nu_periodicidad`, 
			`dtm_fecha_inicio`, 
			`dtm_fecha_vigencia`, 
			`ln_descripcion`)
		VALUES
			('".$contratoID."', 
			'".$confContratoID."', 
			'99990002', 
			'6000', 
			'6001', 
			'1', 
			'desarrollo2', 
			'2020-01-02 00:57:21', 
			'Pendiente', 
			'Año', 
			1, 
			'".utf8_decode($valor->fecha)."',
			'2020-01-16', 
			'".utf8_decode($valor->observaciones_grp)."')";
		

		$result = DB_query($sql, $db);

		// $sql = "SELECT MAX(id_contrato) as lastID FROM tb_contratos";

		// $resultIn = DB_query($sql, $db);
		// $rs = DB_fetch_array($resultIn);
		// $contratoID = $rs['lastID'];

		$sql = "UPDATE tb_parka
		SET id_contrato ='".$contratoID."'
		WHERE id_parka_infraccion = '".$valor->id_parka_infraccion."'";
		$resultUptade = DB_query($sql, $db);

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


		$SQL="INSERT INTO 
			`tb_propiedades_atributos` (
				`id_folio_contrato`, 
				`id_folio_configuracion`, 
				`id_etiqueta_atributo`, 
				`ln_valor`, 
				`nu_activo`
				)
        VALUES(
			'".$contratoID."', 
			'".$confContratoID."', 
			'".$etiquetaAtributoPlacaID."', 
			'".$valor->vehiculo_placa."', 
			'1')";

        $ErrMsg = "No se agrego la informacion del contrato  ".$contratoID;
		$TransResult = DB_query($SQL, $db, $ErrMsg);

		$SQL="INSERT INTO 
			`tb_propiedades_atributos` (
				`id_folio_contrato`, 
				`id_folio_configuracion`, 
				`id_etiqueta_atributo`, 
				`ln_valor`, 
				`nu_activo`
				)
        VALUES(
			'".$contratoID."', 
			'".$confContratoID."', 
			'".$etiquetaAtributoFolioID."', 
			'".$valor->referencia."', 
			'1')";

        $ErrMsg = "No se agrego la informacion del contrato  ".$contratoID;
		$TransResult = DB_query($SQL, $db, $ErrMsg);
		

		//GENERATE AUTOMATICO ADEUDO
        $sql = "SELECT contratos.id_contrato as clave,
        CONCAT(tags.tagref , ' - ' , tags.tagdescription ) AS unidadNegocio,
        tags.tagref AS unidadNegocioID,
        CONCAT(ues.ue , ' - ' , ues.desc_ue ) AS unidadEjecutora,
        ues.ue AS unidadEjecutoraID,
        CONCAT(configContrato.id_contratos, ' - ', configContrato.id_loccode,' - ', locations.locationname) AS configContrato,
        configContrato.id_contratos AS idconfContrato,
        debtorsmaster.debtorno as contribuyenteID,
        tb_cat_objetos_contrato.id_stock as objetoParcial,
        tb_cat_objetos_contrato.id_loccode as objetoPrincipal,
        tb_cat_objetos_contrato.amt_valor as importe,
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
        JOIN tb_cat_objetos_contrato on tb_cat_objetos_contrato.id_objetos = tb_contratos_objetos_parciales.id_objetos
        WHERE contratos.id_contrato = '".$contratoID."' AND contratos.ind_activo = '1'";

        $result = DB_query($sql, $db);

        // comprobación de existencia de la información
        if(DB_num_rows($result) == 0){
            $data['msg'] = 'No se encontraron los datos solicitados. Por favor verifique si tiene objetos detalle configurados';
            return $data;
        }
        // procesamiento de la información obtenida
        $rs3 = DB_fetch_array($result);
		$mes = (int)date("m",strtotime($rs3['fechaInicio']));
		$final = date("Y-m-t", strtotime($rs3['fechaInicio']."+1 month"));
		$perioNum = ($mes);

		// $sql = "INSERT INTO `tb_administracion_contratos`
		// 		(
		// 		id_contrato, 
		// 		id_contribuyente, 
		// 		id_periodo,
		// 		id_objeto_principal,
		// 		id_objeto_parcial,
		// 		nu_cantidad,
		// 		mtn_importe,
		// 		mtn_total,
		// 		dt_vencimineto, 
		// 		pase_cobro, 
		// 		folio_recibo,
		// 		cajero, 
		// 		dt_fechadepago, 
		// 		estatus, 
		// 		dtm_fecha_efectiva
		// 	)
		// VALUES
		// 	(
		// 		".$rs['clave'].",
		// 		".$rs['contribuyenteID'].",
		// 		'".(date("Y",strtotime($rs['fechaInicio']))).str_pad(($mes), 2, '0', STR_PAD_LEFT)."',
		// 		'".$rs['objetoPrincipal']."',
		// 		'".$rs['objetoParcial']."',
		// 		'".$rs['cantidad']."',
		// 		'".$rs['importe']."',
		// 		'".$rs['total']."',
		// 		'".date("Y-m-t", strtotime($rs['fechaVigencia']))."',
		// 		'',
		// 		'',
		// 		'',
		// 		'',
		// 		'En Proceso',
		// 		now()
		// 	)";

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
                    ".$rs3['clave'].",
                    ".$rs3['contribuyenteID'].",
                    '".(date("Y",strtotime($rs3['fechaInicio']))).str_pad(($perioNum), 2, '0', STR_PAD_LEFT)."',
                    '".$rs3['objetoPrincipal']."',
                    '".$rs3['objetoParcial']."',
                    '".$rs3['cantidad']."',
                    '".$rs3['importe']."',
                    '".$rs3['total']."',
                    '".$final."',
                    '',
                    '',
                    '',
                    '',
                    'En Proceso',
                    now()
                )";
			// $result = DB_query($sql, $db);
	
		try {
			$result = DB_query($sql, $db);
			$data['success'] = true;
			$data['msg'][]  = "Se ha generado el folio de contrato: ".$contratoID." exitosamente.";
			$data['folioContrato'][] = $contratoID;
			
		} catch (Exception $e) {
			// captura del error
			$data['msg'] .= '<br>'.$e->getMessage();
			DB_Txn_Rollback($db);
		}
            
          
	

		// //Add salesorders

		// $sql = "INSERT INTO `salesorders` (
		// 		`orderno`, 
		// 		`debtorno`, 
		// 		`branchcode`, 
		// 		`customerref`, 
		// 		`buyername`, 
		// 		`comments`, 
		// 		`orddate`, 
		// 		`ordertype`, 
		// 		`shipvia`, 
		// 		`deladd1`, 
		// 		`deladd2`, 
		// 		`deladd3`, 
		// 		`deladd4`, 
		// 		`deladd5`, 
		// 		`deladd6`, 
		// 		`contactphone`, 
		// 		`contactemail`, 
		// 		`deliverto`, 
		// 		`deliverblind`, 
		// 		`freightcost`, 
		// 		`fromstkloc`, 
		// 		`deliverydate`, 
		// 		`quotedate`, 
		// 		`confirmeddate`, 
		// 		`printedpackingslip`, 
		// 		`datepackingslipprinted`, 
		// 		`quotation`, 
		// 		`placa`, 
		// 		`serie`, 
		// 		`kilometraje`, 
		// 		`salesman`, 
		// 		`tagref`, 
		// 		`taxtotal`, 
		// 		`totaltaxret`, 
		// 		`currcode`, 
		// 		`paytermsindicator`, 
		// 		`contract_type`, 
		// 		`advance`, 
		// 		`userregister`, 
		// 		`typeorder`, 
		// 		`refundpercentsale`, 
		// 		`vehicleno`, 
		// 		`idtarea`, 
		// 		`contid`, 
		// 		`codigobarras`, 
		// 		`idprospect`, 
		// 		`nopedido`, 
		// 		`noentrada`, 
		// 		`extratext`, 
		// 		`noremision`, 
		// 		`totalrefundpercentsale`, 
		// 		`puestaenmarcha`, 
		// 		`paymentname`, 
		// 		`nocuenta`, 
		// 		`deliverytext`, 
		// 		`estatusprocesing`, 
		// 		`serviceorder`, 
		// 		`usetype`, 
		// 		`statuscancel`, 
		// 		`fromcr`, 
		// 		`ordenprioridad`, 
		// 		`discountcard`, 
		// 		`payreference`, 
		// 		`app_cotizador`,
		// 		`ln_ue`,
		// 		`id_parka_infraccion`)
		// VALUES      
		// ( 
		// 		".$documentnumber.",
		// 		'".$row['contribuyenteID']."', 
		// 		'".$row['contribuyenteID']."', 
		// 		'".$row['rfc']."', 
		// 		NULL, 
		// 		'".utf8_decode($valor->observaciones_grp)."', 
		// 		'".utf8_decode($valor->fecha)."', 
		// 		'L1', 
		// 		1, 
		// 		'".$row['calle']."', 
		// 		'".$row['municipio']."', 
		// 		'".$row['estado']."', 
		// 		'".$row['cp']."', 
		// 		'', 
		// 		'".$row['colonia']."', 
		// 		'0', 
		// 		'".$row['email']."', 
		// 		'0', 
		// 		1, 
		// 		0, 
		// 		'ESTN', 
		// 		now(), 
		// 		now(), 
		// 		now(), 
		// 		0, 
		// 		'0000-00-00', 
		// 		'1', 
		// 		'', 
		// 		'', 
		// 		0, 
		// 		'', 
		// 		'10',
		// 		0, 
		// 		0, 
		// 		'MXN', 
		// 		'1', 
		// 		0, 
		// 		0, 
		// 		'desarrollo2', 
		// 		0, 
		// 		0, 
		// 		0, 
		// 		0, 
		// 		0, 
		// 		NULL, 
		// 		0, 
		// 		'', 
		// 		'', 
		// 		'', 
		// 		'', 
		// 		0, 
		// 		'', 
		// 		'', 
		// 		'No Identificado', 
		// 		'', 
		// 		0, 
		// 		NULL, 
		// 		0, 
		// 		0, 
		// 		NULL, 
		// 		0, 
		// 		'', 
		// 		NULL, 
		// 		0,
		// 		'0515-21',
		// 		'".$valor->id_parka_infraccion."')
		// ";
		// try {
		// 	$result = DB_query($sql, $db);
		// 	if($result == true){
		// 		$data['success'] = true;
		// 		DB_Txn_Commit($db);
		// 	}else{
		// 		DB_Txn_Rollback($db);
		// 	}
		// } catch (Exception $e) {
		// 	// captura del error
		// 	$data['msg'][] = $e->getMessage();
		// 	DB_Txn_Rollback($db);
		// }


		// $sql = "INSERT INTO `salesorderdetails` 
		// 			(`orderlineno`, 
		// 			`orderno`, 
		// 			`stkcode`, 
		// 			`fromstkloc`, 
		// 			`qtyinvoiced`, 
		// 			`unitprice`, 
		// 			`quantity`, 
		// 			`alto`, 
		// 			`ancho`, 
		// 			`calculatepricebysize`, 
		// 			`largo`, 
		// 			`quantitydispatched`, 
		// 			`adevengar`, 
		// 			`facturado`, 
		// 			`devengado`, 
		// 			`xfacturar`, 
		// 			`afacturar`, 
		// 			`xdevengar`, 
		// 			`nummes`, 
		// 			`refundpercent`, 
		// 			`saletype`, 
		// 			`estimate`, 
		// 			`discountpercent`, 
		// 			`discountpercent1`, 
		// 			`discountpercent2`, 
		// 			`actualdispatchdate`, 
		// 			`completed`, 
		// 			`narrative`, 
		// 			`itemdue`, 
		// 			`poline`, 
		// 			`warranty`, 
		// 			`salestype`, 
		// 			`servicestatus`, 
		// 			`pocost`, 
		// 			`idtarea`, 
		// 			`cashdiscount`, 
		// 			`showdescrip`, 
		// 			`readonlyvalues`, 
		// 			`modifiedpriceanddiscount`, 
		// 			`totalrefundpercent`, 
		// 			`qtylost`, 
		// 			`datelost`, 
		// 			`woline`, 
		// 			`stkmovid`, 
		// 			`userlost`, 
		// 			`localidad`, 
		// 			`stockidkit`, 
		// 			`anticipo`, 
		// 			`numpredial`) 
		// 	VALUES      (
		// 			0, 
		// 			".$documentnumber.", 
		// 			'ESTN_0001',
		// 			'ESTN',
		// 			0,
		// 			".$row2['price'].",
		// 			1,
		// 			0, 
		// 			0, 
		// 			0, 
		// 			0, 
		// 			1,
		// 			0, 
		// 			0, 
		// 			0, 
		// 			0, 
		// 			0, 
		// 			0, 
		// 			0, 
		// 			0, 
		// 			0, 
		// 			0, 
		// 			0, 
		// 			0, 
		// 			0, 
		// 			'0000-00-00 00:00:00', 
		// 			0, 
		// 			'', 
		// 			now(), 
		// 			'', 
		// 			0, 
		// 			'L1', 
		// 			0, 
		// 			NULL, 
		// 			0, 
		// 			0, 
		// 			'1', 
		// 			0, 
		// 			0, 
		// 			0, 
		// 			NULL, 
		// 			NULL, 
		// 			NULL, 
		// 			0, 
		// 			NULL, 
		// 			NULL, 
		// 			NULL, 
		// 			NULL, 
		// 			'')";

		// 		try {
		// 			$result = DB_query($sql, $db);
		// 			if($result == true){
		// 				$data['success'] = true;
		// 				$data['msg'][]  =  "Se ha generado el pase de cobro: ".$documentnumber." exitosamente.";
		// 				$data['paseCobro'][] = $documentnumber;

		// 			}
		// 		} catch (Exception $e) {
		// 			// captura del error
		// 			$data['msg'][] = $e->getMessage();
		// 			DB_Txn_Rollback($db);
		// 		}
		}else{
			$data['msg'][]  = "El número de infracción: ".$parka_id." ya se ha capturado previamente en el sistema.";
			$data['folioContrato'][] = $contratoID;

		}
		
	}



	


	return $data;
}

/*********************************************** DESARROLLO DE FUNCIONES ***********************************************/

/* EJECUCIÓN DE FUNCIONES */
$data = call_user_func_array('store',[$db]);
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
