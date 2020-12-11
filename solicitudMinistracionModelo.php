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
$funcion=2387;
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
            WHERE 1=1 AND cdbtag.anho = '".$_SESSION['ejercicioFiscal']."' ";

            if(isset($_POST['idMinistracion']) and $_POST['idMinistracion'] != ""){
                $SQL .= " AND cdb.cvefrom IN (SELECT presupuesto FROM tb_ministracion_detalle WHERE idMinistracion = ".$_POST['idMinistracion'].")"; 
            }

            if(isset($_POST['ur']) and $_POST['ur']!=""){
                $SQL .= " AND cdb.tagref = '".$_POST['ur']."' "; 
            }

            // if(isset($_POST['ue']) and $_POST['ue']!="" and $_POST['ue']!="-1"){
            //     $SQL .= " AND ln_ue in (".$_POST['ue'].") "; 
            // }

            if(isset($_POST['pp']) and $_POST['pp'] != "" and  $_POST['pp'] != "-1"){
                $SQL .= " AND cdbtag.cppt = '".$_POST['pp']."' "; 
            }

            if(isset($_POST['capitulo']) and $_POST['capitulo']!=""){
                $SQL .= " AND pe.ccap in (".$_POST['capitulo'].") "; 
            }

            $SQL .=" GROUP BY cdb.cvefrom ORDER BY pe.ccap;";
            //echo "".$SQL;
    $result = DB_query($SQL,$db);

    while ( $myrow = DB_fetch_array($result)) {
        $datos['value'] = $myrow ['cvefrom'];
        $datos['texto'] = $myrow ['cvefrom'];
        $info[] = $datos;
    }

    $contenido = array('datos' => $info);
}

if ($option == 'guardarMinistracion'){

    //$folio="1";
    $folio = GetNextTransNo('291',$db);

    $SQL="INSERT INTO `tb_ministracion`
                (`ln_ur`,
                `ln_ue`,
                `folio`,
                `ln_pp`,
                `ln_capitulo`,
                `ln_clcSiaff`,
                `ln_clcGRP`,
                `ln_clcSicop`,
                `ln_mes`,
                `justificacion`,
                `fecha_elab`,
                `fecha_pago`,
                `fecha_autorizacion`,
                `usuario`,
                `estatus`,
                `idbeneficiario`,
                `idconcentradora`,
                `idfirmante`,
                `nu_anio_fiscal`)
            VALUES(
                '".$_POST['ln_ur']."',
                '".$_POST['ln_ue']."',
                '".$folio."',
                '".$_POST['ln_pp']."',
                '".$_POST['ln_capitulo']."',
                '".$_POST['ln_clcSiaff']."',
                '".$_POST['ln_clcGRP']."',
                '".$_POST['ln_clcSicop']."',
                '".$_POST['ln_mes']."',
                '".$_POST['justificacion']."',
                curdate(),
                '".fnFormatoFechaYMD($_POST['fecha_pago'],'-')."',
                '".fnFormatoFechaYMD($_POST['fecha_autorizacion'],'-')."',
                '".$_SESSION['UserID']."',
                '".$_POST['estatus']."',
                '".$_POST['idBeneficiario']."',
                '".$_POST['idConcentradora']."',
                '".$_POST['idFirmante']."',
                '".$_SESSION['ejercicioFiscal']."'
            );";

    //echo "<pre>".$sql."</pre>";
    $result = DB_query($SQL,$db);
    $idMinistracion = DB_Last_Insert_ID($db, 'tb_ministracion', 'id');

    if($result){

        //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        //!!   Guardar Detalle de la Ministracion.         !!
        //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

        $SQL="INSERT INTO tb_ministracion_detalle
                (`idMinistracion`,
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
            $SQL.="('".$idMinistracion."',
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

            fnAfectacionPresupuestal(266,$idMinistracion,$folio,$_POST['ln_ur'],$_POST['ln_mes'],$db);

            $url = "&Folio=>" . $folio;
            $url .= "&idMinistracion=>" . $idMinistracion;
            $url = $enc->encode($url);
            $liga_folio= "URL=". $url;
            $contenido[] = array(
                'folio' => $folio,
                'idMinistracion' => $idMinistracion
            );


            $Mensaje ="Se agregó la ministración con folio: ".$folio.", correctamente.";

            //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
            //!!   Guardar Archivos si hay seleccionado.       !!
            //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

            if(isset($_FILES['archivos'])){
                foreach ($_FILES['archivos']['tmp_name'] as $key => $tmp_name) {

                    $name_visible = $_FILES['archivos']['name'][$key];
                    $file_name = $key.$_FILES['archivos']['name'][$key];
                    $file_tmp =$_FILES['archivos']['tmp_name'][$key];

                    $name_visible=str_replace(" ", "", $name_visible);
                    $file_name=str_replace(" ", "", $file_name);
                    $file_type=$_FILES['archivos']['type'][$key];

                    move_uploaded_file($file_tmp, "archivos/".$file_name);

                    $cadenaDatosInsertar .= "('".$_SESSION['UserID']."','".$file_type."','".$file_name."','archivos/".$file_name."','".$funcion."',291,'".$name_visible."','".$intIdMinistracion."','".$_POST['newtipoclc'.$key]."',1),";
                }

                $cadenaDatosInsertar = substr($cadenaDatosInsertar, 0, -1);

                if($cadenaDatosInsertar !=""){
                    $SQL = "INSERT INTO tb_archivos (`ln_userid`, `sn_tipo`, `ln_nombre_interno_archivo`,`txt_url`,`nu_funcion`,`nu_tipo_sys`,`ln_nombre`, `nu_trasnno`,`ind_permiso_active`,`ind_active`) VALUES ".$cadenaDatosInsertar;

                    $ErrMsg = "Problemas al guardar el archivos de ministracion.";
                    $result = DB_query($SQL, $db, $ErrMsg);

                    if($result){
                        $Mensaje .="<p>Se guardó el archivo correctamente.</p>";
                    }
                }
            } 

            for ($i=0; $i < $_POST['numFilasArchivos']; $i++) { 
                $SQL="UPDATE tb_archivos SET `ind_permiso_active`= ".$_POST['tipoclc'.$i]." WHERE nu_id_documento = " . $_POST['idFileCLC'.$i];
                $result = DB_query($SQL, $db, $ErrMsg);
            }

        }else{
            $Mensaje ="Problemas al guardar detalle de la ministración con folio: ".$folio."";
        }

    }else{
        $Mensaje ="Problemas al guardar la ministración";
    }
}

