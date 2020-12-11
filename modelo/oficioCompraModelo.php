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

/*********************************************** CONSTANTES ***********************************************/
define('DECPLACE', !empty($_SESSION['DecimalPlaces'])? $_SESSION['DecimalPlaces'] : 2);
define('LNTBLCAT', 'tb_proceso_compra');
define('ACTIVECOL', 'ind_activo');
define('DATA', [
		'selectUnidadNegocio' => ['col' => 'tagref', 'tipo' => 'string'],
		'selectUnidadEjecutora' => ['col' => 'id_nu_ue', 'tipo' => 'string'],
		'partidaEspecifica' => ['col' => 'ln_partida_especifica', 'tipo' => 'string'],
		'codigoExpediente' => ['col' => 'ln_codigo_expediente', 'tipo' => 'string'],
		'descripcionExpediente' => ['col' => 'txt_descripcion_expediente', 'tipo' => 'string'],
		'tipoExpediente' => ['col' => 'id_nu_tipo_expediente', 'tipo' => 'integer'],
		'referenciaExpediente' => ['col' => 'txt_referencia_expediente', 'tipo' => 'string'],
		'tipoContratacion' => ['col' => 'id_nu_tipo_contratacion', 'tipo' => 'integer'],
		'fechaConvocatoria' => ['col' => 'dtm_fecha_convocatoria', 'tipo' => 'string'],
		'fechaInicio' => ['col' => 'dtm_fecha_inicio', 'tipo' => 'string'],
		'fechaEstimada' => ['col' => 'dtm_fecha_estimada', 'tipo' => 'string'],
		'proveedor' => ['col' => 'supplierid', 'tipo' => 'string'],
		'textoOculto__proveedor' => ['col' => 'suppname', 'tipo' => 'string'],
		'RFC' => ['col' => 'taxid', 'tipo' => 'string'],
		'representanteLegal' => ['col' => 'ln_representante_legal', 'tipo' => 'string'],
		'contratoConvenio' => ['col' => 'sn_contrato', 'tipo' => 'string'],
		'fechaIni' => ['col' => 'dtm_fecha_contrato_inicio', 'tipo' => 'string'],
		'fechaFin' => ['col' => 'dtm_fecha_contrato_fin', 'tipo' => 'string'],
		'fechaFirma' => ['col' => 'dtm_fecha_contrato_firma', 'tipo' => 'string'],
		'montoCompra' => ['col' => 'sn_monto_compra', 'tipo' => 'string'],
		'periodoContrato' => ['col' => 'ind_periodo_contrato', 'tipo' => 'integer'],
		'contratoDuracion' => ['col' => 'sn_contrato_duracion', 'tipo' => 'string'],
		'contratoDuracionUnidad' => ['col' => 'id_nu_contrato_duracion_unidad', 'tipo' => 'integer'],
		'observaciones' => ['col' => 'ln_observaciones', 'tipo' => 'string'],

		'folioRequisicion'=>['col'=>'requisitionno','tipo'=>'string'],
		'folioCompra'=>['col'=>'sn_folio_compra','tipo'=>'string'],
		'estatusCompra'=>['col'=>'id_nu_estatus','tipo'=>'string'],
		'identificador'=>['col'=>'id_nu_compra','tipo'=>'string']
]);
define('SINUPDATE', [
		'selectUnidadNegocio' => true,
		'selectUnidadEjecutora' => true,
		'partidaEspecifica' => true,
		'folioRequisicion' => true,
		'folioCompra' => true,
		'estatusCompra' => true,
		'identificador' => true
]);

$dataINV = array();
foreach(DATA AS $key => $val){
	$dataINV[$val['col']] = $key;
}
define('DATACLIENTE',$dataINV);

# carpeta para subir archivos
define('SUBIDAARCHIVOS', realpath(dirname(__FILE__)) . '/' . $PathPrefix . 'archivos/adquisiciones/' . date('Y') . '/');
//define('SUBIDAARCHIVOS', substr(realpath(dirname(__FILE__)),0,strrpos(realpath(dirname(__FILE__)), "/")) . '/archivos/adquisiciones/' . date('Y') . '/');
define('PATHELIMINAR', substr(realpath(dirname(__FILE__)),0,strrpos(realpath(dirname(__FILE__)), "/")) . '/');

