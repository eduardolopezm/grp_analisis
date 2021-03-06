<?php
/**
 * Resguardo de activo fijo.
 *
 * @category     Panel
 * @package      ap_grp
 * @version      0.1
 * @link /resguardo_detalles_modelo.php
 * Fecha Creación: 05.04.18
 * Se genera el presente programa para la visualización de la información
 * del detalle de los resguardos.
 */
//

$PageSecurity = 1;
$PathPrefix = '../';
$funcion=2388;

//include($PathPrefix.'includes/session.inc');
session_start();
include($PathPrefix . 'config.php');
include $PathPrefix . "includes/SecurityUrl.php";
include($PathPrefix . 'includes/ConnectDB.inc');
include($PathPrefix . 'includes/SecurityFunctions.inc');
include($PathPrefix . 'includes/SQL_CommonFunctions.inc');
// Include para el periodo
include($PathPrefix . 'includes/DateFunctions.inc');

header('Content-type: text/html; charset=ISO-8859-1');
$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

$option =$_POST['option'];
$enc = new Encryption;

$permisoAutorizador= Havepermission($_SESSION['UserID'], 2434, $db);
$permisoValidador= Havepermission($_SESSION['UserID'], 2431, $db);
$permisoCapturista= Havepermission($_SESSION['UserID'], 2430, $db);

