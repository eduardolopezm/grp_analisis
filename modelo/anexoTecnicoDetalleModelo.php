<?php
/**
 * Panel de anexo técnico.
 *
 * @category     Panel
 * @package      ap_grp
 * @version      0.1
 * @link /anexoTecnicoDetalleModelo.php
 * Fecha Creación: 29.12.17
 * Se genera el presente programa para la visualización de la información
 * de los anexos técnicos que se generan para las inquisiciones.
 */

/**
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
    error_reporting(E_ALL);
/**/

$PageSecurity = 1;
$PathPrefix = '../';
$funcion=2322;
// $funcion=2323;
session_start();
include($PathPrefix . 'config.php');
include $PathPrefix . "includes/SecurityUrl.php";
include($PathPrefix . 'includes/ConnectDB.inc');
include($PathPrefix . 'includes/SecurityFunctions.inc');
include($PathPrefix . 'includes/SQL_CommonFunctions.inc');
// Include para el periodo
include($PathPrefix . 'includes/DateFunctions.inc');
/* DECLARACION DE CONSTANTES */
# tipo de movimiento ubicado en las tabas "systypesinvtrans" y "systypescat"
define('TYPEMOV', 51);
# arreglo que se utilisara para consultar la existencia
define('WHEREEXIST', array(
    'selectUnidadNegocio'=>'nu_tagref',// ur
    'selectUnidadEjecutora'=>'nu_ue',// uni eje
    'area'=>'sn_area',
    'proceso'=>'nu_proceso',
    'partida'=>'nu_partida',
    'bienServicio'=>'txt_bien_serevicio',
    'folio'=>'nu_anexo',
    'id'=>'id_anexo'
));
define('DATA', [
    'selectUnidadNegocio'=>['col'=>"nu_tagref",'type'=>'string'],
    'selectUnidadEjecutora'=>['col'=>"nu_ue",'type'=>'string'],
    'area'=>['col'=>"sn_area",'type'=>'string'],
    'proceso'=>['col'=>"nu_proceso",'type'=>'string'],
    'partida'=>['col'=>"nu_partida",'type'=>'int'],
    'firma'=>['col'=>"sn_firma",'type'=>'string'],
    'antecedentes'=>['col'=>"txt_descripcion_antecedentes",'type'=>'string'],
    'justificacion'=>['col'=>"txt_justificacion",'type'=>'string'],
    'viavilidad'=>['col'=>"ln_viabilidad",'type'=>'string'],
    'bienServicio'=>['col'=>"txt_bien_serevicio",'type'=>'string'],
    'descBienServicio'=>['col'=>"txt_desc_bien_serevicio",'type'=>'string'],
    'cantidad'=>['col'=>"nu_cantidad",'type'=>'int'],
    'vobo'=>['col'=>'ln_visto_bueno, ln_vobo_requiriente','type'=>'string'],
    'selectEstatusGeneral'=>['col'=>'ind_status','type'=>'int'],
    'garantia'=>['col'=>'nu_garantia','type'=>'int'],
    'fecha'=>['col'=>'dt_fecha_creacion','type'=>'string'],
    'requi'=>['col'=>'nu_requisicion','type'=>'int'],
    'costo'=>['col'=>'amt_costo','type'=>'decimal'],
    'total'=>['col'=>'amt_total','type'=>'decimal'],
    'status'=>['col'=>'ind_status','type'=>'string']
]);
define('CONVERT', [
    'selectUnidadNegocio'=>'nu_tagref',
    'selectUnidadEjecutora'=>'nu_ue',
    'area'=>'sn_area',
    'proceso'=>'nu_proceso',
    'partida'=>'nu_partida',
    'firma'=>'sn_firma',
    'antecedentes'=>'txt_descripcion_antecedentes',
    'justificacion'=>'txt_justificacion',
    'viavilidad'=>'ln_viabilidad',
    'bienServicio'=>'txt_bien_serevicio',
    'descBienServicio'=>'txt_desc_bien_serevicio',
    'cantidad'=>'nu_cantidad',
    'vobo'=>'ln_vobo_requiriente',
    'selectEstatusGeneral'=>'ind_status',
    'selectRazonSocial'=>''
]);
define('NEWCONVERT', [
    'bienServicio'=>'txt_bien_serevicio',
    'descBienServicio'=>'txt_desc_bien_serevicio',
    'cantidad'=>'nu_cantidad',
    'costo' => 'amt_costo',
    'total' => 'amt_total',
    'garantia' => 'nu_garantia',
    'selectEstatusGeneral'=>'ind_status',
    'numeral'=>'id_anexo',
    'selectUnidadNegocio'=>'nu_tagref',
    'selectUnidadEjecutora'=>'nu_ue',
    'unidad'=>'units',
    'bienServicioName'=>'description',
    'partida'=>'nu_partida',
    'clave'=>'txt_bien_serevicio'
]);

