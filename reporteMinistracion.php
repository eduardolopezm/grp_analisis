<?php
/**
 * Reporte de Ministración
 *
 * @category Panel
 * @package ap_grp
 * @author Desarrollo <[<email address>]>
 * @link reporteMinistracion.php
 * Fecha Creación: 31/10/2017
 * Fecha Modificación: 0
 * Visualizar reportes conac y ldf
 */

// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);
// ini_set('error_log', dirname(__FILE__) . '/error_log.txt');

$PageSecurity = 1;

include('config.php');
include('includes/session.inc');
include('jasper/JasperReport.php');
//include('jasperconfig/JasperReport.php');
include('includes/SQL_CommonFunctions.inc');
include "includes/SecurityUrl.php";
include ('Numbers/Words.php');
$enc = new Encryption;

$jreport= "";

if (empty($_GET["Folio"])) {
    $_GET["Folio"]= "0";
}

if (empty($_GET["idMinistracion"])) {
    $_GET["idMinistracion"]= "0";
}

$montoLetra="";
$meses = array('Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio',
               'Agosto','Septiembre','Octubre','Noviembre','Diciembre');

$periodo = "";
$ln_ur="";
$fechaPago="";
$fechaElabora="";
$fechaElabora2="";
$fechaElabora3="";
$numControl="00";
$nombrebeneficiario="";
$rfcbeneficiaro="";
$cuentabeneficiaro="";
$plaza="";
$denominaciontesofe="";
$nombre_cuenta_beneficiario="";
$firma="";
$puestoFirma="";
$noOficio=0;
$titularFirma="";
$logo = "";

