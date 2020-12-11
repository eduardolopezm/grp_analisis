<?php
/**
 * Viaticos
 *
 * @category proceso
 * @package  ap_grp
 * @author   Luis Aguilar Sandoval
 * @file:	 comprobacionOficioComisionModelo.php
 * Fecha creacion: 25/01/2017
 * pantalla de comprovacion de los oficios de comision genrados
 * consforme a un identificador
 */

/**/
////ini_set('display_errors', 1);
//ini_set('log_errors', 1);
//error_reporting(E_ALL);
/**/
$PageSecurity = 1;
$PathPrefix = '../';
$funcion=2338;
define('FUNCTIONID', $funcion);

session_start();
include($PathPrefix . 'config.php');
include $PathPrefix . "includes/SecurityUrl.php";
include($PathPrefix . 'includes/ConnectDB.inc');
include($PathPrefix . 'includes/SecurityFunctions.inc');
include($PathPrefix . 'includes/SQL_CommonFunctions.inc');
// Include para el periodo
include($PathPrefix . 'includes/DateFunctions.inc');

/************************** inclusión de validador **************************/
include($PathPrefix . 'xml_validator/lib/Main.class.php');
include($PathPrefix . 'includes/nusoap/lib/nusoap.php');
// include($PathPrefix . 'xml_validator/lib/factory/ComprobanteFactory.class.php');
// include($PathPrefix . 'xml_validator/lib/SatXmlValidatorManager.class.php');

/*********************************************** DEFINICION DE GLOBALES ***********************************************/
# definición de tipos permitidos de compresión
define('TIPOSCOMPRESO', ['application/zip', 'application/x-zip', 'application/octet-stream', 'application/x-zip-compressed']);
# ubicación de los archivos temporales
define('UBICACIONTEMPORAL', realpath(dirname(__FILE__)) . '/' . $PathPrefix . 'xml_validator/temp/' . $_SESSION['UserID'] . '/');
# carpeta de archivos subidos
define('SUBIDAARCHIVOS', realpath(dirname(__FILE__)) . '/' . $PathPrefix . 'archivos/' . $_SESSION['UserID'] . '/');
# definición de extensiones permitidas
define('EXTPER', ['xml','pdf']);
# definición de cantidad de las desimales
define('DECPLACE', !empty($_SESSION['DecimalPlaces'])? $_SESSION['DecimalPlaces'] : 2);

/*********************************************** DESARROLLO DE FUNCIONES ***********************************************/

function obtenInformacionGeneral($db)
{
	$data = ['success'=>true,'msg'=>'Ocurrio un incidente inesperado al consultar la información'];
	# obtencion de la informacion enviada
	$info = getInformacion();
	# generacion de variabees clave => valor de la informacion enviada
	extract($info);
	# generacion de la consulta de los datos base de la comision
	// $sql = " SELECT
	// 		CONCAT(te.`ln_nombre`,' ',te.`sn_primer_apellido`,' ',te.`sn_segundo_apellido`) as nombre,
	// 		CONCAT(estado.`ln_nombre_entidad_federativa`,' - ',mun.`ln_nombre`) as destino,
	// 		tv.`ln_objetivo_comicion` as observaciones,
	// 		date_format(tv.`dtm_fecha_inicio`,'%d-%m-%Y') as fechaInicio,
	// 		date_format(tv.`dtm_fecha_termino`,'%d-%m-%Y') as fechaTermino
	// 	FROM `tb_viaticos` as tv
	// 	INNER JOIN tb_empleados as te
	// 		 ON te.`id_nu_empleado` = tv.`id_nu_empleado`
	// 	INNER JOIN `tb_cat_entidad_federativa` as estado
	// 		ON estado.`id_nu_entidad_federativa` = tv.`nu_destino_estado`
	// 	INNER JOIN `tb_cat_municipio` as mun
	// 		ON mun.`id_nu_entidad_federativa` = estado.`id_nu_entidad_federativa`
	// 		AND mun.`id_nu_municipio` = tv.`nu_destino_municipio`
	// 	WHERE tv.`id_nu_viaticos` = '$identificador'
	// 		AND tv.`sn_folio_solicitud` = '$folio'
	// 		AND tv.`tagref` = '$ur' LIMIT 1 ";
	$sql = "SELECT DISTINCT
			CONCAT(te.`ln_nombre`,' ',te.`sn_primer_apellido`,' ',te.`sn_segundo_apellido`) as nombre,
		--	CONCAT(estado.`ln_nombre_entidad_federativa`,' - ',mun.`ln_nombre`) as destino,
			tsv.`ln_objetivo_comicion` as observaciones,
			date_format(tsv.`dtm_fecha_inicio`,'%d/%m/%Y') as fechaInicio,
			date_format(tsv.`dtm_fecha_termino`,'%d/%m/%Y') as fechaTermino,
			tsv.amt_importe_total as total,
			tsv.ind_tipo_solicitud as tipoSolicitud
		FROM tb_viaticos as tsv
		INNER JOIN tb_empleados as te
			ON te.`id_nu_empleado` = tsv.`id_nu_empleado`
		INNER JOIN tb_solicitud_itinerario as tsi
			ON tsi.`id_nu_solicitud_viaticos` = tsv.`id_nu_viaticos`
		-- INNER JOIN `tb_cat_entidad_federativa` as estado
		--	ON estado.`id_nu_entidad_federativa` = tsi.`nu_destino_estado`
		-- INNER JOIN `tb_cat_municipio` as mun
		--	ON mun.`id_nu_entidad_federativa` = estado.`id_nu_entidad_federativa`
		--	AND mun.`id_nu_municipio` = tsi.`nu_destino_municipio`
		WHERE tsv.`id_nu_viaticos` = '$identificador'
		LIMIT 1";
	# ejecucion de query
	$result = DB_query($sql, $db);
	$content = [];
	while ($rs = DB_fetch_array($result)) {
		$comprobado = obtenMontoComprobado($db,$identificador);
		$destino = obtenDestino($db, $rs, $identificador);
		$content = [
			"empleado" => utf8_encode($rs['nombre']),
			"destino" => $destino,
			"observaciones" => utf8_encode($rs['observaciones']),
			"dateDesde" => $rs['fechaInicio'],
			"dateHasta" => $rs['fechaTermino'],
			'montoTotal' => number_format($rs['total'],DECPLACE,'.',','),
			'montoComp'=>$comprobado
		];
	}
	$data['content'] = $content;
    
    
	# llamado de la información de los documentos ya registrados
	$data['nofiscales'] = obtendatosTablaNoFiscal($db, $identificador);
	$data['fiscales'] = obtendatosTablaFiscal($db, $identificador);
	$data['itinerario'] = obtenItinerario($db, $identificador);
	$data['perfil'] = obtenPerfil($db);
	$data['noPermitido'] = obtenNoPermitidos($db);
	$data['mia'] = confirmaDuenoSolicitud($db, $identificador);

	return $data;
}

