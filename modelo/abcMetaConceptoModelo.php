<?php
/** 
 * Modelo para el ABC de Fuente de Financiamiento
 *
 * @category ABC
 * @package ap_grp
 * @author Jesús Reyes Santos <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 07/11/2019
 * Fecha Modificación: 07/11/2019
 * Se realizan operación pero el Alta, Baja y Modificación. Mediante las validaciones creadas para la operación seleccionada
 */


//ini_set('display_errors', 1);
//ini_set('log_errors', 1);
//error_reporting(E_ALL);
//ini_set('error_log', dirname(__FILE__) . '/error_log.txt');

$PageSecurity = 1;
$PathPrefix = '../';
//include($PathPrefix.'includes/session.inc');
session_start();
include($PathPrefix.'abajo.php');
include($PathPrefix . 'config.php');
include($PathPrefix . 'includes/ConnectDB.inc');
if ($abajo) {
    include($PathPrefix . 'includes/LanguageSetup.php');
}
$funcion=2537;
include($PathPrefix.'includes/SecurityFunctions.inc');
include($PathPrefix.'includes/SQL_CommonFunctions.inc');

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

if ($option == 'mostrarCatalogo') {
    $loccode = $_POST['loccode'];

    $info = array();
    $SQL ="SELECT
    tb_cat_meta.id_meta as id_meta,
    tb_cat_meta.loccode as loccode,
    tb_cat_meta.nu_anio as nu_anio,
    tb_cat_meta.meta as meta,
    tb_cat_meta.nu_mes as nu_mes,
    tb_cat_meta.dtm_fecha_efectiva as dtm_fecha_efectiva,
    tb_cat_meta.userid as userid,
    CONCAT(`tb_cat_meta`.`loccode`, ' - ' , `locations`.`locationname`) AS 'name_descripcion'
    -- CONCAT(`tb_cat_meta`.`nu_mes`, ' - ' , `stockmaster`.`description`) AS 'parcialConcatenado'
    FROM tb_cat_meta
    JOIN locations on (locations.loccode = tb_cat_meta.loccode)
    WHERE  tb_cat_meta.nu_estatus = 1
    ORDER BY tb_cat_meta.loccode ASC";
    $ErrMsg = "No se obtuvieron las metas";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'ObjetoPrincipal' => $myrow ['name_descripcion'],
                'Mes' => $myrow ['nu_mes'],
                'Anio' => $myrow ['nu_anio'],
                'Meta' => $myrow ['meta'],
                'Modificar' => '<a onclick="fnModificar('.$myrow ['id_meta'].',\''.$myrow ['loccode'].'\',\''.$myrow ['nu_mes'].'\',\''.$myrow ['nu_anio'].'\',\''.$myrow ['meta'].'\')"><span class="glyphicon glyphicon-edit"></span></a>',
                'Eliminar' => '<a onclick="fnEliminar('.$myrow ['id_meta'].')"><span class="glyphicon glyphicon-trash"></span></a>');
    } 
    
    
    // Columnas para el GRID
    $columnasNombres .= "[";
    $columnasNombres .= "{ name: 'ObjetoPrincipal', type: 'string' },";
    $columnasNombres .= "{ name: 'Mes', type: 'string' },";
    $columnasNombres .= "{ name: 'Anio', type: 'string' },";
    $columnasNombres .= "{ name: 'Meta', type: 'string' },";
    $columnasNombres .= "{ name: 'Modificar', type: 'string' },";
    $columnasNombres .= "{ name: 'Eliminar', type: 'string' },";
    $columnasNombres .= "]";
    // Columnas para el GRID
    $columnasNombresGrid .= "[";
    $columnasNombresGrid .= " { text: 'Objeto/Principal', datafield: 'ObjetoPrincipal', width: '50%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Mes', datafield: 'Mes', width: '10%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Año', datafield: 'Anio', width: '10%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: '%Meta', datafield: 'Meta', width: '10%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Modificar', datafield: 'Modificar', width: '10%', cellsalign: 'center', align: 'center', hidden: false, sortable: false, filterable: false },";
    $columnasNombresGrid .= " { text: 'Eliminar', datafield: 'Eliminar', width: '10%', cellsalign: 'center', align: 'center', hidden: false, sortable: false, filterable: false },";
    $columnasNombresGrid .= "]";

    $nombreExcel = str_replace(' ', '_', traeNombreFuncionGeneral($funcion, $db)).'_'.date('dmY');

    $contenido = array('datos' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid, 'nombreExcel' => $nombreExcel);
    $result = true;
}

        

        if ($option == 'AgregarCatalogo') {

            $id_meta = $_POST['id_meta'];
            $loccode = $_POST['loccode'];
            $meta = $_POST['meta'];
            $nu_anio = $_POST['anio'];
            $nu_mes = $_POST['nu_mes'];
            $proceso = $_POST['proceso'];

            if ($proceso == 'Modificar') {
                $info = array();
                $clave = $loccode.'_'.$nu_mes.'_'.$nu_anio;
                $SQL = "UPDATE  tb_cat_meta SET tb_cat_meta.clave = '".$clave."', tb_cat_meta.loccode = '$loccode', tb_cat_meta.nu_mes = '$nu_mes', tb_cat_meta.nu_anio = '$nu_anio', tb_cat_meta.meta = '$meta' WHERE  tb_cat_meta.id_meta = '$id_meta'";
                $ErrMsg = "No se agrego la informacion de ".$loccode;
                $TransResult = DB_query($SQL, $db, $ErrMsg);
        
                $contenido = "Se modificó el registro ".$loccode." con éxito";
                $result = true;
            } else {
                if(fnValidarExiste($loccode, $db)){
                    
                    $SQL = "SELECT * FROM tb_cat_meta WHERE loccode = '".$loccode."' and nu_mes = '".$nu_mes."' and nu_anio = '".$nu_anio."' and nu_estatus = 1 ORDER BY loccode ASC";
                    $ErrMsg = "No se obtuvieron los datos";
                    $TransResult = DB_query($SQL, $db, $ErrMsg);
                    if (DB_num_rows($TransResult) == 0) {
                        $info = array(); 
                        
                        $SQL = "INSERT INTO `tb_cat_meta` ( `loccode`, `nu_mes`, `nu_anio`, `meta`, `nu_estatus`, `userid`, `clave`)
                                VALUES( '".$loccode."', '".$nu_mes."', '".$nu_anio."', '".$meta."', 1, '".$_SESSION['UserID']."', '".$loccode.'_'.$nu_mes.'_'.$nu_anio."')";
                        $ErrMsg = "No se agrego la informacion de ". $loccode;
                        $TransResult = DB_query($SQL, $db, $ErrMsg);
        
                        $contenido = "Se agregó el registro ".$loccode."  con éxito";
                        $result = true;
                    } else {
                        $myrow = DB_fetch_array($TransResult);
        
                        if($myrow['nu_estatus']==1){
                            $Mensaje = "3|Error al insertar el registro ".$loccode." del descuento.";
                            $contenido = "Ya existe ".$loccode;
                            $result = false;
                        }else{
        
                            $Mensaje = "Proceso no completado.";
                            $contenido = "El registro ".$loccode." ya existe";
                            $result = true;
                        }
                    }
                  
                }else{
                    $contenido = "No existe el ".$loccode;
                    $result = false;
                }
            }
        }

