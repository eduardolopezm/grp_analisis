<?php
/**
 * Solicitud Radicación
 *
 * @category
 * @package ap_grp
 * @author desarrollo
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link 
 * Fecha Creación: 31/10/2017
 * Fecha Modificación: 31/10/2017
 * Modelos para las operaciones de Solicitud de Radicacion
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
$funcion=2388;
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

define('SUBIDAARCHIVOS', realpath(dirname(__FILE__)) . '/' . $PathPrefix . 'archivos/');


$oficinaCentral= "09";

header('Content-type: text/html; charset=ISO-8859-1');

$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

$option = $_POST['option'];

//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
//!!   Se comentara los filtros por UE solicitud del contador firco    !!
//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

if ($option == 'obtenerCapituloPartida') {
    $SQL = "SELECT cdb.cvefrom
            FROM chartdetailsbudgetlog cdb
            INNER JOIN tb_cat_partidaspresupuestales_partidaespecifica pe on cdb.partida_esp = pe.partidacalculada
            INNER JOIN chartdetailsbudgetbytag cdbtag ON cdb.cvefrom = cdbtag.accountcode 
            WHERE 1=1  AND cdbtag.anho = '".$_SESSION['ejercicioFiscal']."' ";
            
            if(isset($_POST['idRadicacion']) and $_POST['idRadicacion'] != ""){
                $SQL .= " AND cdb.cvefrom IN (SELECT presupuesto FROM tb_radicacion_detalle WHERE idRadicacion = ".$_POST['idRadicacion'].")"; 
            }

            if(isset($_POST['ur']) and $_POST['ur']!=""){
                $SQL .= " AND cdb.tagref = '".$_POST['ur']."' "; 
            }

            if(isset($_POST['ue']) and $_POST['ue']!="" and $_POST['ue']!="-1"){
                $SQL .= " AND ln_ue in (".$_POST['ue'].") "; 
            }

            if(isset($_POST['pp']) and $_POST['pp'] != "" and  $_POST['pp'] != "-1"){
                $SQL .= " AND cdbtag.cppt = '".$_POST['pp']."' "; 
            }

            if(isset($_POST['capitulo']) and $_POST['capitulo']!=""){
                $SQL .= " AND pe.ccap in (".$_POST['capitulo'].") "; 
            }

            $SQL .=" GROUP BY cdb.cvefrom ORDER BY pe.ccap;";

    $result = DB_query($SQL,$db);

    while ( $myrow = DB_fetch_array($result)) {
        $datos['value'] = $myrow ['cvefrom'];
        $datos['texto'] = $myrow ['cvefrom'];
        $info[] = $datos;
    }

    $contenido = array('datos' => $info);
}

if ($option == 'guardarRadicacion'){

    //$folio="1";
    $folio = GetNextTransNo('292',$db);

    $SQL="INSERT INTO `tb_radicacion`
                (`ln_ur`,
                `ln_ue`,
                `folio`,
                `ln_pp`,
                `ln_capitulo`,
                `ln_clcSiaff`,
                `ln_mes`,
                `justificacion`,
                `fecha_elab`,
                `fecha_pago`,
                `fecha_autorizacion`,
                `usuario`,
                `estatus`,
                `idbeneficiario`,
                `idconcentradora`,
                `id_firmante`,
                `nu_anio_fiscal`,
                `ln_oficio`
                )
            VALUES(
                '".$_POST['ln_ur']."',
                '".$_POST['ln_ue']."',
                '".$folio."',
                '".$_POST['ln_pp']."',
                '".$_POST['ln_capitulo']."',
                '".$_POST['ln_clcSiaff']."',
                '".$_POST['ln_mes']."',
                '".$_POST['justificacion']."',
                curdate(),
                '".fnFormatoFechaYMD($_POST['fecha_pago'],'-')."',
                '".fnFormatoFechaYMD($_POST['fecha_autorizacion'],'-')."',
                '".$_SESSION['UserID']."',
                '".$_POST['estatus']."',
                '".$_POST['idBeneficiario']."',
                '".$_POST['idConcentradora']."',
                '".$_POST['firmante']."',
                '".$_SESSION['ejercicioFiscal']."',
                '".$_POST['numOficio']."'
            );";

    //echo "<pre>".$sql."</pre>";
    $result = DB_query($SQL,$db);
    $idRadicacion = DB_Last_Insert_ID($db, 'tb_radicacion', 'id');

    if($result){

        //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        //!!   Guardar Detalle de la Radicacion.           !!
        //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

        $SQL="INSERT INTO tb_radicacion_detalle
                (`idRadicacion`,
                `folio`,
                `presupuesto`,
                `solicitado`,
                `autorizado`,
                `orden`,
                `usuario`)
            VALUES";

        for ($i=0; $i < $_POST['numFilas']; $i++) {   
            if($_POST['filaPresupuestoAutorizado'.$i]=='undefined'){
              $_POST['filaPresupuestoAutorizado'.$i] =0;  
            }         
            $SQL.="('".$idRadicacion."',
                    '".$folio."',
                    '".$_POST['filaPresupuesto'.$i]."',
                    '".$_POST['filaPresupuestoSolicitado'.$i]."',
                    '".$_POST['filaPresupuestoAutorizado'.$i]."',
                    '".$_POST['filaOrden'.$i]."',
                    '".$_SESSION['UserID']."'
                    )";

            if($i < ($_POST['numFilas'] -1)){
                $SQL.=",";
            }
        }

        $result = DB_query($SQL,$db);

        if($result){

            //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
            //!!     Guardar la afectacion presupuestal.       !!
            //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

            fnAfectacionPresupuestal(269,$idRadicacion,$folio,$_POST['ln_ur'],$_POST['ln_mes'],$db);

            $url = "&Folio=>" . $folio;
            $url .= "&idRadicacion=>" . $idRadicacion;
            $url = $enc->encode($url);
            $liga_folio= "URL=". $url;
            $contenido[] = array(
                'folio' => $folio,
                'idRadicacion' => $idRadicacion
            );


            $Mensaje ="Se agrego la radicación con folio: ".$folio.", correctamente.";

            //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
            //!!   Guardar Archivos si hay seleccionado.       !!
            //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

            if(isset($_FILES['archivos'])){
                foreach ($_FILES['archivos']['tmp_name'] as $key => $tmp_name) {

                    $name_visible = $_FILES['archivos']['name'][$key];
                    $file_name = $key.$_FILES['archivos']['name'][$key];
                    $name_visible=str_replace(" ", "", $name_visible);
                    $file_name=str_replace(" ", "", $file_name);
                    $file_type=$_FILES['archivos']['type'][$key];

                    $cadenaDatosInsertar .= "('".$_SESSION['UserID']."','".$file_type."','".$file_name."','archivos/".$file_name."','".$funcion."',292,'".$name_visible."','".$intIdRadicacion."'),";
                }

                $cadenaDatosInsertar = substr($cadenaDatosInsertar, 0, -1);

                if($cadenaDatosInsertar !=""){
                    $SQL = "INSERT INTO tb_archivos (`ln_userid`, `sn_tipo`, `ln_nombre_interno_archivo`,`txt_url`,`nu_funcion`,`nu_tipo_sys`,`ln_nombre`, `nu_trasnno`) VALUES ".$cadenaDatosInsertar;

                    $ErrMsg = "Problemas al guardar el archivo de radicación.";
                    $result = DB_query($SQL, $db, $ErrMsg);

                    if($result){
                        $Mensaje .="<p>Se guardo el archivo correctamente.</p>";
                    }
                }
            }            
        }else{
            $Mensaje ="Problemas al guardar detalle de la radicación con folio: ".$folio."";
        }

    }else{
        $Mensaje ="Problemas al guardar la radicación";
    }
}

if($option == "modificarRadicacion"){
    
    $folio = $_POST['intFolio'];
    $intIdRadicacion = $_POST['intIdRadicacion'];
    $idEstatus = $_POST['estatus'];
    $ur = $_POST['ln_ur'];

    if($idEstatus < 5){
        
        /* Modificar el encabezado*/
        $SQL = "UPDATE tb_radicacion SET ";
        $SQL .= "   `ln_capitulo` = '".$_POST['ln_capitulo']."',
                    `justificacion` = '".$_POST['justificacion']."',
                    `fecha_pago` = '".fnFormatoFechaYMD($_POST['fecha_pago'],'-')."',
                    `fecha_autorizacion` = '".fnFormatoFechaYMD($_POST['fecha_autorizacion'],'-')."',
                    `num_transferencia` = '".$_POST['numTransferencia']."',
                    `idbeneficiario` = '".$_POST['idBeneficiario']."',
                    `idconcentradora` = '".$_POST['idConcentradora']."',
                    `id_firmante` = '".$_POST['firmante']."',
                    `ln_oficio` = '".$_POST['numOficio']."'

                WHERE `id` = '".$intIdRadicacion."'";
        //echo "sql:".$SQL;
        $ErrMsg = "No se obtuvieron las radicacion";
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        /*Modificar Detalle*/

        $SQL="DELETE FROM tb_radicacion_detalle WHERE idRadicacion ='".$intIdRadicacion."';";
        $TransResult = DB_query($SQL, $db);

        $SQL="INSERT INTO tb_radicacion_detalle
                    (`idRadicacion`,
                    `folio`,
                    `presupuesto`,
                    `solicitado`,
                    `autorizado`,
                    `orden`,
                    `usuario`)
                VALUES";

        for ($i=0; $i < $_POST['numFilas']; $i++) {            
            $SQL.="('".$intIdRadicacion."',
                    '".$folio."',
                    '".$_POST['filaPresupuesto'.$i]."',
                    '".$_POST['filaPresupuestoSolicitado'.$i]."',
                    '".$_POST['filaPresupuestoAutorizado'.$i]."',
                    '".$_POST['filaOrden'.$i]."',
                    '".$_SESSION['UserID']."'
                    )";

            if($i < ($_POST['numFilas'] -1)){
                $SQL.=",";
            }
        }

        $result = DB_query($SQL,$db);
        //echo "<pre>". $SQL;
        if($result){

            fnAfectacionPresupuestal(269,$intIdRadicacion,$folio,$ur,$_POST['ln_mes'],$db);

            $url = "&Folio=>" . $folio;
            $url .= "&idRadicacion=>" . $intIdRadicacion;
            $url = $enc->encode($url);
            $liga_folio= "URL=". $url;
            $contenido[] = array(
                'folio' => $folio,
                'idRadicacion' => $intIdRadicacion
            );
            $Mensaje ="<p>Se modificó la radicación con folio: ".$folio.", correctamente.</p>";
        }else{
            $Mensaje ="<p>Problemas al guardar detalle de la radicación con folio: ".$folio.".</p>";
        }
    }

    $cadenaDatosInsertar = "";

    if(isset($_FILES['archivos'])){
        foreach ($_FILES['archivos']['tmp_name'] as $key => $tmp_name) {

            $file_name = "";
            $file_name = "file".$key."_".date('Ymd_his')."";
            $name_visible = $_FILES['archivos']['name'][$key];
            $file_tmp =$_FILES['archivos']['tmp_name'][$key];

            $file_name=basename(str_replace(" ", "", $file_name));
            $name_visible=str_replace(" ", "", $name_visible);
            $file_type=$_FILES['archivos']['type'][$key];

            $cadenaDatosInsertar .= "('".$_SESSION['UserID']."','".$file_type."','".$file_name."','archivos/".$file_name."','".$funcion."',292,'".$name_visible."','".$intIdRadicacion."'),";

            moverArchivo($file_name,$_FILES['archivos']['tmp_name'][$key],SUBIDAARCHIVOS);
            //move_uploaded_file($file_tmp, "/archivos/user.autorizador/comprobante/23/".$file_name);
        }

        $cadenaDatosInsertar = substr($cadenaDatosInsertar, 0, -1);

        if($cadenaDatosInsertar !=""){
            $SQL = "INSERT INTO tb_archivos (`ln_userid`, `sn_tipo`, `ln_nombre_interno_archivo`,`txt_url`,`nu_funcion`,`nu_tipo_sys`,`ln_nombre`, `nu_trasnno`) VALUES ".$cadenaDatosInsertar;

            $ErrMsg = "Problemas al guardar el archivos de radicacion.";
            $result = DB_query($SQL, $db, $ErrMsg);

            if($result){
                $url = "&Folio=>" . $folio;
                $url .= "&idRadicacion=>" . $intIdRadicacion;
                $url = $enc->encode($url);
                $liga_folio= "URL=". $url;
                $contenido[] = array(
                    'folio' => $folio,
                    'idRadicacion' => $intIdRadicacion
                );
                $Mensaje .="<p>Se guardo el archivo en la radicación con folio: ".$folio." correctamente.</p>";
            }
        }
    }

    if($Mensaje ==""){
        $result = true;
        $Mensaje ="<p>Se modificó la radicación con folio: ".$folio.", correctamente.</p>";
    }

}

