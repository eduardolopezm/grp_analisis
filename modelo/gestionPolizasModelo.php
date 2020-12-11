<?php
/**
 * Suficiencia Manual
 *
 * @category Panel
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 31/10/2017
 * Fecha Modificación: 31/10/2017
 * Modelos para las operaciones del panel de Suficiencia Manual
 */
//
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
$funcion=2345;
include $PathPrefix."includes/SecurityUrl.php";
include($PathPrefix.'includes/SecurityFunctions.inc');
include($PathPrefix.'includes/SQL_CommonFunctions.inc');

// Include para el periodo
include($PathPrefix . 'includes/DateFunctions.inc');

$ErrMsg = _('');
$contenido = array();
$result = false;
$SQL = '';
$rowsSelected = "";
$RootPath = "";
$Mensaje = "";

header('Content-type: text/html; charset=ISO-8859-1');

$SQL = "SET NAMES 'utf8'";
$TransResult = DB_query($SQL, $db);

$option = $_POST['option'];

if ($option == 'cargarInformacion') {
    $type = $_POST['typeDoc'];
    $transno = $_POST['transnoDoc'];

    // Datos generales
    $txtTagref = "";
    $txtUe = "";
    $txtTipoPoliza = "";
    $txtFolio = "";
    $selectAseguradora = "";
    $selectCobertura = "";
    $txtDeducible = "";
    $txtCoAseguro = "";
    $txtFechaDesde = "";
    $txtFechaHasta = "";
    $SQL = "SELECT sn_tagref, ln_ue, nu_tipo, ln_folio, nu_aseguradora, nu_cobertura, nu_deducible, nu_coaseguro, DATE_FORMAT(dtm_desde, '%d-%m-%Y') as dtm_desde, DATE_FORMAT(dtm_hasta, '%d-%m-%Y') as dtm_hasta,nu_estatuspoliza
    FROM tb_gestion_poliza 
    WHERE nu_type = '".$type."' AND nu_transno = '".$transno."'";
    $ErrMsg = "No se obtuvo la Información de la Póliza";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        $txtTagref = $myrow['sn_tagref'];
        $txtUe = $myrow['ln_ue'];
        $txtTipoPoliza = $myrow['nu_tipo'];
        $txtFolio = $myrow['ln_folio'];
        $selectAseguradora = $myrow['nu_aseguradora'];
        $selectCobertura = $myrow['nu_cobertura'];
        $txtDeducible = $myrow['nu_deducible'];
        $txtCoAseguro = $myrow['nu_coaseguro'];
        $txtFechaDesde = $myrow['dtm_desde'];
        $txtFechaHasta = $myrow['dtm_hasta'];
        $selectEstatusPoliza = $myrow['nu_estatuspoliza'];
    }

    // Detalles de Poliza
    $info = array();
    $SQL = "SELECT nu_type, nu_transno, ln_vehiculo, ln_marca, ln_submarca, nu_anio, nu_precio, ln_ubicacion, ln_tipo, ln_serie, ln_inventario, ln_factura, ln_niveles, ln_uso, ln_anio_construccion, nu_valor_avaluo, ln_paterno, ln_materno, ln_nombre, nu_aseguramiento, nu_assetid,ln_color,ln_placas,ln_curp,ln_rfc
    FROM tb_gestion_poliza_detalle 
    WHERE nu_type = '".$type."' AND nu_transno = '".$transno."'";
    $ErrMsg = "No se obtuvo la Información de la Póliza Detalle";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
    while ($myrow = DB_fetch_array($TransResult)) {
        if ($txtTipoPoliza == '1') {
            // Formulario Vehículo
            // numElementos = 6;
            // $ln_vehiculo = $datos['txtDato1'];
            // $ln_marca = $datos['txtDato2'];
            // $ln_submarca = $datos['txtDato3'];
            // $nu_anio = $datos['txtDato4'];
            // $nu_precio = $datos['txtDato5'];
            // $ln_ubicacion = $datos['txtDato6'];
            $info[] = array(
                //1 - Numero Inventario, 2 - Marca, 3 - Modelo, 4 - Año, 5 - Color, 6 - Placas, 7 - Precio Factura, 8 - Ubicacion 
                'txtDato1' => $myrow ['nu_assetid'],
                'txtDato2' => $myrow ['ln_marca'],
                'txtDato3' => $myrow ['ln_submarca'],
                'txtDato4' => $myrow ['nu_anio'],
                'txtDato5' => $myrow ['ln_color'],
                'txtDato6' => $myrow ['ln_placas'],
                'txtDato7' => $myrow ['nu_precio']
                //'txtDato6' => $myrow ['ln_ubicacion']
            );
        } else if ($txtTipoPoliza == '2') {
            // Formulario Muebles
            // numElementos = 7;
            // $ln_tipo = $datos['txtDato1'];
            // $ln_marca = $datos['txtDato2'];
            // $ln_serie = $datos['txtDato3'];
            // $ln_inventario = $datos['txtDato4'];
            // $ln_factura = $datos['txtDato5'];
            // $nu_precio = $datos['txtDato6'];
            // $ln_ubicacion = $datos['txtDato7'];
            $info[] = array(
                'txtDato1' => $myrow ['nu_assetid'],
                'txtDato2' => $myrow ['ln_marca'],
                'txtDato3' => $myrow ['ln_submarca'],
                'txtDato4' => $myrow ['ln_serie'],
                'txtDato5' => $myrow ['ln_factura'],
                'txtDato6' => $myrow ['nu_precio'],
                'txtDato7' => $myrow ['ln_ubicacion']
            );
        } else if ($txtTipoPoliza == '3') {
            // Formulario Inmuebles
            // numElementos = 6;
            // $ln_tipo = $datos['txtDato1'];
            // $ln_niveles = $datos['txtDato2'];
            // $ln_uso = $datos['txtDato3'];
            // $ln_anio_construccion = $datos['txtDato4'];
            // $nu_valor_avaluo = $datos['txtDato5'];
            // $ln_ubicacion = $datos['txtDato6'];
            $info[] = array(
                'txtDato1' => $myrow ['nu_assetid'],
                'txtDato2' => $myrow ['ln_niveles'],
                'txtDato3' => $myrow ['ln_uso'],
                'txtDato4' => $myrow ['ln_anio_construccion'],
                'txtDato5' => $myrow ['nu_valor_avaluo'],
                'txtDato6' => $myrow ['ln_ubicacion']
            );
        } else if ($txtTipoPoliza == '4') {
            // Formulario Vida
            // numElementos = 5;
            // $ln_paterno = $datos['txtDato1'];
            // $ln_materno = $datos['txtDato2'];
            // $ln_nombre = $datos['txtDato3'];
            // $nu_aseguramiento = $datos['txtDato4'];
            // $nu_precio = $datos['txtDato5'];
            $info[] = array(
                'txtDato1' => $myrow ['nu_assetid'],
                'txtDato2' => $myrow ['ln_paterno'],
                'txtDato3' => $myrow ['ln_materno'],
                'txtDato4' => $myrow ['ln_nombre'],
                'txtDato5' => $myrow ['ln_curp'],
                'txtDato6' => $myrow ['ln_rfc'],
                'txtDato7' => $myrow ['nu_precio']
            );
        }
    }

    $datosRespuesta['transnoDoc'] = $transno;
    $datosRespuesta['txtTagref'] = $txtTagref;
    $datosRespuesta['txtUe'] = $txtUe;
    $datosRespuesta['txtTipoPoliza'] = $txtTipoPoliza;
    $datosRespuesta['txtFolio'] = $txtFolio;
    $datosRespuesta['selectAseguradora'] = $selectAseguradora;
    $datosRespuesta['selectCobertura'] = $selectCobertura;
    $datosRespuesta['txtDeducible'] = $txtDeducible;
    $datosRespuesta['txtCoAseguro'] = $txtCoAseguro;
    $datosRespuesta['txtFechaDesde'] = $txtFechaDesde;
    $datosRespuesta['txtFechaHasta'] = $txtFechaHasta;
    $datosRespuesta['dataJsonGestionDetalle'] = $info;
    $datosRespuesta['selectEstatusPoliza'] = $selectEstatusPoliza;

    // dataJsonGestionDetalle: dataJsonGestionDetalle

    $contenido = $datosRespuesta;
    $result = true;
}