function updateFilesFiscales($db)
{
	$data = ['success'=>false,'msg'=>'Ocurrió un incidente al momento de cargar la información'];
	$info = getInformacion();
	// $data['path'] = UBICACIONTEMPORAL;
	// return $data;
	# extracción de la información como son $archivos, $folio y $identificador
	extract($info);

	# comprobación del envío de archivos
	if(empty($archivos)){ return ['success'=>false,'msg'=>'Es necesario colocar los archivos que se dese cargar.']; }
	# comprobación de envío de identificador
	if(empty($identificador)){ return ['success'=>false,'msg'=>'Es necesario indicar el identificador de la comisión']; }
	# comprobación del envío de folio
	if(empty($folio)){ return ['success'=>false,'msg'=>'Es necesario indicar el folio del la comisión.']; }

	# procesamiento de los archivos y comprobación de zip
	$flag = 0;
	foreach ($archivos as $key => $doc) {
		# se descomprimen los archivos zip
		if(in_array($doc['type'], TIPOSCOMPRESO)){ descomprimeArchivo($doc); continue;}
		# se mueven los demás datos tipo xml en caso de que ocurra un error mandara mensaje
		if(!moverArchivo($doc, UBICACIONTEMPORAL)){
			$data['msg'] = 'Ocurrió un incidente al momento de mover el archivo '.$doc['name'];
			$flag++;
			break;
		}
	}
	# comprobación de errores la carga de archivos
	if($flag != 0){ return $data; }
	# comprobación y eliminación de archivos no validos
	$archivosComprobar = obtenerArchivosComprobar(UBICACIONTEMPORAL);
	// $archivosComprobados = comprobarArchivos($archivosComprobar, $db);
	/**
	 * Se comenta las siguientes lineas de código debido a lo convenido el día 20.02.18
	 * se retomara posteriormente de ser necesario.
	 *
	 * # comprobación por nombre de archivos para conservar los archivos pdf
	 * $archivosComprobados = comprobarArchivos($archivosComprobar, $db);
	 * $errors = $archivosComprobados['error'];
	 * $archivosComprobados = $archivosComprobados['aceptados'];// se sobre escribe la variable contenedora
	 * # comprobación de validación de todos los elementos
	 * if(count($archivosComprobados)==0){
	 * 	$msg = 'Se encontraron los siguientes errores: <br> <ul>';
	 * 	foreach ($errors as $key => $error) {
	 * 		$msg .= "<li><strong>Archivo</strong>:$key, <strong>Errores</strong>:".utf8_encode($error)."</li>";
	 * 	}
	 * 	$msg .= "</ul>";
	 * 	$data['msg'] = $msg;
	 * 	# se manda la llamada del borrado de los archivos
	 * 	eliminaArchivos(UBICACIONTEMPORAL);
	 * 	return $data;
	 * }
	*/
 	# comprobación por nombre de archivos para conservar los archivos pdf
 	//echo "justo antes de invocar comprobarArchivos";
 	$archivosComprobados = comprobarArchivos($archivosComprobar, $db);
 	if( isset($archivosComprobados['error']) ) {
 	  $errors = $archivosComprobados['error'];
    }
    if ( isset($archivosComprobados['aceptados']) ) {
 	   $archivosComprobados = $archivosComprobados['aceptados'];// se sobre escribe la variable contenedora
    }
 	# comprobación de validación de todos los elementos
	$msg = 'Se encontraron los siguientes errores: <br> <ul>';
	if ( isset($errors) ) {
	   foreach ($errors as $key => $error) {
		 $msg .= "<li><strong>Archivo</strong>:$key, <strong>Errores</strong>:".utf8_encode($error)."</li>";
	   }
    }
	$msg .= "</ul>";
	$data['msg'] = $msg;
	# retorno de la información en caso de que no se pasara ningun archivo
	if(count($archivosComprobados)==0){
		# se manda la llamada del borrado de los archivos
		eliminaArchivos(UBICACIONTEMPORAL);
		return $data;
	}
	/* FIN DE COMENTADO DE CODIGO @date: 20.02.18 */

	DB_Txn_Begin($db);
	try {
		// return $archivosComprobados;
	    # se envían a guardar la información de los datos correctos
	    $documentosGuardados = guardarDocumento($db, $archivosComprobados, $identificador);
	    if(!$documentosGuardados['success']){
	    	$data['msg'] = 'Se encontraron las siguientes incidencias: <br>'.$documentosGuardados['errores'];
	    }else{
	    	$data['success'] = true;
	    	$data['msg'] = 'Se realizo con éxito el registro de la siguiente información: <br>'.
	    	$documentosGuardados['msg'];
	    	if(strlen($documentosGuardados['errores'])!=0){
	    		$data['msg'] .= 'Se encontraron las siguientes incidencias: <br>'.$documentosGuardados['errores'];
	    	}
	    }
	    # consolidación de la información
    	DB_Txn_Commit($db);
	} catch (Exception $e) {
		$data['msg'] .= 'Error: '.$e->getMessage();
		DB_Txn_Rollback($db);
	}

	$data['archivosComprobar'] = $archivosComprobar;
	$data['info'] = $archivosComprobados;
	$data['content'] = obtendatosTablaFiscal($db, $identificador);
	eliminaArchivos(UBICACIONTEMPORAL);
	return $data;
}

