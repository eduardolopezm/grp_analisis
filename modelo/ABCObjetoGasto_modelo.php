<?php

/**
 * ABC de Objeto Gasto (Partida especifica) modelo
 *
 * @category ABC
 * @package ap_grp
 * @author Julio Cesar Garcia Baltazar <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 21/08/2017
 * Fecha Modificación: 21/08/2017
 * Se realizan operación pero el Alta, Baja y Modificación
 */



//ini_set('display_errors', 1);
//ini_set('log_errors', 1);
//ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
//error_reporting(E_ALL);
session_start();
$PageSecurity = 1;
$PathPrefix = '../';
//include($PathPrefix.'includes/session.inc');
header('Content-type: text/html; charset=ISO-8859-1');

include($PathPrefix.'abajo.php');
include($PathPrefix . 'config.php');
include($PathPrefix . 'includes/ConnectDB.inc');
//
if ($abajo) {
    include($PathPrefix . 'includes/LanguageSetup.php');
}
$funcion=1459;
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
$columnasNombres="";
$columnasNombresGrid = "";


header('Content-type: text/html; charset=ISO-8859-1');
$SQL = "SET NAMES 'utf8'";

$TransResult = DB_query($SQL, $db);



$option = $_POST['option'];

if ($option == 'mostrarCatalogo') {
    $sqlUR = " ";

    if (!empty($_POST['ur'])) {
        $ur = $_POST['ur'];
        $sqlUR = " WHERE partidacalculada = '".$ur."' and  a.activo = 1 and b.activo = 1 and c.activo = 'S'";
    } else {
        $sqlUR = " WHERE a.activo = 1 and b.activo = 1 and c.activo = 'S' ";
    }

    $info = array();
    $SQL = "SELECT DISTINCT a.id_clasificador, b.ccapmiles as capitulomiles, a.ccap as capitulo, a.ccon as concepto, c.conceptocalculado as conceptomiles, cparg as partida_gen, substring(partidacalculada,4,2) as partida_esp,
    partidacalculada as partida_esp_eliminarmodificar, partidacalculada, a.descripcion as nombre FROM tb_cat_partidaspresupuestales_partidaespecifica a join tb_cat_partidaspresupuestales_capitulo b on a.ccap = b.ccap
join tb_cat_partidaspresupuestales_concepto c on a.ccon = c.ccon and a.ccap = c.ccap ".$sqlUR . " ORDER BY partidacalculada ";
    $ErrMsg = "No se obtuvieron las objetos de gasto";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        if (!empty($_POST['ur'])) {
            $info[] = array( 'id_clasificador' => $myrow ['id_clasificador'],
            'capitulo' => $myrow ['capitulo'],
            'concepto' => $myrow ['capitulo'].$myrow ['concepto'],
            'partida_gen' => $myrow ['capitulo'].$myrow ['concepto'].$myrow ['partida_gen'],
            'partida_esp' => $myrow ['partida_esp'],
            'nombre' => $myrow ['nombre']);
        } else {
            $info[] = array( 'id_clasificador' => $myrow ['id_clasificador'],
                'PartidaCalculada' => $myrow ['partida_esp_eliminarmodificar'],
                'Capitulo' => $myrow ['capitulo'],
                'Concepto' => $myrow ['concepto'],
                'partida_gen' => $myrow ['partida_gen'],
                'partida_esp' => $myrow ['partida_esp'],
                'nombre' => $myrow ['nombre'],
                'Detalle' => '<a href=" http://dof.gob.mx/" target="_blank"><span class="glyphicon glyphicon-search"></span></a>',
                'Modificar' => '<a onclick="fnModificarOG('.$myrow ['partida_esp_eliminarmodificar'].')"><span class="glyphicon glyphicon-edit"></span></a>',
                'Eliminar' => '<a onclick="fnEliminarOG('.$myrow ['partida_esp_eliminarmodificar'].')"><span class="glyphicon glyphicon-trash"></span></a>' );
        }
    }

     // Columnas para el GRID
    $columnasNombres .= "[";
    $columnasNombres .= "{ name: 'PartidaCalculada', type: 'string' },";
    $columnasNombres .= "{ name: 'Capitulo', type: 'string' },";
    $columnasNombres .= "{ name: 'Concepto', type: 'string' },";
    $columnasNombres .= "{ name: 'partida_gen', type: 'string' },";
    $columnasNombres .= "{ name: 'partida_esp', type: 'string' },";
    $columnasNombres .= "{ name: 'nombre', type: 'string' },";
    //$columnasNombres .= "{ name: 'Detalle', type: 'string' },";
    $columnasNombres .= "{ name: 'Modificar', type: 'string' },";
    $columnasNombres .= "{ name: 'Eliminar', type: 'string' }";
    $columnasNombres .= "]";
    // Columnas para el GRID
    $columnasNombresGrid .= "[";
    $columnasNombresGrid .= " { text: 'Partida', datafield: 'PartidaCalculada', width: '7%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Capítulo', datafield: 'Capitulo', width: '7%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Concepto', datafield: 'Concepto', width: '7%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Partida Genérica', datafield: 'partida_gen', width: '10%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Partida Específica', datafield: 'partida_esp', width: '10%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Descripción', datafield: 'nombre', width: '45%', cellsalign: 'left', align: 'center', hidden: false },"; //antes width: '45%'
    //$columnasNombresGrid .= " { text: 'Ver Detalle', datafield: 'Detalle', width: '7%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Modificar', datafield: 'Modificar', width: '7%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Eliminar', datafield: 'Eliminar', width: '7%', cellsalign: 'center', align: 'center', hidden: false }";
    $columnasNombresGrid .= "]";

    $nombreExcel = str_replace(" ", "_", traeNombreFuncionGeneral($funcion, $db, $ponerNombre = '0'))."_".date('dmY');

    $contenido = array('datosCatalogo' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid, 'nombreExcel' => $nombreExcel);


    //$contenido = array('datosCatalogo' => $info);
    $result = true;
}