function fnInsertarGestionPoliza($db, $type, $transno, $tagref, $ue, $txtTipoPoliza, $txtFolio, $selectAseguradora, $selectCobertura, $txtDeducible, $txtCoAseguro, $txtFechaDesde, $txtFechaHasta, $selectEstatusPoliza)
{
    $SQL = "SELECT * FROM tb_gestion_poliza WHERE nu_type = '".$type."' AND nu_transno = '".$transno."'";
    $ErrMsg = "No se pudo almacenar la información de Gestión de Póliza";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    $txtFechaDesde = date_create($txtFechaDesde);
    $txtFechaDesde = date_format($txtFechaDesde, "Y-m-d");

    $txtFechaHasta = date_create($txtFechaHasta);
    $txtFechaHasta = date_format($txtFechaHasta, "Y-m-d");

    if (DB_num_rows($TransResult) == 0) {
        $SQL = "INSERT INTO tb_gestion_poliza (nu_type, nu_transno, sn_tagref, ln_ue, nu_tipo, ln_folio, nu_aseguradora, nu_cobertura, nu_deducible, nu_coaseguro, dtm_desde, dtm_hasta, dtm_registro, nu_estatuspoliza) 
        VALUES 
        ('".$type."', '".$transno."', '".$tagref."', '".$ue."', '".$txtTipoPoliza."', '".$txtFolio."', '".$selectAseguradora."', '".$selectCobertura."', '".$txtDeducible."', '".$txtCoAseguro."', '".$txtFechaDesde."', '".$txtFechaHasta."', NOW(), '".$selectEstatusPoliza."')";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
    } else {
        $SQL = "UPDATE tb_gestion_poliza SET 
        sn_tagref = '".$tagref."',
        ln_ue = '".$ue."',
        nu_tipo = '".$txtTipoPoliza."',
        ln_folio = '".$txtFolio."',
        nu_aseguradora = '".$selectAseguradora."',
        nu_cobertura = '".$selectCobertura."',
        nu_deducible = '".$txtDeducible."',
        nu_coaseguro = '".$txtCoAseguro."',
        dtm_desde = '".$txtFechaDesde."',
        dtm_hasta = '".$txtFechaHasta."',
        nu_estatuspoliza = '".$selectEstatusPoliza."'
        WHERE nu_type = '".$type."' AND nu_transno = '".$transno."'";
        $TransResult = DB_query($SQL, $db, $ErrMsg);
    }

    return true;
}

