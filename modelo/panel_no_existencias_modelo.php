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

if ($option == 'mostrarNoExistencias') {
    $info = array();
    $condicion= " 1=1 AND tb_no_existencia_detalle.ln_activo = 1 AND tb_no_existencia_detalle.ln_renglon > 0 ";
    $fechaini= ($_POST["fechainicio"] != '') ? date("Y-m-d", strtotime($_POST["fechainicio"])) : '' ;
    $fechafin= ($_POST["fechafin"] != '') ? date("Y-m-d", strtotime($_POST["fechafin"])) : '' ;
    $dependencia= $_POST["dependencia"];
    $unidadres= $_POST["unidadres"];
    $unidadeje= $_POST["unidadeje"];
    $idrequisicion= $_POST["requisicion"];
    $idnoexistencia = $_POST['noexistencia'];
    $selEstatusRequisicion = $_POST['selEstatusRequisicion'];
    //$funcion= $_POST["funcion"];
    $seleccionar= "";

    // separar la seleccion multiple de las unidades responsables
    $datosUR = "";
    foreach ($unidadres as $key) {
        if (empty($datosUR)) {
            $datosUR .= "'".$key."'";
        } else {
            $datosUR .= ", '".$key."'";
        }
    }
    foreach ($selEstatusRequisicion as $key) {
        if (empty($datosStatus)) {
            $datosStatus .= "'".$key."'";
        } else {
            $datosStatus .= ", '".$key."'";
        }
    }

    if (!empty($datosUR)) {
        $unidadres= $datosUR;
    }

    $condicion.= "AND tb_no_existencias.nu_id_requisicion!= 0 AND tb_no_existencias.nu_id_requisicion!= ''";
    
    if (!empty($selEstatusRequisicion)) {
        $condicion.= " AND tb_no_existencias.status in (".$datosStatus.") ";
    }

    if (!empty($fechaini) && !empty($fechafin)) {
        $condicion.= " AND purchorders.requisitionno!= 0 AND purchorders.requisitionno!= '' AND tb_no_existencias.dtm_fecharegistro>= '".$fechaini." 00:00:00' AND tb_no_existencias.dtm_fecharegistro<='".$fechafin." 23:59:59' ";
    }

    if (!empty($fechaini) && empty($fechafin)) {
        $condicion.= " AND purchorders.requisitionno!= 0 AND purchorders.requisitionno!= '' AND tb_no_existencias.dtm_fecharegistro>= '".$fechaini." 00:00:00'";
    }

    if (!empty($fechafin) && empty($fechaini)) {
        $condicion.= " AND purchorders.requisitionno!= 0 AND purchorders.requisitionno!= '' AND tb_no_existencias.dtm_fecharegistro<='".$fechafin." 23:59:59' ";
    }

    /*if (!empty($dependencia) && !strpos("@".$dependencia, "-1")) {
        $condicion.= " AND tb_no_existencias.nu_dependencia = ".$dependencia." ";
    }*/

    if (!empty($unidadres) && !strpos("@".$unidadres, "-1")) {
        $condicion.= " AND tb_no_existencias.nu_tag = ".$unidadres." ";
    }

    if (!empty($unidadeje) && !strpos("@".$unidadeje, "-1")) {
        $condicion.= " AND tb_no_existencias.nu_ue = '".$unidadeje."' ";
    }

    if (!empty($idnoexistencia) && intval($idnoexistencia)!= 0) {
        $condicion.= "AND tb_no_existencias.nu_id_no_existencia= '".$idnoexistencia."' ";
    }

    /*if (!empty($idrequisicion) && intval($idrequisicion)!= 0) {
        $condicion.= "AND tb_no_existencias.nu_id_requisicion= '".$idrequisicion."' ";
    }*/
    /*$consulta="SELECT tb_no_existencias.nu_id_no_existencia as idNoExistencia, tb_no_existencias.nu_id_requisicion as idRequisicion, tb_no_existencias.dtm_fecharegistro as fechaRegistro, tb_no_existencias.nu_tag as tagref, tb_no_existencias.nu_ue as ue, tb_no_existencias.ln_usuario as usuairo, tb_no_existencias.status as statusNoExistencia, tb_no_existencias.txt_observaciones as comments,tb_no_existencia_detalle.nu_cantidad as qty, tb_no_existencia_detalle.ln_item_code as itemcode, tb_no_existencia_detalle.txt_item_descripcion as itemdesc, tb_no_existencia_detalle.ln_cams as idCam, tb_no_existencia_detalle.ln_partida_esp as partida_esp, tb_no_existencia_detalle.ln_unidad_medida as unidad, tb_no_existencia_detalle.ln_renglon as orden, tb_no_existencia_detalle.ln_activo as activo
FROM  tb_no_existencias
JOIN tb_no_existencia_detalle on (tb_no_existencias.nu_id_no_existencia = tb_no_existencia_detalle.nu_id_no_existencia AND tb_no_existencias.nu_id_requisicion = tb_no_existencia_detalle.nu_id_requisicion) WHERE ".$condicion;*/
    $consulta="SELECT    tb_no_existencias.status as status,
                         tb_no_existencias.nu_id_no_existencia as idNoExistencia,
                         purchorders.orderno as orderno,
                         tb_no_existencias.nu_id_requisicion as idRequisicion,
                         purchorders.validfrom as fechaRegistro,
                         tb_no_existencias.nu_dependencia as dependencia,
                         tb_no_existencias.nu_tag as tagref,
                         tb_no_existencias.nu_ue as ue,
                         tb_no_existencias.txt_observaciones as comments,
                         tb_no_existencia_detalle.ln_activo as activo,
                         sum(tb_no_existencia_detalle.nu_cantidad) as qty
    FROM  tb_no_existencias 
    INNER JOIN tb_no_existencia_detalle on (tb_no_existencias.nu_id_no_existencia = tb_no_existencia_detalle.nu_id_no_existencia )
    INNER JOIN (
    SELECT MIN(purchorders.orderno) as orderno, purchorders.requisitionno, purchorders.validfrom FROM purchorders GROUP BY purchorders.requisitionno
    ) as purchorders ON purchorders.requisitionno = tb_no_existencias.nu_id_requisicion
    JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tb_no_existencias.nu_tag  AND sec_unegsxuser.userid = '".$_SESSION['UserID']."'
    JOIN `tb_sec_users_ue` ON `tb_sec_users_ue`.`userid` = '".$_SESSION['UserID']."' AND tb_no_existencias.nu_tag  = `tb_sec_users_ue`.`tagref` AND  tb_no_existencias.nu_ue = `tb_sec_users_ue`.`ue`
    WHERE ".$condicion. " 
    GROUP BY tb_no_existencias.nu_id_no_existencia, purchorders.orderno,  tb_no_existencias.nu_id_requisicion, purchorders.validfrom, tb_no_existencias.nu_tag, tb_no_existencias.nu_ue, tb_no_existencias.txt_observaciones, tb_no_existencia_detalle.ln_activo";
    // Se quita para solo mostrar un registro: INNER JOIN purchorders on (tb_no_existencias.nu_id_requisicion = purchorders.requisitionno)
    $ErrMsg = "No se encontraron no existencias";
    $TransResult = DB_query($consulta, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $enc = new Encryption;
        $url = "&idnoexist=>".$myrow['idNoExistencia']."&idrequisicion=>".$myrow['idRequisicion'];
        $url = $enc->encode($url);
        $liga= "URL=" . $url;
        //$url2 = "PrintPDF=1&idRequisicion=>".$myrow ['idRequisicion'];
        $url2 = "PrintPDF=1&idNoExistencia=>".$myrow['idNoExistencia'];
              //   PrintPDF=1&idRequisicion=254&idNoExistencia=21
        //$url2 = "PrintPDF=1&idRequisicion=>".$myrow ['idRequisicion']."&idNoExistencia=>".$myrow['idNoExistencia'];
        $url2 = $enc->encode($url2);
        $liga2= "URL=" . $url2;
        $info[] = array(
            //'idNoExistencia' => $myrow ['idNoExistencia'],
           'idNoExistencia' => "<a target='_self' href='./noExistencia.php?$liga' style='color: blue; '><u>".$myrow["idNoExistencia"]."</u></a>",
           'idNoExistenciaH' => $myrow['idNoExistencia'],
            'idRequisicion' => $myrow ['idRequisicion'],
            //'idRequisicion' => "<a target='_self' href='./noExistencia.php?$liga' style='color: blue; '><u>".$myrow["idRequisicion"]."</u></a>",
            'fechaRegistro' => date("d-m-Y", strtotime($myrow ['fechaRegistro'])),
            'dependencia' => $myrow ['dependencia'],
            'tagref' => $myrow ['tagref'],
            'ue' => $myrow ['ue'],
            'comments' => ( substr($myrow ['comments'], 0, 4)=="... " ? substr($myrow ['comments'], 4) : $myrow ['comments'] ),
            'qty' => $myrow ['qty'],
            'activo' => $myrow ['activo'],
            'imprimir' => '<a target="_blank" href="noExistenciaImprimirSolicitud.php?'.$liga2.'"><span class="glyphicon glyphicon glyphicon-print"></span></a> ',
            'status' => ($myrow ['status'] == 0) ? 'Cancelado' :( ($myrow ['status'] == 1) ? 'Por Autorizar' : 'Autorizado'  ));
    }
    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'mostrarEstatusNoExistencia') {
    // Estatus de solictud de la no exitencia
    $info = array();

    $info[] = array( 'value' => '0', 'texto' => 'Cancelado' );
    $info[] = array( 'value' => '1', 'texto' => 'Por Autorizar' );
    $info[] = array( 'value' => '2', 'texto' => 'Autorizado' );

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