function updateFilesNotFiscales($db)
{
	$data = ['success'=>false,'msg'=>'Ocurrió un error al momento generar la información.'];
	$info = getInformacion();
	$tempPath = SUBIDAARCHIVOS . 'comprobante/';
	$aux = date('d');
	# extracción de la información como son $archivos, $folio y $identificador
	extract($info);
	# se realiza la conversión del arreglo de datos del comprobante
	$datosComprobante = json_decode($datosComprobante);// se maneja como objeto
	// return $archivos;

	# comprobación del envío de archivos
	if(empty($archivos)){ return ['success'=>false,'msg'=>'Es necesario colocar los archivos que se dese cargar.']; }
	# comprobación de envío de identificador
	if(empty($identificador)){ return ['success'=>false,'msg'=>'Es necesario indicar el identificador de la comisión']; }
	# comprobación del envío de folio
	if(empty($folio)){ return ['success'=>false,'msg'=>'Es necesario indicar el folio del la comisión.']; }

	# creacion del directorio
	if(!file_exists(SUBIDAARCHIVOS)){ crearCarpeta(SUBIDAARCHIVOS); }
	if(!file_exists($tempPath)){ crearCarpeta($tempPath); }
	$finalPath = $tempPath . date('d');
	if(!file_exists($finalPath)){ crearCarpeta($finalPath); }
	$data['pathI'] = $finalPath;

	$flag = 0;
	$msg = '';
	DB_Txn_Begin($db);
	try {
		# procesamiento de los archivos
		foreach ($archivos as $key => $doc) {
			# se guarda el documento
			if(!guardaComprobante($db, $doc, $datosComprobante, $finalPath)){
				$data['msg'] = 'Ocurrió un incidente al momento de guardar el archivo ' . $doc['name'];
				$flag++;
				break;
			}
			else{
				$msg .= 'Se generó con éxito la información del documento: '.$doc['name'].' <br>';
			}
		}
		if($flag==0){
			$data['msg'] = $msg;
			$data['success'] = true;
			$data['content'] = obtendatosTablaNoFiscal($db, $datosComprobante->id_nu_solicitud);
			DB_Txn_Commit($db);
		}
		else{ DB_Txn_Rollback($db); }
	} catch (Exception $e) {
		$data['msg'] = 'Ocurrió un incidente inesperado al guardar la información del comprobante';
		DB_Txn_Rollback($db);
	}

	return $data;
}

/*********************** FUNCIONES ENVIAR ARCHIVO ULIARCHIVOS ***********************/

function descomprimeArchivo($doc)
{
	if(!moverArchivo($doc, SUBIDAARCHIVOS)){ return false; }
	# comprobación y creación de la carpeta de ser necesario
	if(!file_exists(UBICACIONTEMPORAL)){ crearCarpeta(UBICACIONTEMPORAL); }
	# tratamiento del archivo zip
	$zipTemp = new ZipArchive();
	$zipTemp->open(SUBIDAARCHIVOS . $doc['name']);
	$zipTemp->extractTo(UBICACIONTEMPORAL);
	$zipTemp->close();
	return true;
}