function fnInsertarGestionPolizaDetalle($db, $type = 0, $transno = 0, $ln_vehiculo = "", $ln_marca = "", $ln_submarca = "", $nu_anio = 0, $nu_precio = 0, $ln_ubicacion = "", $ln_tipo = "", $ln_serie = "", $ln_inventario = "", $ln_factura = "", $ln_niveles = "", $ln_uso = "", $ln_anio_construccion = 0, $nu_valor_avaluo = 0, $ln_paterno = "", $ln_materno = "", $ln_nombre = "", $nu_aseguramiento = 0,$nu_assetid,$ln_color,$ln_placas, $ln_curp, $ln_rfc)
{
    $SQL = "INSERT INTO tb_gestion_poliza_detalle (nu_type, nu_transno, ln_vehiculo, ln_marca, ln_submarca, nu_anio, nu_precio, ln_ubicacion, ln_tipo, ln_serie, ln_inventario, ln_factura, ln_niveles, ln_uso, ln_anio_construccion, nu_valor_avaluo, ln_paterno, ln_materno, ln_nombre, nu_aseguramiento,nu_assetid,ln_color,ln_placas, ln_curp, ln_rfc)
    VALUES 
    ('".$type."', '".$transno."', '".$ln_vehiculo."', '".$ln_marca."', '".$ln_submarca."', '".$nu_anio."', '".$nu_precio."', '".$ln_ubicacion."', '".$ln_tipo."', '".$ln_serie."', '".$ln_inventario."', '".$ln_factura."', '".$ln_niveles."', '".$ln_uso."', '".$ln_anio_construccion."', '".$nu_valor_avaluo."', '".$ln_paterno."', '".$ln_materno."', '".$ln_nombre."', '".$nu_aseguramiento."',".$nu_assetid.",'".$ln_color."','".$ln_placas."','".$ln_curp."','".$ln_rfc."')";
    $ErrMsg = "No se pudo almacenar la información de Gestión de Póliza";
    $TransResult = DB_query($SQL, $db, $ErrMsg);
}

