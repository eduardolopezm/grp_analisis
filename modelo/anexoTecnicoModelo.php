<?php
/**
 * Panel de anexo técnico.
 *
 * @category     Panel
 * @package      ap_grp
 * @version      0.1
 * @link /anexoTecnicoModelo.php
 * Fecha Creación: 29.12.17
 * Se genera el presente programa para la visualización de la información
 * de los anexos técnicos que se generan para las inquisiciones.
 */
/**
	ini_set('display_errors', 1);
	ini_set('log_errors', 1);
	error_reporting(E_ALL);
	// ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
/**/

$PageSecurity = 1;
$PathPrefix = '../';
$funcion=2322;
define('FUNCTIONID', $funcion);

//include($PathPrefix.'includes/session.inc');
session_start();
include($PathPrefix . 'config.php');
include $PathPrefix . "includes/SecurityUrl.php";
include($PathPrefix . 'includes/ConnectDB.inc');
include($PathPrefix . 'includes/SecurityFunctions.inc');
include($PathPrefix . 'includes/SQL_CommonFunctions.inc');
// Include para el periodo
include($PathPrefix . 'includes/DateFunctions.inc');

// define('NOTPERM', [2,3,5] );
define('TYPEMOV', 51);

/*********************************************** DESARROLLO DE FUNCIONES ***********************************************/
/**
 * Función principal para el muestreo de datos en pantalla con forme a nececidades del cliente
 * @param  DBInstance $db   Instancia de conexión
 * @return array      $data Arreglo con la información resultante de la ejecucion
 */
function getAnexos($db)
{
	$data = array('success'=>false, 'msg'=>'Ocurrió un incidente inesperado al momento de consultar la información.', 'content'=>[]);
	/* DECLARACION DE VARIABLES */
	$noPermitidos = getNoPermitidos($db);// obtencion de los permisos segun el perfil
	$sql = '';
	$info = $_POST; // signación del post
	
	$colums = " ant.nu_anexo as folio, date_format(ant.dt_fecha_creacion,'%d/%m/%Y') as fecha, t.tagdescription as ur, ant.txt_descripcion_antecedentes as descripcion, ant.ind_status as status, ant.nu_tagref,ant.nu_ue,(SELECT desc_ue FROM tb_cat_unidades_ejecutoras where ur=ant.nu_tagref and ue=ant.nu_ue) as desUE";

	$joins = " INNER JOIN tags AS t ON t.tagref = ant.nu_tagref 
			   JOIN sec_unegsxuser ON sec_unegsxuser.tagref = ant.nu_tagref AND sec_unegsxuser.userid = '".$_SESSION['UserID']."'
               JOIN `tb_sec_users_ue` ON `tb_sec_users_ue`.`userid` = '".$_SESSION['UserID']."' AND ant.nu_tagref = `tb_sec_users_ue`.`tagref` AND  ant.nu_ue= `tb_sec_users_ue`.`ue`";// TODO: Si incrementa mandar a una funcion

	$group = " ant.nu_anexo, t.tagdescription, ant.ind_status, ant.nu_tagref ";
	// $orderBy = " ant.dt_fecha_creacion DESC, ant.nu_anexo DESC, ant.ind_status DESC ";// LIMIT 2
	$orderBy = " folio DESC ";// LIMIT 2
	// $orderBy = " fecha ASC, folio ASC, status DESC, ur, descripcion,  ant.nu_tagref ";// LIMIT 2
	$where = '';
	// generacion del where en caso de sernecesario
	$w = getWhere($db, $info);
	if(strlen($w)!=0){ $where = ' WHERE '.$w; }

	# construccion del query
	$sql = " SELECT DISTINCT $colums FROM tb_cnfg_anexo_tecnico as ant $joins $where ORDER BY $orderBy " . (!empty($info['limit'])? " LIMIT " . $info['limit'] : '');
	// $sql = " SELECT DISTINCT $colums FROM tb_cnfg_anexo_tecnico as ant $joins $where GROUP BY $group ORDER BY $orderBy " . (!empty($info['limit'])? " LIMIT " . $info['limit'] : '');
	// $data['sql'] = $sql;
	$res = DB_query($sql, $db);
	if(DB_num_rows($res)!=0){
		$rows = array();
		$enc = new Encryption;
		while ($rs = mysqli_fetch_object($res)) {
		// while ($result = DB_fetch_assoc($res)) {
			$tempSt = getStatus($db)['content'];
			$status = $tempSt[$rs->status]['label'];
			$url = '&folio=>'.$rs->folio;
			$noLink = [2,3,5];
			$folio = in_array($rs->status, $noPermitidos)?
				'<a href="anexoTecnicoDetalle.php?URL='.($enc->encode($url.'&status=>'.$rs->status)).'" style="color: blue;"><u>'.$rs->folio.'</u></a>'
				: '<a href="anexoTecnicoDetalle.php?URL='.($enc->encode($url)).'" style="color: blue;"><u>'.$rs->folio.'</u></a>';
			$rows[] = [
				'folio'=> $folio,
				'fecha' => $rs->fecha,
				'ur' => utf8_encode($rs->ur),
				'descripcion' => utf8_encode($rs->descripcion),
				'sSta' => $status,
				'nUR' => $rs->nu_tagref,
				'status' => $rs->status,
				'url' => in_array($rs->status, $noPermitidos)?'':$enc->encode($url),
				'iFolio'=>$rs->folio,
				'nUE'=>$rs->nu_ue,
				'ue'=>utf8_encode($rs->desUE)
				
					
			];
		}
		# agregado de la respuesta
		$data['content'] = $rows;
		$data['success'] = true;
		$data['msg'] = 'Cargando la información encontrada';
	}else{
		$data['msg'] = 'No se encontraron datos';
	}
	// obtencion del perfil del usuario
	$data['profile'] = getPerfil($db);
	$data['noPermitidos'] = $noPermitidos;
	$data['lista'] = getAnexosList($db, $info);
	return $data;
}

