<?php
/**
 * Baja Patrimonial
 *
 * @category
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 16/10/2018
 * Fecha Modificación: 16/10/2018
 * Modelos para las operaciones de la Baja Patrimonial
 */

// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);
//ini_set('error_log', dirname(__FILE__) . '/error_log.txt');

$PageSecurity = 1;
$PathPrefix = '../';
//include($PathPrefix.'includes/session.inc');
session_start();
include($PathPrefix . 'config.php');
include($PathPrefix . 'includes/ConnectDB.inc');
$funcion=2480;
include($PathPrefix.'includes/SecurityFunctions.inc');
include($PathPrefix.'includes/SQL_CommonFunctions.inc');

// Include para el periodo
include($PathPrefix . 'includes/DateFunctions.inc');

$ErrMsg = _('');
$contenido = array();
$result = false;
$SQL = '';
$leasing = array();
$LeasingDetails = array();
$rowsSelected = "";
$RootPath = "";
$Mensaje = "";
$añoGeneral = '2017';//date('Y');
$dataJsonMeses = array('Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre');

header('Content-type: text/html; charset=ISO-8859-1');

$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

$option = $_POST['option'];

if ($option == 'obtenerActivos') {
    $tagref = $_POST['tagref'];
    $ue = $_POST['ue'];
    $selectTipo = $_POST['selectTipo'];
    $selectPartidaEsp = $_POST['selectPartidaEsp'];
    $type = $_POST['type'];
    $transno = $_POST['transno'];
    $almacen = $_POST['almacenes'];
    $cabms = $_POST['cambs'];

    $activosCaptura = "";
    $SQL = "SELECT nu_assetid FROM tb_Fixed_Baja_Patrimonial_Detalle
    WHERE nu_type = '".$type."' AND nu_transno = '".$transno."'";
    $ErrMsg = "No se los activos de la captura";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        if ($activosCaptura == "") {
            $activosCaptura .= "'".$myrow['nu_assetid']."'";
        } else {
            $activosCaptura .= ", '".$myrow['nu_assetid']."'";
        }
    }

    $info = array();
    $res = true;

    $sqlWhere = " AND fixedassets.status = 1 ";

    if ($activosCaptura != "") {
        $sqlWhere = " AND (fixedassets.status = 1 OR fixedassets.assetid IN (".$activosCaptura.") ) ";
    }

    if ($tagref != '-1') {
        $sqlWhere .= " AND fixedassets.tagrefowner = '".$tagref."' ";
    }

    if ($ue != '-1') {
        $sqlWhere .= " AND fixedassets.ue = '".$ue."' ";
    }

    if ($selectTipo != '-1') {
        $sqlWhere .= " AND fixedassets.tipo_bien = '".$selectTipo."' ";
    }

    if ($selectPartidaEsp != '') {
        $sqlWhere .= " AND fixedassets.assetcategoryid IN (".$selectPartidaEsp.") ";
    }
    if ($almacen != '') {
        $sqlWhere .= " AND fixedassets.loccode IN (".$almacen.") ";
    }
    if ($cabms != '') {
        $sqlWhere .= " AND fixedassets.cabm IN (".$cabms.") ";
    }

    $SQL = "SELECT
    DISTINCT
    fixedassets.assetid,
    fixedassets.serialno,
    fixedassets.barcode,
    fixedassets.cost,
    fixedassets.description,
    fixedassets.tagrefowner,
    fixedassets.ue,
    CASE WHEN fixedassets.status = 1 THEN 'Activo' ELSE 'Inactivo' END as status,
    fixedassets.active,
    fixedassets.tipo_bien,
    fixedassets.assetcategoryid,
    fixedassets.loccode,
    fixedassets.cabm,
    fixedassetstatus.fixedassetstatus,
    fixedAssetCategoryBien.description as tipoName,
    locations.locationname  as descripcionAlmacen
    FROM fixedassets
    JOIN sec_unegsxuser ON sec_unegsxuser.tagref = fixedassets.tagrefowner AND sec_unegsxuser.userid = '".$_SESSION['UserID']."'
    JOIN tb_sec_users_ue ON tb_sec_users_ue.tagref = fixedassets.tagrefowner AND tb_sec_users_ue.ue = fixedassets.ue AND tb_sec_users_ue.userid = '".$_SESSION['UserID']."'
    LEFT JOIN fixedassetstatus ON fixedassetstatus.fixedassetstatusid = fixedassets.status
    LEFT JOIN fixedAssetCategoryBien ON fixedAssetCategoryBien.id = fixedassets.tipo_bien
    LEFT JOIN locations ON fixedassets.loccode = locations.loccode
    WHERE 1 = 1 ".$sqlWhere."
    ORDER BY fixedassets.description ASC, fixedassets.barcode ASC";
    $ErrMsg = "No se obtuvieron las razones sociales";

    //echo "sql: " .$SQL ;
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array(
            'noReg' => $myrow ['assetid'],
            'serial' => $myrow ['serialno'],
            'noInventario' => $myrow ['barcode'],
            'costo' => $myrow ['cost'],
            'descripcion' => $myrow ['description'],
            'ur' => $myrow ['tagrefowner'],
            'ue' => $myrow ['ue'],
            'estatus' => $myrow ['status'],
            'activo' => $myrow ['active'],
            'tipo' => $myrow ['tipo_bien'],
            'partida' => $myrow ['assetcategoryid'],
            'estatusName' => $myrow ['fixedassetstatus'],
            'almacen' => $myrow ['descripcionAlmacen'],
            'tipoName' => $myrow ['tipoName'],
            'observaciones' => ''
        );
    }

    if (empty($info)) {
        $Mensaje = "No se encontró la información";
        $res = false;
    }

    $contenido = array('datos' => $info);
    $result = $res;
}

