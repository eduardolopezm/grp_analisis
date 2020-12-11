<?php
/**
 * Disposición Final de Bienes
 *
 * @category
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 16/10/2018
 * Fecha Modificación: 16/10/2018
 * Modelos para las operaciones de la Disposición Final de Bienes
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
$funcion=2487;
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

define('SUBIDAARCHIVOS', realpath(dirname(__FILE__)) . '/' . $PathPrefix . 'archivos/');

$option = $_POST['option'];

if ($option == 'obtenerActivos') {
    $tagref = $_POST['tagref'];
    $ue = $_POST['ue'];
    $selectTipo = $_POST['selectTipo'];
    $txtFolioBaja = $_POST['txtFolioBaja'];
    $type = $_POST['type'];
    $transno = $_POST['transno'];

    $activosCaptura = "";
    $SQL = "SELECT nu_assetid FROM tb_Fixed_Baja_Patrimonial_Detalle
    WHERE nu_type = '".$type."' AND nu_transno = '".$transno."' and afectacion_disposicion = 0";
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

    $sqlWhere = " ";

    if ($activosCaptura != "") {
        // $sqlWhere = " AND (fixedassets.status = 1 OR fixedassets.assetid IN (".$activosCaptura.") ) ";
    }

    if ($tagref != '-1') {
        $sqlWhere .= " AND tb_Fixed_Baja_Patrimonial.sn_tagref = '".$tagref."' ";
    }

    if ($ue != '-1') {
        $sqlWhere .= " AND tb_Fixed_Baja_Patrimonial.ln_ue = '".$ue."' ";
    }

    if ($selectTipo != '-1') {
        $sqlWhere .= " AND tb_Fixed_Baja_Patrimonial.nu_tipo_bien = '".$selectTipo."' ";
    }

    $SQL = "
    SELECT
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
    fixedassetstatus.fixedassetstatus,
    fixedAssetCategoryBien.description as tipoName
    FROM tb_Fixed_Baja_Patrimonial
    JOIN tb_Fixed_Baja_Patrimonial_Detalle ON tb_Fixed_Baja_Patrimonial_Detalle.nu_type = tb_Fixed_Baja_Patrimonial.nu_type AND tb_Fixed_Baja_Patrimonial_Detalle.nu_transno = tb_Fixed_Baja_Patrimonial.nu_transno and tb_Fixed_Baja_Patrimonial_Detalle.afectacion_disposicion = 0
    JOIN fixedassets ON fixedassets.assetid = tb_Fixed_Baja_Patrimonial_Detalle.nu_assetid AND fixedassets.status = 12
    JOIN sec_unegsxuser ON sec_unegsxuser.tagref = fixedassets.tagrefowner AND sec_unegsxuser.userid = '".$_SESSION['UserID']."'
    JOIN tb_sec_users_ue ON tb_sec_users_ue.tagref = fixedassets.tagrefowner AND tb_sec_users_ue.ue = fixedassets.ue AND tb_sec_users_ue.userid = '".$_SESSION['UserID']."'
    LEFT JOIN fixedassetstatus ON fixedassetstatus.fixedassetstatusid = fixedassets.status
    LEFT JOIN fixedAssetCategoryBien ON fixedAssetCategoryBien.id = fixedassets.tipo_bien
    WHERE 
    tb_Fixed_Baja_Patrimonial.nu_estatus = '4'
    AND tb_Fixed_Baja_Patrimonial.nu_transno = '".$txtFolioBaja."' ".$sqlWhere."
    ORDER BY fixedassets.description ASC, fixedassets.barcode ASC";
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
            'tipoName' => $myrow ['tipoName']
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
    $fechaCaptura = date('d-m-Y');

    $SQL = "SELECT
    DISTINCT
    tb_dp.nu_type,
    tb_dp.nu_transno,
    tb_dp.nu_transno_baja,
    0 as total,
    tb_dp.nu_estatus,
    tb_dp.sn_userid,
    www_users.realname,
    DATE_FORMAT(tb_dp.dtm_fecha, '%d-%m-%Y') as fecha_captura,
    tb_dp.sn_tagref,
    tags.tagname,
    tags.legalid,
    fixedAssetCategoryBien.description as tipoBien,
    CONCAT(tb_dp.nu_type, ' - ', systypescat.typename) as nombreSuficiencia,
    CONCAT(tags.tagref, ' - ', tags.tagname) as tagname,
    tb_dp.nu_estatus as statusid,
    tb_botones_status.statusname,
    tb_dp.sn_tagref,
    tb_dp.ln_ue,
    tb_dp.txt_justificacion,
    tb_dp.nu_tipo_bien,
    tb_dp.nu_type_disposicion
    FROM tb_Fixed_Disposicion_Patrimonial as tb_dp
    LEFT JOIN systypescat ON systypescat.typeid = tb_dp.nu_type
    LEFT JOIN www_users ON www_users.userid = tb_dp.sn_userid
    JOIN tags ON tags.tagref = tb_dp.sn_tagref
    LEFT JOIN tb_botones_status ON tb_botones_status.statusid = tb_dp.nu_estatus AND tb_botones_status.sn_funcion_id = tb_dp.sn_funcion_id
    LEFT JOIN fixedAssetCategoryBien ON fixedAssetCategoryBien.id = tb_dp.nu_tipo_bien
    WHERE 
    tb_dp.nu_type = '".$type."'
    AND tb_dp.nu_transno = '".$transno."'";
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
        $selectTipoDisposicion = $myrow['nu_type_disposicion'];
        $transnoBaja = $myrow['nu_transno_baja'];
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
    fixedAssetCategoryBien.description as tipoName
    FROM tb_Fixed_Disposicion_Patrimonial_Detalle as tb_dp_detalle
    JOIN fixedassets ON fixedassets.assetid = tb_dp_detalle.nu_assetid
    JOIN sec_unegsxuser ON sec_unegsxuser.tagref = fixedassets.tagrefowner AND sec_unegsxuser.userid = '".$_SESSION['UserID']."'
    JOIN tb_sec_users_ue ON tb_sec_users_ue.tagref = fixedassets.tagrefowner AND tb_sec_users_ue.ue = fixedassets.ue AND tb_sec_users_ue.userid = '".$_SESSION['UserID']."'
    LEFT JOIN fixedassetstatus ON fixedassetstatus.fixedassetstatusid = fixedassets.status
    LEFT JOIN fixedAssetCategoryBien ON fixedAssetCategoryBien.id = fixedassets.tipo_bien
    WHERE 
    tb_dp_detalle.nu_type = '".$type."'
    AND tb_dp_detalle.nu_transno = '".$transno."'
    ORDER BY tb_dp_detalle.nu_mov ASC";
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
            'tipoName' => $myrow ['tipoName']
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
    $reponse['selectTipoDisposicion'] = $selectTipoDisposicion;
    $reponse['transnoBaja'] = $transnoBaja;

    $contenido = $reponse;
    $result = true;
}

if ($option == 'guardarOperacion') {
    $datosCaptura = $_POST['datosCaptura'];
    $type = $_POST['type'];
    $transno = $_POST['transno'];
    $legalid = $_POST['legalid'];
    $tagref = $_POST['tagref'];
    $estatus = $_POST['estatus'];
    $fechaCaptura = $_POST['fechaCaptura'];
    $justificacion = $_POST['justificacion'];
    $ue = $_POST['ue'];
    $selectTipoBien = $_POST['selectTipoBien'];
    $selectDisposicion = $_POST['selectDisposicion'];
    $folioBaja = $_POST['folioBaja'];

    $transno = GetNextTransNo($type, $db);

    $SQL="INSERT INTO tb_Fixed_Disposicion_Patrimonial 
            (`dtm_fecha`,
             `sn_userid`,
             `txt_justificacion`,
             `nu_type`,
             `nu_transno`,
             `nu_estatus`,
             `sn_tagref`,
             `sn_funcion_id`,
             `ln_ue`,
             `nu_tipo_bien`,
             `nu_type_disposicion`,
             `nu_transno_baja`) 
        VALUES (
            curdate(),
            '".$_SESSION['UserID']."',
            '".$justificacion."',
            '".$type."',
            '".$transno."',
            '1',
            '".$tagref."',
            '".$funcion."',
            '".$ue ."',
            '".$selectTipoBien."',
            '".$selectDisposicion."',
            '".$folioBaja."'
            )";

    //echo "SQL:" . $SQL;
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    if($TransResult){
        $SQL="INSERT INTO tb_Fixed_Disposicion_Patrimonial_Detalle (`dtm_fecha`, `sn_userid`, `nu_type`, `nu_transno`, `nu_assetid`) VALUES";
        foreach ($datosCaptura as $datos) {
            $SQL.="(curdate(),'".$_SESSION['UserID']."','".$type."','".$transno."','".$datos['noReg']."' ),";
        }

        $rsDetalle = DB_query(rtrim($SQL,','), $db, $ErrMsg);

        if($rsDetalle){
            foreach ($datosCaptura as $datos) {
                $SQL = "UPDATE tb_Fixed_Baja_Patrimonial_Detalle
                        SET afectacion_disposicion = 1
                        WHERE nu_type = '306' AND nu_transno = '".$folioBaja."' AND nu_assetid = ".$datos['noReg'].";";
                $result = DB_query($SQL, $db, $ErrMsg);
            }
        }
    }



    $SQL="SELECT statusname FROM tb_botones_status WHERE statusid = 1 AND sn_funcion_id = ".$funcion;
    $result = DB_query($SQL, $db, $ErrMsg);
    $myrowEsatus= DB_fetch_array($result);

    $datosPresupuesto['transno'] = $transno;
    $datosPresupuesto['type'] = $type;
    $datosPresupuesto['statusname'] = $myrowEsatus['statusname'];

    $contenido = array('datos' => $datosPresupuesto);
    $Mensaje = "Se ha guardado exitosamente la Disposición Patrimonial con folio ".$transno;

    $result = true;

}

if ($option == 'modificarOperacion') {
    $datosCaptura = $_POST['datosCaptura'];
    $type = $_POST['type'];
    $transno = $_POST['folio'];
    $legalid = $_POST['legalid'];
    $tagref = $_POST['tagref'];
    $estatus = $_POST['estatus'];
    $fechaCaptura = $_POST['fechaCaptura'];
    $justificacion = $_POST['justificacion'];
    $ue = $_POST['ue'];
    $selectTipoBien = $_POST['selectTipoBien'];
    $selectDisposicion = $_POST['selectDisposicion'];
    $folioBaja = $_POST['folioBaja'];

    $SQL = "UPDATE tb_Fixed_Baja_Patrimonial_Detalle
                    SET afectacion_disposicion = 0
                    WHERE nu_type = '306' AND nu_transno = '".$folioBaja."'";
    $rsDetalle = DB_query($SQL, $db, $ErrMsg);     

    $SQL="DELETE FROM tb_Fixed_Disposicion_Patrimonial_Detalle WHERE  `nu_type`='".$type."' AND  `nu_transno`= '".$transno."'";    
    $rsDetalle = DB_query($SQL, $db, $ErrMsg);          

    if($rsDetalle){
        $SQL="INSERT INTO tb_Fixed_Disposicion_Patrimonial_Detalle (`dtm_fecha`, `sn_userid`, `nu_type`, `nu_transno`, `nu_assetid`) VALUES";
        foreach ($datosCaptura as $datos) {
            $SQL.="(curdate(),'".$_SESSION['UserID']."','".$type."','".$transno."','".$datos['noReg']."' ),";
        }

        $rsDetalle = DB_query(rtrim($SQL,','), $db, $ErrMsg);

        if($rsDetalle){
            foreach ($datosCaptura as $datos) {
                $SQL = "UPDATE tb_Fixed_Baja_Patrimonial_Detalle
                        SET afectacion_disposicion = 1
                        WHERE nu_type = '306' AND nu_transno = '".$folioBaja."' AND nu_assetid = ".$datos['noReg'].";";
                $result = DB_query($SQL, $db, $ErrMsg);
            }
        }
    }

    $SQL="SELECT statusname FROM tb_botones_status WHERE statusid = 1 AND sn_funcion_id = ".$funcion;
    $result = DB_query($SQL, $db, $ErrMsg);
    $myrowEsatus= DB_fetch_array($result);

    $datosPresupuesto['transno'] = $transno;
    $datosPresupuesto['type'] = $type;
    $datosPresupuesto['statusname'] = $myrowEsatus['statusname'];

    $contenido = array('datos' => $datosPresupuesto);
    $Mensaje = "Se ha guardado exitosamente la Disposición Patrimonial con folio ".$transno;


}

if($option == "guardarArchivos"){
    $transno=$_POST['transno'];
    $cadenaDatosInsertar ="";
    
    if(isset($_FILES['archivos'])){
        foreach ($_FILES['archivos']['tmp_name'] as $key => $tmp_name) {

            $name_visible = $_FILES['archivos']['name'][$key];
            $file_name = $key.date('YmdHis').$_FILES['archivos']['name'][$key] ;
            $name_visible=str_replace(" ", "", $name_visible);
            $file_name=str_replace(" ", "", $file_name);
            $file_type=$_FILES['archivos']['type'][$key];

            $cadenaDatosInsertar .= "('".$_SESSION['UserID']."','".$file_type."','".$file_name."','archivos/".$file_name."','".$funcion."',1003,'".$name_visible."','".$transno."','".$_POST['observacionFile'.$key]."','1'),";

            moverArchivo($file_name,$_FILES['archivos']['tmp_name'][$key],SUBIDAARCHIVOS);
        }

        $cadenaDatosInsertar = substr($cadenaDatosInsertar, 0, -1);

        if($cadenaDatosInsertar !=""){
            $SQL = "INSERT INTO tb_archivos (`ln_userid`, `sn_tipo`, `ln_nombre_interno_archivo`,`txt_url`,`nu_funcion`,`nu_tipo_sys`,`ln_nombre`, `nu_trasnno`, `txt_descripcion`,ind_active) VALUES ".$cadenaDatosInsertar;

            $ErrMsg = "Problemas al guardar el archivo.";
            $result = DB_query($SQL, $db, $ErrMsg);
        }
    } 

    for ($i=0; $i < $_POST['numObservacionesOldFile']; $i++) { 
        $SQL="UPDATE tb_archivos 
                SET `txt_descripcion` = '".$_POST['observacionOldFile'.$i]."'
                WHERE nu_id_documento = ".$_POST['OldIdFile'.$i];

        $ErrMsg = "Problemas al modificar observaciones del archivo.";
        $result = DB_query($SQL, $db, $ErrMsg);
    }
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

//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
//!!                                               !!
//!!         Mover archivo al servidor.            !!
//!!                                               !!
//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

function moverArchivo($docName, $docTemp, $ubicacion)
{
    # comprobación y creación de la carpeta de ser necesario
    if(!file_exists($ubicacion)){ crearCarpeta($ubicacion); }
    $name = $ubicacion . $docName;
    # comprobación de archivo subido
    if(is_uploaded_file($docTemp)){
        # cambio de ubicación del archivo
        $conf = move_uploaded_file($docTemp, $name);
        @chown($name, 'root');
        @chgrp($name, 'root');
        return $conf;
    }
    return false;
}


$dataObj = array('sql' => '', 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);