function getBotones($db)
{
	$data = array('success'=>false, 'msg'=>'Ocurio un incidente inesperado al momento de consultar la información');
	$info = $_POST;
	$sql = "SELECT distinct tbs.functionid, tbs.statusid, tbs.statusname, tbs.namebutton, tbs.functionid,
			tbs.adecuacionPresupuestal, tbs.clases, tbs.sn_estatus_siguiente, tbs.sn_mensaje_opcional2
		FROM sec_profilexuser as sp
		INNER JOIN sec_funxprofile as sf
			ON sf.profileid = sp.profileid
		INNER JOIN tb_botones_status as tbs
			ON tbs.functionid = sf.functionid
		WHERE sp.`userid` = '%s'
		AND tbs.sn_funcion_id = %d";
	$sql = sprintf($sql, $_SESSION['UserID'], $info['fn']);
	$data['sql'] = $sql;
	$result = DB_query($sql, $db);
	$rows = [];
	while ($rs = DB_fetch_array($result)) {
		$rows[] = [
			'statusid' => $rs['sn_estatus_siguiente'],
            'statusname' => $rs['statusname'],
            'namebutton' => $rs['namebutton'],
            'functionid' => $rs['functionid'],
            'name' => $rs['sn_mensaje_opcional2'],
            'clases' => $rs['clases']
		];
	}
	if(count($rows)){
		$data['success']=true;
		$data['msg']='se cargaron con exito los datos';
		$data['content']=$rows;
	}
	return $data;
}

