<?php
/**
 * Solicitud Ministracion
 *
 * @category
 * @package ap_grp
 * @author desarrollo
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 31/10/2017
 * Fecha Modificación: 31/10/2017
 * Modelos para las operaciones de Solicitud de Ministracion
 */

// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);
// ini_set('error_log', dirname(__FILE__) . '/error_log.txt');

$PageSecurity = 1;
$PathPrefix = '../';
//include($PathPrefix.'includes/session.inc');
session_start();
include($PathPrefix . 'config.php');
include($PathPrefix . 'includes/ConnectDB.inc');
$funcion=1987;
include($PathPrefix.'includes/SecurityFunctions.inc');
include($PathPrefix.'includes/SQL_CommonFunctions.inc');

// Include para el periodo
include($PathPrefix . 'includes/DateFunctions.inc');

include $PathPrefix . "includes/SecurityUrl.php";
$enc = new Encryption;

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


if ($option == 'obtenerCapituloPartida') {
    // chartdetailsbudgetbytag.budget, 
    //         chartdetailsbudgetbytag.tagref, 
    //         chartdetailsbudgetbytag.ln_aux1
    $SQL = "SELECT chartdetailsbudgetbytag.accountcode as cvefrom,
                    chartdetailsbudgetbytag.partida_esp
            FROM chartdetailsbudgetbytag
            JOIN tags ON tags.tagref = chartdetailsbudgetbytag.tagref
            JOIN legalbusinessunit ON legalbusinessunit.legalid = tags.legalid
            JOIN tb_cat_unidades_ejecutoras tb_ue ON  chartdetailsbudgetbytag.ln_aux1 = tb_ue.ln_aux1 AND chartdetailsbudgetbytag.tagref = tb_ue.ur
            WHERE chartdetailsbudgetbytag.tagref = '".$_POST['ur']."' AND tb_ue.ue = ".$_POST['ue']. " AND chartdetailsbudgetbytag.anho = '".$_SESSION['ejercicioFiscal']."' AND chartdetailsbudgetbytag.partida_esp like '35%'
            ORDER BY chartdetailsbudgetbytag.partida_esp;";
            
    $result = DB_query($SQL,$db);

    while ( $myrow = DB_fetch_array($result)) {
        $datos['value'] = $myrow ['partida_esp'];
        $datos['texto'] = $myrow ['cvefrom'];
        $info[] = $datos;
    }

    $contenido = array('datos' => $info);
}

if ($option == 'obtenerPatrimonioMatto') {
    $ur = $_POST['ur'];
    $ue = $_POST['ue'];
    $tipo_bien = $_POST['tipo_bien'];
    $almacen = $_POST['almacen'];
    $pe = $_POST['pe'];
    $cabms = $_POST['cabms'];
    
    $SQL = "SELECT `assetid`,
                     `serialno`,
                     `eco`,
                     `barcode`,
                     `cost`,
                     `accumdepn`,
                     `disposalproceeds`,
                     `assetcategoryid`,
                     `description`,
                     `longdescription`,
                     `depntype`,
                     `depnrate`,
                     `fixedassettype`,
                     `status`,
                     `certificate`,
                     `datepurchased`,
                     `disposaldate`,
                     `endcalibrationdate`,
                     `calibrationdate`,
                     `lastmaintenancedate`,
                     `assetlocation`,
                     `tagrefowner`,
                     `ue`,
                     `currentlocation`,
                     `loccode`,
                     `ownertype`,
                     `active`,
                     `model`,
                     `size`,
                     `pdf`,
                     `cabm`,
                     `legalid`,
                     `marca`,
                     `fechaIncorporacionPatrimonial`,
                     `factura`,
                     `contabilizado`,
                     `clavebien`,
                     `proveedor`,
                     `tipo_bien`,
                     `placas`,
                     `color`,
                     `observaciones`,
                     `asegurado`,
                     `anio`
            FROM fixedassets
            WHERE `active` = 1
                    AND `ownertype` = 1
                    AND `status` =1
                    AND `tagrefowner` = ".$ur."
                    AND `ue` = ".$ue."
                    AND `tipo_bien` = ".$tipo_bien."";
    
    if(isset($almacen) && $almacen !="" ){
        $SQL .= "   AND `loccode` = ".$almacen."";
    }

    $SQL .= "       AND `assetcategoryid` in  (".$pe.") ";
    

    if($cabms != ""){
        $SQL .= "  AND `cabm` in (".$cabms.")";
    }

    $SQL .= "      -- AND cost > accumdepn

            ORDER BY `barcode`;";
                       
    $result = DB_query($SQL,$db);
    //echo "".$SQL;
    while ( $myrow = DB_fetch_array($result)) {
        $info[] = array(
            'assetid' => $myrow ['assetid'],
            'barcode' => $myrow ['barcode'],
            'clavebien' => $myrow ['clavebien'],
            'marca' => $myrow ['marca'],
            'description' => $myrow ['description']
        );
    }

    $contenido = array('datos' => $info);
}