/*********************************************** DESARROLLO DE FUNCIONES ***********************************************/

function informacionProcesoCompra($db){
	$data = array('success'=>false, 'msg'=>'Ocurrió un incidente inesperado al momento de consultar la información.');
	$info = $_POST;
	$sql = "SELECT pc.*,
			DATE_FORMAT(pc.`dtm_fecha_convocatoria`,'%d-%m-%Y') AS `dtm_fecha_convocatoria`,
			DATE_FORMAT(pc.`dtm_fecha_inicio`,'%d-%m-%Y') AS `dtm_fecha_inicio`,
			DATE_FORMAT(pc.`dtm_fecha_estimada`,'%d-%m-%Y') AS `dtm_fecha_estimada`,
			DATE_FORMAT(pc.`dtm_fecha_contrato_inicio`,'%d-%m-%Y') AS `dtm_fecha_contrato_inicio`,
			DATE_FORMAT(pc.`dtm_fecha_contrato_fin`,'%d-%m-%Y') AS `dtm_fecha_contrato_fin`,
			DATE_FORMAT(pc.`dtm_fecha_contrato_firma`,'%d-%m-%Y') AS `dtm_fecha_contrato_firma`,
			DATE_FORMAT(pc.`dtm_fecha_creacion`,'%d-%m-%Y') AS `dtm_fecha_creacion`,
			pc.`id_nu_estatus` AS `id_estatus`,
			bs.`namebutton` AS `id_nu_estatus`

			FROM `tb_proceso_compra` AS pc
			LEFT JOIN `tb_botones_status` AS bs ON bs.`statusid` = pc.`id_nu_estatus` AND bs.`sn_funcion_id` = '2455' AND bs.`functionid` = 0

			WHERE pc.`id_nu_compra` = '$info[identificador]'";

	$result = DB_query($sql, $db, '');
	if (DB_num_rows($result)!=0) {
		$datos = array();
		while ($row = DB_fetch_array($result)) {
			foreach ($row as $key => $value) {
				$datos[ ( array_key_exists($key, DATACLIENTE) ? DATACLIENTE[$key] : $key) ] = utf8_encode($value);
			}
			$data['content'] = $datos;
			$data['idEstatus'] = $row['id_estatus'];
		}

		$data['success']=true;
		$data['msg']='Solicitud ejecutada con exito';
	}

	return $data;
}

function update($db){
	$data = array('success'=>false, 'msg'=>'Ocurrió un incidente inesperado al momento de actualizar la información.');
	$info = $_POST;
	$identificador = $info['identificador'];

	$sql = "SELECT * FROM `".LNTBLCAT."` WHERE `".DATA['identificador']['col']."` = '$identificador'";
	$result = DB_query($sql, $db);
	// comprobación de consistencia de datos
	if(DB_num_rows($result) == 0){
		$data['msg'] = 'No se encontraron los datos solicitados.';
		return $data;
	}

	$datosProceso = DB_fetch_array($result);
	$folio = $datosProceso['sn_folio_compra'];

	$sql = "SELECT * FROM `".LNTBLCAT."` WHERE `".DATA['identificador']['col']."` = '$identificador' AND `id_nu_estatus` = '3'";
	$result = DB_query($sql, $db);
	// comprobación de consistencia de datos
	if(DB_num_rows($result) != 0){
		$data['msg'] = "El registro <strong>$folio</strong> no puede actualizarse por el estatus en el que se encuentra.";
		return $data;
	}

	// comprobación de éxito de la información
	if(DB_query(obtenUpdate($info), $db)==true){
		$data['msg'] = "<center>Se actualizó correctamente el registro <strong>$folio</strong>.";
		$data['success'] = true;
	}

	return $data;
}