function getStatus($db)
{
	$data = array('success'=>false, 'msg'=>'No se encontraron datos');
	$functionid = !empty($_POST['functionid'])?$_POST['functionid']:FUNCTIONID;
	$sql = 'SELECT DISTINCT statusname, namebutton, sn_orden FROM tb_botones_status WHERE sn_funcion_id = '.$functionid.' 
		AND sn_captura_requisicion = 0 AND functionid = 0 ORDER BY sn_orden';
	// $data['sql'] = $sql;
	$result = DB_query($sql, $db);
	if(DB_num_rows($result)){
		$rows = array(['label'=>'Seleccione una opción', 'title'=> 'Seleccione una opción', 'value'=>'']);
		while ($rs = DB_fetch_assoc($result)) {
			$rows[$rs['statusname']] = ['label'=>utf8_encode($rs['namebutton']), 'title'=>utf8_encode($rs['namebutton']), 'value'=>$rs['statusname']];
		}
		$data['content'] = $rows;
		$data['success'] = true;
	}
	return $data;
}

function getPerfil($db)
{
	$userid = $_SESSION['UserID'];
	$sql = "SELECT profileid FROM sec_profilexuser WHERE userid = '$userid'";
	$resl = DB_query($sql, $db);
	$p = DB_fetch_assoc($resl);
	return $p['profileid'];
}

function getNoPermitidos($db){
	$data = [ 3, 5 ];
	$perfil = getPerfil($db);
	// [2, 3, 5, 4, 6, 7]
	if( Havepermission($_SESSION['UserID'], 2329, $db ) && !Havepermission($_SESSION['UserID'], 2328, $db ) ){
		$data = array_merge($data,[2, 4, 6, 7]);
	}
	// [2, 3, 5, 7]	
	if( Havepermission($_SESSION['UserID'], 2328, $db ) && !Havepermission($_SESSION['UserID'], 2330, $db ) ){
		$data = array_merge($data,[2, 7]);
	}
	// [3, 5, 2, 6, 7]
	if(Havepermission($_SESSION['UserID'], 2330, $db )){
		$data = array_merge($data,[2, 6]);
	}
	return $data;
}

function updateStatus($db)
{
	$data = array('success'=>false, 'msg'=>'Ocurrio un incidente al momento de actualizar la información');
	$info = $_POST;
	$rows = $info['rows'];
	$leng = count($rows);
	$flag = 0;
	unsetFromArray($info,['method','rows']);
	if($leng){
		DB_Txn_Begin($db);
		try {
			$enc = new Encryption;
			$newLinks = [];
			$newStatus = [];
			$noPermitidos = getNoPermitidos($db);
			foreach ($rows as $k => $rw) {
				$folio = $rw['iFolio'];
				$tipoStatus = $info['type'];
				$where = "WHERE nu_type = 51 AND nu_anexo = '%s' AND nu_tagref='%s'";
				$where = sprintf($where, $folio, $rw['nUR']);
				$sql = sprintf("SELECT * FROM tb_cnfg_anexo_tecnico %s", $where);
				$result = DB_query($sql, $db);
				$numdat = DB_num_rows($result);
				if ($numdat) {
					$upSql = sprintf("UPDATE tb_cnfg_anexo_tecnico SET ind_status = %d %s", $tipoStatus, $where);
					$rs = DB_query($upSql, $db);
					if($rs!=true){
						$flag++;
						$data['msg'] = 'No se realizar la modificación para el folio '.$folio;
						DB_Txn_Rollback($db);
						break;
					}else{
						$url = '&folio=>'.$folio.(in_array($tipoStatus, $noPermitidos)? '&status=>'.$tipoStatus : '');
						$newLinks[$folio] = '<a href="anexoTecnicoDetalle.php?URL='.($enc->encode($url)).'" style="color: blue;"><u>'.$folio.'</u></a>';
						$newStatus[$folio] = getEstatusById($tipoStatus, $db);
					}
				}else{
					$flag++;
					$data['msg'] = 'No se encontro la información solicitada para el folio #'.$folio;
					DB_Txn_Rollback($db);
					break;
				}
			}
			if(!$flag){
				$data = [
					'success'=>true,
					'msg'=>'Se realizaron las modificaciones solictadas',
					'links'=>$newLinks,
					'nuevoEstatus' => $newStatus
				];
				DB_Txn_Commit($db);
			}
		} catch (Exception $e) {
			$data['msg'] .= '<br>'.$e->getMessage();
			DB_Txn_Rollback($db);
		}
	}
	else{ $data['msg'] = 'La información proporcionada no es sufuciente para realizar el cambio'; }
	$data['info'] = $info;
	return $data;
}