if ($option == 'eliminarUR') {

    $id_meta = $_POST['id_meta'];

    $info = array();
    
    $SQL = "UPDATE tb_cat_meta SET nu_estatus = 0 WHERE id_meta = '$id_meta'";
    

    $ErrMsg = "No se realizó:  ".$id_meta;
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    $contenido = "Se eliminó el registro ".$id_meta." con éxito";
    $result = true;
}

if ($option == 'mostrarClaveFuncion') {
    $id_identificacion = $_POST['id_identificacion'];
    // $loccode = $_POST['loccode'];
    // $accountcode = $_POST['accountcode'];
    // $SQL = "SELECT discontinued FROM stockmaster WHERE  stockid = '$clave' ORDER BY stockid ASC";
    //         $ErrMsg = "No se obtuvieron las unidades resposables";
    //         $TransResult = DB_query($SQL, $db, $ErrMsg);
    //         if (DB_num_rows($TransResult) == 0) {
    //             $info = array();
    //             $tagrefbank = "";
    //             $SQL = "SELECT tagref FROM bankaccounts WHERE accountcode = '$accountcode'";
    //             $TransResult = DB_query($SQL, $db, $ErrMsg);
    //             while ($myrow = DB_fetch_array($TransResult)) {
    //                 $tagrefbank = $myrow ['tagref'];
    //             }
    //         }
    
    // $SQL = "SELECT discontinued FROM stockmaster WHERE  stockid = '$clave' ORDER BY stockid ASC";
    //         $ErrMsg = "No se obtuvieron las unidades resposables";
    //         $TransResult = DB_query($SQL, $db, $ErrMsg);
    //         if (DB_num_rows($TransResult) == 0) {
    //             $info = array();
    //             $tagref = "";
    //             $SQL = "SELECT tagref FROM locations WHERE loccode = '$loccode'";
    //             $TransResult = DB_query($SQL, $db, $ErrMsg);
    //             while ($myrow = DB_fetch_array($TransResult)) {
    //                 $tagref = $myrow ['tagref'];
    //             }
    //         }

    $SQL = " SELECT DISTINCT
            chartdetailsbudgetbytag.accountcode as value,
            chartdetailsbudgetbytag.accountcode as texto,
            clasificador_ingreso.descripcion as nombre
            FROM chartdetailsbudgetbytag
            JOIN budgetConfigClave ON budgetConfigClave.idClavePresupuesto = chartdetailsbudgetbytag.idClavePresupuesto
            JOIN clasificador_ingreso ON clasificador_ingreso.rtc = chartdetailsbudgetbytag.rtc
            WHERE 
            budgetConfigClave.tipo_config = 1 
            AND chartdetailsbudgetbytag.anho = '$id_identificacion'";
    $ErrMsg = "No se obtuvo la Función";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    //and chartdetailsbudgetbytag.tagref = '$tagref' and chartdetailsbudgetbytag.tagref = '$tagrefbank'
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'id_fuente' => $myrow ['value'], 'nombr' => $myrow ['nombre'] , 'descripcion' => $myrow ['texto']);
    }

    $contenido = array('datos' => $info);
    $result = true;
}


