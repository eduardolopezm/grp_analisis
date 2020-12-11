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
$funcion=2517;
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
    
    //$sqlUR = " WHERE  stockmaster.tipo_dato = 2";

    // if (!empty($id_parcial)) {
       
    // }
    $info = array();

    $SQL = "SELECT contratos.id_contrato as clave,    
    CONCAT(tags.tagref , ' - ' , tags.tagdescription ) AS unidadNegocio,
    CONCAT(ues.ue , ' - ' , ues.desc_ue ) AS unidadEjecutora,    
    CONCAT(configContrato.id_contratos, ' - ', configContrato.id_loccode,' - ', locations.locationname) AS configContrato,
    configContrato.id_contratos AS idconfContrato,  
    debtorsmaster.name as contribuyente 	
    FROM tb_contratos AS contratos JOIN tags on (tags.tagref = contratos.tagref)    
    JOIN tb_cat_unidades_ejecutoras as ues on (ues.ue = contratos.ln_ue) 
    JOIN tb_contratos_contribuyentes as configContrato on (configContrato.id_contratos = contratos.id_confcontratos) 
    JOIN locations on (configContrato.id_loccode = locations.loccode)  
    JOIN debtorsmaster on (debtorsmaster.debtorno = contratos.id_debtorno)	
    WHERE contratos.ind_activo = '1'";
    $ErrMsg = "No se obtuvieron los contratos del contribuyente";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array(
            'Clave' => $myrow ['clave'],
            'CC' => $myrow ['configContrato'],
            'UE' => $myrow ['unidadEjecutora'],
            'Contri' => $myrow ['contribuyente'],
            'GenerarAdeudos' => '<a onclick="fnGenerarAdeudos('.$myrow ['clave'].')"><span class="glyphicon glyphicon-duplicate"></span></a>',
            'PaseDeCobro' => '<a onclick="fnPaseDeCobro('.$myrow ['clave'].')"><span class="glyphicon glyphicon-credit-card"></span></a>',
            'Historial' => '<a onclick="fnHistorial('.$myrow ['clave'].')"><span class="glyphicon glyphicon-th-list"></span></a>'
            
        );
    }
    // Columnas para el GRID
    $columnasNombres .= "[";
    $columnasNombres .= "{ name: 'Clave', type: 'string' },";
    $columnasNombres .= "{ name: 'CC', type: 'string' },";
    $columnasNombres .= "{ name: 'UE', type: 'string' },";
    $columnasNombres .= "{ name: 'Contri', type: 'string' },";
    $columnasNombres .= "{ name: 'GenerarAdeudos', type: 'string' },";
    $columnasNombres .= "{ name: 'PaseDeCobro', type: 'string' },";
    $columnasNombres .= "{ name: 'Historial', type: 'string' },";
    $columnasNombres .= "]";
    // Columnas para el GRID
    $columnasNombresGrid .= "[";
    //$columnasNombresGrid .= " { text: 'Clave', datafield: 'idFinalidad', width: '14%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Folio', datafield: 'Clave', width: '7%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Contrato', datafield: 'CC', width: '26%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Unidad Ejecutora', datafield: 'UE', width: '27%', cellsalign: 'left', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Contribuyente', datafield: 'Contri', width: '20%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Generar Adeudos', datafield: 'GenerarAdeudos', width: '7%', cellsalign: 'center', align: 'center', hidden: false, sortable: false, filterable: false },";
    $columnasNombresGrid .= " { text: 'Pase De Cobro', datafield: 'PaseDeCobro', width: '7%', cellsalign: 'center', align: 'center', hidden: false, sortable: false, filterable: false },";
    $columnasNombresGrid .= " { text: 'Historial', datafield: 'Historial', width: '7%', cellsalign: 'center', align: 'center', hidden: false, sortable: false, filterable: false },";
    $columnasNombresGrid .= "]";

    $nombreExcel = str_replace(' ', '_', traeNombreFuncionGeneral($funcion, $db)).'_'.date('dmY');

    $contenido = array('datos' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid, 'nombreExcel' => $nombreExcel);
    $result = true;
}