if ($option == 'cargarInfoNoCaptura') {
    $type = $_POST['type'];
    $transno = $_POST['transno'];

    $info = array();
    $statusname = "";
    $estatus = "";
    $legalid = "";
    $tagref = "";
    $justificacion = "";
    $ln_ue = "";
    $selectTipo = '';
    $selectPartidaEsp = '';
    $fechaCaptura = date('d-m-Y');

    $SQL = "SELECT
    DISTINCT
    tb_Fixed_Baja_Patrimonial.nu_type,
    tb_Fixed_Baja_Patrimonial.nu_transno,
    0 as total,
    tb_Fixed_Baja_Patrimonial.nu_estatus,
    tb_Fixed_Baja_Patrimonial.sn_userid,
    www_users.realname,
    DATE_FORMAT(tb_Fixed_Baja_Patrimonial.dtm_fecha, '%d-%m-%Y') as fecha_captura,
    tb_Fixed_Baja_Patrimonial.sn_tagref,
    tags.tagname,
    tags.legalid,
    fixedAssetCategoryBien.description as tipoBien,
    CONCAT(tb_Fixed_Baja_Patrimonial.nu_type, ' - ', systypescat.typename) as nombreSuficiencia,
    CONCAT(tags.tagref, ' - ', tags.tagname) as tagname,
    tb_Fixed_Baja_Patrimonial.nu_estatus as statusid,
    tb_botones_status.statusname,
    tb_Fixed_Baja_Patrimonial.sn_tagref,
    tb_Fixed_Baja_Patrimonial.ln_ue,
    tb_Fixed_Baja_Patrimonial.txt_justificacion,
    tb_Fixed_Baja_Patrimonial.nu_tipo_bien,
    tb_Fixed_Baja_Patrimonial.ln_partida,
    tb_Fixed_Baja_Patrimonial.ln_cabms,
    tb_Fixed_Baja_Patrimonial.nu_loccode,
    tb_Fixed_Baja_Patrimonial.dtm_fecha_baja
    FROM tb_Fixed_Baja_Patrimonial
    LEFT JOIN systypescat ON systypescat.typeid = tb_Fixed_Baja_Patrimonial.nu_type
    LEFT JOIN www_users ON www_users.userid = tb_Fixed_Baja_Patrimonial.sn_userid
    JOIN tags ON tags.tagref = tb_Fixed_Baja_Patrimonial.sn_tagref
    LEFT JOIN tb_botones_status ON tb_botones_status.statusid = tb_Fixed_Baja_Patrimonial.nu_estatus AND tb_botones_status.sn_funcion_id = tb_Fixed_Baja_Patrimonial.sn_funcion_id
    LEFT JOIN fixedAssetCategoryBien ON fixedAssetCategoryBien.id = tb_Fixed_Baja_Patrimonial.nu_tipo_bien
    WHERE 
    tb_Fixed_Baja_Patrimonial.nu_type = '".$type."'
    AND tb_Fixed_Baja_Patrimonial.nu_transno = '".$transno."'";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $legalid = $myrow['legalid'];
        $tagref = $myrow['sn_tagref'];
        $estatus = $myrow['nu_estatus'];
        $statusname = $myrow['statusname'];
        $justificacion = $myrow['txt_justificacion'];
        $ln_ue = $myrow['ln_ue'];
        $fechaCaptura = $myrow['fecha_captura'];
        $selectTipo = $myrow['nu_tipo_bien'];
        $selectPartidaEsp = $myrow['ln_partida'];
        $cabms = $myrow['ln_cabms'];
        $almacenes = $myrow['nu_loccode'];
        $fechaBaja = $myrow['dtm_fecha_baja'];
    }

    if (empty($justificacion)) {
        $justificacion = "";
    }

    $SQL = "SELECT
    fixedassets.assetid,
    fixedassets.serialno,
    fixedassets.barcode,
    fixedassets.cost,
    fixedassets.description,
    fixedassets.tagrefowner,
    fixedassets.ue,
    CASE WHEN fixedassets.status = 1 THEN 'Activo' ELSE 'Inactivo' END as status,
    fixedassets.active,
    fixedassets.tipo_bien,
    fixedassets.assetcategoryid,
    fixedassetstatus.fixedassetstatus,
    fixedAssetCategoryBien.description as tipoName,
    tb_Fixed_Baja_Patrimonial_Detalle.observaciones,
    locations.locationname  as descripcionAlmacen
    FROM tb_Fixed_Baja_Patrimonial_Detalle
    JOIN fixedassets ON fixedassets.assetid = tb_Fixed_Baja_Patrimonial_Detalle.nu_assetid
    JOIN sec_unegsxuser ON sec_unegsxuser.tagref = fixedassets.tagrefowner AND sec_unegsxuser.userid = '".$_SESSION['UserID']."'
    JOIN tb_sec_users_ue ON tb_sec_users_ue.tagref = fixedassets.tagrefowner AND tb_sec_users_ue.ue = fixedassets.ue AND tb_sec_users_ue.userid = '".$_SESSION['UserID']."'
    LEFT JOIN fixedassetstatus ON fixedassetstatus.fixedassetstatusid = fixedassets.status
    LEFT JOIN fixedAssetCategoryBien ON fixedAssetCategoryBien.id = fixedassets.tipo_bien
    LEFT JOIN locations ON fixedassets.loccode = locations.loccode
    WHERE 
    tb_Fixed_Baja_Patrimonial_Detalle.nu_type = '".$type."'
    AND tb_Fixed_Baja_Patrimonial_Detalle.nu_transno = '".$transno."'
    ORDER BY tb_Fixed_Baja_Patrimonial_Detalle.nu_mov ASC";
    $ErrMsg = "No se obtuvieron las razones sociales";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array(
            'noReg' => $myrow ['assetid'],
            'serial' => $myrow ['serialno'],
            'noInventario' => $myrow ['barcode'],
            'costo' => $myrow ['cost'],
            'descripcion' => $myrow ['description'],
            'ur' => $myrow ['tagrefowner'],
            'ue' => $myrow ['ue'],
            'estatus' => $myrow ['status'],
            'activo' => $myrow ['active'],
            'tipo' => $myrow ['tipo_bien'],
            'partida' => $myrow ['assetcategoryid'],
            'estatusName' => $myrow ['fixedassetstatus'],
            'tipoName' => $myrow ['tipoName'],
            'observaciones' => $myrow ['observaciones'],
            'almacen' => $myrow ['descripcionAlmacen']
        );
    }

    $reponse['datos'] = $info;
    $reponse['legalid'] = $legalid;
    $reponse['tagref'] = $tagref;
    $reponse['ln_ue'] = $ln_ue;
    $reponse['transno'] = $transno;
    $reponse['type'] = $type;
    $reponse['estatus'] = $estatus;
    $reponse['statusname'] = $statusname;
    $reponse['justificacion'] = $justificacion;
    $reponse['fechaCaptura'] = $fechaCaptura;
    $reponse['selectTipo'] = $selectTipo;
    $reponse['selectPartidaEsp'] = $selectPartidaEsp;
    $reponse['cabms'] = $cabms;
    $reponse['almacenes'] = $almacenes;
    $reponse['fechaBaja'] = $fechaBaja;

    $contenido = $reponse;
    $result = true;
}

