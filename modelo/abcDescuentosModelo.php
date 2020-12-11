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
$funcion=2518;
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
    tb_descuentos.id_descuentos as id_descuentos,
    tb_descuentos.loccode as loccode,
    CONCAT(`tb_descuentos`.`loccode`, ' - ' , `locations`.`locationname`) AS 'name_descripcion',
    CONCAT(`tb_descuentos`.`id_parcial`, ' - ' , `stockmaster`.`description`) AS 'parcialConcatenado',
    tb_descuentos.id_parcial as id_parcial,
    tb_descuentos.nu_porcentaje as porcentaje,
    tb_descuentos.dtm_inicio as fechaini,
    tb_descuentos.dtm_fin as fechafin,
    tb_descuentos.num_dias as dias,
    tb_descuentos.tipo_descuento as tipo_descuento,
    locstock.stockid as objetoParcial
    FROM tb_descuentos
    JOIN locstock ON (locstock.stockid = tb_descuentos.id_parcial)
    JOIN stockmaster on (stockmaster.stockid = tb_descuentos.id_parcial)
    JOIN locations on (locations.loccode = tb_descuentos.loccode)
    WHERE  tb_descuentos.nu_estatus = 1
    ORDER BY tb_descuentos.loccode ASC";
    $ErrMsg = "No se obtuvieron las Subfunciones";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    $tipocampana = "";
    while ($myrow = DB_fetch_array($TransResult)) {
        $tipocampana = $myrow ['tipo_descuento'];
        $info[] = array( 'Principal' => $myrow ['name_descripcion'],
                $FechaInicio = $myrow['fechaini'] ==  '1111-11-11' ? '-' : date("d-m-Y", strtotime($myrow['fechaini'])),
                $FechaFinal = $myrow['fechafin'] ==  '1111-11-11' ? '-' : date("d-m-Y", strtotime($myrow['fechafin'])),
                'fechaFinal'=> date("d-m-Y", strtotime($myrow['fechafin'])),
                'Parcial' => $myrow ['parcialConcatenado'],
                'Procentaje' => $myrow ['porcentaje'],
                'FechaInicio' => $myrow['fechaini'] ==  '1111-11-11' ? '-' : date("d-m-Y", strtotime($myrow['fechaini'])),
                'FechaFin' => $myrow['fechafin'] ==  '1111-11-11' ? '-' : date("d-m-Y", strtotime($myrow['fechafin'])),
                'Tipo' => $myrow ['tipo_descuento'],
                'Dias' => $myrow ['dias'],
                'Modificar' => '<a onclick="fnModificar('.$myrow ['id_descuentos'].',\''.$myrow ['loccode'].'\',\''.$myrow ['id_parcial'].'\',\''.$myrow ['porcentaje'].'\',\''.$FechaInicio.'\',\''.$FechaFinal.'\',\''.$myrow ['tipo_descuento'].'\',\''.$myrow ['dias'].'\')"><span class="glyphicon glyphicon-edit"></span></a>',
                'Eliminar' => '<a onclick="fnEliminar('.$myrow ['id_descuentos'].')"><span class="glyphicon glyphicon-trash"></span></a>');
    } 
    
    
    // Columnas para el GRID
    $columnasNombres .= "[";
    $columnasNombres .= "{ name: 'Principal', type: 'string' },";
    $columnasNombres .= "{ name: 'Parcial', type: 'string' },";
    $columnasNombres .= "{ name: 'Procentaje', type: 'string' },";
    $columnasNombres .= "{ name: 'FechaInicio', type: 'string' },";
    $columnasNombres .= "{ name: 'FechaFin', type: 'string' },";
    $columnasNombres .= "{ name: 'Tipo', type: 'string' },";
    $columnasNombres .= "{ name: 'Dias', type: 'string' },";
    $columnasNombres .= "{ name: 'Modificar', type: 'string' },";
    $columnasNombres .= "{ name: 'Eliminar', type: 'string' },";
    $columnasNombres .= "]";
    // Columnas para el GRID
    $columnasNombresGrid .= "[";
    $columnasNombresGrid .= " { text: 'Objeto/Principal', datafield: 'Principal', width: '15%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Objeto/Parcial', datafield: 'Parcial', width: '14%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Porcentaje', datafield: 'Procentaje', width: '14%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Tipo de Descuento', datafield: 'Tipo', width: '10%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Fecha de Inicio', datafield: 'FechaInicio', width: '14%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Fecha de Fin', datafield: 'FechaFin', width: '14%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Dias de descuento', datafield: 'Dias', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Modificar', datafield: 'Modificar', width: '7%', cellsalign: 'center', align: 'center', hidden: false, sortable: false, filterable: false },";
    $columnasNombresGrid .= " { text: 'Eliminar', datafield: 'Eliminar', width: '7%', cellsalign: 'center', align: 'center', hidden: false, sortable: false, filterable: false },";
    $columnasNombresGrid .= "]";

    $nombreExcel = str_replace(' ', '_', traeNombreFuncionGeneral($funcion, $db)).'_'.date('dmY');

    $contenido = array('datos' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid, 'nombreExcel' => $nombreExcel);
    $result = true;
}

        

        if ($option == 'AgregarCatalogo') {
            $id_descuentos = $_POST['id_descuentos'];
            $loccode = $_POST['loccode'];
            $id_parcial = $_POST['id_parcial'];
            $porcentaje = $_POST['porcentaje'];
            $dtm_inicio= $_POST['dtm_inicio'] != '' ? date("Y-m-d", strtotime($_POST['dtm_inicio'])) : '';
            $dtm_fin= $_POST['dtm_fin'] != '' ? date("Y-m-d", strtotime($_POST['dtm_fin'])) : '';
            $tipo_descuento = $_POST['tipo_descuento'];
            $numDias = $_POST['numDias'];
            $proceso = $_POST['proceso'];

            if ($proceso == 'Modificar') {
                $info = array();
                $SQL = "UPDATE  tb_descuentos SET  tb_descuentos.loccode = '$loccode', tb_descuentos.id_parcial = '$id_parcial', tb_descuentos.nu_porcentaje = '$porcentaje', tb_descuentos.dtm_inicio = '$dtm_inicio', tb_descuentos.dtm_fin  = '$dtm_fin', tb_descuentos.id_usuario = '".$_SESSION['UserID']."', tb_descuentos.tipo_descuento = '$tipo_descuento' WHERE  tb_descuentos.id_descuentos = '$id_descuentos'";
                $ErrMsg = "No se agrego la informacion de ".$id_loccode;
                $TransResult = DB_query($SQL, $db, $ErrMsg);
        
                $contenido = "Se modificó el registro ".$id_loccode." del descuento con éxito";
                $result = true;
            } else {
                if(fnValidarExiste($loccode, $db)){
                    
                    $SQL = "SELECT * FROM tb_descuentos WHERE loccode = '".$loccode."' and id_parcial = '".$id_parcial."' and dtm_inicio = '".$dtm_inicio."' and dtm_fin = '".$dtm_fin."' and num_dias = '".$numDias."' and nu_estatus = 1 ORDER BY loccode ASC";
                    // if($_SESSION['UserID'] == 'desarrollo'){
                    //     echo "<br><pre>".$SQL."</pre>";
                    // }
                    $ErrMsg = "No se obtuvieron los descuentos";
                    $TransResult = DB_query($SQL, $db, $ErrMsg);
                    if (DB_num_rows($TransResult) == 0) {
                        $info = array(); 
                        
                        $SQL = "INSERT INTO `tb_descuentos` ( `loccode`, `id_parcial`, `nu_porcentaje`, `dtm_inicio`, `dtm_fin`, `tipo_descuento`, `nu_estatus`, `id_usuario`, `num_dias`)
                                VALUES( '".$loccode."', '".$id_parcial."', '".$porcentaje."', '".$dtm_inicio."', '".$dtm_fin."', '".$tipo_descuento."', 1, '".$_SESSION['UserID']."', '".$numDias."')";
                        $ErrMsg = "No se agrego la informacion de ". $loccode;
                        $TransResult = DB_query($SQL, $db, $ErrMsg);
        
                        $contenido = "Se agregó el registro ".$loccode." del descuento con éxito";
                        $result = true;
                    } else {
                        $myrow = DB_fetch_array($TransResult);
        
                        if($myrow['nu_estatus']==1){
                            $Mensaje = "3|Error al insertar el registro ".$loccode." del descuento.";
                            $contenido = "Ya existe el descuento ".$loccode;
                            $result = false;
                        }else{
        
                            $Mensaje = "Proceso no completado.";
                            $contenido = "El registro ".$loccode." del descuento ya existe";
                            $result = true;
                        }
                    }
                  
                }else{
                    $contenido = "No existe el descuento con la clave ".$loccode;
                    $result = false;
                }
            }
        }