if ($option == 'obtenerRadicacion'){

    //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    //!!                                                           !!
    //!!        Obtener encabezado de la radicacion.               !!
    //!!                                                           !!
    //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    $SQL = "SELECT  tb_m.id,
                    tb_m.ln_ur as UR,
                    tags.tagname as URDescripcion,
                    tb_m.ln_ue as UE, 
                    tb_ue.desc_ue as UEDescripcion,
                    tb_m.ln_mes as mes_solicitado,
                    tb_m.folio as folio,
                    date_format(tb_m.fecha_elab,'%d-%m-%Y') as fecha_captura,
                    tb_m.ln_pp as programa,
                    tb_pp.descripcion as PPDescripcion,
                    tb_m.ln_clcSiaff as clc,
                    tb_m.ln_capitulo as capitulo,
                    truncate(coalesce(sum(tb_m_d.solicitado),0),2) as importe,
                    case when tb_m.fecha_pago = '0000-00-00 00:00:00' then '' else  date_format(tb_m.fecha_pago,'%d-%m-%Y')   end as fecha_pago,
                    case when tb_m.fecha_autorizacion = '0000-00-00 00:00:00' then '' else  date_format(tb_m.fecha_autorizacion,'%d-%m-%Y')   end as fecha_autorizacion,
                    tb_m.estatus,
                    tb_m.justificacion,
                    tb_m.num_transferencia,
                    tb_m.id_firmante,
                    tb_m.ln_oficio,
                    tb_bc.id as idbeneficiario,
                    tb_bc.rfc as rfcbeneficiario,
                    tb_bc.cuenta as clabebeneficiario,
                    tb_m.idconcentradora,
                    tb_bc2.cuenta as cuentaConcentradora
            FROM tb_radicacion tb_m
            LEFT JOIN tags on tb_m.ln_ur = tags.tagref
            LEFT JOIN tb_cat_unidades_ejecutoras tb_ue ON tb_m.ln_ue = tb_ue.ue
            LEFT JOIN tb_cat_programa_presupuestario tb_pp ON tb_m.ln_pp = tb_pp.cppt
            LEFT JOIN tb_radicacion_detalle tb_m_d ON tb_m.folio=tb_m_d.folio
            LEFT JOIN tb_estatus_radicacion tb_e_r ON tb_m.estatus = tb_e_r.id
            LEFT JOIN tb_beneficiario_concentradora tb_bc ON tb_m.idbeneficiario =tb_bc.id
            LEFT JOIN tb_beneficiario_concentradora tb_bc2 ON tb_m.idconcentradora=tb_bc2.id
            WHERE tb_m.id = '".$_POST['idRadicacion']."'";

            // if(isset($_POST['UR']) and $_POST['UR'] !=""){
            //     $SQL.=" AND tb_m.ln_ur = '".$_POST['UR']."'";
            // }

            // if(isset($_POST['UE']) and $_POST['UE'] !=""){
            //     $SQL.=" AND tb_m.ln_ue = '".$_POST['UE']."'";
            // }

    $SQL .= " GROUP BY tb_m.id,tb_m.ln_ur, tb_m.ln_ue,tb_m.folio;";

    //echo "sql:".$SQL;
    $ErrMsg = "No se obtuvieron las radicaciones";
    $result = DB_query($SQL, $db, $ErrMsg);

    while ($myrow = DB_fetch_array($result)) {

        $info[] = array(
            'UR' => $myrow ['UR'],
            'URDescripcion' => $myrow ['URDescripcion'],
            'UE' => $myrow ['UE'],
            'UEDescripcion' => $myrow ['UEDescripcion'],
            'mes_solicitado' => $myrow['mes_solicitado'],
            'folio' => $myrow ['folio'],
            'fecha_captura' => $myrow ['fecha_captura'],
            'programa' => $myrow ['programa'],
            'PPDescripcion' => $myrow ['PPDescripcion'],
            'clc' => $myrow ['clc'],
            'capitulo' => $myrow ['capitulo'],
            'importe' => $myrow ['importe'],
            'fecha_pago' => $myrow ['fecha_pago'],
            'fecha_autorizacion' => $myrow ['fecha_autorizacion'],
            'estatus' => $myrow ['estatus'],
            'justificacion' => $myrow ['justificacion'],
            'numTransferencia' => $myrow ['num_transferencia'],
            'idbeneficiario' => $myrow ['idbeneficiario'],
            'rfcbeneficiario' => $myrow ['rfcbeneficiario'],
            'clabebeneficiario' => $myrow ['clabebeneficiario'],
            'idConcentradora' => $myrow ['idconcentradora'],
            'cuentaConcentradora' => $myrow ['cuentaConcentradora'],
            'firmante' => $myrow ['id_firmante'],
            'ln_oficio' => $myrow ['ln_oficio']
        );
    }

    //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    //!!                                                           !!
    //!!        Obtener detalle de la radicacion.                  !!
    //!!                                                           !!
    //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    $SQL = "SELECT tb_m_d.id, 
                    tb_m_d.folio, 
                    tb_m_d.presupuesto, 
                    truncate(coalesce(tb_m_d.solicitado,0), 2) as solicitado,
                    truncate(coalesce(tb_m_d.autorizado,0), 2) as autorizado,
                    tb_m_d.orden, 
                    tb_m_d.usuario 
            FROM tb_radicacion_detalle tb_m_d
            WHERE tb_m_d.idRadicacion ='".$_POST['idRadicacion']."'
            ORDER BY orden";

    $ErrMsg = "No se obtuvieron el detalle de radicación";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    while ($myrow = DB_fetch_array($TransResult)) {

        $infoDetalle[] = array(
            'id' => $myrow ['id'],
            'presupuesto' => $myrow ['presupuesto'],
            'solicitado' => $myrow ['solicitado'],
            'autorizado' => $myrow ['autorizado'],
            'orden' => $myrow ['orden']
        );
    }

    //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    //!!                                                           !!
    //!!        Obtener archivos de la radicacion.                 !!
    //!!                                                           !!
    //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

    $SQL = "SELECT nu_id_documento,txt_url,ln_nombre FROM tb_archivos WHERE nu_tipo_sys = '292' AND nu_trasnno = '".$_POST['idRadicacion']."';";

    $ErrMsg = "No se obtuvieron el detalle de los archivos";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    while ($myrow = DB_fetch_array($TransResult)) {

        $infoDetalleArchivos[] = array(
            'idFile' => $myrow ['nu_id_documento'],
            'urlFile' => $myrow ['txt_url'],
            'nameFile' => $myrow ['ln_nombre']
        );
    }
    
    $contenido = array('datos' => $info,'datalle' =>$infoDetalle, 'detalleArchivos' => $infoDetalleArchivos);
    $result = true;
}

