<?php
/**
 * Modelo para el ABC de Fuente del Recurso
 * 
 * @category ABC
 * @package ap_grp
 * @author Jesùs Reyes Santos <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 02/08/2017
 * Fecha Modificación: 02/08/2017
 * Se realizan operación pero el Alta, Baja y Modificación. Mediante las validaciones creadas para la operación seleccionada
 */
// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);
//ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
/*
stockid = clave
description = descripcion
Units = unidad de medida
mbflag B fijo
decimalplace fijo2
discontinued = activo
sat_stock_code = id_producto

borrar eliminar

SELECT DISTINCT id_parcial , desc_parcial, tb_cat_objeto_parcial.estatus as estatus, tb_cat_objeto_parcial.disminuye_ingreso as ingreso, locations.loccode as idFinalidad, locations.locationname as finalidad
            FROM tb_cat_objeto_parcial
            JOIN locations on (tb_cat_objeto_parcial.loccode = locations.loccode)
            ".$sqlUR."
            ORDER BY locations.loccode, id_parcial ASC";

*/
session_start();

$PageSecurity = 1;
$PathPrefix = '../';
//include($PathPrefix.'includes/session.inc');
//header('Content-type: text/html; charset=ISO-8859-1');
include($PathPrefix.'abajo.php');
include($PathPrefix . 'config.php');
include($PathPrefix . 'includes/ConnectDB.inc');
if ($abajo) {
    include($PathPrefix . 'includes/LanguageSetup.php');
}
$funcion=2505;
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
    $id_parcial = $_POST['id_parcial'];
    $sqlUR = " WHERE  stockmaster.tipo_dato = 2";

    if (!empty($id_parcial)) {
       
    }
    $info = array();

    $SQL = "SELECT
    locstock.loccode as idFinalidad,
    CONCAT(`locstock`.`loccode`, ' - ' , `locations`.`locationname`) AS 'name_descripcion',
    stockmaster.stockid as id_parcial,
    stockmaster.description as desc_parcial,
    stockmaster.discontinued as estatusmdf,
    stockmaster.clave as clv,
    stockmaster.disminuye_ingreso as ingresomdf,
    CASE WHEN stockmaster.discontinued = 1 THEN 'Activo' ELSE 'Inactivo' END AS estatus,
    CASE WHEN stockmaster.disminuye_ingreso = 1 THEN 'Si' ELSE 'No' END AS ingreso
    FROM stockmaster
    JOIN locstock ON (locstock.stockid = stockmaster.stockid)
    JOIN locations on (locations.loccode = locstock.loccode)   
    ".$sqlUR."
    ORDER BY locstock.loccode, stockmaster.stockid ASC ";
    $ErrMsg = "No se obtuvieron las Fuentes del Recurso";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array(
            'Clave' => $myrow ['id_parcial'],
            'Estatus' => $myrow ['estatus'],
            'Disminuye' => $myrow ['ingreso'],
            'Funcion' => $myrow ['desc_parcial'],
            'idFinalidad' => $myrow ['idFinalidad'],
            'Almacen' => $myrow ['name_descripcion'],
            'CLV' => $myrow ['clv'],
            'Modificar' => '<a onclick="fnModificar(\''.$myrow ['id_parcial'].'\',\''.$myrow ['desc_parcial'].'\',\''.$myrow ['idFinalidad'].'\',\''.$myrow ['estatusmdf'].'\',\''.$myrow ['ingresomdf'].'\',\''.$myrow ['clv'].'\')"><span class="glyphicon glyphicon-edit"></span></a>');
    }
    // Columnas para el GRID
    $columnasNombres .= "[";
    $columnasNombres .= "{ name: 'idFinalidad', type: 'string' },";
    $columnasNombres .= "{ name: 'Almacen', type: 'string' },";
    $columnasNombres .= "{ name: 'CLV', type: 'string' },";
    $columnasNombres .= "{ name: 'Funcion', type: 'string' },";
    $columnasNombres .= "{ name: 'Estatus', type: 'string' },";
    $columnasNombres .= "{ name: 'Disminuye', type: 'string' },";
    $columnasNombres .= "{ name: 'Modificar', type: 'string' },";
    $columnasNombres .= "]";
    // Columnas para el GRID
    $columnasNombresGrid .= "[";
    //$columnasNombresGrid .= " { text: 'Clave', datafield: 'idFinalidad', width: '14%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Objeto Principal', datafield: 'Almacen', width: '14%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Clave', datafield: 'CLV', width: '13%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Descripción', datafield: 'Funcion', width: '47%', cellsalign: 'left', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Estatus', datafield: 'Estatus', width: '7%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Disminuye ingreso', datafield: 'Disminuye', width: '12%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Modificar', datafield: 'Modificar', width: '7%', cellsalign: 'center', align: 'center', hidden: false, sortable: false, filterable: false },";
    $columnasNombresGrid .= "]";

    $nombreExcel = str_replace(' ', '_', traeNombreFuncionGeneral($funcion, $db)).'_'.date('dmY');

    $contenido = array('datos' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid, 'nombreExcel' => $nombreExcel);
    $result = true;
}
/*
if ($option == 'mostrarCatalogo') {
    $id_identificacion = $_POST['id_identificacion'];
    $id_fuente = $_POST['id_fuente'];
    $sqlUR = " WHERE tb_cat_fuente_recurso.activo = 1 and tb_cat_identificacion_fuente.activo = 1 ";

    if (!empty($idFuncion)) {
        $sqlUR = " WHERE tb_cat_fuente_recurso.activo = 1 and tb_cat_identificacion_fuente.activo = 1 and tb_cat_identificacion_fuente.id_identificacion = '".trim($id_identificacion)."' and id_fuente = '".trim($id_fuente)."' ";
    }
    $info = array();
    $SQL = "SELECT DISTINCT id_fuente , desc_fuente, tb_cat_identificacion_fuente.id_identificacion as idIdentificacion, tb_cat_identificacion_fuente.desc_identificacion as identificacion
            FROM tb_cat_fuente_recurso
            JOIN tb_cat_identificacion_fuente on (tb_cat_fuente_recurso.id_identificacion = tb_cat_identificacion_fuente.id_identificacion)
            ".$sqlUR."
            ORDER BY tb_cat_identificacion_fuente.id_identificacion, id_fuente ASC";
    $ErrMsg = "No se obtuvieron las fuentes de recurso"; 
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array(
            'Clave' => $myrow ['id_fuente'],
            'Funcion' => $myrow ['desc_fuente'],
            'dIdentificacion' => $myrow ['idIdentificacion'],
            'Identificacion' => $myrow ['identificacion'],
            'Modificar' => '<a onclick="fnModificar('.$myrow ['id_fuente'].',\''.$myrow ['desc_fuente'].'\','.$myrow ['idIdentificacion'].',\''.$myrow ['identificacion'].'\')"><span class="glyphicon glyphicon-edit"></span></a>',
            'Eliminar' => '<a onclick="fnEliminar('.$myrow ['id_fuente'].',\''.$myrow ['desc_fuente'].'\','.$myrow ['idIdentificacion'].')"><span class="glyphicon glyphicon-trash"></span></a>' );
    }
    // Columnas para el GRID
    $columnasNombres .= "[";
    $columnasNombres .= "{ name: 'idIdentificacion', type: 'string' },";
    $columnasNombres .= "{ name: 'Clave', type: 'string' },";
    $columnasNombres .= "{ name: 'Funcion', type: 'string' },";
    $columnasNombres .= "{ name: 'Modificar', type: 'string' },";
    $columnasNombres .= "{ name: 'Eliminar', type: 'string' }";
    $columnasNombres .= "]";
    // Columnas para el GRID
    $columnasNombresGrid .= "[";
    $columnasNombresGrid .= " { text: 'Identificación Fuente', datafield: 'idIdentificacion', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Clave', datafield: 'Clave', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Descripción', datafield: 'Funcion', width: '76%', cellsalign: 'left', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Modificar', datafield: 'Modificar', width: '7%', cellsalign: 'center', align: 'center', hidden: false, sortable: false, filterable: false },";
    $columnasNombresGrid .= " { text: 'Eliminar', datafield: 'Eliminar', width: '7%', cellsalign: 'center', align: 'center', hidden: false, sortable: false, filterable: false }";
    $columnasNombresGrid .= "]";

    $nombreExcel = str_replace(' ', '_', traeNombreFuncionGeneral($funcion, $db)).'_'.date('dmY');

    $contenido = array('datos' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid, 'nombreExcel' => $nombreExcel);
    $result = true;
}
*/
if ($option == 'AgregarCatalogo') {
    $id_identificacion = $_POST['id_identificacion'];
    $clave = $_POST['clave'];
    $descripcion = $_POST['descripcion'];
    $estatus = $_POST['estatus'];
    $ingreso = $_POST['ingreso'];
    $proceso = $_POST['proceso'];
    $valorid=$id_identificacion."_".$clave;
    if ($proceso == 'Modificar') {
        $info = array();
        $SQL = "UPDATE stockmaster SET stockmaster.description = '$descripcion', stockmaster.discontinued = '$estatus', stockmaster.disminuye_ingreso = '$ingreso' WHERE  stockmaster.stockid = '$clave'";
        $ErrMsg = "No se agrego la informacion de ".$clave." - ".$descripcion;
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        $contenido = "Se modificó el registro ".$clave." del Catálogo Objeto Parcial con éxito";
        $result = true;
    } else {
        if(fnValidarExiste($id_identificacion, $db)){
            
            $SQL = "SELECT discontinued FROM stockmaster WHERE  stockid = '$valorid' ORDER BY stockid ASC";
            $ErrMsg = "No se obtuvieron las unidades resposables";
            $TransResult = DB_query($SQL, $db, $ErrMsg);
            if (DB_num_rows($TransResult) == 0) {
                $info = array();
                $producto_sat = "";
                $unidad_sat = "";
                $SQL = "SELECT id_producto, id_unidad FROM locations WHERE loccode = '$id_identificacion'";
                $TransResult = DB_query($SQL, $db, $ErrMsg);
                while ($myrow = DB_fetch_array($TransResult)) {
                    $producto_sat = $myrow ['id_producto'];
                    $unidad_sat = $myrow ['id_unidad'];
                }
            

                $SQL = "INSERT INTO `stockmaster` 
                    (`stockid`, `spes`, `categoryid`, `description`, `longdescription`, `manufacturer`, `stockautor`, `units`, `mbflag`, `lastcurcostdate`, `actualcost`, `lastcost`, `materialcost`, `labourcost`, `overheadcost`, `lowestlevel`, `discontinued`, `controlled`, `eoq`, `volume`, `kgs`, `barcode`, `discountcategory`, `taxcatid`, `taxcatidret`, `serialised`, `appendfile`, `perishable`, `decimalplaces`, `nextserialno`, `pansize`, `shrinkfactor`, `netweight`, `idclassproduct`, `stocksupplier`, `securitypoint`, `pkg_type`, `idetapaflujo`, `flagcommission`, `fijo`, `fecha_modificacion`, `stockupdate`, `isbn`, `grade`, `subject`, `deductibleflag`, `u_typeoperation`, `typeoperationdiot`, `height`, `width`, `large`, `fichatecnica`, `percentfactorigi`, `OrigenCountry`, `OrigenDate`, `inpdfgroup`, `flagadvance`, `eq_conversion_factor`, `eq_stockid`, `unitequivalent`, `accountinventario`, `nu_cve_familia`, `sat_stock_code`, `disminuye_ingreso`, `tipo_dato`, `clave`)
                VALUES('".$id_identificacion."_".$clave."', '', '211', '".$descripcion."', '', '', NULL, '".$unidad_sat."', 'B', '', 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0, '".$estatus."', 0, 0, 0.0000, 0.0000, '', '', 2, 0, 0, 'none', 0, 2, 0, 0, 0, 0.0000, '', NULL, NULL, NULL, 0, 0, '0', '', 0, NULL, NULL, NULL, 0, 0, 0, 0.00, 0.00, 0.00, NULL, NULL, NULL, NULL, '', NULL, 0, '', NULL, NULL, '', '".$producto_sat."', '".$ingreso."', '2', '".$clave."')
                
                ";
                $ErrMsg = "No se agrego la informacion de ".$clave." - ".$descripcion;
                $TransResult = DB_query($SQL, $db, $ErrMsg);
                
                
                $SQL="INSERT INTO `locstock` (`loccode`, `stockid`, `quantity`, `reorderlevel`, `ontransit`, `quantityv2`, `localidad`, `minimumlevel`, `timefactor`, `delay`, `qtybysend`, `quantityprod`, `loccode_aux`)
                VALUES
                ('".$id_identificacion."', '".$id_identificacion."_".$clave."', 0, 0, 0, 0, NULL, 0, 0, 0, 0, 0, '')";
                $ErrMsg = "No se agrego la informacion de ".$clave." - ".$descripcion;
                $TransResult = DB_query($SQL, $db, $ErrMsg);
                
                $contenido = "Se agregó el registro ".$clave." del Catálogo Objeto Parcial con éxito";
                $result = true;
            } else {
                $myrow = DB_fetch_array($TransResult);

                if($myrow['activo']==1){
                    $Mensaje = "3|Error al insertar el registro ".$clave." del Catálogo Objeto Parcial.";
                    $contenido = "Ya existe la Objeto Parcial con la clave ".$clave;
                    $result = false;
                }else{
                    $Mensaje = "Proceso no completado.";
                    $contenido = "El registro ".$clave." del Catálogo Objeto Parcial ya existe";
                    $result = true;
                }
            }
        }else{
            $contenido = "No existe el Objeto Parcial con la clave ".$id_identificacion;
            $result = false;
        }
    }
}