if($option == "obtenerRadicacion"){

    $SQL = "SELECT  tb_m.id,
                    tb_m.ln_ur as UR,
                    tb_m.ln_ue as UE, 
                    tb_m.ln_mes as mes_solicitado,
                    tb_m.folio as folio,
                    tb_m.fecha_elab as fecha_captura,
                    tb_m.ln_pp as programa,
                    tb_m.ln_clcSiaff as clc,
                    truncate(coalesce(sum(tb_m_d.solicitado),0),2) as importe,
                    case when tb_m.fecha_pago = '0000-00-00 00:00:00' then '' else  tb_m.fecha_pago  end as fecha_pago,
                    tb_e_r.estatus as estatus,
                    tb_m.justificacion,
                    tb_m.estatus as idEstatus
            FROM tb_radicacion tb_m
            LEFT JOIN tb_radicacion_detalle tb_m_d ON tb_m.folio=tb_m_d.folio
            LEFT JOIN tb_estatus_radicacion tb_e_r ON tb_m.estatus = tb_e_r.id
            JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tb_m.ln_ur AND sec_unegsxuser.userid = '".$_SESSION['UserID']."'
            JOIN `tb_sec_users_ue` ON `tb_sec_users_ue`.`userid` = '".$_SESSION['UserID']."' AND tb_m.ln_ur = `tb_sec_users_ue`.`tagref` AND  tb_m.ln_ue = `tb_sec_users_ue`.`ue`
            WHERE 1=1 and tb_m.nu_anio_fiscal = '".$_SESSION['ejercicioFiscal']."' ";

        if(isset($_POST['selectUnidadNegocio']) and $_POST['selectUnidadNegocio']!="" and $_POST['selectUnidadNegocio']!="-1"){
            $SQL .= " AND tb_m.ln_ur = '".$_POST['selectUnidadNegocio']."'";
        }

        if(isset($_POST['selectUnidadEjecutora']) and $_POST['selectUnidadEjecutora']!=""){
            $SQL .= " AND tb_m.ln_ue IN (".$_POST['selectUnidadEjecutora'].")";
        }

        if(isset($_POST['selectMesRadicacion']) and $_POST['selectMesRadicacion']!=""){
            $SQL .= " AND tb_m.ln_mes IN (".$_POST['selectMesRadicacion'].")";
        }

        if(isset($_POST['selectEstatusRadicacion']) and $_POST['selectEstatusRadicacion']!=""){
            $SQL .= " AND tb_m.estatus IN (".$_POST['selectEstatusRadicacion'].")";
        }

        if(isset($_POST['txtFolio']) and $_POST['txtFolio']!=""){
            $SQL .= " AND tb_m.folio like '".$_POST['txtFolio']."%'";
        }

        if(isset($_POST['selectProgramaPresupuestal']) and $_POST['selectProgramaPresupuestal']!=""){
            $SQL .= " AND tb_m.ln_pp IN (".$_POST['selectProgramaPresupuestal'].")";
        }

        if(isset($_POST['dateDesde']) and $_POST['dateDesde']!=""){
            $SQL .= " AND tb_m.fecha_elab >= '".fnFormatoFechaYMD($_POST['dateDesde'],'-')."'";
        }

        if(isset($_POST['dateHasta']) and $_POST['dateHasta']!=""){
            $SQL .= " AND tb_m.fecha_elab <= '".fnFormatoFechaYMD($_POST['dateHasta'],'-')."'";
        }


    $SQL .= " GROUP BY tb_m.id,tb_m.ln_ur, tb_m.ln_ue,tb_m.folio
                ORDER BY tb_m.ln_ur,tb_m.ln_ue, tb_m.folio DESC;";

    //echo "sql:".$SQL;
    $ErrMsg = "No se obtuvieron las resguardos";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    $datosMeses = array('Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre');

    while ($myrow = DB_fetch_array($TransResult)) {

        $enc = new Encryption;
        $url = "&Folio=>" . $myrow["folio"] . "&idRadicacion=>" .$myrow["id"];
        $url = $enc->encode($url);
        $liga= "URL=" . $url;

        $urlPDF = "&Folio=>" . $myrow["folio"] . "&idRadicacion=>" .$myrow["id"];
        $urlPDF = $enc->encode($urlPDF);
        $ligaPDF= "URL=" . $urlPDF;

        $liga_folio ="<a target='_self' href='./solicitudRadicacion.php?$liga' style='color: blue;'><u>".$myrow ['folio']."</u></a>";

        $liga_pdf = "<a target='_blank' href='./reporteRadicacion.php?$ligaPDF' title='Imprimir'><span class='glyphicon glyphicon glyphicon-print'></span></a>";

        $info[] = array(
            'idCHK'=> false,
            'idPartida' => $myrow ['id'],
            'idFolio' => $myrow ['folio'],
            'idEstatus' => $myrow ['idEstatus'],
            'UR' => $myrow ['UR'],
            'UE' => $myrow ['UE'],
            'mes_solicitado' => $datosMeses[((int)$myrow['mes_solicitado'] -1)],
            'folio' => $liga_folio,
            'fecha_captura' => $myrow ['fecha_captura'],
            'programa' => $myrow ['programa'],
            'importe' => $myrow ['importe'],
            'estatus' => $myrow ['estatus'],
            'justificacion' => $myrow ['justificacion'],
            'pdf' => $liga_pdf
        );
    }

    $ocultarPDF="true";
    $columTamano = '31';
    if($permisoAutorizador == '1'){
        $ocultarPDF="false";
        $columTamano = '25';
    }

    // Columnas para el GRID
    $columnasNombres .= "[";
    $columnasNombres .= "{ name: 'idCHK', type: 'bool' },";
    $columnasNombres .= "{ name: 'idPartida', type: 'string' },";
    $columnasNombres .= "{ name: 'idEstatus', type: 'string' },";
    $columnasNombres .= "{ name: 'UR', type: 'string' },";
    $columnasNombres .= "{ name: 'UE', type: 'string' },";
    $columnasNombres .= "{ name: 'mes_solicitado', type: 'string' },";
    $columnasNombres .= "{ name: 'idFolio', type: 'string' },";
    $columnasNombres .= "{ name: 'folio', type: 'string' },";
    $columnasNombres .= "{ name: 'fecha_captura', type: 'string' },";
    $columnasNombres .= "{ name: 'programa', type: 'string' },";
    $columnasNombres .= "{ name: 'importe', type: 'number' },";
    $columnasNombres .= "{ name: 'estatus', type: 'string' },";
    $columnasNombres .= "{ name: 'justificacion', type: 'string' },";
    $columnasNombres .= "{ name: 'pdf', type: 'string' }";
    $columnasNombres .= "]";

    // Columnas para el GRID
    $columnasNombresGrid .= "[";
    $columnasNombresGrid .= " { text: 'Sel', datafield:'idCHK', editable:true, columntype: 'checkbox', width: '3%', cellsalign: 'center', align: 'center' },";
    $columnasNombresGrid .= " { text: 'idPartida', datafield: 'idPartida', editable:false, width: '6%', cellsalign: 'center', align: 'center', hidden: true },";
    $columnasNombresGrid .= " { text: 'idEstatus', datafield: 'idEstatus', editable:false, width: '6%', cellsalign: 'center', align: 'center', hidden: true },";
    $columnasNombresGrid .= " { text: 'UR', datafield: 'UR', editable:false, width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'UE', datafield: 'UE', editable:false, width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Mes Solicitado', editable:false, datafield: 'mes_solicitado', width: '9%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Folio', datafield: 'idFolio', editable:false, width: '6%', cellsalign: 'center', align: 'center', hidden: true },";
    $columnasNombresGrid .= " { text: 'Folio', datafield: 'folio',editable:false, width: '10%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Fecha Captura', datafield: 'fecha_captura', editable:false, width: '9%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Programa', datafield: 'programa',editable:false, width: '10%', cellsalign: 'center', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Importe', datafield: 'importe',editable:false, width: '10%', cellsalign: 'right', align: 'center', cellsformat: 'C2', hidden: false },";
    $columnasNombresGrid .= " { text: 'Estatus', datafield: 'estatus',editable:false, width: '8%', cellsalign: 'center', align: 'center', hidden: false },";
    
    $columnasNombresGrid .= " { text: 'Justificación', datafield: 'justificacion',editable:false, width: '".$columTamano."%', cellsalign: 'left', align: 'center', hidden: false },";
    $columnasNombresGrid .= " { text: 'Imprimir', datafield: 'pdf',editable:false, width: '6%', cellsalign: 'center', align: 'center', hidden: ".$ocultarPDF." }";
    
    
    $columnasNombresGrid .= "]";

    $nombreExcel = str_replace(' ', '_', traeNombreFuncionGeneral($funcion, $db)).'_'.date('dmY');

    $contenido = array('datos' => $info, 'columnasNombres' => $columnasNombres, 'columnasNombresGrid' => $columnasNombresGrid, 'nombreExcel' => $nombreExcel);
    $result = true;

}