if ($option == 'obtenerDetalleRadicacion'){
    $SQL = "SELECT tb_m_d.id, 
                    tb_m_d.folio, 
                    tb_m_d.presupuesto, 
                    truncate(coalesce(tb_m_d.solicitado,0), 2) as solicitado,
                    truncate(coalesce(tb_m_d.autorizado,0), 2) as autorizado,
                    tb_m_d.orden, 
                    tb_m_d.usuario 
            FROM tb_radicacion_detalle tb_m_d
            WHERE tb_m_d.idRadicacion ='".$_POST['idRadicacion']."'
            ORDER BY orden";

    $ErrMsg = "No se obtuvieron el detalle de radicacion";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    while ($myrow = DB_fetch_array($TransResult)) {

        $infoDetalle[] = array(
            'id' => $myrow ['id'],
            'presupuesto' => $myrow ['presupuesto'],
            'solicitado' => $myrow ['solicitado'],
            'autorizado' => $myrow ['autorizado'],
            'orden' => $myrow ['orden']
        );
    }
    
    $contenido = array('datosDetalle' =>$infoDetalle);
    $result = true;
}


if ($option == 'autorizarRadicacion'){
    //echo "entro1";
    $folio = $_POST['intFolio'];
    $idRadicacion = $_POST['idRadicacion'];
    $ur = $_POST['ur'];
    $ue = $_POST['ue'];
    $mes = $_POST['mes'];
    $capitulos = $_POST['capitulos'];
    $identificador = $_POST['identificador'];
    //echo "entro2";

    $Mensaje = fnMovimientosContables($idRadicacion,$capitulos,$identificador,$ur,$ue,$folio,$mes,$oficinaCentral,$db);
    $result=true;
    // /* Modificar el encabezado*/
    // $SQL = "UPDATE tb_radicacion 
    //         SET estatus = 5
    //         WHERE id = '".$idRadicacion."' AND `folio` = '".$folio."'";
    
    // //echo "sql:".$SQL;

    // $ErrMsg = "No se obtuvieron las radicación";
    // $result = DB_query($SQL, $db, $ErrMsg);

    // if($result){

    //     fnAfectacionPresupuestal(271,$idRadicacion,$folio,$ur,$mes,$db);

    //     $Mensaje ="Se autorizo la radicación con folio: ".$folio.", correctamente.";
    // }else{
    //     $Mensaje ="Problemas al autorizar la radicación con folio: ".$folio."";
    // }

}

