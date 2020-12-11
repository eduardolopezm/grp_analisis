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
$funcion=2509;
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
    $id_contratos = $_POST['id_contratos'];
    $id_loccode = $_POST['id_loccode'];
    //$sqlUR = " WHERE  stockmaster.tipo_dato = 2";

    // if (!empty($id_parcial)) {
       
    // }
    $info = array();

    $SQL = "SELECT
	tb_contratos_contribuyentes.id_contratos as folio,
	tb_contratos_contribuyentes.id_loccode as principal,
	locations.locationname as principal_name,
	tb_contratos_contribuyentes.nu_estatus as estatus1,
	tb_contratos_contribuyentes.nu_recargos as recargos,
	tb_contratos_contribuyentes.nu_multa as multa,
    tb_contratos_contribuyentes.descripcion as descripcion,
    tb_contratos_contribuyentes.reporte as report,
    CONCAT(`tb_contratos_contribuyentes`.`id_loccode`, ' - ' , `locations`.`locationname`) AS 'name_descripcion',
    CASE WHEN tb_contratos_contribuyentes.nu_estatus = 1 THEN 'Activo' ELSE 'Inactivo' END AS estatus2,
    CASE WHEN tb_contratos_contribuyentes.nu_recargos = 1 THEN 'Si' ELSE 'No' END AS recargos2,
     CASE WHEN 	tb_contratos_contribuyentes.nu_multa = 1 THEN 'Si' ELSE 'No' END AS multa2
    FROM tb_contratos_contribuyentes
    JOIN locations on (locations.loccode = tb_contratos_contribuyentes.id_loccode)   
    ORDER BY locations.loccode ASC";
    $ErrMsg = "No se obtuvieron los contratos del contribuyente";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    

    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array(
            'Folio' => $myrow ['folio'],
            'Principal' => $myrow ['name_descripcion'],
            'Estatus' => $myrow ['estatus2'],
            'Recargos' => $myrow ['recargos2'],
            'Multa' => $myrow ['multa2'],
            'Descripcion' => $myrow ['descripcion'],
            'Modificar' => '<a onclick="fnModificar(\''.$myrow ['folio'].'\',\''.$myrow ['principal'].'\',\''.$myrow ['estatus1'].'\',\''.$myrow ['recargos'].'\',\''.$myrow ['multa'].'\',\''.$myrow ['descripcion'].'\',\''.$myrow ['report'].'\')"><span class="glyphicon glyphicon-edit"></span></a>',
            'Objetos' => '<a onclick="fnObjetos(\''.$myrow ['folio'].'\',\''.$myrow ['principal'].'\',\''.$myrow ['estatus1'].'\',\''.$myrow ['recargos'].'\',\''.$myrow ['multa'].'\')"><span class="glyphicon glyphicon-modal-window"></span></a>',
            'Atributos' => '<a onclick="fnAtributos(\''.$myrow ['folio'].'\',\''.$myrow ['name_descripcion'].'\',\''.$myrow ['principal_name'].'\',\''.$myrow ['recargos'].'\',\''.$myrow ['multa'].'\')"><span class="glyphicon glyphicon-list-alt"></span></a>'
        );
    }
    // Columnas para el GRID
    $columnasNombres .= "[";
    $columnasNombres .= "{ name: 'Folio', type: 'string' },";
    $columnasNombres .= "{ name: 'Principal', type: 'string' },";
    $columnasNombres .= "{ name: 'Estatus', type: 'string' },";
    $columnasNombres .= "{ name: 'Recargos', type: 'string' },";
    $columnasNombres .= "{ name: 'Multa', type: 'string' },";
    $columnasNombres .= "{ name: 'Descripcion', type: 'string' },";
    $columnasNombres .= "{ name: 'Modificar', type: 'string' },";
    $columnasNombres .= "{ name: 'Objetos', type: 'string' },";
    $columnasNombres .= "{ name: 'Atributos', type: 'string' },";
    $columnasNombres .= "]";
    // Columnas para el GRID
    $columnasNombresGrid .= "[";
    //$columnasNombresGrid .= " { text: 'Clave', datafield: 'idFinalidad', width: '14%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Folio', datafield: 'Folio', width: '13%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Objeto Principal', datafield: 'Principal', width: '13%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Estatus', datafield: 'Estatus', width: '13%', cellsalign: 'left', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Aplica recargos (S/N)', datafield: 'Recargos', width: '7%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Aplica Multa (S/N)', datafield: 'Multa', width: '12%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Descripción', datafield: 'Descripcion', width: '21%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Modificar', datafield: 'Modificar', width: '7%', cellsalign: 'center', align: 'center', hidden: false, sortable: false, filterable: false },";
    $columnasNombresGrid .= " { text: 'Objetos', datafield: 'Objetos', width: '7%', cellsalign: 'center', align: 'center', hidden: false, sortable: false, filterable: false },";
    $columnasNombresGrid .= " { text: 'Atributos', datafield: 'Atributos', width: '7%', cellsalign: 'center', align: 'center', hidden: false, sortable: false, filterable: false },";
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
    $id_contratos = $_POST['id_contratos'];
    $id_loccode = $_POST['id_loccode'];
    $nu_estatus = $_POST['nu_estatus'];
    $nu_recargos = $_POST['nu_recargos'];
    $nu_multa = $_POST['nu_multa'];
    $descripcion = $_POST['descripcion'];
    $reporte = $_POST['reporte'];
    $proceso = $_POST['proceso'];
    

    if ($proceso == 'Modificar') {
        $info = array();
        $SQL = "UPDATE  tb_contratos_contribuyentes SET  tb_contratos_contribuyentes.id_loccode = '$id_loccode', tb_contratos_contribuyentes.nu_estatus = '$nu_estatus', tb_contratos_contribuyentes.nu_recargos = '$nu_recargos', tb_contratos_contribuyentes.nu_multa  = '$nu_multa', tb_contratos_contribuyentes.userid = '".$_SESSION['UserID']."', tb_contratos_contribuyentes.descripcion = '$descripcion', tb_contratos_contribuyentes.reporte = '$reporte' WHERE  tb_contratos_contribuyentes.id_contratos = '$id_contratos'";
        $ErrMsg = "No se agrego la informacion de ".$id_loccode;
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        $contenido = "Se modificó el registro ".$id_loccode." del Contrato de Contribuyente con éxito";
        $result = true;
    } else {
        if(fnValidarExiste($id_loccode, $db)){
            
            $SQL = "SELECT nu_estatus FROM tb_contratos_contribuyentes WHERE id_loccode = '$id_loccode'  ORDER BY id_loccode ASC";
            $ErrMsg = "No se obtuvieron l0s contratos";
            $TransResult = DB_query($SQL, $db, $ErrMsg);
            if (DB_num_rows($TransResult) == 0) {
                $info = array(); 
                
                $SQL = "INSERT INTO `tb_contratos_contribuyentes` (`id_loccode`, `nu_estatus`, `nu_recargos`, `nu_multa`,`userid`,`descripcion`, `reporte` )
                VALUES ('".$id_loccode."', '".$nu_estatus."', '".$nu_recargos."', '".$nu_multa."', '".$_SESSION['UserID']."', '".$descripcion."', '".$reporte."')";
                $ErrMsg = "No se agrego la informacion de ". $id_loccode;
                $TransResult = DB_query($SQL, $db, $ErrMsg);

                $contenido = "Se agregó el registro ".$id_loccode." del Contrato de Contribuyente con éxito";
                $result = true;
            } else {
                $myrow = DB_fetch_array($TransResult);

                if($myrow['nu_estatus']==1){
                    $Mensaje = "3|Error al insertar el registro ".$id_loccode." del Catálogo Fuente del Recurso.";
                    $contenido = "Ya existe el Contrato de Contribuyente ".$id_loccode;
                    $result = false;
                }else{

                    $Mensaje = "Proceso no completado.";
                    $contenido = "El registro ".$id_loccode." del Contrato de Contribuyente ya existe";
                    $result = true;
                }
            }
          
        }else{
            $contenido = "No existe el Objeto Principal con la clave ".$id_loccode;
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
    $id_contratos = $_POST['id_contratos'];
    $nu_estatus = $_POST['nu_estatus'];
    $nu_recargos = $_POST['nu_recargos'];
    $nu_multa = $_POST['nu_multa'];
    $id_loccode = $_POST['id_loccode'];

    $sqlWhere = "";
    
    if (!empty($id_loccode) &&  $id_loccode != "'0'") {
        $sqlWhere .= " AND tb_contratos_contribuyentes.id_loccode IN (".$id_loccode.") ";
    }
    if (!empty($id_contratos) &&  $id_contratos != 0) {
        $sqlWhere .= " AND (tb_contratos_contribuyentes.id_contratos like '%".$id_contratos."%' OR tb_contratos_contribuyentes.id_contratos like '%".$id_contratos."%' )";
    } 
    if (!empty($nu_estatus) &&  $nu_estatus != 0) {
        $sqlWhere .= " AND (tb_contratos_contribuyentes.nu_estatus like '%".$nu_estatus."%' OR tb_contratos_contribuyentes.nu_estatus like '%".$nu_estatus."%' )";
    } 
    if (!empty($nu_recargos) &&  $nu_recargos != 0) {
        $sqlWhere .= " AND (tb_contratos_contribuyentes.nu_recargos like '%".$nu_recargos."%' OR tb_contratos_contribuyentes.nu_recargos like '%".$nu_recargos."%' )";
    }
    if (!empty($nu_multa) &&  $nu_multa != 0) {
        $sqlWhere .= " AND (tb_contratos_contribuyentes.nu_multa like '%".$nu_multa."%' OR tb_contratos_contribuyentes.nu_multa like '%".$nu_multa."%' )";
    }
    
    


    $info = array();
    
    $SQL = "SELECT
	tb_contratos_contribuyentes.id_contratos as folio,
	tb_contratos_contribuyentes.id_loccode as principal,
	locations.locationname as principal_name,
	tb_contratos_contribuyentes.nu_estatus as estatus1,
	tb_contratos_contribuyentes.nu_recargos as recargos,
    tb_contratos_contribuyentes.nu_multa as multa,
    tb_contratos_contribuyentes.descripcion as descripcion,
    CONCAT(`tb_contratos_contribuyentes`.`id_loccode`, ' - ' , `locations`.`locationname`) AS 'name_descripcion',
    CASE WHEN tb_contratos_contribuyentes.nu_estatus = 1 THEN 'Activo' ELSE 'Inactivo' END AS estatus2,
    CASE WHEN tb_contratos_contribuyentes.nu_recargos = 1 THEN 'Si' ELSE 'No' END AS recargos2,
     CASE WHEN 	tb_contratos_contribuyentes.nu_multa = 1 THEN 'Si' ELSE 'No' END AS multa2
    FROM tb_contratos_contribuyentes
    JOIN locations on (locations.loccode = tb_contratos_contribuyentes.id_loccode)    
    WHERE tb_contratos_contribuyentes.id_contratos = tb_contratos_contribuyentes.id_contratos".$sqlWhere;
    $ErrMsg = "No se obtuvieron los botones para el proceso";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    // $sqlEQGeneral="SELECT eq_stockid from tb_partida_articulo where eq_stockid like'G%'";
    // $resultEQGeneral= DB_query($sqlEQGeneral, $db);
    // $myrowEQGeneral = DB_fetch_array($resultEQGeneral);

    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array(
            'Folio' => $myrow ['folio'],
            'Principal' => $myrow ['name_descripcion'],
            'Estatus' => $myrow ['estatus2'],
            'Recargos' => $myrow ['recargos2'],
            'Multa' => $myrow ['multa2'],
            'Descripcion' => $myrow ['descripcion'],
            'Modificar' => '<a onclick="fnModificar(\''.$myrow ['folio'].'\',\''.$myrow ['principal'].'\',\''.$myrow ['estatus1'].'\',\''.$myrow ['recargos'].'\',\''.$myrow ['multa'].'\',\''.$myrow ['descripcion'].'\')"><span class="glyphicon glyphicon-edit"></span></a>',
            'Objetos' => '<a onclick="fnObjetos(\''.$myrow ['folio'].'\',\''.$myrow ['principal'].'\',\''.$myrow ['estatus1'].'\',\''.$myrow ['recargos'].'\',\''.$myrow ['multa'].'\')"><span class="glyphicon glyphicon-modal-window"></span></a>',
            'Atributos' => '<a onclick="fnAtributos(\''.$myrow ['folio'].'\',\''.$myrow ['name_descripcion'].'\',\''.$myrow ['principal_name'].'\',\''.$myrow ['recargos'].'\',\''.$myrow ['multa'].'\')"><span class="glyphicon glyphicon-list-alt"></span></a>');
    }
    // Columnas para el GRID
    $columnasNombres .= "[";
    $columnasNombres .= "{ name: 'Folio', type: 'string' },";
    $columnasNombres .= "{ name: 'Principal', type: 'string' },";
    $columnasNombres .= "{ name: 'Estatus', type: 'string' },";
    $columnasNombres .= "{ name: 'Recargos', type: 'string' },";
    $columnasNombres .= "{ name: 'Multa', type: 'string' },";
    $columnasNombres .= "{ name: 'Descripcion', type: 'string' },";
    $columnasNombres .= "{ name: 'Modificar', type: 'string' },";
    $columnasNombres .= "{ name: 'Objetos', type: 'string' },";
    $columnasNombres .= "{ name: 'Atributos', type: 'string' },";
    $columnasNombres .= "]";
    // Columnas para el GRID
    $columnasNombresGrid .= "[";
    //$columnasNombresGrid .= " { text: 'Clave', datafield: 'idFinalidad', width: '14%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Folio', datafield: 'Folio', width: '13%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Objeto Principal', datafield: 'Principal', width: '13%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Estatus', datafield: 'Estatus', width: '13%', cellsalign: 'left', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Aplica recargos (S/N)', datafield: 'Recargos', width: '7%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Aplica Multa (S/N)', datafield: 'Multa', width: '12%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Descripción', datafield: 'Descripcion', width: '21%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Modificar', datafield: 'Modificar', width: '7%', cellsalign: 'center', align: 'center', hidden: false, sortable: false, filterable: false },";
    $columnasNombresGrid .= " { text: 'Objetos', datafield: 'Objetos', width: '7%', cellsalign: 'center', align: 'center', hidden: false, sortable: false, filterable: false },";
    $columnasNombresGrid .= " { text: 'Atributos', datafield: 'Atributos', width: '7%', cellsalign: 'center', align: 'center', hidden: false, sortable: false, filterable: false },";
    $columnasNombresGrid .= "]";

    $nombreExcel = str_replace(' ', '_', traeNombreFuncionGeneral($funcion, $db)).'_'.date('dmY');

    $contenido = array('datos' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid, 'nombreExcel' => $nombreExcel);
    $result = true;
}

function fnValidarExiste($id_loccode, $db){
    $SQL = "SELECT * FROM locations WHERE activo = 1 and loccode = '".$id_loccode."' ORDER BY loccode ASC";
    $ErrMsg = "No se encontro la informacion de ".$id_loccode;
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