if ($option == 'obtenerInformacion') {
    $contribuyente = $_POST['contribuyente'];
    
    $contratos = $_POST['contratos'];
    $unidadNegocio = $_POST['unidadNegocio'];
    $unidadEjecutora = $_POST['unidadEjecutora'];
    $dateini=date("Y-m-d", strtotime($_POST['txtFechaInicial']));
    $datefin=date("Y-m-d", strtotime($_POST['txtFechaFinal']));

    $sqlWhere = "";
    
    if (!empty($contribuyente) &&  $contribuyente != "-1") {
        $sqlWhere .= " AND contratos.id_debtorno IN (".$contribuyente.") ";
    }
    if (!empty($contratos) &&  $contratos != "'-1'") {
        $sqlWhere .= " AND configContrato.id_contratos IN (".$contratos.") ";
    } 
    if (!empty($unidadNegocio) &&  $unidadNegocio != "-1") {
        $sqlWhere .= " AND tags.tagref IN (".$unidadNegocio.") ";
    } 
    if (!empty($unidadEjecutora) &&  $unidadEjecutora != "-1") {
        $sqlWhere .= " AND ues.ue IN (".$unidadEjecutora.") ";
    }


    if (!empty($dateini) &&  !empty($datefin)) {

        // echo $dateini . " - ". $datefin;
         
        $sqlWhere .= " AND contratos.dtm_fecha_efectiva BETWEEN '" .  $dateini . " 00:00:00' AND '" .  $datefin . " 23:59:59'";
    }

    $info = array();
    
    $SQL = "SELECT contratos.id_contrato as clave,    
    CONCAT(tags.tagref , ' - ' , tags.tagdescription ) AS unidadNegocio,
    CONCAT(ues.ue , ' - ' , ues.desc_ue ) AS unidadEjecutora,    
    CONCAT(configContrato.id_contratos, ' - ', configContrato.id_loccode,' - ', locations.locationname) AS configContrato,
    configContrato.id_contratos AS idconfContrato,  
    debtorsmaster.name as contribuyente,
    debtorsmaster.debtorno as idContribuyente 	
    FROM tb_contratos AS contratos JOIN tags on (tags.tagref = contratos.tagref)    
    JOIN tb_cat_unidades_ejecutoras as ues on (ues.ue = contratos.ln_ue) 
    JOIN tb_contratos_contribuyentes as configContrato on (configContrato.id_contratos = contratos.id_confcontratos) 
    JOIN locations on (configContrato.id_loccode = locations.loccode)  
    JOIN debtorsmaster on (debtorsmaster.debtorno = contratos.id_debtorno)	
    WHERE contratos.ind_activo = '1'".$sqlWhere;
    
    $ErrMsg = "No se obtuvieron los botones para el proceso";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    // $sqlEQGeneral="SELECT eq_stockid from tb_partida_articulo where eq_stockid like'G%'";
    // $resultEQGeneral= DB_query($sqlEQGeneral, $db); echo "<br><pre>".$SQL."</pre>";
    // $myrowEQGeneral = DB_fetch_array($resultEQGeneral); echo "<br><pre>".$SQL."</pre>";

    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array(
            'Clave' => $myrow ['clave'],
            'CC' => $myrow ['configContrato'],
            'UE' => $myrow ['unidadEjecutora'],
            'Contri' => $myrow ['contribuyente'],
            'GenerarAdeudos' => '<a onclick="fnGenerarAdeudos('.$myrow ['clave'].')"><span class="glyphicon glyphicon-duplicate"></span></a>',
            'PaseDeCobro' => '<a onclick="fnPaseDeCobro('.$myrow ['clave'].')"><span class="glyphicon glyphicon-credit-card"></span></a>',
            'Historial' => '<a onclick="fnHistorial('.$myrow ['clave'].')"><span class="glyphicon glyphicon-th-list"></span></a>'
        );
    }
   
    // Columnas para el GRID
    $columnasNombres .= "[";
    $columnasNombres .= "{ name: 'Clave', type: 'string' },";
    $columnasNombres .= "{ name: 'CC', type: 'string' },";
    $columnasNombres .= "{ name: 'UE', type: 'string' },";
    $columnasNombres .= "{ name: 'Contri', type: 'string' },";
    $columnasNombres .= "{ name: 'GenerarAdeudos', type: 'string' },";
    $columnasNombres .= "{ name: 'PaseDeCobro', type: 'string' },";
    $columnasNombres .= "{ name: 'Historial', type: 'string' },";
    $columnasNombres .= "]";
    // Columnas para el GRID
    $columnasNombresGrid .= "[";
    //$columnasNombresGrid .= " { text: 'Clave', datafield: 'idFinalidad', width: '14%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Folio', datafield: 'Clave', width: '7%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Contrato', datafield: 'CC', width: '26%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Unidad Ejecutora', datafield: 'UE', width: '27%', cellsalign: 'left', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Contribuyente', datafield: 'Contri', width: '20%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Generar Adeudos', datafield: 'GenerarAdeudos', width: '7%', cellsalign: 'center', align: 'center', hidden: false, sortable: false, filterable: false },";
    $columnasNombresGrid .= " { text: 'Pase De Cobro', datafield: 'PaseDeCobro', width: '7%', cellsalign: 'center', align: 'center', hidden: false, sortable: false, filterable: false },";
    $columnasNombresGrid .= " { text: 'Historial', datafield: 'Historial', width: '7%', cellsalign: 'center', align: 'center', hidden: false, sortable: false, filterable: false },";
    $columnasNombresGrid .= "]";

    $nombreExcel = str_replace(' ', '_', traeNombreFuncionGeneral($funcion, $db)).'_'.date('dmY');

    $contenido = array('datos' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid, 'nombreExcel' => $nombreExcel);
    $result = true;
}



$dataObj = array('sql' => "", 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);