define('TBL', 'tb_cnfg_anexo_tecnico');

define('DECPLACE', !empty($_SESSION['DecimalPlaces'])? $_SESSION['DecimalPlaces'] : 2);

/*********************************************** DESARROLLO DE FUNCIONES ***********************************************/
/**
 * Funcion para la creacion de nuevos anexos tecnicos conforme a las espesificaciones del usuario
 * @param  DB    $db Instancia de conexión.
 * @return array     Datos que el cliente necesita ver.
 */
function store($db)
{
    $data = array('success'=>false,'msg'=>'Ocurrio un incedente inesperado. Favor de contactar con el administrador');
    $info = $_POST;
    $rows = $info['rows'];
    unsetFromArray($info, ['method','rows']);
    if (empty($info['folio'])) {
        unsetFromArray($info, ['folio']);
    }
    $exite = ($info['selectUnidadNegocio']!=-1 && $info['selectUnidadEjecutora']!=-1);
    // consulta de existencia de anexo similar con cada una de los bienes o serbicios
    $bienServicio = '';
    if ($exite) {
        DB_Txn_Begin($db);
        try {
            # se obtiene el siguiente registro
            $nu_anexo = GetNextTransNo(TYPEMOV, $db);
            $result = false;
            foreach ($rows as $key => $item) {
                $d = array_merge($info, $item, [ 'proceso'=>( $key + 1 ) ]);
                $sql = getInsert($d, $nu_anexo);
                $result = DB_query($sql, $db);
                if ($result==true) {
                    $enc = new Encryption;
                    $url = '&folio=>'.$nu_anexo;
                    $data = array(
                        'success' => true,
                        'msg' => 'Se ha guardado la información del Anexo Técnico '.$nu_anexo,
                        'folio' => $nu_anexo,
                        'url'=>$enc->encode($url)
                    );
                }
                else {
                	# en caso de error no cachado por try se hace rollback
                    break;
                }
            }// fin for each
            // comprobacion para consolidacion de los datos en base
            if ($result) {
                DB_Txn_Commit($db);
            } else {
                DB_Txn_Rollback($db);
            }
        } catch (Exception $e) {
            $data['msg'] = 'Ocurrio un incedente inesperado. '.$e->getMessage();
            DB_Txn_Rollback($db);
        }
    } else {
        $data['msg'] = "Los datos de la UR y UE no pueden  ir vacíos";
    }
    return $data;
}

function show($db)
{
    $data = array('success'=>false,'msg'=>'Ocurrio un incedente inesperado. Favor de contactar con el administrador',
        'content'=>array('head'=>[], 'data'=>[], 'partida'=>[]));
    $info = (object)$_POST;
    $exist = getIfExist($db, $info, true);
    if (DB_num_rows($exist)) {
        # devclaracion de variables
        $dataInt = ['costo','total'];
        $flag = 0;
        $identificadores = array();
        $productsOfPartida = array();

        # consulta de latos para la cavecera
        $data['content']['head'] = getHeadAnexo($db, $info);

        # procesamiento de lineas
        while ($rs = DB_fetch_assoc($exist)) {
            $content = array();
            foreach (NEWCONVERT as $key => $value) {
                if (!empty($value) && !empty($rs[$value])) {
                    if (in_array($key, $dataInt)) {
                        $content[$key] = number_format($rs[$value], DECPLACE, '.', ',');
                    } else {
                        if ($key=='numeral') {
                            $content[$key] = utf8_encode($rs[$value]);
                            $identificadores[] = $rs[$value];
                        } elseif ($key=='selectEstatusGeneral') {
                            $content[$key] = getStatusString($rs, $value);
                        } else {
                            // extraccion de los productos de las partidas guradadas
                            if ($key == 'partida') {
                                if ( !in_array($rs[$value], $productsOfPartida) ) {
                                    $_POST['partida'] = $rs[$value];
                                    $productsOfPartida[ $rs[$value] ] = getProducts($db)['content'];
                                    unset( $_POST['partida'] );
                                }
                            }

                            // colocacion de forma normal de los datos
                            $content[$key] = utf8_encode($rs[$value]);
                        }
                    }

                } elseif ($key == 'unidad' || $key == 'bienServicioName') {
                    $content[$key] = getSubQuery($db, $rs['txt_bien_serevicio'], $key, $value);
                } elseif ($key=='garantia') {
                    $content[$key] = $rs[$value];
                }
            }
            $data['content']['data'][] = $content;
        }
        # agregado de la partida
        $data['content']['partida'] = getPartida($db)['content'];
        # agregado de los productos por partida
        $data['content']['productsOfPartida'] = $productsOfPartida;
        # datos de los identificadores que se tienen actualmente
        $data['content']['identificadores'] = $identificadores;
        # agregado de productos
        $data['success'] = true;
        $data['msg'] = 'Se cargo con exito la información solicitada.';
    } else {
        $data['msg'] = 'No se encontró la información solicitada. Inténtelo mas tarde o contacte con el administrador.';
    }
    return $data;
}

