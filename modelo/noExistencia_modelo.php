<?php
/**
 * Modelo para el ABC de Finalidad
 *
 * @category ABC
 * @package ap_grp
 * @author Luis Aguilar Sandoval <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 02/08/2017
 * Fecha Modificación: 02/08/2017
 * Se realizan operación pero el Alta, Baja y Modificación. Mediante las validaciones creadas para la operación seleccionada
 */

$PageSecurity = 1;
$funcion=2320;
$PathPrefix = '../';

session_start();

// incluir archivos de apoyo
include($PathPrefix. "includes/SecurityUrl.php");
include($PathPrefix.'abajo.php');
include($PathPrefix . 'config.php');
include($PathPrefix . 'includes/ConnectDB.inc');
if ($abajo) {
    include($PathPrefix . 'includes/LanguageSetup.php');
}
include($PathPrefix.'includes/SecurityFunctions.inc');
include($PathPrefix.'includes/SQL_CommonFunctions.inc');
include($PathPrefix .'includes/DateFunctions.inc');

// declaracion de variables locales
$ErrMsg = _('');
$contenido = array();
$result = false;
$SQL = '';
$leasing = array();
$LeasingDetails = array();
$rowsSelected = "";
$RootPath = "";
$Mensaje = "";
$periodo = GetPeriod(date('d/m/Y'), $db);

header('Content-type: text/html; charset=ISO-8859-1');

$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

$option = $_POST['option'];

// para el grid
/*if ($option == 'detalleNoExistencias') {
	$info = array();
    $condicion= " 1=1 AND ln_activo = 1 ";
    $idrequisicion= $_POST["idReq"];
    $idnoexistencia= $_POST["idNoExist"];
    $seleccionar= "";

    $consulta="SELECT tb_no_existencias.nu_id_no_existencia as idNoExistencia, tb_no_existencias.nu_id_requisicion as idRequisicion, tb_no_existencias.dtm_fecharegistro as fechaRegistro, tb_no_existencias.nu_tag as tagref, tb_no_existencias.nu_ue as ue, tb_no_existencias.ln_usuario, tb_no_existencias.status, tb_no_existencias.txt_observaciones as comments, tb_no_existencia_detalle.nu_cantidad as qty, tb_no_existencia_detalle.ln_item_code as item, tb_no_existencia_detalle.txt_item_descripcion as itemdesc, tb_no_existencia_detalle.ln_cams, tb_no_existencia_detalle.ln_partida_esp, tb_no_existencia_detalle.ln_unidad_medida, tb_no_existencia_detalle.ln_renglon as orden, tb_no_existencia_detalle.ln_activo
    FROM  tb_no_existencias
    INNER JOIN tb_no_existencia_detalle on (tb_no_existencias.nu_id_no_existencia = tb_no_existencia_detalle.nu_id_no_existencia AND tb_no_existencias.nu_id_requisicion = tb_no_existencia_detalle.nu_id_requisicion)
    WHERE tb_no_existencias.nu_id_requisicion = '".$idrequisicion."' AND tb_no_existencias.nu_id_no_existencia ='".$idnoexistencia."'";
	$ErrMsg = "No se encontraron no existencias";
    $TransResult = DB_query($consulta, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        
        $info[] = array( 
        	'idNoExistencia' => $myrow ['idNoExistencia'],
        	'idRequisicion' => $myrow ['idRequisicion'],
            //'idRequisicion' => "<a target='_self' href='./noExistencia.php?$liga' style='color: blue; '><u>".$myrow["idRequisicion"]."</u></a>",
        	'tagref' => $myrow ['tagref'], 
        	'ue' => $myrow ['ue'],
        	'qty' => $myrow ['qty'],
        	'orden' => $myrow ['orden'],
            'item' => $myrow ['item'],
            'itemdesc' => $myrow ['itemdesc'] );
    }
    $contenido = array('datos' => $info);
    $result = true;
}*/