if($option == "guardarMantenimiento"){

    $ur = $_POST['ur'];
    $ue = $_POST['ue'];
    $tipo_bien = $_POST['tipo_bien'];
    $tipo_mtto = $_POST['tipo_mtto'];
    $almacen = $_POST['almacen'];
    $pe = $_POST['pe'];
    $cabms = $_POST['cabms'];
    $fecha_captura = $_POST['fecha_captura'];
    $fecha_mtto = $_POST['fecha_mtto'];
    $clave = $_POST['clave'];
    $requisicion = $_POST['requisicion'];
    $observaciones = $_POST['observaciones'];
    $result=false; 
    $dataJsonMttoDetalle = $_POST['dataJsonMttoDetalle'];
    
    DB_Txn_Begin($db);

    $folioNew = GetNextTransNo('1005',$db);

    $SQL = "INSERT INTO fixedassetmaintenance 
            (`mttoid`,
            `userup`,
            `datetimeup`,
            `status`,
            `description`,
            `ur`,
            `ue`,
            `tipoMantenimiento`,
            tipo_bien,
            loccode,
            partida_especifica,
            clave,
            dateMtto,
            cabms,
            requisicion) 
            VALUES('".$folioNew."',
            '".$_SESSION['UserID']."',
            '".fnFormatoFechaYMD($fecha_captura,'-')."',
            '1',
            '".$observaciones."',
            '".$ur."',
            '".$ue."',
            '".$tipo_mtto."',
            '".$tipo_bien."',
            '".$almacen."',
            '".$pe."',
            '".$clave."',
            '".fnFormatoFechaYMD($fecha_mtto,'-')."',
            '".$cabms."',
             '".$requisicion."'
        )";

    $ErrMsg = "No se guardo el mantenimiento";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    $SQL = "";

    if($TransResult){
        $SQL="INSERT INTO tb_fixedassetmaintenance_detalle(`folio`,`assetid`,`observaciones`) VALUES";
        foreach ($dataJsonMttoDetalle as $datos) {
            $SQL.="(".$folioNew.",'".$datos['val0']."','".$datos['val1']."' ),";
        }

        $rsDetalle = DB_query(rtrim($SQL,','), $db, $ErrMsg);
    }

    if($rsDetalle){
        $SQL="UPDATE fixedassets SET status=11 WHERE assetid in (SELECT assetid FROM tb_fixedassetmaintenance_detalle WHERE folio = ".$folioNew.");";
        $rs = DB_query($SQL, $db, $ErrMsg);

        DB_Txn_Commit($db);
        $Mensaje = '<p><i class="glyphicon glyphicon-ok text-success" aria-hidden="true"></i> Se guardó el mantenimiento con folio: <b>'.$folioNew .'</b>, correctamente.<p>';
        $result=true;

    }else{
        DB_Txn_Rollback($db);
        $Mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>Problemas al guardar el mantenimiento.<p>';
    }

    $info[] = array(
            'folio' => $folioNew
        );
    $contenido = array('datos' => $info);
}