if ($option == 'obtenerPresupuesto') {
    $clave = $_POST['clave'];
    $account = $_POST['account'];
    $legalid = $_POST['legalid'];
    $datosClave = $_POST['datosClave'];
    $datosClaveAdecuacion = $_POST['datosClaveAdecuacion'];
    $tipoAfectacion = $_POST['tipoAfectacion'];
    $type = $_POST['type'];
    $transno = $_POST['transno'];
    $tipoMovimiento = $_POST['tipoMovimiento'];
    $mes = $_POST['mes'];
    $period = "";

    $res = true;

    //$period = GetPeriod((date('d').'/'.$mes.'/'.date('Y')), $db);

    $period =fnObtenerPeriodo($mes,$db);
    if ($_SESSION['ejercicioFiscal'] != date('Y')) {
        // si no es el año actual tomar diciembre
        $period = GetPeriod(date('d').'/12/'.$_SESSION['ejercicioFiscal'], $db);
    }

    $info = fnInfoPresupuestoRadicacion($db, $clave, $period, $account, $legalid, $datosClave, $datosClaveAdecuacion, $tipoAfectacion, $type, $transno, $tipoMovimiento);

    if (empty($info)) {
        $Mensaje = "No se encontró la información para la Clave Presupuestal ".$clave;
        $res = false;
    }

    $contenido = array('datos' => $info);
    $result = $res;
}