function updateStatusGeneral($db)
{
	$data = ['success'=>false,'msg'=>'Ocurrió un incidente inesperado. Favor de contactar con el administrador'];
	$rows = $_POST['rows'];
	$type = $_POST['type'];
	if(!empty($rows)){
		DB_Txn_Begin($db);
		try {
			$flag = 0;
			$enc = new Encryption;
			foreach ($rows as $key => $linea) {
				$folio = $linea['iFolio'];
				# consulta del siguente status
				$nexStatus = getNexStatus($linea, $type, $db);
				# generacion de query
				$where = "WHERE nu_type = 51 AND nu_anexo = '%s' AND nu_tagref='%s'";
				$where = sprintf($where, $folio, $linea['nUR']);
				$sql = sprintf("SELECT * FROM tb_cnfg_anexo_tecnico %s", $where);
				$result = DB_query($sql, $db);
				$numdat = DB_num_rows($result);
				if ($numdat && !empty($nexStatus[0])) {
					$upSql = sprintf("UPDATE tb_cnfg_anexo_tecnico SET ind_status = %d %s", $nexStatus[0], $where);
					$rs = DB_query($upSql, $db);
					if($rs!=true){
						$flag++;
						$data['msg'] = 'No se realizar la modificación para el folio #'.$folio;
						DB_Txn_Rollback($db);
						break;
					}else{
						$data[$folio] = $nexStatus;
						$url = '&folio=>'.$folio.'&status=>'.$nexStatus[0];
						$newLinks[$folio] = '<a href="anexoTecnicoDetalle.php?URL='.($enc->encode($url)).'" style="color: blue;"><u>'.$folio.'</u></a>';
					}
				}else if(!empty($nexStatus[0])){
					$flag++;
					$data['msg'] = 'No se encontro la información solicitada para el folio #'.$linea['folio'];
					DB_Txn_Rollback($db);
					break;
				}
			}// fin foreach
			if(!$flag){
				$data['success'] = true;
				$data['msg'] = 'Se realizaron las modificaciones solictadas';
				$data['links'] = $newLinks;
				DB_Txn_Commit($db);
			}
		} catch (Exception $e) {
			$data['msg'] .= '<br>'.$e->getMessage();
			DB_Txn_Rollback($db);
		}
	}
	else{ $data['msg'] = 'No cuenta con los datos necesarios para la operación'; }
	return $data;
}