if ($option == 'obtenerMantenimiento') {

    $Folio = $_POST["folio"];

    $SQL  ="SELECT fixedassetmaintenance.id,
                fixedassetmaintenance.ur,
                fixedassetmaintenance.ue,
                fixedassetmaintenance.tipoMantenimiento,
                fixedassetmaintenance.mttoid,
                fixedassetmaintenance.description,
                DATE_FORMAT(datetimeup,'%d-%m-%Y') AS datetimeup,
                DATE_FORMAT(dateMtto,'%d-%m-%Y') AS dateMtto,
                fixedassetmaintenance.tipo_bien,
                fixedassetmaintenance.status,
                fixedassetmaintenance.loccode,
                fixedassetmaintenance.clave,
                fixedassetmaintenance.requisicion,
                fixedassetmaintenance.cabms,
                fixedassetmaintenance.partida_especifica,
                tags.tagdescription
            FROM fixedassetmaintenance
            LEFT JOIN tb_fixedassetmaintenance_types on fixedassetmaintenance.tipoMantenimiento =  tb_fixedassetmaintenance_types.id
            LEFT JOIN fixedAssetCategoryBien ON fixedassetmaintenance.tipo_bien = fixedAssetCategoryBien.id
            LEFT JOIN tb_fixedassetmaintenance_status ON fixedassetmaintenance.status = tb_fixedassetmaintenance_status.id
            LEFT JOIN tags on fixedassetmaintenance.ur = tags.tagref
            WHERE fixedassetmaintenance.mttoid = ".$Folio.";";

    $result = DB_query($SQL,$db);

    while ( $myrow = DB_fetch_array($result)) {
        $infoEncabezado[] = array(
            'mttoid' => $myrow ['mttoid'],
            'ur' => $myrow ['ur'],
            'ue' => $myrow ['ue'],
            'tipoMtto' => $myrow ['tipoMantenimiento'],
            'observacion' => $myrow ['description'],
            'datetimeup' => $myrow ['datetimeup'],
            'dateMtto' => $myrow ['dateMtto'],
            'tipoBien' => $myrow ['tipo_bien'],
            'estatus' => $myrow ['status'],
            'loccode' => $myrow ['loccode'],
            'clave' => $myrow ['clave'],
            'requisicion' => $myrow ['requisicion'],
            'cabms' => $myrow ['cabms'],
            'partidaEspecifica' => $myrow ['partida_especifica'],
            'urDescription' => $myrow ['tagdescription']
            
        );
    }

    /* === Detalle ===*/
    $SQL = "SELECT   tb_fixedassetmaintenance_detalle.`observaciones`  AS observacionesDetalle,
                     fixedassets.`assetid`,
                     fixedassets.`serialno`,
                     fixedassets.`eco`,
                     fixedassets.`barcode`,
                     fixedassets.`cost`,
                     fixedassets.`accumdepn`,
                     fixedassets.`disposalproceeds`,
                     fixedassets.`assetcategoryid`,
                     fixedassets.`description`,
                     fixedassets.`longdescription`,
                     fixedassets.`depntype`,
                     fixedassets.`depnrate`,
                     fixedassets.`fixedassettype`,
                     fixedassets.`status`,
                     fixedassets.`certificate`,
                     fixedassets.`datepurchased`,
                     fixedassets.`disposaldate`,
                     fixedassets.`endcalibrationdate`,
                     fixedassets.`calibrationdate`,
                     fixedassets.`lastmaintenancedate`,
                     fixedassets.`assetlocation`,
                     fixedassets.`tagrefowner`,
                     fixedassets.`ue`,
                     fixedassets.`currentlocation`,
                     fixedassets.`loccode`,
                     fixedassets.`ownertype`,
                     fixedassets.`active`,
                     fixedassets.`model`,
                     fixedassets.`size`,
                     fixedassets.`pdf`,
                     fixedassets.`cabm`,
                     fixedassets.`legalid`,
                     fixedassets.`marca`,
                     fixedassets.`fechaIncorporacionPatrimonial`,
                     fixedassets.`factura`,
                     fixedassets.`contabilizado`,
                     fixedassets.`clavebien`,
                     fixedassets.`proveedor`,
                     fixedassets.`tipo_bien`,
                     fixedassets.`placas`,
                     fixedassets.`color`,
                     fixedassets.`observaciones`,
                     fixedassets.`asegurado`,
                     fixedassets.`anio`
            FROM tb_fixedassetmaintenance_detalle
            inner join fixedassets on tb_fixedassetmaintenance_detalle.`assetid` = fixedassets.`assetid`
            WHERE tb_fixedassetmaintenance_detalle.folio = ".$Folio."
            ORDER BY tb_fixedassetmaintenance_detalle.id;";
                       
    $result = DB_query($SQL,$db);
    //echo "".$SQL;
    while ( $myrow = DB_fetch_array($result)) {
        $infoDetalle[] = array(
            'assetid' => $myrow ['assetid'],
            'barcode' => $myrow ['barcode'],
            'clavebien' => $myrow ['clavebien'],
            'marca' => $myrow ['marca'],
            'description' => $myrow ['description'],
            'observaciones' => $myrow ['observacionesDetalle']
        );
    }

    $contenido = array('datosEncabezado' => $infoEncabezado, 'datosDetalle' => $infoDetalle);

}

