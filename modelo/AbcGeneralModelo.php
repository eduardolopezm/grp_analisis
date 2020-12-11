<?php
/**
 * AbcGeneral.php
 * @category	Modelo
 * @package		ap_grp/modelo
 * @author		JP
 * @version		1.0.0
 * @date:		10.03.18
 *
 * Programa para afectación de paneles, la captura y administración de la información de los
 * catálogos.
 *
 * Se reemplazaron todos los `".ACTIVECOL."` = '1' por (`".ACTIVECOL."` = '1' OR `".ACTIVECOL."` LIKE 'S')
 */
session_start();
define('NAMEVARFUNC', 'dominiogeneral');
$PageSecurity = 1;
$PathPrefix = '../';
include($PathPrefix . 'config.php');
include($PathPrefix . 'includes/SecurityUrl.php');// extrae el numero de función
# extracción de función
$funcion = obtenFuncion();
include($PathPrefix . 'includes/ConnectDB.inc');
include($PathPrefix.'includes/SecurityFunctions.inc');
include($PathPrefix.'includes/SQL_CommonFunctions.inc');

/*********************************************** CONSTANSTANTES ***********************************************/
define('FUNID', $funcion);
define('DECPLACE', !empty($_SESSION['DecimalPlaces'])? $_SESSION['DecimalPlaces'] : 2);
define('SQLDETALLE', "SELECT
	`id_nu_panel_detalle` AS id, `ln_etiqueta` AS label, `ln_campo` AS clave, `ln_columna` AS col, `ln_tipo` AS tipo
	,`sn_longitud` AS len, `ln_formato` AS format ,`ln_select` AS opt
	FROM tb_cat_panel_detalle
	WHERE `ind_activo` = 1 AND `id_nu_panel_catalogo` = '%s' AND id_nu_funcion='".FUNID."' "
);

define('GENERICMSG', "[
	'error'=>'No puede eliminar el registro indicado', 'store'=>'Se generó con éxito el registro',
	'update'=>'Se actualizó con éxito el registro', 'destroy'=>'Se eliminó con éxito del registro'
]");

# variables esperadas LNTBLCAT, LNGRID, DATA, CONVERT
defineVaribles($db);
/*********************************************** DESARROLLO DE FUNCIONES ***********************************************/
/**
 * Función para el guardado de la información de los ítems generados
 * @param	[DBInstance]	$db		Instancia de la base de datos
 * @return	[Array]			$data	Arreglo con la respuesta verdadera o falsa según lo obtenido
 */
