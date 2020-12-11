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
//
/* DECLARACIÓN DE VARIABLES */
$PageSecurity = 11;
$PathPrefix = '../';
$funcion = 2455;

/* INCLUSIÓN DE ARCHIVOS NECESARIOS */
include($PathPrefix . 'config.php');
include($PathPrefix . 'includes/SecurityUrl.php');
include($PathPrefix . 'includes/ConnectDB.inc');
include($PathPrefix . 'includes/SecurityFunctions.inc');
include($PathPrefix . 'includes/SQL_CommonFunctions.inc');

/*********************************************** DESARROLLO DE FUNCIONES ***********************************************/

function listaDeDocumentos($db){
	$data = array('success'=>false, 'msg'=>'Ocurrió un incidente inesperado al momento de consultar la información.', 'noPermitidos' => getNoPermitidos($db), 'listaDeDocumentos' => getPerfil($db));
	$info = $_POST;
	$info['fechaIni'] = date_format(date_create_from_format('d-m-Y', $info['fechaIni']),'Y-m-d');
	$info['fechaFin'] = date_format(date_create_from_format('d-m-Y', $info['fechaFin']),'Y-m-d');

	$where = array();
	$where[] = "c.`ind_activo` = '1'";

	if(!empty($info['selectUnidadNegocio'])&&$info['selectUnidadNegocio']!="-1"){
		$where[] = "c.`tagref` LIKE '$info[selectUnidadNegocio]'";
	}
	if(!empty($info['selectUnidadEjecutora'])&&$info['selectUnidadEjecutora']!="-1"){
		$where[] = "c.`id_nu_ue` LIKE '$info[selectUnidadEjecutora]'";
	}
	if(!empty($info['selectEstatus'])&&$info['selectEstatus']!="-1"){
		$where[] = "c.`id_nu_estatus` = '$info[selectEstatus]'";
	}
	if(empty($info['folio'])==false){
		$where[] = "c.`sn_folio_compra` LIKE '$info[folio]'";
	}
	if(!empty($info['tipoExpediente'])&&$info['tipoExpediente']!="-1"){
		$where[] = "c.`id_nu_tipo_expediente` = '$info[tipoExpediente]'";
	}
	if(!empty($info['fechaIni']) && !empty($info['fechaFin'])){
		$where[] = "c.`dtm_fecha_creacion` >= '$info[fechaIni] 00:00:00' AND `dtm_fecha_creacion` <= '$info[fechaFin] 23:59:59' ";
	}
	if(!empty($info['fechaIni']) && empty($info['fechaFin'])){
		$where[] = "c.`dtm_fecha_creacion` >= '$info[fechaIni] 00:00:00' ";
	}
	if(empty($info['fechaIni']) && !empty($info['fechaFin'])){
		$where[] = "c.`dtm_fecha_creacion` <= '$info[fechaFin] 23:59:59' ";
	}
	if(!empty($info['codigoExpediente'])){
		$where[] = "c.`ln_codigo_expediente` LIKE '%$info[codigoExpediente]%' ";
	}

	$where = ( count($where) ? "WHERE ".implode(" AND ", $where) : "" );

	$sql = "SELECT DISTINCT
			c.`id_nu_compra` AS `id`,
			cur.`desc_ur` AS `ur`,
			cue.`desc_ue` AS `ue`,
			DATE_FORMAT(c.`dtm_fecha_creacion`,'%d-%m-%Y') AS `fechaCaptura`,
			c.`requisitionno` AS `folioRequisicion`,
			c.`sn_folio_compra` AS `folio`,
			DATE_FORMAT(c.`dtm_fecha_convocatoria`,'%d-%m-%Y') AS `fechaConvocatoria`,
			DATE_FORMAT(c.`dtm_fecha_contrato_firma`,'%d-%m-%Y') AS `fechaFirma`,
			ce.`ln_nombre_descripcion` AS `tipoExpediente`,
			c.`id_nu_estatus` AS `idStatus`,
			bs.`namebutton` AS `estatus`,
			c.`txt_descripcion_expediente` AS `descripcion`,
			c.`sn_monto_compra` AS `montoTotal`

			FROM `tb_proceso_compra` AS c
			LEFT JOIN `tb_cat_unidades_responsables` AS cur ON cur.`ur` = c.`tagref`
			LEFT JOIN `tb_cat_unidades_ejecutoras` AS cue ON cue.`ur` = c.`tagref` AND cue.`ue` = c.`id_nu_ue`
			LEFT JOIN `tb_cat_tipo_compra_expediente` AS ce ON ce.`id_nu_tipo_compra_expediente` = c.`id_nu_tipo_expediente`
			LEFT JOIN `tb_botones_status` AS bs ON bs.`statusid` = c.`id_nu_estatus` AND bs.`sn_funcion_id` = '2455' AND bs.`functionid` = 0

			$where

			ORDER BY c.`id_nu_compra` DESC";

	$result = DB_query($sql, $db, '');
	if (DB_num_rows($result)!=0) {
		$rows = array();
		while ($row = mysqli_fetch_object($result)) {
			$enc = new Encryption;
			$liga= "URL=".$enc->encode("&idCompra=>".$row->id);

			$rows[] = [
				'ur'=> utf8_encode($row->ur),
				'ue' => utf8_encode($row->ue),
				'fechaCaptura' => $row->fechaCaptura,
				'folioRequisicion' => $row->folioRequisicion,
				'folio' => "<a target='_self' href='./oficioCompra.php?$liga' style='color: blue; '><u>$row->folio</u></a>\n",
				'folioTexto' => $row->folio,
				'fechaConvocatoria' => $row->fechaConvocatoria,
				'fechaFirma' => $row->fechaFirma,
				'tipoExpediente' => utf8_encode($row->tipoExpediente),
				'idStatus' => $row->idStatus,
				'estatus' => utf8_encode($row->estatus),
				'descripcion' => utf8_encode($row->descripcion),
				'imprimir' => '<span class="modificar glyphicon glyphicon glyphicon-print"></span>',
				'montoTotal' => $row->montoTotal,
				'identificador' => $row->id
			];
		}

		$data['success']=true;
		$data['msg']='Solicitud ejecutada con exito';
		$data['content'] = $rows;
	}

	return $data;
}