if($option == 'guardarArchivos'){

    $cadenaDatosInsertar = "";
    foreach ($_FILES['archivos']['tmp_name'] as $key => $tmp_name) {

        $file_name = $key.$_FILES['archivos']['name'][$key];
        $file_name=str_replace(" ", "", $file_name);
        $file_type=$_FILES['archivos']['type'][$key];

        $cadenaDatosInsertar .= "('".$_SESSION['UserID']."','".$file_type."','".$file_name."','archivos/".$file_name."','".$funcion."',292,'".$file_name."'),";
    }

    $cadenaDatosInsertar = substr($cadenaDatosInsertar, 0, -1);

    if($cadenaDatosInsertar !=""){
        $SQL = "INSERT INTO tb_archivos (`ln_userid`, `sn_tipo`, `ln_nombre_interno_archivo`,`txt_url`,`nu_funcion`,`nu_tipo_sys`,`ln_nombre`) VALUES ".$cadenaDatosInsertar;

        $ErrMsg = "problemas al guardar el archivos de radicacion";
        $result = DB_query($SQL, $db, $ErrMsg);

        if($result){
            $Mensaje .="<p>Se guardo el archivo correctamente</p>";
        }

    }
    
}

if($option == 'removerArchivo'){

    $idArchivo = $_POST['idArchivo'];
    
    $SQL = "DELETE  FROM tb_archivos WHERE nu_id_documento = ". $idArchivo;
    $ErrMsg = "Problemas al elimnar el archivo";
    $result = DB_query($SQL, $db, $ErrMsg);

    if($result){
        $Mensaje = "Se elimino correctamente";
    }
}


if($option == 'firmanteTitular'){
    $SQL = "SELECT 
                tb_detalle_firmas.id_nu_detalle_firmas as id,
                CONCAT(tb_detalle_firmas.titulo,' ',tb_empleados.ln_nombre,' ',tb_empleados.sn_primer_apellido ,' ',tb_empleados.sn_segundo_apellido) as firmante,
                tb_detalle_firmas.informacion as puestoFirma,
                coalesce(tb_reporte_firmas.id_dafault,'') as id_dafault
            FROM tb_radicacion
            LEFT JOIN tb_detalle_firmas on tb_radicacion.id_firmante  = tb_detalle_firmas.id_nu_detalle_firmas
            LEFT JOIN tb_empleados ON tb_detalle_firmas.id_nu_empleado = tb_empleados.id_nu_empleado
            LEFT JOIN tb_reporte_firmas on tb_detalle_firmas.id_nu_detalle_firmas = tb_reporte_firmas.id_nu_detalle_firmas
            WHERE tb_radicacion.id = " . $_POST['idRadicacion'];
    $ErrMsg = "No se obtuvieron los capitulos de las partidas presupuestales";
    //echo $SQL;
    $TransResult = DB_query($SQL, $db, $ErrMsg);


    while ($myrowfirma = DB_fetch_array($TransResult)) {
        $infoDetalle[] = array(
            'titular' => $myrowfirma ['id_dafault']
        );
    }
    
    $contenido = array('datosDetalle' =>$infoDetalle);
    $result = true;
}

//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
//!!                                               !!
//!!                 Funciones.                    !!
//!!                                               !!
//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

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

//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
//!!                                               !!
//!!     Realizar Afectaciones Presupuestal.       !!
//!!                                               !!
//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