function store($db)
{
	// declaracion de variables de la función
	$data = ['success'=>false,'msg'=>'Ocurrió un incidente inesperado. Favor de contactar con el administrador.'];
	$info = $_POST;
	// comprobación de datos vacios
	if(empty($info['descripcion'])){
		$data['msg'] = 'Es necesario indicar el contenido principal, por ejemplo <strong>Descripción</strong>';
		return $data;
	}
	# actualización de registros encontrados
	if(updateWhenInserting($db,$info)){
		$data['success'] = true;
		$data['msg'] = MSGCONF[__FUNCTION__];
		$data['content'] = show($db)['content'];
		return $data;
	}
	# comprobación de duplicidad de identificadores múltiples
	if(array_key_exists("multiidentificadorcampo", $info)){
		if($info['multiidentificadorcampo']!=""){
			$comprobacionIdentificadoresMultiples = compruebaDuplicadoIdentificadoresMultiples($db,$info);
			if($comprobacionIdentificadoresMultiples[0]){
				$data['msg'] = "El dato que desea generar ya se encuentra registrado.<br>(<strong>$comprobacionIdentificadoresMultiples[1]</strong>)";
				$data['content'] = show($db)['content'];
				return $data;
			}
		}
	}else{
		# comprobación de duplicidad de datos
		if(compruebaDuplicado($db, $info)){
			$data['msg'] = 'El dato que desea generar ya se encuentra registrado.<br>(<strong>'.$info['descripcion'].'</strong>)';
			$data['content'] = show($db)['content'];
			return $data;
		}
	}
	# eliminación de datos no necesarios
	unset($info['identificador']);
	unset($info['multiidentificadorcampo']);
	unset($info['multiidentificadorvalor']);
	# generación de registros
	DB_Txn_Begin($db);
	try {
		// obtención del query para la inserción
		$sqlIn = obtenInsercion($db, $info);
		$resultIn = DB_query($sqlIn, $db);
		// comprobación de éxito de la generación de la información
		if($resultIn == true){
			$data['success'] = true;
			$data['msg'] = MSGCONF[__FUNCTION__];
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
	return $data;
}
/**
 * Función para la búsqueda de la información que llenará la tabla principal
 * @param	[DBInstance]	$db		Instancia de la base de datos
 * @return	[Array]			$data	Arreglo con la respuesta verdadera o falsa según lo obtenido
 */
function show($db)
{
	// declaración de variables de la función
	$data = ['success'=>false,'msg'=>'No se encontraron datos de la función'];
	$sql = LNCOMPUESTO!="" ? LNCOMPUESTO : "SELECT * FROM `".LNTBLCAT."` WHERE (`".ACTIVECOL."` = '1' OR `".ACTIVECOL."` LIKE 'S')";
	$result = DB_query($sql, $db);
	$IdentificadoresMultiples = "";
	$data['content'] = array();
	$data['Labels'] = array();
	$data['IMS'] = array();
	$data['IdentificadoresMultiples'] = array();
	$data['valoresSelect'] = array();
	$data['padresDelSelect'] = array();
	$data['hijosDelSelect'] = array();
	$ArregloSelect = array();
	// procesamiento de la información obtenida
	while ($rs = DB_fetch_array($result)) {
		$content = [
			'eliminar'=>'<span class="eliminar glyphicon glyphicon-trash"></span>',
			'modificar'=>'<span class="modificar glyphicon glyphicon-edit"></span>'
		];
		foreach (DATA as $campo => $columna) {
			$content[$campo] = convierteDato($rs, $columna);
			if($IdentificadoresMultiples==""&&$campo=="multiidentificadorcampo"){
				$IdentificadoresMultiples = convierteDato($rs, $columna);
			}
		}
		$data['content'][] = $content;
	}
	$idMS = explode("<=>", $IdentificadoresMultiples);
	foreach (DATA as $campo => $columna){
		if(array_key_exists(trim($campo),CONFIGDATA)){
			if(CONFIGDATA[$campo]['label']){
				$data['Labels'][$campo] = utf8_encode(CONFIGDATA[$campo]['label']);
			}
		}
		if($columna['tipo']=='select'){
			$ArregloSelect[$campo] = obtenSqlCampo($db, $columna['row']);
		}
		if(array_key_exists(trim($campo),CONFIGDATA)){
			if(is_numeric(array_search(CONFIGDATA[$campo]['col'], $idMS))){ //&&CONFIGDATA[$campo]['tipo']=="select"
				$data['IMS'][$campo] = utf8_encode(CONFIGDATA[$campo]['col']);
			}
		}
	}
	if(count($ArregloSelect)){
		foreach($ArregloSelect as $campo => $sql){
			$salida = array();
			$padres = array();
			$padresTemp = array();

			$optionSelect = array();
			$optionSelectN = array();
			$hijosSelect = 0;
			$padreAnterior = "";
			$result = DB_query($sql, $db);
			while ($rs = DB_fetch_array($result)) {
				// código para crear arrays multidimensiones con los padres e hijos de cada registro.
				/*if(array_key_exists("padresSelect", $rs)){
					$padres = explode("<=>",$rs['padresSelect']);
					$padresTemp = [
						'label'=>utf8_encode($rs['label']),
						'title'=>utf8_encode($rs['label']),
						'value'=>$rs['valor']
					];
					if(count($padres)){
						if($padreAnterior!=$rs["padresSelect".(count($padres)-1)]){
							$hijosSelect = 0;
							$padreAnterior = $rs["padresSelect".(count($padres)-1)];
						}else{
							$hijosSelect++;
						}
						if(count($padres)==1){
							$optionSelect[$rs["padresSelect0"]][$hijosSelect] = $padresTemp;
						}
						if(count($padres)==2){
							$optionSelect[$rs["padresSelect0"]][$rs["padresSelect1"]][$hijosSelect] = $padresTemp;
						}
					}
				}else*/{
					$salidaTemporal = [
						'label'=>utf8_encode($rs['label']),
						'title'=>utf8_encode($rs['label']),
						'value'=>$rs['valor']
					];
					if(array_key_exists("padresSelect", $rs)){
						$padres = explode("<=>",$rs['padresSelect']);
						if(count($padres)){
							$salidaTemporal["padres"] = count($padres);
							foreach($padres AS $idPadre => $Padre){
								$salidaTemporal["padresSelectCampo$idPadre"] = $Padre;
								$salidaTemporal["padresSelectValor$idPadre"] = $rs["padreSelect$idPadre"];
							}
						}
					}
					$salida[$campo][] = $salidaTemporal;
				}
			}
			if(count($optionSelect)){
				$salida[$campo] = $optionSelect;
			}
			$data['valoresSelect'] = array_merge($data['valoresSelect'],$salida);
			if(count($padres)){
				$salida = array();
				$salida[$campo] = $padres;
				$data['padresDelSelect'] = array_merge($data['padresDelSelect'],$salida);
			}
		}
		if(is_array($data['valoresSelect'])&&count($data['valoresSelect'])&&is_array($data['padresDelSelect'])&&count($data['padresDelSelect'])){
			foreach($data['valoresSelect'] AS $padre => $basura){
				foreach($data['padresDelSelect'] AS $hijo => $datosPadres){
					if(array_search($padre, $datosPadres)===0||array_search($padre, $datosPadres)){
						$data['hijosDelSelect'][$padre][] = $hijo;
					}
				}
			}
		}
	}
	if($IdentificadoresMultiples){
		$data['IdentificadoresMultiples'] = $IdentificadoresMultiples;
	}
	$data['success'] = true;
	// se obtiene la información para la generación del formulario
	$data['forForm'] = DATA;
	// $data['extra'] = CONFIGDATA;
	// retorno de la información
	return $data;
}
/**
 * Función de obtención de información por ítem especificado
 * @param	[DBInstance]	$db		Instancia de la base de datos
 * @return	[Array]			$data	Arreglo con la respuesta verdadera o falsa, según lo obtenido
 */
function edit($db)
{
	// declaración de variables de la función
	$data = ['success'=>false, 'msg'=>'Ocurrió un incidente al obtener la información. Favor de contactar al administrador.'];
	$info = $_POST;

	// Líneas nuevas para agregar identificadores múltiples
	$IdMu = obtenerIdentificadoresMultiples($info);
	$MultiIdentificador = $IdMu['query'];
	$DatosFormularioMultiIdentificador = $IdMu['campos'];

	$sql = "SELECT * FROM `".LNTBLCAT."` WHERE (`".ACTIVECOL."` = '1' OR `".ACTIVECOL."` LIKE 'S') AND ".( $MultiIdentificador ? $MultiIdentificador : "`".CONVERT['identificador']."` = '".$info['identificador']."'" )." ";
	$result = DB_query($sql, $db);
	// comprobación de existencia de la información
	if(DB_num_rows($result) == 0){
		$data['msg'] = 'No se encontraron los datos solicitados.';
		return $data;
	}
	// prcesamiento de la información obtenida
	while ($rs = DB_fetch_array($result)) {
		$content = [];
		foreach (DATA as $campo => $columna) { $content[$campo] = convierteDato($rs, $columna); }
		$content = array_merge($content,$DatosFormularioMultiIdentificador);
		$data['content'] = $content;
	}
	$data['success'] = true;
	// retorno de la información
	return $data;
}
/**
 * Función de actualización de los datos de un ítem específico
 * @param	[DBInstance]	$db		Instancia de la base de datos
 * @return	[Array]			$data	Arreglo con la respuesta verdadera o falsa según lo obtenido
 */
function update($db)
{
	// declaración de variables de la función
	$data = ['success'=>false,'msg'=>'Ocurrió un incidente al actualizar la información. Favor de contactar al administrador'];
	$info = $_POST;

	// Líneas nuevas para agregar identificadores múltiples
	$IdMu = obtenerIdentificadoresMultiples($info);
	$MultiIdentificador = $IdMu['query'];
	$DatosFormularioMultiIdentificador = $IdMu['campos'];

	$sql = "SELECT * FROM `".LNTBLCAT."` WHERE (`".ACTIVECOL."` = '1' OR `".ACTIVECOL."` LIKE 'S') AND ".( $MultiIdentificador ? $MultiIdentificador : "`".CONVERT['identificador']."` = '".$info['identificador']."'" )." ";
	$result = DB_query($sql, $db);
	// comprobación de consistencia de datos
	if(DB_num_rows($result) == 0){
		$data['msg'] = 'No se encontraron los datos solicitados.';
		return $data;
	}
	# comprobación de duplicidad de datos
	if(array_key_exists("multiidentificadorcampo", $info)){
		//// Necesita una validación extra para ver que realmente se está actualizando el identificador múltiple
		/*if($info['multiidentificadorcampo']!=""){
			$comprobacionIdentificadoresMultiples = compruebaDuplicadoIdentificadoresMultiples($db,$info);
			if($comprobacionIdentificadoresMultiples[0]){
				$data['msg'] = "El dato que desea generar ya se encuentra registrado.<br>(<strong>$comprobacionIdentificadoresMultiples[1]</strong>)";
				$data['content'] = show($db)['content'];
				return $data;
			}
		}*/
	}else{
		if(compruebaDuplicado($db, $info)){
			$data['msg'] = 'El dato que desea generar ya se encuentra registrado.(<strong>'.$info['descripcion'].'</strong>)';
			$data['content'] = show($db)['content'];
			return $data;
		}
	}
	# desarrollo de la transacción de eliminación
	DB_Txn_Begin($db);
	try {
		// obtención de la cadena de ejecución para la actualización
		$sqlUpdate = obtenUpdate($info);
		$resultUpdate = DB_query($sqlUpdate, $db);
		// comprobación de éxito de la información
		if($resultUpdate==true){
			$data['msg'] = MSGCONF[__FUNCTION__];
			$data['success'] = true;
			DB_Txn_Begin($db);
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
 * @return	[Array]			$data	Arreglo con la respuesta verdadera o falsa según lo obtenido
 */
function destroy($db)
{
	// declaración de variables de la función
	$data = ['success'=>false,'msg'=>'Ocurrió un incidente al momento de eliminar la información.'];
	$info = $_POST;

	// Líneas nuevas para agregar identificadores múltiples
	$IdMu = obtenerIdentificadoresMultiples($info);
	$MultiIdentificador = $IdMu['query'];
	$DatosFormularioMultiIdentificador = $IdMu['campos'];

	# comprobación de identificador
	if(empty($info['identificador'])){
		$data['msg'] = 'Es necesario indicar el identificador del registro';
		return $data;
	}
	# retorno de mensaje según ejecución de precondición
	if(!exePreReq($db, $info)){
		$data['msg'] = MSGCONF['error'];
		return $data;
	}
	# se determina el tipo de valor a almacenar en el campo Activo
	$valorInactivo = obtenerValorCampoActivo($db,false);
	# desarrollo de la transacción de eliminación
	DB_Txn_Begin($db);
	try {
		$sql = "UPDATE `".LNTBLCAT."` SET `".ACTIVECOL."` = '$valorInactivo'
			WHERE (`".ACTIVECOL."` = '1' OR `".ACTIVECOL."` LIKE 'S')
			AND ".( $MultiIdentificador ? $MultiIdentificador : "`".CONVERT['identificador']."` = '".$info['identificador']."'" )." ";
		$result = DB_query($sql, $db);
		// comprobación de éxito de la ejecución
		if($result==true){
			$data['msg'] = MSGCONF[__FUNCTION__];
			$data['success'] = true;
			$data['content'] = show($db)['content'];
			DB_Txn_Begin($db);
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

function obtenContenidoSelect($db)
{
	$data = ['success'=>true, 'content'=>[ ['label'=>'Seleccione una opción', 'title'=>'Seleccione una opción', 'value'=>'0'] ]];
	$sql = obtenSqlCampo($db, $_POST['row']);
	$result = DB_query($sql, $db);
	while ($rs = DB_fetch_array($result)) {
		$data['content'][] = [
			'label'=>utf8_encode($rs['label']),
			'title'=>utf8_encode($rs['label']),
			'value'=>$rs['valor']
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

/*********************************************** FUNCIONES UTILES ***********************************************/
/**
 * Función para la obtención del identificador de la función a la que se quiere acceder
 */
function obtenFuncion()
{
	$enc = new Encryption();
	$string = $enc->decode($_POST['urlSave']);
	$cad_get = explode("&", $string);
	$band = 0;
	foreach ($cad_get as $k => $value) {
		if ($k == 0) { continue; }
		$val_get = explode("=>", $value);
		if($val_get[0] == NAMEVARFUNC){ return $val_get[1]; }
	}
}
/**
 * Función de definición de las variables globales que se utilisaran en el programa, tomando como base
 * la configuración de la función dada
 * @param	[DBInstance]	$db	Instancia de la base de datos
 */
function defineVaribles($db)
{
	$sql = "SELECT DISTINCT `id_nu_panel_catalogo`, `ln_tbl_cat`, `ln_precondicion`, `ln_mensaje`, `ln_compuesto`, `ln_grid`, `ln_campo_activo`
		FROM `tb_cat_panel_catalogo` WHERE `ind_activo` = 1 AND `id_nu_funcion` = '".FUNID."' LIMIT 1";
	$result = DB_query($sql, $db);
	$id_catalogo = 1;
	// procesamiento de configuración de la función dada
	while ($rs = DB_fetch_array($result)) {
		$msg = empty($rs['ln_mensaje']) ? GENERICMSG
			: (is_array($rs['ln_mensaje'])? $rs['ln_mensaje'] : GENERICMSG);
		$campoActivo = empty($rs['ln_campo_activo'])? 'active' : $rs['ln_campo_activo'];
		define('LNTBLCAT', $rs['ln_tbl_cat']);
		define('LNCOMPUESTO', $rs['ln_compuesto']);
		define('LNGRID', $rs['ln_grid']);
		define('PRECOND', $rs['ln_precondicion']);
		// define('MSGCONF', $msg);
		eval("define('MSGCONF', $msg);");
		define('ACTIVECOL', $campoActivo);

		$id_catalogo = $rs['id_nu_panel_catalogo'];
	}
	$sqlFormat = sprintf(SQLDETALLE,$id_catalogo);
	$resultCampos = DB_query($sqlFormat, $db);
	$resultCamposConfig = DB_query($sqlFormat, $db);
	$resultCamposConv = DB_query($sqlFormat, $db);
	define('DATA', obtenDefData($resultCampos));
	define('CONFIGDATA', obtenDefDataConf($resultCamposConfig));
	define('CONVERT', obtenDefConversion($resultCamposConv));
}
/**
 * Función de obtención de definición de configuración
 * @param	[Object]	$resulset	Resultado de una ejecuíon de consulta
 * @return	[Array]		$data		Arreglo con la respuesta según lo obtenido
 */
function obtenDefData($resulset)
{
	$data = [];
	while ($rs = DB_fetch_array($resulset)) {
		$data[$rs['clave']] = [
			'col'=>trim($rs['col']),'tipo'=>trim($rs['tipo']),'len'=>$rs['len'],'row'=>$rs['id']
		];
	}
	return $data;
}
function obtenDefDataConf($resulset){
	$data = [];
	while ($rs = DB_fetch_array($resulset)) {
		$data[trim($rs['clave'])] = ['col'=>trim($rs['col']),'tipo'=>trim($rs['tipo']),'len'=>$rs['len'],'label'=>$rs['label']];
	}
	return $data;
}
/**
 * Función de obtención de definición de configuración
 * @param	[Object]	$resulset	Resultado de una ejecuíon de consulta
 * @return	[Array]		$data		Arreglo con la respuesta según lo obtenido
 */
function obtenDefConversion($resulset)
{
	$data = [];
	while ($rs = DB_fetch_array($resulset)) { $data[trim($rs['clave'])] = trim($rs['col']); }
	return $data;
}
/**
 * Función para la realización del formato del dato proporcionado
 * @param	[Object]	$resulset		Resultado de una ejecuíon de consulta
 * @param	[Array]		$detColumna		Arreglo con la configuración de formato
 * @return	elemento	Dato ya con el formato
 */
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
/**
 * Función para la ejecución de la precondición de eliminación
 * según la configuración dada
 * @param	DBInstance	$db Instancia de la base de datos
 * @return	Bolean		Comprobación de existencia de datos resultantes de la condición dada
 */
function exePreReq($db, $info)
{
	if(empty(PRECOND)){ return true; }
	if($info['multiidentificadorvalor']!=""){
		$sql = vsprintf(PRECOND,explode("<=>",$info['multiidentificadorvalor']));
	}else{
		$sql = sprintf(PRECOND,$info['identificador']);
	}
	$result = DB_query($sql, $db);
	return (DB_num_rows($result) == 0);
}
/**
 * Función para la obtención del sql para generar datos o ítems
 * @param	Array	$info Datos a ser procesados
 * @return 	String	Sql para la ejecución de la generación de datos
 */
function obtenInsercion($db, $info)
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
	$campos .= ", ".ACTIVECOL; $datos .= ", '".obtenerValorCampoActivo($db,true)."'";
	return " INSERT INTO `".LNTBLCAT."` ($campos) VALUES($datos) ";
}
/**
 * Función para la obtención del SQL de actualización del ítem
 * @param	Array	$info Arreglo con los datos para la construcción del string
 * @return	String	Cadena de texto con el SQL
 */
function obtenUpdate($info)
{
	$campos = ''; $flag = 0; $identificador = $info['identificador'];

	// Líneas nuevas para agregar identificadores múltiples
	$IdMu = obtenerIdentificadoresMultiples($info);
	$MultiIdentificador = $IdMu['query'];
	$DatosFormularioMultiIdentificador = $IdMu['campos'];

	unset($info['identificador']);
	unset($info['multiidentificadorcampo']);
	unset($info['multiidentificadorvalor']);
	foreach ($info as $input => $valor) {
		if(!array_key_exists($input, DATA)){ continue; }
		if($flag!=0){ $campos .= ', '; }
		$data = DATA[$input];
		$campos .= $data['col']." = ".($data['tipo']=='string'? utf8_decode(" '$valor'") : " $valor");
		$flag++;
	}
	return " UPDATE `".LNTBLCAT."` SET $campos WHERE (`".ACTIVECOL."` = '1' OR `".ACTIVECOL."` LIKE 'S') AND ".( $MultiIdentificador ? $MultiIdentificador : "`".CONVERT['identificador']."` = '".$identificador."'" )." ";
}
/**
 * Función para la actualización de cualqueir elemento encontrado
 * a partir de la descripción que se da para ser evaluada.
 * @param	DbInstance		$db		Instancia de la base de datos
 * @param	Array			$info	Información proporcionada por el cliente
 */
function updateWhenInserting($db, $info)
{
	// Líneas nuevas para agregar identificadores múltiples
	$IdMu = obtenerIdentificadoresMultiples($info);
	$MultiIdentificador = $IdMu['query'];
	$DatosFormularioMultiIdentificador = $IdMu['campos'];

	DB_Txn_Begin($db);
	$comp = false;
	$preSql = "SELECT * FROM `".LNTBLCAT."` WHERE (`".ACTIVECOL."` = '0' OR `".ACTIVECOL."` LIKE 'N') AND ".( $MultiIdentificador ? $MultiIdentificador : "`".CONVERT['descripcion']."` = '".$info['descripcion']."'" )." ";
	$preResult = DB_query($preSql, $db);
	if(DB_num_rows($preResult) == 0){ return $comp; }
	$valorActivo = obtenerValorCampoActivo($db,true);

	try {
		$sql = "UPDATE `".LNTBLCAT."` SET `".ACTIVECOL."` = '$valorActivo' WHERE ".( $MultiIdentificador ? $MultiIdentificador : "`".CONVERT['descripcion']."` = '".$info['descripcion']."'" )." ";
		$result = mysqli_query($db, $sql);
		DB_Txn_Commit($db);
		$comp = true;
	} catch (Exception $e) {
		// captura del error
		DB_Txn_Begin($db);
	}

	if($MultiIdentificador){
		try {
			$sql = obtenUpdate($info);
			$result = mysqli_query($db, $sql);
			DB_Txn_Commit($db);
			$comp = true;
		} catch (Exception $e) {
			// captura del error
			DB_Txn_Begin($db);
		}
	}
	return $comp;
}
/**
 * Función para obtener el sql que se usará para los elementos select que se generen
 * @param	{DBInstance}	$db				Instancia de la base de datos
 * @param	{Integer}		$identificador	Identificador unido del registro
 * @return	{String}		retorno de sql
 */
function obtenSqlCampo($db, $identificador)
{
	$sql = "SELECT `ln_select` as `querytext` FROM `tb_cat_panel_detalle` WHERE `id_nu_panel_detalle` = '$identificador'";
	$result = DB_query($sql, $db);
	return DB_fetch_array($result)['querytext'];
}
/**
 * Función para la existencia del elelmentos a registrar filtrado por descripción
 * @param	{DBInstance}	$db		Instacia de la base de datos
 * @param	{Array}			$datos	Arreglo con la información que se desea balidar
 * @return	{Boolean}		Retorno de evaluacion
 */
function compruebaDuplicado($db, $datos)
{
	$sql = "SELECT * FROM `".LNTBLCAT."` WHERE `".CONVERT['descripcion']."` = '".$datos['descripcion']."' ";
	$result = DB_query($sql, $db);
	return DB_num_rows($result);
}
function compruebaDuplicadoIdentificadoresMultiples($db,$info){
	$identificador = $info['identificador'];

	$IdMu = obtenerIdentificadoresMultiples($info);
	$MultiIdentificador = $IdMu['query'];
	$DatosFormularioMultiIdentificador = $IdMu['campos'];

	$sql = "SELECT * FROM `".LNTBLCAT."` WHERE ".( $MultiIdentificador ? $MultiIdentificador : "`".CONVERT['identificador']."` = '".$identificador."'" ); //." AND `".CONVERT['descripcion']."` = '".$info['descripcion']."' "
	$result = DB_query($sql, $db);
	$datoConsultado = DB_fetch_array($result);
	return array( DB_num_rows($result), utf8_encode( $datoConsultado[CONVERT['identificador']]." - ".$datoConsultado[CONVERT['descripcion']] ) );
}
/**
 * Función de obtención de información de querys y estructuras para Identificadores Múltiples
 * @return	[Array]	$data	Arreglo con la respuesta verdadera o falsa según lo obtenido
 */
function obtenerIdentificadoresMultiples($info){
	// declaración de variables de la función
	$DatosFormulario = [];
	if($info['multiidentificadorcampo']&&$info['multiidentificadorvalor']){
		$DatosFormulario['multiidentificadorcampo'] = $info['multiidentificadorcampo'];
		$DatosFormulario['multiidentificadorvalor'] = $info['multiidentificadorvalor'];
	}
	$MultiIdentificador = "";
	$MultiIdentificadorCampo = $info['multiidentificadorcampo'] ? explode("<=>",$info['multiidentificadorcampo']) : "" ;
	$MultiIdentificadorValor = $info['multiidentificadorvalor'] ? explode("<=>",$info['multiidentificadorvalor']) : "" ;
	if(is_array($MultiIdentificadorCampo)&&!is_array($MultiIdentificadorValor)){
		foreach ($MultiIdentificadorCampo AS $miID => $miVar) {
			if(array_key_exists($MultiIdentificadorCampo[$miID], $info)){
				$MultiIdentificadorValor[$miID] = $info[$MultiIdentificadorCampo[$miID]];
			}
		}
	}
	if(is_array($MultiIdentificadorCampo)&&is_array($MultiIdentificadorValor)){
		if(count($MultiIdentificadorCampo)==count($MultiIdentificadorValor)){
			$MultiIdentificador = array();

			foreach ($MultiIdentificadorCampo AS $miID => $miVar) {
				$MultiIdentificador[$miID] = "`".$MultiIdentificadorCampo[$miID]."` = '".$MultiIdentificadorValor[$miID]."'";
			}

			$MultiIdentificador = implode(" AND ",$MultiIdentificador);
			$MultiIdentificador = "( $MultiIdentificador )";
			$data['success'] = true;
		}
	}

	$data['query'] = $MultiIdentificador;
	$data['campos'] = $DatosFormulario;

	return $data;
}
/**
 * Función que devuelve el valor positivo o negativo del campo Activo
 * @param 	{DBInstance}	$db				Instacia de la base de datos
 * @param 	{Boolean}		$tipoValor		Indicador sobre si debe devolver el valor de Activo o Inactivo
 * @return	{String}		$valorInactivo	Retorno del valor a usar en el campo Activo
 */
function obtenerValorCampoActivo($db,$tipoValor){
	$valorInactivo = "0";
	$valorInteger = array( true => "1", false => "0" );
	$valorString = array( true => "S", false => "N" );

	$sql = "SELECT `".ACTIVECOL."` FROM `".LNTBLCAT."` WHERE `".ACTIVECOL."` = '1' OR `".ACTIVECOL."` = '0' GROUP BY `".ACTIVECOL."`";
	$result = DB_query($sql, $db);
	$valorInactivo = ( DB_num_rows($result) ? $valorInteger[$tipoValor] : $valorInactivo );

	$sql = "SELECT `".ACTIVECOL."` FROM `".LNTBLCAT."` WHERE `".ACTIVECOL."` LIKE 'S' OR `".ACTIVECOL."` LIKE 'N' GROUP BY `".ACTIVECOL."`";
	$result = DB_query($sql, $db);
	$valorInactivo = ( DB_num_rows($result) ? $valorString[$tipoValor] : $valorInactivo );

	return $valorInactivo;
}