if ($option == 'existeSubfuncion') {
    $cveFin = $_POST['idIdentificacion'];
    $cveFun = $_POST['idFuente'];
    $SQL = "SELECT * FROM tb_cat_fuente_financiamiento WHERE activo = 1 AND id_identificacion = '".$cveFin."' AND id_fuente = '". $cveFun."'";
    $ErrMsg = "No se elimino la informacion de ".$clave;
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    if (DB_num_rows($TransResult) > 0) {
        $result = false;
    }else{
        $result = true;
    }
}

if ($option == 'obtenerInformacion') {
    
    $txtClave = $_POST['txtClave'];
    $txtEstatus = $_POST['txtEstatus'];
    $txtDescripcion = $_POST['txtDescripcion'];
    $tipoAlmacen = $_POST['tipoAlmacen'];

    $sqlWhere = "";

    if ($tipoAlmacen != '') {
        $sqlWhere .= " AND locstock.loccode IN (".$tipoAlmacen.") ";
    }
    if (trim($txtClave) != '') {
        $sqlWhere .= " AND (stockmaster.stockid like '%".$txtClave."%' OR stockmaster.stockid like '%".$txtClave."%' )";
    }
    if (trim($txtEstatus) != '') {
        $sqlWhere .= " AND (stockmaster.stockid like '%".$txtEstatus."%' OR stockmaster.discontinued like '%".$txtEstatus."%' )";
    }
    if (trim($txtDescripcion) != '') {
        $sqlWhere .= " AND (stockmaster.stockid like '%".$txtDescripcion."%' OR stockmaster.description like '%".$txtDescripcion."%' )";
    }


    $info = array();
    
    $SQL = "SELECT
    locstock.loccode as idFinalidad,
    CONCAT(`locstock`.`loccode`, ' - ' , `locations`.`locationname`) AS 'name_descripcion',
    stockmaster.stockid as id_parcial,
    stockmaster.description as desc_parcial,
    stockmaster.discontinued as estatusmdf,
    stockmaster.clave as clv,
    stockmaster.disminuye_ingreso as ingresomdf,
    CASE WHEN stockmaster.discontinued = 1 THEN 'Activo' ELSE 'Inactivo' END AS estatus,
    CASE WHEN stockmaster.disminuye_ingreso = 1 THEN 'Si' ELSE 'No' END AS ingreso
    FROM stockmaster
    LEFT JOIN locstock ON (locstock.stockid = stockmaster.stockid)
    LEFT JOIN locations on (locations.loccode = locstock.loccode)   
    WHERE stockmaster.tipo_dato = 2".$sqlWhere;
    $ErrMsg = "No se obtuvieron los botones para el proceso";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    // $sqlEQGeneral="SELECT eq_stockid from tb_partida_articulo where eq_stockid like'G%'";
    // $resultEQGeneral= DB_query($sqlEQGeneral, $db);
    // $myrowEQGeneral = DB_fetch_array($resultEQGeneral);

    while ($myrow = DB_fetch_array($TransResult)) {
        // $liga = "&StockID=".$myrow['stockid'];
        $info[] = array(
            'Clave' => $myrow ['id_parcial'],
            'Estatus' => $myrow ['estatus'],
            'Disminuye' => $myrow ['ingreso'],
            'Funcion' => $myrow ['desc_parcial'],
            'idFinalidad' => $myrow ['idFinalidad'],
            'Almacen' => $myrow ['name_descripcion'],
            'CLV' => $myrow ['clv'],
            'Modificar' => '<a onclick="fnModificar(\''.$myrow ['id_parcial'].'\',\''.$myrow ['desc_parcial'].'\',\''.$myrow ['idFinalidad'].'\',\''.$myrow ['estatusmdf'].'\',\''.$myrow ['ingresomdf'].'\')"><span class="glyphicon glyphicon-edit"></span></a>');
    }
    
    
    // Columnas para el GRID
    $columnasNombres .= "[";
    $columnasNombres .= "{ name: 'idFinalidad', type: 'string' },";
    $columnasNombres .= "{ name: 'Almacen', type: 'string' },";
    $columnasNombres .= "{ name: 'CLV', type: 'string' },";
    $columnasNombres .= "{ name: 'Funcion', type: 'string' },";
    $columnasNombres .= "{ name: 'Estatus', type: 'string' },";
    $columnasNombres .= "{ name: 'Disminuye', type: 'string' },";
    $columnasNombres .= "{ name: 'Modificar', type: 'string' },";
    $columnasNombres .= "]";
    // Columnas para el GRID
    $columnasNombresGrid .= "[";
    //$columnasNombresGrid .= " { text: 'Clave', datafield: 'idFinalidad', width: '14%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Objeto Principal', datafield: 'Almacen', width: '14%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Clave', datafield: 'CLV', width: '13%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Descripción', datafield: 'Funcion', width: '47%', cellsalign: 'left', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Estatus', datafield: 'Estatus', width: '7%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Disminuye ingreso', datafield: 'Disminuye', width: '12%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Modificar', datafield: 'Modificar', width: '7%', cellsalign: 'center', align: 'center', hidden: false, sortable: false, filterable: false },";
    $columnasNombresGrid .= "]";

    $nombreExcel = str_replace(' ', '_', traeNombreFuncionGeneral($funcion, $db)).'_'.date('dmY');

    $contenido = array('datos' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid, 'nombreExcel' => $nombreExcel);
    $result = true;
}

function fnValidarExiste($idIdentificacion, $db){
    $SQL = "SELECT * FROM locations WHERE activo = 1 and loccode = '".$idIdentificacion."' ORDER BY loccode ASC";
    $ErrMsg = "No se encontro la informacion de ".$idIdentificacion;
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    if (DB_num_rows($TransResult) > 0) {
        $existeFin = true;
    }else{
        $existeFin = false;
    }
    return $existeFin;
}


$dataObj = array('sql' => "", 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);