function fnAfectacionPresupuestal($tipoAfectacion,$idRadicacion,$folio,$ln_ur,$mes,$db){
    $PeriodNo = GetPeriod((date('d').'/'.$mes.'/'.date('Y')), $db);
    $PeriodNo = fnObtenerPeriodo($mes,$db);

    $monto=0;
    $status=5;
    $comentarios="Radicación autorizada.";

    if($tipoAfectacion == 269){
        $SQL="DELETE FROM chartdetailsbudgetlog WHERE type = '292' and transno ='".$idRadicacion."';";
        $result = DB_query($SQL, $db);
        $comentarios="Radicación en tramite.";
    }else{
        fnInsertPresupuestoLogMovContrarios($db, '292', $idRadicacion, $typeNuevo = 0, $transnoNuevo = 0);
    }

    $SQL = "SELECT `idRadicacion`,
                    `folio`,
                    `presupuesto`,
                    `solicitado`,
                    `autorizado`,
                    `orden`,
                    `usuario`
            FROM tb_radicacion_detalle
            WHERE idRadicacion = '".$idRadicacion."' AND `folio` = '".$folio."'";
    $$ErrMsg="Problemas en la afectacion presupuestal.";
    $result = DB_query($SQL, $db, $ErrMsg);

    while ($myrow = DB_fetch_array($result)) {
        $ln_ue = fnObtenerUnidadEjecutoraClave($db, $myrow['presupuesto']);
        $monto = $myrow['autorizado'];
        if($tipoAfectacion == 269){
            $monto = $myrow['solicitado'];
            $status=4;
        }

        // $agregoLogAcumulado = fnInsertPresupuestoLogAcomulado($db, 292, $idRadicacion, $ln_ur,$myrow['presupuesto'], $PeriodNo, ($monto * -1), $tipoAfectacion,"", $comentarios, 1, $status, 0, $ln_ue, 'DESC', 'radicacion');

        $agregoLog = fnInsertPresupuestoLog($db, 292, $idRadicacion, $ln_ur, $myrow['presupuesto'], $PeriodNo, ($monto * -1), $tipoAfectacion, "", $comentarios, 1, $status, 0, $ln_ue);
    }

}