if($option == "modificarMinistracion"){
    
    $folio = $_POST['intFolio'];
    $intIdMinistracion = $_POST['intIdMinistracion'];
    $idEstatus = $_POST['estatus'];
    $ur = $_POST['ln_ur'];

    if($idEstatus < 5){
        /* Modificar el encabezado*/
        $SQL = "UPDATE tb_ministracion SET ";
        $SQL .= "   `ln_capitulo` = '".$_POST['ln_capitulo']."',
                    `ln_clcSiaff` = '".$_POST['ln_clcSiaff']."',
                    `ln_clcGRP` = '".$_POST['ln_clcGRP']."',
                    `ln_clcSicop` = '".$_POST['ln_clcSicop']."',
                    `justificacion` = '".$_POST['justificacion']."',
                    `fecha_pago` = '".fnFormatoFechaYMD($_POST['fecha_pago'],'-')."',
                    `fecha_autorizacion` = '".fnFormatoFechaYMD($_POST['fecha_autorizacion'],'-')."',
                    `idbeneficiario` = '".$_POST['idBeneficiario']."',
                    `idconcentradora` = '".$_POST['idConcentradora']."',
                    `idfirmante` = '".$_POST['idFirmante']."'
                WHERE `id` = '".$intIdMinistracion."'";
        //echo "sql:".$SQL;
        $ErrMsg = "No se obtuvieron las ministracion";
        $TransResult = DB_query($SQL, $db, $ErrMsg);

        /*Modificar Detalle*/

        $SQL="DELETE FROM tb_ministracion_detalle WHERE idMinistracion ='".$intIdMinistracion."';";
        $TransResult = DB_query($SQL, $db);

        $SQL="INSERT INTO tb_ministracion_detalle
                    (`idMinistracion`,
                    `folio`,
                    `presupuesto`,
                    `solicitado`,
                    `autorizado`,
                    `orden`,
                    `usuario`)
                VALUES";

        for ($i=0; $i < $_POST['numFilas']; $i++) {            
            $SQL.="('".$intIdMinistracion."',
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

            fnAfectacionPresupuestal(266,$intIdMinistracion,$folio,$ur,$_POST['ln_mes'],$db);

            $url = "&Folio=>" . $folio;
            $url .= "&idMinistracion=>" . $intIdMinistracion;
            $url = $enc->encode($url);
            $liga_folio= "URL=". $url;
            $contenido[] = array(
                'folio' => $folio,
                'idMinistracion' => $intIdMinistracion
            );
            $Mensaje ="<p>Se modificó la ministración con folio: ".$folio.", correctamente.</p>";
        }else{
            $Mensaje ="<p>Problemas al guardar detalle de la ministración con folio: ".$folio.".</p>";
        }
    }elseif($idEstatus == 5){
        /* Modificar el encabezado*/
        $SQL = "UPDATE tb_ministracion SET ";
        $SQL .= "   `ln_clcSiaff` = '".$_POST['ln_clcSiaff']."',
                    `ln_clcGRP` = '".$_POST['ln_clcGRP']."',
                    `ln_clcSicop` = '".$_POST['ln_clcSicop']."'
                WHERE `id` = '".$intIdMinistracion."'";
        //echo "sql:".$SQL;
        $ErrMsg = "No se obtuvieron las ministracion";
        $result = DB_query($SQL, $db, $ErrMsg);

        $url = "&Folio=>" . $folio;
        $url .= "&idMinistracion=>" . $intIdMinistracion;
        $url = $enc->encode($url);
        $liga_folio= "URL=". $url;
        $contenido[] = array(
            'folio' => $folio,
            'idMinistracion' => $intIdMinistracion
        );
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

                $cadenaDatosInsertar .= "('".$_SESSION['UserID']."','".$file_type."','".$file_name."','archivos/".$file_name."','".$funcion."',291,'".$name_visible."','".$intIdMinistracion."','".$_POST['newtipoclc'.$key]."',1),";

                moverArchivo($file_name,$_FILES['archivos']['tmp_name'][$key],SUBIDAARCHIVOS);
                //move_uploaded_file($file_tmp, "/archivos/user.autorizador/comprobante/23/".$file_name);
            
        }

        $cadenaDatosInsertar = substr($cadenaDatosInsertar, 0, -1);

        if($cadenaDatosInsertar !=""){
            $SQL = "INSERT INTO tb_archivos (`ln_userid`, `sn_tipo`, `ln_nombre_interno_archivo`,`txt_url`,`nu_funcion`,`nu_tipo_sys`,`ln_nombre`, `nu_trasnno`,`ind_permiso_active`,`ind_active`) VALUES ".$cadenaDatosInsertar;

            $ErrMsg = "Problemas al guardar el archivos de ministracion.";
            $result = DB_query($SQL, $db, $ErrMsg);
            if($result){
                $Mensaje .="<p>Se guardó el archivo en la ministración con folio: ".$folio." correctamente.</p>";
            }
        }
    }

    for ($i=0; $i < $_POST['numFilasArchivos']; $i++) { 
        $SQL="UPDATE tb_archivos SET `ind_permiso_active`= ".$_POST['tipoclc'.$i]." WHERE nu_id_documento = " . $_POST['idFileCLC'.$i];
        $result = DB_query($SQL, $db, $ErrMsg);
    }

    if($Mensaje ==""){
        $Mensaje ="<p>Se modificó la ministración con folio: ".$folio.", correctamente.</p>";
    }

    unset($_FILES['archivos']);
}