function update($db)
{
    $data = array('success'=>false,'msg'=>'Ocurrió un incidente inesperado. Favor de contactar con el administrador');
    $info = $_POST;
    $rows = $info['rows'];
    $deleteData = !empty($info['delete'])?$info['delete']:[];
    $folio = $info['folio'];
    $targref = $info['folio'];
    $identificadores = $info['identificadores'];
    unsetFromArray($info, ['method','rows','delete','identificadores']);
    $m = ['folio'=>$folio];
    $exist = getIfExist($db, $m);
    DB_Txn_Begin($db);
    if ($exist) {
        try {
        	# 1 .- recuperando la fecha de creación(dt_fecha_creacion), el estatus actual(ind_status) y la información de la creación(txt_informacion_creacion)
        	$sqlSelect = "SELECT dt_fecha_creacion, ind_status, txt_informacion_creacion FROM ".TBL." WHERE nu_type = '".TYPEMOV."' AND nu_anexo = '".$folio."' LIMIT 1 ";
        	$resultSelect = DB_query($sqlSelect, $db);
        	$datosAnterioresSelect = DB_fetch_array($resultSelect);

        	# 2 .- eliminar todos los registros del anexo
        	$sqlDelete = "DELETE FROM ".TBL." WHERE nu_type = '".TYPEMOV."' AND nu_anexo = '".$folio."' ";
        	$resultDelete = DB_query($sqlDelete, $db);

        	# 3 .- generar las inserciones de los registros con los nuevos datos
        	$confirmacion = 0;
        	foreach ($rows as $key => $row) {
				$agregados = [
					'fecha'		=>	$datosAnterioresSelect['dt_fecha_creacion'],
					'status'	=>	$datosAnterioresSelect['ind_status'],
					'proceso'	=>	( $key + 1 )
				];
				$newRow = array_merge($info, $row, $agregados);
				$sqlInsert = getInsert($newRow, $folio);
				$resultInsert = DB_query($sqlInsert, $db);
				if ($resultInsert != true){
					$confirmacion = 1;
				}
        	}
        	# 4 .- confirmación de los datos en caso de que todo saliera bien
        	if ($confirmacion == 0){
				$data['msg'] = 'Se realizaron con éxito las modificaciones del anexo '.$folio;
				$data['success'] = true;
        		DB_Txn_Commit($db);
        	} else {
        		DB_Txn_Rollback($db);
        	}
        } catch (Exception $e) {
            $data['msg'] = 'Ocurrió un incidente inesperado. '.$e->getMessage();
            DB_Txn_Rollback($db);
        }
    } else {
        $data['msg'] = 'No se encontró la información solicitada.';
    }
    return $data;
}

/**
 * Funcion para la obtencion de todos los productos
 * que se pueden agregar a un anexo
 * @param  DB $db Instancia de base de datos
 * @return array  Datos de los productos
 */