if ($option == 'mostrarFuncion') {
    $id_identificacion = $_POST['id_identificacion'];
    $info = array();
    $SQL = "SELECT stockmaster.stockid, CONCAT(stockmaster.stockid, ' - ', stockmaster.description) as fuentedescription  FROM stockmaster JOIN locstock ON (locstock.stockid = stockmaster.stockid) WHERE stockmaster.discontinued = 1 and locstock.loccode = '$id_identificacion'  ORDER BY locstock.loccode, stockmaster.stockid ASC ";
    // stockmaster.disminuye_ingreso = 1 and 
    $ErrMsg = "No se obtuvo la Función";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'id_fuente' => $myrow ['stockid'], 'fuentedescription' => $myrow ['fuentedescription'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

function fnValidarExiste($loccode, $db){
    $SQL = "SELECT * FROM locations WHERE activo = 1 and loccode = '".$loccode."' ORDER BY loccode ASC";
    $ErrMsg = "No se encontro la informacion de ".$loccode;
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    if (DB_num_rows($TransResult) > 0) {
        $existeFun = true;
    }else{
        $existeFun = false;
    }
    return $existeFun;
}

if ($option == 'obtenerInformacion') {
    $nu_mes = $_POST['nu_mes'];
    $nu_anio = $_POST['nu_anio'];
    $objPrincipal = $_POST['objPrincipal'];
    
    $sqlWhere = "";

    
    if(!empty($objPrincipal) &&  $objPrincipal != "'0'"){
        $sqlWhere .= " AND  tb_cat_meta.loccode LIKE ".$objPrincipal." ";
    }

    
    if (!empty($nu_mes) &&  $nu_mes != 0) {
        $sqlWhere .= " AND (tb_cat_meta.nu_mes like '%".$nu_mes."%' OR tb_cat_meta.nu_mes like '%".$nu_mes."%' )";
    }

    if (!empty($nu_anio) &&  $nu_anio != 0) {
        $sqlWhere .= " AND (tb_cat_meta.nu_anio like '%".$nu_anio."%' OR tb_cat_meta.nu_anio like '%".$nu_anio."%' )";
    }

 
    $info = array();
    
    $SQL ="SELECT
    tb_cat_meta.id_meta as id_meta,
    tb_cat_meta.loccode as loccode,
    tb_cat_meta.nu_anio as nu_anio,
    tb_cat_meta.meta as meta,
    tb_cat_meta.nu_mes as nu_mes,
    tb_cat_meta.dtm_fecha_efectiva as dtm_fecha_efectiva,
    tb_cat_meta.userid as userid,
    CONCAT(`tb_cat_meta`.`loccode`, ' - ' , `locations`.`locationname`) AS 'name_descripcion'
    -- CONCAT(`tb_cat_meta`.`nu_mes`, ' - ' , `stockmaster`.`description`) AS 'parcialConcatenado'
    FROM tb_cat_meta
    JOIN locations on (locations.loccode = tb_cat_meta.loccode)
    WHERE  tb_cat_meta.nu_estatus = 1" .$sqlWhere;
    $ErrMsg = "No se obtuvieron datos para el filtro";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'ObjetoPrincipal' => $myrow ['name_descripcion'],
                'Mes' => $myrow ['nu_mes'],
                'Anio' => $myrow ['nu_anio'],
                'Meta' => $myrow ['meta'],
                'Modificar' => '<a onclick="fnModificar('.$myrow ['id_meta'].',\''.$myrow ['loccode'].'\',\''.$myrow ['nu_mes'].'\',\''.$myrow ['nu_anio'].'\',\''.$myrow ['meta'].'\')"><span class="glyphicon glyphicon-edit"></span></a>',
                'Eliminar' => '<a onclick="fnEliminar('.$myrow ['id_meta'].')"><span class="glyphicon glyphicon-trash"></span></a>');
    } 
    
    
    // Columnas para el GRID
    $columnasNombres .= "[";
    $columnasNombres .= "{ name: 'ObjetoPrincipal', type: 'string' },";
    $columnasNombres .= "{ name: 'Mes', type: 'string' },";
    $columnasNombres .= "{ name: 'Anio', type: 'string' },";
    $columnasNombres .= "{ name: 'Meta', type: 'string' },";
    $columnasNombres .= "{ name: 'Modificar', type: 'string' },";
    $columnasNombres .= "{ name: 'Eliminar', type: 'string' },";
    $columnasNombres .= "]";
    // Columnas para el GRID
    $columnasNombresGrid .= "[";
    $columnasNombresGrid .= " { text: 'Objeto/Principal', datafield: 'ObjetoPrincipal', width: '50%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Mes', datafield: 'Mes', width: '10%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Año', datafield: 'Anio', width: '10%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: '%Meta', datafield: 'Meta', width: '10%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Modificar', datafield: 'Modificar', width: '10%', cellsalign: 'center', align: 'center', hidden: false, sortable: false, filterable: false },";
    $columnasNombresGrid .= " { text: 'Eliminar', datafield: 'Eliminar', width: '10%', cellsalign: 'center', align: 'center', hidden: false, sortable: false, filterable: false },";
    $columnasNombresGrid .= "]";

    $nombreExcel = str_replace(' ', '_', traeNombreFuncionGeneral($funcion, $db)).'_'.date('dmY');

    $contenido = array('datos' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid, 'nombreExcel' => $nombreExcel);
    $result = true;
}

$dataObj = array('sql' => "", 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);