if ($option == 'obtenerMinistracion'){

    //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    //!!                                                           !!
    //!!        Obtener encabezado de la ministracion.             !!
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
                    tb_m.ln_clcGRP as clcGRP,
                    tb_m.ln_clcSicop as clcSicop,
                    tb_m.ln_capitulo as capitulo,
                    coalesce(tb_m.idfirmante,'') as idfirmante,
                    truncate(coalesce(sum(tb_m_d.solicitado),0),2) as importe,
                    case when tb_m.fecha_pago = '0000-00-00 00:00:00' then '' else  date_format(tb_m.fecha_pago,'%d-%m-%Y')   end as fecha_pago,
                    case when tb_m.fecha_autorizacion = '0000-00-00 00:00:00' then '' else  date_format(tb_m.fecha_autorizacion,'%d-%m-%Y')   end as fecha_autorizacion,
                    tb_m.estatus,
                    tb_m.justificacion,
                    tb_bc.id as idbeneficiario,
                    tb_bc.rfc as rfcbeneficiario,
                    tb_bc.cuenta as clabebeneficiario,
                    tb_m.idconcentradora,
                    tb_bc2.cuenta as cuentaConcentradora
            FROM tb_ministracion tb_m
            LEFT JOIN tags on tb_m.ln_ur = tags.tagref
            LEFT JOIN tb_cat_unidades_ejecutoras tb_ue ON tb_m.ln_ue = tb_ue.ue
            LEFT JOIN tb_cat_programa_presupuestario tb_pp ON tb_m.ln_pp = tb_pp.cppt
            LEFT JOIN tb_ministracion_detalle tb_m_d ON tb_m.folio=tb_m_d.folio
            LEFT JOIN tb_estatus_ministracion tb_e_r ON tb_m.estatus = tb_e_r.estatus
            LEFT JOIN tb_beneficiario_concentradora tb_bc ON tb_m.idbeneficiario =tb_bc.id
            LEFT JOIN tb_beneficiario_concentradora tb_bc2 ON tb_m.idconcentradora=tb_bc2.id
            WHERE tb_m.id = '".$_POST['idMinistracion']."'";

            // if(isset($_POST['UR']) and $_POST['UR'] !=""){
            //     $SQL.=" AND tb_m.ln_ur = '".$_POST['UR']."'";
            // }

            // if(isset($_POST['UE']) and $_POST['UE'] !=""){
            //     $SQL.=" AND tb_m.ln_ue = '".$_POST['UE']."'";
            // }

    $SQL .= " GROUP BY tb_m.id,tb_m.ln_ur, tb_m.ln_ue,tb_m.folio;";

    //echo "sql:".$SQL;
    $ErrMsg = "No se obtuvieron las ministracion";
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
            'clcGRP' => $myrow ['clcGRP'],
            'clcSicop' => $myrow ['clcSicop'],
            'capitulo' => $myrow ['capitulo'],
            'importe' => $myrow ['importe'],
            'fecha_pago' => $myrow ['fecha_pago'],
            'fecha_autorizacion' => $myrow ['fecha_autorizacion'],
            'estatus' => $myrow ['estatus'],
            'justificacion' => $myrow ['justificacion'],
            'idbeneficiario' => $myrow ['idbeneficiario'],
            'rfcbeneficiario' => $myrow ['rfcbeneficiario'],
            'clabebeneficiario' => $myrow ['clabebeneficiario'],
            'idConcentradora' => $myrow ['idconcentradora'],
            'cuentaConcentradora' => $myrow ['cuentaConcentradora'],
            'idfirmante' => $myrow ['idfirmante']

        );
    }

    //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    //!!                                                           !!
    //!!        Obtener detalle de la ministracion.                !!
    //!!                                                           !!
    //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    $SQL = "SELECT tb_m_d.id, 
                    tb_m_d.folio, 
                    tb_m_d.presupuesto, 
                    truncate(coalesce(tb_m_d.solicitado,0), 2) as solicitado,
                    truncate(coalesce(tb_m_d.autorizado,0), 2) as autorizado,
                    tb_m_d.orden, 
                    tb_m_d.usuario 
            FROM tb_ministracion_detalle tb_m_d
            WHERE tb_m_d.idMinistracion ='".$_POST['idMinistracion']."'
            ORDER BY orden";

    $ErrMsg = "No se obtuvieron el detalle de ministracion";
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
    //!!        Obtener archivos de la ministracion.               !!
    //!!                                                           !!
    //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

    $SQL = "SELECT nu_id_documento,txt_url,ln_nombre,ind_permiso_active FROM tb_archivos WHERE  nu_tipo_sys = '291' AND nu_trasnno = '".$_POST['idMinistracion']."' and ind_active = 1;";

    $ErrMsg = "No se obtuvieron el detalle de los archivos";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    while ($myrow = DB_fetch_array($TransResult)) {

        $infoDetalleArchivos[] = array(
            'idFile' => $myrow ['nu_id_documento'],
            'urlFile' => $myrow ['txt_url'],
            'nameFile' => $myrow ['ln_nombre'],
            'idTipo' => $myrow ['ind_permiso_active']
        );
    }
    
    $contenido = array('datos' => $info,'datalle' =>$infoDetalle, 'detalleArchivos' => $infoDetalleArchivos);
    $result = true;
}