function fnObtenerPeriodo($mes,$db){

    if ($_SESSION['ejercicioFiscal'] != date('Y')) {
        $mes = 12;
    }

    $SQL = "SELECT
            periods.periodno,
            cat_Months.mes
            FROM periods
            JOIN cat_Months ON cat_Months.u_mes = MONTH(periods.lastdate_in_period)
            WHERE MONTH(periods.lastdate_in_period) = ".$mes." and YEAR(periods.lastdate_in_period) = '".$_SESSION['ejercicioFiscal']."'";
    
    $result = DB_query($SQL, $db, $ErrMsg);

    $myrow = DB_fetch_array($result,$db);

    return $myrow['periodno'];
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

function fnMovimientosContables($idRadicacion,$capitulos,$identificador,$ur,$ue,$folio,$mes,$oficinaCentral,$db){
    //echo "entro3";

    $errMsj="";
    $blnErr=false;

    $cuentaConcentradora="";
    $cuentaClabeConcentradora="";

    $arrIdentificador = explode(",", $identificador);

    $str="";
    
    for ($i=0; $i < count($arrIdentificador) ; $i++) { 
        //Tipo 1 para capitulos con una cuenta concentradora
        //Tipo 2 para capitulos con mas de una cuenta concentradora 
        if($arrIdentificador[$i] =="1"){
            $str=" OR tipodefault=1 ";
        }
    }

    //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    //!!                                               !!
    //!!        VALIDACION DE CUENTAS CONTABLES.       !!
    //!!                                               !!
    //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

    $SQL="SELECT tb_radicacion.id,
                                tb_radicacion.ln_ur,
                                tb_radicacion.ln_pp,
                                tb_radicacion.idbeneficiario,
                                tb_radicacion.idconcentradora,
                                coalesce(tb_bc.cuenta,'') as cuentaBeneficiario,
                                coalesce(tb_bc.cuentacontable,'') as cuentaContableBeneficiario,
                                coalesce(tb_bc2.cuenta,'') as cuentaConcentradora,
                                coalesce(tb_bc2.cuentacontable,'') as cuentaContableConcentradora,
                                coalesce(tb_bc2.cuentafinal,'') as cuentaFinal,
                                coalesce(tb_bc2.cuentacontablefinal,'') as cuentaContableFinal,
                                tb_bc2.identificador
                        FROM tb_radicacion 
                        LEFT JOIN tb_beneficiario_concentradora tb_bc on tb_radicacion.idbeneficiario = tb_bc.id
                        LEFT JOIN tb_beneficiario_concentradora tb_bc2 on tb_radicacion.idconcentradora = tb_bc2.id
                        WHERE tb_radicacion.id = '".$idRadicacion."';";

    //echo $SQL;
    $result = DB_query($SQL, $db);

    $cuentaContableBeneficiarios ="";
    $cuentaContableConcentradora ="";
    $cuentaContableConcentradoraFinal ="";


    if($result){
        while ($myrow = DB_fetch_array($result)) {

            $cuentaContableBeneficiarios = $myrow['cuentaContableBeneficiario'];
            if($myrow['cuentaContableBeneficiario'] ==""){
                $errMsj .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>No se encuentra configurada la cuenta contable beneficiario.</p>';
            }

            $cuentaContableConcentradora = $myrow['cuentaContableConcentradora'];
            if($myrow['cuentaContableConcentradora'] ==""){
                $errMsj .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>No se encuentra configurada la cuenta contable concentradora.</p>';
            }

            //Tercer brinco contable como nomina
            if($myrow['identificador'] == '2'){
                $cuentaContableConcentradoraFinal = $myrow['cuentaContableFinal'];

                if($myrow['cuentaContableFinal'] ==""){
                    $errMsj .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>No se encuentra configurada la cuenta contable concentradora.</p>';
                }
            }
        }

        $SQLDetalle = "SELECT 
                        tb_detalle.ln_ur, 
                        tb_detalle.ue,
                        tb_detalle.ueDestno,
                        tb_detalle.ccap,  
                        tb_detalle.solicitado, 
                        tb_detalle.autorizado,
                        tb_cuentas.accountegreso,
                        tb_ue.desc_ue
                        FROM(
                            SELECT tb_r.ln_ur, '".$oficinaCentral."' AS ue,
                                    tb_r.ln_ue AS ueDestno,
                                    pe.ccap,  
                                    SUM(solicitado) AS solicitado, 
                                    SUM(autorizado) AS autorizado
                            FROM tb_radicacion_detalle trd
                            LEFT JOIN  chartdetailsbudgetbytag cdbtag ON trd.presupuesto = cdbtag.accountcode
                            LEFT JOIN tb_cat_partidaspresupuestales_partidaespecifica pe ON cdbtag.partida_esp = pe.partidacalculada
                            LEFT JOIN (
                                        SELECT id,folio,ln_ur, ln_ue 
                                        FROM tb_radicacion 
                                        WHERE id = '".$idRadicacion."') tb_r ON trd.idRadicacion = tb_r.id
                            WHERE idRadicacion = '".$idRadicacion."'
                            GROUP BY pe.ccap) tb_detalle
                        LEFT  JOIN (
                                    SELECT ing.id, ing.stockact, ing.accountegreso, ing.ln_ue, ing.ln_ue_destino, cap.nu_cap
                                    FROM tb_matriz_ingreso ing
                                    LEFT JOIN tb_r_matriz_ingresos_cap AS cap ON ing.id = cap.fk_id_tb_matriz_ingresos
                                    WHERE ln_ue IS NOT NULL) tb_cuentas ON tb_detalle.ue = tb_cuentas.ln_ue AND tb_detalle.ueDestno = tb_cuentas.ln_ue_destino AND tb_detalle.ccap = tb_cuentas.nu_cap
                        LEFT JOIN tb_cat_unidades_ejecutoras tb_ue ON tb_detalle.ueDestno = tb_ue.ue
                        GROUP BY 
                        tb_detalle.ln_ur, 
                        tb_detalle.ue,
                        tb_detalle.ueDestno,
                        tb_detalle.ccap,  
                        tb_detalle.solicitado, 
                        tb_detalle.autorizado
                        ORDER BY tb_detalle.ccap;";

        $SQLDetalle="SELECT tb_r.ln_ur,
                        tb_r.ln_ue,
                        tb_r_d.presupuesto,
                        tb_r.ln_pp,
                        sum(tb_r_d.solicitado) as solicitado,
                        sum(tb_r_d.autorizado) as autorizado,
                        tb_matriz_ingreso.stockact,
                        tb_matriz_ingreso.accountegreso,
                        cdbtag.partida_esp,
                        tb_ue.desc_ue,
                        SUBSTRING(cdbtag.partida_esp, 1, 1) as capitulo
                    FROM tb_radicacion tb_r
                    LEFT JOIN tb_radicacion_detalle tb_r_d on tb_r.id = tb_r_d.idRadicacion
                    LEFT JOIN chartdetailsbudgetbytag cdbtag on tb_r_d.presupuesto = cdbtag.accountcode
                    LEFT JOIN tb_cat_partidaspresupuestales_capitulo tb_capitulo on SUBSTRING(cdbtag.partida_esp, 1, 1) = tb_capitulo.ccap
                    LEFT JOIN tb_matriz_ingreso on concat(tb_r.ln_ur,'-',tb_r.ln_ue,'-',tb_capitulo.ccapmiles) = tb_matriz_ingreso.ln_clave
                    LEFT JOIN tb_cat_unidades_ejecutoras tb_ue ON tb_r.ln_ue = tb_ue.ue
                    WHERE tb_r_d.idRadicacion =  '".$idRadicacion."'
                    group by tb_r.ln_ue, tb_r.ln_pp, SUBSTRING(cdbtag.partida_esp, 1, 1)";
        //echo '<pre>'.$SQLDetalle .'</pre>';
        $resultDetalle=DB_query($SQLDetalle,$db);

        $infoClaves = array();
        while ($myrowDetalle = DB_fetch_array($resultDetalle)) {
            //echo '<pre>'.$myrowDetalle['accountegreso'] .'</pre>';
            $infoClaves[] = array(
                'accountcode' => $myrowDetalle ['presupuesto']
            );

            if($myrowDetalle['accountegreso'] == ""){
                $errMsj .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No existe la configuración en la Matriz de Ingreso para la clave presupuestal: '.$myrowDetalle['presupuesto'].'</p>';
            }   
        }

        $mensajeErrores="";
        $respuesta = fnValPeriodoEjercicioFiscal($db, $infoClaves);
        
        if (!$respuesta['result']) {
            $errMsj .= $respuesta['mensaje'];
        }

        $PeriodNo = $respuesta['periodo'];
        //echo var_dump($respuesta);
        //echo 'sdf: '.$errMsj;
        if($errMsj == ""){
            
            // /* Modificar el encabezado*/
            $SQL = "UPDATE tb_radicacion 
                    SET estatus = 5
                    WHERE id = '".$idRadicacion."' AND `folio` = '".$folio."'";

            $ErrMsg = "No se modifico la radicación";
            $result = DB_query($SQL, $db, $ErrMsg);
            
            $Mensaje ="";

            if($result){
                fnAfectacionPresupuestal(271,$idRadicacion,$folio,$ur,$mes,$db);
                //$errMsj ="Se autorizo la radicación con folio: ".$folio.", correctamente.";
                $errMsj = "true";
            }else{
                $errMsj ="Problemas al autorizar la radicación con folio: ".$folio."";
            }

            DB_data_seek($resultDetalle ,0);
            //$PeriodNo = GetPeriod((date('d/m/Y')), $db);
            $folioPolizaRadicacion = fnObtenerFolioUeGeneral($db, $ur,$oficinaCentral, 307);

            $SQLMonto = "SELECT SUM(autorizado) AS autorizado FROM tb_radicacion_detalle WHERE idRadicacion ='".$idRadicacion."';";

            $resultMonto = DB_query($SQLMonto, $db);
            $myrowMonto = DB_fetch_array($resultMonto);
            $montoRadicacion = $myrowMonto['autorizado'];

            //****************************************************************
            /*/
                Poliza de oficina central
            /*/
            $narrativa="Radicación Autorizada, Folio: " .$folio ."";

            /*/
                Concentradora
            /*/
            $ISQL = Insert_Gltrans('307',$folio,date('Y-m-d'),$PeriodNo,$cuentaContableConcentradora,$narrativa,$ur,$_SESSION['UserID'],1,'','','',0,0,0,0,'',0,($montoRadicacion),$db,$ChequeNo=0,$catcuenta='',$jobref=0, $bancodestino=null, $rfcdestino=null, $cuentadestino=null,1, $oficinaCentral,$folioPolizaRadicacion);
            $ErrMsg = "Problemas en la cuenta concentradora";
            //echo $ISQL;
            $result = DB_query($ISQL, $db,$ErrMsg);

            $ISQL = fnInsertBankTrans($folio,'292',$cuentaContableConcentradora,'Radicación Autorizada',($montoRadicacion),$ur,'',$oficinaCentral);
            $result = DB_query($ISQL, $db,$ErrMsg);

            
            /*/
                Beneficiario 
            /*/
            $ISQL = Insert_Gltrans('307',$folio,date('Y-m-d'),$PeriodNo,$cuentaContableBeneficiarios,$narrativa,$ur,$_SESSION['UserID'],1,'','','',0,0,0,0,'',0,($montoRadicacion * -1),$db,$ChequeNo=0,$catcuenta='',$jobref=0, $bancodestino=null, $rfcdestino=null, $cuentadestino=null,1, $oficinaCentral,$folioPolizaRadicacion);
            $ErrMsg = "Problemas en la cuenta concentradora";
            $result = DB_query($ISQL, $db,$ErrMsg);

            $ISQL = fnInsertBankTrans($folio,'292',$cuentaContableBeneficiarios,'Radicación Autorizada',($montoRadicacion * -1),$ur,'',$oficinaCentral);
            $result = DB_query($ISQL, $db,$ErrMsg);
            
            

            //***************************************************************

            /*/
                Afectacion gerencia 
            /*/

            $folioPolizaRadicacionDetalle = fnObtenerFolioUeGeneral($db, $ur, $ue, 292);

            while ($myrowDetalle = DB_fetch_array($resultDetalle)) {
                
                /*/
                    CARGO
                /*/
                $ISQL = Insert_Gltrans('292',$folio,date('Y-m-d'),$PeriodNo,$myrowDetalle['stockact'],'Radicación Autorizada',$ur,$_SESSION['UserID'],1,'','','',0,0,0,0,'',0,($myrowDetalle['autorizado']),$db,$ChequeNo=0,$catcuenta='',$jobref=0, $bancodestino=null, $rfcdestino=null, $cuentadestino=null,1, $ue,$folioPolizaRadicacionDetalle);
                $ErrMsg = "Problemas en la cuenta capitulo";

                $result = DB_query($ISQL, $db, $ErrMsg);

                $ISQL = fnInsertBankTrans($folio,'292',$myrowDetalle['stockact'],'Radicación Autorizada',($myrowDetalle['autorizado']),$ur,'',$ue);
                $result = DB_query($ISQL, $db,$ErrMsg);

                /*/
                    ABONO
                /*/

                $ISQL = Insert_Gltrans('292',$folio,date('Y-m-d'),$PeriodNo,$myrowDetalle['accountegreso'],'Radicación Autorizada',$ur,$_SESSION['UserID'],1,'','','',0,0,0,0,'',0,($myrowDetalle['autorizado'] *-1),$db,$ChequeNo=0,$catcuenta='',$jobref=0, $bancodestino=null, $rfcdestino=null, $cuentadestino=null,1, $ue,$folioPolizaRadicacionDetalle);
                $ErrMsg = "Problemas en la cuenta capitulo";

                $result = DB_query($ISQL, $db, $ErrMsg);

                $ISQL = fnInsertBankTrans($folio,'292',$myrowDetalle['accountegreso'],'Radicación Autorizada',($myrowDetalle['autorizado'] *-1),$ur,'',$ue);
                $result = DB_query($ISQL, $db,$ErrMsg);

            }
        }
    }

    return $errMsj;
}

function fnInsertBankTrans($transno, $systype_doc, $cuenta, $narrative, $rmonto, $tagref, $beneficiario,$ue){
    
    $SQL="INSERT INTO banktrans (transno,
                    type,
                    bankact,
                    ref,
                    exrate,
                    functionalexrate,
                    transdate,
                    banktranstype,
                    amount,
                    currcode,
                    tagref,
                    beneficiary,
                    usuario,
                    ln_ue,
                    nu_type,
                    nu_anio_fiscal
                ) ";
    $SQL= $SQL . "VALUES ('" . $transno . "',
                            '" . $systype_doc . "',
                            '" . $cuenta . "',
                            '" . $narrative . "',
                            1,
                            1,
                            curdate(),
                            'Transferencia',
                            '" . $rmonto . "',
                            'MXN',
                            '" . $tagref . "',
                            '" . $beneficiario . "',
                            '".$_SESSION['UserID']."',
                            '".$ue."',
                            '" . $systype_doc . "',
                            '".$_SESSION['ejercicioFiscal']."'
                        )";
        
    return $SQL;
}


$dataObj = array('sql' => '', 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);

?>