function listaDeDocumentos($db){
	$data = array('success'=>false, 'msg'=>'Ocurrió un incidente inesperado al momento de consultar la información.');
	$info = $_POST;
	$sql = "SELECT DISTINCT
			cd.`id_nu_documento_compra` AS `id`,
			cd.`txt_nombre_real` AS `nombreReal`,
			cd.`txt_nombre` AS `nombre`,
			cd.`sn_extension` AS `extension`,
			cd.`txt_url` AS `url`,
			cd.`ind_tipo_anexo` AS `idTipoAnexo`,
			ca.`ln_nombre_descripcion` AS `tipoAnexo`,
			ca.`id_nu_tipo_carpeta` AS `idTipoCarpeta`,
			cd.`userid` AS `usuario`,
			DATE_FORMAT(cd.`dtm_fecha_creacion`,'%d-%m-%Y') AS `fecha`,
			cd.`ind_post_concluido` AS `postConcluido`,
			pc.`id_nu_estatus` AS `idEstatus`

			FROM `tb_proceso_compra_documentos` AS cd
			LEFT JOIN `tb_proceso_compra` AS pc ON pc.`id_nu_compra` = cd.`id_nu_compra`
			LEFT JOIN `tb_cat_tipo_compra_anexo` AS ca ON ca.`id_nu_tipo_compra_anexo` = cd.`ind_tipo_anexo`

			WHERE cd.`id_nu_compra` = '$info[identificador]'

			ORDER BY cd.`id_nu_documento_compra` ASC";

	$result = DB_query($sql, $db, '');
	if (DB_num_rows($result)!=0) {
		$rows = array();
		while ($row = mysqli_fetch_object($result)) {
			$rows[$row->idTipoCarpeta][] = [
				'extension'=> utf8_encode($row->extension),
				'nombreArchivo' => utf8_encode($row->nombre),
				'usuario' => utf8_encode($row->usuario),
				'tipoAnexo' => utf8_encode($row->tipoAnexo),
				'fecha' => $row->fecha,
				'descargar' => '<a href="'.$row->url.'" target="_blank"><span class="modificar glyphicon glyphicon glyphicon-download-alt"></span></a>',
				'eliminar' => ( $row->idEstatus<3||($row->idEstatus==3&&$row->postConcluido) ? '<span class="modificar glyphicon glyphicon glyphicon-trash"></span>' : "" ),
				'postConcluido' => $row->postConcluido,
				'identificador' => $row->id
			];
		}

		$data['success']=true;
		$data['msg']='Solicitud ejecutada con exito';
		$data['files-adm'] = ( array_key_exists(1, $rows) ? $rows[1] : array() );
		$data['files-seg'] = ( array_key_exists(2, $rows) ? $rows[2] : array() );
		$data['files-otr'] = ( array_key_exists(3, $rows) ? $rows[3] : array() );
	}

	return $data;
}

function uploadFiles($db){
	$data = ['success'=>false,'msg'=>'Ocurrió un error al momento generar la información.'];
	$info = getInformacion();
	$info['folio'] = fnConsultaFolio($db,$info);
	# extracción de la los índices del arreglo de información a variables $archivos, $identificador y $tiposDeAnexo
	extract($info);
	$finalPath = SUBIDAARCHIVOS . "$folio/";

	# comprobación del envío de archivos
	if(empty($archivos)){ return ['success'=>false,'msg'=>'Es necesario colocar los archivos que se dese cargar.']; }
	# comprobación de envío de identificador
	if(empty($identificador)){ return ['success'=>false,'msg'=>'Es necesario indicar el identificador del proceso de compra']; }
	# comprobación del envío de folio
	if(empty($folio)){ return ['success'=>false,'msg'=>'Es necesario indicar el folio del proceso de compra.']; }

	# creacion del directorio
	if(!file_exists(SUBIDAARCHIVOS)){ crearCarpeta(SUBIDAARCHIVOS); }
	if(!file_exists($finalPath)){ crearCarpeta($finalPath); }

	$flag = 0;
	$msg = '';
	DB_Txn_Begin($db);
	try {
		# procesamiento de los archivos
		foreach ($archivos as $key => $doc) {
			# se guarda el documento
			if(!guardaArchivo($db, $doc, $identificador, $tiposDeAnexo[$key], $finalPath)){
				$data['msg'] = "Ocurrió un incidente al momento de guardar el archivo $doc[name]";
				$flag++;
				break;
			}
			else{
				$msg .= "Se generó con éxito la información del documento: $doc[name]<br>";
			}
		}
		if($flag==0){
			$data['msg'] = $msg;
			$data['success'] = true;
			DB_Txn_Commit($db);
		}
		else{ DB_Txn_Rollback($db); }
	} catch (Exception $e) {
		$data['msg'] = 'Ocurrió un incidente inesperado al guardar la información del comprobante';
		DB_Txn_Rollback($db);
	}

	return $data;
}