if ($option == 'obtenerDetalleMinistracion'){
    $SQL = "SELECT tb_m_d.id, 
                    tb_m_d.folio, 
                    tb_m_d.presupuesto, 
                    truncate(coalesce(tb_m_d.solicitado,0), 2) as solicitado,
                    truncate(coalesce(tb_m_d.autorizado,0), 2) as autorizado,
                    tb_m_d.orden, 
                    tb_m_d.usuario 
            FROM tb_ministracion_detalle tb_m_d
            WHERE tb_m_d.idMinistracion ='".$_POST['idMinistracion']."'
            ORDER BY orden";

    $ErrMsg = "No se obtuvieron el detalle de ministracion";
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

if ($option == 'autorizarMinistracion'){

    //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    //!!    Validacion de las cuentas contables.       !!
    //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    $folio = $_POST['intFolio'];
    $idMinistracion = $_POST['idMinistracion'];
    $ur = $_POST['ur'];
    $mes =$_POST['ln_mes'];

    $amountAutorizado=0;
    $accountCargo="";
    $accountAbono="";

    $Mensaje = fnContabilidadMinistracion($folio,$idMinistracion,$ur,$mes,$db);
    $result = true;

    // $SQLMonto="SELECT tb_m_d.idMinistracion,
    //             sum(autorizado) AS amount, 
    //             tb_m.ln_ur,
    //             tb_m.ln_pp,
    //             tb_matriz_ingreso.stockact,
    //             tb_matriz_ingreso.accountegreso,
    //             tb_m.idbeneficiario,
    //             tb_bc.cuenta,
    //             tb_bc.cuentafinal
    //             FROM tb_ministracion_detalle tb_m_d 
    //             LEFT JOIN (SELECT tb_ministracion.id,tb_ministracion.ln_ur,tb_ministracion.ln_pp,tb_ministracion.idbeneficiario,tb_ministracion.idconcentradora 
    //                         FROM tb_ministracion 
    //                         LEFT JOIN tb_beneficiario_concentradora on tb_ministracion.idbeneficiario = tb_beneficiario_concentradora.id  
    //                         WHERE tb_ministracion.id = " . $idMinistracion . " ) tb_m on tb_m_d.idMinistracion = tb_m.id
    //             LEFT JOIN tb_matriz_ingreso on tb_matriz_ingreso.ln_clave = concat(tb_m.ln_ur,'-',tb_m.ln_pp)
    //             LEFT JOIN tb_beneficiario_concentradora tb_bc ON tb_bc.id
    //             WHERE idMinistracion =" . $idMinistracion . "
    //             GROUP BY tb_m_d.idMinistracion";
    // $resultMonto = DB_query($SQLMonto, $db, $ErrMsg);

    // if($resultMonto){
    //     $arrMontoAutorizado = DB_fetch_array($resultMonto);
    //     $amountAutorizado = $arrMontoAutorizado['amount'];
    //     $accountCargo=$arrMontoAutorizado['stockact'];
    //     $accountAbono=$arrMontoAutorizado['accountegreso'];
    //     $cuentaBancoBeneficiario = $arrMontoAutorizado['cuenta'];
    //     $cuentaBancoFinal = $arrMontoAutorizado['cuentafinal'];
    // }

    // if(!empty($accountCargo) and $accountCargo !="" and $cuentaBancoBeneficiario !=""){
    //     /* Modificar el encabezado*/
    //     $SQL = "UPDATE tb_ministracion 
    //             SET estatus = 5
    //             WHERE id = '".$idMinistracion."' AND `folio` = '".$folio."'";
    //     $ErrMsg = "No se obtuvieron las ministración";
    //     $result = DB_query($SQL, $db, $ErrMsg);

    //     if($result){

    //         fnAfectacionPresupuestal(268,$idMinistracion,$folio,$ur,$mes,$db);

    //         //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    //         //!!      Poliza contable de la ministracion.      !!
    //         //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

    //         $PeriodNo = GetPeriod((date('d').'/'.$mes.'/'.date('Y')), $db);
    //         $PeriodNo = fnObtenerPeriodo($mes,$db);
    //         $folioPolizaUe = fnObtenerFolioUeGeneral($db, $ur,'00', 291);


    //         //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    //         //!!                                               !!
    //         //!!            Poliza de ministracion.            !!
    //         //!!                                               !!
    //         //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    //         //Cargo
    //         $ISQL = Insert_Gltrans('291',$idMinistracion,date('Y-m-d'),$PeriodNo,$accountCargo,'Ministracion Autorizada',$ur,$_SESSION['UserID'],1,'','','',0,0,0,0,'',0,($amountAutorizado * -1),$db,$ChequeNo=0,$catcuenta='',$jobref=0, $bancodestino=null, $rfcdestino=null, $cuentadestino=null,1, '00',$folioPolizaUe);
    //         $resultCargo = DB_query($ISQL, $db);

    //         //Abono
    //         $ISQL = Insert_Gltrans('291',$idMinistracion,date('Y-m-d'),$PeriodNo,$accountAbono,'Ministracion Autorizada',$ur,$_SESSION['UserID'],1,'','','',0,0,0,0,'',0,$amountAutorizado,$db,$ChequeNo=0,$catcuenta='',$jobref=0, $bancodestino=null, $rfcdestino=null, $cuentadestino=null,1, '00',$folioPolizaUe);
    //         $resultAbono = DB_query($ISQL, $db);

    //         //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    //         //!!                                               !!
    //         //!!          Poliza y Afectacion bancos.          !!
    //         //!!                                               !!
    //         //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

    //         $folioPolizaUe = fnObtenerFolioUeGeneral($db, $ur,'00', 291);

    //         $ISQL = Insert_Gltrans('291',$idMinistracion,date('Y-m-d'),$PeriodNo,$cuentaBancoBeneficiario,'Ministracion Autorizada',$ur,$_SESSION['UserID'],1,'','','',0,0,0,0,'',0,$amountAutorizado,$db,$ChequeNo=0,$catcuenta='',$jobref=0, $bancodestino=null, $rfcdestino=null, $cuentadestino=null,1, '00',$folioPolizaUe);
    //         $result = DB_query($ISQL, $db);

    //         /*Afectación en bancos*/
    //         $ISQL = fnInsertBankTrans($idMinistracion,'291',$cuentaBancoBeneficiario,'Ministración',$amountAutorizado,$ur,$arrMontoAutorizado['idbeneficiario']);

    //         $result = DB_query($ISQL, $db);

    //         //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    //         //!!                                               !!
    //         //!!        Poliza y Afectacion bancos.            !!
    //         //!!                                               !!
    //         //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

    //         if($cuentaBancoFinal !=""){
    //             $folioPolizaUe = fnObtenerFolioUeGeneral($db, $ur,'00', 291);

    //             $ISQL = Insert_Gltrans('291',$idMinistracion,date('Y-m-d'),$PeriodNo,$cuentaBancoBeneficiario,'Ministracion Autorizada',$ur,$_SESSION['UserID'],1,'','','',0,0,0,0,'',0,($amountAutorizado * -1),$db,$ChequeNo=0,$catcuenta='',$jobref=0, $bancodestino=null, $rfcdestino=null, $cuentadestino=null,1, '00',$folioPolizaUe);

    //             $result = DB_query($ISQL, $db);

    //             $ISQL = Insert_Gltrans('291',$idMinistracion,date('Y-m-d'),$PeriodNo,$cuentaBancoFinal,'Ministracion Autorizada',$ur,$_SESSION['UserID'],1,'','','',0,0,0,0,'',0,$amountAutorizado,$db,$ChequeNo=0,$catcuenta='',$jobref=0, $bancodestino=null, $rfcdestino=null, $cuentadestino=null,1, '00',$folioPolizaUe);

    //             $result = DB_query($ISQL, $db);

    //             $ISQL = fnInsertBankTrans($idMinistracion,'291',$cuentaBancoFinal,'Ministración',$amountAutorizado,$ur,$arrMontoAutorizado['idbeneficiario']);

    //             $result = DB_query($ISQL, $db);
    //         }

    //         $Mensaje ="Se autorizó la ministración con folio: ".$folio.", correctamente.";
    //     }else{
    //         $Mensaje ="Problemas al autorizar la ministración con folio: ".$folio."";
    //     }
    // }else{
    //     $result = false;
    //     $Mensaje="No existen cuentas contables configuradas para la UR:".$ur." y el Programa Presupuestal: ".$arrMontoAutorizado['ln_pp'].".";

    //     if($cuentaBancoBeneficiario ==""){
    //         $Mensaje .= "<br> No existen cuentas de banco configuradas."; 
    //     }
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
    $period = GetPeriod((date('d').'/'.$mes.'/'.date('Y')), $db);

    if ($_SESSION['ejercicioFiscal'] != date('Y')) {
        // si no es el año actual tomar diciembre
        $period = GetPeriod(date('d').'/12/'.$_SESSION['ejercicioFiscal'], $db);
    }

    $period = fnObtenerPeriodo($mes,$db);
    $info = fnInfoPresupuestoMinistracion($db, $clave, $period, $account, $legalid, $datosClave, $datosClaveAdecuacion, $tipoAfectacion, $type, $transno, $tipoMovimiento);

    if (empty($info)) {
        $Mensaje = "No se encontró la información para la Clave Presupuestal ".$clave;
        $res = false;
    }

    $contenido = array('datos' => $info);
    $result = $res;
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

if($option == 'obtenerArchivosMinistracion'){
    $SQL = "SELECT nu_id_documento,txt_url,ln_nombre,ind_permiso_active FROM tb_archivos WHERE  nu_tipo_sys = '291' AND nu_trasnno = '".$_POST['idMinistracion']."' and ind_active = 1;";

    $ErrMsg = "No se obtuvieron el detalle de los archivos";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    while ($myrow = DB_fetch_array($TransResult)) {

        $infoDetalleArchivos[] = array(
            'idFile' => $myrow ['nu_id_documento'],
            'urlFile' => $myrow ['txt_url'],
            'nameFile' => $myrow ['ln_nombre'],
            'idTipo' => $myrow ['ind_permiso_active']
        );
    }
    
    $contenido = array('datos' => $infoDetalleArchivos);
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

function fnAfectacionPresupuestal($tipoAfectacion,$idMinistracion,$folio,$ln_ur,$mes,$db){
    $PeriodNo = GetPeriod((date('d').'/'.$mes.'/'.date('Y')), $db);
    $PeriodNo = fnObtenerPeriodo($mes,$db);
    $monto=0;
    $status=5;
    $comentarios="Ministración autorizada.";

    if($tipoAfectacion == 266){
        $SQL="DELETE FROM chartdetailsbudgetlog WHERE type = '291' and transno ='".$idMinistracion."';";
        $result = DB_query($SQL, $db);
        $comentarios="Ministración en tramite.";
    }else{
        fnInsertPresupuestoLogMovContrarios($db, '291', $idMinistracion, $typeNuevo = 0, $transnoNuevo = 0);
    }

    $SQL = "SELECT `idMinistracion`,
                    `folio`,
                    `presupuesto`,
                    `solicitado`,
                    `autorizado`,
                    `orden`,
                    `usuario`
            FROM tb_ministracion_detalle
            WHERE idMinistracion = '".$idMinistracion."' AND `folio` = '".$folio."'";
    $$ErrMsg="Problemas en la afectacion presupuestal.";
    $result = DB_query($SQL, $db, $ErrMsg);

    while ($myrow = DB_fetch_array($result)) {
        $ln_ue = fnObtenerUnidadEjecutoraClave($db, $myrow['presupuesto']);
        $monto = $myrow['autorizado'];
        if($tipoAfectacion == 266){
            $monto = $myrow['solicitado'];
            $status=4;
        }

        $agregoLogAcumulado = fnInsertPresupuestoLogAcomulado($db, 291, $idMinistracion, $ln_ur,$myrow['presupuesto'], $PeriodNo, ($monto * -1), $tipoAfectacion,"", $comentarios, 1, $status, 0, $ln_ue, 'DESC', 'ministracion');
        // $agregoLog = fnInsertPresupuestoLog($db, 291, $idMinistracion, $ln_ur, $myrow['presupuesto'], $PeriodNo, ($monto * -1), $tipoAfectacion, "", $comentarios, 1, $status, 0, $ln_ue);            
    }
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

function fnContabilidadMinistracion($folio,$idMinistracion,$ur,$mes,$db){
    $msjError="";
    $msjErrorContable="";
    $msjErrorConcentradora="";
    $cuentaConcentradora ="";
    $msjErrorBeneficiario="";
    $cuentaBeneficiario ="";

    $SQL = "SELECT tb_md.idMinistracion,
                    tb_md.presupuesto, 
                    tb_md.autorizado,
                    tb_m.ln_pp,
                    chartdetailsbudgetbytag.tagref,
                    tb_cat_unidades_ejecutoras.ue, 
                    chartdetailsbudgetbytag.partida_esp,
                    tb_m.cuentaBeneficiario,
                    tb_m.cuentaContableBeneficiario,
                    tb_m.cuentaConcentradora,
                    tb_m.cuentaContableConcentradora,
                    tb_m.cuentaFinal,
                    tb_m.cuentaContableFinal,
                    tb_m.identificador,
                    (SELECT accountegreso 
                     FROM tb_matriz_ingreso 
                     WHERE tb_matriz_ingreso.ln_clave = concat(chartdetailsbudgetbytag.tagref,'-',tb_cat_unidades_ejecutoras.ue,'-',tb_m.ln_pp) and partida = chartdetailsbudgetbytag.partida_esp 
                     ORDER BY accountegreso 
                     LIMIT 1) as accountegreso
            FROM tb_ministracion_detalle tb_md
            LEFT JOIN (SELECT tb_ministracion.id,
                                tb_ministracion.ln_ur,
                                tb_ministracion.ln_pp,
                                tb_ministracion.idbeneficiario,
                                tb_ministracion.idconcentradora,
                                coalesce(tb_bc.cuenta,'') as cuentaBeneficiario,
                                coalesce(tb_bc.cuentacontable,'') as cuentaContableBeneficiario,
                                coalesce(tb_bc2.cuenta,'') as cuentaConcentradora,
                                coalesce(tb_bc2.cuentacontable,'') as cuentaContableConcentradora,
                                coalesce(tb_bc2.cuentafinal,'') as cuentaFinal,
                                coalesce(tb_bc2.cuentacontablefinal,'') as cuentaContableFinal,
                                tb_bc2.identificador
                        FROM tb_ministracion 
                        LEFT JOIN tb_beneficiario_concentradora tb_bc on tb_ministracion.idbeneficiario = tb_bc.id
                        LEFT JOIN tb_beneficiario_concentradora tb_bc2 on tb_ministracion.idconcentradora = tb_bc2.id
                        WHERE tb_ministracion.id = ".$idMinistracion.") tb_m on tb_md.idMinistracion = tb_m.id
            LEFT JOIN chartdetailsbudgetbytag on tb_md.presupuesto = chartdetailsbudgetbytag.accountcode
            LEFT JOIN tb_cat_unidades_ejecutoras on chartdetailsbudgetbytag.ln_aux1 = tb_cat_unidades_ejecutoras.ln_aux1
            WHERE idMinistracion = " . $idMinistracion;
            
    $resultMinistracion = DB_query($SQL,$db);
    

    //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    //!!        Validacion Cuentas Contables.          !!
    //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    $infoClaves = array();
    if($resultMinistracion){
        while ($myrow = DB_fetch_array($resultMinistracion)) {
            $cuentaConcentradora = $myrow['cuentaConcentradora'];
            $cuentaBeneficiario = $myrow['cuentaContableBeneficiario'];
            
            $infoClaves[] = array(
                'accountcode' => $myrow ['presupuesto']
            );

            if($myrow['accountegreso'] == "" or empty($myrow['accountegreso'])){
                $msjErrorContable .= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>La clave presupuestaria: '.$myrow['presupuesto'].', no tiene configurada cuentas contables</p>';
            }
        }

        if($cuentaConcentradora == ""){
            $msjErrorConcentradora = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>No se encuentra configurada la cuenta contable concentradora</p>';
        }

        if($cuentaBeneficiario == ""){
            $msjErrorBeneficiario = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>No se encuentra configurada la cuenta contable del beneficiario</p>';
        }
    }

    $mensajeErrores="";
    $respuesta = fnValPeriodoEjercicioFiscal($db, $infoClaves);
    
    if (!$respuesta['result']) {
        $mensajeErrores .= $respuesta['mensaje'];
    }

    $PeriodNo = $respuesta['periodo'];

    $msjError = $msjErrorContable . $msjErrorConcentradora . $msjErrorBeneficiario . $mensajeErrores;

    if($msjError==""){

        //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        //!!          Generar Polizas Contables.           !!
        //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

        DB_data_seek($resultMinistracion ,0);

        /* Modificar el encabezado*/
        $SQL = "UPDATE tb_ministracion 
                SET estatus = 5
                WHERE id = '".$idMinistracion."' AND `folio` = '".$folio."'";
        $ErrMsg = "No se obtuvieron las ministración";
        $result = DB_query($SQL, $db, $ErrMsg);

        if($result){
            //$PeriodNo = GetPeriod((date('d/m/Y')), $db);

            fnAfectacionPresupuestal(268,$idMinistracion,$folio,$ur,$mes,$db);

            $SQLPolizaConcentradora = "SELECT tb_ministracion.id,
                                tb_ministracion.ln_ur,
                                tb_ministracion.ln_pp,
                                tb_ministracion.ln_clcSiaff, 
                                tb_ministracion.ln_clcGRP, 
                                tb_ministracion.ln_clcSicop,
                                tb_ministracion.idbeneficiario,
                                tb_ministracion.idconcentradora,
                                tb_m_d.autorizado,
                                coalesce(tb_bc.cuenta,'') as cuentaBeneficiario,
                                coalesce(tb_bc.cuentacontable,'') as cuentaContableBeneficiario,
                                coalesce(tb_bc2.cuenta,'') as cuentaConcentradora,
                                coalesce(tb_bc2.cuentacontable,'') as cuentaContableConcentradora,
                                coalesce(tb_bc2.cuentafinal,'') as cuentaFinal,
                                coalesce(tb_bc2.cuentacontablefinal,'') as cuentaContableFinal,
                                tb_bc2.identificador
                        FROM tb_ministracion 
                        LEFT JOIN (SELECT idMinistracion, SUM(autorizado) as autorizado 
                                    FROM tb_ministracion_detalle 
                                    WHERE idMinistracion = ".$idMinistracion.") tb_m_d ON tb_ministracion.id = tb_m_d.idMinistracion
                        LEFT JOIN tb_beneficiario_concentradora tb_bc on tb_ministracion.idbeneficiario = tb_bc.id
                        LEFT JOIN tb_beneficiario_concentradora tb_bc2 on tb_ministracion.idconcentradora = tb_bc2.id
                        WHERE tb_ministracion.id = ".$idMinistracion;

            $resultPolizConcentradora = DB_query($SQLPolizaConcentradora, $db);
            
            if($resultPolizConcentradora){
                
                $rowPolizaConcentradora = DB_fetch_array($resultPolizConcentradora);

                $cuentaBancoBeneficiario = $rowPolizaConcentradora['cuentaContableBeneficiario'];
                $cuentaBancoConcentradora = $rowPolizaConcentradora['cuentaContableConcentradora'];
                $amountMinistracion = $rowPolizaConcentradora['autorizado'];


                //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
                //!!                                               !!
                //!!   Poliza beneficiario - concentradora.        !!
                //!!                                               !!
                //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

                $folioPolizaMinistracion = fnObtenerFolioUeGeneral($db, $ur,'09', 291);
                $narrativa="Ministración Autorizada, Folio: " .$folio .", CLC GRP:".$rowPolizaConcentradora['ln_clcGRP']." , CLC SICOP:".$rowPolizaConcentradora['ln_clcSicop']." , CLC SIAFF:".$rowPolizaConcentradora['ln_clcSiaff']." .";

                //Cargo
                $ISQL = Insert_Gltrans('291',$folio,date('Y-m-d'),$PeriodNo,$cuentaBancoConcentradora,$narrativa,$ur,$_SESSION['UserID'],1,'','','',0,0,0,0,'',0,$amountMinistracion,$db,$ChequeNo=0,$catcuenta='',$jobref=0, $bancodestino=null, $rfcdestino=null, $cuentadestino=null,1, '09',$folioPolizaMinistracion);
                $result = DB_query($ISQL, $db);

                //Abono
                $ISQL = Insert_Gltrans('291',$folio,date('Y-m-d'),$PeriodNo,$cuentaBancoBeneficiario,$narrativa,$ur,$_SESSION['UserID'],1,'','','',0,0,0,0,'',0,($amountMinistracion * -1),$db,$ChequeNo=0,$catcuenta='',$jobref=0, $bancodestino=null, $rfcdestino=null, $cuentadestino=null,1, '09',$folioPolizaMinistracion);
                $result = DB_query($ISQL, $db);

                /*Afectación en bancos*/
                $ISQL = fnInsertBankTrans($folio,'291',$cuentaBancoConcentradora,'Ministración Autorizada',$amountMinistracion,$ur,$rowPolizaConcentradora['idbeneficiario'],'09');

                $result = DB_query($ISQL, $db);


                //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
                //!!                                               !!
                //!!        Poliza de partida especifica.          !!
                //!!                                               !!
                //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

                //Cargo
                //$folioPolizaCuentaPartidas = fnObtenerFolioUeGeneral($db, $ur,'00', 291);

                $ISQL = Insert_Gltrans('291',$folio,date('Y-m-d'),$PeriodNo,$cuentaBeneficiario,'Ministración Autorizada',$ur,$_SESSION['UserID'],1,'','','',0,0,0,0,'',0,$amountMinistracion,$db,$ChequeNo=0,$catcuenta='',$jobref=0, $bancodestino=null, $rfcdestino=null, $cuentadestino=null,1, '09',$folioPolizaMinistracion);
                $resultCargo = DB_query($ISQL, $db);

                $ISQL = fnInsertBankTrans($folio,'291',$cuentaBeneficiario,'Ministración Autorizada',$amountMinistracion,$ur,$rowPolizaConcentradora['idbeneficiario'],'09');
                $resultCargo = DB_query($ISQL, $db);

                while ($myrowPolizaClaves = DB_fetch_array($resultMinistracion)) {
                    
                    //Abono
                    $accountAbono = $myrowPolizaClaves['accountegreso'];
                    $montoPartida = $myrowPolizaClaves['autorizado'];
                    $ISQL = Insert_Gltrans('291',$folio,date('Y-m-d'),$PeriodNo,$accountAbono,'Ministración Autorizada',$ur,$_SESSION['UserID'],1,'','','',0,0,0,0,'',0,($montoPartida * -1),$db,$ChequeNo=0,$catcuenta='',$jobref=0, $bancodestino=null, $rfcdestino=null, $cuentadestino=null,1, '09',$folioPolizaMinistracion);

                    $resultAbono = DB_query($ISQL, $db);

                    $ISQL = fnInsertBankTrans($folio,'291',$accountAbono,'Ministración Autorizada',($montoPartida * -1),$ur,$rowPolizaConcentradora['idbeneficiario'],'09', $_SESSION['ejercicioFiscal']);
                    $resultCargo = DB_query($ISQL, $db);

                }

                $cuentaCargo = $cuentaBancoConcentradora;


                //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
                //!!                                               !!
                //!! Poliza concentradora - Nomina(configuración). !!
                //!!                                               !!
                //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
                $cuentaFinal = $rowPolizaConcentradora['cuentaContableFinal'];
                if($cuentaFinal !=""){
                    //$folioPolizaCuentaFinal = fnObtenerFolioUeGeneral($db, $ur,'00', 291);
                    $cuentaCargo = $cuentaFinal;

                    //Movimientos contables
                    $ISQL = Insert_Gltrans('291',$folio,date('Y-m-d'),$PeriodNo,$cuentaFinal,'Ministración Autorizada',$ur,$_SESSION['UserID'],1,'','','',0,0,0,0,'',0,$amountMinistracion ,$db,$ChequeNo=0,$catcuenta='',$jobref=0, $bancodestino=null, $rfcdestino=null, $cuentadestino=null,1, '09',$folioPolizaMinistracion);
                    $result = DB_query($ISQL, $db);

                    $ISQL = Insert_Gltrans('291',$folio,date('Y-m-d'),$PeriodNo,$cuentaBancoConcentradora,'Ministración Autorizada',$ur,$_SESSION['UserID'],1,'','','',0,0,0,0,'',0,($amountMinistracion * -1),$db,$ChequeNo=0,$catcuenta='',$jobref=0, $bancodestino=null, $rfcdestino=null, $cuentadestino=null,1, '09',$folioPolizaMinistracion);
                    $result = DB_query($ISQL, $db);

                    //Movimientos de bancos
                    $ISQL = fnInsertBankTrans($folio,'291',$cuentaBancoConcentradora,'Ministración Autorizada',($amountMinistracion * -1),$ur,$rowPolizaConcentradora['idbeneficiario'],'09');
                    $result = DB_query($ISQL, $db);

                    $ISQL = fnInsertBankTrans($folio,'291',$cuentaFinal,'Ministración Autorizada',$amountMinistracion,$ur,$rowPolizaConcentradora['idbeneficiario'],'09');
                    $result = DB_query($ISQL, $db);

                }
                $msjError = "true";
                //$msjError ="Se autorizó la ministración con folio: ".$folio.", correctamente.";

            }

        }else{
            $msjError ="Problemas al autorizar la ministración con folio: ".$folio."";
        }

    }

    return $msjError;
}

$dataObj = array('sql' => '', 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);

?>