if ($option == 'guardarOperacion') {
    $datosReducciones = $_POST['datosCapturaReducciones'];
    $type = $_POST['type'];
    $transno = $_POST['transno'];
    $legalid = $_POST['legalid'];
    $tagref = $_POST['tagref'];
    $estatus = $_POST['estatus'];
    $fechaCaptura = $_POST['fechaCaptura'];
    $justificacion = $_POST['justificacion'];
    $ue = $_POST['ue'];
    $tipoSuficiencia = $_POST['selectTipo'];
    $selectPartidaEsp = $_POST['selectPartidaEsp'];
    $selectAlmacen = $_POST['selectAlmacen'];
    $selectCABMS = $_POST['selectCABMS'];
    $fechaBaja = $_POST['fechaBaja'];
    
    if (empty($transno)) {
        $transno = GetNextTransNo($type, $db);
        $datosPresupuesto['transno'] = $transno;
    }

    if ($selectPartidaEsp == '-1') {
        $selectPartidaEsp = "";
    }

    $SQL = "SELECT nu_type, nu_transno FROM tb_Fixed_Baja_Patrimonial 
            WHERE 
            nu_type = '".$type."' 
            AND nu_transno = '".$transno."' ";
    $ErrMsg = "No se pudo almacenar la información";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    if (DB_num_rows($TransResult) == 0) {
        $sqlOperacion = 1;
    } else {
        $sqlOperacion = 2;
    }
    if ($sqlOperacion == 1) {
        // Agregar datos
        $SQL = "INSERT INTO tb_Fixed_Baja_Patrimonial
        (dtm_fecha,
        sn_userid,
        txt_justificacion,
        nu_type,
        nu_transno,
        nu_estatus,
        sn_tagref,
        sn_funcion_id,
        ln_ue,
        nu_tipo_bien,
        ln_partida,
        ln_cabms,
        nu_loccode,
        dtm_fecha_baja
        )
        VALUES
        (NOW(), 
        '".$_SESSION['UserID']."', 
        '".$justificacion."', 
        '".$type."', 
        '".$transno."', 
        '".$estatus."', 
        '".$tagref."', 
        ".$funcion.",
        '".$ue."',
        '".$tipoSuficiencia."',
        '".$selectPartidaEsp."',
        '".$selectCABMS."',
        '".$selectAlmacen."',
        '".fnFormatoFechaYMD($fechaBaja,'-')."'
        )";
        $ErrMsg = "No se pudo almacenar la información";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
    } else if ($sqlOperacion == 2) {
        // Actualizar datos
        $SQL = "UPDATE tb_Fixed_Baja_Patrimonial SET 
        sn_userid = '".$_SESSION['UserID']."', 
        txt_justificacion = '".$justificacion."', 
        nu_estatus = '".$estatus."',
        sn_tagref = '".$tagref."',
        sn_funcion_id = ".$funcion.",
        ln_ue = '".$ue."',
        nu_tipo_bien = '".$tipoSuficiencia."',
        ln_partida = '".$selectPartidaEsp."',
        ln_cabms = '".$selectCABMS."',
        nu_loccode = '".$selectAlmacen."',
        dtm_fecha_baja = '".fnFormatoFechaYMD($fechaBaja,'-')."'
        WHERE 
        nu_type = '".$type."' 
        AND nu_transno = '".$transno."'";
        $ErrMsg = "No se pudo almacenar la información";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
    }

    // Regresar a disponible activos anteriores
    $SQL = "UPDATE fixedassets
    JOIN tb_Fixed_Baja_Patrimonial_Detalle ON tb_Fixed_Baja_Patrimonial_Detalle.nu_assetid = fixedassets.assetid 
    AND tb_Fixed_Baja_Patrimonial_Detalle.nu_type = '".$type."' 
    AND tb_Fixed_Baja_Patrimonial_Detalle.nu_transno = '".$transno."'
    SET fixedassets.status = '1'";
    $ErrMsg = "No se actualizo los registros anteriores a disponible";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    $SQL = "DELETE FROM tb_Fixed_Baja_Patrimonial_Detalle 
    WHERE 
    tb_Fixed_Baja_Patrimonial_Detalle.nu_type = '".$type."' 
    AND tb_Fixed_Baja_Patrimonial_Detalle.nu_transno = '".$transno."'";
    $ErrMsg = "No se borraron los registros anteriores";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    foreach ($datosReducciones as $datosClave) {
        $SQL = "INSERT INTO tb_Fixed_Baja_Patrimonial_Detalle
        (dtm_fecha,
        sn_userid,
        nu_type,
        nu_transno,
        nu_assetid,
        observaciones,
        afectacion_disposicion
        )
        VALUES
        (NOW(), 
        '".$_SESSION['UserID']."',
        '".$type."', 
        '".$transno."',
        '".$datosClave['noReg']."',
        '".$datosClave['observaciones']."',
        0
        )";
        $ErrMsg = "No se pudo almacenar la información";
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        $SQL = "UPDATE fixedassets SET status = '12' WHERE assetid = '".$datosClave['noReg']."'";
        $ErrMsg = "No se pudo actualizar la información";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
    }    

    $Mensaje = "Se ha guardado exitosamente la Baja Patrimonial con Folio ".$transno;

    //Obtener nombre de estatus
    $statusname = "";
    $SQL = "SELECT statusname as statusname, statusname as statusname2
            FROM tb_botones_status WHERE statusid = '".$estatus."' AND tb_botones_status.sn_funcion_id = '".$funcion."'";
    $ErrMsg = "No se obtuvo el nombre del Estatus";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $statusname = $myrow['statusname'];
    }

    $datosPresupuesto['transno'] = $transno;
    $datosPresupuesto['type'] = $type;
    $datosPresupuesto['estatus'] = $estatus;
    $datosPresupuesto['statusname'] = $statusname;

    $contenido = array('datos' => $datosPresupuesto);
    $result = true;
}

