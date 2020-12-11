<?php
/**
 * ABC PPI(Programa Proyecto Inversion)
 *
 * @category     ABC
 * @package      ap_grp
 * @author       Arturo Lopez Peña  <[<email address>]>
 * @license      [<url>] [name]
 * @version      GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 01/08/2017
 * Fecha Modificación: 04/10/2017
 * Se realizan operación pero el Alta, Baja y Modificación.Conforme a las validaciones creadas para la operación seleccionada
 */

// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);
//ini_set('error_log', dirname(__FILE__) . '/error_log.txt');

$PageSecurity = 1;
$PathPrefix = '../';        
//include($PathPrefix.'includes/session.inc');
session_start();
require $PathPrefix . 'config.php';
require $PathPrefix . 'includes/ConnectDB.inc';
$funcion=2253;
require $PathPrefix.'includes/SecurityFunctions.inc';
require $PathPrefix.'includes/SQL_CommonFunctions.inc';

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


switch ($option) {
case 'mostrarCatalogo':
    $pyin=$_POST['pyin'];
    
    $sqlppi=  " WHERE activo = 'S' ";
    if (!empty($pyin) && ($pyin!='0')) {
        $sqlppi = " WHERE pyin = '".trim($_POST['pyin'])."' ";
    }

    $info = array();

    $SQL = "SELECT pyin,nomb,descripcion,cunr,inicio,fin,total,inv_ejercida,fact FROM g_cat_ppi ".$sqlppi."   ORDER BY pyin asc";
    $ErrMsg = "No se obtuvieron registros del Catálogo Programa Proyecto Inversión.";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ( $myrow = DB_fetch_array($TransResult) ) {
        if (!empty($_POST['pyin'])) {
            //Solo nombre de campo para consulta de modificar y eliminar
            /* $info[] = array( 
            'pyin' => $myrow ['pyin'],'nomb' => $myrow ['nomb'], 
            'descripcion' => $myrow ['descripcion'],
            'cunr' => $myrow ['cunr'],
            'inicio' => date("d-m-Y",strtotime($myrow ['inicio'])),
             'fin' => date("d-m-Y",strtotime($myrow ['fin'])),
            'total' => $myrow ['total'],
            'inv_ejercida' => $myrow ['inv_ejercida'],
            'fact' => date("d-m-Y",strtotime($myrow ['fact'])),
	        	
             'Modificar' => '<a onclick="fnModificar('.$myrow ['pyin'].')"><span class="glyphicon glyphicon-edit"></span></a>', 
             'Eliminar' => '<a onclick="fnEliminar('.$myrow ['pyin'].')"><span class="glyphicon glyphicon-trash"></span></a>' ); */

              $info[] = array( 
            'clave' => $myrow ['pyin'], 
             'nombre'=> $myrow ['nomb'],
            'descripcion' => $myrow ['descripcion'],
            'cunr' => $myrow ['cunr'],
            'fechainicio' => date("d-m-Y", strtotime($myrow ['inicio'])),
            'fechafin' => date("d-m-Y", strtotime($myrow ['fin'])),
            'total' => $myrow ['total'],
            'inversion' => $myrow ['inv_ejercida'],
            'fact' => date("d-m-Y", strtotime($myrow ['fact'])),

            'Modificar' => '<a onclick="fnModificar(\''.$myrow ['pyin'].'\')"><span class="glyphicon glyphicon-edit"></span></a>', 
            'Eliminar' => '<a onclick="fnEliminar(\''.$myrow ['pyin'].'\')"><span class="glyphicon glyphicon-trash"></span></a>' );
        }else{
            /*$info[] = array( 'pyin,10%,Clave,' => $myrow ['pyin'],'nomb,40%,Nombre,' => $myrow ['nomb'], 'descripcion,40%,Descripción,' => $myrow ['descripcion'],'cunr,6%,CUNR,' => $myrow ['cunr'],'inicio,30%,Fecha Inicio,' => $myrow ['inicio'],
            'fin,30%,Fecha Fin,' => $myrow ['fin'],
            'total,20%,Total,' => $myrow ['total'],
            'inv_ejercida,20%,Invesion Ejercida,' => $myrow ['inv_ejercida'],
            'Fact,30%,Fact,' => $myrow ['fact'],
             'Modificar,6%,Modificar,' => '<a onclick="fnModificar(\''.$myrow ['pyin'].'\')"><span class="glyphicon glyphicon-edit"></span></a>', 
             'Eliminar,6%,Eliminar,' => '<a onclick="fnEliminar(\''.$myrow ['pyin'].'\')"><span class="glyphicon glyphicon-trash"></span></a>' ); */ 
              $info[] = array( 
            'clave' => $myrow ['pyin'], 
             'nombre'=> $myrow ['nomb'],
            'descripcion' => $myrow ['descripcion'],
           
            'cunr' => $myrow ['cunr'],
            'fechainicio' => date("d-m-Y", strtotime($myrow ['inicio'])),
            'fechafin' => date("d-m-Y", strtotime($myrow ['fin'])),
            'total' => $myrow ['total'],
            'inversion' => $myrow ['inv_ejercida'],
            'fact' => date("d-m-Y", strtotime($myrow ['fact'])),

            'Modificar' => '<a onclick="fnModificar(\''.$myrow ['pyin'].'\')"><span class="glyphicon glyphicon-edit"></span></a>', 
            'Eliminar' => '<a onclick="fnEliminar(\''.$myrow ['pyin'].'\')"><span class="glyphicon glyphicon-trash"></span></a>' );
        }
    }

     // Columnas para el GRID
    $columnasNombres .= "[";
    $columnasNombres .= "{ name: 'clave', type: 'string' },";
    $columnasNombres .= "{ name: 'nombre', type: 'string' },";
    $columnasNombres .= "{ name: 'descripcion', type: 'string' },";
    $columnasNombres .= "{ name: 'cunr', type: 'string' },";
    $columnasNombres .= "{ name: 'fechainicio', type: 'string' },";
    $columnasNombres .= "{ name: 'fechafin', type: 'string' },";
    $columnasNombres .= "{ name: 'total', type: 'number'},";
    $columnasNombres .= "{ name: 'inversion', type: 'number' },";
    $columnasNombres .= "{ name: 'fact', type: 'string' },";
    $columnasNombres .= "{ name: 'Modificar', type: 'string' },";
    $columnasNombres .= "{ name: 'Eliminar', type: 'string' }";
    $columnasNombres .= "]";
    // Columnas para el GRID
    $columnasNombresGrid .= "[";
    $columnasNombresGrid .= " { text: 'PPI', datafield: 'clave', width: '9%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Nombre', datafield: 'nombre', width: '35%', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Descripción', datafield: 'descripcion', width: '35%', align: 'center',hidden: false },";
    $columnasNombresGrid .= " { text: 'CUNR', datafield: 'cunr', width: '5%', hidden: false },";
    $columnasNombresGrid .= " { text: 'Fecha Inicio',align: 'center', datafield: 'fechainicio', width: '8%', cellsalign: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Fecha Fin',align: 'center', datafield: 'fechafin', width: '8%',cellsalign: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Total', datafield: 'total', width: '10%',  cellsalign: 'right', align: 'center', cellsformat: 'C2',  hidden: false },";
    $columnasNombresGrid .= " { text: 'Inversión', datafield: 'inversion',  width: '10%', cellsalign: 'right', align: 'center', cellsformat: 'C2', hidden: false },";
    $columnasNombresGrid .= " { text: 'Fact',datafield: 'fact', width: '8%', align: 'center', cellsalign: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Modificar', datafield: 'Modificar', width: '7%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Eliminar', datafield: 'Eliminar', width: '7%', cellsalign: 'center', align: 'center', hidden: false }";
    $columnasNombresGrid .= "]";
    $funcion = 2253;
    $nombre= traeNombreFuncionGeneral($funcion, $db);
    $nombre=str_replace(" ","_",$nombre);
    $nombreExcel = $nombre.'_'.date('dmY');
    $contenido = array('datos' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid,'nombreExcel' => $nombreExcel);
    $result = true;

    break;

case 'AgregarCatalogo':
    
    $pyin = $_POST['pyinnuevo'];
    $descripcion = $_POST['descripcion'];
    $fecha=$_POST['fecha'];
    $proceso = $_POST['proceso'];
    $nombre=$_POST['nombre'];
    $cunr=$_POST['cunr'];
    $fechaFinal=$_POST['fechaFinal'];
    $total=$_POST['total'];
    $inv_ejercida=$_POST['inv_ejercida'];
    $fact=$_POST['fact'];

    if ($proceso == 'Modificar') {
        $SQL = "UPDATE g_cat_ppi SET descripcion = '$descripcion', nomb= '$nombre', cunr='$cunr',total='$total',inv_ejercida='$inv_ejercida', inicio=STR_TO_DATE('$fecha','%d-%m-%Y %H:%i:%s'),fin= STR_TO_DATE('$fechaFinal','%d-%m-%Y %H:%i:%s'),
	    fact= STR_TO_DATE('$fact','%d-%m-%Y %H:%i:%s')
	     WHERE pyin = '".$pyin."'";
        $ErrMsg = "No se modificó el registro para <b>".$pyin."</b> del Catálogo Programa Proyecto Inversión.";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        $contenido = "Se modificó el registro <b>".$pyin."</b> del Catálogo Programa Proyecto Inversión con éxito." ;
        $result = true;
    }
    else
    {
        $SQL = "SELECT pyin,activo FROM g_cat_ppi WHERE pyin='" . $pyin . "'";
        //AND descripcion = '$descripcion'";
        $ErrMsg = "No se obtuvieron registros del Catálogo Programa Proyecto Inversión";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        if (DB_num_rows($TransResult) == 0) {
            /*
            $SQL = "INSERT INTO g_cat_ppi (`pyin`, `descripcion`, `fecha`)
		            VALUES ('".$clave."', '".$descripcion."', '1')";
		            */

                    $SQL = "INSERT INTO g_cat_ppi (`pyin`, `descripcion`, `inicio`,`nomb`,`cunr`,`fin`,`total`,`inv_ejercida`,`fact`,`activo`)
                    VALUES ('". $pyin . "','".$descripcion."', STR_TO_DATE('".$fecha."','%d-%m-%Y %H:%i:%s'),'". $nombre . "','". $cunr . "', STR_TO_DATE('".$fechaFinal."','%d-%m-%Y %H:%i:%s'), '". $total . "',
                    '". $inv_ejercida . "', STR_TO_DATE('".$fact."','%d-%m-%Y %H:%i:%s'),'S')";
                    
            //STR_TO_DATE('".$fechaEfectiva."','%m-%d-%Y %H:%i:%s')' ";

            $ErrMsg = "No se agregó el registro para <b>".$pyin."</b> del Catálogo Programa Proyecto Inversión.";
            $TransResult = DB_query($SQL, $db, $ErrMsg);
                //“Se agregó el registro 08 del Catálogo Ramo con éxito.”
            $contenido = "Se agregó el registro <b>".$pyin."</b> del Catálogo Programa Proyecto Inversión con éxito.";
            $result = true;
        }
        else
        {
           $activo='';
            while ($myrow = DB_fetch_array($TransResult)){
                $activo=$myrow['activo'];
            }
            if($activo=='N'){

            $SQL = "UPDATE g_cat_ppi SET activo ='S', descripcion = '$descripcion', nomb= '$nombre', cunr='$cunr',total='$total',inv_ejercida='$inv_ejercida', inicio=STR_TO_DATE('$fecha','%d-%m-%Y %H:%i:%s'),fin= STR_TO_DATE('$fechaFinal','%d-%m-%Y %H:%i:%s'),
        fact= STR_TO_DATE('$fact','%d-%m-%Y %H:%i:%s')
         WHERE pyin = '".$pyin."'";

            $ErrMsg = "No se agregó el registro para <b>".$pyin."</b> del Catálogo  Programa Proyecto Inversión.";
            $TransResult = DB_query($SQL, $db, $ErrMsg);
            $contenido = "Se agregó el registro <b>".$pyin."</b> del Catálogo  Programa Proyecto Inversión con éxito.";
            $result = true;

            }else{
                $contenido = "Ya existe Programa Proyecto inversión  <b>".$pyin."</b>";
                $result = true;
            }
//  
        }
    }
    break;

case 'eliminarpyin':

    $pyin = $_POST['pyin'];
    $SQL = "UPDATE g_cat_ppi SET activo = 'N' WHERE pyin = '$pyin'";
    //$SQL         = "DELETE FROM g_cat_ppi where pyin='" . $pyin . "'";
    $ErrMsg = "No se eliminó el registro <b> ".$pyin."</b> del Catálogo Programa Proyecto Inversión.";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    $contenido ="Se eliminó el registro <b>".$pyin."</b> del Catálogo Programa Proyecto Inversión con éxito.";
    $result = true;
    break;
    
default:
    // code...
    break;
}



$dataObj = array( 'contenido' => $contenido, 'result' => $TransResult, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);
?>