function moverArchivo($doc, $ubicacion)
{
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

function crearCarpeta($directorio)
{
	# crea el directorio indicado
	@mkdir($directorio);
}

function obtenerArchivosComprobar($ubicacion)
{
	$data = [];
	$directorioAbierto = opendir($ubicacion);
	while ($actualDir = readdir($directorioAbierto)) {
		# si se encuentra en los directorios . y .. no se procesan
		if(in_array($actualDir, ['.','..','__MACOSX'])){ continue; }
		# si no pertenece al tipo de archivo se manda eliminar
		$nombreTemp = explode('.', $actualDir);
		$len = count($nombreTemp)-1;
		if(!in_array($nombreTemp[$len], EXTPER)){ unlink($ubicacion . $actualDir); continue; }
		# se coloca en el indice solicitado pdf o xml
		$data[$nombreTemp[$len]][] = $actualDir;
	}
	return $data;
}


/*if (function_exists($_GET['f'])) {
	//print_r("entro A ");
	//consultarFacturaElectronicaSat();
	call_user_func($_GET['f']);
} */

function comprobarArchivos($archivos, $db)
{
	$data = [];
	$xmls = $archivos['xml'];
	foreach ($xmls as $key => $doc) {
		$error = '';
		$nombre = obtenNombreArchivo($doc);
		#se genera la instancia del manejador del xml de facturas
		try {
		    
		    $manejador = Main::getValidationManager(UBICACIONTEMPORAL, $doc, 0, $db, true);

            # se obtienen los datos de la factura
		    $total       = $manejador->getTotal();
		    $rfcEmisor   = $manejador->getEmisor()->getRfc();
		    $rfcReceptor = $manejador->getReceptor()->getRfc();
	    	$uuid        = $manejador->getTimbreFiscal()->getUuid();
    	}
    	catch (Exception $e) {
    		echo 'Message: '. $e->getMessage();
    	}

        # se invoca el servicio del sat para verificar si la factura está vigente
        try {
          $vigencia = consultarFacturaElectronicaSat($rfcEmisor,$rfcReceptor,$total,$uuid);
        } 
        catch (Exception $e) {
        	echo 'Message: '. $e->getMessage();
        }
        if ( $vigencia == "Vigente" ) {
           $data['aceptados'][$nombre] = $manejador;
           ////echo "Esta vigente";
        }
        else {
           ////echo "No esta vigente";	
        }

	}

	return $data;
}

/**
 * Función depreciada debido a los cambios del día 20.02.18
 * @author: JP
 */
function comprobarArchivosDepreciada($archivos, $db)
{
	// $data = ['error'=>[],'aceptados'=>[]];
	// $xmls = $archivos['xml'];
	// foreach ($xmls as $key => $doc) {
	// 	$error = '';
	// 	$nombre = obtenNombreArchivo($doc);
	// 	#se genera la instancia para la validación
	// 	$manejador = Main::getValidationManager(UBICACIONTEMPORAL, $doc, 0, $db);
	// 	# ejecución de validación
	// 	$manejador->validate();
	// 	# obtención de errores
	// 	$errors = $manejador->getErrors();
	// 	if (empty($errors) == false) {
 //            foreach ($errors as $k => $err) { $error = $err->toHtml().'<br>'; }
 //            $data['error'][$nombre] = $error;
 //            continue;
 //        }

 //        # si no contiene errores se procesa la información
 //        $data['aceptados'][$nombre] = $manejador;
	// }
	// return $data;
}

function guardarDocumento($db, $documentos, $identificador)
{
	$data = ['success'=>false,'msg'=>'Ocurrió un incidente al momento de consolidar los archivos.'];
	# retorno no balido
	if(empty($documentos)){ return $data; }

	# declaracion de varibles globales
	$msg = '';
	$msgOk = '';

	# procesamiento de los datos
	foreach ($documentos as $key => $comprobante) {
		# datos del comprobante
		// $comprobante = $doc->getComprobante();
		//$xmlContenido = $comprobante->getXmlObject()->saveXML();
		$xmlContenido = addslashes($comprobante->getXmlObject()->saveXML());
        $timbreFiscal = $comprobante->getTimbreFiscal();
		# dato de identificador unico de la factura
		$uuid = $timbreFiscal->getUuid();
		# busqueda de la duplicidad de la factura
		$sqlBus = "SELECT *
			FROM `tb_cat_documentos_comprobacion`
			WHERE `ind_tipo_documento` = '1' AND `id_nu_estatus` <> '5'
			AND `ln_uuid` = '$uuid'";
		$resBus = DB_query($sqlBus, $db);
		if(DB_num_rows($resBus) != 0){
			$msg .= 'La siguiente factura ya se encuentra registrada <strong>'.$key.'</strong>.<br>';
			continue;
		}

        # datos de la factura
        $nuFactura = $comprobante->getFolio();
        $concepto = $comprobante->getConceptos()[0]->getDescripcion();
        $total = $comprobante->getTotal();
        $rfc = $comprobante->getEmisor()->getRfc();
        $nombreEmisor = $comprobante->getEmisor()->getNombre();
        $fechaEmision = $comprobante->getFecha();

        # datos generados para la inserción
        $nombreDoc = $key.'.xml';

        # generación de inserción en base de datos
        $sqlInsert = "INSERT INTO `tb_cat_documentos_comprobacion`
        	(`id_nu_solicitud`, `ind_tipo_documento`, `ln_uuid`, `nu_factura`, `sn_concepto`, `amt_total`,
        	`sn_rfc_emisor`, `ln_nombre_emisor`, `ln_xml`, `dtm_fecha_emision`, `txt_nombre_real`,
        	`txt_nombre`, `sn_extension`, `dtm_fecha_creacion`)
		VALUES
			('$identificador', 1, '$uuid', '$nuFactura', '$concepto', '$total',
			'$rfc', '$nombreEmisor', '$xmlContenido', '$fechaEmision', '$nombreDoc',
			'$nombreDoc', 'xml', NOW())";
		$resInsert = DB_query($sqlInsert, $db);
		if($resInsert == true){
			$msgOk .= 'Se generó con éxito la información de la factura '.$nombreDoc.'<br>';
		}
	}// fin foreach

	# asignación  de la información
	$data['msg'] = $msgOk;
	$data['errores'] = $msg;
	$data['success'] = (strlen($msgOk) != 0);

	return $data;
}

function guardaComprobante($db, $doc, $solicitud, $finalPath)
{
	# se comprueba que el documento se mueva
	if(!moverArchivo($doc, $finalPath.'/')){ return flase; }

	$fecha = date_format(date_create_from_format('d-m-Y', $solicitud->fecha),'Y-m-d');//$solicitud->fecha;//fechaParaSQL('','-',$solicitud->fecha);
	$extencion = obtenExtencion($doc['name']);
	$codNombre = base64_encode($_SESSION['UserID'] . '-' . $solicitud->id_nu_solicitud . '-' . $doc['name']);
	$url = 'archivos/'.$_SESSION['UserID'].'/comprobante/'.date('d').'/'.$doc['name'];
	$sql = "INSERT INTO `tb_cat_documentos_comprobacion`
		(`id_nu_solicitud`, `ind_tipo_documento`, `id_nu_estatus`, `sn_concepto`, `amt_total`, `dtm_fecha_emision`, `txt_nombre_real`, `txt_nombre`, `sn_extension`, `txt_url`, `dtm_fecha_creacion`)
	VALUES
		('$solicitud->id_nu_solicitud', 2, 1, '".utf8_decode($solicitud->concepto)."', '$solicitud->monto', '$fecha', '".$doc['name']."', '".$doc['name']."', '$extencion', '$url', NOW())";
	$result = DB_query($sql, $db);
	# retorno de información correcta
	if($result == true){ return true; }
	# retorno de información con error
	return false;
}

// FIXME: es necesario terminar de revisar la eliminación de archivos
function eliminaDirectorios($directorio)
{
	# apertura de directorio
	$directorioAbierto = opendir($directorio);
	while ($actualDir = readdir($directorioAbierto)) {
		if(!in_array($actualDir, ['.','..'])){
			# encaso de ser un directorio se elimina
			if(is_dir($actualDir)){ rmdir($actualDir); }
			$nombreTemp = explode('.', $actualDir);
			$len = count($nombreTemp)-1;
			# se eliminan los ficheros que no son pdf o xml
			if(!in_array($nombreTemp[$len], EXTPER)){
				unlink($actualDir);
			}
		}
	}
}

function limpiaCarpeta($dir=''){
	$dir = empty($dir)? UBICACIONTEMPORAL : $dir;
	var_dump(@rmdir($dir));
}

function obtendatosTablaFiscal($db, $identificador){
	$data = [];
	$sql = "SELECT
		`id_nu_documento_comprobacion` as id,
		nu_factura as factura,
		date_format(`dtm_fecha_emision`,'%d/%m/%Y') as fecha,
		`sn_concepto` as concepto,
		ln_xml,
		`txt_url` as pdf,
		`id_nu_estatus` as estId,
		`txt_nombre` as nombre,
		`amt_total` as capturado,
		`amt_comprobado` as comprobado
	FROM tb_cat_documentos_comprobacion
	WHERE `id_nu_solicitud` = '$identificador'
	AND `ind_tipo_documento` = '1'";
	$result = DB_query($sql, $db);
	# comprobación de contenido de la consulta
	if(DB_num_rows($result) ==0 ){ return $data; }
	while ($rs = DB_fetch_array($result)) {
		$sqlEst = "SELECT `namebutton` as estatus FROM `tb_botones_status` WHERE `sn_funcion_id` = '".FUNCTIONID."' ".
			"AND functionid = 0 and statusid = '".$rs['estId']."' ";
		$resEst = DB_query($sqlEst, $db);
		$temp = DB_fetch_array($resEst);
		# generación de link de pdf
		$pdf = '';
		if(!empty($pdf)){
			$pdf = '<a href="'.$rs['url'].'" target="_blank"><span class="modificar glyphicon glyphicon-print"></span></a>';
		}
		# generación de link para xml
		# FIXME definir la forma en la que se descarga el xml target="/*_blank*/"
		# <a href="javascript: fndescargaXML('.$rs['id'].');"></a>
		$xml = '<span class="modificar glyphicon glyphicon-download-alt"></span>';
		$comentariosHTML = obtenComentarios($db, $rs['id'], $identificador);
		$comentarios = str_replace("*br*","\n",strip_tags(str_replace("</li>", "*br*", $comentariosHTML)));
		$data[] = [
			'id' => $rs['id'],
			'factura'=> $rs['factura'],
			'fecha' => $rs['fecha'],
			'concepto' => utf8_decode($rs['concepto']),
			'xml' => $xml,
			'pdf' => $pdf,
			'estatus' => $temp['estatus'],
			// @FIXME: colocar la consulta de los comentarios generados
			'observaciones' => $comentarios,
			'observacionesHTML' => $comentariosHTML,
			'montoFac' => number_format($rs['capturado'], DECPLACE, '.', ''),
			'montoComp' => number_format($rs['comprobado'], DECPLACE, '.', ''),
			'capturar' => '<span class="modificar glyphicon glyphicon glyphicon-pencil"></span>',
			'eliminar' => '<span class="modificar glyphicon glyphicon glyphicon-trash"></span>'
		];
	}
	return $data;
}

function obtendatosTablaNoFiscal($db, $identificador){
	$data = [];
	$sql = "SELECT
		`id_nu_documento_comprobacion` as id,
		date_format(`dtm_fecha_emision`,'%d/%m/%Y') as fecha,
		`sn_concepto` as concepto,
		`id_nu_estatus` as estId,
		`txt_nombre` as nombre,
		`amt_total` as capturado,
		`amt_comprobado` as comrpobado,
		`txt_url` as url
	FROM tb_cat_documentos_comprobacion
	WHERE `id_nu_solicitud` = '$identificador'
	AND `ind_tipo_documento` = '2'";
	$result = DB_query($sql, $db);
	# comprobación de contenido de la consulta
	if(DB_num_rows($result) ==0 ){ return $data; }
	while ($rs = DB_fetch_array($result)) {
		$sqlEst = "SELECT `namebutton` as estatus FROM `tb_botones_status` WHERE `sn_funcion_id` = '".FUNCTIONID."' ".
			"AND functionid = 0 and statusid = '".$rs['estId']."' ";
		$resEst = DB_query($sqlEst, $db);
		$temp = DB_fetch_array($resEst);
		$archivos = '<a href="'.$rs['url'].'" style="color: blue;" target="_blank">'.$rs['nombre'].'</a>';//utf8_decode($rs['nombre'])
		$comentariosHTML = obtenComentarios($db, $rs['id'], $identificador, 2);
		$comentarios = str_replace("*br*","\n",strip_tags(str_replace("</li>", "*br*", $comentariosHTML)));
		$data[] = [
			'id' => $rs['id'],
			'fecha' => $rs['fecha'],
			'concepto' => utf8_encode($rs['concepto']),
			'estatus' => $temp['estatus'],
			// @FIXME: colocar la consulta de los comentarios generados
			'observaciones' => $comentarios,
			'observacionesHTML' => $comentariosHTML,
			'archivos' => $archivos,
			'montoCap' => number_format($rs['capturado'], DECPLACE, '.', ''),
			'montoComp' => number_format($rs['comrpobado'], DECPLACE, '.', ''),
			'capturar' => '<span class="modificar glyphicon glyphicon glyphicon-pencil"></span>',
			'eliminar' => '<span class="modificar glyphicon glyphicon glyphicon-trash"></span>'
		];
	}
	return $data;
}

function obtenItinerario($db, $identificador){
	$data = [];
	$sql = "SELECT IF(p.`ln_descripcion` IS NULL,1,IF(e.`ln_nombre_entidad_federativa` IS NULL,2,0)) AS `tipoCom`, p.`ln_descripcion` AS `Pais`, e.`ln_nombre_entidad_federativa` AS `Entidad`, m.`ln_nombre` AS `Municipio`, DATE_FORMAT(`dt_periodo_inicio`,'%d/%m/%Y') AS `FechaIni`, DATE_FORMAT(`dt_periodo_termino`,'%d/%m/%Y') AS `FechaFin`, `nu_dias` AS `Dias`, `ind_pernocta` AS `Noches`, `amt_importe` AS `Importe`

			FROM `tb_solicitud_itinerario` AS si
			LEFT JOIN `tb_cat_paises` AS p ON p.`id_nu_pais` = si.`nu_destino_pais`
			LEFT JOIN `tb_cat_entidad_federativa` AS e ON e.`id_nu_entidad_federativa` = si.`nu_destino_estado`
			LEFT JOIN `tb_cat_municipio` AS m ON m.`id_nu_municipio` = si.`nu_destino_municipio`

			WHERE si.`id_nu_solicitud_viaticos` = '$identificador'";
	$result = DB_query($sql, $db);
	# comprobación de contenido de la consulta
	if(DB_num_rows($result) ==0 ){ return $data; }
	while ($rs = DB_fetch_array($result)) {
		$data[] = [
			'tipoCom' => $rs['tipoCom'],
			'pais' => utf8_encode($rs['Pais']),
			'entidad' => utf8_encode($rs['Entidad']),
			'municipio' => utf8_encode($rs['Municipio']),
			'fechaIni' => $rs['FechaIni'],
			'fechaFin' => $rs['FechaFin'],
			'dias' => $rs['Dias'],
			'noches' => $rs['Noches'],
			'importe' => $rs['Importe']
		];
	}
	return $data;
}

function guardarComentario($db)
{
	$data = ['success'=>false, 'msg'=>'Ocurrió un incidente al momento de guardar el comentario.'];
	# se extrae la infromación enviada
	extract( getInformacion() );
	# comprobación de la función que se ejecutara
	$funcionDatos = $tipo==1?'obtendatosTablaFiscal':'obtendatosTablaNoFiscal';
	# ejecución de inserción
	$sql="INSERT INTO `tb_comentario`
		(`id_nu_documento`, `ind_tipo`, `id_nu_usuario`, `ln_comentario`, `dtm_fecha_creacion`)
	VALUES
		('$identificador', '$tipo', '".$_SESSION['UserID']."', '".utf8_decode($coment)."', NOW())";
	$result = DB_query($sql, $db);
	# comprobación consolidacion de datos
	if($result == true){
		$data['success'] = true;
		$data['msg'] = 'Se agregó con éxito el comentario.';
		$data['content'] = $funcionDatos($db,$solicitud);
	}

	return $data;
}

function guardarMontoComprobado($db){
	$data = ['success'=>false, 'msg'=>'Ocurrió un incidente al momento de guardar el monto.'];
	# se extrae la información enviada
	extract( getInformacion() );
	# comprobación de la función que se ejecutara
	$funcionDatos = $tipo==1?'obtendatosTablaFiscal':'obtendatosTablaNoFiscal';
	# ejecución del query de inserción
	DB_Txn_Begin($db);
	try {
		$sql = "UPDATE `tb_cat_documentos_comprobacion`
			SET `amt_comprobado` = '$monto'
			WHERE `id_nu_solicitud` = '$solicitud' AND `id_nu_documento_comprobacion` = '$identificador'";
		$result = DB_query($sql, $db);
		if($result == true){
			$data['success'] = true;
			$data['msg'] = 'Se actualizo con éxito la información.';
			$data['content'] = $funcionDatos($db, $solicitud);
			DB_Txn_Commit($db);
		}
	} catch (Exception $e) {
		$data['msg'] .= '<br>'.$e->getMessage();
		DB_Txn_Rollback($db);
	}

	# se agrega la información de monto generado
	$data['montoComp'] = obtenMontoComprobado($db, $solicitud);
	return $data;
}

function obtenMontoComprobado($db, $identificador)
{
	$sql = "SELECT IFNULL(SUM(`amt_comprobado`),0) as total FROM `tb_cat_documentos_comprobacion`
		WHERE `id_nu_estatus` = '1' AND `id_nu_solicitud` = '$identificador'";
	$result = DB_query($sql, $db);
	$temp = DB_fetch_array($result);

	$sql = "UPDATE `tb_viaticos` SET `amt_importe_comprobado` = '$temp[total]' WHERE `id_nu_viaticos` = '$identificador'";
	DB_query($sql, $db);

	return number_format($temp['total'],DECPLACE,'.',',');
}

function updateStatus($db)
{
	$data = ['success'=>false,'msg'=>'Ocurrió un incidente inesperado al momento de realizar el cambio'];
	# se extrae la información de los datos enviados(datos esperados: identificador, type).
	extract( getInformacion() );
	# comprobación de existencia de solicitud
	$sqlComp = "SELECT * FROM `tb_viaticos` WHERE `id_nu_viaticos` = '$identificador'";
	$resComp = DB_query($sqlComp, $db);
	if(DB_num_rows($resComp) == 0){
		$data['msg'] = 'No se encontró la solicitud de comisión consultada. Favor de verificar';
		return $data;
	}
	# se procesan los datos de la actualización
	DB_Txn_Begin($db);
	try {
		$sql = "UPDATE `tb_viaticos` SET `id_nu_estatus` = '$type' WHERE `id_nu_viaticos` = '$identificador'";
		$result = DB_query($sql, $db);
		if($result==true){
			$data['success'] = true;
			DB_Txn_Commit($db);
		}
	} catch (Exception $e) {
		$data['msg'] .= $e->getMessage();
		DB_Txn_Rollback($db);
	}
	return $data;
}

/*********************** FUNCIONES DE AYUDA ***********************/
function getInformacion()
{
	$archivos = getDocument();
	unset($_POST['method']);# se elimina el metodo
	# se manda la informacion general
	$data = $_POST;
	# se colocan los archivos enviados en caso de existir
	if(!empty($archivos)){ $data['archivos'] = $archivos; }

	return $data;
}

function getDocument($nombreIndice='archivos')
{
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

function obtenNombreArchivo($doc)
{
	$nombre = $doc;
	$nombreTemp = explode('.', $nombre);
	$len = count($nombreTemp)-1;
	unset($nombreTemp[$len]);
	return implode('.', $nombreTemp);
}

function obtenExtencion($doc)
{
	$nombre = $doc;
	$nombreTemp = explode('.', $nombre);
	$len = count($nombreTemp)-1;
	return $nombreTemp[$len];
}

function eliminaArchivos($ubicacion)
{
	$directorioAbierto = opendir($ubicacion);
	while ($actualDir = readdir($directorioAbierto)) {
		# si se encuentra en los directorios . y .. no se procesan
		if(in_array($actualDir, ['.','..','__MACOSX'])){ continue; }
		@unlink($ubicacion.$actualDir);
	}
}

function obtenComentarios($db, $identificador, $solicitud, $tipo=1)
{
	$sql="SELECT
		`id_nu_comentario` as id,
		`ln_comentario` as com,
		`id_nu_usuario` as usuario,
		`dtm_fecha_creacion` as fecha
	FROM `tb_comentario`
	WHERE `id_nu_documento` = '$identificador'
	AND `ind_activo` = '1'
	AND `ind_tipo` = '$tipo'";
	$result = DB_query($sql, $db);
	$data = '<ul>';
	# retorno de la infromación de los comentarios
	if(DB_num_rows($result)==0){
		$data .= '</ul>';
		return $data;
	}
	# generación de comntarios guardados
	while ($rs = DB_fetch_array($result)) {
		$data .= '<li id="'.$rs['id'].'"><strong>'.$rs['usuario'].'</strong>:'.utf8_encode($rs['com']).' | <small>'.ConvertSQLDate($rs['fecha']).'</small> </li>';
	}
	$data .= '</ul>';
	return $data;
}

function obtenPerfil($db)
{
	$userid = $_SESSION['UserID'];
    $sql = "SELECT profileid FROM sec_profilexuser WHERE userid = '$userid'";
    $resl = DB_query($sql, $db);
    $p = DB_fetch_assoc($resl);
    return $p['profileid'];
}

function obtenNoPermitidos($db)
{
	$data = [1,2,3,4,6,7,8];
    switch (obtenPerfil($db)) {
        case 9:
            $data = array_merge($data,[9]);
            break;
        case 10:
            $data = array_merge($data,[10]);
            break;
    }
    return $data;
}

function confirmaDuenoSolicitud($db, $identificador)
{
	$sql = "SELECT * FROM `tb_viaticos` as v
	INNER JOIN `tb_empleados` as e ON e.`id_nu_empleado` = v.`id_nu_empleado`
	WHERE v.`id_nu_viaticos` = '$identificador' AND e.`id_nu_usuario` = '".$_SESSION['UserID']."'";
	$result = DB_query($sql, $db);
	# retorno de verdadero o falso segun los datos obtenidos
	return (DB_num_rows($result) != 0);
}

function obtenDestino($db, $resultset, $identificador)
{
	$destino = '';
	$sqlCampos = "SELECT `nu_destino_estado` as estado,`nu_destino_municipio` as municipio
		from `tb_solicitud_itinerario` where `id_nu_solicitud_viaticos` = '".$identificador."' ";
	$resultCampos = DB_query($sqlCampos, $db);
	$fetchCampos = DB_fetch_array($resultCampos);
	if($resultset['tipoSolicitud'] == 1){
		$sql = "SELECT CONCAT(ent.`ln_nombre_entidad_federativa`,' - ',mun.`ln_nombre`) as destino
			from `tb_cat_entidad_federativa` as ent
			inner join `tb_cat_municipio` as mun on ent.`id_nu_entidad_federativa` = mun.`id_nu_entidad_federativa`
			where ent.`id_nu_entidad_federativa` = '".$fetchCampos['estado']."' and mun.`id_nu_municipio` = '".$fetchCampos['municipio']."'";
	}
	else if($resultset['tipoSolicitud']==2){
		$sql = "SELECT `ln_descripcion` as destino from `tb_cat_paises` where id_nu_pais = '".$fetchCampos['estado']."' ";
	}
	$result = DB_query($sql, $db);
	while ($rs = DB_fetch_array($result)) {
		$destino = $rs['destino'];
	}
	return utf8_encode($destino);
}

function descargaXML($db){
	$data = ['success'=>false,'msg'=>'Ocurrió un incidente inesperado al momento de consultar la base de datos'];
	$sql = "SELECT `ln_xml`, `txt_nombre` FROM `tb_cat_documentos_comprobacion` WHERE `id_nu_documento_comprobacion` = '$_POST[idDocumento]'";
	$result = DB_query($sql, $db);
	if(DB_num_rows($result)){
		$datosXML = DB_fetch_array($result);

		header("Content-disposition: attachment; filename=test-$datosXML[txt_nombre]");
		header("Content-Type:application/xml; charset=utf-8");
		//echo $datosXML['ln_xml'];
		header("Expires: 0");
		$data['xml'] = $datosXML['ln_xml'];
		$data['archivo'] = $datosXML['txt_nombre'];
		$data['success'] = true;
	}else{
		$data['msg'] = "No se encontró la factura indicada.";
	}
	return $data;
}

function eliminarFacturas($db){
	$data = ['success'=>false,'msg'=>'Ocurrió un incidente inesperado al momento de consultar la base de datos'];
	$sql = "SELECT `txt_url` FROM `tb_cat_documentos_comprobacion` WHERE `id_nu_documento_comprobacion` = '$_POST[idDocumento]'";
	$result = DB_query($sql, $db);
	if(DB_num_rows($result)){
		$url = DB_fetch_array($result)['txt_url'];

		try {
			$sql = "DELETE FROM `tb_cat_documentos_comprobacion` WHERE `id_nu_documento_comprobacion` = '$_POST[idDocumento]'";
			$result = DB_query($sql, $db);
			if($result == true){
				$data['success'] = true;
				$data['msg'] = 'Se eliminó con éxito la factura.';
				DB_Txn_Commit($db);
			}else{
				$data['msg'] = "No se encontró la factura indicada.";
				DB_Txn_Rollback($db);
			}
		} catch (Exception $e) {
			$data['msg'] .= '<br>'.$e->getMessage();
			DB_Txn_Rollback($db);
		}
	}else{
		$data['msg'] = "No se encontró la factura indicada.";
	}
	return $data;
}






function consultarFacturaElectronicaSat($rfcEmisor,$rfcReceptor,$total,$uuid) {

    $result = "";
	$client = new nusoap_client("https://consultaqr.facturaelectronica.sat.gob.mx/consultacfdiservice.svc?wsdl", true);
	$client->soap_defencoding = "UTF-8";
	$error = $client->getError();

	$cadena = "?re=".$rfcEmisor."&rr=".$rfcReceptor."&tt=".$total."&id=".$uuid;

	if ($error) {
 	  echo "Error: " . $error . "\n";
   
	}

	$result = @$client->call("Consulta", array("expresionImpresa" => $cadena));
	if ($client->fault) {
 	  echo "Error de conexion al web service CFDI:\n";
  	  exit;
	} 
	else {
   	    $error = $client->getError();
   	    if ($error) {
       			echo "Error: " . $error;
   		} 
   		else {
       		if( $result['ConsultaResult']['Estado'] == "Vigente") {
       		  $result = "Vigente";
            }
       	}
    }

    return $result;

}







/*********************************************** DESARROLLO DE FUNCIONES ***********************************************/

/* EJECUCION DE FUNCIONES */
/*echo "esto que es:\n";
print_r($_POST);
exit;*/



$data = call_user_func_array($_POST['method'],[$db]);
/* MODIFICACION DE HEADER */
header('Content-type:application/json;charset=utf-8');
/* ENVIO DE INFORMACIÓN */
echo json_encode($data);