function updateStatus($db){
	$data = array('success'=>false, 'msg'=>'Ocurrió un incidente al momento de actualizar la información.');
	$info = $_POST;
	$rows = $info['rows'];
	$leng = count($rows);
	$flag = 0;
	$info['comprometido'] = empty($info['comprometido'])?0:$info['comprometido'];
	if($leng){
			$noPermitidos = getNoPermitidos($db);
			foreach ($rows as $k => $rw) {
				$identificador = $rw['identificador'];
				$tipoStatus = $info['type'];
				$where = "WHERE pc.`id_nu_compra` = '$identificador'";
				$sql = sprintf("SELECT pc.* FROM `tb_proceso_compra` AS pc %s", $where);
				$result = DB_query($sql, $db);
				$fetchTempSol = DB_fetch_array($result);
				$numdat = DB_num_rows($result);

				# Validación adicional que evita que más de una persona mande el mismo oficio a validar.
				if($fetchTempSol['id_nu_estatus']==$tipoStatus){
						$data['msg'] .= "<br>No se realizará la modificación para el folio <strong>$fetchTempSol[sn_folio_compra]</strong>, debido a que ya fue ".( $tipoStatus==4 ? "autorizado" : "avanzado" )." previamente. Actualice su navegador.";
						$flag++;
						continue;
				}

				if( ($fetchTempSol['id_nu_estatus']==1&&$tipoStatus==2)||(($fetchTempSol['id_nu_estatus']==1||$fetchTempSol['id_nu_estatus']==2)&&$tipoStatus==3) ){
					$sql = "SELECT *

							FROM `tb_proceso_compra_documentos` AS cd
							LEFT JOIN `tb_cat_tipo_compra_anexo` AS ca ON ca.`id_nu_tipo_compra_anexo` = cd.`ind_tipo_anexo`

							WHERE `id_nu_compra` = '$identificador'
							AND ca.`id_nu_tipo_carpeta` = '$fetchTempSol[id_nu_estatus]'";
					// Se comenta validación, no debe ser abligatorio agregar documentos
					// if(!DB_num_rows(DB_query($sql, $db))){
					// 	$data['msg'] .= "<br>El folio <strong>$fetchTempSol[sn_folio_compra]</strong> no puede cambiarse al estatus solicitado debido a que necesita tener al menos un documento en la carpeta \"".( $tipoStatus==2 ? "Administrativos" : ( $tipoStatus==3 ? "Seguimiento" : "" ) )."\".";
					// 	$flag++;
					// 	continue;
					// }
				}

				if($tipoStatus==3){
					$sql = "SELECT *

							FROM `tb_proceso_compra`

							WHERE `id_nu_compra` = '$identificador'
							AND `supplierid` IS NOT NULL
							AND `supplierid` <> ''
							AND `suppname` IS NOT NULL
							AND `suppname` <> ''
							AND `sn_contrato` IS NOT NULL
							AND `sn_contrato` <> ''
							AND `dtm_fecha_contrato_inicio` IS NOT NULL
							AND `dtm_fecha_contrato_inicio` <> ''
							AND `dtm_fecha_contrato_fin` IS NOT NULL
							AND `dtm_fecha_contrato_fin` <> ''
							AND `dtm_fecha_contrato_firma` IS NOT NULL
							AND `dtm_fecha_contrato_firma` <> ''
							AND `sn_monto_compra` IS NOT NULL
							AND `sn_monto_compra` <> ''
							AND `ind_periodo_contrato` IS NOT NULL
							AND `ind_periodo_contrato` <> ''";
	
					$eligibilidad = DB_num_rows(DB_query($sql, $db));
					if(!$eligibilidad){
						$data['msg'] .= "<br>El folio <strong>$fetchTempSol[sn_folio_compra]</strong> no puede cambiarse a concluido debido a que falta información en la sección <b>Proceso de Contratación</b>.";
						$flag++;
						continue;
					}
				}

				# cambio de estatus
				if ($numdat) {
					$upSql = sprintf("UPDATE `tb_proceso_compra` AS pc SET pc.`id_nu_estatus` = '%d' %s", $tipoStatus, $where);
					$rs = DB_query($upSql, $db);
					if($rs!=true){
						$flag++;
						$data['msg'] .= "<br>No se realizará la modificación para el folio <strong>$fetchTempSol[sn_folio_compra]</strong>";
						continue;
					}else{
						$data['msg'] .= "<br>Se realizará la modificación solicitada para el folio <strong>$fetchTempSol[sn_folio_compra]</strong>.";

						if($tipoStatus==3){
							$upSql = "UPDATE `purchorders` SET `supplierno`= '$fetchTempSol[supplierid]', `status` = 'Autorizado', `fecha_modificacion` = current_timestamp() WHERE `orderno` = '$fetchTempSol[orderno]'";
							DB_query($upSql, $db);
						}
					}
				}else{
					$flag++;
					$data['msg'] .= "No se encontró la información solicitada para el folio <strong>$fetchTempSol[sn_folio_compra]</strong>";
					continue;
				}
			}
			if(!$flag){
				$data['success'] = true;
				////$data['msg']='Se realizaron las modificaciones solicitadas';
			}
	}else{
		$data['msg'] = 'La información proporcionada no es sufuciente para realizar el cambio';
	}
	// $data['info'] = $info;

	return $data;
}