if ($option == 'AgregarCatalogo') {
    $capitulo = $_POST['capitulo'];
    $concepto = $_POST['concepto'];
    $partidageneral = $_POST['partidageneral'];
    $partidaespecifica = $_POST['capitulo'] . $_POST['concepto'] . $_POST['partidageneral'] . $_POST['partidaespecifica'];
    $partidaespecificasolita = $_POST['partidaespecifica'];
    


    $nombre = $_POST['nombre'];
    $proceso = $_POST['proceso'];

    if ($proceso == 'Modificar') {
        $info = array();
        
        $SQL = "UPDATE tb_cat_partidaspresupuestales_partidaespecifica SET descripcion = '$nombre',
        ccap = '$capitulo', ccon = '$concepto', cparg = '$partidageneral', partidacalculada = '$partidaespecifica' WHERE partidacalculada = '$partidaespecifica' ";
        $ErrMsg = "No se agrego la informacion de ".$partidaespecifica." - ".$nombre;
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        $contenido = "Se modificó el registro ".$partidaespecifica." del Catálogo Partida Específica con éxito";
        $result = true;
    } else {
        $SQL = "SELECT activo FROM tb_cat_partidaspresupuestales_partidaespecifica WHERE partidacalculada = '$partidaespecifica' ";
        $ErrMsg = "No se obtuvieron las partidas de gasto";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
        if (DB_num_rows($TransResult) == 0) {
            $info = array();
            $SQL = "INSERT INTO tb_cat_partidaspresupuestales_partidaespecifica (`ccap`, `ccon`, `cparg`, `cpar`, `partidacalculada`,`descripcion`, activo)
    	            VALUES ('".$capitulo."', '".$concepto."', '".$partidageneral."', '".$partidaespecificasolita."', '".$partidaespecifica."', '".$nombre."', 1)";
            $ErrMsg = "No se agregó la informacion de ".$partidaespecifica." - ".$nombre;

            $TransResult = DB_query($SQL, $db, $ErrMsg);

            $contenido = "Se agregó el registro ".$partidaespecifica." del Catálogo Partida Específica con éxito";
            $result = true;
        } else {
            $myrow = DB_fetch_array($TransResult);
            
            if ($myrow['activo']==1) {
                $Mensaje = "3|Error al insertar la partida específica.";
                $contenido = "Ya existe la partida específica  con la clave ".$partidaespecifica;
                $result = true;
            } else {
                $Mensaje = "Proceso completado.";
                $contenido = "Se agregó el registro ".$partidaespecifica." del Catálogo Partida Específica con éxito";

                $SQL = "UPDATE tb_cat_partidaspresupuestales_partidaespecifica SET descripcion = '$nombre', activo = 1, 
                ccap = '$capitulo', ccon = '$concepto', cparg = '$partidageneral', partidacalculada = '$partidaespecifica' WHERE partidacalculada = '$partidaespecifica' ";

                $TransResult = DB_query($SQL, $db, $ErrMsg);

                $result = true;
            }
        }
    }
}

if ($option == 'eliminarUR') {
    $id_claveespecifica = $_POST['id_claveespecifica'];

    $info = array();
    $SQL = "UPDATE tb_cat_partidaspresupuestales_partidaespecifica SET activo = '0' WHERE partidacalculada = '$id_claveespecifica' ";
    $ErrMsg = "No se eliminó el registro ".$id_claveespecifica." del Catálogo Partida Específica con éxito";
    $Mensaje = "1|Eliminación Exitosa.";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    $contenido = "Se eliminó  el registro ".$id_claveespecifica." del Catálogo Partida Específica con éxito";
    $result = true;
}

$dataObj = array('sql' => "", 'contenido' => $contenido, 'result' => $TransResult, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);