function deleteFiles($db){
	$data = ['success'=>false,'msg'=>'Ocurrió un incidente inesperado al momento de consultar la base de datos'];
	$sql = "SELECT `txt_url` FROM `tb_proceso_compra_documentos` WHERE `id_nu_documento_compra` = '$_POST[idDocumento]'";
	$result = DB_query($sql, $db);
	if(DB_num_rows($result)){
		$url = PATHELIMINAR.DB_fetch_array($result)['txt_url'];

		try {
			$sql = "DELETE FROM `tb_proceso_compra_documentos` WHERE `id_nu_documento_compra` = '$_POST[idDocumento]'";
			$result = DB_query($sql, $db);
			if($result == true){
				@unlink($url);
				$data['file'] = $url;
				$data['success'] = true;
				$data['msg'] = 'Se eliminó con éxito el documento.';
				DB_Txn_Commit($db);
			}else{
				$data['msg'] = "No se encontró el documento indicado.";
				DB_Txn_Rollback($db);
			}
		} catch (Exception $e) {
			$data['msg'] .= '<br>'.$e->getMessage();
			DB_Txn_Rollback($db);
		}
	}else{
		$data['msg'] = "No se encontró el documento indicado.";
	}

	return $data;
}

function cargaSelects($db){
	// declaración de variables de la función
	$data = ['success'=>false,'msg'=>'Ocurrió un incidente al actualizar la información. Favor de contactar al administrador'];

	$data['selectExpediente'] = datosSelectExpediente($db)['content'];
	$data['selectContratacion'] = datosSelectContratacion($db)['content'];
	$data['selectAnexo'] = datosSelectAnexo($db)['content'];
	$data['selectAnexoTipoCarpeta'] = datosSelectAnexoTipoCarpeta($db)['content'];
	$data['selectPeriodoContrato'] = datosSelectPeriodoContrato($db)['content'];
	

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

function obtenUpdate($info){
	$campos = '';
	$flag = 0;
	$identificador = $info['identificador'];
	$iterador = DATA;

	foreach(SINUPDATE AS $key => $value){
		unset($iterador[$key]);
	}

	$info['fechaIni'] = ( is_null($info['fechaIni']) ? $info['fechaInicio'] : $info['fechaIni'] );
	$info['fechaFin'] = ( is_null($info['fechaFin']) ? $info['fechaInicio'] : $info['fechaFin'] );
	$info['fechaFirma'] = ( is_null($info['fechaFirma']) ? $info['fechaInicio'] : $info['fechaFirma'] );

	$info['fechaConvocatoria'] = date_format(date_create_from_format('d-m-Y', $info['fechaConvocatoria']),'Y-m-d');
	$info['fechaInicio'] = date_format(date_create_from_format('d-m-Y', $info['fechaInicio']),'Y-m-d');
	$info['fechaEstimada'] = date_format(date_create_from_format('d-m-Y', $info['fechaEstimada']),'Y-m-d');
	$info['fechaIni'] = date_format(date_create_from_format('d-m-Y', $info['fechaIni']),'Y-m-d');
	$info['fechaFin'] = date_format(date_create_from_format('d-m-Y', $info['fechaFin']),'Y-m-d');
	$info['fechaFirma'] = date_format(date_create_from_format('d-m-Y', $info['fechaFirma']),'Y-m-d');

	foreach ($info as $input => $valor) {
		if(!array_key_exists($input, $iterador)){ continue; }
		if($flag!=0){ $campos .= ', '; }
		$data = DATA[$input];
		$campos .= "`$data[col]` = '".( $data['tipo']=='string' ? utf8_decode($valor) : ( $data['tipo']=='integer'&&$valor=="" ? 0 : $valor ) )."'";
		$flag++;
	}

	return " UPDATE `".LNTBLCAT."` SET $campos WHERE `".DATA['identificador']['col']."` = '$identificador' ";
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

function obtenDatosLista($db,$sql){
	// declaración de variables de la función
	$data = ['success'=>false, 'msg'=>'Ocurrió un incidente al obtener la información. Favor de contactar al administrador.'];
	$info = $_POST;

	$datosCortos = array();

	$result = DB_query($sql, $db);

	// comprobación de existencia de la información
	if(DB_num_rows($result) == 0){
		$data['msg'] = 'No se encontraron los datos solicitados.';
		return $data;
	}

	while ($rs = DB_fetch_array($result)) {
		$datosCortos[] = [
			'value' => $rs['valor'],
			'text' => utf8_encode($rs['label']),
			'rfc' => utf8_encode($rs['RFC']),
			'reps' => utf8_encode($rs['representante'])
		];
	}

	$data['cuentasMenores'] = $datosCortos;
	$data['success'] = true;
	// retorno de la información
	return $data;
}

function datosSelectExpediente($db){
	$sql = "SELECT DISTINCT `id_nu_tipo_compra_expediente` AS `valor`, `ln_nombre_descripcion` AS `label`

			FROM `tb_cat_tipo_compra_expediente`

			ORDER BY `ln_nombre_descripcion`";
	
	return obtenDatosSelect($db,$sql);
}

function datosSelectContratacion($db){
	$sql = "SELECT DISTINCT `id_nu_tipo_compra_contratacion` AS `valor`, `ln_nombre_descripcion` AS `label`

			FROM `tb_cat_tipo_compra_contratacion`

			ORDER BY `ln_nombre_descripcion`";
	
	return obtenDatosSelect($db,$sql);
}

function datosSelectAnexo($db){
	$sql = "SELECT DISTINCT `id_nu_tipo_compra_anexo` AS `valor`, `ln_nombre_descripcion` AS `label`

			FROM `tb_cat_tipo_compra_anexo`

			ORDER BY `ln_nombre_descripcion`";
	
	return obtenDatosSelect($db,$sql);
}

function datosSelectAnexoTipoCarpeta($db){
	$sql = "SELECT DISTINCT `id_nu_tipo_compra_anexo` AS `valor`, `id_nu_tipo_carpeta` AS `label`

			FROM `tb_cat_tipo_compra_anexo`

			ORDER BY `id_nu_tipo_carpeta`";
	
	return obtenDatosSelect($db,$sql);
}

function datosSelectPeriodoContrato($db){
	$sql = "SELECT DISTINCT `id_nu_tipo_compra_periodo_contrato` AS `valor`, `ln_nombre_descripcion` AS `label`

			FROM `tb_cat_tipo_compra_periodo_contrato`

			ORDER BY `id_nu_tipo_compra_periodo_contrato`";
	
	return obtenDatosSelect($db,$sql);
}

function datosListaProveedor($db){
	$sql = "SELECT `supplierid` AS `valor`, `suppname` AS `label`, `taxid` AS 'RFC', IF(`ln_representante_legal` IS NOT NULL AND `ln_representante_legal` <> '',`ln_representante_legal`,`suppname`) AS `representante`

			FROM `suppliers`

			WHERE `supplierid` <> '' AND `suppname` <> ''

			ORDER BY `suppname` ASC";
	
	return obtenDatosLista($db,$sql);
}

function datosListaProveedores($db){
	// declaración de variables de la función
	$data = ['success'=>false, 'msg'=>'Ocurrió un incidente al obtener la información. Favor de contactar al administrador.'];
	$info = $_POST;

	$registrosEncontrados = array();

	$sql = "SELECT `supplierid` AS `valor`, `suppname` AS `label`, `taxid` AS 'RFC', IF(`ln_representante_legal` IS NOT NULL AND `ln_representante_legal` <> '',`ln_representante_legal`,`suppname`) AS `representante`

			FROM `suppliers`

			WHERE `supplierid` <> '' AND `suppname` <> ''

			ORDER BY `suppname` ASC";

	$result = DB_query($sql, $db);

	// comprobación de existencia de la información
	if(DB_num_rows($result) == 0){
		$data['msg'] = 'No se encontraron los datos solicitados.';
		return $data;
	}

	while ($rs = DB_fetch_array($result)) {
		$registrosEncontrados[] = [
			'valor' => $rs['valor'],
			'texto' => utf8_encode($rs['label']),
			'RFC' => utf8_encode($rs['RFC']),
			'representanteLegal' => utf8_encode($rs['representante'])
		];
	}

	$data['registrosEncontrados'] = $registrosEncontrados;
	$data['success'] = true;
	// retorno de la información
	return $data;
}

function fnConsultaFolio($db,$info){
	$sql = "SELECT `sn_folio_compra` AS `Folio`

			FROM `tb_proceso_compra`

			WHERE `id_nu_compra` = '$info[identificador]'";

	return DB_fetch_array(DB_query($sql, $db))['Folio'];
}

/*********************** FUNCIONES DE AYUDA PARA MANIPULACIÓN DE ARCHIVOS ***********************/
function getInformacion(){
	$archivos = getDocument();
	unset($_POST['method']);# se elimina el metodo
	# se manda la informacion general
	$data = $_POST;
	# se colocan los archivos enviados en caso de existir
	if(!empty($archivos)){ $data['archivos'] = $archivos; }

	return $data;
}

function getDocument($nombreIndice='archivos'){
	# se compureba la existencion de la informacion
	$docs = !empty($_FILES[$nombreIndice])? $_FILES[$nombreIndice] : [];
	$data = [];# creacion contenedor
	if(!empty($docs)){
		# iteracion por los elementos enviados
		foreach ($docs['name'] as $key => $doc) {
			$data[] = array(
				'error'=>$docs['error'][$key],
				'name'=>$docs['name'][$key],
				'size'=>$docs['size'][$key],
				'temp_name'=>$docs['tmp_name'][$key],
				'type'=>$docs['type'][$key],
			);
		}
	}

	return $data;
}

function crearCarpeta($directorio){
	# crea el directorio indicado
	@mkdir($directorio);
}

function moverArchivo($doc, $ubicacion){
	# comprobación y creación de la carpeta de ser necesario
	if(!file_exists($ubicacion)){ crearCarpeta($ubicacion); }
	$name = $ubicacion . $doc['name'];
	# comprobación de archivo subido
	if(is_uploaded_file($doc['temp_name'])){
		# cambio de ubicación del archivo
		$conf = move_uploaded_file($doc['temp_name'], $name);
		@chown($name, 'root');
		@chgrp($name, 'root');
		return $conf;
	}
	return false;
}

function obtenExtension($doc){
	$nombre = $doc;
	$nombreTemp = explode('.', $nombre);
	$len = count($nombreTemp)-1;
	return $nombreTemp[$len];
}

function guardaArchivo($db, $doc, $identificador, $tipoDeAnexo, $finalPath){
	# se comprueba que el documento se mueva
	if(!moverArchivo($doc, $finalPath)){ return false; }

	$finalPath = substr( $finalPath, strpos($finalPath, "archivos/adquisiciones") );

	$extension = obtenExtension($doc['name']);
	$url = "$finalPath$doc[name]";

	$sql = "SELECT `id_nu_estatus` AS `Estatus` FROM `tb_proceso_compra` WHERE `id_nu_compra` = '$identificador'";
	$postConcluido = ( DB_fetch_array(DB_query($sql, $db))['Estatus'])==3 ? 1 : 0 ;

	$sql = "INSERT INTO `tb_proceso_compra_documentos`
				(`id_nu_compra`, `ind_tipo_anexo`, `txt_nombre_real`, `txt_nombre`, `sn_extension`, `txt_url`, `userid`, `dtm_fecha_creacion`, `ind_post_concluido`)
			VALUES
				('$identificador', '$tipoDeAnexo', '$doc[name]', '$doc[name]', '$extension', '$url', '$_SESSION[UserID]', NOW(), '$postConcluido')";
	$result = DB_query($sql, $db);
	# retorno de información correcta
	if($result == true){ return true; }
	# retorno de información con error
	return false;
}