function reversar($db){
	$data = array('success'=>false, 'msg'=>'Ocurrió un incidente al momento de actualizar la información.');
	$info = $_POST;
	$rows = $info['rows'];
	$leng = count($rows);
	$flag = 0;
	$sqls = "";
	if($leng){
		$noPermitidos = [3,4];
		foreach ($rows as $k => $rw) {
			$identificador = $rw['identificador'];

			$where = "WHERE pc.`id_nu_compra` = '$identificador'";
			$sql = sprintf("SELECT pc.* FROM `tb_proceso_compra` AS pc %s", $where);
			$result = DB_query($sql, $db);
			$fetchTempSol = DB_fetch_array($result);
			$sqls .= "$sql<br>\n";

			if(!in_array($fetchTempSol['id_nu_estatus'], $noPermitidos)){
				$datosClave = array();
				$datosClave['requisitionno'] = $rw['folioRequisicion'];

				$sql = "UPDATE `tb_proceso_compra` SET `ind_activo` = '0' WHERE `id_nu_compra` = '$identificador'";
				DB_query($sql, $db);
			$sqls .= "$sql<br>\n";

				// Obtener orderno original (Inicial)
				$SQL = "SELECT MIN(`purchorders`.`orderno`) AS `ordernoOriginal` 
						FROM `purchorders` 
						WHERE `purchorders`.`requisitionno` = '$datosClave[requisitionno]'";

				$datosClave['orderno'] = DB_fetch_array(DB_query($SQL, $db))['ordernoOriginal'];
			$sqls .= "$SQL<br>\n";

				// Agregar detalle al orderno original
				$SQL = "UPDATE `purchorderdetails`
						JOIN `purchorders` ON `purchorders`.`orderno` = `purchorderdetails`.`orderno`
						SET `purchorderdetails`.`orderno` = '$datosClave[orderno]'
						WHERE `purchorders`.`requisitionno` = '$datosClave[requisitionno]'";
				$TransResult = DB_query($SQL, $db, $ErrMsg);
			$sqls .= "$SQL<br>\n";

				// Actualizar orden de los bienes y servicios
				$SQL = "UPDATE `purchorderdetails`
						SET `purchorderdetails`.`orderlineno_` = `purchorderdetails`.`nu_original`
						WHERE `purchorderdetails`.`status` = '2'
						AND `purchorderdetails`.`orderno` = '$datosClave[orderno]'";
				$TransResult = DB_query($SQL, $db, $ErrMsg);
			$sqls .= "$SQL<br>\n";

				$SQL = "SELECT `tb_suficiencias`.`nu_type`, `tb_suficiencias`.`nu_transno`
						FROM `tb_suficiencias` 
						WHERE `tb_suficiencias`.`nu_estatus` <> '0' AND `tb_suficiencias`.`sn_orderno` = '$datosClave[orderno]'";
				$ErrMsg = "No se obtuvieron los registros del Orden $datosClave[orderno]";
				$TransResult = DB_query($SQL, $db, $ErrMsg);
			$sqls .= "$SQL<br>\n";

				if ($myrow = DB_fetch_array($TransResult)) {
					$agrego = fnInsertPresupuestoLogMovContrarios($db, $myrow['nu_type'], $myrow['nu_transno']);
					if ($agrego) {
						$data['msg'] .= '<p><i class="glyphicon glyphicon-remove-sign text-success" aria-hidden="true"></i> La Requisición '.$datosClave['requisitionno'].' ha sido reversada </p>';

						// Cancelar Suficiencia y precomprometido
						$SQL = "UPDATE `tb_suficiencias` SET `tb_suficiencias`.`nu_estatus` = '0', `tb_suficiencias`.`sn_description` = CONCAT(`tb_suficiencias`.`sn_description`, '. Reversada')
								WHERE `tb_suficiencias`.`nu_type` = '$myrow[nu_type]' AND `tb_suficiencias`.`nu_transno` = '$myrow[nu_transno]'";
						$TransResult2 = DB_query($SQL, $db, $ErrMsg);
			$sqls .= "$SQL<br>\n";
						$SQL = "UPDATE `chartdetailsbudgetlog` SET `estatus` = '0' 
								WHERE `type` = '$myrow[nu_type]' AND `transno` = '$myrow[nu_transno]'";
						$TransResult2 = DB_query($SQL, $db, $ErrMsg);
			$sqls .= "$SQL<br>\n";

						// Actualizar requisición
						$SQL = "UPDATE `purchorders` SET `status` = 'PorAutorizar', `supplierno` = '111111', `comments` = substr(`purchorders`.`comments`, 1, LOCATE('NOTA: ', `purchorders`.`comments`, 1) - 1)
								WHERE `orderno` = '$datosClave[orderno]'";
						$TransResult2 = DB_query($SQL, $db, $ErrMsg);
			$sqls .= "$SQL<br>\n";

						// Actualizar detalle, sumar lo de la solicitud al almacén
						$SQL = "UPDATE `purchorderdetails`
								LEFT JOIN `tb_solicitudes_almacen_detalle` ON `tb_solicitudes_almacen_detalle`.`ln_ontransit` = `purchorderdetails`.`podetailitem` AND `tb_solicitudes_almacen_detalle`.`ln_arctivo` = '1'
								SET `purchorderdetails`.`quantityord` = `purchorderdetails`.`quantityord` + CASE WHEN `tb_solicitudes_almacen_detalle`.`nu_cantidad` IS NULL THEN 0 ELSE `tb_solicitudes_almacen_detalle`.`nu_cantidad` END
								WHERE `purchorderdetails`.`orderno` = '$datosClave[orderno]'
								AND `purchorderdetails`.`status` = '2'";
						$TransResult2 = DB_query($SQL, $db, $ErrMsg);
			$sqls .= "$SQL<br>\n";

						// Actualizar no existencia
						$SQL = "UPDATE `tb_no_existencias` SET `status` = '1' 
								WHERE `nu_id_requisicion` = '$datosClave[requisitionno]'";
						$TransResult2 = DB_query($SQL, $db, $ErrMsg);
			$sqls .= "$SQL<br>\n";

						// Actualizar no existencia
						$SQL = "UPDATE `tb_solicitudes_almacen` SET `estatus`='65', `ln_nombre_estatus`='Por Autorizar' 
								WHERE `nu_id_requisicion` = '$datosClave[orderno]'";
						$TransResult2 = DB_query($SQL, $db, $ErrMsg4);
			$sqls .= "$SQL<br>\n";
					} else {
						$result = false;
						$data['msg'] .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> La Requisición '.$datosClave['requisitionno'].' no pudo ser reversada </p>';
					}
				}
			}else{
				$flag++;
				$data['msg'] .= "<br>No se realizará la modificación para el folio <strong>$fetchTempSol[sn_folio_compra]</strong>";
				continue;
			}
		}
		$data['success'] = true;
	}else{
		$data['msg'] = 'La información proporcionada no es sufuciente para realizar el cambio';
	}

	$data['sqls'] = $sqls;

	return $data;
}

/*********************************************** DESARROLLO DE FUNCIONES ***********************************************/

/* EJECUCIÓN DE FUNCIONES */
try{
	$data = call_user_func_array($_POST['method'],[$db]);
}
catch(Exception $e){
	$data = array('success'=>false, 'msg'=>'Ocurrió un incidente inesperado al momento de consultar la información.');
}
/* MODIFICACIÓN DE HEADER */
header('Content-type:application/json;charset=utf-8');
/* ENVÍO DE INFORMACIÓN */
echo json_encode($data);

/*********************************************** FUNCIONES ÚTILES ***********************************************/

function getNoPermitidos($db){
	$data = [4];
	switch(getPerfil($db)){
		case 9:
			$data = array_merge($data,[2,3]);
			break;
		case 10:
			$data = array_merge($data,[3]);
			break;
	}
	return $data;
}

function getPerfil($db){
	$sql = "SELECT `profileid`

			FROM `sec_profilexuser`

			WHERE `userid` = '$_SESSION[UserID]'";

	return DB_fetch_assoc(DB_query($sql, $db))['profileid'];
}