if ($option == 'cambiarEstatusMantenimiento') {
    $Mensaje="";
    $estatus = $_POST['estatus'];
    $folio = $_POST['folio'];
    $leyenda = $_POST['leyenda'];
    $leyenda2 = $_POST['leyenda2'];
    $reuisicion = $_POST['requisicion'];
    $strRequisicion="";

    if(isset($_POST['requisicion']) and $_POST['requisicion'] !=""){
        $strRequisicion = ", requisicion = '".$reuisicion."'";
    }

    $SQL="UPDATE fixedassetmaintenance SET status='".$estatus."' ". $strRequisicion ." WHERE mttoid in (".$folio.")";

    $result = DB_query($SQL,$db);

    if($result){
        $SQL="UPDATE fixedassets SET status=1 WHERE assetid in (SELECT assetid FROM tb_fixedassetmaintenance_detalle WHERE folio in (".$folio."));";
        $result = DB_query($SQL, $db, $ErrMsg);

        if($result){

            if(substr_count($folio,",") > 1){
                $Mensaje='<p><i class="glyphicon glyphicon-ok text-success" aria-hidden="true"></i> Se '. $leyenda2 .' los mantenimientos con folios : <b>' .rtrim($folio,',') .'</b> correctamente.</p>';
            }else{
                $Mensaje='<p><i class="glyphicon glyphicon-ok text-success" aria-hidden="true"></i> Se '. $leyenda .' el mantenimiento con folio : <b>'.$folio.'</b>, correctamente.</p>';

            }
        }
    }
}

if ($option == 'modificarMantenimiento') {
    $folio = $_POST['folio'];
    $ur = $_POST['ur'];
    $ue = $_POST['ue'];
    $tipo_bien = $_POST['tipo_bien'];
    $tipo_mtto = $_POST['tipo_mtto'];
    $almacen = $_POST['almacen'];
    $pe = $_POST['pe'];
    $cabms = $_POST['cabms'];
    $fecha_captura = $_POST['fecha_captura'];
    $fecha_mtto = $_POST['fecha_mtto'];
    $clave = $_POST['clave'];
    $requisicion = $_POST['requisicion'];
    $observaciones = $_POST['observaciones'];
    $result=false; 
    $dataJsonMttoDetalle = $_POST['dataJsonMttoDetalle'];
    
    $Mensaje='<p><i class="glyphicon glyphicon-ok text-danger" aria-hidden="true"></i> Problemas al modificar el mantenimiento con folio : <b>'.$folio.'</b>.</p>';

    $SQL="UPDATE fixedassetmaintenance 
            SET tipoMantenimiento = '".$tipo_mtto."',
            description = '".$observaciones."',
            dateMtto = '".fnFormatoFechaYMD($fecha_mtto,'-')."',
            requisicion = '".$requisicion."',
            clave = '".$clave."',
            partida_especifica = '".$pe."',
            cabms = '".$cabms."'
            WHERE mttoid = '".$folio."'";
    $result = DB_query($SQL,$db);

    if($result){
        $SQL="UPDATE fixedassets SET status=1 WHERE assetid in (SELECT assetid FROM tb_fixedassetmaintenance_detalle WHERE folio = ".$folio.");";
        $rsUpdate = DB_query($SQL, $db, $ErrMsg);

        if($rsUpdate){
            $SQL="DELETE FROM tb_fixedassetmaintenance_detalle WHERE folio = ".$folio.";";
            $rsDelete = DB_query($SQL, $db, $ErrMsg);

            if($rsDelete){
                $SQL="INSERT INTO tb_fixedassetmaintenance_detalle(`folio`,`assetid`,`observaciones`) VALUES";
                
                foreach ($dataJsonMttoDetalle as $datos) {
                    $SQL.="(".$folio.",'".$datos['val0']."','".$datos['val1']."' ),";
                }

                $rsDetalle = DB_query(rtrim($SQL,','), $db, $ErrMsg);

                if($rsDetalle){
                    $SQL="UPDATE fixedassets SET status=11 WHERE assetid in (SELECT assetid FROM tb_fixedassetmaintenance_detalle WHERE folio = ".$folio.");";
                    $rsUpdate = DB_query($SQL, $db, $ErrMsg);
                    $Mensaje='<p><i class="glyphicon glyphicon-ok text-success" aria-hidden="true"></i> Se modificó el mantenimiento con folio : <b>'.$folio.'</b>, correctamente.</p>';
                   
                }
            }
        }

    }

    $info[] = array(
        'folio' => $folio
    );

    $contenido = array('datos' => $info);


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

$dataObj = array('sql' => '', 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);

?>