function processFromExiting($db)
{
	$data = ['success'=>false, 'msg'=>'Ocurrió un incidente al momento de genrar la información.'];
	$info = $_POST;
	$sql = "SELECT * FROM `tb_cnfg_anexo_tecnico` WHERE `ind_status` = '3' AND  `nu_anexo` = '".$info['anexoTecnicoPartida']."' ";
	$result = DB_query($sql, $db);
	if(DB_num_rows($result) == 0){
		$data['msg'] = "No se encontraron datos del anexo técnico seleccionado.";
		return $data;
	}
	DB_Txn_Begin($db);
	try {
		$transNo = GetNextTransNo(TYPEMOV,$db);
		$datosCreacion = "Generación de anexo técnico a partir de anexo anterior con folio: ".$info['anexoTecnicoPartida']." usuario alta:" . $_SESSION['UserID'];
		$datosCreacion = utf8_decode($datosCreacion);
		$antecedentes = utf8_decode("EL presente anexo técnico se genero a partir del anexo número ".$info['anexoTecnicoPartida']);
		$sqlProcess = "INSERT INTO `tb_cnfg_anexo_tecnico`( `nu_anexo`, `nu_tagref`, `nu_type`, `nu_proceso`, `nu_partida`, `txt_informacion_creacion`,
			`txt_bien_serevicio`, `txt_desc_bien_serevicio`, `nu_cantidad`, `nu_ue`, `nu_garantia`, `amt_costo`, `amt_total`, `ind_status`, `txt_descripcion_antecedentes`
			)
			SELECT '$transNo', `nu_tagref`, `nu_type`, `nu_proceso`, `nu_partida`, '$datosCreacion', `txt_bien_serevicio`, `txt_desc_bien_serevicio`,
			`nu_cantidad`, `nu_ue`, `nu_garantia`, `amt_costo`, `amt_total`, '1', '$antecedentes'
			FROM `tb_cnfg_anexo_tecnico` WHERE `nu_anexo` = '".$info['anexoTecnicoPartida']."' ";
		$resultProcess = DB_query($sqlProcess, $db);
		if($resultProcess == true){
			DB_Txn_Commit($db);
			$enc = new Encryption();
			$url = $enc->encode('&folio=>'.$transNo);
			$data['success'] = true;
			$data['msg'] = 'Se genero con éxito el anexo técnico con folio <a href="anexoTecnicoDetalle.php?URL='.$url.'" style="color: blue;">'.$transNo.'</a>';
		}else{
			DB_Txn_Rollback($db);
		}
	} catch (Exception $e) {
		$data['msg'] .= '<br>'.$e->getMessage();
		DB_Txn_Rollback($db);
	}

	return $data;
}

/*********************** FUNCIONES VARIAS ***********************/

/**
 * Función para la generacion del where según las nececidades
 * enviadas en el metodo post.
 * @param  DBInstance $db   Instancia de conxión
 * @param  array 	  $data Datos para generar la cadena de where
 * @return string     Cadena resultante del las comparaciones y conbinaciones posibles
 */
function getWhere($db, $data){
	$where = '';
	$flag = 1;
	# se agrega filtro para los estatus de requisicion
	$where .= " ind_status NOT IN(10) ";

	if(!empty($data['dateDesde']) && empty($data['dateHasta'])){
		$fecha = comvertDate($data['dateDesde']);
		$where .= ($flag==0? '' : ' AND ') . " ant.dt_fecha_creacion >= '".$fecha." 00:00:00' ";
		$flag++;
	}
	else if(empty($data['dateDesde']) && !empty($data['dateHasta'])){
		$fecha = comvertDate($data['dateHasta']);
		$where .= ($flag==0? '' : ' AND ') . " ant.dt_fecha_creacion <= '".$fecha." 23:59:59' ";
		$flag++;
	}
	else if(!empty($data['dateDesde']) && !empty($data['dateHasta'])){
		$fecha1 = comvertDate($data['dateDesde']);
		$fecha2 = comvertDate($data['dateHasta']);
		$where .= ($flag==0? '' : ' AND ')
				." ant.dt_fecha_creacion BETWEEN '".$fecha1." 00:00:00' AND '".$fecha2." 23:59:59'";
				// ." OR ant.dt_fecha_creacion LIKE '".$data['dateHasta']."%' ";
		$flag++;
	}

	if(!empty($data['numeroFolio'])){
		$where .= ($flag==0? '' : ' AND ') . " ant.nu_anexo = '".$data['numeroFolio']."' ";
		$flag++;
	}

	if(!empty($data['selectEstatusGeneral'])){
		$where .= ($flag==0? '' : ' AND ') . " ant.ind_status = '".$data['selectEstatusGeneral']."' ";
		$flag++;
	}

	// como no existe el campop en la tabla si solo se selecciona este dato se buscan todas la ur de la razon social
	if(!empty($data['selectRazonSocial']) && (empty($data['selectUnidadNegocio']) || $data['selectUnidadNegocio']==-1)){
		$sql = "SELECT t.tagref FROM tags as t where t.legalid = '".$data['selectRazonSocial']."';";
		$res = DB_query($sql,$db);
		$result = DB_fetch_array($res);
		$in = '';
		foreach ($result as $key => $value) { $in.= ($key!=0?',':'') ." '$value'"; }
		$where .= ($flag==0? '' : ' AND ') . " ant.nu_tagref in($in) ";
		$flag++;
	}

	if(!empty($data['selectUnidadNegocio']) && $data['selectUnidadNegocio']!=-1){
		$where .= ($flag==0? '' : ' AND ') . " ant.nu_tagref = '".$data['selectUnidadNegocio']."' ";
		$flag++;
	}

	if(!empty($data['selectUnidadEjecutora']) && $data['selectUnidadEjecutora']!=-1){
		$where .= ($flag==0? '' : ' AND ') . " ant.nu_ue = '".$data['selectUnidadEjecutora']."' ";
		$flag++;
	}
	
	return $where;
}