if(!empty($_GET["Folio"]) and $_GET["Folio"] !=""){
    $objNumbers = new Numbers_Words();

    $SQL="  SELECT tb_m.ln_ur as ur, 
                concat(tb_m.ln_ur,' - ',tags.tagdescription) as entidadPublica,
                tags.tagdescription,
                lbusiness.taxid as rfcEntidad,
                tb_m.folio,
                truncate(coalesce(SUM(tb_m_d.solicitado),0),2) as solicitado,
                truncate(coalesce(SUM(tb_m_d.autorizado),0),2) as autorizado,
                -- concat(tb_m.ln_ur ,' - ', tb_m.folio,'/', year(fecha_elab)) as numControl,
                concat(tb_m.ln_pp,' - ',tb_pp.descripcion) as programaPresupuestal,
                tb_m.estatus,
                year(tb_m.fecha_elab) as anioElabo,
                tb_m.ln_mes,
                tb_m.fecha_pago,
                tb_m.fecha_elab,
                tb_m.num_transferencia,
                tb_m.num_oficio,
                tb_beneficiario.nombre as nombrebeneficiario,
                tb_beneficiario.rfc as rfcbeneficiaro,
                tb_beneficiario.cuenta as cuentabeneficiaro,
                tb_beneficiario.plaza,
                tb_beneficiario.denominaciontesofe,
                tb_beneficiario.nombre_cuenta,
                lbusiness.logo
            FROM tb_ministracion tb_m
            LEFT JOIN tags on tb_m.ln_ur = tags.tagref
            LEFT JOIN legalbusinessunit lbusiness on tags.legalid  = lbusiness.legalid
            LEFT JOIN tb_ministracion_detalle tb_m_d on tb_m.id = tb_m_d.idMinistracion
            LEFT JOIN tb_cat_programa_presupuestario tb_pp on tb_m.ln_pp = tb_pp.cppt
            LEFT JOIN tb_beneficiario_concentradora tb_beneficiario ON tb_m.idBeneficiario = tb_beneficiario.id
            WHERE tb_m.id= '".$_GET["idMinistracion"]."'
            GROUP BY tb_m.ln_ur;";

    $result = DB_query($SQL, $db);

    if($result){
        while ($myrow = DB_fetch_array($result)) {

            if($myrow['estatus'] == "5"){
                $montoLetra=$myrow['autorizado'];
            }else{
                $montoLetra=$myrow['solicitado'];    
            }

            $separa = explode(".", $montoLetra);
            $enteros = $separa [0];
            $decimales = $separa [1];

            $montoLetra = $objNumbers->toWords(($enteros), 'es');
            $montoLetra = "(".strtoupper(str_replace("ó","Ó",utf8_encode($montoLetra)))." PESOS ".$decimales ."/100 M.N.".")";

            $periodo=strtoupper($meses[intval($myrow['ln_mes'] - 1)]." ".$myrow['anioElabo']);

            $ln_ur=$myrow['ur'];

            $arrFechaPago =explode("-",$myrow['fecha_pago']);
            list($anioPago, $mesPago, $diaPago) = $arrFechaPago;
            $fechaPago = strtoupper($diaPago ." de " . $meses[intval($mesPago)-1] . " de ". $anioPago);

            $arrFechaElabo = explode('-', $myrow['fecha_elab']);
            list($anioElabora, $mesElabora, $diaElabora) = $arrFechaElabo;
            $fechaElabora = strtoupper($diaElabora ." de " . $meses[intval($mesElabora)-1] . " de ". $anioElabora);
            $fechaElabora3 = ($diaElabora ." de " . $meses[intval($mesElabora)-1] . " de ". $anioElabora);
            $fechaElabora2=$myrow['fecha_elab'];

            $folio=$myrow['folio'];
            if(strlen($myrow['folio']) == 1){
                $folio="00".$myrow['folio'];
            }elseif (strlen($myrow['folio']) == 2) {
                $folio="0".$myrow['folio'];
            }

            $numControl = $myrow['ur']." - ".$folio."/".$myrow['anioElabo'];

            $denominaciontesofe=$myrow['denominaciontesofe'];
            $nombre_cuenta_beneficiario=$myrow['nombre_cuenta'];

            $nombrebeneficiario=$myrow['nombrebeneficiario'];
            $rfcbeneficiaro=$myrow['rfcbeneficiaro'];
            $cuentabeneficiaro=$myrow['cuentabeneficiaro'];
            $plaza=$myrow['plaza'];
            $noOficio = $myrow['num_oficio'];
            $logo = $myrow['logo'];
        }
    }

    //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    //!!                                               !!
    //!!                     FIRMA.                    !!
    //!!                                               !!
    //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

    $SQL = "SELECT 
                tb_detalle_firmas.id_nu_detalle_firmas as id,
                CONCAT(tb_detalle_firmas.titulo,' ',tb_empleados.ln_nombre,' ',tb_empleados.sn_primer_apellido ,' ',tb_empleados.sn_segundo_apellido) as firmante,
                tb_detalle_firmas.informacion as puestoFirma,
                tb_reporte_firmas.id_dafault
            FROM tb_ministracion
            LEFT JOIN tb_detalle_firmas on tb_ministracion.idfirmante  = tb_detalle_firmas.id_nu_detalle_firmas
            LEFT JOIN tb_empleados ON tb_detalle_firmas.id_nu_empleado = tb_empleados.id_nu_empleado
            LEFT JOIN tb_reporte_firmas on tb_detalle_firmas.id_nu_detalle_firmas = tb_reporte_firmas.id_nu_detalle_firmas
            WHERE tb_ministracion.id = " . $_GET['idMinistracion'];
    $ErrMsg = "No se obtuvieron los capitulos de las partidas presupuestales";
    //echo $SQL;
    $TransResult = DB_query($SQL, $db, $ErrMsg);

    while ($myrowfirma = DB_fetch_array($TransResult)) {
        $firma=$myrowfirma['firmante'];
        $puestoFirma=$myrowfirma['puestoFirma'];
        $titularFirma=$myrowfirma['id_dafault'];
    }
}else{
    exit();
}

$_GET["anio"] ="2018";
$_GET["entepublico"] = "ente2";
$_GET["fechainicial"]= "01-01-".$_GET["anio"];
$_GET["fechafinal"]= "31-12-".$_GET["anio"];