if($option == "obtenerBotones"){
    $SQL="";

    $SQL="SELECT * 
          FROM tb_botones_status 
          INNER JOIN sec_funxuser ON tb_botones_status.functionid = sec_funxuser.functionid AND sec_funxuser.userid = '".$_SESSION['UserID']."'
          WHERE sn_funcion_id='".$_POST['funcion']."' and statusid !=4 ORDER BY sn_orden;";

    $SQL="SELECT 
            distinct tb_botones_status.*
            FROM tb_botones_status
            left JOIN sec_profilexuser ON sec_profilexuser.userid = '".$_SESSION['UserID']."'
            left JOIN sec_funxprofile ON sec_funxprofile.profileid = sec_profilexuser.profileid
            WHERE 
            (tb_botones_status.sn_funcion_id = '".$_POST['funcion']."')
            AND (tb_botones_status.sn_flag_disponible = 1)
            and sn_panel_adecuacion_presupuestal = 1
            AND
            (tb_botones_status.functionid = sec_funxprofile.functionid 
            OR 
            tb_botones_status.functionid = (SELECT sec_funxuser.functionid FROM sec_funxuser WHERE sec_funxuser.functionid = tb_botones_status.functionid and sec_funxuser.userid = '".$_SESSION['UserID']."')
            )             ORDER BY tb_botones_status.functionid ASC;";
    $TransResult = DB_query($SQL, $db);

    while ($myrow = DB_fetch_array($TransResult)) {
        switch ($myrow['statusid']) {
            case '3':
                if($permisoCapturista != '1'){
                   $Mensaje .= '<component-button type="button" id="'.$myrow['statusname'].'" class="'.$myrow['clases'].'"  value="'.$myrow['namebutton'].'"></component-button>'; 
                }
                break;
            default:
                $Mensaje .= '<component-button type="button" id="'.$myrow['statusname'].'" class="'.$myrow['clases'].'"  value="'.$myrow['namebutton'].'"></component-button>';
                break;
        }  
    }

}

if($option == "modificarEstatusRadicacion"){

    $SQL="";
    $accion = $_POST['accion'];
    $folios = $_POST['idFolios'];
    $msj="";
    
    $partidas = $_POST['Partidas'];
    $findString   = ',';
    $blnExiste = strpos($partidas, $findString);


    switch ($accion) {
        case 'avanzar':

            if($permisoValidador == 1){
                $SQL="UPDATE tb_radicacion SET estatus = 3 WHERE id IN (".$_POST['Partidas'].");";
            }else{
                $SQL="UPDATE tb_radicacion SET estatus = (estatus + 1) WHERE id IN (".$_POST['Partidas'].");";
            }

            if($blnExiste === false){
                $msj="Se avanzó la radicación con folio: ".$folios ." correctamente";
            }else{
                $msj="Se avanzaron las radicaciones con folios: ".$folios ." correctamente";
            }
            
            break;
        case 'solicitar':
            $SQL="UPDATE tb_radicacion SET estatus = 4 WHERE id IN (".$_POST['Partidas'].");";

            if($blnExiste === false){
                $msj="Se solicitó la radicación con folio: ".$folios ." correctamente";
            }else{
                $msj="Se solicitaron las radicaciones con folios: ".$folios ." correctamente";
            }
            
            break;
        case 'rechazar':
            $SQL="UPDATE tb_radicacion SET estatus = (estatus -1) WHERE id IN (".$_POST['Partidas'].");";
            
            if($blnExiste === false){
                $msj="Se rechazó la radicación con folio: ".$folios ." correctamente";
            }else{
                $msj="Se rechazaron las radicaciones con folios: ".$folios ." correctamente";
            }

            break;
        case 'cancelar':
            $SQL="UPDATE tb_radicacion SET estatus = 6 WHERE id IN (".$_POST['Partidas'].");";
            
            if($blnExiste === false){
                $msj="Se canceló la radicación con folio: ".$folios ." correctamente";
            }else{
                $msj="Se cancelaron las radicaciones con folios: ".$folios ." correctamente";
            }

            $arrPartidas = explode(",", $_POST['Partidas']);

            break;
    }

    $TransResult = DB_query($SQL, $db);

    if($TransResult){
        if($accion == "cancelar"){
            foreach ($arrPartidas as $idRadicacion) {
                fnInsertPresupuestoLogMovContrarios($db, '292', $idRadicacion);
            }
        }

        $Mensaje=$msj;
    }else{
        $Mensaje="Problemas al modificar las radicaciones con folios: ". $folios;
    }
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


$dataObj = array('sql' => '', 'contenido' => $contenido, 'result' => $TransResult, 'ErrMsg' => $ErrMsg, 'DbgMsg' => $DbgMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);

?>