function getProducts($db)
{
    $data = array('success'=>false, 'msg'=>'No se encontraron datos');
    $partida = !empty($_POST['partida'])?$_POST['partida']:'';
    $sql = 'SELECT DISTINCT stockid as id, description as des, units as um FROM stockmaster';
    if ($partida!='') {
        $sql = "SELECT DISTINCT sm.stockid as id, sm.description as des, sm.units as um, tcpp.partidacalculada as partida
		FROM tb_cat_partidaspresupuestales_partidaespecifica as tcpp
		INNER JOIN tb_partida_articulo as tpa ON tcpp.partidacalculada= tpa.partidaEspecifica
		INNER JOIN stockmaster as sm ON tpa.eq_stockid= sm.eq_stockid
		WHERE tcpp.ccap IN(2,3) AND tcpp.partidacalculada NOT IN (22106,26103) AND tcpp.partidacalculada = '$partida'
		ORDER BY id, partida";
    }
    $result = DB_query($sql, $db);
    if (DB_num_rows($result)) {
        $rows = array();
        $labels = array();
        if ($partida!='') {
            while ($rs = DB_fetch_assoc($result)) {
                $rows[$rs['id']] = [
                    'id'=>$rs['id'],
                    'desc'=>utf8_encode($rs['des']),
                    'um'=>utf8_encode($rs['um'])
                ];
            }
        } else {
            while ($rs = DB_fetch_assoc($result)) {
                $rows[$rs['id']] = [
                    'id'=>$rs['id'],
                    'desc'=>utf8_encode($rs['des']),
                    'um'=>utf8_encode($rs['um'])
                ];
                $labels[] = [
                    'label'=>utf8_encode($rs['des']),
                    'title'=>utf8_encode($rs['des']),
                    'value'=>$rs['id']
                ];
            }
        }
        $data['content'] = ($partida!=''?$rows:['rows'=>$rows,'labels'=>$labels]);
        $data['success'] = true;
    }
    return $data;
}

function getPartida($db)
{
    $data = ['success'=>false,'msg'=>'Ocurrio un incidente al obtener la información'];
    $sql = "SELECT DISTINCT
			tcpp.partidacalculada, tcpp.descripcion AS partidadescripcion, IF(SUBSTRING(tpa.partidaEspecifica,1,1)=2,'B','D') as tipo
		FROM tb_cat_partidaspresupuestales_partidaespecifica as tcpp
		INNER JOIN tb_partida_articulo as tpa ON tcpp.partidacalculada= tpa.partidaEspecifica
		WHERE
			tcpp.ccap IN(2,3)
			AND tcpp.partidacalculada NOT IN (22106,26103)
		ORDER BY tcpp.partidacalculada";
    $result = DB_query($sql, $db);
    if (DB_num_rows($result)) {
        $labels = array(
            [
                'label'=>'Seleccionar',
                'value'=>''
            ]
        );
        while ($rs = DB_fetch_assoc($result)) {
            $labels[] = [
                'label'=>utf8_encode($rs['partidacalculada']),
                'value'=>$rs['partidacalculada']
            ];
        }
        $data['content'] = $labels;
        $data['success'] = true;
    } else {
        $data['msg']='No se encontraron datos';
    }

    return $data;
}

function getHeadAnexo($db, $info)
{
    $sqlHead = "SELECT DISTINCT t.`legalid`, ant.`nu_tagref`, ant.`nu_ue`, ant.`txt_descripcion_antecedentes` as antecedentes FROM ".TBL." as ant "
        ." INNER JOIN tags as t ON t.`tagref` = ant.`nu_tagref` WHERE ant.`nu_type` = ".TYPEMOV." AND ant.`nu_anexo` = $info->folio";
    $resultHead = DB_query($sqlHead, $db);
    $proHead = DB_fetch_assoc($resultHead);

    return [
        'selectRazonSocial'=>$proHead['legalid'],
        'selectUnidadNegocio'=>$proHead['nu_tagref'],
        'selectUnidadEjecutora'=>$proHead['nu_ue'],
        'antecedentes'=>utf8_encode($proHead['antecedentes'])
    ];
}

/**
 * Funcion que retorna el numero de coincidencias encontradas
 * por la consulta realizada.
 * @param  DB    &$db   Instancia de base de datos
 * @param  array $info  Areglo que se usara para construir la consulta
 * @return integer      Retorna la cantidad encontrada
 */
function getIfExist(&$db, $info, $retData=false)
{
    $sql = sprintf('SELECT * FROM %s WHERE ', TBL);
    $flag = 0;
    foreach ($info as $key => $value) {
        # evalua si existe el indice en el arreglo para ser usado
        if (array_key_exists($key, WHEREEXIST)) {
            if ($flag!=0) {
                $sql .= ' AND ';
            }
            $sql .= WHEREEXIST[$key] . " = '$value' ";
            $flag++;
        }
    }
    $sql .= ' AND nu_type = ' . TYPEMOV;
    $result = DB_query($sql, $db);
    $rows = DB_num_rows($result);
    // retorno de informacion segin tipo de retorno
    return ($retData? $result : $rows);
}