if ($option == 'guardarInformacion') {
    $type = $_POST['typeDoc'];
    $transno = $_POST['transnoDoc'];
    $tagref = $_POST['txtTagref'];
    $ue = $_POST['txtUe'];
    $txtTipoPoliza = $_POST['txtTipoPoliza'];
    $txtFolio = $_POST['txtFolio'];
    $selectAseguradora = $_POST['selectAseguradora'];
    $selectCobertura = $_POST['selectCobertura'];
    $txtDeducible = $_POST['txtDeducible'];
    $txtCoAseguro = $_POST['txtCoAseguro'];
    $txtFechaDesde = $_POST['txtFechaDesde'];
    $txtFechaHasta = $_POST['txtFechaHasta'];
    $dataJsonGestionDetalle = $_POST['dataJsonGestionDetalle'];
    $selectEstatusPoliza = $_POST['selectEstatusPoliza'];

    if (empty($transno)) {
        $transno = GetNextTransNo($type, $db);
    }

    fnInsertarGestionPoliza($db, $type, $transno, $tagref, $ue, $txtTipoPoliza, $txtFolio, $selectAseguradora, $selectCobertura, $txtDeducible, $txtCoAseguro, $txtFechaDesde, $txtFechaHasta, $selectEstatusPoliza);

    // Borrar Registros anteriores
    $SQL = "DELETE FROM tb_gestion_poliza_detalle WHERE nu_type = '".$type."' AND nu_transno = '".$transno."'";
    $ErrMsg = "No se pudo eliminar los registros anteriores de la Póliza";
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    foreach ($dataJsonGestionDetalle as $datos) {
        // echo "\n dato1: ".$datos['txtDato1'];
        $ln_vehiculo = "";
        $ln_marca = "";
        $ln_submarca = "";
        $nu_anio = 0;
        $nu_precio = 0;
        $ln_ubicacion = "";
        $ln_tipo = "";
        $ln_serie = "";
        $ln_inventario = "";
        $ln_factura = "";
        $ln_niveles = "";
        $ln_uso = "";
        $ln_anio_construccion = 0;
        $nu_valor_avaluo = 0;
        $ln_paterno = "";
        $ln_materno = "";
        $ln_nombre = "";
        $ln_color = "";
        $ln_placas = "";
        $nu_assetid = "";
        $nu_aseguramiento = 0;
        $nu_empleado=0;
        $ln_curp="";
        $ln_rfc="";

        if ($txtTipoPoliza == '1') {
            // Formulario Vehículo
            //1 - Numero Inventario, 2 - Marca, 3 - Modelo, 4 - Año, 5 - Color, 6 - Placas, 7 - Precio Factura, 8 - Ubicacion 
            $nu_assetid = $datos['txtDato1'];
            $ln_vehiculo = $datos['txtDato1'];
            $ln_marca = $datos['txtDato2'];
            $ln_submarca = $datos['txtDato3'];
            $nu_anio = $datos['txtDato4'];
            $ln_color = $datos['txtDato5'];
            $ln_placas = $datos['txtDato6'];
            $nu_precio = $datos['txtDato7'];
            //$ln_ubicacion = $datos['txtDato6'];
        } else if ($txtTipoPoliza == '2') {
            // Formulario Muebles
            // numElementos = 7;
            $nu_assetid = $datos['txtDato1'];
            $ln_tipo = $datos['txtDato1'];
            $ln_marca = $datos['txtDato2'];
            $ln_submarca = $datos['txtDato3'];
            $ln_serie = $datos['txtDato4'];            
            $ln_factura = $datos['txtDato5'];
            $nu_precio = $datos['txtDato6'];
            //$ln_ubicacion = $datos['txtDato7'];
        } else if ($txtTipoPoliza == '3') {
            // Formulario Inmuebles
            // numElementos = 6;
            $nu_assetid = $datos['txtDato1'];
            $ln_tipo = $datos['txtDato1'];
            $ln_niveles = $datos['txtDato2'];
            $ln_uso = $datos['txtDato3'];
            $ln_anio_construccion = $datos['txtDato4'];
            $nu_valor_avaluo = $datos['txtDato5'];
            $ln_ubicacion = $datos['txtDato6'];
        } else if ($txtTipoPoliza == '4') {
            // Formulario Vida
            // numElementos = 5;
            $nu_assetid = $datos['txtDato1'];
            $ln_paterno = $datos['txtDato2'];
            $ln_materno = $datos['txtDato3'];
            $ln_nombre = $datos['txtDato4'];
            $ln_curp = $datos['txtDato5'];
            $ln_rfc = $datos['txtDato6'];
            $nu_precio = $datos['txtDato7'];
        }

        fnInsertarGestionPolizaDetalle($db, $type, $transno, $ln_vehiculo, $ln_marca, $ln_submarca, $nu_anio, $nu_precio, $ln_ubicacion, $ln_tipo, $ln_serie, $ln_inventario, $ln_factura, $ln_niveles, $ln_uso, $ln_anio_construccion, $nu_valor_avaluo, $ln_paterno, $ln_materno, $ln_nombre, $nu_aseguramiento,$nu_assetid,$ln_color,$ln_placas,$ln_curp,$ln_rfc );
    }

    $datosRespuesta['transno'] = $transno;
    $Mensaje = '<p><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i> El Folio de la Póliza es '.$transno.'</p>';

    $contenido = array('datos' => $datosRespuesta);
    $result = true;
}

$dataObj = array('sql' => '', 'contenido' => $contenido, 'result' => $result, 'RootPath' => $RootPath, 'ErrMsg' => $ErrMsg, 'Mensaje' => $Mensaje);
echo json_encode($dataObj);