$JasperReport = new JasperReport($confJasper);

$jreport = $JasperReport->compilerReport("../jasper/rptMinistracion");

/* Parametros obsoletos*/
$JasperReport->addParameter("parEntePublico", "".$_GET["entepublico"]);
$myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechainicial"]);
$newDateStringInicial = $myDateTime->format('Y-m-d');
$myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechafinal"]);
$newDateStringFinal = $myDateTime->format('Y-m-d');
$JasperReport->addParameter("fechainicial", $newDateStringInicial);
$JasperReport->addParameter("fechafinal", $newDateStringFinal);
$JasperReport->addParameter("parRangoDeFechas", "Desde  hasta ");
$JasperReport->addParameter("anioreporte", (int)$_GET["anio"]);
$JasperReport->addParameter("ue", "09");
$JasperReport->addParameter("entepublico", "sdfsd");

/* Parametros usados actualmente*/
$ruta = $JasperReport->getPathFile()."".$logo;
$ruta = str_replace('jasper/', '', $ruta);
$ruta = str_replace('jasperconfig/', '', $ruta);
$JasperReport->addParameter("imagen", $ruta);
$JasperReport->addParameter("SUBREPORT_DIR", $JasperReport->getPathFile() . "../jasper/");

$JasperReport->addParameter("tagref", $ln_ur);
$JasperReport->addParameter("fechaPago", $fechaPago);
$JasperReport->addParameter("periodo", $periodo);
$JasperReport->addParameter("idministracion", $_GET["idMinistracion"]);
$JasperReport->addParameter("nombreFirma", "NOMBRE");
$JasperReport->addParameter("puestoFirma", "PUESTO");
$JasperReport->addParameter("montoLetra", $montoLetra);
$JasperReport->addParameter("fechaReporte", $fechaElabora);
$JasperReport->addParameter("numControl", $numControl);
$JasperReport->addParameter("denominacion", $denominaciontesofe);
$JasperReport->addParameter("nombre_cuenta", $nombre_cuenta_beneficiario);
$JasperReport->addParameter('institucion',$nombrebeneficiario);
$JasperReport->addParameter('rfcbeneficiario',$rfcbeneficiaro);
$JasperReport->addParameter('cuentabeneficiario',$cuentabeneficiaro);
$JasperReport->addParameter('plaza',$plaza);
$JasperReport->addParameter('firmante',$firma);
$JasperReport->addParameter('puestofirmante',$puestoFirma);

$SQL = "SELECT leyenda, parametro,fecha FROM tb_leyendas_reportes WHERE functionid='2387' AND '".$fechaElabora2."' BETWEEN fecha_ini AND fecha_fin  ORDER BY orden";

$TransResult = DB_query($SQL, $db, $ErrMsg);

while ($myrowleyendas = DB_fetch_array($TransResult)) {
    $fecha="";
    if($myrowleyendas['fecha'] !=""){
        $fecha = ' '.$noOficio.', de fecha '.$fechaElabora3.'.';
        if($titularFirma == "selected"){
            $JasperReport->addParameter($myrowleyendas['parametro'],"");  
        }else{
           $JasperReport->addParameter($myrowleyendas['parametro'],($myrowleyendas['leyenda'].' '.$fecha));   
        }
    }else{
        $JasperReport->addParameter($myrowleyendas['parametro'],($myrowleyendas['leyenda']));  
    }
}

$conexion = $JasperReport->getConexionDB($_SESSION ["DatabaseName"], $dbuser, $dbpassword);
$jPrint = $JasperReport->fillReport($jreport, $JasperReport->getParameters(), $conexion);
$pdfBytes = $JasperReport->exportReportPDF($jPrint);

header('Content-type: application/pdf');
header('Content-Length: ' . strlen($pdfBytes));
header('Content-Disposition: inline; filename=report.pdf');

echo $pdfBytes;