/**
 * Funcion para la generacion del QUERY para la incercion en base de datos
 * del nuevo anexo técnico.
 * NOTE: Se pueden tomar algunos usos de la funcion "sprintf" para futuras referencias.
 * @param  array   $info  Arreglo con los datos necesarios para ser incertados
 * @param  integer $anexo Dato tomado de la funcion "GetNextTransNo".
 * @return string         Retorna el string del sql que sera usado para la incercion.
 */
function getInsert($info, $anexo)
{
    $campos = '';
    $datos = '';
    $flag = 0;
    $extra = 'nu_anexo, nu_type, txt_informacion_creacion';// campos personalizados
    $comentario = "usuario alta:" . $_SESSION['UserID'];// comentario para el desarrollador
    $datosExtra = sprintf("'%s', %s, '%s'", $anexo, TYPEMOV, $comentario);// inclucion de datos uso por la constante
    # procesamiento de campos a insertar
    foreach ($info as $key => $value) {
        if (array_key_exists($key, DATA)) {
            $data  = DATA[$key];
            if ($flag!=0) {
                $campos .= ', ';
                $datos .= ', ';
            }
            # creacion y baciado de datos
            if ($key!='vobo') {
                $campos .= ' '.$data['col'];
                $datos .= $data['type']=='string'? utf8_decode(" '$value'") : " $value";
            } else {
                # se considera que los siguientes campos son string
                $campos .= " {$data['col']}";
                $datos .= " '$value', '$value'";
            }
            $flag++;
        }
    }
    $sql = "INSERT INTO %s ($campos, %s) VALUES ($datos, %s)";// cadena temporal
    $sql = sprintf($sql, TBL, $extra, $datosExtra);// cadena formateada con los datos extra
    return $sql;
}

function getUpdate($data, $updateDelete=false)
{
    if (!empty($data['id'])) {
        $where = sprintf(' id_anexo = %d AND nu_anexo = %d AND nu_type = %d', $data['id'], $data['folio'], TYPEMOV); // generacion de where del update
    } else {
        $where = sprintf(' nu_anexo = %d AND nu_type = %d', $data['folio'], TYPEMOV); // generacion de where del update
    }
    $sqlTemp = sprintf("UPDATE %s SET %%s WHERE %s", TBL, $where); // agregado del where al query
    $set = '';
    $flag = 0;
    # procesamiento de los datos a actualizar
    foreach ($data as $key => $value) {
        if (array_key_exists($key, DATA)) {
            $column  = DATA[$key];
            if ($flag!=0) {
                $set .= ', ';
            }
            # creacion y baciado de datos
            if ($key!='vobo') {
                $set .= " {$column['col']} = ".($column['type']=='string'? " '$value'" : " $value");
            } else {
                # se considera que los siguientes campos son string
                $col = explode(', ', $column['col']);
                $set .= " {$col[0]} = '$value', {$col[1]} = '$value'";
            }
            $flag++;
        } // fin array_key_exists
    }
    $set .= $updateDelete? ", txt_informacion_creacion = '%s'" : "";
    $sql = sprintf($sqlTemp, $set);
    return $sql;
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
    foreach ($toUnset as $key => $value) {
        if (!empty($data[$key])) {
            $old[$key] = $data[$key];
            unset($data[$key]);
        }
    }
    return $old;
}

function getStatusString($data, $filedata, $number=false)
{
    $txt = $number? 0 : 'SIN ESTATUS';
    if ($number) {
        switch ($data[$filedata]) {
            case 'CAPTURADO':
                $txt = 1;
                break;
            case 'POR ASIGNAR':
                $txt = 2;
                break;
            case 'ASIGNADO':
                $txt = 3;
                break;
        }
    } else {
        switch ($data[$filedata]) {
            case 1:
                $txt = 'CAPTURADO';
                break;
            case 2:
                $txt = 'POR ASIGNAR';
                break;
            case 3:
                $txt = 'ASIGNADO';
                break;
        }
    }
    return $txt;
}

function getSubQuery($db, $rs, $key, $filedata)
{
    $sql = sprintf("SELECT %s FROM stockmaster WHERE stockid='%s'", $filedata, $rs);
    $result = DB_query($sql, $db);
    $res = DB_fetch_assoc($result);
    return utf8_encode($res[$filedata]);
}

/*********************************************** DESARROLLO DE FUNCIONES ***********************************************/

/* EJECUCION DE FUNCIONES */
$data = call_user_func_array($_POST['method'], [$db]);
/* MODIFICACION DE HEADER */
header('Content-type:application/json;charset=utf-8');
/* ENVIO DE INFORMACIÓN */
echo json_encode($data);