/**
 * Funcion para eliminar campos de un array 
 * @param  array &$data   Arreglo que se quitara la informacion (pasado por referencia) 
 * @param  array $toUnset Arreglo con datos que se ban a eliminar del arreglo principal
 * @return array $old     Datos que se rescatan de lo que se elimino del arreglo principal
 */
function unsetFromArray(&$data, $toUnset)
{
	$old = array();
	foreach ($toUnset as $key) {
		$old[$key] = $data[$key];
		unset($data[$key]);
	}
	return $old;
}

function comvertDate($fecha)
{
	$exp = explode('-', $fecha);
	return "$exp[2]-$exp[1]-$exp[0]";
}

function getNexStatus($linea, $type, $db)
{
	$actual = $linea['status'];
	$campo = $type==1? 'sn_estatus_siguiente' : 'sn_estatus_anterior';
	$sql = "SELECT $campo FROM `tb_botones_status` WHERE `functionid` = 0 AND `sn_funcion_id` = '".FUNCTIONID."' AND `statusname` = $actual";
	$result = DB_query($sql, $db);
	$nextStatus = DB_fetch_assoc($result);
	$intStatus = $nextStatus[$campo];
	# consulta del status en letra
	$sqlS = "SELECT `namebutton` FROM `tb_botones_status` WHERE `functionid` = 0 AND `sn_funcion_id` = '".FUNCTIONID."' AND `statusname` = $intStatus";
	$resultS = DB_query($sqlS, $db);
	$stringStatusTemp = DB_fetch_assoc($resultS);
	$stringStatus = $stringStatusTemp['namebutton'];
	return [$intStatus,$stringStatus];
}

function getEstatusById($id, $db)
{
	$sql = "SELECT `statusname` as id, `namebutton` as name FROM `tb_botones_status` WHERE `sn_funcion_id` = ".FUNCTIONID." AND `statusname` = '$id' LIMIT 1;";
	$result = DB_query($sql, $db);
	$resTemp = DB_fetch_assoc($result);
	return [$resTemp['id'], $resTemp['name']];
}

function getAnexosList($db, $datos)
{
	$data = array(
		[
			'label'=>'Seleccione una opción',
			'value'=>''
		]
	);;
	$sql = "SELECT DISTINCT ant.nu_anexo as folio, ant.txt_descripcion_antecedentes as descripcion 
		FROM tb_cnfg_anexo_tecnico as ant WHERE ant.ind_status = 3 AND `nu_tagref` = '".$datos['selectUnidadNegocio']."' ";
	$result = DB_query($sql, $db);
	while ($rs = DB_fetch_array($result)) {
		$data[] = [
			'label'=>$rs['folio'].'-'.utf8_encode($rs['descripcion']),
			'value'=>$rs['folio']
		];
	}
	return $data;
}

/*********************************************** DESARROLLO DE FUNCIONES ***********************************************/

/* EJECUCION DE FUNCIONES */
$data = call_user_func_array($_POST['method'],[$db]);
/* MODIFICACION DE HEADER */
header('Content-type:application/json;charset=utf-8');
/* ENVIO DE INFORMACIÓN */
echo json_encode($data);