if ($option == 'eliminarUR') {

    $id_descuentos = $_POST['id_descuentos'];

    $info = array();
    
    $SQL = "UPDATE tb_descuentos SET nu_estatus = 0 WHERE id_descuentos = '$id_descuentos'";
    

    $ErrMsg = "No se realizó:  ".$id_descuentos;
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    $contenido = "Se eliminó el registro ".$id_descuentos." de descuentos con éxito";
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
    $tipoParcial = $_POST['tipoParcial'];
    $tipoAlmacen = $_POST['tipoAlmacen'];

    $sqlWhere = "";

    
    if(!empty($tipoAlmacen) &&  $tipoAlmacen != "'0'"){
        $sqlWhere .= " AND  tb_descuentos.loccode LIKE ".$tipoAlmacen." ";
    }

    
    if (!empty($tipoParcial) &&  $tipoParcial != 0) {
        $sqlWhere .= " AND (tb_descuentos.id_parcial like '%".$tipoParcial."%' OR tb_descuentos.id_parcial like '%".$tipoParcial."%' )";
    }

 
    $info = array();
    
    $SQL ="SELECT
    tb_descuentos.id_descuentos as id_descuentos,
    tb_descuentos.loccode as loccode,
    CONCAT(`tb_descuentos`.`loccode`, ' - ' , `locations`.`locationname`) AS 'name_descripcion',
    CONCAT(`tb_descuentos`.`id_parcial`, ' - ' , `stockmaster`.`description`) AS 'parcialConcatenado',
    tb_descuentos.id_parcial as id_parcial,
    tb_descuentos.nu_porcentaje as porcentaje,
    tb_descuentos.dtm_inicio as fechaini,
    tb_descuentos.dtm_fin as fechafin,
    tb_descuentos.num_dias as dias,
    tb_descuentos.tipo_descuento as tipo_descuento,
    locstock.stockid as objetoParcial
    FROM tb_descuentos
    JOIN locstock ON (locstock.stockid = tb_descuentos.id_parcial)
    JOIN stockmaster on (stockmaster.stockid = tb_descuentos.id_parcial)
    JOIN locations on (locations.loccode = tb_descuentos.loccode)
    WHERE  tb_descuentos.nu_estatus = 1" .$sqlWhere;
    $ErrMsg = "No se obtuvieron los botones para el proceso";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    /*echo "<pre>".$SQL;
    exit();*/

    // $sqlEQGeneral="SELECT eq_stockid from tb_partida_articulo where eq_stockid like'G%'";
    // $resultEQGeneral= DB_query($sqlEQGeneral, $db);
    // $myrowEQGeneral = DB_fetch_array($resultEQGeneral);

    while ($myrow = DB_fetch_array($TransResult)) {
        $tipocampana = $myrow ['tipo_descuento'];
        $info[] = array( 'Principal' => $myrow ['name_descripcion'],
                $FechaInicio = $myrow['fechaini'] ==  '1111-11-11' ? '-' : date("d-m-Y", strtotime($myrow['fechaini'])),
                $FechaFinal = $myrow['fechafin'] ==  '1111-11-11' ? '-' : date("d-m-Y", strtotime($myrow['fechafin'])),
                'fechaFinal'=> date("d-m-Y", strtotime($myrow['fechafin'])),
                'Parcial' => $myrow ['parcialConcatenado'],
                'Procentaje' => $myrow ['porcentaje'],
                'FechaInicio' => $myrow['fechaini'] ==  '1111-11-11' ? '-' : date("d-m-Y", strtotime($myrow['fechaini'])),
                'FechaFin' => $myrow['fechafin'] ==  '1111-11-11' ? '-' : date("d-m-Y", strtotime($myrow['fechafin'])),
                'Tipo' => $myrow ['tipo_descuento'],
                'Dias' => $myrow ['dias'],
                'Modificar' => '<a onclick="fnModificar('.$myrow ['id_descuentos'].',\''.$myrow ['loccode'].'\',\''.$myrow ['id_parcial'].'\',\''.$myrow ['porcentaje'].'\',\''.$FechaInicio.'\',\''.$FechaFinal.'\',\''.$myrow ['tipo_descuento'].'\',\''.$myrow ['dias'].'\')"><span class="glyphicon glyphicon-edit"></span></a>',
                'Eliminar' => '<a onclick="fnEliminar('.$myrow ['id_descuentos'].')"><span class="glyphicon glyphicon-trash"></span></a>');
    }  
    // Columnas para el GRID
    $columnasNombres .= "[";
    $columnasNombres .= "{ name: 'Principal', type: 'string' },";
    $columnasNombres .= "{ name: 'Parcial', type: 'string' },";
    $columnasNombres .= "{ name: 'Procentaje', type: 'string' },";
    $columnasNombres .= "{ name: 'FechaInicio', type: 'string' },";
    $columnasNombres .= "{ name: 'FechaFin', type: 'string' },";
    $columnasNombres .= "{ name: 'Tipo', type: 'string' },";
    $columnasNombres .= "{ name: 'Dias', type: 'string' },";
    $columnasNombres .= "{ name: 'Modificar', type: 'string' },";
    $columnasNombres .= "{ name: 'Eliminar', type: 'string' },";
    $columnasNombres .= "]";
    // Columnas para el GRID
    $columnasNombresGrid .= "[";
    $columnasNombresGrid .= " { text: 'Objeto/Principal', datafield: 'Principal', width: '15%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Objeto/Parcial', datafield: 'Parcial', width: '14%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Porcentaje', datafield: 'Procentaje', width: '14%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Tipo de Descuento', datafield: 'Tipo', width: '10%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Fecha de Inicio', datafield: 'FechaInicio', width: '14%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Fecha de Fin', datafield: 'FechaFin', width: '14%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Dias de descuento', datafield: 'Dias', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Modificar', datafield: 'Modificar', width: '7%', cellsalign: 'center', align: 'center', hidden: false, sortable: false, filterable: false },";
    $columnasNombresGrid .= " { text: 'Eliminar', datafield: 'Eliminar', width: '7%', cellsalign: 'center', align: 'center', hidden: false, sortable: false, filterable: false },";
    $columnasNombresGrid .= "]";

    $nombreExcel = str_replace(' ', '_', traeNombreFuncionGeneral($funcion, $db)).'_'.date('dmY');

    $contenido = array('datos' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid, 'nombreExcel' => $nombreExcel);
    $result = true;
}

$dataObj = array('sql' => "", 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);