if ($option == 'detalleNoExistencias') {
    $info = array();
    $condicion= " 1=1 ";
    $idrequisicion= $_POST["idReq"];
    $idnoexistencia= $_POST["idNoExist"];
    $seleccionar= "";
    /*$consulta="SELECT tb_no_existencias.nu_id_no_existencia as idNoExistencia, tb_no_existencias.nu_id_requisicion as idRequisicion, tb_no_existencias.dtm_fecharegistro, DATE_FORMAT(tb_no_existencias.dtm_fecharegistro, '%Y-%m-%d') as fechaRegistro, tb_no_existencias.nu_tag as tagref, tb_no_existencias.nu_ue as ue, tb_no_existencias.ln_usuario, tb_no_existencias.status, tb_no_existencias.txt_observaciones as comments, tb_no_existencia_detalle.nu_cantidad as qty, tb_no_existencia_detalle.ln_item_code as item, tb_no_existencia_detalle.txt_item_descripcion as itemdesc, tb_no_existencia_detalle.ln_cams, tb_no_existencia_detalle.ln_partida_esp, tb_no_existencia_detalle.ln_unidad_medida, tb_no_existencia_detalle.ln_renglon as orden, tb_no_existencia_detalle.ln_activo
    FROM  tb_no_existencias
    INNER JOIN tb_no_existencia_detalle on (tb_no_existencias.nu_id_no_existencia = tb_no_existencia_detalle.nu_id_no_existencia AND tb_no_existencias.nu_id_requisicion = tb_no_existencia_detalle.nu_id_requisicion)
    WHERE tb_no_existencias.nu_id_requisicion = '".$idrequisicion."' AND tb_no_existencias.nu_id_no_existencia ='".$idnoexistencia."'";
    $ErrMsg = "No se encontraron no existencias";*/
    $consulta = "SELECT DISTINCT tb_no_existencias.nu_id_no_existencia as idNoExistencia, tb_no_existencias.nu_id_requisicion as idRequisicion, purchorders.requisitionno as noRequisition, DATE_FORMAT(tb_no_existencias.dtm_fecharegistro, '%Y-%m-%d') as fechaRegistro, tb_no_existencias.nu_tag as tagref, tb_no_existencias.nu_ue as ue, tb_no_existencias.ln_usuario, tb_no_existencias.status, tb_no_existencias.txt_observaciones as comments, tb_no_existencia_detalle.nu_cantidad as qty, tb_no_existencia_detalle.ln_item_code as item, tb_no_existencia_detalle.txt_item_descripcion as itemdesc, tb_no_existencia_detalle.ln_cams, tb_no_existencia_detalle.ln_partida_esp, tb_no_existencia_detalle.ln_unidad_medida, tb_no_existencia_detalle.ln_renglon as orden, tb_no_existencia_detalle.ln_activo, legalbusinessunit.legalid as iddependencia, CONCAT(legalbusinessunit.legalid,' - ',legalbusinessunit.legalname) as dependencia, tags.tagref as idunidadNegocio, CONCAT(tags.tagref,' - ',tags.tagdescription) as unidadNegocio, tb_cat_unidades_ejecutoras.ue as idunidadEjecutora, CONCAT(tb_cat_unidades_ejecutoras.ue,' - ',tb_cat_unidades_ejecutoras.desc_ue) as unidadEjecutora
    FROM  tb_no_existencias
    INNER JOIN tb_no_existencia_detalle on (tb_no_existencias.nu_id_no_existencia = tb_no_existencia_detalle.nu_id_no_existencia AND tb_no_existencias.nu_id_requisicion = tb_no_existencia_detalle.nu_id_requisicion AND tb_no_existencia_detalle.ln_activo = 1 AND tb_no_existencia_detalle.ln_renglon != 0)
    INNER JOIN tb_cat_unidades_ejecutoras on (tb_no_existencias.nu_ue = tb_cat_unidades_ejecutoras.ue and tb_no_existencias.nu_tag = tb_cat_unidades_ejecutoras.ur)
    INNER JOIN tags on (tb_no_existencias.nu_tag = tags.tagref) 
    INNER JOIN legalbusinessunit on (legalbusinessunit.legalid = tags.legalid )
    INNER JOIN purchorders on (purchorders.requisitionno = tb_no_existencias.nu_id_requisicion )
    WHERE  tb_no_existencias.nu_id_no_existencia ='".$idnoexistencia."'";

    $TransResult = DB_query($consulta, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        
        $info[] = array( 
            'idNoExistencia' => $myrow ['idNoExistencia'],
            'idRequisicion' => $myrow ['idRequisicion'],
            'noRequisition' => $myrow ['noRequisition'],
            'tagref' => $myrow ['tagref'], 
            'ue' => $myrow ['ue'],
            'fechaRegistro' => $myrow ['fechaRegistro'],
            'qty' => $myrow ['qty'],
            'orden' => $myrow ['orden'],
            'item' => $myrow ['item'],
            'itemdesc' => $myrow ['itemdesc'],
            'comments' =>  $myrow ['comments'],
            'iddependencia' =>  $myrow ['iddependencia'],
            'dependencia' =>  $myrow ['dependencia'],
            'idunidadNegocio' =>  $myrow ['idunidadNegocio'],
            'unidadNegocio' =>  $myrow ['unidadNegocio'],
            'idunidadEjecutora' =>  $myrow ['idunidadEjecutora'],
            'unidadEjecutora' =>  $myrow ['unidadEjecutora']);
    }
    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarUnidadNegocio') {
    $legalid = $_POST['legalid'];

    $sqlWhere = "";
    if ($legalid != 0 && !empty($legalid)) {
        $sqlWhere = " AND t.legalid IN(".$legalid.") ";
    }
    $info = array();
    $SQL = "SELECT  t.tagref, CONCAT(t.tagref, ' - ', t.tagdescription) as tagdescription 
            FROM sec_unegsxuser u,tags t 
            JOIN areas ON t.areacode = areas.areacode  
            WHERE u.tagref = t.tagref and u.userid = '" . $_SESSION['UserID'] . "' " . $sqlWhere . "
            ORDER BY t.tagref";

    $ErrMsg = "No se obtuvieron las URG";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'tagref' => $myrow ['tagref'], 'tagdescription' => $myrow ['tagdescription'] );
    }
    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'buscarStatusReq') {
    $idReq = $_POST['idReq'];
    $statusReq = "";
    $info = array();
    $SQL = "SELECT status FROM purchorders WHERE requisitionno = '$idReq'";
    $ErrMsg = "No se encontro un status";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    if(DB_num_rows($TransResult) == 1){
        while ($myrow = DB_fetch_array($TransResult)) {
            $info[] = array(
                'status' => $myrow ['status']
            );
        }
        $contenido = array('datos' => $info);
        $result = true;
    }else{
        $contenido = "Error al buscar el estatus de la requisicion";
        $result = false;
    }   
}

if (empty($consulta)) {
    $consulta= $SQL;
}

$dataObj = array(
    'sql' => '',
    'contenido' => $contenido,
    'result' => $result,
    'RootPath' => $RootPath,
    'ErrMsg' => $ErrMsg,
    'Mensaje' => $Mensaje);

echo json_encode($dataObj);