function fnFormatoFechaYMD($fecha,$separador){
    $fechaFormateada = "";

    if($fecha == ""){
        $fechaFormateada = "0000-00-00";
    }else{
        list($dia, $mes, $anio) = explode($separador, $fecha);
        $fechaFormateada = $anio.'-'.$mes.'-'.$dia;
    }
    
    return $fechaFormateada;
}

if ($option == 'mostrarUnidadNegocio') {
    $legalid = $_POST['legalid'];

    $sqlWhere = "";
    if ($legalid != '-1' and !empty($legalid)) {
        $sqlWhere = " AND t.legalid IN(".$legalid.") ";
    }
    $info = array();
    $SQL = "SELECT  t.tagref as value, CONCAT(t.tagref, ' - ', t.tagdescription) as texto, t.tagref
            FROM sec_unegsxuser u,tags t 
            WHERE u.tagref = t.tagref and u.userid = '" . $_SESSION['UserID'] . "' ".$sqlWhere." 
            ORDER BY t.tagref ";
    $ErrMsg = "No se obtuvieron las URG";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array( 'value' => $myrow ['value'], 'texto' => $myrow ['texto'] );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

if ($option == 'obtenerBotones') {
    $autorizarGeneral = $_POST['autorizarGeneral'];
    $soloActFoliosAutorizada = $_POST['soloActFoliosAutorizada'];
    $sqlWhere = " AND tb_botones_status.statusid <> '98' ";
    if ($autorizarGeneral == '1') {
        // Si es autorizar solo mostrar rechazar y autorizar
        $sqlWhere = " AND tb_botones_status.statusid IN ('5', '99') ";
    }

    if ($soloActFoliosAutorizada == '1') {
        // Estatus 7 todo finalizado
        $sqlWhere = " AND tb_botones_status.statusid IN ('98') ";
    }

    $info = array();
    $SQL = "SELECT 
            distinct tb_botones_status.functionid,
            tb_botones_status.statusid,
            tb_botones_status.statusname,
            tb_botones_status.namebutton,
            tb_botones_status.functionid,
            tb_botones_status.adecuacionPresupuestal,
            tb_botones_status.clases
            FROM tb_botones_status
            LEFT JOIN sec_profilexuser ON sec_profilexuser.userid = '".$_SESSION['UserID']."'
            LEFT JOIN sec_funxprofile ON sec_funxprofile.profileid = sec_profilexuser.profileid
            WHERE 
            (tb_botones_status.sn_funcion_id = '".$funcion."')
            AND (tb_botones_status.sn_flag_disponible = 1)
            AND (tb_botones_status.sn_adecuacion_presupuestal = 1)
            AND
            (tb_botones_status.functionid = sec_funxprofile.functionid 
            OR 
            tb_botones_status.functionid = (SELECT sec_funxuser.functionid FROM sec_funxuser WHERE sec_funxuser.functionid = tb_botones_status.functionid AND sec_funxuser.userid = '".$_SESSION['UserID']."')
            ) ".$sqlWhere."
            ORDER BY tb_botones_status.functionid ASC
            ";
    $ErrMsg = "No se obtuvieron los botones para el proceso";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $info[] = array(
            'statusid' => $myrow ['statusid'],
            'statusname' => $myrow ['statusname'],
            'namebutton' => $myrow ['namebutton'],
            'functionid' => $myrow ['functionid'],
            'clases' => $myrow ['clases']
        );
    }

    $contenido = array('datos' => $info);
    $result = true;
}

$dataObj = array('sql' => '', 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);
